<?php
/**
 * Created by PhpStorm.
 * User: dev02
 * Date: 7/26/2018
 * Time: 4:54 PM
 */

namespace App\Http\Controllers;


use App\Models\ProductsModel;
use App\Repository\ProductsRepository;

class ProductController extends Controller
{

    protected $_productRepository;

    protected $_modelProduct ;

    public function __construct(ProductsRepository $productsRepository, ProductsModel $productsModel)
    {
        $this->_productRepository =  $productsRepository ;

        $this->_modelProduct =  $productsModel ;
    }

    public function index(){
        $shopId = session('shopId');
        $_objectProduct  = $this->_productRepository->getAll($shopId) ;
        return view('product.index', [
            'object'=>$_objectProduct
            ]
        ) ;
    }


}