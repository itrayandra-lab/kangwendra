<?php

return [

    // Order of attempts: default -> premium -> fallback. Providers without an API key are skipped.
    'default' => env('AI_DEFAULT_PROVIDER', 'deepseek'),
    'premium' => env('AI_PREMIUM_PROVIDER', 'openai'),
    'fallback' => env('ARAY_FALLBACK_PROVIDER', env('AILUNA_FALLBACK_PROVIDER', 'groq')),

    // All three speak the OpenAI-compatible /chat/completions protocol.
    'providers' => [
        'deepseek' => [
            'key' => env('DEEPSEEK_API_KEY'),
            // Separate from DEEPSEEK_MODEL, which the article paraphrase pipeline uses.
            'model' => env('ARAY_DEEPSEEK_MODEL', env('AILUNA_DEEPSEEK_MODEL', 'deepseek-chat')),
            'url' => rtrim(env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'), '/') . '/chat/completions',
        ],
        'openai' => [
            'key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'url' => 'https://api.openai.com/v1/chat/completions',
        ],
        'groq' => [
            'key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),
            // gpt-oss is a reasoning model: keep its thinking short so the answer fits in max_tokens.
            'extra' => ['reasoning_effort' => 'low'],
            'url' => 'https://api.groq.com/openai/v1/chat/completions',
        ],
    ],

    'max_tokens' => (int) env('ARAY_MAX_TOKENS', 1500),
    'temperature' => 0.6,
    'timeout' => 30,
    'connect_timeout' => 8,

    'history_turns' => 8,
    'max_message_chars' => 1500,

    // Cost and abuse protection.
    'limits' => [
        'per_minute' => (int) env('ARAY_PER_MINUTE', 8),
        'per_day' => (int) env('ARAY_PER_DAY', 120),
        'global_daily' => (int) env('ARAY_GLOBAL_DAILY', 2500),
    ],
];
