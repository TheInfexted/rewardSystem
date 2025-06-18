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
     * Bonus claims dashboard
     */
    public function index()
    {
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
            'bonus_type' => $this->request->getGet('bonus_type') // Add this line
        ];
    
        // Get claims data
        $claims = $this->bonusClaimModel->getClaimsWithPagination($perPage, $offset, $filters);
        $totalClaims = $this->bonusClaimModel->getClaimsCount($filters);
        $totalPages = ceil($totalClaims / $perPage);
    
        // Get statistics
        $stats = $this->bonusClaimModel->getClaimsStats();
    
        // Get current settings
        $bonusSettings = $this->adminSettingsModel->getBonusSettings();
    
        $data = [
            'title' => t('Admin.bonus.title'),
            'claims' => $claims,
            'totalClaims' => $totalClaims,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'filters' => $filters,
            'stats' => $stats,
            'bonusSettings' => $bonusSettings
        ];
    
        return view('admin/bonus/index', $data);
    }

    /**
     * Update bonus settings (redirect URL, etc.)
     */
    public function updateSettings()
    {
        if ($this->request->getMethod() === 'POST') {
            $settings = [
                'bonus_redirect_url' => $this->request->getPost('bonus_redirect_url'),
                'bonus_claim_enabled' => $this->request->getPost('bonus_claim_enabled') ? '1' : '0',
                'bonus_terms_text' => $this->request->getPost('bonus_terms_text')
            ];

            // Validate redirect URL
            if (!empty($settings['bonus_redirect_url']) && !filter_var($settings['bonus_redirect_url'], FILTER_VALIDATE_URL)) {
                return redirect()->back()->with('error', 'Please enter a valid redirect URL.');
            }

            try {
                $this->adminSettingsModel->updateSettings($settings);
                return redirect()->back()->with('success', 'Settings updated successfully!');
            } catch (\Exception $e) {
                log_message('error', 'Failed to update bonus settings: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to update settings. Please try again.');
            }
        }

        return redirect()->to('admin/bonus');
    }

    /**
     * View single claim details
     */
    public function view($id)
    {
        $claim = $this->bonusClaimModel->find($id);

        if (!$claim) {
            return redirect()->to('admin/bonus')->with('error', 'Claim not found.');
        }

        $data = [
            'title' => 'Claim Details - ' . $claim['user_name'],
            'claim' => $claim
        ];

        return view('admin/bonus/view', $data);
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
     * Export claims to CSV
     */
    public function export()
    {
        // Get filters for export
        $filters = [
            'status' => $this->request->getGet('status'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'search' => $this->request->getGet('search')
        ];

        // Get all claims matching filters
        $claims = $this->bonusClaimModel->getClaimsWithPagination(10000, 0, $filters); // Large limit for export

        // Set headers for CSV download
        $filename = 'bonus_claims_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Create file pointer
        $output = fopen('php://output', 'w');

        // Add CSV headers
        fputcsv($output, [
            'ID',
            'Claim Time',
            'User Name',
            'Phone Number',
            'Email',
            'User IP',
            'Bonus Type',
            'Bonus Amount',
            'Status',
            'Admin Notes'
        ]);

        // Add data rows
        foreach ($claims as $claim) {
            fputcsv($output, [
                $claim['id'],
                $claim['claim_time'],
                $claim['user_name'],
                $claim['phone_number'],
                $claim['email'] ?: 'N/A',
                $claim['user_ip'],
                $claim['bonus_type'],
                $claim['bonus_amount'],
                ucfirst($claim['status']),
                $claim['admin_notes'] ?: ''
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Get claims statistics for AJAX requests
     */
    public function getStats()
    {
        if ($this->request->isAJAX()) {
            $stats = $this->bonusClaimModel->getClaimsStats();
            return $this->response->setJSON($stats);
        }

        return redirect()->to('admin/bonus');
    }

    /**
     * Delete claim (admin only)
     */
    public function delete($id)
    {
        if ($this->request->isAJAX()) {
            try {
                $deleted = $this->bonusClaimModel->delete($id);

                if ($deleted) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Claim deleted successfully!'
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to delete claim.'
                    ]);
                }
            } catch (\Exception $e) {
                log_message('error', 'Failed to delete claim: ' . $e->getMessage());
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'An error occurred while deleting the claim.'
                ]);
            }
        }

        return redirect()->to('admin/bonus');
    }
}