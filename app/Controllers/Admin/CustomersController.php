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
        $perPage = 20;
        $search = $this->request->getGet('search');

        $model = $this->customerModel;

        if ($search) {
            $model = $model
                ->like('username', $search)
                ->orLike('name', $search)
                ->orLike('email', $search);
        }

        $model = $model->orderBy('created_at', 'DESC');

        $data = [
            'title' => 'Customer Management',
            'customers' => $model->paginate($perPage),
            'pager' => $model->pager,
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

        $themeData = [
            'current_bg_color' => $customer['dashboard_bg_color'] ?? '#ffffff',
            'current_bg_image' => $customer['profile_background_image'] ?? null,
            'theme_history' => $this->getCustomerThemeHistory($customerId)
        ];
        
        $data = [
            'title' => 'Customer Details - ' . $customer['username'],
            'customer' => $customer,
            'stats' => $stats,
            'theme_data' => $themeData
        ];
        
        return view('admin/customers/view', $data);
    }

    /**
     * Get customer theme change history
     */
    private function getCustomerThemeHistory($customerId, $limit = 10)
    {
        $db = \Config\Database::connect();
        
        try {
            $query = $db->query("
                SELECT 
                    action_type,
                    old_bg_color,
                    new_bg_color,
                    old_bg_image,
                    new_bg_image,
                    change_reason,
                    created_at,
                    CASE 
                        WHEN admin_id IS NOT NULL THEN 'Admin'
                        ELSE 'Customer'
                    END as changed_by
                FROM dashboard_theme_history 
                WHERE customer_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ", [$customerId, $limit]);
            
            return $query->getResultArray();
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to get theme history: ' . $e->getMessage());
            return [];
        }
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
            'validation' => \Config\Services::validation(),
            // ADD THESE NEW LINES:
            'theme_colors' => $this->getThemeColors(),
            'current_theme' => [
                'bg_color' => $customer['dashboard_bg_color'] ?? '#ffffff',
                'bg_image' => $customer['profile_background_image'] ?? null
            ]
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
            'dashboard_bg_color' => 'permit_empty|regex_match[/^#[0-9A-F]{6}$/i]',
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
            $updateData = [
                'name' => $this->request->getPost('name') ?: null,
                'email' => $this->request->getPost('email') ?: null,
                'profile_background' => $this->request->getPost('profile_background'),
                'points' => (int) $this->request->getPost('points'),
                'spin_tokens' => (int) $this->request->getPost('spin_tokens'),
                'is_active' => (int) $this->request->getPost('is_active')
            ];

            // DASHBOARD THEME HANDLING
            $this->handleDashboardThemeUpdate($customerId, $customer, $updateData);
            
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
                
                return redirect()->to('admin/customers/edit/' . $customerId)
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
     * Handle dashboard theme updates in your existing update method
     */
    private function handleDashboardThemeUpdate($customerId, $customer, &$updateData)
    {
        // Handle dashboard background color
        $bgColor = $this->request->getPost('dashboard_bg_color');
        if (!empty($bgColor)) {
            $validColor = $this->customerModel->validateDashboardColor($bgColor);
            if ($validColor !== false) {
                $updateData['dashboard_bg_color'] = $validColor;
            }
        }
        
        // Handle background image upload
        $profileImage = $this->request->getFile('profile_background_image');
        if ($profileImage && $profileImage->isValid() && !$profileImage->hasMoved()) {
            $uploadResult = $this->handleBackgroundImageUpload($profileImage, $customerId, $customer);
            if ($uploadResult['success']) {
                $updateData['profile_background_image'] = $uploadResult['filename'];
            } else {
                throw new \Exception($uploadResult['message']);
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
    }

    /**
     * Get available theme colors
     */
    private function getThemeColors()
    {
        return [
            '#ffffff' => 'Default White',
            '#667eea' => 'Blue Gradient',
            '#764ba2' => 'Purple Gradient', 
            '#11998e' => 'Teal Gradient',
            '#ff9a56' => 'Orange Gradient',
            '#f093fb' => 'Pink Gradient',
            '#2c3e50' => 'Dark Blue',
            '#e74c3c' => 'Red',
            '#f39c12' => 'Orange',
            '#27ae60' => 'Green',
            '#9b59b6' => 'Purple',
            '#34495e' => 'Dark Gray'
        ];
    }
    
    /**
     * Change customer password
     */
    public function changePassword($customerId)
    {
        // Set JSON response header
        $this->response->setContentType('application/json');
        
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
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
            
            $newPassword = $this->request->getPost('new_password');
            $confirmPassword = $this->request->getPost('confirm_password');
            $reason = $this->request->getPost('reason') ?: 'Password changed by admin';
            $adminId = session()->get('user_id');
            
            // Log what we received
            log_message('info', 'Password change request - Customer ID: ' . $customerId . ', Admin ID: ' . $adminId);
            
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
            
            // Use the model's changePassword method
            $result = $this->customerModel->changePassword($customerId, $newPassword, $adminId, $reason);
            
            if ($result) {
                log_message('info', 'Password changed successfully for customer ID: ' . $customerId);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Password changed successfully'
                ]);
            } else {
                log_message('error', 'Failed to change password for customer ID: ' . $customerId);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update password in database'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Password change exception: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate a new random password for customer
     */
    public function generatePassword($customerId)
    {
        // Set JSON response header
        $this->response->setContentType('application/json');
        
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
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
            
            $reason = $this->request->getPost('reason') ?: 'Password generated by admin';
            $adminId = session()->get('user_id');
            
            log_message('info', 'Password generation request - Customer ID: ' . $customerId . ', Admin ID: ' . $adminId);
            
            // Use the model's generateRandomPassword method with correct parameters
            $result = $this->customerModel->generateRandomPassword($customerId, $adminId, $reason, 8);
            
            if ($result && $result['success']) {
                log_message('info', 'Password generated successfully for customer ID: ' . $customerId);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'New password generated successfully',
                    'new_password' => $result['password']
                ]);
            } else {
                log_message('error', 'Failed to generate password for customer ID: ' . $customerId);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to generate new password. Please check server logs for details.'
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

    /**
     * Handle background image upload
     */
    private function handleBackgroundImageUpload($profileImage, $customerId, $customer)
    {
        try {
            // Validate file type
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($profileImage->getMimeType(), $allowedMimes)) {
                return [
                    'success' => false,
                    'message' => 'Invalid file type. Please upload JPEG, PNG, GIF, or WebP images only.'
                ];
            }
            
            // Check file size (5MB max)
            if ($profileImage->getSize() > 5 * 1024 * 1024) {
                return [
                    'success' => false,
                    'message' => 'File size too large. Maximum size is 5MB.'
                ];
            }
            
            $uploadPath = FCPATH . 'uploads/profile_backgrounds/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0755, true)) {
                    return [
                        'success' => false,
                        'message' => 'Failed to create upload directory.'
                    ];
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
                
                return [
                    'success' => true,
                    'filename' => $newName,
                    'message' => 'Image uploaded successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to upload image file.'
                ];
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Background image upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ];
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
            
            // Use the enhanced model method
            $result = $this->customerModel->resetDashboardTheme($customerId);
            
            if ($result) {
                // Log the reset using enhanced logging
                $adminId = session()->get('user_id');
                $this->logDashboardThemeChange($customerId, 'theme_reset', [
                    'old_color' => $customer['dashboard_bg_color'],
                    'old_image' => $customer['profile_background_image'],
                    'new_color' => '#ffffff',
                    'new_image' => null,
                    'admin_id' => $adminId,
                    'reason' => 'Dashboard reset by admin'
                ]);
                
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
    private function generateRandomPassword(int $length = 8): string
    {
        try {
            // Character sets for password generation
            $lowercase = 'abcdefghijklmnopqrstuvwxyz';
            $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $numbers = '0123456789';
            
            // Ensure password has at least one character from each set for strength
            $password = '';
            $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
            $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
            $password .= $numbers[rand(0, strlen($numbers) - 1)];
            
            // Fill the rest with random characters from all sets
            $allChars = $lowercase . $uppercase . $numbers;
            for ($i = 3; $i < $length; $i++) {
                $password .= $allChars[rand(0, strlen($allChars) - 1)];
            }
            
            // Shuffle the password to randomize character positions
            return str_shuffle($password);
            
        } catch (\Exception $e) {
            log_message('error', 'Error generating random password: ' . $e->getMessage());
            throw new \Exception('Failed to generate random password');
        }
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
            'checkin_settings' => $this->getCheckinSettings($customerId),
            'customer_checkin_history' => $this->getCustomerCheckinHistory($customerId),
            'weekly_progress' => $this->getWeeklyProgress($customerId),
            'checkin_statistics' => $this->getCheckinStatistics($customerId)
        ];
        
        return view('admin/customers/checkin_settings', $data);
    }

    /**
     * ADD: Log customer-specific settings changes
     */
    private function logCustomerSpecificSettingsChange($customerId, $adminId, $action, $details)
    {
        $db = \Config\Database::connect();
        
        try {
            $db->query("
                INSERT INTO customer_profile_changes 
                (customer_id, admin_id, field_changed, old_value, new_value, change_reason, created_at) 
                VALUES (?, ?, ?, '', ?, 'Customer-specific settings updated by admin', NOW())
            ", [$customerId, $adminId, $action, $details]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log customer-specific settings change: ' . $e->getMessage());
        }
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
        
        // Get customer ID from the request
        $customerId = $this->request->getPost('customer_id');
        
        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Customer ID is required'
            ]);
        }
        
        // Validation rules for check-in settings
        $validationRules = [
            'customer_id' => 'required|integer',
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
            
            // Verify customer exists
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Customer not found'
                ]);
            }
            
            // Prepare customer-specific settings
            $customerSettings = [
                'customer_id' => $customerId,
                'default_checkin_points' => $settingsData['default_checkin_points'],
                'weekly_bonus_multiplier' => $settingsData['weekly_bonus_multiplier'],
                'max_streak_days' => $settingsData['max_streak_days'],
                'weekend_bonus_points' => $settingsData['weekend_bonus_points'],
                'consecutive_bonus_points' => $settingsData['consecutive_bonus_points'],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Insert or update customer-specific settings
            $existingSettings = $db->query("
                SELECT id FROM customer_checkin_settings WHERE customer_id = ?
            ", [$customerId])->getRow();
            
            if ($existingSettings) {
                // Update existing settings
                $result = $db->query("
                    UPDATE customer_checkin_settings 
                    SET default_checkin_points = ?,
                        weekly_bonus_multiplier = ?,
                        max_streak_days = ?,
                        weekend_bonus_points = ?,
                        consecutive_bonus_points = ?,
                        updated_at = NOW()
                    WHERE customer_id = ?
                ", [
                    $customerSettings['default_checkin_points'],
                    $customerSettings['weekly_bonus_multiplier'],
                    $customerSettings['max_streak_days'],
                    $customerSettings['weekend_bonus_points'],
                    $customerSettings['consecutive_bonus_points'],
                    $customerId
                ]);
            } else {
                // Insert new settings
                $result = $db->query("
                    INSERT INTO customer_checkin_settings 
                    (customer_id, default_checkin_points, weekly_bonus_multiplier, max_streak_days, weekend_bonus_points, consecutive_bonus_points, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                ", [
                    $customerId,
                    $customerSettings['default_checkin_points'],
                    $customerSettings['weekly_bonus_multiplier'],
                    $customerSettings['max_streak_days'],
                    $customerSettings['weekend_bonus_points'],
                    $customerSettings['consecutive_bonus_points']
                ]);
            }
            
            if ($result) {
                // Log the changes for this specific customer
                $adminId = session()->get('user_id');
                $this->logCustomerSpecificSettingsChange($customerId, $adminId, 'checkin_settings_updated', json_encode($customerSettings));
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Check-in settings updated successfully for customer: ' . $customer['username']
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update customer settings'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Customer-specific check-in settings update error: ' . $e->getMessage());
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
                    // First, get the customer's current streak info
                    $currentStreakQuery = $db->query("
                        SELECT id, streak_day, checkin_date 
                        FROM customer_checkins 
                        WHERE customer_id = ? 
                        ORDER BY checkin_date DESC 
                        LIMIT 1
                    ", [$customerId]);
                    
                    $latestCheckin = $currentStreakQuery->getRowArray();
                    
                    if ($latestCheckin && $latestCheckin['streak_day'] > 1) {
                        // Calculate the date range for the current streak
                        $streakStartDate = date('Y-m-d', strtotime($latestCheckin['checkin_date'] . ' -' . ($latestCheckin['streak_day'] - 1) . ' days'));
                        
                        // Reset all streak records to 1 for the current streak period
                        $resetQuery = $db->query("
                            UPDATE customer_checkins 
                            SET streak_day = 1 
                            WHERE customer_id = ? 
                            AND checkin_date >= ? 
                            AND checkin_date <= ?
                        ", [$customerId, $streakStartDate, $latestCheckin['checkin_date']]);
                        
                        if ($resetQuery) {
                            $message = 'Current streak reset successfully. Previous streak was ' . $latestCheckin['streak_day'] . ' days, now reset to 1.';
                            
                            // Also update user_points table if it exists
                            $userPointsUpdate = $db->query("
                                UPDATE user_points 
                                SET current_streak = 1 
                                WHERE user_id = ?
                            ", [$customerId]);
                        } else {
                            throw new \Exception('Failed to reset current streak');
                        }
                    } else {
                        $message = 'No streak to reset or streak is already at 1 day';
                    }
                    break;
                    
                case 'monthly_progress':
                    // Start transaction for monthly reset
                    $db->transStart();
                    
                    // Count current month checkins before deletion
                    $countQuery = $db->query("
                        SELECT COUNT(*) as count 
                        FROM customer_checkins 
                        WHERE customer_id = ? 
                        AND MONTH(checkin_date) = MONTH(CURRENT_DATE())
                        AND YEAR(checkin_date) = YEAR(CURRENT_DATE())
                    ", [$customerId]);
                    $currentMonthCount = $countQuery->getRowArray()['count'];
                    
                    // Reset current month checkins
                    $monthlyResetQuery = $db->query("
                        DELETE FROM customer_checkins 
                        WHERE customer_id = ? 
                        AND MONTH(checkin_date) = MONTH(CURRENT_DATE())
                        AND YEAR(checkin_date) = YEAR(CURRENT_DATE())
                    ", [$customerId]);
                    
                    // Also reset checkin_history table if it exists
                    $historyResetQuery = $db->query("
                        DELETE FROM checkin_history 
                        WHERE user_id = ? 
                        AND MONTH(checkin_date) = MONTH(CURRENT_DATE())
                        AND YEAR(checkin_date) = YEAR(CURRENT_DATE())
                    ", [$customerId]);
                    
                    // Update customer monthly count
                    $this->customerModel->update($customerId, ['monthly_checkins' => 0]);
                    
                    // Update user_points table if it exists
                    $userPointsUpdate = $db->query("
                        UPDATE user_points 
                        SET monthly_checkins = 0, current_streak = 0 
                        WHERE user_id = ?
                    ", [$customerId]);
                    
                    $db->transComplete();
                    
                    if ($db->transStatus() === FALSE) {
                        throw new \Exception('Failed to reset monthly progress');
                    }
                    
                    $message = "Monthly progress reset successfully. Removed {$currentMonthCount} check-in records.";
                    break;
                    
                case 'all_history':
                    // Start transaction for complete reset
                    $db->transStart();
                    
                    // Count total checkins before deletion
                    $totalCountQuery = $db->query("
                        SELECT COUNT(*) as count 
                        FROM customer_checkins 
                        WHERE customer_id = ?
                    ", [$customerId]);
                    $totalCount = $totalCountQuery->getRowArray()['count'];
                    
                    // Reset all check-in history
                    $allHistoryQuery1 = $db->query("DELETE FROM customer_checkins WHERE customer_id = ?", [$customerId]);
                    $allHistoryQuery2 = $db->query("DELETE FROM checkin_history WHERE user_id = ?", [$customerId]);
                    
                    // Reset customer counters
                    $this->customerModel->update($customerId, ['monthly_checkins' => 0]);
                    
                    // Reset user_points table if it exists
                    $userPointsUpdate = $db->query("
                        UPDATE user_points 
                        SET monthly_checkins = 0, current_streak = 0, last_checkin_date = NULL 
                        WHERE user_id = ?
                    ", [$customerId]);
                    
                    $db->transComplete();
                    
                    if ($db->transStatus() === FALSE) {
                        throw new \Exception('Failed to reset all history');
                    }
                    
                    $message = "All check-in history reset successfully. Removed {$totalCount} total records.";
                    break;
                    
                default:
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Invalid reset type'
                    ]);
            }
            
            // Log the reset action with more details
            $this->logProfileChanges($customerId, $customer, [
                'checkin_reset_type' => $resetType,
                'reset_by_admin' => $adminId,
                'reset_date' => date('Y-m-d H:i:s'),
                'reset_message' => $message
            ]);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Check-in reset error for customer ' . $customerId . ': ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to reset check-in progress: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get check-in settings from database
     */
    private function getCheckinSettings($customerId = null)
    {
        $db = \Config\Database::connect();
        
        // If customer ID provided, try to get customer-specific settings
        if ($customerId) {
            $customerSettings = $db->query("
                SELECT * FROM customer_checkin_settings 
                WHERE customer_id = ?
            ", [$customerId])->getRowArray();
            
            if ($customerSettings) {
                // Return customer-specific settings
                return [
                    'default_checkin_points' => $customerSettings['default_checkin_points'],
                    'weekly_bonus_multiplier' => $customerSettings['weekly_bonus_multiplier'],
                    'max_streak_days' => $customerSettings['max_streak_days'],
                    'weekend_bonus_points' => $customerSettings['weekend_bonus_points'],
                    'consecutive_bonus_points' => $customerSettings['consecutive_bonus_points'] ?? 5
                ];
            }
        }
        
        // Fallback to global admin settings
        $query = $db->query("
            SELECT setting_key, setting_value 
            FROM admin_settings 
            WHERE setting_key IN (
                'default_checkin_points', 
                'weekly_bonus_multiplier', 
                'max_streak_days', 
                'weekend_bonus_points',
                'consecutive_bonus_points'
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
            'weekend_bonus_points' => 5,
            'consecutive_bonus_points' => 5
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

    /**
     * Log dashboard theme changes
     */
    private function logDashboardThemeChange($customerId, $actionType, $details)
    {
        $db = \Config\Database::connect();
        
        try {
            $logData = [
                'customer_id' => $customerId,
                'admin_id' => $details['admin_id'] ?? null,
                'action_type' => $actionType,
                'old_bg_color' => $details['old_color'] ?? null,
                'new_bg_color' => $details['new_color'] ?? null,
                'old_bg_image' => $details['old_image'] ?? null,
                'new_bg_image' => $details['new_image'] ?? null,
                'change_reason' => $details['reason'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ];
            
            $db->table('dashboard_theme_history')->insert($logData);
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to log theme change: ' . $e->getMessage());
        }
    }
}