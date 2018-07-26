<?php

namespace App\Jobs;

use App\Helpers\Helpers;
//use App\Repository\CommentBackEndRepository;
use App\Repository\ProductsRepository;
use App\ShopifyApi\ProductsApi;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ImportProductsFromApi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1200;

    private $_shopId;

    private $_accessToken;

    private $_shopDomain;
    /**
     * @var
     */
    private $_productRepo;

    private $_productApi;

    private $_limitApi = 250;


    /**
     * ImportProductsFromApi constructor.
     * @param $shopId
     * @param $accessToken
     * @param $shopDomain
     */
    public function __construct($shopId, $accessToken, $shopDomain)
    {
        $this->_shopId = $shopId;

        $this->_accessToken = $accessToken;

        $this->_shopDomain = $shopDomain;

        $this->_productRepo = new ProductsRepository();

        $this->_productApi = new ProductsApi();

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $totalProductApi = $this->getCountProductsApi();
        $page = ceil($totalProductApi/$this->_limitApi);
        for($i = 1; $i <= $page; $i++)
        {
            $products = $this->getProductsApi($i);
            if( ! $products)
            {
                Helpers::saveLog('error', ['message' => 'Cannot import product from API', 'file' => __FILE__, 'line' => __LINE__, 'function' => __FUNCTION__, 'domain' => $this->_shopDomain]);
            }

            if( ! $this->saveProduct($products))
            {
                Helpers::saveLog('error', ['message' => 'Cannot save product from API to database', 'file' => __FILE__, 'line' => __LINE__, 'function' => __FUNCTION__, 'domain' => $this->_shopDomain]);
            }

            sleep(5);
        }
    }


    /**
     * @return bool
     */
    private function getCountProductsApi()
    {
        $this->_productApi->getAccessToken($this->_accessToken, $this->_shopDomain);
        $count = $this->_productApi->count([]);
        if( ! $count['status'])
            return false;

        return $count['count'];
    }

    /**
     * @param $page
     * @return bool
     */
    private function getProductsApi($page)
    {
        $this->_productApi->getAccessToken($this->_accessToken, $this->_shopDomain);
        $products = $this->_productApi->all(['id','title','handle','image', 'created_at', 'updated_at'],[],$page, $this->_limitApi);
        if( ! $products['status'])
            return false;

        return $products['products'];
    }

    /**
     * @param $products
     * @return bool
     */
    private function saveProduct($products)
    {
        return $this->_productRepo->import($this->_shopId, $products);
    }
}
