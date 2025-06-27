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

        // Check if user won a prize (from session OR from POST data)
        $winnerData = $session->get('winner_data');
        
        // Check if winner data was passed directly via POST (PRIORITY)
        $postedWinnerData = $this->request->getPost('winner_data');
        if ($postedWinnerData) {
            $decodedData = json_decode($postedWinnerData, true);
            if ($decodedData && isset($decodedData['name'])) {
                $winnerData = $decodedData;
                // Store in session for consistency
                $session->set('winner_data', $winnerData);
                log_message('info', 'Winner data received via POST redirect');
            }
        }
        
        // Only try API if we don't have winner data from POST
        $apiToken = $this->request->getPost('api_token') ?? $this->request->getGet('api_token');
        $apiUrl = $this->request->getPost('api_url') ?? $this->request->getGet('api_url');
        
        if (!$winnerData && $apiToken && $apiUrl) {
            $winnerData = $this->fetchWinnerDataFromApi($apiUrl, $apiToken);
            
            // Store fetched data in session for consistency
            if ($winnerData) {
                $session->set('winner_data', $winnerData);
            }
        }
        
        // Check for customer login
        $customerId = $session->get('customer_id');
        $customerLoggedIn = $session->get('customer_logged_in');
        $customerData = null;
        
        if ($customerId && $customerLoggedIn) {
            $customerData = $this->customerModel->find($customerId);
            if (!$customerData) {
                // Customer not found, clear session
                $session->remove(['customer_id', 'customer_logged_in', 'customer_data']);
                $customerLoggedIn = false;
            }
        }
        
        $data = [
            'title' => 'Claim Your Reward',
            'winner_data' => $winnerData,
            'logged_in' => $customerLoggedIn && !empty($customerData),
            'customer_data' => $customerData,
            'has_prize_data' => !empty($winnerData) && is_array($winnerData) && isset($winnerData['name']),
            'show_dashboard_access' => true,
            'api_redirect' => !empty($apiToken)
        ];

        return view('public/reward_system', $data);
    }

    /**
     * NEW: Fetch winner data from API
     */
    private function fetchWinnerDataFromApi($apiUrl, $apiToken)
    {
        try {
            log_message('error', '=== CURL REQUEST DEBUG ===');
            log_message('error', 'Making cURL request to: ' . $apiUrl);
            log_message('error', 'With token: ' . substr($apiToken, 0, 10) . '...');
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'api_token' => $apiToken,
                    'clear_session' => 'true'
                ]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,  // Add this for local SSL issues
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'User-Agent: RewardSystem/1.0'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            log_message('error', 'cURL response code: ' . $httpCode);
            log_message('error', 'cURL response: ' . substr($response, 0, 500));
            log_message('error', 'cURL error: ' . ($curlError ?: 'none'));
            log_message('error', '=== END CURL DEBUG ===');
            log_message('info', 'API Response: ' . $response);
            
            if ($curlError) {
                log_message('error', 'API cURL Error: ' . $curlError);
                return null;
            }
            
            if ($httpCode !== 200) {
                log_message('error', 'API HTTP Error: ' . $httpCode);
                return null;
            }
            
            $apiData = json_decode($response, true);
            
            if ($apiData && $apiData['success']) {
                // Convert API data format to session format
                $winnerData = [
                    'name' => $apiData['data']['prize_name'],
                    'prize' => floatval($apiData['data']['prize_value']),
                    'type' => $apiData['data']['prize_type'],
                    'timestamp' => strtotime($apiData['data']['won_at']),
                    'session_id' => $apiData['data']['session_id'],
                    'user_ip' => $apiData['data']['user_ip']
                ];
                
                log_message('info', 'Successfully fetched winner data: ' . json_encode($winnerData));
                return $winnerData;
            } else {
                log_message('error', 'API returned error: ' . ($apiData['message'] ?? 'Unknown error'));
                return null;
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Exception fetching winner data: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Auto register new customer
     */
    public function autoRegister()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/reward');
        }

        $session = session();

        try {
            // Create auto customer
            $result = $this->customerModel->createAutoCustomer();
            
            if ($result) {
                // AUTOMATICALLY LOG IN THE USER
                $session->set([
                    'customer_id' => $result['id'],
                    'customer_logged_in' => true,
                    'customer_data' => [
                        'id' => $result['id'],
                        'username' => $result['username'],
                        'name' => $result['username'],
                        'phone' => $result['username'],
                        'points' => 0,
                    ]
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Account created!',
                    'customer_data' => [
                        'username' => $result['username'],
                        'password' => $result['password'],
                        'id' => $result['id']
                    ],
                    'auto_logged_in' => true, // Flag to show user is now logged in
                    'redirect' => base_url('customer/dashboard') 
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create account. Please try again.'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Auto registration error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ]);
        }
    }

    /**
     * Customer login for existing users
     */
    public function login()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/reward');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'username' => 'required|min_length[3]|max_length[50]',
            'password' => 'required|min_length[4]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please fill in all required fields correctly.',
                'errors' => $validation->getErrors()
            ]);
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        
        // Debug logging
        log_message('info', 'Login attempt for username: ' . $username);
        log_message('info', 'Password provided length: ' . strlen($password));
        
        try {
            // Find customer by username
            $customer = $this->customerModel->where('username', $username)
                                        ->where('is_active', 1)
                                        ->first();
            
            if (!$customer) {
                log_message('info', 'Customer not found: ' . $username);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid username or password.'
                ]);
            }
            
            log_message('info', 'Customer found, stored password hash: ' . substr($customer['password'], 0, 20) . '...');
            
            // Verify password
            $passwordValid = password_verify($password, $customer['password']);
            log_message('info', 'Password verification result: ' . ($passwordValid ? 'VALID' : 'INVALID'));
            
            if ($passwordValid) {
                // Update last login
                $this->customerModel->update($customer['id'], [
                    'last_login' => date('Y-m-d H:i:s')
                ]);
                
                // Set session data
                session()->set([
                    'customer_id' => $customer['id'],
                    'customer_logged_in' => true,
                    'customer_data' => [
                        'id' => $customer['id'],
                        'username' => $customer['username'],
                        'name' => $customer['name'] ?? $customer['username'],
                        'phone' => $customer['phone'] ?? $customer['username'],
                        'points' => $customer['points'] ?? 0,
                    ]
                ]);

                log_message('info', 'Login successful for customer ID: ' . $customer['id']);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Login successful!',
                    'redirect' => base_url('customer/dashboard'),
                ]);
            }
            
            log_message('info', 'Password verification failed for username: ' . $username);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid username or password.'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Login failed. Please try again.'
            ]);
        }
    }

    /**
     * Claim reward after authentication
     */
    public function claimReward()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/reward');
        }

        $session = session();
        
        // Check if customer is logged in
        if (!$session->get('customer_logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please login first to claim your reward.'
            ]);
        }

        // Check winner data
        $winnerData = $session->get('winner_data');
        if (!$winnerData) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No prize data found. Please spin the wheel first.'
            ]);
        }

        $platform = $this->request->getPost('platform') ?: 'whatsapp';
        $customerData = $session->get('customer_data');

        // Validate customer data
        if (!$customerData || !isset($customerData['id'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid customer session. Please login again.'
            ]);
        }

        try {
            // Insert bonus claim record
            $claimData = [
                'session_id' => session_id(),
                'user_ip' => $this->request->getIPAddress(),
                'user_name' => $customerData['username'],
                'customer_id' => $customerData['id'],
                'phone_number' => $customerData['phone'] ?? $customerData['username'],
                'email' => null,
                'bonus_type' => $winnerData['name'],
                'bonus_amount' => isset($winnerData['prize']) ? floatval($winnerData['prize']) : 0.00,
                'claim_time' => date('Y-m-d H:i:s'),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'status' => 'pending',
                'platform_selected' => $platform,
                'account_type' => 'manual'
            ];

            $claimId = $this->bonusClaimModel->insert($claimData);

            if ($claimId) {
                // Clear winner data from session after successful claim
                $session->remove('winner_data');

                // Get platform settings dynamically from admin settings
                $whatsappNumber = $this->adminSettingsModel->getSetting('reward_whatsapp_number', '601159599022');
                $telegramUsername = $this->adminSettingsModel->getSetting('reward_telegram_username', 'harryford19');

                // Create message for platform
                $message = "🎉 I just won {$winnerData['name']}!\nMy User ID is: {$customerData['username']}.\nClaim ID: {$claimId}";
                $encodedMessage = urlencode($message);
                
                // Platform URLs
                $whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$encodedMessage}";
                $telegramUrl = "https://t.me/{$telegramUsername}?text={$encodedMessage}";
                
                $redirectUrl = $platform === 'telegram' ? $telegramUrl : $whatsappUrl;
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Claim submitted successfully! Redirecting to ' . ucfirst($platform) . '...',
                    'redirect_url' => $redirectUrl,
                    'claim_id' => $claimId
                ]);
            }
            
            throw new \Exception('Failed to process claim');
            
        } catch (\Exception $e) {
            log_message('error', 'Claim reward error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to process claim. Please try again.'
            ]);
        }
    }

    /**
     * Customer logout
     */
    public function logout()
    {
        $session = session();
        
        // Clear customer session data but keep winner_data for potential re-login
        $session->remove([
            'customer_id',
            'customer_data', 
            'customer_logged_in'
        ]);
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Logged out successfully.'
            ]);
        }
        
        return redirect()->to('/reward')->with('success', 'Logged out successfully.');
    }

    /**
     * Test method to debug session data
     */
    public function test()
    {
        $session = session();
        
        $data = [
            'session_id' => session_id(),
            'winner_data' => $session->get('winner_data'),
            'customer_id' => $session->get('customer_id'),
            'customer_logged_in' => $session->get('customer_logged_in'),
            'customer_data' => $session->get('customer_data'),
            'all_session_data' => $session->get()
        ];
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON($data);
        }
        
        echo '<pre>' . print_r($data, true) . '</pre>';
    }

    private function setCorsHeaders()
    {
        // FIXED: Set more specific CORS headers for security
        $allowedOrigins = [
            'https://clientzone.kopisugar.cc',
            'https://rewardcheckin.kopisugar.cc',
        ];
        
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array($origin, $allowedOrigins)) {
            $this->response->setHeader('Access-Control-Allow-Origin', $origin);
        }
        
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
        $this->response->setHeader('Access-Control-Max-Age', '86400'); // Cache preflight for 24 hours
    }
}