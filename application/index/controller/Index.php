<?php
namespace app\index\controller;
use app\index\controller\Common;
use app\index\model\GoodsCategory;
use app\index\model\Goods;
class Index extends Auth
{
	protected $is_check_login = [''];
	public function index()
	{
		$this->assign('isLogin' , $this->is_login);
		return $this->fetch();
	}

	public function showGoods()
	{
		$cate = GoodsCategory::column('category_id,pid,name,path,image');
		$cate = Common::getTree($cate , 0 , 'category_id');
		foreach ($cate as $key => $value) {
			$data = Goods::query('select g.goods_id,g.name,g.image,g.price from shop_goods g ,shop_goods_to_category gc ,(select c.category_id from shop_goods_category c where c.path like "'.$value['path'].',%") a where g.goods_id=gc.goods_id and a.category_id=gc.category_id  order by g.viewed desc limit 0,8');

			$cate[$key]['goods'] = $data;
		}
		return json($cate);
	}
}