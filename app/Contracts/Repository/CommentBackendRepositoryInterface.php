<?php

namespace App\Contracts\Repository;

/**
 * Interface CommentBackendRepositoryInterface
 * @package App\Contracts\Repository
 */
interface CommentBackendRepositoryInterface
{

	/**
	 * @param $shopId
	 * @param $params
	 *
	 * @return mixed
	 */
	public function all($shopId,$params);

	/**
	 * @param $shopId
	 * @param $commentId
	 *
	 * @return mixed
	 */
	public function detail($shopId,$commentId);

	/**
	 * @param $shopId
	 * @param $data
	 * @param $commentId
	 *
	 * @return array
	 */
	public function update($shopId, $commentId, $data);


	/**
	 * @param $shopId
	 * @param $commentId
	 *
	 * @return array
	 */
	public function delete($shopId, $commentId):array ;
}