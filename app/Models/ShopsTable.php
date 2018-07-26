<?php
/**
 * Created by PhpStorm.
 * User: dev02
 * Date: 7/26/2018
 * Time: 11:05 AM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ShopsTable extends Model
{

    protected $table = 'shop' ;
    protected $fillable = [
        'shop_id',
        'shop_name',
        'shop_email',
        'shop_status',
        'is_review_app',
        'shop_country',
        'shop_owner',
        'plan_name',
        'app_version',
        'app_plan',
        'init_app',
        'billing_id',
        'billing_on',
        'cancelled_on',
        'code_invite',
        'trial_date',
        'are_trial',
        'access_token',
        'created_at',
        'updated_at'
    ];


    protected $primaryKey = 'shop_id' ;


    public function getAll(){
        return $this->get()->toArray() ;
    }

    public function detail(array $field)
    {
        $shopId = session('shopId');

//        $this->sentry->user_context([
//            'shop_id' => $shopId
//        ]);

        try {
            $shopInfo = $this->where($field)->first();
            if (empty($shopInfo)) {
                return ['status' => false, 'message' => 'Shop detail NULL'];
            }
            return ['status' => true, 'shopInfo' => $shopInfo];
        } catch (\Exception $ex) {
//            $eventId = $this->sentry->captureException($ex);
            return ['status' => false, 'message' => "Error get shop detail. EventId: "];
        }
    }

    public function inserts(array  $attribute){

        try{
            $shopModel = $this->findOrNew($attribute['shop_id']);

            foreach ($this->getFillable() as $k=>$v)
            {
                if(key_exists($v, $attribute))
                    $shopModel->setAttribute($v, $attribute[$v]);
            }

            $isSave = $shopModel->save();
            if($isSave)
                return ['status' => true, 'message' => ''];
            return ['status' => false, 'message' => 'Cannot save info shop'];

        } catch (\Exception $exception)
        {

            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }
}