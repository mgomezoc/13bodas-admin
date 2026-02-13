<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\ProjectShowcaseService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Projects extends BaseController
{
    public function __construct(private ProjectShowcaseService $projectShowcaseService = new ProjectShowcaseService())
    {
    }

    public function index(): string
    {
        return view('pages/projects/index', [
            'projects' => $this->projectShowcaseService->getProjects(),
        ]);
    }

    public function show(string $slug): string
    {
        $project = $this->projectShowcaseService->getProjectBySlug($slug);

        if ($project === null) {
            throw PageNotFoundException::forPageNotFound('Proyecto no encontrado');
        }

        return view('pages/projects/show', [
            'project' => $project,
        ]);
    }
}
