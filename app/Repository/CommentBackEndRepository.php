<?php

namespace App\Repository;


use App\Contracts\Repository\CommentBackendRepositoryInterface;
use App\Factory\RepositoryFactory;
use App\Helpers\Helpers;
use App\Models\CommentsModel;
use App\ShopifyApi\ProductsApi;
use Faker\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;

/**
 * Class CommentBackEndRepository
 * @package App\Repository
 */
class CommentBackEndRepository implements CommentBackendRepositoryInterface {
	/**
	 * @var \Illuminate\Foundation\Application|mixed
	 */
	private $_commentModel;


	private $sentry;

	/**
	 * CommentBackEndRepository constructor.
	 *
	 */
	public function __construct() {
		$this->_commentModel = new CommentsModel();
//		$this->sentry = app('sentry');
	}

	/**
	 * Created table comment
	 *
	 * @param $shopId
	 *
	 * @return bool|string
	 */
	public function createTable( $shopId ) {
		$table = 'comment_' . $shopId;
		if ( Schema::hasTable( $table ) ) {
			return true;
		}

		return $this->_commentModel->createTableComment( $shopId );
	}

	/**
	 * Get list review
	 *
	 * @param $shopId
	 * @param $params
	 *
	 * @return mixed
	 */
	public function all($shopId = '', $params = [])
	{
		try {
			$table = $this->_commentModel->getTableComment($shopId);
			$query = DB::table($table);

			$perPage = isset($params['perPage']) ? $params['perPage'] : config('common.pagination');

			if (!empty($params['product_id'])) {
				$query->where('product_id', '=', $params['product_id']);
			}

			if (!empty($params['keyword'])) {
				$query->where('content', 'like', '%' . trim($params['keyword']) . '%');
			}

			if (!empty($params['status'])) {
				if ($params['status'] == 'publish') {
					$query->where('status', '=', 1);
				} else {
					$query->where('status', '!=', 1);
				}
			}

			if (!empty($params['star'])) {
				if (is_array($params['star'])) {
					$query->whereIn('star', $params['star']);
				} else {
					$query->where('star', '=', $params['star']);
				}
			}

			if (!empty($params['source'])) {
				if (is_array($params['source'])) {
					$query->whereIn('source', $params['source']);
				} else if ($params['source'] === 'all') {
					$sourceInternal = array_keys(config('common.review_sources'));
					$query->whereIn('source', $sourceInternal);
				} else {
					$query->where('source', '=', $params['source']);
				}
			}

			if (!empty($params['from'])) {
				$query->whereDate('created_at', '>=', $params['from']);
			}
			if (!empty($params['to'])) {
				$query->whereDate('created_at', '<=', $params['to']);
			}

			if (Schema::hasColumn($table, 'pin')) { 
				$query->orderBy('pin', 'DESC');
			}

			$total = $query->count();

			if (!is_numeric($perPage) && !empty($total)) {
				$result = $query->orderBy('created_at', 'desc')->paginate($total);
			} else {
				$result = $query->orderBy('created_at', 'desc')->paginate($perPage);
			}

			if ($result->total()) {
				foreach ($result as $comment) {
					if (!empty($comment->img)) {
						$comment->img = json_decode($comment->img);
					}

					if (!empty($comment->product_id)) {
						$productRepo = new ProductsRepository();
						$product = $productRepo->detail($shopId, $comment->product_id);
						$comment->product_info = $product;
					}
				}
			}

			return $result;
		} catch (\Exception $ex) {
			$this->sentry->captureException($ex);
			return [];
		}
	}


	/**
	 * Get detail comment
	 *
	 * @param $shopId
	 * @param $commentId
	 *
	 * @return mixed
	 */
	public function detail( $shopId, $commentId ) {
		$comment = DB::table( $this->_commentModel->getTableComment( $shopId ) )->where( 'id', $commentId )->first();
		if ( ! empty( $comment ) ) {
			if ( ! empty( $comment->img ) ) {
				$comment->img = json_decode( $comment->img );
			}
		}

		return $comment;
	}

	public function findReview($shopId = '', $productId = '', $data = [])
	{
		$this->sentry->user_context([
			'shopId' => $shopId,
			'product_id' => $productId,
			'data' => $data
		]);
		try {
			$tableComment = $this->_commentModel->getTableComment($shopId);
			$query = DB::table($tableComment);
			if (!empty($productId)) {
				$query->where('product_id', $productId);
			}
			if (array_key_exists('source', $data)) {
				if (is_array($data['source'])) {
					$query->whereIn('source', $data['source']);
				} else {
					$query->where('source', $data['source']);
				}
			}

			if (array_key_exists('status', $data)) {
				$query->where('status', $data['status']);
			}
			if (array_key_exists('limit', $data)) {
				$limit = (int) $data['limit'];
				$query->take($limit);
			}
			return ['status' => true, 'result' => $query];
		} catch (\Exception $ex) {
			$eventId = $this->sentry->captureException($ex);
			return ['status' => false, 'message'=> "{$ex->getMessage()}. EventId: {$eventId}"];
		}
	}


	/**
	 * Get average ratting
	 *
	 * @param $productId
	 * @param $shopId
	 *
	 * @return float
	 */
	public function getAvgStar( $productId, $shopId, $status = 0 ) {
		$avg = DB::table( $this->_commentModel->getTableComment( $shopId ) )
		         ->where( 'product_id', '=', $productId );
		if(!empty($status)){
			$avg->where( 'status', '=',config('common.status.publish') );
		}

		return round( $avg->avg( 'star' ),1 );
	}


	/**
	 * Get total review by star
	 *
	 * @param $productId
	 * @param $shopId
	 * @param null $star
	 * @param array $status
	 *
	 * @return mixed
	 */
	public function getTotalStar( $productId, $shopId, $star = null, $status = [] ) {
		$query = DB::table( $this->_commentModel->getTableComment( $shopId ) )
		           ->where( 'product_id', '=', $productId );
		if ( ! empty( $status ) ) {
			$query->whereIn( 'status', $status );
		}
		if ( isset( $star ) ) {
			$query->where( 'star', '=', $star );
		}

		$total = $query->count( 'star' );

		return $total;
	}


	/**
	 * Get total review by status
	 *
	 * @param $productId
	 * @param $shopId
	 * @param null $status
	 *
	 * @return mixed
	 */
	public function getTotalStatus( $productId, $shopId, $status = null ) {
		$query = DB::table( $this->_commentModel->getTableComment( $shopId ) )->where( 'product_id', '=', $productId );
		if ( isset( $status ) ) {
			$query->where( 'status', '=', $status );
		}

		$total = $query->count( 'status' );

		return $total;
	}

	public function getTotalReview($productId, $shopId)
	{
		$totalReview = DB::table( $this->_commentModel->getTableComment( $shopId ) )->where( 'product_id', '=', $productId )->count('id');
		return $totalReview;
	}

	/**
	 * Convert data to save comment
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function convertDataSave( $data ) {
		$data['updated_at'] = date( 'Y-m-d H:i:s', time() );
		if(!empty($data['img'])){
			$data['img'] = json_encode($data['img']);
		}

		/*if(empty($data['avatar'])){
			$data['avatar'] = Helpers::getAvatarAbstract();
		}*/

		return $data;
	}


	/**
	 * Insert comment
	 *
	 * @param $shopId
	 * @param $data
	 *
	 * @return array
	 */
	public function insert($shopId, $data ){
		$result = array(
			'status'  => 'error',
			'message' => Lang::get( 'reviews.fail' ),
		);

		$data = $this->convertDataSave( $data );
		$update = DB::table( $this->_commentModel->getTableComment( $shopId ) )->insert( $data );
		if ( $update ) {
			$result = array(
				'status'  => 'success',
				'message' => Lang::get( 'reviews.updateSuccess' ),
			);
		}

		return $result;
	}

	/**
	 * Update comment
	 *
	 * @param $shopId
	 * @param $commentId
	 * @param $data
	 *
	 * @return array
	 */
	public function update($shopId = '', $commentId = '', $data = [])
	{
		$this->sentry->user_context([
			'shop_id' => $shopId,
			'comment_id' => $commentId,
			'data' => $data
		]);
		try {
			$result = [
				'status'  => 'error',
				'message' => trans('reviews.fail'),
			];

			if (!empty($commentId)) {
				$data = $this->convertDataSave($data);

				$update = DB::table($this->_commentModel->getTableComment($shopId))->where('id', $commentId)->update($data);

				if ($update) {
					$result = [
						'status'  => 'success',
						'message' => trans('reviews.updateSuccess'),
					];
				}
			}

			return $result;
		} catch (\Exception $ex) {
			$eventId = $this->sentry->captureException($ex);
			return [
				'stauts' => 'error',
				'message' => "{$ex->getMessage()}. EventId: {$eventId}"
			];
		}
	}

	/**
	 * Delete comment
	 *
	 * @param $shopId
	 * @param $commentId
	 *
	 * @return array
	 */
	public function delete( $shopId, $commentId ): array {
		$result = array(
			'status'  => 'error',
			'message' => Lang::get( 'reviews.fail' ),
		);

		if ( ! empty( $commentId ) ) {
			$comment = $this->detail($shopId,$commentId);
			if($comment){
				$delete = DB::table( $this->_commentModel->getTableComment( $shopId ) )->where( 'id', $commentId )->delete();

				if ( $delete ) {

					/**
					 *  echo if is last comment to change is_reviews of product to 0
					 */
					$productRepo = RepositoryFactory::productsRepository();
					$list_product_comments = $this->all($shopId,['product_id' => $comment->product_id]);
					if($list_product_comments->total() == 0){
						$productRepo->update($shopId,$comment->product_id,['is_reviews' => 0]);
					}

					$result = array(
						'status'  => 'success',
						'message' => Lang::get( 'reviews.deleteSuccess' ),
					);
				}
			}
		}

		return $result;
	}

	public function deleteComments($shopId = '', $comment = [])
	{
		$this->sentry->user_context([
			'shop_id' => $shopId,
			'comments' => $comment
		]);

		try {
			$result = [
				'status' => 'error',
				'message' => trans('reviews.fail')
			];

			DB::beginTransaction();

			$tableComment = DB::table($this->_commentModel->getTableComment($shopId));

			if (!empty($comment)) {
				$state = $tableComment->whereIn('id', $comment)->delete();

				if ($state) {
					DB::commit();					
					return [
						'status' => 'success',
						'message' => trans('reviews.deleteSuccess')
					];
				}
			}
			return $result;
		} catch (\Exception $ex) {
			$eventId = $this->sentry->captureException($ex);
			DB::rollback();
			return [
				'status' => 'error',
				'message' => "{$ex->getMessage()}. EventId: {$eventId}"
			];
		}
	}


	public function updateComments($shopId = '', $comment = [], $data = [])
	{
		$this->sentry->user_context([
			'shop_id' => $shopId,
			'comments' => $comment
		]);

		try {
			$result = [
				'status' => 'error',
				'message' => trans('reviews.fail')
			];

			DB::beginTransaction();

			$tableComment = DB::table($this->_commentModel->getTableComment($shopId));

			if (!empty($comment)) {
				$state = $tableComment->whereIn('id', $comment)->update($data);
				DB::commit();					
				return [
					'status' => 'success',
					'message' => trans('reviews.updateSuccess')
				];
			}
			return $result;
		} catch (\Exception $ex) {
			$eventId = $this->sentry->captureException($ex);
			DB::rollback();
			return [
				'status' => 'error',
				'message' => "{$ex->getMessage()}. EventId: {$eventId}"
			];
		}
	}

	/**
	 * Update all comment in product
	 *
	 * @param $shopId
	 * @param $productId
	 * @param $data
	 *
	 * @return array
	 */
	public function updateAll($shopId,$productId, $data): array {
		$result = array(
			'status'  => 'error',
			'message' => Lang::get( 'reviews.fail' ),
		);
		$data = $this->convertDataSave( $data );

		$update = DB::table( $this->_commentModel->getTableComment( $shopId ) )->where('product_id', $productId)->update( $data );

		if ( $update ) {
			$result = array(
				'status'  => 'success',
				'message' => Lang::get( 'reviews.updateSuccess' ),
			);
		}

		return $result;
	}

	public function updateAllCommentBySource($shopId = '', $source = '', $data = []) 
	{
		$this->sentry->user_context(array(
			'shop_id' => $shopId,
			'source' => $source,
			'data' => $data
		));
		DB::beginTransaction();

		try {

			$query = DB::table($this->_commentModel->getTableComment($shopId));
			if (is_array($source)) {
				$query->whereIn('source', $source);
			} else {
				$query->where('source', $source);
			}

			$update = $query->update($data);
			DB::commit();

		} catch (\Exception $ex) {
			$this->sentry->captureException($ex);			
			DB::rollback();
		}
	}

	public function pipeUpdateAndSetPublish($shopId = '', $source = '', $numberPublish = 10)
	{
		$this->sentry->user_context(array(
			'shop_id' => $shopId,
			'source' => $source,
			'numberPublish' => $numberPublish
		));

		DB::beginTransaction();

		try {

			$tableComment = $this->_commentModel->getTableComment($shopId);
			// first set all reviews to disable
			$updateDisable = DB::table($tableComment)
			->where('source', $source)
			->update(['status' => 0]);

			// second update all reviews to active limit by numberPublish
			$updateActive = DB::table($tableComment)
			->where('source', $source)
			->take($numberPublish)
			->update(['status' => 1]);			
			DB::commit();

		} catch (Exception $ex) {
			$this->sentry->captureException($ex);			
			DB::rollback();
		}
	}

	public function updateCommentLimit($shopId, $source, $limit = 1, $status = 1)
	{
		$this->sentry->user_context(array(
			'shop_id' => $shopId,
			'source' => $source,
			'limit' => $limit
		));

		DB::beginTransaction();

		try {

			$tableComment = $this->_commentModel->getTableComment($shopId);
			$affected = DB::update("update {$tableComment} set status = ? where source = ? limit ?", [$status, $source, $limit]);
			DB::commit();

		} catch (\Exception $ex) {
			$this->sentry->captureException($ex);			
			DB::rollback();
		}
	}




	/**
	 * Delete all review in product
	 *
	 * @param $shopId
	 * @param $productId
	 *
	 * @return array
	 */
	public function deleteAll( $shopId,$productId ): array {
		$result = array(
			'status'  => 'error',
			'message' => Lang::get( 'reviews.fail' ),
		);

		$delete = DB::table( $this->_commentModel->getTableComment( $shopId ) )->where('product_id', $productId)->delete();

		if ( $delete ) {
			$productRepo = RepositoryFactory::productsRepository();
			$productRepo->update($shopId,$productId,['is_reviews' => 0]);

			$result = array(
				'status'  => 'success',
				'message' => Lang::get( 'reviews.deleteSuccess' ),
			);
		}

		return $result;
	}

	public function delReviewByProductList( $shopId,$productIdList = [] ) {
		$result = array(
			'status'  => 'error',
			'message' => Lang::get( 'reviews.fail' ),
		);

		$delete = DB::table( $this->_commentModel->getTableComment( $shopId ) )->whereIn('product_id', $productIdList)->delete();

		if ( $delete ) {
			foreach($productIdList as $productId) {
				$productRepo = RepositoryFactory::productsRepository();
				$productRepo->update($shopId,$productId,['is_reviews' => 0]);
			}
			

			$result = array(
				'status'  => 'success',
				'message' => Lang::get( 'reviews.deleteSuccess' ),
			);
		}

		return $result;
	}

	/**
	 * @param $shop_id
	 * @param $product_id
	 *
	 * @return bool
	 */
	public function deleteCommentByProduct($shop_id, $product_id)
	{
		$table = $this->_commentModel->getTableComment($shop_id);
		$is_del = DB::table($table)->where('product_id', $product_id)->delete();
		return $is_del;
	}

	/**
	 * Delete comments by source
	 * @param String $shopId
	 * @param String $source
	 * @return boolean
	 */

	public function deleteCommentBySource($shopId = '', $source = 'default')
	{
		$client =new \Raven_Client(env('SENTRY_DSN'));
		$client->user_context(array(
			'shop_id' => $shopId,
		));
		DB::beginTransaction();
		try {
			$table = $this->_commentModel->getTableComment($shopId);
			$query = DB::table($table)->where('source', $source);
			$comments = DB::table($table)->where('source', $source)->distinct('product_id')->get();
			$productRepo = RepositoryFactory::productsRepository();
			foreach($comments as $comment) {
				$productRepo->update($shopId, $comment->product_id, ['is_reviews' => 0]);
			}
			$query->delete();
			DB::commit();
			return true;
		} catch (\Exception $ex) {
			$client->captureException($ex);			
			DB::rollback();
			return false;
		}
	}


	/**
	 * @param $shop_id
	 *
	 * @return bool
	 */
	public function deleteCommentByShop($shop_id)
	{
		$table = $this->_commentModel->getTableComment($shop_id);
		$is_del = DB::table($table)->delete();
		return $is_del;
	}


	/**
	 * @param $shopId
	 * @param $productId
	 * @param $reviewObj
	 * @param $type
	 * @return bool
	 */
	public function saveObjReviewAliexpress($shopId = '', $productId = '', $reviewObj, $type ='', $shopPlanInfo = array(), $review_source = 'aliexpress')
	{
		$this->sentry->user_context(array(
			'shop_id' => $shopId,
			'type' => $type,
			'product_id' => $productId,
			'review_source' => $review_source,
			'shop_plan_info' => $shopPlanInfo,
			'review_obj' => $reviewObj
		));

		$tableName = $this->_commentModel->getTableComment($shopId);
		$data = [];
		$faker = Factory::create();

		if (empty($reviewObj)) {
			return false;
		}

		$total_reviews_publish_product = -1;
		if (!empty($shopPlanInfo['total_reviews_publish_product'])) {
			$total_reviews_publish_product = $shopPlanInfo['total_reviews_publish_product'];

			// nếu không xóa hết review cũ thì cần kiểm tra số review đã publish trước để tính đc số review đc phép publish còn lại
			if ($type != 'del_add_new') {
				$list_review_publish = $this->all( $shopId, [
					'product_id' => $productId,
					'status'     => 'publish',
					'source'     => ['aliexpress', 'oberlo'],
				] );
				
				$total_list_review_publish = $list_review_publish->total();
				if (!empty($total_list_review_publish)) {
					if ($total_list_review_publish >= $total_reviews_publish_product) {
						$total_reviews_publish_product = 0;
					} else {
						$total_reviews_publish_product = $total_reviews_publish_product - $total_list_review_publish;
					}
				}
			}
		}

		foreach ($reviewObj as $k => $v) {
			if (is_object($v)) {
				$v= (array) $v;
			}

			$status = config('common.status.publish');
			if ($total_reviews_publish_product != -1) {
				if ((($k + 1) > $total_reviews_publish_product)) {
					$status = config('common.status.unpublish');
				}
			}

			$data[] = [
				'product_id' => $productId,
				'author' => $faker->firstName . ' ' . $faker->lastName,
				'avatar' => Helpers::getAvatarAbstract(),
				'email' => $faker->email,
				'country' => isset($v['user_country']) ? $v['user_country'] : 'US',
				'star' => isset($v['review_star']) ? $v['review_star'] : 5,
				'content' => isset($v['review_content']) ? $v['review_content'] : '',
				'img' => ( ! empty($v['review_image_str'])) ? json_encode($v['review_image_str']) :  null,
				'source' => $review_source,
				'verified' => config('common.status.publish'),
				'status' => $status,
				'created_at' => isset($v['review_date']) ? date('Y-m-d H:i:s', strtotime($v['review_date'])) : date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			];
		}

		DB::beginTransaction();

		try {
			// delete all review if request was del_add_new
			if ($type == 'del_add_new') {
				DB::table($tableName)->where('product_id', '=', $productId)->where('source', '=', $review_source)->delete();
			}

			DB::table($tableName)->insert($data);
			DB::commit();
		} catch (Exception $exception) {
			DB::rollback();
			$this->sentry->captureException($exception);
			return false;
		}

		return true;
	}

	public function addPinColumn($shopId){
		$tableName = $this->_commentModel->getTableComment($shopId);
		if (Schema::hasColumn($tableName, 'pin'))
			return true;

		return Schema::table($tableName, function($table ){
			$table->integer("pin")->default(0);
		});
	}


	public function addLikeColumn($shopId){
		$tableName = $this->_commentModel->getTableComment($shopId);
		if (Schema::hasColumn($tableName, 'like'))
			return true;

		return Schema::table($tableName, function($table ){
			$table->integer("like")->default(0);
		});
	}

	public function addUnLikeColumn($shopId){
		$tableName = $this->_commentModel->getTableComment($shopId);
		if (Schema::hasColumn($tableName, 'unlike'))
			return true;

		return Schema::table($tableName, function($table ){
			$table->integer("unlike")->default(0);
		});
	}

}