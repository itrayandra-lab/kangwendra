<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScraperConfig extends Model
{
    protected $fillable = ['key', 'value', 'type', 'label', 'description'];

    // Get value as appropriate type
    public function getTypedValueAttribute()
    {
        if ($this->type === 'array') {
            return json_decode($this->value, true) ?? [];
        }
        if ($this->type === 'integer') {
            return (int) $this->value;
        }
        return $this->value;
    }

    // Set value (auto-detect type)
    public function setTypedValueAttribute($val)
    {
        if (is_array($val)) {
            $this->type = 'array';
            $this->value = json_encode($val);
        } elseif (is_int($val)) {
            $this->type = 'integer';
            $this->value = (string) $val;
        } else {
            $this->type = 'string';
            $this->value = (string) $val;
        }
    }

    // Helpers
    public static function getValue(string $key, $default = null)
    {
        $config = static::where('key', $key)->first();
        return $config ? $config->typed_value : $default;
    }

    public static function setValue(string $key, $value, string $label = '', ?string $description = null): self
    {
        $config = static::firstOrCreate(['key' => $key], [
            'label' => $label,
            'description' => $description,
        ]);

        // Preserve type if exists
        $oldType = $config->type;

        if (is_array($value)) {
            $config->type = 'array';
            $config->value = json_encode($value);
        } elseif (is_int($value)) {
            $config->type = 'integer';
            $config->value = (string) $value;
        } else {
            $config->type = $oldType !== 'array' ? 'string' : 'array';
            $config->value = (string) $value;
        }

        $config->label = $label ?: $config->label;
        if ($description) $config->description = $description;
        $config->save();

        return $config;
    }

    public static function getKeywords(): array
    {
        return static::getValue('keywords', [
            'ChatGPT', 'Gemini', 'Claude', 'DeepSeek', 'OpenAI', 'LLM',
            'AI Agent', 'Machine Learning', 'Artificial Intelligence',
            'Anthropic', 'Mistral', 'AI Search',
        ]);
    }

    public static function getMinYear(): int
    {
        return (int) static::getValue('min_year', 2022);
    }

    public static function getSourceUrls(): array
    {
        return static::getValue('source_urls', [
            'searchengineland.com' => 'https://searchengineland.com/sitemap_index.xml',
            'searchenginejournal.com' => 'https://www.searchenginejournal.com/sitemap_index.xml',
        ]);
    }

    public static function getConfidenceThreshold(): int
    {
        return (int) static::getValue('confidence_threshold', 45);
    }

    public static function getDailyLimit(): int
    {
        return (int) static::getValue('daily_limit', 5);
    }
}
