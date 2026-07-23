<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Posts;
use App\Models\PostCategory;
use App\Models\User;

class NewsService
{
    public function fetchFromYahooAiRss($date = null)
    {
        $rssSources = [
            // 🇮🇩 Indonesia (tech/finance - accessible) - HARUS /tech/ path only
            'https://www.cnbcindonesia.com/tech/rss'        => ['name' => 'CNBC Indonesia Tech',   'domain' => 'cnbcindonesia.com'],
            'https://inet.detik.com/rss'                   => ['name' => 'Detikinet',             'domain' => 'detik.com'],
            // 🌍 English Tech - ONLY AI/Tech focused
            'https://venturebeat.com/category/ai/feed/'   => ['name' => 'VentureBeat AI',       'domain' => 'venturebeat.com'],
            'https://search.cnbc.com/rs/search/combinedcms/view.xml?partnerId=wrss01&id=10000664'
                                                                    => ['name' => 'CNBC AI',             'domain' => 'cnbc.com'],
        ];

        // Positive: HARUS ada minimal 1 dari ini
        $keywords = [
            // AI & Tech
            'ai', 'artificial intelligence', 'machine learning', 'deep learning',
            'neural network', 'chatgpt', 'llm', 'generative ai', 'large language model',
            'openai', 'gemini', 'claude', 'anthropic', 'deepseek', 'mistral', 'llama',
            'microsoft', 'google', 'apple', 'meta', 'nvidia', 'amd', 'intel', 'qualcomm',
            'samsung', 'xiaomi', 'oppo', 'vivo', 'huawei', 'tesla', 'amazon',
            'robot', 'automation', 'semiconductor', 'chip', 'cybersecurity', 'data breach',
            'cloud computing', 'software', 'hardware', 'smartphone', 'laptop', 'gadget', 'iot', '5g', '6g',
            'blockchain', 'crypto', 'bitcoin', 'ethereum', 'metaverse', 'vr', 'ar', 'xr', 'quantum',
            'startup', 'silicon valley', 'venture capital', 'ipo', 'funding',
            'digital', 'app', 'application', 'platform', 'browser', 'os',
            'streaming', 'netflix', 'youtube', 'social media', 'instagram', 'tiktok',
            'gaming', 'esport', 'console', 'playstation', 'xbox', 'nintendo',
            // Indonesian Tech terms
            'teknologi', 'teknologi informasi', 'startup', 'digital', 'kecerdasan buatan',
            'smartphone', 'gadget', 'aplikasi', 'software', 'hardware', 'siber', 'cyber',
            'internet', 'jaringan', 'data', 'cloud', 'server', 'database',
            'otomatis', 'robot', 'otomasi', 'vaksin', 'digitalisasi',
        ];

        // Negative: SKIP jika ada di judul atau deskripsi
        $negativeKeywords = [
            // Sports
            'bola', 'sepak bola', 'football', 'soccer', 'piala', ' AFF', 'liga', 'premier league',
            'champions league', 'europa league', 'world cup', 'piala dunia', 'timnas', 'nasional',
            'juara', 'gol', 'gol', 'menang', 'kalah', 'skor', 'pertandingan',
            'basket', 'basketball', 'nba', 'voli', 'badminton', 'tennis',
            // Politics
            'pilpres', 'pileg', 'pemilu', 'presiden', 'menteri', 'governor', 'walikota',
            'partai', 'politisi', 'parlemen', 'senayan', 'dpr', 'mk', 'mk',
            'trump', 'biden', 'putin', 'xjokowi', 'presiden', 'pemerintah',
            'election', 'voting', 'voted', 'vote',
            // Entertainment (non-tech)
            'film', 'movie', 'sinema', 'bioskop', 'drama', 'sinetron',
            'musik', 'music', 'konser', 'lagu', 'artis', 'selebriti', 'celebrity',
            'gossip', 'artis', 'hot', 'scandal',
            // Crime/Hard news
            ' убийство', 'murder', 'rape', 'kriminal', 'polisi', 'kejaksaan',
            'penemuan mayat', 'penyelundupan', 'narkoba', 'OTT', 'korupsi',
            // Health non-tech
            'rumah sakit', 'pasien', 'dokter', 'operasi', 'sembuh', 'meninggal',
            'weather', 'cuaca', 'hujan', 'banjir', 'gempa', 'tsunami',
            // Finance non-tech (skip yang bukan tech/finance)
            'kurs rupiah', 'rupiah', 'dollar', '外汇',
        ];

        $tagMap = [
            'artificial intelligence' => 'Artificial Intelligence',
            'machine learning'        => 'Machine Learning',
            'deep learning'           => 'Deep Learning',
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
            'smartphone'              => 'Smartphone',
            'gadget'                  => 'Gadget',
            'blockchain'              => 'Blockchain',
            'crypto'                  => 'Crypto',
            'metaverse'               => 'Metaverse',
            'telemedicine'            => 'Telemedicine',
            'digital health'          => 'Digital Health',
            'healthtech'              => 'Health Tech',
            'biotech'                 => 'Biotech',
            'pharmacy'                => 'Pharmacy',
            ' ai '                    => 'AI',
            'tech'                    => 'Tech',
        ];

        $newsItems = [];
        $seenLinks = [];

        foreach ($rssSources as $rssUrl => $sourceInfo) {
            try {
                $response = Http::timeout(60)->get($rssUrl);
            } catch (\Exception $e) {
                Log::warning('NewsService: gagal fetch RSS', ['url' => $rssUrl, 'error' => $e->getMessage()]);
                continue;
            }

            if (!$response->successful() || empty($response->body())) {
                continue;
            }

            $body = $response->body();
            $xml = @simplexml_load_string($body);

            if (!$xml) {
                continue;
            }

            // Handle RSS 2.0 format: <channel><item>
            $items = [];
            if (isset($xml->channel) && isset($xml->channel->item)) {
                $items = $xml->channel->item;
            }
            // Handle Atom format: <entry>
            elseif (isset($xml->entry)) {
                $items = $xml->entry;
            }
            // Handle standalone <item> (no channel wrapper)
            elseif (isset($xml->item)) {
                $items = $xml->item;
            }

            if (empty($items)) {
                continue;
            }

            foreach ($items as $item) {
                $title       = isset($item->title) ? (string)$item->title : '';
                $link        = isset($item->link) ? (string)$item->link : '';
                // Atom feeds use <link href="..."> instead of <link>text</link>
                if (empty($link) && isset($item->link['href'])) {
                    $link = (string)$item->link['href'];
                }
                $description = isset($item->description) ? strip_tags((string)$item->description) : '';
                $pubDate     = isset($item->pubDate) ? (string)$item->pubDate : (isset($item->published) ? (string)$item->published : '');

                if (empty($title) || empty($link)) {
                    continue;
                }

                $itemDate    = $pubDate ? date('Y-m-d', strtotime($pubDate)) : null;
                if ($date && $itemDate && $itemDate !== $date) {
                    continue;
                }

                if (in_array($link, $seenLinks)) {
                    continue;
                }

                $haystack  = strtolower(' ' . $title . ' ' . $description . ' ');

                // SKIP jika ada negative keyword di judul (lebih ketat)
                $hasNegative = false;
                foreach ($negativeKeywords as $neg) {
                    if (strpos(strtolower($title), $neg) !== false) {
                        $hasNegative = true;
                        break;
                    }
                }
                if ($hasNegative) {
                    continue;
                }

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

                $articleTags = [$sourceInfo['name']];
                foreach ($tagMap as $keyword => $label) {
                    if (strpos($haystack, $keyword) !== false && !in_array($label, $articleTags)) {
                        $articleTags[] = $label;
                    }
                }
                $articleTags = array_slice($articleTags, 0, 5);

                $newsItems[] = [
                    'title'       => $title,
                    'link'        => $link,
                    'description' => $description,
                    'published_at' => $pubDate ? date('Y-m-d H:i:s', strtotime($pubDate)) : now()->format('Y-m-d H:i:s'),
                    'source'      => $sourceInfo['name'],
                    'domain'      => $sourceInfo['domain'],
                    'tags'        => $articleTags,
                ];
            }
        }

        return $newsItems;
    }

    public function extractDomainFromDescription($description)
    {
        if (preg_match('/<a\s+href=["\']([^"\']+)["\']/', $description, $matches)) {
            $url    = $matches[1];
            $domain = parse_url($url, PHP_URL_HOST);
            return $domain ? str_replace('www.', '', $domain) : null;
        }
        return null;
    }

    public function extractDomainFromTitle($title)
    {
        $sourceToDomain = [
            'Kompas.com' => 'kompas.com', 'Kompas.id' => 'kompas.com',
            'Kontan' => 'kontan.co.id', 'kontan.co.id' => 'kontan.co.id',
            'ANTARA News' => 'antaranews.com', 'CNBC Indonesia' => 'cnbcindonesia.com',
            'CNN Indonesia' => 'cnnindonesia.com', 'detikcom' => 'detik.com',
            'detikNews' => 'detik.com', 'detikFinance' => 'detik.com',
            'Tempo.co' => 'tempo.co', 'IDN Times' => 'idntimes.com',
            'IDN Times Bali' => 'idntimes.com', 'Bloomberg Technoz' => 'bloomberg.com',
            'Suara.com' => 'suara.com', 'investor.id' => 'investor.id',
            'Universitas Airlangga Official Website' => 'unair.ac.id',
            'Kementerian Agama' => 'kemenag.go.id',
            'BRIN - Badan Riset dan Inovasi Nasional' => 'brin.go.id',
            'Media Center Rohil' => 'mediacenter.rohilkab.go.id',
            'Media Center Rokan Hilir' => 'mediacenter.rohilkab.go.id',
            'Kementerian Pendidikan Tinggi, Sains, dan Teknologi' => 'kemdikbud.go.id',
            'Tuban Smart City' => 'tubankab.go.id', 'NUSABALI.com' => 'nusabali.com',
            'BSINews' => 'bsi.ac.id', 'GovInsider' => 'govinsider.asia',
            'WinPoin' => 'winpoin.com', 'Grafika News' => 'grafikanews.com',
            'TopBusiness.id' => 'topbusiness.id', 'Suara Aisyiyah' => 'suaraaisyiyah.com',
            "Suara 'Aisyiyah" => 'suaraaisyiyah.com', 'Telkom Indonesia' => 'telkom.co.id',
            'JournalArta' => 'journalarta.com', 'JurnalPost' => 'jurnalpost.com',
            'Tirto.id' => 'tirto.id', 'MOST 1058' => 'most1058.com', 'VOI.id' => 'voi.id',
            'InfoPublik' => 'infopublik.id', 'PWMU.CO' => 'pwmu.co',
            'republika.co.id' => 'republika.co.id', 'Website DJKN' => 'djkn.kemenkeu.go.id',
            'Pemerintah Kota Cirebon' => 'cirebonkota.go.id', 'Balipuspanews.com' => 'balipuspanews.com',
            'Warta Ekonomi' => 'wartaekonomi.co.id', 'RCTI+' => 'rctiplus.com',
            'Iconomics' => 'iconomics.co.id', 'seputarkebumen.com' => 'seputarkebumen.com',
            'GoSumut.com' => 'gosumut.com', 'KabarBursa.com' => 'kabarbursa.com',
            'Universitas Islam Indonesia' => 'uii.ac.id', 'Jabarprov' => 'jabarprov.go.id',
            'Portal Resmi Kota Sukabumi' => 'sukabumikota.go.id', 'INDODAX' => 'indodax.com',
            'Kupas Tuntas' => 'kupastuntas.com', 'Marketeers' => 'marketeers.com',
            'Suara Pemerintah' => 'suara-pemerintah.go.id',
            'Universitas Sains dan Teknologi Komputer' => 'stekom.ac.id',
            'Universitas Nusa Mandiri' => 'unm.ac.id', 'SinPo.id' => 'sinpo.id',
            'Pos Merdeka' => 'posmerdeka.com', 'Lentera.co' => 'lentera.co',
            'ANTARA News Riau' => 'antaranews.com', 'rmoljabar.id' => 'rmoljabar.id',
            'Pusat Informasi Pengawasan - Badan Pengawasan Keuangan dan Pembangunan' => 'pkp.go.id',
            'Suara Papua' => 'suarapapua.com', 'Edisi - edisi.co.id' => 'edisi.co.id',
            'Universitas Nasional' => 'unas.ac.id', 'parepos' => 'parepos.com',
            'SURYAKABAR.com' => 'suryakabar.com',
            'Kompasiana.com - Kompasiana.com' => 'kompasiana.com',
            'Berita Cilegon' => 'beritacilegon.com', 'Universitas Negeri Malang' => 'um.ac.id',
            'MTsN 4 Batang Hari' => 'mtsn4batanghari.sch.id', 'unand.ac.id' => 'unand.ac.id',
            'ANTARA News Megapolitan' => 'antaranews.com', 'Rilis.id Lampung' => 'rilis.id',
            'Universitas Islam Negeri Kiai Haji Achmad Siddiq Jember' => 'uinkhas.ac.id',
            'Langgam.id' => 'langgam.id', 'Kementerian Komunikasi dan Digital' => 'kemkominfo.go.id',
            'MUI - Majelis Ulama Indonesia' => 'mui.or.id', 'Infobanknews' => 'infobanknews.com',
            'VIVA.co.id' => 'viva.co.id', 'suarakalbar.co.id' => 'suarakalbar.co.id',
            'radarmukomuko.disway.id - Radar Mukomuko' => 'disway.id',
            'harian.disway.id - HARIAN DISWAY' => 'disway.id',
            'Kabar Nusantara' => 'kabarnusantara.com', 'malang-post.com' => 'malang-post.com',
            'Industry.co.id' => 'industry.co.id', 'klikmu' => 'klikmu.com',
            'suara usu' => 'suara.usu.ac.id', 'MIN.CO.ID' => 'min.co.id',
            'MIX Marcomm' => 'mix-marcomm.com', 'Majalah ICT' => 'majalahict.com',
            'achmadnurhidayat.id' => 'achmadnurhidayat.id', 'Suara Surabaya' => 'suarasurabaya.net',
            'MetroTVNews.com' => 'metrotvnews.com', 'KilasJatim.com' => 'kilasjatim.com',
            'Harian Jogja' => 'harianjogja.com', 'paltv.disway.id - Disway' => 'disway.id',
            'BERNAS.id' => 'bernas.id', 'suarapubliknews.net' => 'suarapubliknews.net',
            'Mnctrijaya.com' => 'mnctrijaya.com', 'Reuters' => 'reuters.com',
            'Associated Press' => 'apnews.com', 'The Hill' => 'thehill.com',
            'Fox News' => 'foxnews.com', 'BBC' => 'bbc.com', 'CNN' => 'cnn.com',
            'Business Insider' => 'businessinsider.com', 'Fortune' => 'fortune.com',
            'USA TODAY' => 'usatoday.com', 'CBS News' => 'cbsnews.com',
            'NewsNation' => 'newsnationnow.com', 'NBC' => 'nbc.com', 'ABC News' => 'abcnews.com',
        ];

        $parts = explode(' - ', $title);
        if (count($parts) > 1) {
            $sourceName = trim(end($parts));
            if (isset($sourceToDomain[$sourceName])) {
                return $sourceToDomain[$sourceName];
            }
            foreach ($sourceToDomain as $name => $domain) {
                if (stripos($sourceName, $name) !== false || stripos($name, $sourceName) !== false) {
                    return $domain;
                }
            }
        }
        return null;
    }

    public function saveNewsToDatabase($newsItems)
    {
        $category = PostCategory::firstOrCreate(
            ['name' => 'Teknologi'],
            ['slug' => 'teknologi', 'description' => 'Berita Teknologi dan AI']
        );

        $adminUser = User::role('admin')->first();
        if (!$adminUser) {
            return 0;
        }

        $count = 0;

        foreach ($newsItems as $item) {
            if (Posts::where('source', $item['link'])->exists()) {
                continue;
            }

            $slug = Str::slug($item['title']);
            $originalSlug = $slug;
            $counter = 1;
            while (Posts::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            Posts::create([
                'title'        => $item['title'],
                'slug'         => $slug,
                'content'      => $item['description'],
                'image'        => null,
                'source'       => $item['link'],
                'domain'       => $item['domain'],
                'status'       => 'active',
                'category_id'  => $category->id,
                'created_by'   => $adminUser->id,
                'published_at' => $item['published_at'],
                'counter'      => 0,
                'tags'         => $item['tags'],
            ]);

            $count++;
        }

        return $count;
    }
}
