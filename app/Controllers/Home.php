<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\StructuredDataBuilder;
use CodeIgniter\HTTP\ResponseInterface;

class Home extends BaseController
{
    public function __construct(private StructuredDataBuilder $structuredDataBuilder = new StructuredDataBuilder())
    {
    }

    /**
     * Página principal
     */
    public function index(): string
    {
        $schemas = $this->structuredDataBuilder->homeSchemas(base_url('/'));

        return view('pages/home', [
            'homeStructuredDataScripts' => $this->structuredDataBuilder->renderScripts($schemas),
        ]);
    }

    /**
     * Página de Términos y Condiciones
     */
    public function terminos(): string
    {
        return view('pages/terminos');
    }

    /**
     * Página de Aviso de Privacidad
     */
    public function privacidad(): string
    {
        return view('pages/privacidad');
    }

    /**
     * Página de agradecimiento (después del envío del formulario)
     */
    public function gracias(): string
    {
        return view('pages/gracias');
    }


    public function llms(): ResponseInterface
    {
        return $this->serveTextFile(FCPATH . 'llms.txt');
    }

    public function llmsFull(): ResponseInterface
    {
        return $this->serveTextFile(FCPATH . 'llms-full.txt');
    }

    private function serveTextFile(string $path): ResponseInterface
    {
        if (!is_file($path)) {
            return $this->response->setStatusCode(404);
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return $this->response->setStatusCode(500);
        }

        return $this->response
            ->setContentType('text/plain; charset=UTF-8')
            ->setBody($content);
    }

}
