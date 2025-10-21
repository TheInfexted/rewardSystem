<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\AdminSettingsModel;
use App\Models\WhatsAppNumbersModel;

class SettingsController extends BaseController
{
    protected $userModel;
    protected $adminSettingsModel;
    protected $whatsappNumbersModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->adminSettingsModel = new AdminSettingsModel();
        $this->whatsappNumbersModel = new WhatsAppNumbersModel();
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
            'reward_telegram_username',
            'customer_service_hours',
            'customer_service_enabled'
        ]);

        // Get contact settings for landing page
        $contactSettings = $this->adminSettingsModel->getContactSettings();
        
        // Get WhatsApp numbers for management
        $whatsappNumbers = $this->whatsappNumbersModel->getNumbersForAdmin();
        
        // Get WhatsApp global enable setting
        $whatsappNumbersEnabled = $this->adminSettingsModel->getSetting('whatsapp_numbers_enabled', '1');
        
        // Auto-disable if no numbers exist
        if (empty($whatsappNumbers)) {
            $whatsappNumbersEnabled = '0';
        }
    
        $data = [
            'title' => 'Settings',
            'user' => $user,
            'customer_service_settings' => $customerServiceSettings,
            'contact_settings' => $contactSettings,
            'whatsapp_numbers' => $whatsappNumbers,
            'whatsapp_numbers_enabled' => $whatsappNumbersEnabled
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
            // Save contact settings - allow empty values
            $contactSettings = [
                'contact_link_1_name' => $this->request->getPost('contact_link_1_name') ?? '',
                'contact_link_1_url' => $this->request->getPost('contact_link_1_url') ?? '',
                'contact_link_1_enabled' => $this->request->getPost('contact_link_1_enabled') ? '1' : '0',
                'contact_link_2_name' => $this->request->getPost('contact_link_2_name') ?? '',
                'contact_link_2_url' => $this->request->getPost('contact_link_2_url') ?? '',
                'contact_link_2_enabled' => $this->request->getPost('contact_link_2_enabled') ? '1' : '0',
                'contact_link_3_name' => $this->request->getPost('contact_link_3_name') ?? '',
                'contact_link_3_url' => $this->request->getPost('contact_link_3_url') ?? '',
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

    /**
     * Add new WhatsApp number
     */
    public function addWhatsAppNumber()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request method.');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'phone_number' => 'required|regex_match[/^[\d\+\-\s\(\)]+$/]|max_length[20]',
            'display_name' => 'permit_empty|max_length[100]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            $phoneNumber = $this->request->getPost('phone_number');
            $displayName = $this->request->getPost('display_name') ?: null;

            // Check if phone number already exists
            if ($this->whatsappNumbersModel->phoneNumberExists($phoneNumber)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'This phone number already exists.'
                ]);
            }

            $data = [
                'phone_number' => $phoneNumber,
                'display_name' => $displayName,
                'is_active' => 1
            ];

            if ($this->whatsappNumbersModel->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => t('Admin.settings.whatsapp_numbers.messages.add_success')
            ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => t('Admin.settings.whatsapp_numbers.messages.add_failed')
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to add WhatsApp number: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => t('Admin.settings.whatsapp_numbers.messages.add_failed')
            ]);
        }
    }

    /**
     * Update WhatsApp number
     */
    public function updateWhatsAppNumber()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request method.');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'id' => 'required|integer',
            'phone_number' => 'required|regex_match[/^[\d\+\-\s\(\)]+$/]|max_length[20]',
            'display_name' => 'permit_empty|max_length[100]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            $id = $this->request->getPost('id');
            $phoneNumber = $this->request->getPost('phone_number');
            $displayName = $this->request->getPost('display_name') ?: null;
            $isActive = $this->request->getPost('is_active') ? 1 : 0;

            // Check if phone number already exists (excluding current record)
            if ($this->whatsappNumbersModel->phoneNumberExists($phoneNumber, $id)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'This phone number already exists.'
                ]);
            }

            $data = [
                'phone_number' => $phoneNumber,
                'display_name' => $displayName,
                'is_active' => $isActive
            ];

            if ($this->whatsappNumbersModel->update($id, $data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => t('Admin.settings.whatsapp_numbers.messages.update_success')
            ]);
            } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => t('Admin.settings.whatsapp_numbers.messages.update_failed')
            ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to update WhatsApp number: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => t('Admin.settings.whatsapp_numbers.messages.update_failed')
            ]);
        }
    }

    /**
     * Delete WhatsApp number
     */
    public function deleteWhatsAppNumber()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request method.');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'id' => 'required|integer'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid ID provided.'
            ]);
        }

        try {
            $id = $this->request->getPost('id');

            if ($this->whatsappNumbersModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => t('Admin.settings.whatsapp_numbers.messages.delete_success')
            ]);
            } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => t('Admin.settings.whatsapp_numbers.messages.delete_failed')
            ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to delete WhatsApp number: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => t('Admin.settings.whatsapp_numbers.messages.delete_failed')
            ]);
        }
    }

    /**
     * Update WhatsApp global enable toggle
     */
    public function updateWhatsAppGlobalToggle()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request method.');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'whatsapp_numbers_enabled' => 'required|in_list[0,1]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => t('Admin.settings.whatsapp_numbers.messages.invalid_toggle')
            ]);
        }

        try {
            $isEnabled = $this->request->getPost('whatsapp_numbers_enabled');
            
            // Check if there are any WhatsApp numbers before enabling
            if ($isEnabled === '1') {
                $activeNumbers = $this->whatsappNumbersModel->getActiveNumbers();
                if (empty($activeNumbers)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => t('Admin.settings.whatsapp_numbers.messages.global_enable_failed')
                    ]);
                }
            }

            $this->adminSettingsModel->setSetting('whatsapp_numbers_enabled', $isEnabled);

            return $this->response->setJSON([
                'success' => true,
                'message' => t('Admin.settings.whatsapp_numbers.messages.global_update_success')
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to update WhatsApp global toggle: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => t('Admin.settings.whatsapp_numbers.messages.global_update_failed')
            ]);
        }
    }

    /**
     * Toggle WhatsApp number status
     */
    public function toggleWhatsAppStatus()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request method.');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'id' => 'required|integer',
            'is_active' => 'required|in_list[0,1]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => t('Admin.settings.whatsapp_numbers.messages.invalid_parameters')
            ]);
        }

        try {
            $id = $this->request->getPost('id');
            $isActive = $this->request->getPost('is_active');

            if ($this->whatsappNumbersModel->update($id, ['is_active' => $isActive])) {
                $statusText = $isActive ? t('Admin.settings.whatsapp_numbers.status.active') : t('Admin.settings.whatsapp_numbers.status.inactive');
                return $this->response->setJSON([
                    'success' => true,
                    'message' => str_replace('{status}', $statusText, t('Admin.settings.whatsapp_numbers.messages.toggle_success'))
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => t('Admin.settings.whatsapp_numbers.messages.toggle_failed')
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to toggle WhatsApp number status: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => t('Admin.settings.whatsapp_numbers.messages.toggle_failed')
            ]);
        }
    }
}