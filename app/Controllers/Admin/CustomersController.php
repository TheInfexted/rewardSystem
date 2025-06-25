<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CustomerModel;

class CustomersController extends BaseController
{
    protected $customerModel;
    
    public function __construct()
    {
        $this->customerModel = new CustomerModel();
    }
    
    public function index()
    {
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 20;
        $search = $this->request->getGet('search');
        
        $builder = $this->customerModel;
        
        if ($search) {
            $builder = $builder->groupStart()
                ->like('username', $search)
                ->orLike('name', $search)
                ->orLike('email', $search)
                ->groupEnd();
        }
        
        $data = [
            'title' => 'Customer Management',
            'customers' => $builder->paginate($perPage),
            'pager' => $builder->pager,
            'search' => $search
        ];
        
        return view('admin/customers/index', $data);
    }
    
    public function view($customerId)
    {
        $customer = $this->customerModel->find($customerId);
        
        if (!$customer) {
            return redirect()->to('/admin/customers')->with('error', 'Customer not found');
        }
        
        // Get customer statistics
        $stats = $this->getCustomerStats($customerId);
        
        $data = [
            'title' => 'Customer Details - ' . $customer['username'],
            'customer' => $customer,
            'stats' => $stats
        ];
        
        return view('admin/customers/view', $data);
    }
    
    public function edit($customerId)
    {
        $customer = $this->customerModel->find($customerId);
        
        if (!$customer) {
            return redirect()->to('/admin/customers')->with('error', 'Customer not found');
        }
        
        $data = [
            'title' => 'Edit Customer - ' . $customer['username'],
            'customer' => $customer,
            'profile_backgrounds' => $this->getProfileBackgrounds(),
            'validation' => \Config\Services::validation()
        ];
        
        return view('admin/customers/edit', $data);
    }
    
    public function update($customerId)
    {
        $customer = $this->customerModel->find($customerId);
        
        if (!$customer) {
            return redirect()->to('/admin/customers')->with('error', 'Customer not found');
        }
        
        // Validation rules
        $validationRules = [
            'name' => 'permit_empty|max_length[100]',
            'email' => 'permit_empty|valid_email|max_length[100]',
            'profile_background' => 'required|in_list[default,blue,purple,green,orange,pink,dark]',
            'dashboard_bg_color' => 'required|regex_match[/^#[0-9A-F]{6}$/i]',
            'points' => 'required|integer|greater_than_equal_to[0]',
            'spin_tokens' => 'required|integer|greater_than_equal_to[0]',
            'is_active' => 'required|in_list[0,1]'
        ];
        
        // Add image validation if file is uploaded
        $profileImage = $this->request->getFile('profile_background_image');
        if ($profileImage && $profileImage->isValid() && !$profileImage->hasMoved()) {
            $validationRules['profile_background_image'] = 'uploaded[profile_background_image]|max_size[profile_background_image,5120]|is_image[profile_background_image]';
        }
        
        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        try {
            // Prepare update data
            $updateData = [
                'name' => $this->request->getPost('name') ?: null,
                'email' => $this->request->getPost('email') ?: null,
                'profile_background' => $this->request->getPost('profile_background'),
                'dashboard_bg_color' => $this->request->getPost('dashboard_bg_color'),
                'points' => (int) $this->request->getPost('points'),
                'spin_tokens' => (int) $this->request->getPost('spin_tokens'),
                'is_active' => (int) $this->request->getPost('is_active')
            ];
            
            // Handle profile background image upload
            if ($profileImage && $profileImage->isValid() && !$profileImage->hasMoved()) {
                
                // Validate file type manually since built-in validation might not work properly
                $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($profileImage->getMimeType(), $allowedMimes)) {
                    return redirect()->back()->withInput()
                        ->with('error', 'Invalid file type. Please upload JPEG, PNG, GIF, or WebP images only.');
                }
                
                // Check file size (5MB max)
                if ($profileImage->getSize() > 5 * 1024 * 1024) {
                    return redirect()->back()->withInput()
                        ->with('error', 'File size too large. Maximum size is 5MB.');
                }
                
                $uploadPath = FCPATH . 'uploads/profile_backgrounds/';
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadPath)) {
                    if (!mkdir($uploadPath, 0755, true)) {
                        return redirect()->back()->withInput()
                            ->with('error', 'Failed to create upload directory.');
                    }
                }
                
                // Generate unique filename
                $newName = $customerId . '_' . time() . '_' . $profileImage->getRandomName();
                
                if ($profileImage->move($uploadPath, $newName)) {
                    // Remove old image if exists
                    if (!empty($customer['profile_background_image'])) {
                        $oldImagePath = $uploadPath . $customer['profile_background_image'];
                        if (file_exists($oldImagePath)) {
                            @unlink($oldImagePath);
                        }
                    }
                    $updateData['profile_background_image'] = $newName;
                } else {
                    return redirect()->back()->withInput()
                        ->with('error', 'Failed to upload image file.');
                }
            }
            
            // Handle remove background image
            if ($this->request->getPost('remove_background_image') === '1') {
                if (!empty($customer['profile_background_image'])) {
                    $oldImagePath = FCPATH . 'uploads/profile_backgrounds/' . $customer['profile_background_image'];
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                }
                $updateData['profile_background_image'] = null;
            }
            
            // Update customer
            if ($this->customerModel->update($customerId, $updateData)) {
                
                // Log the profile changes
                $this->logProfileChanges($customerId, $customer, $updateData);
                
                return redirect()->to('/admin/customers/view/' . $customerId)
                    ->with('success', 'Customer updated successfully');
            } else {
                return redirect()->back()->withInput()
                    ->with('error', 'Failed to update customer. Please try again.');
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Admin customer update error: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'An error occurred while updating the customer: ' . $e->getMessage());
        }
    }
    
    /**
     * Change customer password
     */
    public function changePassword($customerId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/customers')->with('error', 'Invalid request');
        }
        
        try {
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer not found'
                ]);
            }
            
            $newPassword = $this->request->getPost('new_password');
            $confirmPassword = $this->request->getPost('confirm_password');
            $reason = $this->request->getPost('reason') ?: 'Password changed by admin';
            
            // Validation
            if (empty($newPassword)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'New password is required'
                ]);
            }
            
            if (strlen($newPassword) < 4) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Password must be at least 4 characters long'
                ]);
            }
            
            if ($newPassword !== $confirmPassword) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Password confirmation does not match'
                ]);
            }
            
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password using the raw update method to avoid double hashing
            $result = $this->customerModel->updatePasswordRaw($customerId, $hashedPassword);
            
            if ($result) {
                // Log the password change
                $adminId = session()->get('user_id');
                $this->logProfileChanges($customerId, $customer, [
                    'password_changed' => 'YES',
                    'password_change_reason' => $reason,
                    'changed_by_admin' => $adminId,
                    'change_date' => date('Y-m-d H:i:s')
                ]);
                
                // Log to security log
                $this->logSecurityEvent($customerId, 'password_changed_by_admin', [
                    'admin_id' => $adminId,
                    'reason' => $reason,
                    'ip_address' => $this->request->getIPAddress(),
                    'user_agent' => $this->request->getUserAgent()
                ]);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Password changed successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update password. Please try again.'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Password change error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while changing the password: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Generate a new random password for customer
     */
    public function generatePassword($customerId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/customers')->with('error', 'Invalid request');
        }
        
        try {
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer not found'
                ]);
            }
            
            $reason = $this->request->getPost('reason') ?: 'Password generated by admin';
            
            // Generate new random password
            $newPassword = $this->generateRandomPassword(8);
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $result = $this->customerModel->updatePasswordRaw($customerId, $hashedPassword);
            
            if ($result) {
                // Log the password generation
                $adminId = session()->get('user_id');
                $this->logProfileChanges($customerId, $customer, [
                    'password_generated' => 'YES',
                    'password_generation_reason' => $reason,
                    'generated_by_admin' => $adminId,
                    'generation_date' => date('Y-m-d H:i:s')
                ]);
                
                // Log to security log
                $this->logSecurityEvent($customerId, 'password_generated_by_admin', [
                    'admin_id' => $adminId,
                    'reason' => $reason,
                    'ip_address' => $this->request->getIPAddress(),
                    'user_agent' => $this->request->getUserAgent()
                ]);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'New password generated successfully',
                    'new_password' => $newPassword
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to generate new password. Please try again.'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Password generation error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while generating the password: ' . $e->getMessage()
            ]);
        }
    }
    
    public function manageTokens($customerId)
    {
        $customer = $this->customerModel->find($customerId);
        
        if (!$customer) {
            return redirect()->to('/admin/customers')->with('error', 'Customer not found');
        }
        
        $data = [
            'title' => 'Manage Spin Tokens - ' . $customer['username'],
            'customer' => $customer,
            'history' => $this->getTokensHistory($customerId)
        ];
        
        return view('admin/customers/tokens', $data);
    }
    
    public function updateTokens()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/customers');
        }
        
        $customerId = $this->request->getPost('customer_id');
        $action = $this->request->getPost('action');
        $amount = (int) $this->request->getPost('amount');
        $reason = $this->request->getPost('reason') ?: 'Admin adjustment';
        
        $adminId = session()->get('user_id');
        
        // Validation
        if ($amount <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Amount must be greater than 0'
            ]);
        }
        
        if (!in_array($action, ['add', 'remove'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid action specified'
            ]);
        }
        
        try {
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer not found'
                ]);
            }
            
            // Use the proper model methods for token management
            $result = false;
            
            if ($action === 'add') {
                $result = $this->customerModel->addSpinTokens($customerId, $amount, $adminId, $reason);
            } elseif ($action === 'remove') {
                // Check if customer has enough tokens
                if ($customer['spin_tokens'] < $amount) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Insufficient tokens. Customer only has ' . $customer['spin_tokens'] . ' tokens.'
                    ]);
                }
                $result = $this->customerModel->removeSpinTokens($customerId, $amount, $adminId, $reason);
            }
            
            if ($result) {
                // Get updated customer data
                $updatedCustomer = $this->customerModel->find($customerId);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => ucfirst($action) . ' operation completed successfully',
                    'new_balance' => $updatedCustomer['spin_tokens']
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update tokens. Please check the error logs.'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Token update error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while updating tokens: ' . $e->getMessage()
            ]);
        }
    }
    
    public function delete($customerId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/customers')->with('error', 'Invalid request');
        }
        
        try {
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer not found'
                ]);
            }
            
            // Check if customer has any important data that should prevent deletion
            $hasCheckins = $this->hasCustomerData($customerId, 'customer_checkins');
            $hasSpinHistory = $this->hasCustomerData($customerId, 'spin_history'); 
            $hasBonusClaims = $this->hasCustomerData($customerId, 'bonus_claims');
            
            if ($hasCheckins || $hasSpinHistory || $hasBonusClaims) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Cannot delete customer with existing activity history. Consider deactivating instead.'
                ]);
            }
            
            // Remove profile background image if exists
            if (!empty($customer['profile_background_image'])) {
                $imagePath = FCPATH . 'uploads/profile_backgrounds/' . $customer['profile_background_image'];
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }
            
            // Log the deletion before deleting
            $adminId = session()->get('user_id');
            $this->logCustomerDeletion($customerId, $customer, $adminId);
            
            // Delete customer (cascade will handle related records)
            $result = $this->customerModel->delete($customerId);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Customer deleted successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete customer'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Customer deletion error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while deleting the customer: ' . $e->getMessage()
            ]);
        }
    }
    
    public function deactivate($customerId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/customers')->with('error', 'Invalid request');
        }
        
        try {
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer not found'
                ]);
            }
            
            $newStatus = $customer['is_active'] ? 0 : 1;
            $result = $this->customerModel->update($customerId, ['is_active' => $newStatus]);
            
            if ($result) {
                // Log the status change
                $adminId = session()->get('user_id');
                $this->logProfileChanges($customerId, $customer, ['is_active' => $newStatus]);
                
                $message = $newStatus ? 'Customer activated successfully' : 'Customer deactivated successfully';
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $message,
                    'new_status' => $newStatus
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update customer status'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Customer status update error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    public function resetDashboard($customerId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/customers');
        }
        
        try {
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer not found'
                ]);
            }
            
            // Reset to defaults
            $resetData = [
                'profile_background' => 'default',
                'profile_background_image' => null,
                'dashboard_bg_color' => '#ffffff'
            ];
            
            // Remove background image file if exists
            if (!empty($customer['profile_background_image'])) {
                $imagePath = FCPATH . 'uploads/profile_backgrounds/' . $customer['profile_background_image'];
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }
            
            $result = $this->customerModel->update($customerId, $resetData);
            
            if ($result) {
                // Log the changes
                $this->logProfileChanges($customerId, $customer, $resetData);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Dashboard reset successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to reset dashboard'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Dashboard reset error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    private function getCustomerStats($customerId)
    {
        $db = \Config\Database::connect();
        
        // Get check-in stats
        $checkinQuery = $db->query("
            SELECT 
                COUNT(*) as total_checkins,
                SUM(reward_points) as total_points_earned,
                MAX(checkin_date) as last_checkin
            FROM customer_checkins 
            WHERE customer_id = ?
        ", [$customerId]);
        $checkinStats = $checkinQuery->getRowArray();
        
        // Get current month checkins
        $monthlyQuery = $db->query("
            SELECT COUNT(*) as monthly_checkins
            FROM customer_checkins 
            WHERE customer_id = ? 
            AND MONTH(checkin_date) = MONTH(CURRENT_DATE())
            AND YEAR(checkin_date) = YEAR(CURRENT_DATE())
        ", [$customerId]);
        $monthlyStats = $monthlyQuery->getRowArray();
        
        // Get spin history
        $spinQuery = $db->query("
            SELECT 
                COUNT(*) as total_spins,
                SUM(CASE WHEN item_won != 'Try Again' AND item_won IS NOT NULL THEN 1 ELSE 0 END) as winning_spins,
                MAX(spin_date) as last_spin
            FROM spin_history 
            WHERE customer_id = ?
        ", [$customerId]);
        $spinStats = $spinQuery->getRowArray();
        
        return [
            'checkins' => $checkinStats,
            'monthly' => $monthlyStats,
            'spins' => $spinStats
        ];
    }
    
    private function getTokensHistory($customerId)
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT 
                action,
                amount,
                balance_before as old_balance,
                balance_after as new_balance,
                admin_id,
                reason,
                created_at
            FROM spin_tokens_history 
            WHERE customer_id = ? 
            ORDER BY created_at DESC 
            LIMIT 20
        ", [$customerId]);
        
        return $query->getResultArray();
    }
    
    private function logTokenTransaction($customerId, $action, $amount, $oldBalance, $newBalance, $adminId, $reason)
    {
        $db = \Config\Database::connect();
        
        $db->query("
            INSERT INTO spin_tokens_history 
            (customer_id, action, amount, balance_before, balance_after, admin_id, reason, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ", [$customerId, $action, $amount, $oldBalance, $newBalance, $adminId, $reason]);
    }
    
    private function hasCustomerData($customerId, $tableName)
    {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT COUNT(*) as count FROM {$tableName} WHERE customer_id = ?", [$customerId]);
        $result = $query->getRowArray();
        return $result['count'] > 0;
    }
    
    private function logCustomerDeletion($customerId, $customerData, $adminId)
    {
        $db = \Config\Database::connect();
        
        try {
            $db->query("
                INSERT INTO customer_profile_changes 
                (customer_id, admin_id, field_changed, old_value, new_value, change_reason, created_at) 
                VALUES (?, ?, 'customer_deleted', ?, 'DELETED', 'Customer deleted by admin', NOW())
            ", [$customerId, $adminId, json_encode($customerData)]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log customer deletion: ' . $e->getMessage());
        }
    }
    
    private function logProfileChanges($customerId, $oldData, $newData)
    {
        $adminId = session()->get('user_id');
        $db = \Config\Database::connect();
        
        try {
            foreach ($newData as $field => $newValue) {
                $oldValue = $oldData[$field] ?? null;
                
                // Only log if value actually changed
                if ($oldValue != $newValue) {
                    $db->query("
                        INSERT INTO customer_profile_changes 
                        (customer_id, admin_id, field_changed, old_value, new_value, created_at) 
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ", [$customerId, $adminId, $field, $oldValue, $newValue]);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to log profile changes: ' . $e->getMessage());
        }
    }
    
    /**
     * Log security events (password changes, etc.)
     */
    private function logSecurityEvent($customerId, $eventType, $details)
    {
        $db = \Config\Database::connect();
        
        try {
            $db->query("
                INSERT INTO security_log 
                (customer_id, event_type, event_details, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ", [
                $customerId, 
                $eventType, 
                json_encode($details),
                $details['ip_address'] ?? null,
                $details['user_agent'] ?? null
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log security event: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate random password
     */
    private function generateRandomPassword($length = 8)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($chars), 0, $length);
    }
    
    private function getProfileBackgrounds()
    {
        return [
            'default' => 'Default',
            'blue' => 'Blue',
            'purple' => 'Purple', 
            'green' => 'Green',
            'orange' => 'Orange',
            'pink' => 'Pink',
            'dark' => 'Dark'
        ];
    }

    /**
     * Display check-in settings and history for a customer
     */
    public function checkinSettings($customerId)
    {
        $customer = $this->customerModel->find($customerId);
        
        if (!$customer) {
            return redirect()->to('/admin/customers')->with('error', 'Customer not found');
        }
        
        $data = [
            'title' => 'Check-in Settings - ' . $customer['username'],
            'customer' => $customer,
            'checkin_settings' => $this->getCheckinSettings(),
            'customer_checkin_history' => $this->getCustomerCheckinHistory($customerId),
            'weekly_progress' => $this->getWeeklyProgress($customerId),
            'checkin_statistics' => $this->getCheckinStatistics($customerId)
        ];
        
        return view('admin/customers/checkin_settings', $data);
    }

    /**
     * Update check-in settings
     */
    public function updateCheckinSettings()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/customers');
        }
        
        $settingsData = $this->request->getPost();
        
        // Validation rules for check-in settings
        $validationRules = [
            'default_checkin_points' => 'required|integer|greater_than[0]|less_than[1000]',
            'weekly_bonus_multiplier' => 'required|decimal|greater_than[0]|less_than[10]',
            'max_streak_days' => 'required|integer|greater_than[0]|less_than[365]',
            'weekend_bonus_points' => 'required|integer|greater_than_equal_to[0]|less_than[500]'
        ];
        
        if (!$this->validate($validationRules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }
        
        try {
            $db = \Config\Database::connect();
            
            // Update multiple settings at once
            $settings = [
                'default_checkin_points' => $settingsData['default_checkin_points'],
                'weekly_bonus_multiplier' => $settingsData['weekly_bonus_multiplier'],
                'max_streak_days' => $settingsData['max_streak_days'],
                'weekend_bonus_points' => $settingsData['weekend_bonus_points']
            ];
            
            foreach ($settings as $key => $value) {
                $db->query("
                    INSERT INTO admin_settings (setting_key, setting_value, setting_description, created_at) 
                    VALUES (?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE 
                    setting_value = VALUES(setting_value), 
                    updated_at = NOW()
                ", [$key, $value, "Check-in setting: {$key}"]);
            }
            
            // Log the changes
            $adminId = session()->get('user_id');
            $this->logSettingsChange($adminId, 'checkin_settings_updated', json_encode($settings));
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Check-in settings updated successfully'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Check-in settings update error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reset customer check-in progress
     */
    public function resetCheckinProgress($customerId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/customers');
        }
        
        try {
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer not found'
                ]);
            }
            
            $resetType = $this->request->getPost('reset_type');
            $db = \Config\Database::connect();
            $adminId = session()->get('user_id');
            
            switch ($resetType) {
                case 'current_streak':
                    // Reset current streak only
                    $db->query("
                        UPDATE customer_checkins 
                        SET streak_day = 1 
                        WHERE customer_id = ? 
                        AND checkin_date = (
                            SELECT MAX(checkin_date) 
                            FROM (SELECT checkin_date FROM customer_checkins WHERE customer_id = ?) as max_date
                        )
                    ", [$customerId, $customerId]);
                    $message = 'Current streak reset successfully';
                    break;
                    
                case 'monthly_progress':
                    // Reset current month checkins
                    $db->query("
                        DELETE FROM customer_checkins 
                        WHERE customer_id = ? 
                        AND MONTH(checkin_date) = MONTH(CURRENT_DATE())
                        AND YEAR(checkin_date) = YEAR(CURRENT_DATE())
                    ", [$customerId]);
                    
                    // Update customer monthly count
                    $this->customerModel->update($customerId, ['monthly_checkins' => 0]);
                    $message = 'Monthly progress reset successfully';
                    break;
                    
                case 'all_history':
                    // Reset all check-in history
                    $db->query("DELETE FROM customer_checkins WHERE customer_id = ?", [$customerId]);
                    $db->query("DELETE FROM checkin_history WHERE user_id = ?", [$customerId]);
                    $this->customerModel->update($customerId, ['monthly_checkins' => 0]);
                    $message = 'All check-in history reset successfully';
                    break;
                    
                default:
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Invalid reset type'
                    ]);
            }
            
            // Log the reset action
            $this->logProfileChanges($customerId, $customer, [
                'checkin_reset' => $resetType,
                'reset_by_admin' => $adminId,
                'reset_date' => date('Y-m-d H:i:s')
            ]);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Check-in reset error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to reset check-in progress: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get check-in settings from database
     */
    private function getCheckinSettings()
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT setting_key, setting_value 
            FROM admin_settings 
            WHERE setting_key IN (
                'default_checkin_points', 
                'weekly_bonus_multiplier', 
                'max_streak_days', 
                'weekend_bonus_points'
            )
        ");
        
        $settings = [];
        foreach ($query->getResultArray() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        // Set defaults if not found
        $defaults = [
            'default_checkin_points' => 10,
            'weekly_bonus_multiplier' => 1.5,
            'max_streak_days' => 7,
            'weekend_bonus_points' => 5
        ];
        
        return array_merge($defaults, $settings);
    }

    /**
     * Get customer check-in history with pagination
     */
    private function getCustomerCheckinHistory($customerId, $limit = 30)
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT 
                checkin_date,
                reward_points,
                streak_day,
                created_at,
                DAYNAME(checkin_date) as day_name,
                CASE 
                    WHEN DAYOFWEEK(checkin_date) IN (1,7) THEN 'weekend'
                    ELSE 'weekday'
                END as day_type
            FROM customer_checkins 
            WHERE customer_id = ? 
            ORDER BY checkin_date DESC 
            LIMIT ?
        ", [$customerId, $limit]);
        
        return $query->getResultArray();
    }

    /**
     * Get weekly progress for current and previous weeks
     */
    private function getWeeklyProgress($customerId)
    {
        $db = \Config\Database::connect();
        
        // Get current week progress
        $currentWeekQuery = $db->query("
            SELECT 
                checkin_date,
                reward_points,
                streak_day,
                DAYOFWEEK(checkin_date) as day_of_week,
                DAYNAME(checkin_date) as day_name
            FROM customer_checkins 
            WHERE customer_id = ? 
            AND YEARWEEK(checkin_date, 1) = YEARWEEK(CURDATE(), 1)
            ORDER BY checkin_date ASC
        ", [$customerId]);
        
        $currentWeek = $currentWeekQuery->getResultArray();
        
        // Get previous week for comparison
        $previousWeekQuery = $db->query("
            SELECT 
                checkin_date,
                reward_points,
                COUNT(*) as checkin_count
            FROM customer_checkins 
            WHERE customer_id = ? 
            AND YEARWEEK(checkin_date, 1) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 WEEK), 1)
            GROUP BY YEARWEEK(checkin_date, 1)
        ", [$customerId]);
        
        $previousWeek = $previousWeekQuery->getRowArray();
        
        // Create weekly calendar view
        $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $weeklyCalendar = [];
        
        foreach ($weekDays as $index => $dayName) {
            $dayNumber = $index + 2; // MySQL DAYOFWEEK starts from Sunday=1
            if ($dayNumber == 8) $dayNumber = 1; // Sunday
            
            $checkinData = null;
            foreach ($currentWeek as $checkin) {
                if ($checkin['day_of_week'] == $dayNumber) {
                    $checkinData = $checkin;
                    break;
                }
            }
            
            $weeklyCalendar[] = [
                'day_name' => $dayName,
                'day_number' => $dayNumber,
                'checkin_data' => $checkinData,
                'is_weekend' => in_array($dayName, ['Saturday', 'Sunday'])
            ];
        }
        
        return [
            'current_week' => $weeklyCalendar,
            'previous_week' => $previousWeek,
            'total_current_week' => count($currentWeek),
            'perfect_week' => count($currentWeek) >= 7
        ];
    }

    /**
     * Get comprehensive check-in statistics
     */
    private function getCheckinStatistics($customerId)
    {
        $db = \Config\Database::connect();
        
        // Basic statistics
        $statsQuery = $db->query("
            SELECT 
                COUNT(*) as total_checkins,
                SUM(reward_points) as total_points_earned,
                MAX(streak_day) as max_streak,
                MIN(checkin_date) as first_checkin,
                MAX(checkin_date) as last_checkin,
                AVG(reward_points) as avg_points_per_checkin
            FROM customer_checkins 
            WHERE customer_id = ?
        ", [$customerId]);
        
        $basicStats = $statsQuery->getRowArray();
        
        // Monthly statistics
        $monthlyQuery = $db->query("
            SELECT 
                MONTH(checkin_date) as month,
                YEAR(checkin_date) as year,
                COUNT(*) as checkin_count,
                SUM(reward_points) as points_earned,
                MONTHNAME(checkin_date) as month_name
            FROM customer_checkins 
            WHERE customer_id = ? 
            GROUP BY YEAR(checkin_date), MONTH(checkin_date)
            ORDER BY year DESC, month DESC
            LIMIT 12
        ", [$customerId]);
        
        $monthlyStats = $monthlyQuery->getResultArray();
        
        // Streak analysis
        $streakQuery = $db->query("
            SELECT 
                streak_day,
                COUNT(*) as frequency
            FROM customer_checkins 
            WHERE customer_id = ? 
            GROUP BY streak_day
            ORDER BY streak_day ASC
        ", [$customerId]);
        
        $streakAnalysis = $streakQuery->getResultArray();
        
        // Weekend vs weekday analysis
        $dayTypeQuery = $db->query("
            SELECT 
                CASE 
                    WHEN DAYOFWEEK(checkin_date) IN (1,7) THEN 'Weekend'
                    ELSE 'Weekday'
                END as day_type,
                COUNT(*) as checkin_count,
                AVG(reward_points) as avg_points
            FROM customer_checkins 
            WHERE customer_id = ? 
            GROUP BY day_type
        ", [$customerId]);
        
        $dayTypeStats = $dayTypeQuery->getResultArray();
        
        return [
            'basic' => $basicStats,
            'monthly' => $monthlyStats,
            'streak_analysis' => $streakAnalysis,
            'day_type_analysis' => $dayTypeStats
        ];
    }

    /**
     * Log settings changes
     */
    private function logSettingsChange($adminId, $action, $details)
    {
        $db = \Config\Database::connect();
        
        try {
            $db->query("
                INSERT INTO customer_profile_changes 
                (customer_id, admin_id, field_changed, old_value, new_value, change_reason, created_at) 
                VALUES (0, ?, ?, '', ?, 'Settings updated by admin', NOW())
            ", [$adminId, $action, $details]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log settings change: ' . $e->getMessage());
        }
    }
}