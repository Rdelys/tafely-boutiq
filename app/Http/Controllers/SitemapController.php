<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * sitemap.xml : pages publiques + vitrines des boutiques actives
     * qui ont au moins un produit visible. Mis en cache 1 heure.
     */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap_xml', 3600, function () {
            $pages = [
                ['loc' => route('home'), 'changefreq' => 'weekly', 'priority' => '1.0', 'lastmod' => null],
                ['loc' => route('aide'), 'changefreq' => 'monthly', 'priority' => '0.6', 'lastmod' => null],
                ['loc' => route('contact'), 'changefreq' => 'monthly', 'priority' => '0.5', 'lastmod' => null],
                ['loc' => route('confidentialite'), 'changefreq' => 'yearly', 'priority' => '0.3', 'lastmod' => null],
            ];

            $boutiques = User::query()
                ->where(fn ($q) => $q->where('suspendu', false)->orWhereNull('suspendu'))
                ->whereHas('produits', fn ($q) => $q->visibles())
                ->orderByDesc('updated_at')
                ->limit(5000)
                ->get(['id', 'pseudo', 'slug', 'updated_at']);

            foreach ($boutiques as $boutique) {
                $pages[] = [
                    'loc' => $boutique->lienBoutique(),
                    'changefreq' => 'daily',
                    'priority' => '0.7',
                    'lastmod' => $boutique->updated_at?->toAtomString(),
                ];
            }

            $sortie = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $sortie .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

            foreach ($pages as $page) {
                $sortie .= "  <url>\n";
                $sortie .= '    <loc>'.htmlspecialchars($page['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</loc>\n";
                if ($page['lastmod']) {
                    $sortie .= '    <lastmod>'.$page['lastmod']."</lastmod>\n";
                }
                $sortie .= '    <changefreq>'.$page['changefreq']."</changefreq>\n";
                $sortie .= '    <priority>'.$page['priority']."</priority>\n";
                $sortie .= "  </url>\n";
            }

            return $sortie.'</urlset>'."\n";
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * robots.txt dynamique (l'URL du sitemap suit APP_URL).
     */
    public function robots(): Response
    {
        $lignes = [
            'User-agent: *',
            'Allow: /',
            'Allow: /b/',
            'Disallow: /admin',
            'Disallow: /auth',
            'Disallow: /dashboard',
            'Disallow: /produits',
            'Disallow: /commandes',
            'Disallow: /commande/',
            'Disallow: /ventes',
            'Disallow: /notifications',
            'Disallow: /support',
            'Disallow: /abonnement',
            'Disallow: /boutique',
            'Disallow: /parametres',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lignes)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}