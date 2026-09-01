<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiProxyController extends Controller
{
    public function models(Request $request)
    {
        $config = $this->config($request);
        try {
            $response = Http::withToken($config['apiKey'])
                ->acceptJson()
                ->connectTimeout(10)
                ->timeout(30)
                ->get($config['baseUrl'].'/models', $config['provider'] === 'gemini'
                    ? ['key' => $config['apiKey']]
                    : []);
        } catch (ConnectionException) {
            return response()->json(['message' => 'Base URL provider tidak dapat dihubungi dari server.'], 502);
        }

        return $this->forward($response);
    }

    public function chat(Request $request)
    {
        $config = $this->config($request);
        $payload = $request->validate([
            'model' => ['required', 'string', 'max:200'],
            'messages' => ['required', 'array'],
            'messages.*.role' => ['required', 'string'],
            'messages.*.content' => ['required'],
        ]);

        try {
            $response = Http::withToken($config['apiKey'])
                ->acceptJson()
                ->asJson()
                ->connectTimeout(10)
                ->timeout(120)
                ->post($config['baseUrl'].'/chat/completions', $payload);
        } catch (ConnectionException) {
            return response()->json(['message' => 'Base URL provider tidak dapat dihubungi dari server.'], 502);
        }

        return $this->forward($response);
    }

    private function config(Request $request): array
    {
        $url = trim((string) $request->input('base_url'));
        $apiKey = trim((string) preg_replace('/^Bearer\s+/i', '', (string) $request->header('Authorization')));
        $provider = $request->input('provider', 'openai');

        if (! in_array($provider, ['openai', 'gemini'], true) || ! filter_var($url, FILTER_VALIDATE_URL) || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true) || $apiKey === '') {
            abort(422, 'Provider, Base URL, dan API key wajib diisi dengan benar.');
        }

        $url = rtrim($url, '/');
        $url = preg_replace('~/(?:chat/completions|models)$~i', '', $url);

        return compact('url', 'apiKey', 'provider') + ['baseUrl' => $url];
    }

    private function forward($response)
    {
        return response($response->body(), $response->status())
            ->header('Content-Type', $response->header('Content-Type', 'application/json'));
    }
}