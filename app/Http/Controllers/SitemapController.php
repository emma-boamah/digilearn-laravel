<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate a dynamic XML sitemap for public pages.
     */
    public function index(): Response
    {
        $urls = [
            [
                'loc'        => url('/'),
                'lastmod'    => now()->toW3cString(),
                'changefreq' => 'daily',
                'priority'   => '1.0',
            ],
            [
                'loc'        => url('/about'),
                'lastmod'    => now()->toW3cString(),
                'changefreq' => 'monthly',
                'priority'   => '0.8',
            ],
            [
                'loc'        => url('/pricing'),
                'lastmod'    => now()->toW3cString(),
                'changefreq' => 'weekly',
                'priority'   => '0.9',
            ],
            [
                'loc'        => url('/contact'),
                'lastmod'    => now()->toW3cString(),
                'changefreq' => 'monthly',
                'priority'   => '0.7',
            ],
            [
                'loc'        => url('/login'),
                'lastmod'    => now()->toW3cString(),
                'changefreq' => 'yearly',
                'priority'   => '0.3',
            ],
            [
                'loc'        => url('/signup'),
                'lastmod'    => now()->toW3cString(),
                'changefreq' => 'yearly',
                'priority'   => '0.3',
            ],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$url['loc']}</loc>\n";
            $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
