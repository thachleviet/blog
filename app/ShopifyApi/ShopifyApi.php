<?php
/**
 * Created by PhpStorm.
 * User: dev02
 * Date: 7/25/2018
 * Time: 5:47 PM
 */

namespace App\ShopifyApi;



use App\Contracts\ShopifyAPI\ShopsApiInterface;
use App\Services\ShopifyService;

class ShopifyApi extends ShopifyService implements ShopsApiInterface
{
    public function get()
    {
        try{
            $shop = $this->_shopify->call([
                'URL' => 'shop.json',
                'METHOD' => 'GET'
            ]);
            return ['status' => true, 'shop' => $shop->shop];
        } catch (\Exception $exception)
        {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }
}