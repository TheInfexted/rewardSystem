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
    
    public function manageTokens($customerId)
    {
        $customer = $this->customerModel->find($customerId);
        
        if (!$customer) {
            return redirect()->to('/admin/customers')->with('error', 'Customer not found');
        }
        
        $data = [
            'title' => 'Manage Spin Tokens',
            'customer' => $customer,
            'history' => $this->customerModel->getTokensHistory($customerId)
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
        
        $result = false;
        
        if ($action === 'add') {
            $result = $this->customerModel->addSpinTokens($customerId, $amount, $adminId, $reason);
        } elseif ($action === 'remove') {
            $result = $this->customerModel->removeSpinTokens($customerId, $amount, $adminId, $reason);
        }
        
        if ($result) {
            $customer = $this->customerModel->find($customerId);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tokens updated successfully',
                'new_balance' => $customer['spin_tokens']
            ]);
        }
        
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to update tokens'
        ]);
    }
}