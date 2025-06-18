<?php

namespace App\Controllers;

use App\Controllers\BaseController;
// use App\Models\UserModel;

class AuthController extends BaseController
{
    // protected $userModel;

    // public function __construct()
    // {
    //     $this->userModel = new UserModel();
    // }

    /*
    * Protected
    */

    protected function userSessionGenerator($username,$password)
    {
        $recentDateTime = date('YmdHis');
        $hashToken = md5($username.$password.$recentDateTime);
        return $hashToken;
    }

    /*
    * End Protected
    */
    
    /*
    * User Login
    */

    public function attemptLogin()
    {
        if( session()->get('isLoggedIn') ): return false; endif;

        $this->validation->setRuleGroup('loginUser');
        $checkValidate = $this->validation->run($this->request->getpost());

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if( $checkValidate ):
            $user = $this->UserModel->findUserByEmail(['email'=>$email]);
            // echo json_encode($user);

            if (!$user) {
                return redirect()->back()->withInput()->with('error', 'Invalid email or password');
            }

            if (!password_verify($password, $user['password'])) {
                return redirect()->back()->withInput()->with('error', 'Invalid email or password');
            }

            // Compare and Verifying Password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            if( !password_verify($password,$user['password']) ):
                echo json_encode([
                    'code' => -1,
                    'message' => lang('Response.invaliduser')
                ]);
                return false;
            endif;
            // End Compare and Verifying Password

            // Token
            $sessionToken = $this->userSessionGenerator($email,$password);
            // End Token

            $payload = [
                'email' => $email,
                'user_token' => $sessionToken,
                'lastlogin_date' => date('Y-m-d H:i:s')
            ];
            $res = $this->UserModel->updateUserLogin($payload);
            // echo json_encode($res);

            if( $res['code']==1 && $res['data']!=[] ):
                // Set session data
                $user_data = [
                    'isLoggedIn' => TRUE,
                    'token' => $res['data']['user_token'],
                    'user_id' => $res['data']['id'],
                    'user_name' => $res['data']['name'],
                    'user_email' => $res['data']['email'],
                ];
                
                //Set user's language preference if available
                if (!empty($res['data']['language_preference'])) {
                    $user_data['locale'] = $res['data']['language_preference'];
                    service('request')->setLocale($res['data']['language_preference']);
                }
                
                $this->session->set($user_data);
                // End Set session data

                // echo json_encode([
                //     'code' => $res['code'],
                //     'message' => $res['message']
                // ]);
                $redirectUrl = session()->get('redirect_url') ?? base_url('admin/dashboard');
                return redirect()->to($redirectUrl)->with('success', 'Welcome back, ' . $user['name'] . '!');
            else:
                echo json_encode($res);
            endif;

            // Update last login
            // $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

            // Check if there's a redirect URL
            // $redirectUrl = session()->get('redirect_url') ?? '/admin/dashboard';
            // session()->remove('redirect_url');

            // return redirect()->to($redirectUrl)->with('success', 'Welcome back, ' . $user['name'] . '!');
        else:
            if( $this->validation->hasError('email') ):
                $err = $this->validation->getError('email');
            elseif( $this->validation->hasError('password') ):
                $err = $this->validation->getError('password');
            endif;

            echo json_encode([
                'code' => -1,
                'message' => $err,
            ]);
        endif;
    }

    /*
    * End User Login
    */

    /**
     * Logout
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Display registration page
     */
    public function register()
    {
        // Check if this is the first user (for initial setup)
        $userCount = $this->userModel->countAll();
        
        // If users exist and current user is not admin, deny access
        if ($userCount > 0 && !session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Registration is disabled.');
        }

        $data = [
            'title' => 'Register',
            'isFirstUser' => $userCount == 0
        ];

        return view('auth/register', $data);
    }

    /**
     * Handle registration process
     */
    public function attemptRegister()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'language_preference' => $this->request->getPost('language_preference'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->userModel->insert($data)) {
            // If this is the first user, auto-login
            $userCount = $this->userModel->countAll();
            if ($userCount == 1) {
                $user = $this->userModel->where('email', $data['email'])->first();
                $sessionData = [
                    'user_id' => $user['id'],
                    'user_name' => $user['name'],
                    'user_email' => $user['email'],
                    'isLoggedIn' => true
                ];
                session()->set($sessionData);
                return redirect()->to('/admin/dashboard')->with('success', 'Account created successfully! Welcome!');
            }
            
            return redirect()->to('/login')->with('success', 'Account created successfully! Please login.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create account. Please try again.');
    }
}