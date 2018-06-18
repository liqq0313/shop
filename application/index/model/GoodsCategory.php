<?php
namespace app\index\model;
use think\Model;
class GoodsCategory extends Model
{
	public function getGoods()
	{
		return $this->belongsToMany('Goods' , 'goods_to_category' , 'goods_id' , 'category_id');
	}
}