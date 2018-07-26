<?php
/**
 * Created by PhpStorm.
 * User: dev02
 * Date: 7/26/2018
 * Time: 1:03 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductTable extends  Model
{

    protected $connection = 'mysql_product';
    /**
     * @var string
     */
    protected $table = 'products_';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'title',
        'handle',
        'image',
        'review_link',
        'is_reviews',
        'created_at',
        'updated_at'
    ];

    public function __construct(array $attributes = [])
    {
        die() ;
        parent::__construct($attributes);
        $this->setConnection('mysql_product');
    }

    public function getTableProduct( $shopId ) {
        $table = $this->getTable() . $shopId ;

        if ( ! Schema::connection( $this->getConnectionName() )->hasTable( $table ) ) {
            $this->createTable( $table );
        }

        return $table;
    }

    public function createTable( $table ) {
        if ( Schema::connection( $this->getConnectionName() )->hasTable( $table ) ) {
            return false;
        }
        Schema::connection( $this->getConnectionName() )->create( $table, function ( Blueprint $table ) {
            $table->string( 'id' );
            $table->string( 'title', 1000 );
            $table->string( 'handle', 1000 );
            $table->string( 'image', 1000 )->nullable();
            $table->string( 'collection_id' )->nullable();
            // added in v3.3
            $table->longText('review_link')->nullable();
            $table->integer( 'is_reviews' )->default( 0 );
            $table->timestamps();
            $table->primary( 'id' );
            $table->index( 'id' );
        } );
    }


    public function addColumn($shopId = '', $columnName = '')
    {
//        $client = new \Raven_Client( env( 'SENTRY_DSN' ) );
//        $client->user_context([
//            'shopId' => $shopId,
//            'columnName' => $columnName
//        ]);

        if (!$shopId || !$columnName) {
            return false;
        }
        try {
            $schemaConnection = Schema::connection($this->getConnectionName());

            $tableName = $this->getTable() . $shopId;

            if (!$schemaConnection->hasTable($tableName))
            {
                return false;
            }

            if ($schemaConnection->hasColumn($tableName, $columnName)) {
                return true;
            }
            $result = $schemaConnection->table($tableName, function($table) use (&$columnName)
            {
                $table->longText($columnName)->nullable();
            });
            return $result;
        } catch (\Exception $ex) {
//            $client->captureException($ex);
            return false;
        }
    }



    /**
     * Import product from api and webhook update-add product in shopify
     *
     * @param $shopId
     * @param $products
     *
     * @return bool
     */
    public function import( $shopId, $products ) {
        foreach ( $products as $key => $product ) {
            $openConnect = DB::connection( $this->getConnectionName() );
            $dbProducts  = $openConnect->table( $this->getTableProduct( $shopId ) );

            if ( is_array( $product ) ) {
                $product['image']      = isset( $product['image']['src'] ) ? $product['image']['src'] : null;
                $product['created_at'] = isset( $product['created_at'] ) ? date( 'Y-m-d H:i:s', strtotime( $product['created_at'] ) ) : date( 'Y-m-d H:i:s' );
                $product['updated_at'] = isset( $product['updated_at'] ) ? date( 'Y-m-d H:i:s', strtotime( $product['updated_at'] ) ) : date( 'Y-m-d H:i:s' );
            } else {
                $product->image      = isset( $product->image->src ) ? $product->image->src : null;
                $product->created_at = isset( $product->created_at ) ? date( 'Y-m-d H:i:s', strtotime( $product->created_at ) ) : date( 'Y-m-d H:i:s' );
                $product->updated_at = isset( $product->updated_at ) ? date( 'Y-m-d H:i:s', strtotime( $product->updated_at ) ) : date( 'Y-m-d H:i:s' );
                $product             = get_object_vars( $product );
            }

            $total_review = $this->_commentBackendRepo->getTotalReview( $product['id'], $shopId );
            if ( $total_review ) {
                $product['is_reviews'] = 1;
            }

            $dataSave = $this->filterProductDatabase( $product );

            $productInfo = $dbProducts->where( 'id', $dataSave['id'] )->first();

            $client = new \Raven_Client( env( 'SENTRY_DSN' ) );
            $client->user_context( array(
                'shop_id' => $shopId,
            ) );

            if ( empty( $productInfo ) ) {
                try {
                    $dbProducts->insert( $dataSave );
                } catch ( \Exception $exception ) {
                    $client->captureException( $exception );
                }
            } else {
                try {
                    $id = $dataSave['id'];
                    unset( $dataSave['id'] );
                    $dbProducts->where( 'id', $id )->update( $dataSave );
                } catch ( \Exception $exception ) {
                    $client->captureException( $exception );
                }

            }
        }

        return true;
    }

    /**
     * Convert product to product save database
     *
     * @param $data
     *
     * @return mixed
     */
    public function filterProductDatabase( $data ) {
        $fillable = $this->_productModel->getFillable();
        foreach ( $data as $k => $v ) {
            if ( ! in_array( $k, $fillable ) ) {
                unset( $data[ $k ] );
            }
        }

        return $data;
    }


}