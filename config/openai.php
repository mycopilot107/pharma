<?php

return [
    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'enabled' => (bool) env('OPENAI_ENABLED', false),
    'timeout' => (int) env('OPENAI_TIMEOUT', 60),
    'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 2000),
];
