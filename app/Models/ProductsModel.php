<?php
/**
 * Created by PhpStorm.
 * User: dev02
 * Date: 7/26/2018
 * Time: 4:28 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProductsModel extends Model
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
        parent::__construct($attributes);
        $this->setConnection('mysql_product');
    }
}