<?php
/**
 * Created by PhpStorm.
 * User: dev02
 * Date: 7/25/2018
 * Time: 5:13 PM
 */

namespace App\Services;


use Illuminate\Support\Facades\App;

class ShopifyService
{

    protected $_shopify ;
    public function __construct()
    {
        $this->_shopify  = App::make('ShopifyAPI');
        $this->_shopify->setup(['API_KEY' => env("API_KEY"), 'API_SECRET' =>  env("API_SECRET")]);
    }


    public function installURL(){
        return $this->_shopify->installURL(['permissions' => config('shopify.scope.write_orders') ,'redirect' => config('shopify.scope.redirect_before_install')]) ;
    }


    /**
     * @param $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->_shopify->setup(['ACCESS_TOKEN' => $accessToken]);
    }
    /**
     * @param $shopDomain
     */
    public function setShopDomain($shopDomain)
    {
        $this->_shopify->setup(['SHOP_DOMAIN' => $shopDomain]);
    }

    /**
     * @param $accessToken
     * @param $shopDomain
     * @return mixed
     * @throws \Exception
     */
    public function getAccessToken($accessToken, $shopDomain)
    {
        try{
            $this->setShopDomain($shopDomain);
            $this->setAccessToken($accessToken);
            return $this->_shopify;

        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }


    /**
     * @param $request
     * @return array
     */
    public function authApp($request)
    {
        try{
            $this->setShopDomain($request['shop']);

            $verify = $this->_shopify->verifyRequest($request);
            if($verify)
            {
                $accessToken = $this->_shopify->getAccessToken($request['code']);
                return ['status' => true, 'accessToken' => $accessToken];
            }

            return ['status' => false, 'message' => 'Request not verify'];
        } catch (\Exception $exception)
        {
            throw new \Exception($exception->getMessage());
        }
    }
}