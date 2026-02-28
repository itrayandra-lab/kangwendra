<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\FileHelper;
use App\Models\WebIdentity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WebIdentityController extends Controller
{
    public function index()
    {
        $webIdentity = WebIdentity::first(); 
        return view('pages.admin.web-identities.index', compact('webIdentity'))->with('page', 'Identitas Web');
    }

    public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'web_name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'domain' => 'nullable|url|max:255',
            'phone_number' => 'nullable|string|max:20',
            'facebook_link' => 'nullable|url|max:255',
            'instagram_link' => 'nullable|url|max:255',
            'youtube_link' => 'nullable|url|max:255',
            'twitter_link' => 'nullable|url|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'og_image' => 'nullable|image|max:4096',
            'google_maps' => 'nullable|string|max:255',
            'favicon' => 'nullable|image|max:4096',
            'logo' => 'nullable|image|max:4096',
            'status' => 'nullable|in:active,inactive',
            'version' => 'nullable|string|max:50',
            'api_posts' => 'nullable|string',
            'api_key_master' => 'nullable|string',
            'is_master' => 'nullable|boolean',
        ], [
            'domain.url' => 'Domain harus berupa URL yang valid.',
            'facebook_link.url' => 'Tautan Facebook harus berupa URL yang valid.',
            'instagram_link.url' => 'Tautan Instagram harus berupa URL yang valid.',
            'youtube_link.url' => 'Tautan YouTube harus berupa URL yang valid.',
            'twitter_link.url' => 'Tautan Twitter harus berupa URL yang valid.',
            'og_image.image' => 'OG Image harus berupa file gambar.',
            'og_image.max' => 'Ukuran OG Image tidak boleh lebih dari 2MB.',
            'favicon.image' => 'Favicon harus berupa file gambar.',
            'logo.image' => 'Logo harus berupa file gambar.',
            'favicon.max' => 'Ukuran favicon tidak boleh lebih dari 2MB.',
            'logo.max' => 'Ukuran logo tidak boleh lebih dari 2MB.',
        ]);

        $data = $request->except(['favicon', 'logo', 'og_image']); 

        if ($request->hasFile('favicon') && $request->file('favicon')->isValid()) {
            $data['favicon'] = FileHelper::saveFile($request->file('favicon'), 'web-identities', 'favicon');
        }
        
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $data['logo'] = FileHelper::saveFile($request->file('logo'), 'web-identities', 'logo');
        }
        
        if ($request->hasFile('og_image') && $request->file('og_image')->isValid()) {
            $data['og_image'] = FileHelper::saveFile($request->file('og_image'), 'web-identities', 'og_image');
        }        

        $webIdentity = WebIdentity::first();
        if ($webIdentity) {
            $webIdentity->update($data);
            return redirect()->back()->with('success', 'Identitas web berhasil diperbarui.');
        } else {
            WebIdentity::create($data);
            return redirect()->back()->with('success', 'Identitas web berhasil dibuat.');
        }
    }
}