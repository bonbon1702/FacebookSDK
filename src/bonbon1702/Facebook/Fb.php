<?php
/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 11/30/2014
 * Time: 10:26 PM
 */

namespace bonbon1702\Facebook;

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

    protected $redirect;

    function __construct(Repository $config,Store $session ,Redirector $redirect, $app_id = null, $app_secret = null, $redirectUrl = null)
    {
        $this->config = $config;
        $this->session = $session;
        $this->redirect = $redirect;
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
        $scope = $this->getScope();
        $helper = $this->getFacebookHelper();

        return $helper->getLoginUrl($scope);
    }

    public function authenticate()
    {
        return $this->redirect->to($this->getLoginUrl());
    }

    public function getProfile()
    {
        $result = $this->api('GET', '/me');

        return $result;
    }

    public function getUserProfilePicture($type)
    {
        $result = $this->api('GET','me/picture?type='.$type.'&&redirect=false');

        return $result;
    }

    public function postToTimeLine($caption, $link, $message)
    {
        $result = $this->api('POST','/me/feed', array(
            'caption' => $caption,
            'link' => $link,
            'message' => $message
        ));

        return $result;
    }

    public function logout()
    {
        $this->session->forget('facebook.session');
        $this->session->forget('facebook.access_token');
        return $this->redirect->to($this->redirectUrl);
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

    public function api($method,$path,$parameters = null)
    {
        $session = new FacebookSession($this->getAccessToken());
        $result = (new FacebookRequest(
            $session, $method, $path, $parameters
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

    public function getScope()
    {
        return $this->config->get('facebooksdk::scope');
    }

}