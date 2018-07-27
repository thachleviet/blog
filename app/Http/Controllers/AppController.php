<?php
/**
 * Created by PhpStorm.
 * User: dev02
 * Date: 7/26/2018
 * Time: 9:25 AM
 */

namespace App\Http\Controllers;


use App\Jobs\ImportProductsFromApi;
use App\Models\ShopsTable;
use App\Services\ShopifyService;
use App\ShopifyApi\ShopifyApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppController extends Controller
{
    protected $_shopifyServive ;

    protected  $_shopApi;
    public function __construct(ShopifyService $shopifyService  ,ShopifyApi $shopifyApi)
    {
        $this->_shopifyServive  = $shopifyService ;
        $this->_shopApi  = $shopifyApi ;
    }

    public function installApp(){
        session( [ 'accessToken' => '', 'shopDomain' => '', 'shopId' => '' ] );
        return view('app.index') ;
    }

    public function submitInstall(Request $request){


        $validator  = Validator::make($request->all(), [
            'shop'=>'required'
        ]) ;
        if($validator->fails()){
            return redirect()->route('app')
                ->withErrors($validator)
                ->withInput();
        }
        $urlShopStore = $request->input( 'shop', null );

        $this->_shopifyServive->setShopDomain($urlShopStore) ;

        return redirect($this->_shopifyServive->installURL());
    }


    public function auth(Request $request){
        $request  = $request->all() ;

        $auth = $this->_shopifyServive->authApp($request) ;
        $mShops =  new ShopsTable() ;
        if($auth['status']){
            $shopDomain  = $request['shop'];
            $accessToken = $auth['accessToken'];

            $this->_shopApi->getAccessToken( $accessToken, $shopDomain );
            $shopInfoApi = $this->_shopApi->get();

            if ( ! $shopInfoApi['status'] ) {
                return redirect( route( 'app' ) )->with( 'error', $shopInfoApi['message'] );
            }
            $shopInfoApi = $shopInfoApi['shop'];
            session( [
                'accessToken' => $accessToken,
                'shopDomain'  => $shopDomain,
                'shopId'      => $shopInfoApi->id
            ] );


//            $shopInfoDB = $mShops->detail( [ 'shop_id' => $shopInfoApi->id ] );
//
//            $recordShop   = [
//                'shop_id'      => $shopInfoApi->id,
//                'shop_name'    => isset( $shopInfoApi->myshopify_domain ) ? $shopInfoApi->myshopify_domain : null,
//                'shop_email'   => isset( $shopInfoApi->email ) ? $shopInfoApi->email : null,
////                'shop_status'  => config( 'common.status.publish' ),
//                'shop_country' => isset( $shopInfoApi->country ) ? $shopInfoApi->country : null,
//                'shop_owner'   => isset( $shopInfoApi->shop_owner ) ? $shopInfoApi->shop_owner : null,
//                'plan_name'    => isset( $shopInfoApi->plan_name ) ? $shopInfoApi->plan_name : null,
////                'app_version'  => config( 'common.app_version', null ),
////                'app_plan'     => $app_plan,
//                'access_token' => $accessToken
//            ];
//            $saveInfoShops  = $mShops->inserts( $recordShop );
//            if($saveInfoShops['status']){
                //Import Database
                $this->dispatch( new ImportProductsFromApi( $shopInfoApi->id, $accessToken, $shopDomain ) );
//            }
            return redirect( route( 'home' ) );
        }
    }
}