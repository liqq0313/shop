<?php
namespace app\index\model;
use think\Model;
class Order extends Model
{
	public function getGoods(){
		return $this->hasOne('goods' , 'goods_id' , 'goods_id');
	}
	//返回原有数据  不自动进行时间转换
    public function getCreateTimeAttr($time)
    {
        return date('Y-m-d',$time);
    }

    public function getPayTimeAttr($time)
    {
        return date('Y-m-d H:i:s',$time);
    }

    public function getSuccessTimeAttr($time)
    {
        return date('Y-m-d H:i:s',$time);
    }

    public function getStatusAttr($status)
    {
    	switch ($status) {
    		case 0:
    			return '等待付款';
    		case 1:
    			return '等待发货';	
    		case 2:
    			return '等待收货';	
    		case 3:
    			return '交易成功';
    		case 4:
    			return '订单已取消';
            case 5:
                return '已评价';
            case 6:
                return '已追评';
            case 7:
                return '订单已删除';
    	}
    }

    public function getAddress()
    {
        return $this->hasOne('address','id','address_id');
    }
}