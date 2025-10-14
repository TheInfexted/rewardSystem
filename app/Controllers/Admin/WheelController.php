<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\WheelItemsModel;

class WheelController extends BaseController
{
    protected $wheelItemsModel;

    public function __construct()
    {
        $this->wheelItemsModel = new WheelItemsModel();
    }

    /**
     * Display wheel items management page
     */
    public function index()
    {
        $data = [
            'title' => 'Wheel Items Management',
            'wheel_items' => $this->wheelItemsModel->orderBy('order', 'ASC')->findAll()
        ];

        return view('admin/wheel/index', $data);
    }

    /**
     * Add new wheel item
     */
    public function add()
    {
        // Count current active items - use findAll() to avoid query builder caching issues
        $activeItems = $this->wheelItemsModel->where('is_active', 1)->findAll();
        $activeItemCount = count($activeItems);
    
        // Prevent adding more than 8
        if ($activeItemCount >= 8 && strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/admin/wheel')
                             ->with('error', 'You already have 8 active wheel items. Remove one to add a new item.');
        }
        
        if (strtolower($this->request->getMethod()) === 'post') {
            $rules = [
                'item_name' => 'required|max_length[100]',
                'item_prize' => 'required|numeric|greater_than_equal_to[0]',
                'item_types' => 'required|in_list[cash,product]',
                'winning_rate' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
                'order' => 'required|integer|greater_than[0]|less_than_equal_to[8]'
            ];

            if ($this->validate($rules)) {
                $data = [
                    'item_name' => $this->request->getPost('item_name'),
                    'item_prize' => $this->request->getPost('item_prize'),
                    'item_types' => $this->request->getPost('item_types'),
                    'winning_rate' => $this->request->getPost('winning_rate'),
                    'order' => $this->request->getPost('order'),
                    'is_active' => $this->request->getPost('is_active') ? 1 : 0
                ];

                if ($this->wheelItemsModel->insert($data)) {
                    return redirect()->to('/admin/wheel')->with('success', 'Wheel item added successfully!');
                } else {
                    return redirect()->back()->withInput()->with('error', 'Failed to add wheel item.');
                }
            } else {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $data = [
            'title' => 'Add Wheel Item'
        ];

        return view('admin/wheel/add', $data);
    }

    /**
     * Edit wheel item - YOUR ORIGINAL CODE WITH DEBUG
     */
    public function edit($id)
    {
        $item = $this->wheelItemsModel->find($id);
        
        if (!$item) {
            return redirect()->to('/admin/wheel')->with('error', 'Item not found');
        }
    
        // FIX: Use strtolower() to handle case sensitivity
        if (strtolower($this->request->getMethod()) === 'post') {
            $rules = [
                'item_name' => 'required|max_length[100]',
                'item_prize' => 'required|numeric|greater_than_equal_to[0]',
                'item_types' => 'required|in_list[cash,product]',
                'winning_rate' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
                'order' => 'required|integer|greater_than[0]|less_than_equal_to[8]'
            ];
    
            if ($this->validate($rules)) {
                $data = [
                    'item_name' => $this->request->getPost('item_name'),
                    'item_prize' => $this->request->getPost('item_prize'),
                    'item_types' => $this->request->getPost('item_types'),
                    'winning_rate' => $this->request->getPost('winning_rate'),
                    'order' => $this->request->getPost('order'),
                    'is_active' => $this->request->getPost('is_active') ? 1 : 0
                ];
    
                if ($this->wheelItemsModel->update($id, $data)) {
                    return redirect()->to('/admin/wheel')->with('success', 'Wheel item updated successfully!');
                } else {
                    return redirect()->back()->withInput()->with('error', 'Failed to update wheel item.');
                }
            } else {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }
    
        $data = [
            'title' => 'Edit Wheel Item',
            'item' => $item
        ];
    
        return view('admin/wheel/edit', $data);
    }

    /**
     * Delete wheel item
     */
    public function delete($id)
    {
        if ($this->wheelItemsModel->delete($id)) {
            return redirect()->to('/admin/wheel')->with('success', 'Wheel item deleted successfully!');
        }
        
        return redirect()->to('/admin/wheel')->with('error', 'Failed to delete item');
    }

    /**
     * Check winning rates total
     */
    public function checkRates()
    {
        $items = $this->wheelItemsModel->where('is_active', 1)->findAll();
        $total = 0;
        
        foreach ($items as $item) {
            $total += $item['winning_rate'];
        }
        
        return $this->response->setJSON([
            'total' => $total,
            'valid' => ($total <= 100)
        ]);
    }
}