<?php
namespace app\index\model;
use think\Model;
class Cart extends Model
{
	public function getGoods()
	{
		return $this->hasOne('Goods','goods_id','goods_id');
	}
}