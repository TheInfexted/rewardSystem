<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CustomerModel;

class LiveChatController extends BaseController
{
    protected $customerModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
    }

    /**
     * Generic handler for give_tokens action (NEW: matches dynamic system)
     */
    public function give_tokens()
    {
        return $this->processGenericAction('give_tokens');
    }
    
    /**
     * Legacy endpoint for backward compatibility
     */
    public function giveTokens()
    {
        return $this->give_tokens();
    }
    
    /**
     * Generic action processor for live chat system
     */
    private function processGenericAction($actionType)
    {
        // Set CORS headers for cross-domain requests
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        
        // Handle preflight OPTIONS request
        if ($this->request->getMethod() === 'OPTIONS') {
            return $this->response->setStatusCode(200);
        }
        
        // Only allow POST requests
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Method not allowed'
            ], 405);
        }
        
        try {
            // Get JSON payload
            $input = $this->request->getJSON(true);
            
            if (!$input) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Invalid JSON payload'
                ], 400);
            }
            
            log_message('info', "Live Chat Action: {$actionType} with payload: " . json_encode($input));
            
            // Route to specific action handler
            switch ($actionType) {
                case 'give_tokens':
                    return $this->handleGiveTokens($input);
                case 'check_balance':
                    return $this->handleCheckBalance($input);
                case 'add_bonus':
                    return $this->handleAddBonus($input);
                default:
                    return $this->response->setJSON([
                        'success' => false,
                        'error' => "Unsupported action: {$actionType}"
                    ], 400);
            }
            
        } catch (\Exception $e) {
            log_message('error', "Live Chat API Error ({$actionType}): " . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'Failed to process action'
            ], 500);
        }
    }
    
    /**
     * Handle give_tokens action
     */
    private function handleGiveTokens($input)
    {
        // Validate required fields
        if (!isset($input['customer_id']) || !isset($input['tokens'])) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Missing required fields: customer_id and tokens'
            ], 400);
        }
        
        $customerId = $input['customer_id'];
        $tokensToAdd = (int) $input['tokens'];
        $reason = $input['reason'] ?? 'Live Chat Support Bonus';
        
        // Validate token amount
        if ($tokensToAdd <= 0 || $tokensToAdd > 100) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Token amount must be between 1 and 100'
            ], 400);
        }
        
        // Find customer
        $customer = $this->customerModel->find($customerId);
        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Customer not found'
            ], 404);
        }
        
        // Add tokens to customer account
        $currentTokens = (int) ($customer['spin_tokens'] ?? 0);
        $newTokens = $currentTokens + $tokensToAdd;
        
        $updated = $this->customerModel->update($customerId, [
            'spin_tokens' => $newTokens,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($updated) {
            // Log the transaction
            log_message('info', "Live Chat API: Added {$tokensToAdd} tokens to customer {$customerId}. Reason: {$reason}");
            
            return $this->response->setJSON([
                'success' => true,
                'message' => "Successfully added {$tokensToAdd} tokens to customer account",
                'data' => [
                    'customer_id' => $customerId,
                    'customer_username' => $customer['username'],
                    'tokens_added' => $tokensToAdd,
                    'previous_tokens' => $currentTokens,
                    'new_token_balance' => $newTokens,
                    'reason' => $reason,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Failed to update customer tokens'
            ], 500);
        }
    }
    
    /**
     * Handle check_balance action
     */
    private function handleCheckBalance($input)
    {
        if (!isset($input['customer_id'])) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Missing required field: customer_id'
            ], 400);
        }
        
        $customer = $this->customerModel->find($input['customer_id']);
        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Customer not found'
            ], 404);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Balance retrieved successfully',
            'data' => [
                'customer_id' => $customer['id'],
                'username' => $customer['username'],
                'spin_tokens' => (int) ($customer['spin_tokens'] ?? 0),
                'points' => (int) ($customer['points'] ?? 0)
            ]
        ]);
    }
    
    /**
     * Handle add_bonus action (example of another action)
     */
    private function handleAddBonus($input)
    {
        // Similar to give_tokens but for bonus points
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Bonus action not implemented yet',
            'data' => $input
        ]);
    }
    
    /**
     * Generic handler for check_balance action
     */
    public function check_balance()
    {
        return $this->processGenericAction('check_balance');
    }
    
    /**
     * Generic handler for add_bonus action
     */
    public function add_bonus()
    {
        return $this->processGenericAction('add_bonus');
    }
    
    /**
     * Get customer info (for validation/debugging)
     */
    public function getCustomer()
    {
        // CORS headers
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type');
        
        if ($this->request->getMethod() === 'OPTIONS') {
            return $this->response->setStatusCode(200);
        }
        
        $customerId = $this->request->getGet('customer_id');
        
        if (!$customerId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'customer_id parameter required'
            ], 400);
        }
        
        try {
            $customer = $this->customerModel->find($customerId);
            
            if (!$customer) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Customer not found'
                ], 404);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'id' => $customer['id'],
                    'username' => $customer['username'],
                    'name' => $customer['name'] ?? $customer['username'],
                    'spin_tokens' => (int) ($customer['spin_tokens'] ?? 0),
                    'points' => (int) ($customer['points'] ?? 0),
                    'is_active' => (bool) ($customer['is_active'] ?? false),
                    'last_login' => $customer['last_login'] ?? null,
                    'created_at' => $customer['created_at'] ?? null
                ]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Get Customer API Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Failed to retrieve customer data'
            ], 500);
        }
    }
}
