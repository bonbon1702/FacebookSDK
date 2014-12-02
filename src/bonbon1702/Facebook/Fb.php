<?php
/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 11/30/2014
 * Time: 10:26 PM
 */

namespace bonbon1702\Facebook;

use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Config\Repository;
use Illuminate\Routing\Redirector;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\GraphUser;
use Facebook\FacebookRequest;
use Facebook\FacebookSession;

class Fb {
    protected $session;

    protected $config;

    function __construct(Repository $config,Store $session, $app_id = null, $app_secret = null, $redirectUrl = null)
    {
        $this->config = $config;
        $this->session = $session;
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
        $this->redirectUrl = $redirectUrl;

        FacebookSession::setDefaultApplication($this->app_id,$this->app_secret);

        $this->startSession();
    }

    public function startSession()
    {
        session_start();
    }

    public function getLoginUrl()
    {
        $helper = $this->getFacebookHelper();

        return $helper->getLoginUrl();
    }

    public function getProfile(){
        $result = $this->api('GET', '/me');

        return $result;
    }

    public function logout()
    {
        $session = new FacebookSession($this->getAccessToken());
        $this->session->forget('facebook.session');
        $this->session->forget('facebook.access_token');
        $logoutURL = $this->getFacebookHelper()->getLogoutUrl($session,$this->redirectUrl);

        return $logoutURL;
    }

    public function check()
    {
        $token = $this->getAccessToken();
        if ( ! empty($token))
        {
            $this->putSessionToken($token);
            return true;
        }
        return false;
    }

    public function api($method,$path)
    {
        $session = new FacebookSession($this->getAccessToken());
        $result = (new FacebookRequest(
            $session, $method, $path
        ))->execute()->getGraphObject(GraphUser::className());

        return $result;
    }

    public function getAccessToken()
    {
        if ($this->hasSessionToken()){
            return $this->getSessionToken();
        }
        return $this->getTokenFromRedirect();
    }

    public function getTokenFromRedirect()
    {
        $session = $this->getSessionFromRedirect();

        return $session ? $session->getToken() : null;
    }

    public function getSessionFromRedirect()
    {
        $session = $this->getFacebookHelper()->getSessionFromRedirect();

        $this->session->put('facebook.session', $session);

        return $session;
    }

    public function getSessionToken()
    {
        return $this->session->get('facebook.access_token');
    }

    public function hasSessionToken()
    {
        return $this->session->has('facebook.access_token');
    }

    public function putSessionToken($token)
    {
        $this->session->put('facebook.access_token', $token);
    }

    public function getFacebookHelper()
    {
        $helper = new FacebookRedirectLoginHelper($this->redirectUrl);

        $helper->disableSessionStatusCheck();

        return $helper;
    }

}