<?php

namespace App\Services;

use Illuminate\Support\Str;

class NewsService
{
    /**
     * Extract domain from HTML description (used by UpdateExistingPostsDomain)
     */
    public function extractDomainFromDescription($description)
    {
        if (preg_match('/<a\s+href=["\']([^"\']+)["\']/', $description, $matches)) {
            $url    = $matches[1];
            $domain = parse_url($url, PHP_URL_HOST);
            return $domain ? str_replace('www.', '', $domain) : null;
        }
        return null;
    }

    /**
     * Extract domain from article title using known source mappings (used by UpdateExistingPostsDomain)
     */
    public function extractDomainFromTitle($title)
    {
        $sourceToDomain = [
            'Kompas.com' => 'kompas.com', 'Kompas.id' => 'kompas.com',
            'Kontan' => 'kontan.co.id', 'kontan.co.id' => 'kontan.co.id',
            'CNBC Indonesia' => 'cnbcindonesia.com', 'CNBC' => 'cnbc.com',
            'Tempo' => 'tempo.co', 'tempo.co' => 'tempo.co',
            'Detik' => 'detik.com', 'detik.com' => 'detik.com',
            'Tribun' => 'tribunnews.com', 'tribunnews.com' => 'tribunnews.com',
            'Liputan6' => 'liputan6.com', 'liputan6.com' => 'liputan6.com',
            'Viva' => 'viva.co.id', 'viva.co.id' => 'viva.co.id',
            'Sindonews' => 'sindonews.com', 'sindonews.com' => 'sindonews.com',
            'Okezone' => 'okezone.com', 'okezone.com' => 'okezone.com',
            'Merdeka' => 'merdeka.com', 'merdeka.com' => 'merdeka.com',
            'Republika' => 'republika.co.id', 'republika.co.id' => 'republika.co.id',
            'Jawa Pos' => 'jawapos.com', 'jawapos.com' => 'jawapos.com',
            'Antara' => 'antaranews.com', 'antaranews.com' => 'antaranews.com',
            'kumparan' => 'kumparan.com', 'kumparan.com' => 'kumparan.com',
            'CNN Indonesia' => 'cnnindonesia.com', 'cnnindonesia.com' => 'cnnindonesia.com',
            'CNN' => 'cnn.com', 'cnn.com' => 'cnn.com',
            'BBC' => 'bbc.com', 'bbc.com' => 'bbc.com',
            'TechCrunch' => 'techcrunch.com', 'techcrunch.com' => 'techcrunch.com',
            'The Verge' => 'theverge.com', 'theverge.com' => 'theverge.com',
            'Wired' => 'wired.com', 'wired.com' => 'wired.com',
            'Ars Technica' => 'arstechnica.com', 'arstechnica.com' => 'arstechnica.com',
            'Engadget' => 'engadget.com', 'engadget.com' => 'engadget.com',
            'ZDNet' => 'zdnet.com', 'zdnet.com' => 'zdnet.com',
            'Gizmodo' => 'gizmodo.com', 'gizmodo.com' => 'gizmodo.com',
            'Forbes' => 'forbes.com', 'forbes.com' => 'forbes.com',
            'The Information' => 'theinformation.com', 'theinformation.com' => 'theinformation.com',
            'VentureBeat' => 'venturebeat.com', 'venturebeat.com' => 'venturebeat.com',
            'MIT Tech Review' => 'technologyreview.com', 'technologyreview.com' => 'technologyreview.com',
            'Google News' => 'news.google.com', 'news.google.com' => 'news.google.com',
            'Microsoft' => 'microsoft.com', 'microsoft.com' => 'microsoft.com',
            'Apple' => 'apple.com', 'apple.com' => 'apple.com',
            'Amazon' => 'amazon.com', 'amazon.com' => 'amazon.com',
            'Samsung' => 'samsung.com', 'samsung.com' => 'samsung.com',
            'Xiaomi' => 'xiaomi.com', 'xiaomi.com' => 'xiaomi.com',
            'Inilah' => 'inilah.com', 'inilah.com' => 'inilah.com',
            'Rmol' => 'rmol.co.id', 'rmol.co.id' => 'rmol.co.id',
            'Okezone' => 'okezone.com', 'okezone.com' => 'okezone.com',
        ];

        foreach ($sourceToDomain as $source => $domain) {
            if (Str::contains(Str::lower($title), Str::lower($source))) {
                return $domain;
            }
        }

        return null;
    }
}
