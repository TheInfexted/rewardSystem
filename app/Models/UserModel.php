<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'email',
        'password',
        'last_login',
        'language_preference',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function __construct()
	{
	    parent::__construct();
		$this->db = db_connect();
	}

    /*
    * Protected
    */

    protected function findUserByUsername($where)
	{
        try {
            $builder = $this->db->table($this->table);
            $query = $builder->select('parent_role,username,role,user_token,permission')
                ->where('username', $where['username'])
                ->get()->getRowArray();

            if( $query ):
                $response = $query;
            else:
                $response = $this->db->error();
            endif;

            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /*
    * End Protected
    */

    /**
     * Get user by email
     */
    // public function getUserByEmail($email)
    // {
    //     return $this->where('email', $email)->first();
    // }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin($userId)
    {
        return $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    public function updateUserLogin($where)
	{
        try {
            $params = [
                'user_token' => $where['user_token'],
                'last_login' => $where['lastlogin_date'],
            ];

            $builder = $this->db->table($this->table);
            $query = $builder->ignore(true)
                ->set($params)
                ->where('email', $where['email'])
                ->update();

            if( $query ):
                // Find User
                $user = $this->findUserByEmail(['email'=>$where['email']]);
                // End Find User

                $response = [
                    'code' => 1,
                    'message' => lang('Response.success'),
                    'data' => $user
                ];
            else:
                $response = $this->db->error();
            endif;

            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function findUserByEmail($where)
	{
        try {
            $builder = $this->db->table($this->table);
            $query = $builder->select('*')
                ->where('email', $where['email'])
                ->get()->getRowArray();

            if( $query ):
                $response = $query;
            else:
                $response = $this->db->error();
            endif;

            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    
    /**
     * Update user language preference
     */
    public function updateLanguagePreference($userId, $language)
    {
        return $this->update($userId, [
            'language_preference' => $language
        ]);
    }
    
    /**
     * Get user language preference
     */
    public function getUserLanguage($userId)
    {
        $user = $this->find($userId);
        return $user ? ($user['language_preference'] ?? 'en') : 'en';
    }
}