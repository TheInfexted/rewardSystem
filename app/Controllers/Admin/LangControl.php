<?php namespace App\Controllers;

use App\Controllers\BaseController;

class LangControl extends BaseController
{
    // Translation
    public function translateLocale()
    {
        // Custom
        // $defaultLang = $this->request->getLocale();
        // switch( $defaultLang ):
        //     case 'my': $newLang = 'ms'; break;
        //     case 'cn': $newLang = 'zh'; break;
        //     case 'zh': $newLang = 'zh_hant'; break;
        //     default:
        //         $newLang = 'en';
        // endswitch;
        // End Custom

        $session = session();
        $locale = $this->request->getLocale();
        // $locale = $newLang;
        $session->remove('lang');
        $session->set('lang', $locale);

        // Cookies
        set_cookie("lang", $locale);
        // echo $_COOKIE["locale"];
        // End Cookies

        // $url = base_url();
        // return redirect()->to($url);
        return json_encode(['code'=>1]);
    }
}