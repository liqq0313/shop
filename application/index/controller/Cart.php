<?php
namespace app\index\controller;
use app\index\model\Cart;
class Cart extends Auth
{
	public function cart()
	{
		return $this->fetch();
	}
	public function getCart()
	{
		if(!empty(session('user'))){
			$user_id = session('user')['u_id'];
			$cart_goods = Cart::all(['user_id'=>$user_id]);
			foreach($cart_goods as $k=>$v){
				$goods_info[$k] = $v->getGoods;
			}
			//var_dump($cart_goods);die;
			//var_dump($goods_info);die;
			return json(['cart_info'=>$cart_goods,'goods_info'=>$goods_info]);
		}elseif(!empty($_COOKIE('shop_cart_info'))){

		}
	}
}