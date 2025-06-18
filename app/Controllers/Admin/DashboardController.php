<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LandingPageModel;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    protected $landingPageModel;
    protected $userModel;

    public function __construct()
    {
        $this->landingPageModel = new LandingPageModel();
        $this->userModel = new UserModel();
    }

    /**
     * Display admin dashboard
     */
    public function index()
    {
        log_message('debug', 'Current locale: ' . service('request')->getLocale());

        // Get statistics
        $stats = [
            'total_pages' => $this->landingPageModel->countAll(),
            'active_pages' => $this->landingPageModel->where('is_active', 1)->countAllResults(),
            'total_users' => $this->userModel->countAll(),
            'last_update' => $this->getLastUpdate(),
            'wheel_items' => 0,
            'total_spins' => 0,
            'total_wins' => 0,
            'today_spins' => 0
        ];
        
        // Add wheel stats if available
        if (class_exists('\App\Models\WheelItemsModel')) {
            $wheelModel = new \App\Models\WheelItemsModel();
            $stats['wheel_items'] = $wheelModel->where('is_active', 1)->countAllResults();
        }
        
        // Add spin stats if available
        if (class_exists('\App\Models\SpinHistoryModel')) {
            $spinModel = new \App\Models\SpinHistoryModel();
            $spinStats = $spinModel->getStatistics();
            $stats['total_spins'] = $spinStats['total_spins'] ?? 0;
            $stats['total_wins'] = $spinStats['total_wins'] ?? 0;
            $stats['today_spins'] = $spinStats['today_spins'] ?? 0;
        }

        // Get recent landing page versions
        $recentPages = $this->landingPageModel->orderBy('updated_at', 'DESC')->limit(5)->findAll();

        $data = [
            'title' => t('Admin.dashboard.title'),
            'stats' => $stats,
            'recentPages' => $recentPages
        ];

        return view('admin/dashboard', $data);
    }

    /**
     * Get last update timestamp
     */
    private function getLastUpdate()
    {
        $lastPage = $this->landingPageModel->orderBy('updated_at', 'DESC')->first();
        
        if ($lastPage && !empty($lastPage['updated_at'])) {
            return date('M d, Y h:i A', strtotime($lastPage['updated_at']));
        }
        
        return 'No updates yet';
    }
}