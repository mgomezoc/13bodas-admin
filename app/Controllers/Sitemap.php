<?php

namespace App\Controllers;

class Sitemap extends BaseController
{
    /**
     * Genera el sitemap.xml dinámico
     */
    public function index()
    {
        $this->response->setContentType('application/xml');
        
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        // Homepage
        $sitemap .= '<url>';
        $sitemap .= '<loc>' . base_url() . '</loc>';
        $sitemap .= '<lastmod>' . date('Y-m-d') . '</lastmod>';
        $sitemap .= '<changefreq>weekly</changefreq>';
        $sitemap .= '<priority>1.0</priority>';
        $sitemap .= '</url>';
        
        // Páginas estáticas
        $pages = ['terminos', 'privacidad', 'gracias'];
        foreach ($pages as $page) {
            $sitemap .= '<url>';
            $sitemap .= '<loc>' . base_url($page) . '</loc>';
            $sitemap .= '<changefreq>monthly</changefreq>';
            $sitemap .= '<priority>0.5</priority>';
            $sitemap .= '</url>';
        }
        
        // MagicCam subdomain
        $sitemap .= '<url>';
        $sitemap .= '<loc>https://magiccam.13bodas.com</loc>';
        $sitemap .= '<changefreq>weekly</changefreq>';
        $sitemap .= '<priority>0.9</priority>';
        $sitemap .= '</url>';
        
        // Eventos públicos (si existen)
        try {
            $eventModel = model('EventModel');
            $events = $eventModel->where('is_public', 1)->findAll();
            
            foreach ($events as $event) {
                $sitemap .= '<url>';
                $sitemap .= '<loc>' . base_url('i/' . $event['slug']) . '</loc>';
                $sitemap .= '<lastmod>' . date('Y-m-d', strtotime($event['updated_at'])) . '</lastmod>';
                $sitemap .= '<changefreq>weekly</changefreq>';
                $sitemap .= '<priority>0.8</priority>';
                $sitemap .= '</url>';
            }
        } catch (\Exception $e) {
            // Si no hay eventos o hay error en la BD, continuar sin eventos
            log_message('error', 'Error generando sitemap eventos: ' . $e->getMessage());
        }
        
        $sitemap .= '</urlset>';
        
        return $this->response->setBody($sitemap);
    }
}
