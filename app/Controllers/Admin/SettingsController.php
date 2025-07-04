<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\AdminSettingsModel;

class SettingsController extends BaseController
{
    protected $userModel;
    protected $adminSettingsModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->adminSettingsModel = new AdminSettingsModel();
    }

    /**
     * Display settings page
     */
    public function index()
    {
        $userId = session()->get('user_id');
    
        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Session expired. Please log in again.');
        }
    
        $user = $this->userModel->find($userId);
        
        // Get current customer service settings
        $customerServiceSettings = $this->adminSettingsModel->getSettings([
            'reward_whatsapp_number',
            'reward_telegram_username',
            'customer_service_hours',
            'customer_service_enabled'
        ]);

        // Get contact settings for landing page
        $contactSettings = $this->adminSettingsModel->getContactSettings();
    
        $data = [
            'title' => 'Settings',
            'user' => $user,
            'customer_service_settings' => $customerServiceSettings,
            'contact_settings' => $contactSettings
        ];
    
        return view('admin/settings/index', $data);
    }
        
    /**
     * Update profile
     */
    public function updateProfile()
    {
        $userId = session()->get('user_id');
        if (empty($userId)) {
            return redirect()->to('/login')->with('error', 'Session expired. Please log in again.');
        }
        
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $userId . ']'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email')
        ];
        
        if ($this->userModel->update($userId, $data)) {
            // Update session
            session()->set('user_name', $data['name']);
            session()->set('user_email', $data['email']);
            
            return redirect()->back()->with('success', 'Profile updated successfully.');
        }
        
        return redirect()->back()->with('error', 'Failed to update profile.');
    }
    
    /**
     * Update password
     */
    public function updatePassword()
    {
        $userId = session()->get('user_id');
        if (empty($userId)) {
            return redirect()->to('/login')->with('error', 'Session expired. Please log in again.');
        }
        
        $user = $this->userModel->find($userId);
        
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->with('password_errors', $this->validator->getErrors());
        }
        
        $currentPassword = $this->request->getPost('current_password');
        
        if (!password_verify($currentPassword, $user['password'])) {
            return redirect()->back()->with('password_error', 'Current password is incorrect.');
        }
        
        $newPassword = password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT);
        
        if ($this->userModel->update($userId, ['password' => $newPassword])) {
            return redirect()->back()->with('password_success', 'Password updated successfully.');
        }
        
        return redirect()->back()->with('password_error', 'Failed to update password.');
    }

    /**
     * Update customer service settings
     */
    public function updateCustomerService()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request method.');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'reward_whatsapp_number' => 'required|regex_match[/^[\d\+\-\s\(\)]+$/]|max_length[20]',
            'reward_telegram_username' => 'required|alpha_dash|max_length[50]',
            'customer_service_hours' => 'permit_empty|max_length[100]',
            'customer_service_enabled' => 'permit_empty|in_list[0,1]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            // Get all the customer service settings from the form
            $settings = [
                'reward_whatsapp_number' => $this->request->getPost('reward_whatsapp_number'),
                'reward_telegram_username' => $this->request->getPost('reward_telegram_username'),
                'customer_service_hours' => $this->request->getPost('customer_service_hours') ?: '9:00 AM - 6:00 PM (GMT+8)',
                'customer_service_enabled' => $this->request->getPost('customer_service_enabled') ? '1' : '0'
            ];

            // Update all settings
            foreach ($settings as $key => $value) {
                $this->adminSettingsModel->setSetting($key, $value);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Customer service settings updated successfully!'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to update customer service settings: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update settings. Please try again.'
            ]);
        }
    }

    /**
     * Update landing page contact settings
     */
    public function updateLandingPageContact()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request method.');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'contact_link_1_name' => 'permit_empty|max_length[50]',
            'contact_link_1_url' => 'permit_empty|valid_url',
            'contact_link_2_name' => 'permit_empty|max_length[50]',
            'contact_link_2_url' => 'permit_empty|valid_url',
            'contact_link_3_name' => 'permit_empty|max_length[50]',
            'contact_link_3_url' => 'permit_empty|valid_url'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            // Save contact settings
            $contactSettings = [
                'contact_link_1_name' => $this->request->getPost('contact_link_1_name') ?: 'WhatsApp Support',
                'contact_link_1_url' => $this->request->getPost('contact_link_1_url') ?: 'https://wa.me/601159599022',
                'contact_link_1_enabled' => $this->request->getPost('contact_link_1_enabled') ? '1' : '0',
                'contact_link_2_name' => $this->request->getPost('contact_link_2_name') ?: 'Telegram Support',
                'contact_link_2_url' => $this->request->getPost('contact_link_2_url') ?: 'https://t.me/harryford19',
                'contact_link_2_enabled' => $this->request->getPost('contact_link_2_enabled') ? '1' : '0',
                'contact_link_3_name' => $this->request->getPost('contact_link_3_name') ?: 'Email Support',
                'contact_link_3_url' => $this->request->getPost('contact_link_3_url') ?: 'mailto:support@yourcompany.com',
                'contact_link_3_enabled' => $this->request->getPost('contact_link_3_enabled') ? '1' : '0'
            ];

            // Update contact settings in admin_settings table
            foreach ($contactSettings as $key => $value) {
                $this->adminSettingsModel->setSetting($key, $value);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Landing page contact settings updated successfully!'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to update landing page contact settings: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update contact settings. Please try again.'
            ]);
        }
    }
}