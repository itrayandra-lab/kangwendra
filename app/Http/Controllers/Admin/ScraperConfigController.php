<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScraperConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ScraperConfigController extends Controller
{
    public function index()
    {
        $configs = ScraperConfig::orderBy('key')->get();

        // Group by category
        $grouped = [
            'keywords' => $configs->where('key', 'keywords')->first(),
            'min_year' => $configs->where('key', 'min_year')->first(),
            'source_urls' => $configs->where('key', 'source_urls')->first(),
            'confidence_threshold' => $configs->where('key', 'confidence_threshold')->first(),
            'daily_limit' => $configs->where('key', 'daily_limit')->first(),
        ];

        return view('pages.admin.scraper-config.index', compact('grouped'));
    }

    // Update single config
    public function update(Request $request, string $key)
    {
        $config = ScraperConfig::where('key', $key)->first();
        if (!$config) {
            return back()->with('error', "Config '{$key}' not found.");
        }

        $value = $request->input('value');

        // Parse based on type
        if ($config->type === 'array') {
            $items = array_filter(array_map('trim', explode("\n", $value)));
            $config->value = json_encode($items);
        } elseif ($config->type === 'integer') {
            $config->value = (string) max(1, (int) $value);
        } else {
            $config->value = trim($value);
        }

        $config->save();

        return back()->with('success', "Pengaturan '" . $config->label . "' berhasil diupdate.");
    }

    // Add single item to an array config (e.g. add a keyword or source URL)
    public function addItem(Request $request, string $key)
    {
        $config = ScraperConfig::where('key', $key)->first();
        if (!$config) {
            return back()->with('error', "Config '{$key}' not found.");
        }

        $newItem = trim($request->input('item'));
        if (empty($newItem)) {
            return back()->with('error', 'Item tidak boleh kosong.');
        }

        $items = $config->typed_value;
        if (!is_array($items)) $items = [];

        if ($key === 'source_urls') {
            // Key = domain, Value = URL
            $domain = trim($request->input('domain'));
            if (empty($domain)) return back()->with('error', 'Domain tidak boleh kosong.');
            $items[$domain] = $newItem;
        } else {
            if (!in_array($newItem, $items)) {
                $items[] = $newItem;
            }
        }

        $config->value = json_encode(array_values($items));
        $config->save();

        return back()->with('success', 'Item berhasil ditambahkan.');
    }

    // Remove single item from an array config
    public function removeItem(Request $request, string $key)
    {
        $config = ScraperConfig::where('key', $key)->first();
        if (!$config) {
            return back()->with('error', "Config '{$key}' not found.");
        }

        $items = $config->typed_value;
        if (!is_array($items)) $items = [];

        if ($key === 'source_urls') {
            $domain = $request->input('domain');
            unset($items[$domain]);
        } else {
            $item = $request->input('item');
            $items = array_values(array_filter($items, fn($i) => $i !== $item));
        }

        $config->value = json_encode($items);
        $config->save();

        return back()->with('success', 'Item berhasil dihapus.');
    }

    public function flushCache()
    {
        // Flush all sitemap discovery caches
        $sourceUrls = ScraperConfig::getSourceUrls();
        foreach (array_keys($sourceUrls) as $domain) {
            Cache::forget('sitemap_discovered_' . md5("https://www.{$domain}/sitemap_index.xml"));
            Cache::forget('sitemap_discovered_' . md5("https://{$domain}/sitemap_index.xml"));
        }

        // Also flush general caches
        Cache::flush();

        return back()->with('success', 'Cache sitemap berhasil dibersihkan. Scraping berikutnya akan fetch ulang dari sumber.');
    }
}
