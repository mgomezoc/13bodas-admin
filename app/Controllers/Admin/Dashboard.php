<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $data = [
            'pageTitle' => 'Dashboard'
        ];
        
        return view('admin/dashboard', $data);
    }
}
