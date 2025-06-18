<?php

use CodeIgniter\Router\RouteCollection;

//Translation routes
$routes->get('translate/(:segment)', 'TranslateController::index/$1');
$routes->get('translate', 'TranslateController::getCurrentLanguage');

$routes->get('device/check', 'General::checkDevice');

// Public landing page routes
$routes->get('/', 'LandingController::index');
$routes->post('spin', 'LandingController::spin');
$routes->post('claim-bonus', 'LandingController::claimBonus');
$routes->post('store-winner', 'LandingController::storeWinner'); 

// NEW: Reward System routes
$routes->group('reward', function($routes) {
    $routes->get('/', 'RewardController::index');
    $routes->post('auto-register', 'RewardController::autoRegister');
    $routes->post('auto-login', 'RewardController::autoLogin');
    $routes->post('login', 'RewardController::login');
    $routes->post('claim', 'RewardController::claim');
});

// Authentication routes
$routes->get('login', 'General::index_login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');
$routes->get('register', 'AuthController::register');
$routes->post('register', 'AuthController::attemptRegister');

// Admin routes group (protected by auth filter)
$routes->group('admin', ['filter' => 'auth'], function($routes) {
    // Dashboard
    $routes->get('/', 'Admin\DashboardController::index');
    $routes->get('dashboard', 'Admin\DashboardController::index');
    
    // Landing Page Builder routes
    $routes->group('landing-page', function($routes) {
        $routes->get('/', 'Admin\LandingPageController::index');
        $routes->post('save', 'Admin\LandingPageController::save');
        $routes->get('preview', 'Admin\LandingPageController::preview');
        $routes->post('upload/image', 'Admin\UploadController::image');
        $routes->post('upload/remove', 'Admin\UploadController::remove');
        $routes->get('edit/(:num)', 'Admin\LandingPageController::edit/$1');
        $routes->post('save-music', 'Admin\LandingPageController::saveMusic');
        $routes->post('delete-music', 'Admin\LandingPageController::deleteMusic');
        $routes->post('save-spin-sound', 'Admin\LandingPageController::saveSpinSound');
        $routes->post('delete-spin-sound', 'Admin\LandingPageController::deleteSpinSound');
        $routes->post('save-win-sound', 'Admin\LandingPageController::saveWinSound');
        $routes->post('delete-win-sound', 'Admin\LandingPageController::deleteWinSound');
    });
    
    // Media Library routes
    $routes->group('media', function($routes) {
        $routes->get('/', 'Admin\MediaController::index');
        $routes->post('upload', 'Admin\MediaController::upload');
        $routes->get('delete/(:any)', 'Admin\MediaController::delete/$1');
    });

    
    // Wheel management
    $routes->group('wheel', function($routes) {
        $routes->get('/', 'Admin\WheelController::index');
        $routes->get('add', 'Admin\WheelController::add');
        $routes->post('add', 'Admin\WheelController::add');
        $routes->get('edit/(:num)', 'Admin\WheelController::edit/$1');
        $routes->post('edit/(:num)', 'Admin\WheelController::edit/$1');
        $routes->get('delete/(:num)', 'Admin\WheelController::delete/$1');
        $routes->get('check-rates', 'Admin\WheelController::checkRates');
    });
    
    // Bonus Claims Management
    $routes->group('bonus', function($routes) {
        $routes->get('/', 'Admin\BonusController::index');
        $routes->get('view/(:num)', 'Admin\BonusController::view/$1');
        $routes->get('export', 'Admin\BonusController::export');
        $routes->get('stats', 'Admin\BonusController::getStats');
        $routes->post('settings', 'Admin\BonusController::updateSettings');
        $routes->post('status/(:num)', 'Admin\BonusController::updateStatus/$1');
    });
    
    // Reports and Analytics
    $routes->group('reports', function($routes) {
        $routes->get('/', 'Admin\ReportsController::index');
        $routes->get('view/(:num)', 'Admin\ReportsController::view/$1');
        $routes->get('export', 'Admin\ReportsController::export');
        $routes->get('analytics', 'Admin\ReportsController::analytics');
    });
    
    // System Settings
    $routes->group('settings', function($routes) {
        $routes->get('/', 'Admin\SettingsController::index');
        $routes->post('profile', 'Admin\SettingsController::updateProfile');
        $routes->post('password', 'Admin\SettingsController::updatePassword');
        $routes->post('system', 'Admin\SettingsController::updateSystem');
    });
});

// API routes for AJAX calls
$routes->get('api/recent-wins', 'LandingController::getRecentWins');
$routes->get('api/user-stats', 'LandingController::getUserStats');