<?php

namespace App\Controllers;

class General extends BaseController
{
    /*
    * Public
    */

    public function index_login()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/admin/dashboard');
        }

        $data = [
            'title' => 'Login'
        ];

        echo view('auth/login', $data);
    }

    /*
    * End Public
    */
}
