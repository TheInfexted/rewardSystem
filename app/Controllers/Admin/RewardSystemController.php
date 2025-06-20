<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RewardSystemAdModel;

class RewardSystemController extends BaseController
{
    protected $rewardSystemAdModel;

    public function __construct()
    {
        $this->rewardSystemAdModel = new RewardSystemAdModel();
        
        // Ensure upload directory exists
        $uploadPath = FCPATH . 'uploads/reward_ads/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
    }

    /**
     * Display reward system ads management
     */
    public function index()
    {
        $data = [
            'title' => 'Reward System Ads',
            'ads' => $this->rewardSystemAdModel->getAllAds()
        ];

        return view('admin/reward_system/index', $data);
    }

    /**
     * Add new ad
     */
    public function add()
    {
        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'ad_title' => 'permit_empty',
                'ad_type' => 'required|in_list[image,video]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $data = [
                'ad_type' => $this->request->getPost('ad_type'),
                'ad_title' => $this->request->getPost('ad_title'),
                'ad_description' => $this->request->getPost('ad_description'),
                'click_url' => $this->request->getPost('click_url'),
                'display_order' => $this->request->getPost('display_order') ?? 0,
                'is_active' => $this->request->getPost('is_active') ? 1 : 0,
                'start_date' => $this->request->getPost('start_date') ?: null,
                'end_date' => $this->request->getPost('end_date') ?: null,
            ];

            // Handle file upload
            $file = $this->request->getFile('media_file');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(FCPATH . 'uploads/reward_ads', $newName);
                $data['media_file'] = $newName;
            } else {
                // Use URL if no file uploaded
                $data['media_url'] = $this->request->getPost('media_url');
            }

            if ($this->rewardSystemAdModel->insert($data)) {
                return redirect()->to('/admin/reward-system')->with('success', 'Ad created successfully!');
            }

            return redirect()->back()->withInput()->with('error', 'Failed to create ad');
        }

        $data = [
            'title' => 'Add New Ad'
        ];

        return view('admin/reward_system/add', $data);
    }

    /**
     * Edit ad
     */
    public function edit($id)
    {
        $ad = $this->rewardSystemAdModel->find($id);
        
        if (!$ad) {
            return redirect()->to('/admin/reward-system')->with('error', 'Ad not found');
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'ad_title' => 'permit_empty',
                'ad_type' => 'required|in_list[image,video]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $data = [
                'ad_type' => $this->request->getPost('ad_type'),
                'ad_title' => $this->request->getPost('ad_title'),
                'ad_description' => $this->request->getPost('ad_description'),
                'click_url' => $this->request->getPost('click_url'),
                'display_order' => $this->request->getPost('display_order') ?? 0,
                'is_active' => $this->request->getPost('is_active') ? 1 : 0,
                'start_date' => $this->request->getPost('start_date') ?: null,
                'end_date' => $this->request->getPost('end_date') ?: null,
            ];

            // Handle file upload
            $file = $this->request->getFile('media_file');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                // Delete old file if exists
                if ($ad['media_file'] && file_exists(FCPATH . 'uploads/reward_ads/' . $ad['media_file'])) {
                    unlink(FCPATH . 'uploads/reward_ads/' . $ad['media_file']);
                }
                
                $newName = $file->getRandomName();
                $file->move(FCPATH . 'uploads/reward_ads', $newName);
                $data['media_file'] = $newName;
                $data['media_url'] = null; // Clear URL if file uploaded
            } else if ($this->request->getPost('media_url')) {
                // Use URL and clear file
                $data['media_url'] = $this->request->getPost('media_url');
                if ($ad['media_file'] && file_exists(FCPATH . 'uploads/reward_ads/' . $ad['media_file'])) {
                    unlink(FCPATH . 'uploads/reward_ads/' . $ad['media_file']);
                }
                $data['media_file'] = null;
            }

            if ($this->rewardSystemAdModel->update($id, $data)) {
                return redirect()->to('/admin/reward-system')->with('success', 'Ad updated successfully!');
            }

            return redirect()->back()->withInput()->with('error', 'Failed to update ad');
        }

        $data = [
            'title' => 'Edit Ad',
            'ad' => $ad
        ];

        return view('admin/reward_system/edit', $data);
    }

    /**
     * Delete ad
     */
    public function delete($id)
    {
        $ad = $this->rewardSystemAdModel->find($id);
        
        if (!$ad) {
            return redirect()->to('/admin/reward-system')->with('error', 'Ad not found');
        }

        // Delete media file if exists
        if ($ad['media_file'] && file_exists(FCPATH . 'uploads/reward_ads/' . $ad['media_file'])) {
            unlink(FCPATH . 'uploads/reward_ads/' . $ad['media_file']);
        }

        if ($this->rewardSystemAdModel->delete($id)) {
            return redirect()->to('/admin/reward-system')->with('success', 'Ad deleted successfully!');
        }

        return redirect()->to('/admin/reward-system')->with('error', 'Failed to delete ad');
    }

    /**
     * Update display order via AJAX
     */
    public function updateOrder()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false]);
        }

        $id = $this->request->getPost('id');
        $order = $this->request->getPost('order');

        if ($this->rewardSystemAdModel->updateOrder($id, $order)) {
            return $this->response->setJSON(['success' => true]);
        }

        return $this->response->setJSON(['success' => false]);
    }
}