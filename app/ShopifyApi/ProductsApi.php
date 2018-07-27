<?php
/**
 * Created by PhpStorm.
 * User: dev02
 * Date: 7/26/2018
 * Time: 2:36 PM
 */

namespace App\ShopifyApi;


use App\Contracts\ShopifyAPI\ProductsApiInterface;

use App\Services\GuzzleHttp;

class ProductsApi extends  GuzzleHttp implements ProductsApiInterface
{
    /**
     * @param array $field
     * @param int $page
     * @param int $limit
     * @param string $status
     * @param array $filters
     * @return array
     */
    public function all(array $field = [], array $filters = [], int $page = 1, int $limit = 250, $status = 'any') : array
    {
        try{
            $field = implode(',', $field);
            $data = [
                'limit' => $limit,
                'page' => $page,
                'fields' => $field,
                'published_status' => $status
            ];

            if(! empty($filters['title']))
                $data['title'] = $filters['title'];

            if( ! empty($filters['collection_id']))
                $data['collection_id'] = $filters['collection_id'];


            $products = $this->get('products.json',$data);
            return ['status' => true, 'products' => $products->products];
        } catch (\Exception $exception)
        {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    /**
     * @param array $field
     * @param string $product
     * @return array
     */
    public function detail(array $field = [], string $product) : array
    {
        try{
            $product = $this->get('products/'.$product.'.json',$field);
            return ['status' => true, 'product' => $product->product];
        } catch (\Exception $exception)
        {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    /**
     * @param string $status
     * @param array $filters
     * @return array
     */
    public function count(array $filters, string $status = 'any') : array
    {
        try{
            $data['published_status'] = $status;
            if( ! empty($filters['collection_id']))
                $data['collection_id'] = $filters['collection_id'];

            if( ! empty($filters['title']))
                $data['title'] = $filters['title'];

            $count = $this->get('products/count.json',$data);

            return ['status' => true, 'count' => $count->count];

        } catch (\Exception $exception)
        {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data) : array
    {
        try{
            $product = $this->post('products.json',['product' => $data]);

            return ['status' => true, 'product' => $product->product];
        } catch (\Exception $exception)
        {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    /**
     * @param string $product
     * @param array $data
     * @return array
     */
    public function update(string $product, array $data) : array
    {

        try{
            $product = $this->put('products/'.$product.'.json',['product' => $data]);

            return ['status' => true, 'product' => $product->product];
        } catch (\Exception $exception) {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    /**
     * @param string $product
     * @return array
     */
    public function delete(string $product): array
    {
        try{
            $this->drop('products/'.$product.'.json');
            return ['status' => true];
        } catch (\Exception $exception)
        {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }
}