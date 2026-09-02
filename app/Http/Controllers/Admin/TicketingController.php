<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistSubmission;
use App\Models\DailyActivity;
use App\Models\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Client\ConnectionException;

class TicketingController extends Controller
{
    public function departments()
    {
        return $this->get('/api/support/departments');
    }

    public function types(Request $request)
    {
        $request->validate(['department' => ['required', 'string', 'max:100']]);

        return $this->get('/api/support/ticket-types', $request->only('department'));
    }

    public function categories(Request $request)
    {
        $request->validate([
            'department' => ['required', 'string', 'max:100'],
            'type' => ['required', 'integer'],
        ]);

        return $this->get('/api/support/ticket-categories', $request->only('department', 'type'));
    }

    public function create(Request $request, ChecklistSubmission $submission)
    {
        $payload = $request->validate([
            'item' => ['required', 'string', 'max:255'],
            'service_department' => ['required', 'string', 'max:100'],
            'type' => ['required', 'integer'],
            'category' => ['required', 'string', 'max:150'],
            'user' => ['required', 'string', 'max:255'],
            'departement' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'detail' => ['required', 'string', 'max:5000'],
            'location' => ['required', 'string', 'max:255'],
            'remote_code' => ['nullable', 'string', 'max:255'],
        ]);

        $item = $payload['item'];
        unset($payload['item']);
        $existingTicket = $submission->ticketing_data[$item] ?? null;
        $category = $this->normalizeCategory($payload['category']);
        $payload['category'] = $category;
        if (filled($existingTicket['ticket_url'] ?? null)) {
            $existingTicket = [...$existingTicket, 'category' => $this->normalizeCategory($existingTicket['category'] ?? $category)];
            $this->storeTicketData($submission, $item, $existingTicket);
            $this->storeUserRequest($submission, $item, [...$existingTicket, ...$payload]);
            return response()->json(['success' => true, 'data' => $existingTicket, 'already_created' => true]);
        }

        $response = $this->request('post', '/api/tickets', $payload);
        $body = json_decode($response->getContent(), true);

        $ticket = $this->ticketData($body, $category);
        if ($response->isSuccessful() && ($body['success'] ?? true) !== false && ($ticket['ticket_number'] || $ticket['ticket_url'])) {
            $ticket = [...$ticket, ...collect($payload)->only([
                'user', 'departement', 'contact', 'email', 'detail', 'location',
            ])->all()];
            $ticketingData = $submission->ticketing_data ?? [];
            $ticketingData[$item] = $ticket;
            $this->storeTicketData($submission, $item, $ticket);
            $this->storeUserRequest($submission, $item, $ticket);
        }

        return $response;
    }

    public function status(Request $request, ChecklistSubmission $submission)
    {
        $apiKey = (string) config('services.terra_ticketing.api_key');
        if (! $request->user() && ($apiKey === '' || ! hash_equals($apiKey, (string) $request->header('X-API-Key')))) {
            abort(401, 'Unauthorized ticketing callback.');
        }

        $payload = $request->validate([
            'item' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:50'],
            'ticket_number' => ['nullable', 'string', 'max:100'],
            'ticket_url' => ['nullable', 'url', 'max:2000'],
            'category' => ['nullable', 'string', 'max:150'],
        ]);

        $item = $payload['item'];
        $ticket = $submission->ticketing_data[$item] ?? [];
        $storedCategory = $this->categoryValue($ticket);
        $ticket = array_merge($ticket, array_filter([
            'ticket_number' => $payload['ticket_number'] ?? null,
            'ticket_url' => $payload['ticket_url'] ?? null,
        ], static fn ($value) => filled($value)));
        // The callback may contain Terra's default category. Keep the category
        // selected when the ticket was created instead of replacing it.
        $ticket['category'] = filled($storedCategory)
            ? $storedCategory
            : $this->normalizeCategory($payload['category'] ?? null);
        $this->storeTicketData($submission, $item, $ticket);

        $this->storeUserRequest($submission, $item, $ticket);

        if ($this->isClosedStatus($payload['status'])) {
            $this->createTicketActivity($submission, $item, $ticket);
        }

        return response()->json(['success' => true, 'closed' => $this->isClosedStatus($payload['status']), 'data' => $ticket]);
    }

    private function storeTicketData(ChecklistSubmission $submission, string $item, array $ticket): void
    {
        $ticketingData = $submission->ticketing_data ?? [];
        $ticketingData[$item] = $ticket;
        $submission->update(['ticketing_data' => $ticketingData]);
    }

    private function isClosedStatus(string $status): bool
    {
        return in_array(strtolower(trim($status)), ['close', 'closed', 'resolved', 'completed'], true);
    }

    private function createTicketActivity(ChecklistSubmission $submission, string $item, array $ticket): void
    {
        $ticketNumber = $ticket['ticket_number'] ?? null;
        $legacyActivity = filled($ticketNumber)
            ? DailyActivity::query()
                ->where('user_id', $submission->submitted_by)
                ->where('activity', 'like', 'Selesaikan Ticket #'.$ticketNumber.'%')
                ->first()
            : null;

        if ($legacyActivity) {
            $legacyActivity->update([
                'type' => 'ticketing',
                'category' => $ticket['category'] ?? DailyActivity::DEFAULT_CATEGORY,
                'submission_id' => $submission->id,
                'user_request' => $ticket['user'] ?? null,
                'ticket_item' => $item,
                'ticket_number' => $ticketNumber,
                'ticket_url' => $ticket['ticket_url'] ?? null,
            ]);

            return;
        }

        $activity = DailyActivity::firstOrCreate(
            ['submission_id' => $submission->id, 'ticket_item' => $item],
            [
                'user_id' => $submission->submitted_by,
                'type' => 'ticketing',
                'category' => $ticket['category'] ?? DailyActivity::DEFAULT_CATEGORY,
                'activity_date' => $submission->submission_date ?? today(),
                'activity' => 'Ticketing: '.$item,
                'status' => 'completed',
                'notes' => filled($ticket['ticket_number'] ?? null) ? 'Ticket '.$ticket['ticket_number'] : null,
                'ticket_number' => $ticket['ticket_number'] ?? null,
                'ticket_url' => $ticket['ticket_url'] ?? null,
                'user_request' => $ticket['user'] ?? null,
            ]
        );

        // Older direct-write integrations did not set structured ticket references.
        if ($activity->wasRecentlyCreated === false) {
            $activity->update([
                'type' => 'ticketing',
                'category' => $ticket['category'] ?? DailyActivity::DEFAULT_CATEGORY,
                'ticket_number' => $ticket['ticket_number'] ?? $activity->ticket_number,
                'ticket_url' => $ticket['ticket_url'] ?? $activity->ticket_url,
            ]);
        }
    }

    private function storeUserRequest(ChecklistSubmission $submission, string $item, array $ticket): void
    {
        if (! filled($ticket['user'] ?? null)) {
            return;
        }

        UserRequest::updateOrCreate(
            ['submission_id' => $submission->id, 'ticket_item' => $item],
            collect($ticket)->only(['user', 'departement', 'contact', 'email', 'detail', 'location'])
                ->mapWithKeys(fn ($value, $key) => [$key === 'user' ? 'requester' : $key => $value])
                ->all()
        );
    }

    private function normalizeCategory(?string $category): string
    {
        return filled($category) ? trim($category) : DailyActivity::DEFAULT_CATEGORY;
    }

    private function responseCategory(array $data, string $fallback): string
    {
        return $this->categoryValue($data) ?? $this->normalizeCategory($fallback);
    }

    private function categoryValue(array $data): ?string
    {
        $category = $data['category'] ?? $data['category_name'] ?? null;

        if (is_array($category)) {
            $category = $category['name']
                ?? $category['category_name']
                ?? $category['label']
                ?? $category['value']
                ?? null;
        }

        return filled($category) ? trim((string) $category) : null;
    }

    private function ticketData(array $body, string $category): array
    {
        $data = $body['data'] ?? $body['ticket'] ?? $body;

        return [
            'ticket_number' => $data['ticket_number'] ?? $data['number'] ?? $data['ticket_no'] ?? null,
            'ticket_url' => $data['ticket_url'] ?? $data['url'] ?? null,
            'category' => $this->responseCategory($data, $category),
        ];
    }

    private function get(string $path, array $query = [])
    {
        return $this->request('get', $path, $query);
    }

    private function request(string $method, string $path, array $data = [])
    {
        $url = rtrim((string) config('services.terra_ticketing.base_url'), '/') . $path;
        $apiKey = (string) config('services.terra_ticketing.api_key');

        if ($apiKey === '' || config('services.terra_ticketing.base_url') === null) {
            return response()->json(['message' => 'Konfigurasi API ticketing belum tersedia.'], 503);
        }

        try {
            $client = Http::withHeaders(['X-API-Key' => $apiKey])
                ->acceptJson()
                ->connectTimeout(10)
                ->timeout(30);
            $response = $method === 'get'
                ? $client->get($url, $data)
                : $client->asJson()->post($url, $data);
        } catch (ConnectionException) {
            return response()->json(['message' => 'Server ticketing tidak dapat dihubungi.'], 502);
        }

        return response($response->body(), $response->status())
            ->header('Content-Type', $response->header('Content-Type', 'application/json'));
    }
}