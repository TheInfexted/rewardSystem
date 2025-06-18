<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\CheckinModel;
use App\Models\AdminSettingsModel;

class UserController extends BaseController
{
    protected $userModel;
    protected $checkinModel;
    protected $adminSettingsModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->checkinModel = new CheckinModel();
        $this->adminSettingsModel = new AdminSettingsModel();
    }

    /**
     * User Dashboard Page
     */
    public function dashboard()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please login to access your dashboard.');
        }

        $userId = session()->get('user_id');
        $userData = $this->userModel->find($userId);

        if (!$userData) {
            return redirect()->to('/login')->with('error', 'User not found.');
        }

        // Get today's check-in status
        $todayCheckin = $this->checkinModel->getTodayCheckin($userId);
        
        // Get consecutive check-in streak
        $checkinStreak = $this->checkinModel->getCheckinStreak($userId);
        
        // Get total check-ins this month
        $monthlyCheckins = $this->checkinModel->getMonthlyCheckins($userId);

        // Get user's background preference
        $userBgPreference = $userData['profile_background'] ?? 'default';

        $data = [
            'title' => 'User Dashboard',
            'user' => $userData,
            'username' => $userData['name'], // This will be the 10-digit username
            'today_checkin' => $todayCheckin,
            'checkin_streak' => $checkinStreak,
            'monthly_checkins' => $monthlyCheckins,
            'profile_background' => $userBgPreference
        ];

        return view('user/dashboard', $data);
    }

    /**
     * Handle daily check-in
     */
    public function checkin()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/user/dashboard');
        }

        $userId = session()->get('user_id');
        
        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not logged in'
            ]);
        }

        // Check if already checked in today
        $todayCheckin = $this->checkinModel->getTodayCheckin($userId);
        
        if ($todayCheckin) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You have already checked in today!'
            ]);
        }

        // Record today's check-in
        $checkinData = [
            'user_id' => $userId,
            'checkin_date' => date('Y-m-d'),
            'checkin_time' => date('Y-m-d H:i:s'),
            'reward_points' => 10 // Base reward points
        ];

        if ($this->checkinModel->insert($checkinData)) {
            // Calculate new streak
            $newStreak = $this->checkinModel->getCheckinStreak($userId);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Check-in successful!',
                'reward_points' => 10,
                'new_streak' => $newStreak
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Check-in failed. Please try again.'
        ]);
    }

    /**
     * Update profile background
     */
    public function updateBackground()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/user/dashboard');
        }

        $userId = session()->get('user_id');
        $background = $this->request->getPost('background');

        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not logged in'
            ]);
        }

        // Validate background option
        $allowedBackgrounds = ['default', 'blue', 'purple', 'green', 'red', 'orange'];
        
        if (!in_array($background, $allowedBackgrounds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid background option'
            ]);
        }

        // Update user's background preference
        if ($this->userModel->update($userId, ['profile_background' => $background])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Background updated successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to update background'
        ]);
    }

    /**
     * Get wheel game data for modal
     */
    public function getWheelData()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/user/dashboard');
        }

        // This will return the same data used in the main wheel page
        // You can reuse the logic from LandingController
        $landingController = new \App\Controllers\LandingController();
        
        // Get spins remaining for this user
        $userId = session()->get('user_id');
        $spinsRemaining = 3; // Default or get from database
        
        return $this->response->setJSON([
            'success' => true,
            'spins_remaining' => $spinsRemaining,
            'wheel_items' => $landingController->getWheelItems() // You'll need to make this method public
        ]);
    }
}