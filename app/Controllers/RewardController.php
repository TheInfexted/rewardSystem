<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\BonusClaimModel;
use App\Models\AdminSettingsModel;

class RewardController extends BaseController
{
    protected $userModel;
    protected $bonusClaimModel;
    protected $adminSettingsModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
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
        
        // Enhanced login detection - check multiple session keys
        $userId = $session->get('user_id');
        $userData = $session->get('user_data');
        $loggedInFlag = $session->get('logged_in');
        
        // If we have user_id but no user_data, fetch it
        if ($userId && !$userData) {
            $userData = $this->userModel->find($userId);
            if ($userData) {
                $session->set('user_data', $userData);
                $session->set('logged_in', true);
                log_message('debug', 'Retrieved and stored user data for user_id: ' . $userId);
            }
        }
        
        // Determine if user is logged in
        $isLoggedIn = ($userId && $userData) || $loggedInFlag;
        
        $data = [
            'title' => 'Claim Your Reward',
            'winner_data' => $winnerData, // Can be null
            'logged_in' => $isLoggedIn,
            'user_data' => $userData,
            'no_prize_data' => empty($winnerData)
        ];

        return view('public/reward_system', $data);
    }

    /**
     * Auto-generate and register new user
     */
    public function autoRegister()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/');
        }

        try {
            // Generate unique username (01 + 8 random digits)
            $username = $this->generateUniqueUsername();
            
            // Generate random password (8 characters)
            $password = $this->generateRandomPassword();
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Create user data
            $userData = [
                'name' => $username,
                'email' => $username . '@rewardsystem.local', // Temporary email
                'password' => $hashedPassword,
                'language_preference' => 'en',
                'user_token' => md5(uniqid(rand(), true))
            ];

            // Insert user
            $userId = $this->userModel->insert($userData);

            if ($userId) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Account created successfully!',
                    'account_details' => [
                        'username' => $username,
                        'password' => $password,
                        'user_id' => $userId
                    ]
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create account. Please try again.'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Auto registration error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while creating your account.'
            ]);
        }
    }

    /**
     * Auto-login with generated credentials
     */
    public function autoLogin()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/');
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (empty($username) || empty($password)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid credentials provided.'
            ]);
        }

        try {
            // Find user by username (name field)
            $user = $this->userModel->where('name', $username)->first();

            if ($user && password_verify($password, $user['password'])) {
                // Set session data
                $session = session();
                $sessionData = [
                    'user_id' => $user['id'],
                    'user_name' => $user['name'],
                    'user_email' => $user['email'],
                    'logged_in' => true,
                    'user_data' => $user
                ];

                $session->set($sessionData);

                // Update last login
                $this->userModel->update($user['id'], [
                    'last_login' => date('Y-m-d H:i:s')
                ]);

                // Now process the bonus claim automatically
                $claimResult = $this->processAutoClaim($session);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Login successful!',
                    'claim_result' => $claimResult
                ]);

            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid username or password.'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Auto login error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Login failed. Please try again.'
            ]);
        }
    }

    /**
     * Manual login for existing users
     */
    public function login()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/');
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (empty($username) || empty($password)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please enter both username and password.'
            ]);
        }

        try {
            $user = $this->userModel->where('name', $username)
                                   ->orWhere('email', $username)
                                   ->first();

            if ($user && password_verify($password, $user['password'])) {
                $session = session();
                $sessionData = [
                    'user_id' => $user['id'],
                    'user_name' => $user['name'],
                    'user_email' => $user['email'],
                    'logged_in' => true,
                    'user_data' => $user
                ];

                $session->set($sessionData);

                $this->userModel->update($user['id'], [
                    'last_login' => date('Y-m-d H:i:s')
                ]);

                $claimResult = $this->processAutoClaim($session);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Login successful!',
                    'claim_result' => $claimResult
                ]);

            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid username or password.'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Login failed. Please try again.'
            ]);
        }
    }

    /**
     * Process automatic bonus claim after login
     */
    private function processAutoClaim($session)
    {
        try {
            log_message('debug', 'processAutoClaim: Starting...');
            
            $winnerData = $session->get('winner_data');
            
            // Try multiple ways to get user data
            $user = $session->get('user_data');
            $userId = $session->get('user_id');
            
            log_message('debug', 'processAutoClaim: Initial data check - winnerData=' . ($winnerData ? 'exists' : 'null') . ', user=' . ($user ? 'exists' : 'null') . ', userId=' . $userId);
            
            // If no user_data but we have user_id, fetch the user
            if (!$user && $userId) {
                log_message('debug', 'processAutoClaim: Fetching user for userId=' . $userId);
                $user = $this->userModel->find($userId);
                if ($user) {
                    $session->set('user_data', $user);
                    log_message('debug', 'processAutoClaim: Retrieved user data from user_id: ' . $userId);
                } else {
                    log_message('error', 'processAutoClaim: User not found in database for userId=' . $userId);
                }
            }
            
            // Final validation
            if (!$user && !$userId) {
                log_message('error', 'processAutoClaim: No user data found in session');
                return [
                    'success' => false, 
                    'message' => 'User session not found. Please login again.',
                    'debug' => [
                        'user_data' => $user,
                        'user_id' => $userId,
                        'session_keys' => array_keys($session->get() ?? [])
                    ]
                ];
            }
            
            if (!$winnerData) {
                log_message('error', 'processAutoClaim: No winner data found');
                return [
                    'success' => false, 
                    'message' => 'No prize data found. Please spin the wheel first.',
                    'redirect_to_game' => true
                ];
            }

            $userIp = $session->get('user_ip') ?? $this->getUserIp();
            log_message('debug', 'processAutoClaim: User IP=' . $userIp);

            // Check if user has already claimed bonus today
            $hasClaimedToday = $this->bonusClaimModel->hasClaimedToday($userIp);
            log_message('debug', 'processAutoClaim: hasClaimedToday=' . ($hasClaimedToday ? 'true' : 'false'));
            
            if ($hasClaimedToday) {
                log_message('warning', 'processAutoClaim: User already claimed bonus today');
                return [
                    'success' => false, 
                    'message' => 'You have already claimed your bonus today.'
                ];
            }

            // Prepare claim data - handle both user object and array
            $userName = is_array($user) ? $user['name'] : $user->name ?? 'Unknown';
            $userEmail = is_array($user) ? $user['email'] : $user->email ?? '';
            
            $claimData = [
                'session_id' => $session->get('session_id') ?? session_id(),
                'user_ip' => $userIp,
                'user_name' => $userName,
                'phone_number' => 'N/A', // No phone required in new flow
                'email' => $userEmail,
                'bonus_type' => $winnerData['name'],
                'bonus_amount' => $winnerData['prize'] ?? 0.00,
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'status' => 'pending'
            ];

            log_message('debug', 'processAutoClaim: Prepared claim data: ' . json_encode($claimData));

            $claimId = $this->bonusClaimModel->insert($claimData);
            log_message('debug', 'processAutoClaim: Insert result - claimId=' . $claimId);

            if ($claimId) {
                log_message('debug', 'processAutoClaim: Claim inserted successfully, marking bonus as claimed...');
                
                // Mark bonus as claimed in spin history
                $this->markBonusAsClaimed($session, $claimId);

                // Clear winner data from session
                $session->remove('winner_data');
                log_message('debug', 'processAutoClaim: Winner data cleared from session');

                // Get redirect URL
                $redirectUrl = $this->adminSettingsModel->getSetting('bonus_redirect_url', '/');
                log_message('debug', 'processAutoClaim: Redirect URL=' . $redirectUrl);

                log_message('info', 'processAutoClaim: SUCCESS - Bonus claimed: ID=' . $claimId . ', User=' . $userName);

                return [
                    'success' => true,
                    'message' => 'Bonus claimed successfully!',
                    'claim_id' => $claimId,
                    'redirect_url' => $redirectUrl
                ];
            } else {
                log_message('error', 'processAutoClaim: Failed to insert claim into database');
                $errors = $this->bonusClaimModel->errors();
                log_message('error', 'processAutoClaim: Model errors: ' . json_encode($errors));
                
                return [
                    'success' => false, 
                    'message' => 'Failed to process claim - database error',
                    'debug' => [
                        'model_errors' => $errors,
                        'claim_data' => $claimData
                    ]
                ];
            }

        } catch (\Exception $e) {
            log_message('error', 'processAutoClaim: Exception - ' . $e->getMessage());
            log_message('error', 'processAutoClaim: Stack trace - ' . $e->getTraceAsString());
            return [
                'success' => false, 
                'message' => 'Failed to process bonus claim: ' . $e->getMessage(),
                'debug' => [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            ];
        }
    }

    /**
     * Handle reward claim request
     */
    public function claim()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/reward');
        }

        $session = session();
        $winnerData = $session->get('winner_data');
        
        // Enhanced debugging
        $debugInfo = [
            'session_id' => session_id(),
            'winner_data' => $winnerData,
            'user_id' => $session->get('user_id'),
            'user_data' => $session->get('user_data'),
            'logged_in' => $session->get('logged_in'),
            'all_session_keys' => array_keys($session->get() ?? [])
        ];
        
        log_message('debug', 'Claim attempt - Full session debug: ' . json_encode($debugInfo));

        if (!$winnerData) {
            log_message('error', 'Claim failed: No winner data');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No prize data found. Please spin the wheel first.',
                'redirect_to_game' => true,
                'debug' => $debugInfo
            ]);
        }

        // Check for user login - be more flexible with session detection
        $user = $session->get('user_data');
        $userId = $session->get('user_id');
        $isLoggedIn = $session->get('logged_in');
        
        log_message('debug', 'User detection: user_data=' . ($user ? 'exists' : 'null') . ', user_id=' . $userId . ', logged_in=' . ($isLoggedIn ? 'true' : 'false'));
        
        if (!$user && !$userId && !$isLoggedIn) {
            log_message('error', 'Claim failed: No user session found');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not logged in. Please login first.',
                'debug' => $debugInfo
            ]);
        }

        // If we have user_id but no user_data, fetch it
        if ($userId && !$user) {
            log_message('debug', 'Fetching user data for user_id: ' . $userId);
            $user = $this->userModel->find($userId);
            if ($user) {
                $session->set('user_data', $user);
                log_message('debug', 'User data retrieved and stored');
            } else {
                log_message('error', 'User not found in database for user_id: ' . $userId);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User account not found.',
                    'debug' => $debugInfo
                ]);
            }
        }

        // Process the claim with detailed logging
        log_message('debug', 'Starting processAutoClaim...');
        $claimResult = $this->processAutoClaim($session);
        log_message('debug', 'processAutoClaim result: ' . json_encode($claimResult));

        return $this->response->setJSON($claimResult);
    }

    /**
     * Generate unique username starting with 01 (10 digits total)
     */
    private function generateUniqueUsername()
    {
        do {
            // Generate 01 + 8 random digits
            $username = '01' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            
            // Check if username already exists
            $existingUser = $this->userModel->where('name', $username)->first();
            
        } while ($existingUser);

        return $username;
    }

    /**
     * Generate random password
     */
    private function generateRandomPassword($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $password;
    }

    /**
     * Mark bonus as claimed in spin history
     */
    private function markBonusAsClaimed($session, $claimId)
    {
        try {
            $db = \Config\Database::connect();
            $sessionId = $session->get('session_id') ?? session_id();

            $db->table('spin_history')
                ->where('session_id', $sessionId)
                ->where('item_won LIKE', '%BONUS%')
                ->where('DATE(spin_time)', date('Y-m-d'))
                ->set([
                    'bonus_claimed' => 1,
                    'claim_id' => $claimId,
                    'claimed' => 1
                ])
                ->update();

        } catch (\Exception $e) {
            log_message('error', 'Failed to mark bonus as claimed: ' . $e->getMessage());
        }
    }

    /**
     * Get real user IP
     */
    private function getUserIp()
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            return $_SERVER['HTTP_X_FORWARDED'];
        } elseif (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }
}