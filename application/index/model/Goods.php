<?php
namespace app\index\model;
use think\Model;
class Goods extends Model
{
	public function category()
	{
		return $this->belongsToMany('GoodsCategory' , 'goods_to_category' , 'category_id' , 'goods_id');
	}
}