<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class DomainFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $allowedDomains = [
            'rewardcheckin.kopisugar.cc',  // Main fortune wheel domain
            'clientzone.kopisugar.cc',     // Client reward domain
        ];

        $currentDomain = $_SERVER['HTTP_HOST'] ?? '';
        
        // Remove port number if present (for localhost testing)
        $currentDomainClean = explode(':', $currentDomain)[0];
        
        // Check if current domain is in allowed list
        $isAllowed = false;
        foreach ($allowedDomains as $allowedDomain) {
            if (stripos($currentDomain, $allowedDomain) !== false || 
                stripos($currentDomainClean, $allowedDomain) !== false) {
                $isAllowed = true;
                break;
            }
        }

        // If domain is not allowed, show maintenance page
        if (!$isAllowed) {
            // Log the blocked domain for debugging
            log_message('info', 'Domain access blocked: ' . $currentDomain);
            
            // Show maintenance page
            echo view('maintenance');
            exit;
        }

        // Log successful domain access (optional, for debugging)
        log_message('debug', 'Domain access allowed: ' . $currentDomain);

        return; // Allow request to continue
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing after
    }
}