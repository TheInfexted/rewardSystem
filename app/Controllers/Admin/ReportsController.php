<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ReportsController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Spin Reports Dashboard
     */
    public function index()
    {
        // Get pagination parameters
        $page = $this->request->getGet('page') ?: 1;
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        // Get filters
        $filters = [
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'prize_type' => $this->request->getGet('prize_type'),
            'search' => $this->request->getGet('search'),
            'user_ip' => $this->request->getGet('user_ip')
        ];

        // Get spin history data
        $spins = $this->getSpinHistory($perPage, $offset, $filters);
        $totalSpins = $this->getSpinCount($filters);
        $totalPages = ceil($totalSpins / $perPage);

        // Get statistics
        $stats = $this->getSpinStats($filters);

        $data = [
            'title' => 'Spin Reports',
            'spins' => $spins,
            'totalSpins' => $totalSpins,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'filters' => $filters,
            'stats' => $stats
        ];

        return view('admin/reports/index', $data);
    }

    /**
     * Get spin history with pagination and filtering
     */
    private function getSpinHistory($limit, $offset, $filters = [])
    {
        $builder = $this->db->table('spin_history');

        // Apply filters
        if (!empty($filters['date_from'])) {
            $builder->where('DATE(spin_time) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('DATE(spin_time) <=', $filters['date_to']);
        }

        if (!empty($filters['prize_type'])) {
            $builder->where('prize_type', $filters['prize_type']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('item_won', $filters['search'])
                ->orLike('session_id', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['user_ip'])) {
            $builder->like('user_ip', $filters['user_ip']);
        }

        return $builder->orderBy('spin_time', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    /**
     * Get total spin count with filters
     */
    private function getSpinCount($filters = [])
    {
        $builder = $this->db->table('spin_history');

        // Apply same filters
        if (!empty($filters['date_from'])) {
            $builder->where('DATE(spin_time) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('DATE(spin_time) <=', $filters['date_to']);
        }

        if (!empty($filters['prize_type'])) {
            $builder->where('prize_type', $filters['prize_type']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('item_won', $filters['search'])
                ->orLike('session_id', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['user_ip'])) {
            $builder->like('user_ip', $filters['user_ip']);
        }

        return $builder->countAllResults();
    }

    /**
     * Get spin statistics
     */
    private function getSpinStats($filters = [])
    {
        $builder = $this->db->table('spin_history');

        // Apply filters to stats query
        if (!empty($filters['date_from'])) {
            $builder->where('DATE(spin_time) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('DATE(spin_time) <=', $filters['date_to']);
        }

        $baseQuery = $builder->getCompiledSelect();

        $stats = [
            'total_spins' => $this->getSpinCount($filters),
            'today_spins' => $this->getTodaySpins(),
            'unique_users' => $this->getUniqueUsers($filters),
            'prize_spins' => $this->getPrizeSpins($filters)
        ];

        // Get hourly distribution for today
        $hourlyStats = $this->db->query("
            SELECT 
                HOUR(spin_time) as hour,
                COUNT(*) as count
            FROM spin_history 
            WHERE DATE(spin_time) = CURDATE()
            GROUP BY HOUR(spin_time)
            ORDER BY hour
        ")->getResultArray();

        $stats['hourly_distribution'] = $hourlyStats;

        // Get prize distribution
        $prizeStats = $this->db->query("
            SELECT 
                item_won,
                COUNT(*) as count,
                SUM(prize_amount) as total_amount
            FROM spin_history 
            WHERE DATE(spin_time) >= CURDATE() - INTERVAL 7 DAY
            GROUP BY item_won
            ORDER BY count DESC
        ")->getResultArray();

        $stats['prize_distribution'] = $prizeStats;

        return $stats;
    }

    /**
     * Get today's spins count
     */
    private function getTodaySpins()
    {
        return $this->db->table('spin_history')
            ->where('DATE(spin_time)', date('Y-m-d'))
            ->countAllResults();
    }

    /**
     * Get unique users count
     */
    private function getUniqueUsers($filters = [])
    {
        $builder = $this->db->table('spin_history');
        
        if (!empty($filters['date_from'])) {
            $builder->where('DATE(spin_time) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('DATE(spin_time) <=', $filters['date_to']);
        }

        return $builder->distinct()
            ->select('user_ip')
            ->countAllResults();
    }

    /**
     * Get prize spins count (spins that won something)
     */
    private function getPrizeSpins($filters = [])
    {
        $builder = $this->db->table('spin_history');
        
        if (!empty($filters['date_from'])) {
            $builder->where('DATE(spin_time) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('DATE(spin_time) <=', $filters['date_to']);
        }

        return $builder->where('prize_amount >', 0)
            ->countAllResults();
    }

    /**
     * Export spins to CSV
     */
    public function export()
    {
        // Get filters for export
        $filters = [
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'prize_type' => $this->request->getGet('prize_type'),
            'search' => $this->request->getGet('search'),
            'user_ip' => $this->request->getGet('user_ip')
        ];

        // Get all spins matching filters
        $spins = $this->getSpinHistory(10000, 0, $filters); // Large limit for export

        // Set headers for CSV download
        $filename = 'spin_reports_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Create file pointer
        $output = fopen('php://output', 'w');

        // Add CSV headers
        fputcsv($output, [
            'ID',
            'Spin Time',
            'Session ID',
            'User IP',
            'Item Won',
            'Prize Amount',
            'Prize Type',
            'Spin Number',
            'Bonus Claimed'
        ]);

        // Add data rows
        foreach ($spins as $spin) {
            fputcsv($output, [
                $spin['id'],
                $spin['spin_time'],
                $spin['session_id'] ?: 'N/A',
                $spin['user_ip'],
                $spin['item_won'],
                $spin['prize_amount'],
                ucfirst($spin['prize_type']),
                $spin['spin_number'] ?? 'N/A',
                ($spin['bonus_claimed'] ?? 0) ? 'Yes' : 'No'
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Get detailed spin analytics
     */
    public function analytics()
    {
        // Get date range (default last 30 days)
        $dateFrom = $this->request->getGet('date_from') ?: date('Y-m-d', strtotime('-30 days'));
        $dateTo = $this->request->getGet('date_to') ?: date('Y-m-d');

        // Daily spin counts
        $dailySpins = $this->db->query("
            SELECT 
                DATE(spin_time) as date,
                COUNT(*) as total_spins,
                COUNT(DISTINCT user_ip) as unique_users,
                SUM(CASE WHEN prize_amount > 0 THEN 1 ELSE 0 END) as winning_spins
            FROM spin_history 
            WHERE DATE(spin_time) BETWEEN ? AND ?
            GROUP BY DATE(spin_time)
            ORDER BY date
        ", [$dateFrom, $dateTo])->getResultArray();

        // Popular prizes
        $popularPrizes = $this->db->query("
            SELECT 
                item_won,
                COUNT(*) as count,
                ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM spin_history WHERE DATE(spin_time) BETWEEN ? AND ?)), 2) as percentage
            FROM spin_history 
            WHERE DATE(spin_time) BETWEEN ? AND ?
            GROUP BY item_won
            ORDER BY count DESC
        ", [$dateFrom, $dateTo, $dateFrom, $dateTo])->getResultArray();

        // User behavior analysis
        $userBehavior = $this->db->query("
            SELECT 
                spin_number,
                COUNT(*) as count,
                COUNT(DISTINCT user_ip) as unique_users
            FROM spin_history 
            WHERE DATE(spin_time) BETWEEN ? AND ?
            AND spin_number IS NOT NULL
            GROUP BY spin_number
            ORDER BY spin_number
        ", [$dateFrom, $dateTo])->getResultArray();

        $data = [
            'title' => 'Spin Analytics',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'dailySpins' => $dailySpins,
            'popularPrizes' => $popularPrizes,
            'userBehavior' => $userBehavior
        ];

        return view('admin/reports/analytics', $data);
    }

    /**
     * View individual spin details
     */
    public function view($id)
    {
        $spin = $this->db->table('spin_history')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (!$spin) {
            return redirect()->to('admin/reports')->with('error', 'Spin record not found.');
        }

        // Get other spins from same session
        $sessionSpins = [];
        if (!empty($spin['session_id'])) {
            $sessionSpins = $this->db->table('spin_history')
                ->where('session_id', $spin['session_id'])
                ->where('id !=', $id)
                ->orderBy('spin_time', 'ASC')
                ->get()
                ->getResultArray();
        }

        // Get other spins from same IP today
        $ipSpins = $this->db->table('spin_history')
            ->where('user_ip', $spin['user_ip'])
            ->where('DATE(spin_time)', date('Y-m-d', strtotime($spin['spin_time'])))
            ->where('id !=', $id)
            ->orderBy('spin_time', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Spin Details - #' . $spin['id'],
            'spin' => $spin,
            'sessionSpins' => $sessionSpins,
            'ipSpins' => $ipSpins
        ];

        return view('admin/reports/view', $data);
    }
}