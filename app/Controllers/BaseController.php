<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use App\Models\UserModel;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['cookie','url','form','text','filesystem','date','array','email','language','security','number','translation'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;
    protected $session;
    protected $language;
    protected $validation;
    protected $UserModel;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');
        
        // Set locale from session or user preference
        $locale = $this->determineLocale();
        session()->set('locale', $locale);
        service('request')->setLocale($locale);

        // Libraries
        $this->session = \Config\Services::session();
		$this->language = \Config\Services::language();
		$security = \Config\Services::security();
		$this->validation = \Config\Services::validation();

        // End Libraries

        /*
        * Model
        */

        $this->UserModel = new UserModel();

        /*
        * End Model
        */

        /*
        * Global
        */

        $client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];

		if(filter_var($client, FILTER_VALIDATE_IP)) {
			$ip = $client;
		} elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
			$ip = $forward;
		} else {
			$ip = $remote;
		}
		$this->session->set('ip', $ip);

        /*
        * End Global
        */
    }
    
    /**
     * Determine the locale to use
     * 
     * @return string
     */
    protected function determineLocale()
    {
        // First check session
        $locale = session()->get('locale');
        
        if ($locale) {
            return $locale;
        }
        
        // If user is logged in, check their preference
        if (session()->has('user_id')) {
            $db = \Config\Database::connect();
            $user = $db->table('users')
                       ->select('language_preference')
                       ->where('id', session()->get('user_id'))
                       ->get()
                       ->getRow();
            
            if ($user && $user->language_preference) {
                return $user->language_preference;
            }
        }
        
        // Otherwise, detect browser language
        $acceptLanguage = $this->request->getHeaderLine('Accept-Language');
        return $this->detectBrowserLanguage($acceptLanguage);
    }
    
    /**
     * Detect browser language preference
     * 
     * @param string $acceptLanguage
     * @return string
     */
    protected function detectBrowserLanguage($acceptLanguage)
    {
        // Get supported languages from database
        $db = \Config\Database::connect();
        $setting = $db->table('admin_settings')
                      ->where('setting_key', 'supported_languages')
                      ->get()
                      ->getRow();
        
        $supportedLanguages = $setting ? explode(',', $setting->setting_value) : ['en', 'zh'];
        
        // Get default language
        $defaultSetting = $db->table('admin_settings')
                            ->where('setting_key', 'default_language')
                            ->get()
                            ->getRow();
        
        $defaultLanguage = $defaultSetting ? $defaultSetting->setting_value : 'en';
        
        if (empty($acceptLanguage)) {
            return $defaultLanguage;
        }
        
        // Parse Accept-Language header
        $languages = [];
        $parts = explode(',', $acceptLanguage);
        
        foreach ($parts as $part) {
            $lang = strtok(trim($part), ';');
            $lang = substr($lang, 0, 2); // Get first two characters
            
            if (in_array($lang, $supportedLanguages)) {
                return $lang;
            }
        }
        
        return $defaultLanguage;
    }

    public function checkDevice()
    {
        $device = $this->request->getUserAgent();
        $isMobile = $device->isMobile();
        $isBrowser = $device->isBrowser();
        $currentMobile = $device->getMobile();
		$currentPlatform = $device->getPlatform();
		$result = [
            'code' => 0,
			'isMobile' => $isMobile,
            'isBrowser' => $isBrowser,
            'mobile' => $currentMobile,
			'platform' => $currentPlatform
		];
        echo json_encode($result);
    }
}
