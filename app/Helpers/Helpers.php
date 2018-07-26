<?php
namespace App\Helpers;

use App\Factory\RepositoryFactory;
use App\Repository\CommentBackEndRepository;
use Illuminate\Support\Facades\Log;

class Helpers
{
	/**
	 * @param $currentPage
	 * @param $totalItem
	 * @param $itemInPage
	 * @param $filterPagination
	 * @return string
	 */
	public static function paginationApi($currentPage, $totalItem, $itemInPage, $filterPagination)
	{
		$totalPage = ceil($totalItem/$itemInPage);
		$view = view('sections.paginations', compact('currentPage', 'totalPage', 'filterPagination'))->render();
		return $view;
	}

	/**
	 * @return array|mixed
	 */
	public static function getCountryCode()
	{
		$file = storage_path('json/country.json');
		$countryCode = [];
		$data = [];
		if( ! file_exists($file))
			return $countryCode;

		$source = file_get_contents($file);
		$countryCode = json_decode($source, true);

		foreach ($countryCode as $key => $value) {
			$data[$value['Code']] = $value['Name'];
		}
		return $data;
	}

	/**
	 * @param $type
	 * @param $arrayLog
	 */
	public  static function saveLog($type, $arrayLog)
	{
		$message = isset($arrayLog['message']) ? $arrayLog['message'] : '';
		$file = isset($arrayLog['file']) ? ' File: '.$arrayLog['file'] : '';
		$line = isset($arrayLog['line']) ? ' Line: '.$arrayLog['line'] : '';
		$function = isset($arrayLog['function']) ? ' Function: '.$arrayLog['function'] : '';
		$domain =  isset($arrayLog['domain']) ? ' Domain: '.$arrayLog['domain'] : '';
		Log::$type($message.$file.$line.$function.$domain);
	}

	/**
	 * @return array
	 */
	public static function getDefaultCountryCode(){
		$result = array();

		$listCountry = self::getCountryCode();
		//unset($listCountry['RU']);
		foreach ($listCountry as $k=> $v){
			$result[] = $k;
		}

		return $result;
	}

	/**
	 * @return string
	 */
	public static function getAvatarAbstract() {
		$id = rand(1,199);
		return 'images/avatar/abstract/avatar'.$id.'.jpg';
	}


	public  static  function human_time_diff( $from, $to = '',$translate=array() ) {
		$since = '';
		if ( empty( $to ) ) {
			$to = time();
		}
		if(empty($translate)){
			$translate = config('settings')['translate'];
		}

		$diff = (int) abs( $to - $from );

		if ( $diff < config('custom.TIME_SECONDS.HOUR_IN_SECONDS')) {
			$mins = round( $diff / config('custom.TIME_SECONDS.MINUTE_IN_SECONDS') );
			if ( $mins <= 1 ) {
				$mins = 1;
				$since = $mins.' '.$translate['min'];
			} else
				$since = $mins.' '.$translate['mins'];
		} elseif ( $diff < config('custom.TIME_SECONDS.DAY_IN_SECONDS') && $diff >= config('custom.TIME_SECONDS.HOUR_IN_SECONDS') ) {
			$hours = round( $diff / config('custom.TIME_SECONDS.HOUR_IN_SECONDS') );
			if ( $hours <= 1 ) {
				$hours = 1;
				$since = $hours.' '.$translate['hour'];
			} else
				$since = $hours.' '.$translate['hours'];
		} elseif ( $diff < config('custom.TIME_SECONDS.WEEK_IN_SECONDS') && $diff >= config('custom.TIME_SECONDS.DAY_IN_SECONDS') ) {
			$days = round( $diff / config('custom.TIME_SECONDS.DAY_IN_SECONDS') );
			if ( $days <= 1 ) {
				$days = 1;
				$since = $days.' '.$translate['day'];
			} else
				$since = $days.' '.$translate['days'];
		} elseif ( $diff < config('custom.TIME_SECONDS.MONTH_IN_SECONDS') && $diff >= config('custom.TIME_SECONDS.WEEK_IN_SECONDS') ) {
			$weeks = round( $diff / config('custom.TIME_SECONDS.WEEK_IN_SECONDS') );
			if ( $weeks <= 1 ) {
				$weeks = 1;
				$since = $weeks.' '.$translate['week'];
			} else
				$since = $weeks.' '.$translate['weeks'];
		} elseif ( $diff < config('custom.TIME_SECONDS.YEAR_IN_SECONDS') && $diff >= config('custom.TIME_SECONDS.MONTH_IN_SECONDS') ) {
			$months = round( $diff / config('custom.TIME_SECONDS.MONTH_IN_SECONDS') );
			if ( $months <= 1 )
			{
				$months = 1;
				$since = $months.' '.$translate['month'];
			} else
				$since = $months.' '.$translate['months'];

		} elseif ( $diff >= config('custom.TIME_SECONDS.YEAR_IN_SECONDS') ) {
			$years = round( $diff / config('custom.TIME_SECONDS.YEAR_IN_SECONDS') );
			if ( $years <= 1 ) {
				$years = 1;
				$since = $years.' '.$translate['year'];
			} else
				$since = $years.' '.$translate['years'];
		}

		return $since.' ' .$translate['text_ago'];
	}
   /*
	public static function getTotalReviewInProduct($shopId, $productId)
	{
		$commentBackendRepo = new CommentBackEndRepository();
		return $commentBackendRepo->getTotalReview($productId, $shopId);
	}

	public static function getAvgReviewInProduct($shopId, $productId)
	{
		$commentBackendRepo = new CommentBackEndRepository();
		return $commentBackendRepo->getAvgStar($productId, $shopId);
	}
    */
	public static function getStarRating($rating)
	{
		$rating = ($rating <= 5 || $rating >= 0) ? $rating : 0;
		$rating = floor($rating * 2)/2;

		$str = '<div class="rate-it">';
		$strRating = '<i class="demo-icon icon-star-2 rated"></i>';
		$strRatingHalf = '<i class="demo-icon icon-star-half-alt rated"></i>';
		$strRatingEmpty = '<i class="demo-icon icon-star-empty-1 rated"></i>';

		for($i = 0; $i < 5; $i++)
		{
			if($rating == $i + 0.5)
				$str .= $strRatingHalf;
			elseif($rating > $i)
				$str .= $strRating;
			else
				$str .= $strRatingEmpty;
		}

		$str .=  '</div>';

		return $str;

	}


	/**
	 * Get settings default
	 * @param $shop_id
	 * @return array|mixed
	 */

	public static function getSettings($shop_id){
		$shop_meta_repo = RepositoryFactory::shopsMetaReposity();
		$shop_meta = $shop_meta_repo->detail( $shop_id );

		if ( empty( $shop_meta ) ) {
			return config( 'settings' );
		}

		$settings = $shop_meta->toArray();
		if ( $settings['setting'] ) {
			$settings['setting'] = (array) json_decode( $settings['setting'] );
		}

		if(!empty($settings['translate'])){
			$settings['translate'] = (array) json_decode( $settings['translate'] );
		}else{
			$settings['translate'] = config( 'settings' )['translate'];
		}

		/*if(!empty($settings['except_keyword'])){
			$settings['except_keyword'] = (array) json_decode( $settings['except_keyword'] );
		}else{
			$settings['except_keyword'] = config( 'settings' )['except_keyword'];
		}*/

		if(!empty($settings['style_customize'])){
			$settings['style_customize'] = (array) json_decode( $settings['style_customize'] );
		}else{
			$settings['style_customize'] = config( 'settings' )['style_customize'];
		}

		if(!empty($settings['rand_comment_default'])){
			$settings['rand_comment_default'] = (array) json_decode( $settings['rand_comment_default'] );
		}else{
			$settings['rand_comment_default'] = config( 'settings' )['rand_comment_default'];
		}

		return $settings;
	}

	public static function getAvgStarInObjectRating($args)
	{
		$totalRating = 0;
		$countRating  = 0;
		foreach ($args as $k => $v)
		{
			if(is_object($v)){
				$v= (array) $v;
			}

			if(isset($v['review_star']))
			{
				$totalRating = $totalRating+$v['review_star'];
				$countRating++;
			}
		}
		return $totalRating/$countRating;
	}


	// Function to get the client IP address
	public static function getClientIp() {
		if( array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
			if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')>0) {
				$addr = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
				return trim($addr[0]);
			} else {
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		}
		else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}

    /**
     * Get client ip from shopify web proxy request
     * @return string
     */
    public static function getProxyClientIp() {
        if(!empty($_SERVER['HTTP_X_SHOPIFY_CLIENT_IP'])) {
            return $_SERVER['HTTP_X_SHOPIFY_CLIENT_IP'];
        }
        return self::getClientIp();
    }

	public static function randomAvatarByName($name){
		$args_name = explode(' ',$name);
		$first_char = '';
		foreach ($args_name as $k => $v){
			if($k <=1){
				$first_char .= substr($v,0,1);
			}
		}
		$color = dechex(rand(0x000000, 0xFFFFF0));
		$htm = "<div class='alireviewsAvatarByName' style='background: #$color;width: 70px;height: 70px;text-align: center;
color: white;
    text-transform: uppercase;
    font-weight: bold;
        line-height: 70px;'>$first_char</div>";

		return $htm;
	}

	public static function shortName($name){
		$args_name = explode(' ',$name);
		$first_char = '';
		foreach ($args_name as $k => $v){
			if($k < 1){
				$first_char .= substr($v,0,1).'**** ';
			}else{
				$first_char .= $v;
			}
		}

		return $first_char;
	}

	public static function getBrowser()
	{
		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version= "";
		$ub = '';

		//First get the platform?
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
		}
		elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		}
		elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}

		if (preg_match('/Mobile/i', $u_agent)) {
			$platform = 'mobile';
		}

		// Next get the name of the useragent yes seperately and for good reason
		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
		{
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		}
		elseif(preg_match('/Firefox/i',$u_agent))
		{
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		}
		elseif(preg_match('/Chrome/i',$u_agent))
		{
			$bname = 'Google Chrome';
			$ub = "Chrome";
			if(preg_match('/Edge/i',$u_agent)){
				$bname = 'Edge';
				$ub = "Edge";
			}
		}
		elseif(preg_match('/Safari/i',$u_agent))
		{
			$bname = 'Apple Safari';
			$ub = "Safari";
		}
		elseif(preg_match('/Opera/i',$u_agent))
		{
			$bname = 'Opera';
			$ub = "Opera";
		}
		elseif(preg_match('/Netscape/i',$u_agent))
		{
			$bname = 'Netscape';
			$ub = "Netscape";
		}

		return array(
			'userAgent' => $u_agent,
			'name'      => $bname,
			'code'      => $ub,
			'version'   => $version,
			'platform'  => $platform,
		);
	}
}