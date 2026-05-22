<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenAiService
{
    public function isConfigured(): bool
    {
        return config('openai.enabled') && filled(config('openai.api_key'));
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, ?int $maxTokens = null): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('OpenAI is not configured. Set OPENAI_API_KEY and OPENAI_ENABLED=true in .env');
        }

        $response = Http::timeout(config('openai.timeout'))
            ->withToken(config('openai.api_key'))
            ->when(config('openai.organization'), fn ($http) => $http->withHeaders([
                'OpenAI-Organization' => config('openai.organization'),
            ]))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('openai.model'),
                'messages' => $messages,
                'max_tokens' => $maxTokens ?? config('openai.max_tokens'),
                'temperature' => 0.4,
            ]);

        if (! $response->successful()) {
            Log::error('OpenAI API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('OpenAI request failed: '.$response->json('error.message', 'Unknown error'));
        }

        return trim($response->json('choices.0.message.content', ''));
    }
}
