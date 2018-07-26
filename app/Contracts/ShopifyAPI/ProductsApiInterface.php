<?php

namespace App\Contracts\ShopifyAPI;


interface ProductsApiInterface
{
    /**
     * @param array $field
     * @param array $filters
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function all(array $field, array $filters, int $limit, int $page) : array ;

    /**
     * @param array $field
     * @param string $product
     * @return array
     */
    public function detail(array $field, string $product): array ;

    /**
     * @param string $status
     * @param array $filters
     * @return array
     */
    public function count(array $filters, string $status) : array ;

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data) : array ;

    /**
     * @param string $product
     * @param array $data
     * @return array
     */
    public function update(string $product, array $data) : array;

    /**
     * @param string $product
     * @return array
     */
    public function delete(string $product) : array ;
}