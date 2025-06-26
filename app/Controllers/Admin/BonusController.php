<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BonusClaimModel;
use App\Models\AdminSettingsModel;

class BonusController extends BaseController
{
    protected $bonusClaimModel;
    protected $adminSettingsModel;

    public function __construct()
    {
        $this->bonusClaimModel = new BonusClaimModel();
        $this->adminSettingsModel = new AdminSettingsModel();
    }

    /**
     * Bonus claims dashboard with enhanced IP tracking
     */
    public function index()
    {
        try {
            // Get pagination parameters
            $page = $this->request->getGet('page') ?: 1;
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
        
            // Get filters
            $filters = [
                'status' => $this->request->getGet('status'),
                'date_from' => $this->request->getGet('date_from'),
                'date_to' => $this->request->getGet('date_to'),
                'search' => $this->request->getGet('search'),
                'bonus_type' => $this->request->getGet('bonus_type')
            ];
        
            // Get claims data with proper IP display
            $claims = $this->getClaimsWithEnhancedData($perPage, $offset, $filters);
            $totalClaims = $this->bonusClaimModel->getClaimsCount($filters);
            $totalPages = ceil($totalClaims / $perPage);
        
            // Get statistics with IP analysis
            $stats = $this->getEnhancedStats();
            
            // Get spin history correlation for better insight
            $spinCorrelation = $this->getSpinHistoryCorrelation();
        
            // Get current settings
            $bonusSettings = [];
            if (class_exists('App\Models\AdminSettingsModel')) {
                $bonusSettings = $this->adminSettingsModel->getBonusSettings();
            }
        
            $data = [
                'title' => 'Bonus Claims Management',
                'claims' => $claims,
                'totalClaims' => $totalClaims,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'perPage' => $perPage,
                'filters' => $filters,
                'stats' => $stats,
                'spinCorrelation' => $spinCorrelation,
                'bonusSettings' => $bonusSettings
            ];
        
            return view('admin/bonus/index', $data);
            
        } catch (\Exception $e) {
            log_message('error', 'BonusController index error: ' . $e->getMessage());
            
            // Return basic data to prevent complete failure
            $data = [
                'title' => 'Bonus Claims Management',
                'claims' => [],
                'totalClaims' => 0,
                'currentPage' => 1,
                'totalPages' => 0,
                'perPage' => 20,
                'filters' => [],
                'stats' => [
                    'total_claims' => 0,
                    'today_claims' => 0,
                    'unique_ips' => 0,
                    'pending_claims' => 0,
                    'processed_claims' => 0,
                    'cancelled_claims' => 0,
                    'total_amount' => 0
                ],
                'spinCorrelation' => [],
                'bonusSettings' => []
            ];
            
            return view('admin/bonus/index', $data);
        }
    }

    /**
     * Get claims with enhanced data including spin history correlation
     */
    private function getClaimsWithEnhancedData($perPage, $offset, $filters)
    {
        $db = \Config\Database::connect();
        
        // Build query with enhanced IP tracking
        $builder = $db->table('bonus_claims bc');
        $builder->select('
            bc.*,
            customers.username as customer_username,
            CASE 
                WHEN bc.user_ip = "::1" THEN "127.0.0.1 (Localhost)"
                WHEN bc.user_ip IS NULL OR bc.user_ip = "" THEN "No IP Recorded"
                ELSE bc.user_ip
            END as display_ip,
            (SELECT COUNT(*) FROM spin_history sh WHERE sh.user_ip = bc.user_ip AND DATE(sh.spin_time) = DATE(bc.claim_time)) as spins_same_day,
            (SELECT COUNT(*) FROM bonus_claims bc2 WHERE bc2.user_ip = bc.user_ip AND bc2.id != bc.id) as claims_same_ip,
            (SELECT sh.item_won FROM spin_history sh WHERE sh.user_ip = bc.user_ip AND sh.item_won LIKE "%BONUS%" AND DATE(sh.spin_time) = DATE(bc.claim_time) ORDER BY sh.spin_time DESC LIMIT 1) as related_spin_item
        ');
        
        $builder->join('customers', 'customers.id = bc.customer_id', 'left');
        
        // Apply filters
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $builder->where('bc.status', $filters['status']);
        }
        
        if (!empty($filters['bonus_type']) && $filters['bonus_type'] !== 'all') {
            $builder->where('bc.bonus_type', $filters['bonus_type']);
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('bc.claim_time >=', $filters['date_from'] . ' 00:00:00');
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('bc.claim_time <=', $filters['date_to'] . ' 23:59:59');
        }
        
        // Enhanced search including IP addresses
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                    ->like('bc.user_name', $search)
                    ->orLike('bc.phone_number', $search)
                    ->orLike('bc.email', $search)
                    ->orLike('bc.bonus_type', $search)
                    ->orLike('bc.user_ip', $search)
                    ->orLike('customers.username', $search)
                    ->groupEnd();
        }
        
        // Order and pagination
        $builder->orderBy('bc.claim_time', 'DESC')
                ->limit($perPage, $offset);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get enhanced statistics including IP analysis
     */
    private function getEnhancedStats()
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');
        
        // Basic stats
        $basicStats = $this->bonusClaimModel->getClaimsStats();
        
        // Enhanced IP statistics
        $ipStats = $db->table('bonus_claims')
                     ->select('
                         COUNT(DISTINCT CASE WHEN user_ip IS NOT NULL AND user_ip != "" AND user_ip != "::1" THEN user_ip END) as unique_real_ips,
                         COUNT(CASE WHEN user_ip = "::1" THEN 1 END) as localhost_claims,
                         COUNT(CASE WHEN user_ip IS NULL OR user_ip = "" THEN 1 END) as no_ip_claims
                     ')
                     ->get()
                     ->getRowArray();
        
        // Spin to claim conversion rate
        $spinStats = $db->table('spin_history')
                       ->select('COUNT(*) as total_bonus_spins')
                       ->where('item_won LIKE', '%BONUS%')
                       ->get()
                       ->getRowArray();
        
        $conversionRate = 0;
        if ($spinStats['total_bonus_spins'] > 0) {
            $conversionRate = round(($basicStats['total_claims'] / $spinStats['total_bonus_spins']) * 100, 2);
        }
        
        // Merge all stats
        return array_merge($basicStats, $ipStats, [
            'total_bonus_spins' => $spinStats['total_bonus_spins'],
            'conversion_rate' => $conversionRate
        ]);
    }

    /**
     * Get spin history correlation data
     */
    private function getSpinHistoryCorrelation()
    {
        $db = \Config\Database::connect();
        
        // Get recent unclaimed bonus spins (potential claims)
        $unclaimedSpins = $db->table('spin_history sh')
                            ->select('sh.user_ip, sh.spin_time, sh.item_won, sh.session_id')
                            ->where('sh.item_won LIKE', '%BONUS%')
                            ->where('sh.bonus_claimed', 0)
                            ->where('sh.spin_time >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                            ->orderBy('sh.spin_time', 'DESC')
                            ->limit(50)
                            ->get()
                            ->getResultArray();
        
        // Get IP frequency analysis
        $ipFrequency = $db->table('bonus_claims')
                         ->select('user_ip, COUNT(*) as claim_count')
                         ->where('user_ip IS NOT NULL')
                         ->where('user_ip !=', '')
                         ->groupBy('user_ip')
                         ->orderBy('claim_count', 'DESC')
                         ->limit(10)
                         ->get()
                         ->getResultArray();
        
        return [
            'unclaimed_spins' => $unclaimedSpins,
            'top_ips' => $ipFrequency
        ];
    }

    /**
     * Get stats for AJAX refresh with IP details
     */
    public function stats()
    {
        try {
            $stats = $this->getEnhancedStats();
            return $this->response->setJSON($stats);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get bonus stats: ' . $e->getMessage());
            return $this->response->setJSON([
                'total_claims' => 0,
                'today_claims' => 0,
                'unique_ips' => 0,
                'unique_real_ips' => 0,
                'localhost_claims' => 0,
                'no_ip_claims' => 0
            ]);
        }
    }

    /**
     * Get IP analysis report
     */
    public function ipAnalysis()
    {
        try {
            $db = \Config\Database::connect();
            
            // Detailed IP analysis
            $ipAnalysis = $db->table('bonus_claims bc')
                            ->select('
                                bc.user_ip,
                                COUNT(*) as total_claims,
                                COUNT(CASE WHEN bc.status = "pending" THEN 1 END) as pending_claims,
                                COUNT(CASE WHEN bc.status = "processed" THEN 1 END) as processed_claims,
                                MIN(bc.claim_time) as first_claim,
                                MAX(bc.claim_time) as last_claim,
                                COUNT(DISTINCT bc.user_name) as unique_names,
                                COUNT(DISTINCT bc.phone_number) as unique_phones
                            ')
                            ->where('bc.user_ip IS NOT NULL')
                            ->where('bc.user_ip !=', '')
                            ->groupBy('bc.user_ip')
                            ->orderBy('total_claims', 'DESC')
                            ->get()
                            ->getResultArray();
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $ipAnalysis
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'IP Analysis error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to generate IP analysis'
            ]);
        }
    }

    /**
     * Update bonus settings
     */
    public function updateSettings()
    {
        $isAjax = $this->request->isAJAX();
        $settings = [
            'bonus_redirect_url' => $this->request->getPost('bonus_redirect_url'),
            'bonus_claim_enabled' => $this->request->getPost('bonus_claim_enabled') ? '1' : '0',
            'bonus_terms_text' => $this->request->getPost('bonus_terms_text')
        ];

        if (!empty($settings['bonus_redirect_url']) && !filter_var($settings['bonus_redirect_url'], FILTER_VALIDATE_URL)) {
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid URL']);
            }
            return redirect()->back()->with('error', 'Please enter a valid redirect URL.');
        }

        try {
            $this->adminSettingsModel->updateSettings($settings);

            if ($isAjax) {
                return $this->response->setJSON(['success' => true, 'message' => 'Settings updated!']);
            }
            return redirect()->back()->with('success', 'Settings updated!');
        } catch (\Exception $e) {
            log_message('error', 'Bonus settings error: ' . $e->getMessage());
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => 'Save failed.']);
            }
            return redirect()->back()->with('error', 'Failed to update settings.');
        }
    }

    /**
     * View single claim details with spin history
     */
    public function view($id)
    {
        try {
            $claim = $this->bonusClaimModel->find($id);

            if (!$claim) {
                return redirect()->to('admin/bonus')->with('error', 'Claim not found.');
            }

            // Get related spin history
            $db = \Config\Database::connect();
            $relatedSpins = $db->table('spin_history')
                              ->where('user_ip', $claim['user_ip'])
                              ->where('DATE(spin_time)', date('Y-m-d', strtotime($claim['claim_time'])))
                              ->orderBy('spin_time', 'DESC')
                              ->get()
                              ->getResultArray();

            $data = [
                'title' => 'Claim Details - ' . $claim['user_name'],
                'claim' => $claim,
                'relatedSpins' => $relatedSpins
            ];

            return view('admin/bonus/view', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to view claim: ' . $e->getMessage());
            return redirect()->to('admin/bonus')->with('error', 'Failed to load claim details.');
        }
    }

    /**
     * Update claim status
     */
    public function updateStatus($id)
    {
        if ($this->request->isAJAX()) {
            $status = $this->request->getPost('status');
            $adminNotes = $this->request->getPost('admin_notes');

            $validStatuses = ['pending', 'processed', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid status provided.'
                ]);
            }

            try {
                $updated = $this->bonusClaimModel->update($id, [
                    'status' => $status,
                    'admin_notes' => $adminNotes
                ]);

                if ($updated) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Claim status updated successfully!'
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to update claim status.'
                    ]);
                }
            } catch (\Exception $e) {
                log_message('error', 'Failed to update claim status: ' . $e->getMessage());
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'An error occurred while updating the claim.'
                ]);
            }
        }

        return redirect()->to('admin/bonus');
    }

    /**
     * Get bonus settings (for AJAX modal)
     */
    public function getSettings()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }

        try {
            $settings = $this->adminSettingsModel->getBonusSettings();

            return $this->response->setJSON([
                'success' => true,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load bonus settings: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load bonus settings'
            ]);
        }
    }

    /**
     * Export claims to CSV with IP details
     */
    public function export()
    {
        try {
            $filters = [
                'status' => $this->request->getGet('status'),
                'date_from' => $this->request->getGet('date_from'),
                'date_to' => $this->request->getGet('date_to'),
                'search' => $this->request->getGet('search')
            ];

            $claims = $this->getClaimsWithEnhancedData(10000, 0, $filters);

            $filename = 'bonus_claims_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'ID', 'Claim Time', 'User Name', 'Phone Number', 'Email', 'User IP', 
                'Display IP', 'Bonus Type', 'Bonus Amount', 'Status', 'Admin Notes',
                'Related Spin Item', 'Spins Same Day', 'Claims Same IP'
            ]);

            foreach ($claims as $claim) {
                fputcsv($output, [
                    $claim['id'],
                    $claim['claim_time'],
                    $claim['user_name'],
                    $claim['phone_number'],
                    $claim['email'] ?: 'N/A',
                    $claim['user_ip'] ?: 'No IP',
                    $claim['display_ip'] ?: 'No IP',
                    $claim['bonus_type'],
                    $claim['bonus_amount'],
                    ucfirst($claim['status']),
                    $claim['admin_notes'] ?: 'N/A',
                    $claim['related_spin_item'] ?: 'N/A',
                    $claim['spins_same_day'] ?: 0,
                    $claim['claims_same_ip'] ?: 0
                ]);
            }

            fclose($output);
            exit;
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to export claims: ' . $e->getMessage());
            return redirect()->to('admin/bonus')->with('error', 'Failed to export claims.');
        }
    }
}