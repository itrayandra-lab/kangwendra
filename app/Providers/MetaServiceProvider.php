<?php

namespace App\Providers;

use App\Models\WebIdentity;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MetaServiceProvider extends ServiceProvider
{
    private function cleanText($text)
    {
        $text = strip_tags($text); 
        $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        $text = preg_replace('/\s+/', ' ', trim($text)); 
        return $text;
    }

    public function register(): void
    {
        //
    }

    private static ?object $webIdentity = null;

    private function getWebIdentity(): ?object
    {
        if (self::$webIdentity !== null) {
            return self::$webIdentity;
        }

        try {
            DB::connection()->getPdo();
            if (Schema::hasTable('web_identities')) {
                self::$webIdentity = Cache::remember('web_identity_meta', 3600, function () {
                    return WebIdentity::orderBy('id', 'desc')->first() ?: false;
                });
            }
        } catch (\Exception $e) {
            self::$webIdentity = false;
        }

        return self::$webIdentity ?: null;
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $defaultMeta = [
                'web_name' => config('app.name', 'Portal Informasi'),
                'domain' => url('/'),
                'email' => 'info@example.com',
                'phone_number' => '#',
                'facebook_link' => '#',
                'instagram_link' => '#',
                'youtube_link' => '#',
                'twitter_link' => '#',
                'google_maps' => 'https://maps.google.com/',
                'meta_title' => 'Portal Informasi kuli it tecno',
                'meta_description' => 'Description Portal Informasi kuli it tecno',
                'meta_keywords' => 'portal, informasi',
                'og_image' => asset('assets/img/kuliit.png'),
                'favicon' => asset('assets/img/logo_2.png'),
                'logo' => asset('assets/img/kuliit.png'),
                'status' => 'active',
                'version' => '1.0.0',
            ];

            $webIdentity = $this->getWebIdentity();
            if ($webIdentity) {
                $defaultMeta['web_name'] = $webIdentity->web_name ?? $defaultMeta['web_name'];
                $defaultMeta['domain'] = $webIdentity->domain ?? $defaultMeta['domain'];
                $defaultMeta['email'] = $webIdentity->email ?? $defaultMeta['email'];
                $defaultMeta['phone_number'] = $webIdentity->phone_number ?? $defaultMeta['phone_number'];
                $defaultMeta['facebook_link'] = $webIdentity->facebook_link ?? $defaultMeta['facebook_link'];
                $defaultMeta['instagram_link'] = $webIdentity->instagram_link ?? $defaultMeta['instagram_link'];
                $defaultMeta['youtube_link'] = $webIdentity->youtube_link ?? $defaultMeta['youtube_link'];
                $defaultMeta['twitter_link'] = $webIdentity->twitter_link ?? $defaultMeta['twitter_link'];
                $defaultMeta['google_maps'] = $webIdentity->google_maps ?? $defaultMeta['google_maps'];
                $defaultMeta['meta_title'] = $webIdentity->meta_title ?? $defaultMeta['meta_title'];
                $defaultMeta['meta_description'] = $webIdentity->meta_description ?? $defaultMeta['meta_description'];
                $defaultMeta['meta_keywords'] = $webIdentity->meta_keywords ?? $defaultMeta['meta_keywords'];
                $defaultMeta['og_image'] = $webIdentity->og_image ? getFile($webIdentity->og_image) : $defaultMeta['og_image'];
                $defaultMeta['favicon'] = $webIdentity->favicon ? getFile($webIdentity->favicon) : $defaultMeta['favicon'];
                $defaultMeta['logo'] = $webIdentity->logo ? getFile($webIdentity->logo) : $defaultMeta['logo'];
                $defaultMeta['status'] = $webIdentity->status ?? $defaultMeta['status'];
                $defaultMeta['version'] = $webIdentity->version ?? $defaultMeta['version'];
            }

            $data = $view->getData();
            if (isset($data['page'])) {
                $content = $data['page'];
                if (is_object($content)) {
                    $defaultMeta['meta_title'] = $content->title ?? $defaultMeta['meta_title'];
                    $defaultMeta['meta_description'] = $this->cleanText($content->description ?? $content->title ?? $defaultMeta['meta_description']);
                    $defaultMeta['meta_keywords'] = $defaultMeta['meta_keywords'] . ', ' . $content->title;
                } else {
                    $defaultMeta['meta_title'] = $content ?? $defaultMeta['meta_title'];
                    $defaultMeta['meta_description'] = $this->cleanText($content);
                    $defaultMeta['meta_keywords'] = $defaultMeta['meta_keywords'] . ', ' . $content;
                }
            } elseif (isset($data['info'])) {
                $content = $data['info'];
                $defaultMeta['meta_title'] = $content->title ?? $defaultMeta['meta_title'];
                $defaultMeta['meta_description'] = $defaultMeta['meta_keywords'] ?? $this->cleanText($content->description ?? $content->title);
                $defaultMeta['meta_keywords'] = $defaultMeta['meta_keywords'] ;
            } elseif (isset($data['banner'])) {
                $content = $data['banner'];
                $defaultMeta['meta_title'] = $content->title ?? $defaultMeta['meta_title'];
                $defaultMeta['meta_description'] = $this->cleanText($content->description ?? $content->title);
                $defaultMeta['meta_keywords'] = $defaultMeta['meta_keywords'] . ', ' . $content->title;
            } elseif (isset($data['video'])) {
                $content = $data['video'];
                $defaultMeta['meta_title'] = $content->title ?? $defaultMeta['meta_title'];
                $defaultMeta['meta_description'] = $this->cleanText($content->description ?? $content->title);
                $defaultMeta['meta_keywords'] = $defaultMeta['meta_keywords'] . ', ' . $content->title;
                $defaultMeta['og_image'] = $content->image ? getFile($content->image) : $defaultMeta['og_image'];
            } elseif (isset($data['post'])) {
                $content = $data['post'];
                $defaultMeta['meta_title'] = $content->title ?? $defaultMeta['meta_title'];
                $defaultMeta['meta_description'] = $this->cleanText($content->description ?? $content->title);
                $defaultMeta['meta_keywords'] = $defaultMeta['meta_keywords'] . ', ' . $content->title;
                $defaultMeta['og_image'] = $content->image ? getFile($content->image) : $defaultMeta['og_image'];
            }

            $view->with('meta', (object) $defaultMeta);
        });
    }
}