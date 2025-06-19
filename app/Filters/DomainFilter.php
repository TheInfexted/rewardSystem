<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class DomainFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $allowedDomain = 'localhost'; 
        $currentDomain = $_SERVER['HTTP_HOST'] ?? '';

        if (stripos($currentDomain, $allowedDomain) === false) {
            // Show maintenance page if domain doesn't match
            echo view('maintenance');
            exit;
        }

        return; // allow request to continue
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing after
    }
}
