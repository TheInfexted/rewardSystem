<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\BonusClaimModel;
use App\Models\AdminSettingsModel;

class RewardController extends BaseController
{
    protected $customerModel;
    protected $bonusClaimModel;
    protected $adminSettingsModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->bonusClaimModel = new BonusClaimModel();
        $this->adminSettingsModel = new AdminSettingsModel();
    }

    /**
     * Display reward system page
     */
    public function index()
    {
        $session = session();
        
        // Check if user won a prize (from session)
        $winnerData = $session->get('winner_data');
        
        // Check for customer login
        $customerId = $session->get('customer_id');
        $customerData = null;
        
        if ($customerId) {
            $customerData = $this->customerModel->find($customerId);
        }
        
        $data = [
            'title' => 'Claim Your Reward',
            'winner_data' => $winnerData,
            'logged_in' => !empty($customerData),
            'customer_data' => $customerData,
            'no_prize_data' => empty($winnerData)
        ];

        return view('public/reward_system', $data);
    }

    /**
     * Auto-generate and register new customer
     */
    public function autoRegister()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/');
        }

        try {
            // Generate unique username
            $username = $this->customerModel->generateUniqueUsername();
            
            // Generate random password (8 characters)
            $password = bin2hex(random_bytes(4)); // 8 character hex string
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Create customer data
            $customerData = [
                'username' => $username,
                'password' => $hashedPassword,
                'name' => $username,
                'profile_background' => 'default',
                'spin_count' => 0,
                'points' => 0,
                'is_active' => 1
            ];
            
            // Insert customer
            $customerId = $this->customerModel->insert($customerData);
            
            if ($customerId) {
                // Set session data
                session()->set([
                    'customer_id' => $customerId,
                    'customer_data' => [
                        'id' => $customerId,
                        'username' => $username,
                        'name' => $username
                    ],
                    'customer_logged_in' => true
                ]);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Account created successfully!',
                    'credentials' => [
                        'username' => $username,
                        'password' => $password
                    ]
                ]);
            }
            
            throw new \Exception('Failed to create account');
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create account: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Handle reward claim selection (WhatsApp or Telegram)
     */
    public function claimReward()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/reward');
        }

        $session = session();
        $customerId = $session->get('customer_id');
        $winnerData = $session->get('winner_data');
        $platform = $this->request->getPost('platform'); // 'whatsapp' or 'telegram'
        
        if (!$customerId || !$winnerData) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid session or no prize data'
            ]);
        }

        try {
            // Get customer data
            $customer = $this->customerModel->find($customerId);
            
            // Create bonus claim record
            $claimData = [
                'session_id' => session_id(),
                'user_ip' => $this->request->getIPAddress(),
                'user_name' => $customer['username'],
                'customer_id' => $customerId,
                'email' => $customer['email'] ?? null,
                'bonus_type' => $winnerData['name'] ?? 'Unknown',
                'bonus_amount' => $winnerData['prize'] ?? 0,
                'claim_time' => date('Y-m-d H:i:s'),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'status' => 'pending',
                'account_type' => 'auto',
                'platform_selected' => $platform
            ];
            
            $claimId = $this->bonusClaimModel->insert($claimData);
            
            if ($claimId) {
                // Clear winner data from session
                $session->remove('winner_data');
                
                // Generate message for WhatsApp/Telegram
                $message = "Hello! I just won {$winnerData['name']}. My User ID is: {$customer['username']}. Claim ID: {$claimId}";
                $encodedMessage = urlencode($message);
                
                // Set default URLs (to be updated by admin)
                $whatsappUrl = "https://wa.me/60102763672?text={$encodedMessage}";
                $telegramUrl = "https://t.me/brendxn1127?text={$encodedMessage}";
                
                $redirectUrl = $platform === 'telegram' ? $telegramUrl : $whatsappUrl;
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Redirecting to ' . ucfirst($platform) . '...',
                    'redirect_url' => $redirectUrl,
                    'dashboard_url' => base_url('customer/dashboard')
                ]);
            }
            
            throw new \Exception('Failed to process claim');
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Manual customer login
     */
    public function login()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/reward');
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        
        $customer = $this->customerModel->getByUsername($username);
        
        if ($customer && password_verify($password, $customer['password'])) {
            // Set session
            session()->set([
                'customer_id' => $customer['id'],
                'customer_data' => [
                    'id' => $customer['id'],
                    'username' => $customer['username'],
                    'name' => $customer['name'] ?? $customer['username']
                ],
                'customer_logged_in' => true
            ]);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Login successful!'
            ]);
        }
        
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
    }
}