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
        $reason = $this->request->getPost('reason');
        
        $adminId = session()->get('user_id');
        
        if ($amount <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Amount must be greater than 0'
            ]);
        }
        
        $customer = $this->customerModel->find($customerId);
        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Customer not found'
            ]);
        }
        
        $currentTokens = $customer['spin_tokens'];
        $newTokens = $currentTokens;
        
        if ($action === 'add') {
            $newTokens = $currentTokens + $amount;
        } elseif ($action === 'remove') {
            $newTokens = max(0, $currentTokens - $amount);
        }
        
        // Update tokens
        $result = $this->customerModel->update($customerId, ['spin_tokens' => $newTokens]);
        
        if ($result) {
            // Log the transaction
            $this->logTokenTransaction($customerId, $action, $amount, $currentTokens, $newTokens, $adminId, $reason);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tokens updated successfully',
                'new_balance' => $newTokens
            ]);
        }
        
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to update tokens'
        ]);
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
}