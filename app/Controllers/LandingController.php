<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LandingPageModel;
use App\Models\WheelItemsModel;
use App\Models\BonusClaimModel;
use App\Models\AdminSettingsModel;
use App\Models\LandingPageMusicModel;
use App\Models\WheelSoundModel;
use App\Models\CustomerModel;

class LandingController extends BaseController
{
    protected $landingPageModel;
    protected $wheelItemsModel;
    protected $bonusClaimModel;
    protected $adminSettingsModel;
    protected $landingPageMusicModel;
    protected $wheelSoundModel;
    protected $customerModel;

    public function __construct()
    {
        $this->landingPageModel = new LandingPageModel();
        $this->wheelItemsModel = new WheelItemsModel();
        $this->bonusClaimModel = new BonusClaimModel();
        $this->adminSettingsModel = new AdminSettingsModel();
        $this->landingPageMusicModel = new LandingPageMusicModel();
        $this->wheelSoundModel = new WheelSoundModel();
        $this->customerModel = new CustomerModel();
        
        // Set user IP in session when controller is loaded
        $this->setUserIpInSession();
    }

    public function index()
    {
        // Get session to track spins
        $session = session();
        
        // Initialize session spins if not set
        if (!$session->has('spins_today')) {
            $session->set('spins_today', 0);
            $session->set('spin_date', date('Y-m-d'));
        }
        
        // Reset spins if it's a new day
        if ($session->get('spin_date') != date('Y-m-d')) {
            $session->set('spins_today', 0);
            $session->set('spin_date', date('Y-m-d'));
        }
        
        $spinsToday = $session->get('spins_today');
        $spinsRemaining = max(0, 3 - $spinsToday);
        
        // Get bonus settings
        $bonusSettings = $this->adminSettingsModel->getBonusSettings();
        
        $data = [
            'page_data' => $this->landingPageModel->getActiveData(),
            'wheel_items' => $this->wheelItemsModel->getWheelItems(),
            'spins_remaining' => $spinsRemaining,
            'recent_wins' => [],
            'bonus_settings' => $bonusSettings,
            'music_data' => $this->landingPageMusicModel->getActiveMusic(),
            'spin_sound' => $this->wheelSoundModel->getActiveSound('spin'),
            'win_sound' => $this->wheelSoundModel->getActiveSound('win'),
        ];
    
        return view('public/landing_page', $data);
    }

    public function spin()
    {
        if ($this->request->isAJAX()) {
            $session = session();
            
            $customerId = $session->get('customer_id');

            // Check if customer is logged in
            if ($customerId) {
                // Customer spin - use tokens
                $customer = $this->customerModel->find($customerId);
                
                if (!$customer || ($customer['spin_tokens'] ?? 0) <= 0) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No spin tokens available',
                        'spins_remaining' => 0
                    ]);
                }
                
                // Use a spin token
                $tokenUsed = $this->customerModel->useSpinToken($customerId);
                
                if (!$tokenUsed) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to use spin token',
                        'spins_remaining' => $customer['spin_tokens']
                    ]);
                }

                // Get updated token balance
                $updatedCustomer = $this->customerModel->find($customerId);
                $spinsToday = 0; // no session tracking needed for logged-in
                $remainingTokens = $updatedCustomer['spin_tokens'] ?? 0;

            } else {
                // Guest user - use session-based spins
                if ($session->get('spin_date') != date('Y-m-d')) {
                    $session->set('spins_today', 0);
                    $session->set('spin_date', date('Y-m-d'));
                }

                $spinsToday = $session->get('spins_today') ?? 0;
                
                $maxSpinsPerDay = $this->adminSettingsModel->getSetting('max_daily_spins', 3);

                if ($spinsToday >= $maxSpinsPerDay) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'You have used all your free spins for today!',
                        'spins_remaining' => 0
                    ]);
                }

                $session->set('spins_today', $spinsToday + 1);
                $remainingTokens = $maxSpinsPerDay - ($spinsToday + 1);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'spins_remaining' => $remainingTokens,
                'predetermined' => false
            ]);
        }
        
        return redirect()->to('/');
    }

    /**
     * Handle bonus claim form submission
     */
    public function claimBonus()
    {
        if ($this->request->isAJAX()) {
            $session = session();
            $userIp = $session->get('user_ip') ?? $this->getUserIp();

            // Validate that bonus claiming is enabled
            $claimEnabled = $this->adminSettingsModel->getSetting('bonus_claim_enabled', '1');
            if ($claimEnabled !== '1') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Bonus claiming is currently disabled.'
                ]);
            }

            // Check if user has already claimed bonus today
            if ($this->bonusClaimModel->hasClaimedToday($userIp)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You have already claimed your bonus today.'
                ]);
            }

            // Get form data
            $postData = [
                'user_name' => trim($this->request->getPost('user_name')),
                'phone_number' => trim($this->request->getPost('phone_number')),
                'email' => trim($this->request->getPost('email')),
                'bonus_type' => $this->request->getPost('bonus_type', FILTER_SANITIZE_STRING),
                'bonus_amount' => $this->request->getPost('bonus_amount', FILTER_VALIDATE_FLOAT)
            ];

            // Server-side validation
            if (empty($postData['user_name']) || strlen($postData['user_name']) < 2) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Please enter a valid name (minimum 2 characters).'
                ]);
            }

            if (empty($postData['phone_number']) || strlen($postData['phone_number']) < 10) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Please enter a valid phone number (minimum 10 digits).'
                ]);
            }

            if (!empty($postData['email']) && !filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Please enter a valid email address.'
                ]);
            }

            // Prepare claim data
            $claimData = [
                'session_id' => $session->get('session_id') ?? session_id(),
                'user_ip' => $userIp,
                'user_name' => $postData['user_name'],
                'phone_number' => $postData['phone_number'],
                'email' => $postData['email'] ?: null,
                'bonus_type' => $postData['bonus_type'] ?: '120% BONUS',
                'bonus_amount' => $postData['bonus_amount'] ?: 0.00,
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'status' => 'pending'
            ];

            try {
                // Insert claim record
                $claimId = $this->bonusClaimModel->insert($claimData);

                if ($claimId) {
                    // Update spin history to mark bonus as claimed
                    $this->markBonusAsClaimed($session, $claimId);

                    // Get redirect URL
                    $redirectUrl = $this->adminSettingsModel->getSetting('bonus_redirect_url', '/');

                    log_message('info', 'Bonus claimed: ID=' . $claimId . ', IP=' . $userIp . ', Name=' . $postData['user_name']);

                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Bonus claimed successfully!',
                        'claim_id' => $claimId,
                        'redirect_url' => $redirectUrl
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to process claim. Please try again.'
                    ]);
                }

            } catch (\Exception $e) {
                log_message('error', 'Bonus claim error: ' . $e->getMessage());
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'An error occurred while processing your claim.'
                ]);
            }
        }

        return redirect()->to('/');
    }

    /**
     * Mark bonus as claimed in spin history
     */
    private function markBonusAsClaimed($session, $claimId)
    {
        try {
            $db = \Config\Database::connect();
            $sessionId = $session->get('session_id') ?? session_id();

            // Update the most recent bonus spin for this session
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
     * Get real user IP with Cloudflare support
     */
    private function getUserIp()
    {
        $ip = '0.0.0.0'; // Default fallback
        
        // Check Cloudflare first (most reliable)
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
            log_message('debug', 'IP from Cloudflare: ' . $ip);
        }
        // Check standard proxy headers
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
            log_message('debug', 'IP from X-Forwarded-For: ' . $ip);
        }
        // Check other proxy headers
        elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
            log_message('debug', 'IP from X-Forwarded: ' . $ip);
        }
        elseif (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
            log_message('debug', 'IP from X-Cluster-Client-IP: ' . $ip);
        }
        // Direct connection
        elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
            log_message('debug', 'IP from REMOTE_ADDR: ' . $ip);
        }
        
        // Validate IP
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            log_message('warning', 'Invalid IP detected, using fallback: ' . $ip);
        }
        
        log_message('info', 'Final IP captured: ' . $ip);
        return $ip;
    }
    
    /**
     * Set user IP in session
     */
    private function setUserIpInSession()
    {
        $session = session();
        $ip = $this->getUserIp();
        $session->set('ip', $ip);
        $session->set('user_ip', $ip);
        
        log_message('info', 'User IP detected: ' . $ip);
        
        return $ip;
    }
    
    /**
     * Log spin result to database with proper IP tracking
     */
    private function logSpinResult($winner, $session)
    {
        try {
            $db = \Config\Database::connect();
            
            // Get or create session ID
            $sessionId = $session->get('session_id');
            if (!$sessionId) {
                $sessionId = session_id();
                $session->set('session_id', $sessionId);
            }
            
            // Get user IP (make sure this method works)
            $userIp = $this->getUserIp();
            
            // Also store in session for consistency
            $session->set('user_ip', $userIp);
            
            // Get current spin number
            $currentSpinNumber = $session->get('spins_today');
            
            // Prepare basic spin data (that definitely exists in table)
            $spinData = [
                'session_id' => $sessionId,
                'user_ip' => $userIp,
                'spin_time' => date('Y-m-d H:i:s'),
                'item_won' => $winner['item_name'] ?? $winner['name'] ?? 'Unknown',
                'prize_amount' => floatval($winner['item_prize'] ?? $winner['prize'] ?? 0.00),
                'prize_type' => $winner['item_types'] ?? $winner['type'] ?? 'cash',
                'claimed' => 0
            ];
            
            // Check which columns exist in the table to avoid errors
            $tableColumns = $db->getFieldNames('spin_history');
            
            // Add optional columns only if they exist
            if (in_array('user_agent', $tableColumns)) {
                $userAgent = $this->request->getUserAgent();
                if ($userAgent) {
                    $spinData['user_agent'] = substr($userAgent->getAgentString(), 0, 500);
                }
            }
            
            if (in_array('spin_number', $tableColumns)) {
                $spinData['spin_number'] = $currentSpinNumber;
            }
            
            if (in_array('bonus_claimed', $tableColumns)) {
                $spinData['bonus_claimed'] = 0;
            }
            
            if (in_array('claim_id', $tableColumns)) {
                $spinData['claim_id'] = null;
            }
            
            // Insert the record
            $insertResult = $db->table('spin_history')->insert($spinData);
            
            if ($insertResult) {
                log_message('info', 'Spin logged successfully: IP=' . $userIp . ', Session=' . substr($sessionId, 0, 8) . ', Prize=' . $spinData['item_won']);
            } else {
                log_message('error', 'Failed to insert spin record');
            }
            
            return $insertResult;
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to log spin result: ' . $e->getMessage());
            log_message('error', 'Spin data attempted: ' . json_encode($spinData ?? []));
            return false;
        }
    }
    
    /**
     * Get recent wins for ticker display
     */
    public function getRecentWins()
    {
        try {
            $db = \Config\Database::connect();
            
            // Get wins from last 24 hours with prizes > 0
            $recentWins = $db->table('spin_history')
                ->select('session_id, item_won, prize_amount, spin_time')
                ->where('prize_amount >', 0)
                ->where('spin_time >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                ->orderBy('spin_time', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();
            
            return $this->response->setJSON([
                'success' => true,
                'wins' => $recentWins
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to fetch recent wins'
            ]);
        }
    }
    
    /**
     * Reset user spins (admin function or for testing)
     */
    public function resetSpins()
    {
        if ($this->request->isAJAX()) {
            $session = session();
            
            // Reset spin counters
            $session->set('spins_today', 0);
            $session->set('spin_date', date('Y-m-d'));
            
            $userIp = $session->get('user_ip');
            log_message('info', 'Spins reset for IP: ' . $userIp);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Spins reset successfully',
                'spins_remaining' => 3
            ]);
        }
        
        return redirect()->to('/');
    }
    
    /**
     * Get user statistics
     */
    public function getUserStats()
    {
        if ($this->request->isAJAX()) {
            $session = session();
            $userIp = $session->get('user_ip');
            
            try {
                $db = \Config\Database::connect();
                
                // Get user's spin history for today
                $todayStats = $db->table('spin_history')
                    ->where('user_ip', $userIp)
                    ->where('DATE(spin_time)', date('Y-m-d'))
                    ->countAllResults();
                
                // Get total wins for this user
                $totalWins = $db->table('spin_history')
                    ->where('user_ip', $userIp)
                    ->where('prize_amount >', 0)
                    ->countAllResults();
                
                return $this->response->setJSON([
                    'success' => true,
                    'stats' => [
                        'spins_today' => $todayStats,
                        'total_wins' => $totalWins,
                        'spins_remaining' => max(0, 3 - $session->get('spins_today'))
                    ]
                ]);
                
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to fetch user stats'
                ]);
            }
        }
        
        return redirect()->to('/');
    }

    public function storeWinner()
    {
        if ($this->request->isAJAX()) {
            $session = session();
            
            // Get winner data from POST
            $winnerDataJson = $this->request->getPost('winner_data');
            
            if (empty($winnerDataJson)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No winner data provided.'
                ]);
            }
            
            try {
                // Decode winner data
                $winnerData = json_decode($winnerDataJson, true);
                
                if (!$winnerData) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Invalid winner data format.'
                    ]);
                }
                
                // Validate required fields
                if (!isset($winnerData['name']) || !isset($winnerData['type'])) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Incomplete winner data.'
                    ]);
                }
                
                // Store winner data in session for reward system access
                $session->set('winner_data', [
                    'name' => htmlspecialchars($winnerData['name']),
                    'prize' => $winnerData['prize'] ?? 0.00,
                    'type' => htmlspecialchars($winnerData['type']),
                    'timestamp' => time(),
                    'session_id' => $session->get('session_id') ?? session_id()
                ]);
                
                log_message('info', 'Winner data stored in session: ' . $winnerData['name'] . ' for session ' . session_id());
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Winner data stored successfully.'
                ]);
                
            } catch (\Exception $e) {
                log_message('error', 'Error storing winner data: ' . $e->getMessage());
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to store winner data.'
                ]);
            }
        }
        
        return redirect()->to('/');
    }
}