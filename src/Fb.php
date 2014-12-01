<?php
/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 11/30/2014
 * Time: 10:26 PM
 */

namespace bonbon1702\Fb;

use Facebook\GraphUser;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Facebook\FacebookRequest;
use Facebook\FacebookSession;
use Illuminate\Config\Repository;
use Illuminate\Routing\Redirector;
use Facebook\FacebookRedirectLoginHelper;

class Fb {
    /**
     * @var session
     */
    protected $session;

    /**
     * @var redirect
     */
    protected $redirect;

    /**
     * @var config
     */
    protected $config;

    /**
     * @var request
     */
    protected $request;

    /**
     * @var app id
     */
    protected $app_id;

    /**
     * @var app secret
     */
    protected $app_secret;

    /**
     * @var redirect url
     */
    protected $redirect_url;


    function __construct(Store $session,Redirector $redirect, Repository $config, Request $request, $app_id = null, $app_secret = null, $redirect_url = null)
    {
        $this->session = $session;
        $this->redirect = $redirect;
        $this->config = $config;
        $this->request = $request;

        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
        $this->redirect_url = $redirect_url;

        FacebookSession::setDefaultApplication($this->app_id, $this->app_secret);

        $this->start();

    }

    /**
     * @return Store
     */
    public function getAppId()
    {
        return $this->app_id;
    }

    /**
     * @param Store $app_id
     */
    public function setAppId($app_id)
    {
        $this->app_id = $app_id;
    }

    /**
     * @return Store
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * @param Store $redirect
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;
    }

    /**
     * @return Store
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param Store $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }

    /**
     * @return app
     */
    public function getAppSecret()
    {
        return $this->app_secret;
    }

    /**
     * @param app $app_secret
     */
    public function setAppSecret($app_secret)
    {
        $this->app_secret = $app_secret;
    }

    /**
     * @return config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return redirect
     */
    public function getRedirectUrl()
    {
        return $this->redirect_url;
    }

    /**
     * @param redirect $redirect_url
     */
    public function setRedirectUrl($redirect_url)
    {
        $this->redirect_url = $redirect_url;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }


    protected function start()
    {
        session_start();
    }

    protected function getFacebookHelper()
    {
        $helper = new FacebookRedirectLoginHelper($this->redirect_url);

        $helper->disableSessionStatusCheck();

        return $helper;
    }

    public function getLoginFb()
    {
        $scope = $this->getScope();

        return $this->getFacebookHelper()->getLoginUrl($scope);
    }

    public function getScope()
    {
        return $this->config->get('facebook::scope');
    }

    public function getSessionFromRedirect()
    {
        $session = $this->getFacebookHelper()->getSessionFromRedirect();

        $this->session->put('facebook.session', $session);

        return $session;
    }

    public function getTokenFromRedirect()
    {
        $session = $this->getSessionFromRedirect();

        $token = $session->getToken();

        return $token;
    }

    public function setSessionToken($token)
    {
        $this->session->put('facebook.access_token', $token);
    }

    public function getAccessToken()
    {
        if ($this->session->has('facebook.access_token'))
        {
            return $this->session->get('facebook.access_token');
        }

        return $this->getSessionFromRedirect();
    }

    public function callBack()
    {
        $token = $this->getAccessToken();

        if(!empty($token)){
            $this->setSessionToken($token);

            return true;
        }

        return false;
    }

    public function getFacebookSession()
    {
        return $this->session->get('facebook.session');
    }

    public function destroy()
    {
        $this->session->forget('facebook.session');
        $this->session->forget('facebook.access_token');
    }

    public function logout()
    {
        $this->destroy();
    }

    public function api($method, $path){
        $session = new FacebookSession($this->getAccessToken());


        $request = with(new FacebookRequest($session, $method, $path))
            ->execute()
            ->getGraphObject(GraphUser::className());
        return $request;
    }

    public function get($path)
    {
        return $this->api('GET', $path);
    }

    public function getProfile()
    {
        return $this->get('/me');
    }
}