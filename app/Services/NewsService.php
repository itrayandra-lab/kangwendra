<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Posts;
use App\Models\PostCategory;
use App\Models\User;

class NewsService
{
    public function fetchFromYahooAiRss($date = null)
    {
        // RSS Yahoo News untuk kategori Technology dan AI
        $yahooRssUrls = [
            'https://news.yahoo.com/rss/technology',
            'https://news.yahoo.com/rss/science',
            'https://finance.yahoo.com/news/rssindex',
        ];

        // Keywords untuk filter artikel tech/AI
        $keywords = [
            'ai', 'artificial intelligence', 'machine learning', 'deep learning',
            'neural network', 'chatgpt', 'llm', 'generative ai', 'large language model',
            'openai', 'gemini', 'technology', 'tech', 'robot', 'automation',
            'semiconductor', 'chip', 'cybersecurity', 'cloud computing', 'software',
            'microsoft', 'google', 'apple', 'meta', 'nvidia', 'startup',
        ];

        // Mapping keyword ke label tag yang tampil
        $tagMap = [
            'artificial intelligence' => 'Artificial Intelligence',
            'machine learning'        => 'Machine Learning',
            'deep learning'           => 'Deep Learning',
            'neural network'          => 'Neural Network',
            'large language model'    => 'Large Language Model',
            'generative ai'           => 'Generative AI',
            'chatgpt'                 => 'ChatGPT',
            'openai'                  => 'OpenAI',
            'gemini'                  => 'Gemini',
            'llm'                     => 'LLM',
            'cybersecurity'           => 'Cybersecurity',
            'cloud computing'         => 'Cloud Computing',
            'semiconductor'           => 'Semiconductor',
            'automation'              => 'Automation',
            'robot'                   => 'Robotics',
            'nvidia'                  => 'Nvidia',
            'microsoft'               => 'Microsoft',
            'google'                  => 'Google',
            'apple'                   => 'Apple',
            'meta'                    => 'Meta',
            'startup'                 => 'Startup',
            'chip'                    => 'Chip',
            'software'                => 'Software',
            'technology'              => 'Technology',
            'tech'                    => 'Tech',
            ' ai '                    => 'AI',
        ];

        $newsItems = [];
        $seenLinks = [];

        foreach ($yahooRssUrls as $rssUrl) {
            $response = Http::timeout(15)->get($rssUrl);

            if (!$response->successful()) {
                continue;
            }

            $xml = simplexml_load_string($response->body());
            if (!$xml) {
                continue;
            }

            foreach ($xml->channel->item as $item) {
                $title       = (string)$item->title;
                $link        = (string)$item->link;
                $description = strip_tags((string)$item->description);
                $pubDate     = (string)$item->pubDate;
                $itemDate    = date('Y-m-d', strtotime($pubDate));

                // Filter by date if provided
                if ($date && $itemDate !== $date) {
                    continue;
                }

                // Hindari duplikat
                if (in_array($link, $seenLinks)) {
                    continue;
                }

                // Filter hanya artikel yang mengandung kata kunci tech/AI
                $haystack  = strtolower(' ' . $title . ' ' . $description . ' ');
                $isRelated = false;
                foreach ($keywords as $keyword) {
                    if (strpos($haystack, $keyword) !== false) {
                        $isRelated = true;
                        break;
                    }
                }

                if (!$isRelated) {
                    continue;
                }

                $seenLinks[] = $link;

                // Kumpulkan tag dari keyword yang match di judul/deskripsi
                $articleTags = ['Yahoo AI'];
                foreach ($tagMap as $keyword => $label) {
                    if (strpos($haystack, $keyword) !== false && !in_array($label, $articleTags)) {
                        $articleTags[] = $label;
                    }
                }
                // Maksimal 5 tag agar tidak terlalu banyak
                $articleTags = array_slice($articleTags, 0, 5);

                $sourceName     = (string)$item->source;
                $originalDomain = $this->extractDomainFromTitle($title . ' - ' . $sourceName);

                if (!$originalDomain) {
                    $linkDomain     = parse_url($link, PHP_URL_HOST);
                    $originalDomain = $linkDomain ? str_replace('www.', '', $linkDomain) : '-';
                }

                $newsItems[] = [
                    'title'        => $title,
                    'link'         => $link,
                    'description'  => $description,
                    'published_at' => date('Y-m-d H:i:s', strtotime($pubDate)),
                    'source'       => 'Yahoo News (Technology & AI)',
                    'domain'       => $originalDomain ?: '-',
                    'tags'         => $articleTags,
                ];
            }
        }

        return $newsItems;
    }

    public function extractDomainFromDescription($description)
    {
        // Mencari tautan di deskripsi HTML
        if (preg_match('/<a\s+href=["\']([^"\']+)["\']/', $description, $matches)) {
            $url = $matches[1];
            $domain = parse_url($url, PHP_URL_HOST);
            return $domain ? str_replace('www.', '', $domain) : null;
        }
        return null;
    }

    public function extractDomainFromTitle($title)
    {
        // Daftar mapping nama sumber ke domain (bisa ditambahkan sesuai kebutuhan)
        $sourceToDomain = [
            'Kompas.com' => 'kompas.com',
            'Kompas.id' => 'kompas.com',
            'Kontan' => 'kontan.co.id',
            'kontan.co.id' => 'kontan.co.id',
            'ANTARA News' => 'antaranews.com',
            'CNBC Indonesia' => 'cnbcindonesia.com',
            'CNN Indonesia' => 'cnnindonesia.com',
            'detikcom' => 'detik.com',
            'detikNews' => 'detik.com',
            'detikFinance' => 'detik.com',
            'Tempo.co' => 'tempo.co',
            'IDN Times' => 'idntimes.com',
            'IDN Times Bali' => 'idntimes.com',
            'Bloomberg Technoz' => 'bloomberg.com',
            'Suara.com' => 'suara.com',
            'investor.id' => 'investor.id',
            'Universitas Airlangga Official Website' => 'unair.ac.id',
            'Kementerian Agama' => 'kemenag.go.id',
            'BRIN - Badan Riset dan Inovasi Nasional' => 'brin.go.id',
            'Media Center Rohil' => 'mediacenter.rohilkab.go.id',
            'Media Center Rokan Hilir' => 'mediacenter.rohilkab.go.id',
            'Kementerian Pendidikan Tinggi, Sains, dan Teknologi' => 'kemdikbud.go.id',
            'Tuban Smart City' => 'tubankab.go.id',
            'NUSABALI.com' => 'nusabali.com',
            'BSINews' => 'bsi.ac.id',
            'GovInsider' => 'govinsider.asia',
            'WinPoin' => 'winpoin.com',
            'Grafika News' => 'grafikanews.com',
            'TopBusiness.id' => 'topbusiness.id',
            'Suara Aisyiyah' => 'suaraaisyiyah.com',
            "Suara 'Aisyiyah" => 'suaraaisyiyah.com',
            'Telkom Indonesia' => 'telkom.co.id',
            'JournalArta' => 'journalarta.com',
            'JurnalPost' => 'jurnalpost.com',
            'Tirto.id' => 'tirto.id',
            'MOST 1058' => 'most1058.com',
            'VOI.id' => 'voi.id',
            'InfoPublik' => 'infopublik.id',
            'PWMU.CO' => 'pwmu.co',
            'republika.co.id' => 'republika.co.id',
            'Website DJKN' => 'djkn.kemenkeu.go.id',
            'Pemerintah Kota Cirebon' => 'cirebonkota.go.id',
            'Balipuspanews.com' => 'balipuspanews.com',
            'Warta Ekonomi' => 'wartaekonomi.co.id',
            'RCTI+' => 'rctiplus.com',
            'Iconomics' => 'iconomics.co.id',
            'seputarkebumen.com' => 'seputarkebumen.com',
            'GoSumut.com' => 'gosumut.com',
            'KabarBursa.com' => 'kabarbursa.com',
            'Universitas Islam Indonesia' => 'uii.ac.id',
            'Jabarprov' => 'jabarprov.go.id',
            'Portal Resmi Kota Sukabumi' => 'sukabumikota.go.id',
            'INDODAX' => 'indodax.com',
            'Kupas Tuntas' => 'kupastuntas.com',
            'Marketeers' => 'marketeers.com',
            'Suara Pemerintah' => 'suara-pemerintah.go.id',
            'Universitas Sains dan Teknologi Komputer' => 'stekom.ac.id',
            'Universitas Nusa Mandiri' => 'unm.ac.id',
            'SinPo.id' => 'sinpo.id',
            'Pos Merdeka' => 'posmerdeka.com',
            'Lentera.co' => 'lentera.co',
            'ANTARA News Riau' => 'antaranews.com',
            'rmoljabar.id' => 'rmoljabar.id',
            'Pusat Informasi Pengawasan - Badan Pengawasan Keuangan dan Pembangunan' => 'pkp.go.id',
            'Suara Papua' => 'suarapapua.com',
            'Edisi - edisi.co.id' => 'edisi.co.id',
            'Universitas Nasional' => 'unas.ac.id',
            'parepos' => 'parepos.com',
            'SURYAKABAR.com' => 'suryakabar.com',
            'Kompasiana.com - Kompasiana.com' => 'kompasiana.com',
            'Berita Cilegon' => 'beritacilegon.com',
            'Universitas Negeri Malang' => 'um.ac.id',
            'MTsN 4 Batang Hari' => 'mtsn4batanghari.sch.id',
            'unand.ac.id' => 'unand.ac.id',
            'ANTARA News Megapolitan' => 'antaranews.com',
            'Rilis.id Lampung' => 'rilis.id',
            'Universitas Islam Negeri Kiai Haji Achmad Siddiq Jember' => 'uinkhas.ac.id',
            'Langgam.id' => 'langgam.id',
            'Kementerian Komunikasi dan Digital' => 'kemkominfo.go.id',
            'MUI - Majelis Ulama Indonesia' => 'mui.or.id',
            'Infobanknews' => 'infobanknews.com',
            'VIVA.co.id' => 'viva.co.id',
            'suarakalbar.co.id' => 'suarakalbar.co.id',
            'radarmukomuko.disway.id - Radar Mukomuko' => 'disway.id',
            'harian.disway.id - HARIAN DISWAY' => 'disway.id',
            'Kabar Nusantara' => 'kabarnusantara.com',
            'malang-post.com' => 'malang-post.com',
            'Industry.co.id' => 'industry.co.id',
            'klikmu' => 'klikmu.com',
            'suara usu' => 'suara.usu.ac.id',
            'MIN.CO.ID' => 'min.co.id',
            'MIX Marcomm' => 'mix-marcomm.com',
            'Majalah ICT' => 'majalahict.com',
            'achmadnurhidayat.id' => 'achmadnurhidayat.id',
            'Suara Surabaya' => 'suarasurabaya.net',
            'MetroTVNews.com' => 'metrotvnews.com',
            'KilasJatim.com' => 'kilasjatim.com',
            'Harian Jogja' => 'harianjogja.com',
            'paltv.disway.id - Disway' => 'disway.id',
            'BERNAS.id' => 'bernas.id',
            'suarapubliknews.net' => 'suarapubliknews.net',
            'Mnctrijaya.com' => 'mnctrijaya.com',
            'Reuters' => 'reuters.com',
            'Associated Press' => 'apnews.com',
            'The Hill' => 'thehill.com',
            'Fox News' => 'foxnews.com',
            'BBC' => 'bbc.com',
            'CNN' => 'cnn.com',
            'Business Insider' => 'businessinsider.com',
            'Fortune' => 'fortune.com',
            'USA TODAY' => 'usatoday.com',
            'CBS News' => 'cbsnews.com',
            'NewsNation' => 'newsnationnow.com',
            'NBC' => 'nbc.com',
            'ABC News' => 'abcnews.com',
        ];

        // Pisahkan judul dengan " - "
        $parts = explode(' - ', $title);
        if (count($parts) > 1) {
            $sourceName = trim(end($parts));
            // Cari di mapping secara exact match terlebih dahulu
            if (isset($sourceToDomain[$sourceName])) {
                return $sourceToDomain[$sourceName];
            }
            // Coba cari dengan partial match (case-insensitive)
            foreach ($sourceToDomain as $name => $domain) {
                if (stripos($sourceName, $name) !== false || stripos($name, $sourceName) !== false) {
                    return $domain;
                }
            }
        }
        return null;
    }

    private function extractDomain($url)
    {
        $domain = parse_url($url, PHP_URL_HOST);
        return $domain ? str_replace('www.', '', $domain) : '-';
    }

    public function saveNewsToDatabase($newsItems)
    {
        // Get or create category "Teknologi"
        $category = PostCategory::firstOrCreate(
            ['name' => 'Teknologi'],
            ['slug' => 'teknologi', 'description' => 'Berita Teknologi dan AI']
        );

        // Get admin user
        $adminUser = User::role('admin')->first();
        if (!$adminUser) {
            return 0;
        }

        $count = 0;

        foreach ($newsItems as $item) {
            // Check if post already exists by source link
            $existingPost = Posts::where('source', $item['link'])->first();
            if ($existingPost) {
                continue;
            }

            // Create slug
            $slug = Str::slug($item['title']);
            $originalSlug = $slug;
            $counter = 1;
            while (Posts::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            // Create post
            Posts::create([
                'title' => $item['title'],
                'slug' => $slug,
                'content' => $item['description'],
                'image' => null,
                'source' => $item['link'],
                'domain' => $item['domain'],
                'status' => 'active',
                'category_id' => $category->id,
                'created_by' => $adminUser->id,
                'published_at' => $item['published_at'],
                'counter' => 0,
                'tags' => isset($item['tags']) ? json_encode($item['tags']) : json_encode([$item['source'], 'Yahoo News']),
            ]);

            $count++;
        }

        return $count;
    }
}
