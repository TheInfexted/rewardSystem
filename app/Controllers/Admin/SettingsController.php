<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class SettingsController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
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
    
        $data = [
            'title' => 'Settings',
            'user' => $user
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
        
        $data = [
            'password' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT)
        ];
        
        if ($this->userModel->update($userId, $data)) {
            return redirect()->back()->with('password_success', 'Password updated successfully.');
        }
        
        return redirect()->back()->with('password_error', 'Failed to update password.');
    }
}