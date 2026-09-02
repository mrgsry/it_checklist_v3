<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mapping = [
            'Jaringan' => 'Network & Connectivity',
            'Network/System' => 'Network & Connectivity',
            'Hardware' => 'Hardware & Devices',
            'Peripherals' => 'Hardware & Devices',
            'Aplikasi' => 'Software & Application',
            'Software' => 'Software & Application',
            'App Request' => 'IT Service Request (Non-Incident)',
            'Feature Request' => 'IT Service Request (Non-Incident)',
            'Infrastruktur' => 'Infrastructure & Server',
            'Keamanan' => 'Infrastructure & Server',
            'Lainnya' => 'IT Service Request (Non-Incident)',
            'Bug / Error' => 'IT Service Request (Non-Incident)',
        ];

        foreach ($mapping as $old => $new) {
            DB::table('daily_activities')->where('category', $old)->update(['category' => $new]);
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE daily_activities ALTER COLUMN category SET DEFAULT 'IT Service Request (Non-Incident)'");
        }

        $submissions = DB::table('checklist_submissions')
            ->whereNotNull('ticketing_data')
            ->get(['id', 'ticketing_data']);

        foreach ($submissions as $submission) {
            $ticketingData = json_decode((string) $submission->ticketing_data, true);

            if (! is_array($ticketingData)) {
                continue;
            }

            foreach ($ticketingData as $item => $ticket) {
                $category = is_array($ticket) ? ($ticket['category'] ?? null) : null;
                $ticketNumber = is_array($ticket) ? ($ticket['ticket_number'] ?? null) : null;

                if (! filled($category)) {
                    continue;
                }

                if (is_array($category)) {
                    $category = $category['name']
                        ?? $category['category_name']
                        ?? $category['label']
                        ?? $category['value']
                        ?? null;
                }

                DB::table('daily_activities')
                    ->where('type', 'ticketing')
                    ->where(function ($query) use ($submission, $item, $ticketNumber): void {
                        $query->where(function ($query) use ($submission, $item): void {
                            $query->where('submission_id', $submission->id)
                                ->where('ticket_item', $item);
                        });

                        if (filled($ticketNumber)) {
                            $query->orWhere('activity', 'like', 'Selesaikan Ticket #'.$ticketNumber.'%');
                        }
                    })
                    ->update(['category' => $category]);
            }
        }
    }

    public function down(): void
    {
        // The previous category cannot be reconstructed safely.
    }
};