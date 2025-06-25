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
$routes->get('spin-status', 'LandingController::getSpinStatus');

// API Routes
$routes->get('api/wheel-data', 'CustomerController::getWheelData');
$routes->get('api/recent-wins', 'LandingController::getRecentWins');
$routes->get('api/user-stats', 'LandingController::getUserStats');
$routes->post('api/reset-spins', 'LandingController::resetSpins');

//Reward System routes
$routes->group('reward', function($routes) {
    $routes->get('/', 'RewardController::index');
    $routes->get('test', 'RewardController::test');
    $routes->post('auto-register', 'RewardController::autoRegister');
    $routes->post('login', 'RewardController::login');
    $routes->get('logout', 'RewardController::logout');
    $routes->post('logout', 'RewardController::logout');
    $routes->post('claim-reward', 'RewardController::claimReward'); 
});

// Customer routes
$routes->group('customer', function($routes) {
    $routes->get('dashboard', 'CustomerController::dashboard');
    $routes->post('checkin', 'CustomerController::checkin');
    $routes->post('updateDashboardColor', 'CustomerController::updateDashboardColor');
    $routes->post('update-background', 'CustomerController::updateBackground');
    $routes->post('remove-background-image', 'CustomerController::removeBackgroundImage');
    $routes->get('wheel-data', 'CustomerController::getWheelData');
    $routes->get('ads', 'CustomerController::getAds');
    $routes->post('get-current-password', 'CustomerController::getCurrentPassword');
    $routes->post('change-password', 'CustomerController::changePassword');
    $routes->get('getTheme', 'CustomerController::getTheme');
});

// Authentication routes
$routes->get('login', 'General::index_login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');
$routes->get('register', 'AuthController::register');
$routes->post('register', 'AuthController::attemptRegister');

//User Dashboard routes 
$routes->group('user', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'UserController::dashboard');
    $routes->post('checkin', 'UserController::checkin');
    $routes->post('update-background', 'UserController::updateBackground');
    $routes->get('wheel-data', 'UserController::getWheelData');
});

// Admin routes group
$routes->group('admin', ['filter' => 'auth'], function($routes) {
    // Dashboard
    $routes->get('/', 'Admin\DashboardController::index');
    $routes->get('dashboard', 'Admin\DashboardController::index');
    
    // Landing Page Builder routes
    $routes->group('landing-page', function($routes) {
        $routes->get('/', 'Admin\LandingPageController::index');
        $routes->post('save', 'Admin\LandingPageController::save');
        $routes->get('preview', 'Admin\LandingPageController::preview');
        $routes->get('edit/(:num)', 'Admin\LandingPageController::edit/$1');
        
        // Upload routes
        $routes->group('upload', function($routes) {
            $routes->post('image', 'Admin\UploadController::image');
            $routes->post('remove', 'Admin\UploadController::remove');
        });
        
        // Music routes
        $routes->post('save-music', 'Admin\LandingPageController::saveMusic');
        $routes->post('delete-music', 'Admin\LandingPageController::deleteMusic');
        
        // Sound routes
        $routes->post('save-spin-sound', 'Admin\LandingPageController::saveSpinSound');
        $routes->post('delete-spin-sound', 'Admin\LandingPageController::deleteSpinSound');
        $routes->post('save-win-sound', 'Admin\LandingPageController::saveWinSound');
        $routes->post('delete-win-sound', 'Admin\LandingPageController::deleteWinSound');
    });
    
    // Media routes
    $routes->group('media', function($routes) {
        $routes->get('/', 'Admin\MediaController::index');
        $routes->post('upload', 'Admin\MediaController::upload');
        $routes->get('delete/(:segment)', 'Admin\MediaController::delete/$1');
    });
    
    // Wheel management routes
    $routes->group('wheel', function($routes) {
        $routes->get('/', 'Admin\WheelController::index');
        $routes->post('add', 'Admin\WheelController::add');
        $routes->get('edit/(:num)', 'Admin\WheelController::edit/$1');
        $routes->post('edit/(:num)', 'Admin\WheelController::edit/$1');
        $routes->get('delete/(:num)', 'Admin\WheelController::delete/$1');
    });
    
    // Bonus management routes
    $routes->group('bonus', function($routes) {
        $routes->get('/', 'Admin\BonusController::index');
        $routes->post('settings', 'Admin\BonusController::updateSettings');
        $routes->post('update-settings', 'Admin\BonusController::updateSettings'); 
        $routes->post('status/(:num)', 'Admin\BonusController::updateStatus/$1');
        $routes->post('update-status/(:num)', 'Admin\BonusController::updateStatus/$1'); 
        $routes->get('stats', 'Admin\BonusController::stats'); 
        $routes->get('statistics', 'Admin\BonusController::stats'); 
        $routes->get('ip-analysis', 'Admin\BonusController::ipAnalysis');
        $routes->get('view/(:num)', 'Admin\BonusController::view/$1');
        $routes->get('details/(:num)', 'Admin\BonusController::view/$1'); 
        $routes->get('export', 'Admin\BonusController::export');
        $routes->post('export', 'Admin\BonusController::export'); 
    });

    // Reports routes
    $routes->group('reports', function($routes) {
        $routes->get('/', 'Admin\ReportsController::index');                  
        $routes->get('view/(:num)', 'Admin\ReportsController::view/$1');       
        $routes->get('export', 'Admin\ReportsController::export');             
        $routes->get('analytics', 'Admin\ReportsController::analytics');       
    });

    // Reward System routes
    $routes->get('reward-system', 'Admin\RewardSystemController::index');
    $routes->match(['get', 'post'], 'reward-system/add', 'Admin\RewardSystemController::add');
    $routes->match(['get', 'post'], 'reward-system/edit/(:num)', 'Admin\RewardSystemController::edit/$1');
    $routes->get('reward-system/delete/(:num)', 'Admin\RewardSystemController::delete/$1');
    $routes->post('reward-system/update-order', 'Admin\RewardSystemController::updateOrder');
    
    // Settings routes
    $routes->group('settings', function($routes) {
        $routes->get('/', 'Admin\SettingsController::index');
        $routes->post('profile', 'Admin\SettingsController::updateProfile');
        $routes->post('password', 'Admin\SettingsController::updatePassword');
        $routes->post('general', 'Admin\SettingsController::updateGeneral');
    });
    
    // Language management
    $routes->group('language', function($routes) {
        $routes->get('/', 'Admin\LanguageController::index');
        $routes->post('update', 'Admin\LanguageController::update');
        $routes->get('export', 'Admin\LanguageController::export');
        $routes->post('import', 'Admin\LanguageController::import');
    });

    // Customer management routes
    $routes->get('customers', 'Admin\CustomersController::index');
    $routes->get('customers/view/(:num)', 'Admin\CustomersController::view/$1');
    $routes->get('customers/edit/(:num)', 'Admin\CustomersController::edit/$1');
    $routes->post('customers/update/(:num)', 'Admin\CustomersController::update/$1');
    
    // Password management routes
    $routes->post('customers/changePassword/(:num)', 'Admin\CustomersController::changePassword/$1');
    $routes->post('customers/generatePassword/(:num)', 'Admin\CustomersController::generatePassword/$1');
    
    // Token management routes
    $routes->get('customers/tokens/(:num)', 'Admin\CustomersController::manageTokens/$1');
    $routes->post('customers/updateTokens', 'Admin\CustomersController::updateTokens');
    
    // Customer status and dashboard management
    $routes->post('customers/resetDashboard/(:num)', 'Admin\CustomersController::resetDashboard/$1');
    $routes->post('customers/delete/(:num)', 'Admin\CustomersController::delete/$1');
    $routes->post('customers/deactivate/(:num)', 'Admin\CustomersController::deactivate/$1');
    
    // Check-in Settings Routes
    $routes->get('customers/checkin-settings/(:num)', 'Admin\CustomersController::checkinSettings/$1');
    $routes->post('customers/updateCheckinSettings', 'Admin\CustomersController::updateCheckinSettings');
    $routes->post('customers/resetCheckinProgress/(:num)', 'Admin\CustomersController::resetCheckinProgress/$1');
});