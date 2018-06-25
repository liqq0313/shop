<?php
namespace app\index\controller;
use app\index\model\Order;
class Order extends Auth
{	
	//没有登录状态自动跳转到登录界面
	protected $is_check_login = ['*'];
	
	public function index()
	{
		$uid = session('user')['u_id'];
		
		return $this->fetch();
	}

	public function showOrder()
	{
		if (empty($_POST)) {
			return;
		}

		$uid = session('user')['u_id'];
		$flag = $_POST['flag'];
		$num = $_POST['num'];
		$page = $_POST['page'];
		
		$start = ($page-1)*$num;
		if ($flag==0) {
			//全部订单
			$where = ['user_id'=>$uid];
		}else if($flag==1){
			//待付款
			$where = ['user_id'=>$uid , 'status'=>0];
		}else if($flag==2){
			//待收货
			$where = ['user_id'=>$uid , 'status'=>2];
		}else if($flag==3){
			//待评价
			$where = ['user_id'=>$uid , 'status'=>3];
		}

		$data = Order::where($where)->order('create_time desc')->limit("$start,$num")->select();
		$total = Order::where($where)->count();
		$pageCount = ceil($total/$num);

		$prev = $page-1;
		$next = $page+1;
		if ($prev<=1) {
			$prev=1;
		}
		if ($next>=$pageCount) {
			$next=$pageCount;
		}

		if ($data) {
			foreach ($data as $key => $value) {
				if ($value->getGoods) {
					$data[$key]['goods'] = $value->getGoods;	
				}	
			}
		}
		
		return json(['data'=>$data,'total'=>$total,'pageCount'=>$pageCount,'prev'=>$prev,'next'=>$next,'page'=>$page]);
	}

	



	public function detail()
	{
		echo 1;
	}
}