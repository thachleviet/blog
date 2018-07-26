<?php

namespace Contract\Repository;

/**
 * Interface ProductsRepositoryInterface
 * @package Contract\Repository
 */
interface ProductsRepositoryInterface
{
    /**
     * @param array $data
     *
     * @return boolean
     */
    public function addProduct(array $data = []);

    /**
     * @param array $data
     * @return mixed
     */
    public function addMultiProduct(array $data);
}