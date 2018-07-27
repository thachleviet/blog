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
use App\ShopifyApi\ProductsApi;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    protected $_productRepository;

    protected $_modelProduct ;


    protected $_productApi ;
    public function __construct(ProductsRepository $productsRepository, ProductsModel $productsModel ,ProductsApi $productsApi)
    {
        $this->_productRepository =  $productsRepository ;

        $this->_modelProduct  = $productsModel ;
        $this->_productApi   = $productsApi ;
    }

    public function index(){

        session(['shopId'=>"1610678349"]);
        $shopId = session('shopId');
        $_objectProduct  = $this->_productRepository->getAll($shopId) ;
        return view('product.index', [
            'object'=>$_objectProduct
            ]
        ) ;
    }



    public function edit($idProduct){

        $item  = $this->_productRepository->getItem(session('shopId'), $idProduct) ;
        return view('product.edit', ['object'=>$item]) ;
    }



    public function submitEdit(Request $request ,$idProduct){

//        var_dump($request->all() );
        try{
             $this->_productRepository->updateProduct(session('shopId'), $request->input('id'), ['name'=>$request->input('name')]) ;
             $this->_productApi->getAccessToken(session('accessToken'), session('shopDomain'));

             $this->_productApi->update($request->input('id'),['id'=>$idProduct, 'title'=>$request->input('name')] );


            return redirect()->route('product')->with('msg', 'Update Product Success !');
        }catch (\Exception $exception){
            return $exception->getMessage() ;
        }
    }

}