@php
    /*
     * One JSON-LD @graph for every public page: Person + Organization + WebSite + WebPage (+ BreadcrumbList).
     * Article pages push their own Article schema via @push('structured-data'); it links to these @ids.
     * Only real data is emitted: placeholder links ('#') and empty values are dropped, and no ratings are invented.
     */
    $sOrigin = preg_match('~^(https?://[^/]+)~', $canonicalUrl, $om) ? $om[1] : rtrim(url('/'), '/');
    $sPersonId = $sOrigin . '/#person';
    $sOrgId = $sOrigin . '/#organization';
    $sSiteId = $sOrigin . '/#website';
    $sPageUrl = strtok($canonicalUrl, '?');

    $sReal = fn ($v) => (! empty($v) && ! in_array($v, ['#', 'info@example.com'], true)) ? $v : null;
    $sLogo = ! empty($meta->logo) ? getFile($meta->logo) : null;
    $sImage = $seoImage ?: asset('assets/img/background/section-1-hero-1-bg.png');

    $sSame = array_values(array_unique(array_filter([
        $sReal($meta->youtube_link ?? null) ?: config('services.youtube.kang_wendra_channel_url'),
        $sReal($meta->instagram_link ?? null),
        $sReal($meta->facebook_link ?? null),
        $sReal($meta->twitter_link ?? null),
    ])));

    $sDesc = $seoDesc;

    $sPerson = array_filter([
        '@type' => 'Person',
        '@id' => $sPersonId,
        'name' => 'Wendra Wilendra, M.MT.',
        'alternateName' => 'Kang Wendra',
        'jobTitle' => 'Brand & AI Architect',
        'description' => 'Brand & AI Architect yang merancang bagaimana brand, manusia, AI, knowledge, dan intelligent systems bekerja sebagai satu sistem.',
        'url' => $sOrigin . '/',
        'image' => $sImage,
        'knowsAbout' => ['Brand architecture', 'Branding', 'AI understanding & discovery', 'Intelligent business systems', 'Generative engine optimization', 'Artificial intelligence'],
        'worksFor' => ['@id' => $sOrgId],
        'sameAs' => $sSame ?: null,
    ]);

    $sOrg = array_filter([
        '@type' => 'Organization',
        '@id' => $sOrgId,
        'name' => 'Kang Wendra',
        'alternateName' => $sReal($meta->web_name ?? null),
        'url' => $sOrigin . '/',
        'description' => $sDesc,
        'logo' => $sLogo ? ['@type' => 'ImageObject', 'url' => $sLogo] : null,
        'founder' => ['@id' => $sPersonId],
        'email' => $sReal($meta->email ?? null),
        'sameAs' => $sSame ?: null,
    ]);

    $sSite = [
        '@type' => 'WebSite',
        '@id' => $sSiteId,
        'url' => $sOrigin . '/',
        'name' => 'Kang Wendra — Brand & AI Architect',
        'inLanguage' => 'id-ID',
        'publisher' => ['@id' => $sOrgId],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => ['@type' => 'EntryPoint', 'urlTemplate' => $sOrigin . '/search?q={search_term_string}'],
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $sPage = [
        '@type' => 'WebPage',
        '@id' => $sPageUrl . '#webpage',
        'url' => $sPageUrl,
        'name' => $seoTitle,
        'description' => $seoDesc,
        'inLanguage' => 'id-ID',
        'isPartOf' => ['@id' => $sSiteId],
        'about' => ['@id' => $sPersonId],
    ];

    $sCrumbs = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => $sOrigin . '/']];
    if (isset($category) && $category && ! empty($category->name)) {
        $sCrumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $category->name, 'item' => $sOrigin . '/' . $category->slug];
    }
    $sCrumb = ['@type' => 'BreadcrumbList', '@id' => $sPageUrl . '#breadcrumb', 'itemListElement' => $sCrumbs];

    $sGraph = ['@context' => 'https://schema.org', '@graph' => [$sPerson, $sOrg, $sSite, $sPage, $sCrumb]];
@endphp
<script type="application/ld+json">{!! json_encode($sGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
