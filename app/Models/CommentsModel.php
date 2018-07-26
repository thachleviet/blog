<?php

namespace App\Models;


use App\Events\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

/**
 * Class CommentsModel
 * @package App\Models
 */
class CommentsModel extends Model {

    use Notifiable;
	/**
	 * @var string
	 */
	protected $table = 'comment_';

	/**
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * @var string
	 */
	public $prefix = 'alireview_';

	/**
	 * @var array
	 */
	protected $fillable = [
		'product_id',
		'author',
		'country',
		'user_order_info',
		'star',
		'content',
		'email',
		'avatar',
		'img',
		'status',
		'source',
		'verified',
		'pin',
		'like',
		'unlike',
	];

    /**
     * Event Models
     * @var array
     */
    protected $dispatchesEvents = [
        //'deleted' => Event::class
    ];
	/**
	 * @return array
	 */
	public function fillableFrontend() {

		$fillable = [];
		foreach ( $this->fillable as $k => $v ) {
			$fillable[] = $this->prefix . $v;
		}
		//add shop_id in request front end
		$fillable[] = $this->prefix . 'shop_id';
		$fillable[] = $this->prefix . 'country_code';

		return $fillable;
	}

	/**
	 * @param string $shop_id
	 *
	 * @return string
	 */
	public function getTableComment( $shop_id = '' ) {
		$table = $this->table . $shop_id;
		if ( ! Schema::hasTable( $table ) ) {
			$this->createTableComment( $shop_id );
		}

		return $table;
	}

	/**
	 * Create table comment by shop id
	 *
	 * @param $shopId
	 *
	 * @return string
	 */
	public function createTableComment( $shop_id ) {
		Schema::create( $this->table . $shop_id, function ( Blueprint $table ) {
			$table->bigIncrements( 'id' );
			$table->string( 'product_id' );
			$table->string( 'author', 255 )->nullable();
			$table->string( 'country', 20 )->nullable();
			$table->integer( 'star' )->nullable();
			$table->text( 'content' )->nullable();
			$table->mediumText( 'img' )->nullable();
			$table->mediumText( 'user_order_info' )->nullable();
			$table->string( 'email', 255 )->nullable();
			$table->string( 'avatar', 255 )->nullable();
			$table->integer( 'status' )->nullable();
			$table->integer( 'verified' )->nullable();
			$table->integer( 'pin' )->default(0);
			$table->integer( 'like' )->default(0);
			$table->integer( 'unlike' )->default(0);
			$table->string( 'source', 255 )->nullable();
			$table->timestamps();
		} );

		return $this->table;
	}
}
