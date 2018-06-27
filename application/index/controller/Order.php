<?php
namespace app\index\controller;
use app\index\model\Order;
use app\index\model\Comment;
use app\index\model\Address;
use think\Request;
use think\Image;
class Order extends Auth
{	
	//没有登录状态自动跳转到登录界面
	protected $is_check_login = ['*'];
	
	public function index()
	{
		$uid = session('user')['u_id'];
		$totalAll = Order::where('user_id',$uid)->where('status','<>',7)->count();
		$totalPay = Order::where('user_id',$uid )->where('status',0)->where('status','<>',7)->count();

		$totalEmit = Order::where('user_id',$uid )->where('status',2)->where('status','<>',7)->count();
		$totalEval = Order::where('user_id',$uid )->where('status',3)->where('status','<>',7)->count();
		
		$this->assign('totalAll' , $totalAll);
		$this->assign('totalPay' , $totalPay);
		$this->assign('totalEmit' , $totalEmit);
		$this->assign('totalEval' , $totalEval);
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
			$data = Order::where('user_id',$uid)->where('status','<>',7)->order('create_time desc')->limit("$start,$num")->select();
			$total = Order::where('user_id',$uid)->where('status','<>',7)->count();
		}else if($flag==1){
			//待付款
			$data = Order::where('user_id',$uid)->where('status',0)->where('status','<>',7)->order('create_time desc')->limit("$start,$num")->select();
			$total = Order::where('user_id',$uid)->where('status',0)->where('status','<>',7)->count();
		}else if($flag==2){
			//待收货
			
			$data = Order::where('user_id',$uid)->where('status',2)->where('status','<>',7)->order('create_time desc')->limit("$start,$num")->select();
			$total = Order::where('user_id',$uid)->where('status',2)->where('status','<>',7)->count();
		}else if($flag==3){
			//待评价
			
			$data = Order::where('user_id',$uid)->where('status',3)->where('status','<>',7)->order('create_time desc')->limit("$start,$num")->select();
			$total = Order::where('user_id',$uid)->where('status',3)->where('status','<>',7)->count();
		}
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
		$id = Request::instance()->param('id');
		$data = Order::get(['id'=>$id]);
		if (empty($data)) {
			$this->redirect('index');
		}
		$address = $data->getAddress;
		
		$address['dizhi'] = $address['province'].$address['city'].$address['county'].$address['address'];
		
		$data['address'] = $address;
		$this->assign('data' , $data);
		return $this->fetch();
	}

	public function zhong()
	{
		$id = Request::instance()->param('id');
		
		$data = Order::get(['id'=>$id]);
		if (empty($data)) {
			$this->redirect('index');
		}

		switch ($data->getData('status')) {
			case 0:
    			$this->redirect('pay?id='.$id);
    			break;
    		case 2:
    			$this->redirect('receipted?id='.$id);
    		case 3:
    			$this->redirect('evaluate?id='.$id);	
    		default:
    			$this->redirect('index');
		}
	}

	//付款
	public function pay()
	{
		$id = Request::instance()->param('id');
		
		$res = true;
		if ($res) {
			Order::where('id',$id)->update(['status'=>1]);
			$this->success('付款成功' , 'index/order/index');
		}else{
			$this->success('付款失败' , 'index/order/index');
		}
		
	}

	//确认收货
	public function submitrepe()
	{
		if (empty($_POST)) {
			return;
		}

		$id = $_POST['id'];
		$mima = $_POST['mima'];
		if ($mima=='123456') {
			$res = true;
		}else{
			$res = false;
		}

		if ($res) {
			Order::where('id',$id)->update(['status'=>3]);
			return json(['status'=>1,'msg'=>'确认收货成功','url'=>'evaluate?id='.$id,'id'=>$id]);
		}else{
			return json(['status'=>0,'msg'=>'确认收货失败，请重新输入密码','url'=>'receipted?id='.$id,'id'=>$id]);
		}
	}

	//退货/退款
	public function refund()
	{
		$id = Request::instance()->param('id');

		$res = true;
		Order::where('id',$id)->update(['status'=>4]);
		$this->success('退货成功' , 'index/order/index');
	}

	//取消订单
	public function cancel()
	{
		$id = Request::instance()->param('id');

		$res = true;
		Order::where('id',$id)->update(['status'=>4]);
		$this->success('取消订单成功' , 'index/order/index');
	}

	//确认收货
	public function receipted()
	{
		$id = Request::instance()->param('id');
		$data = Order::get(['id'=>$id]);
		if (empty($data)) {
			$this->redirect('index');
		}

		if($data->getData('status')!=2){
			$this->redirect('zhong?id='.$id);
		}
		$address = $data->getAddress;
		
		$address['dizhi'] = $address['province'].$address['city'].$address['county'].$address['address'];
		
		$data['address'] = $address;
		$this->assign('data',$data);
		return $this->fetch();
	}

	//评价
	public function evaluate()
	{
		$id = Request::instance()->param('id');
		$data = Order::get(['id'=>$id]);
		if (empty($data)) {
			$this->redirect('index');
		}
		if($data->getData('status')!=3){
			$this->redirect('zhong?id='.$id);
		}
		$this->assign('data',$data);
		return $this->fetch();
	}

	//提交评价
	public function submitEvaluate(Comment $comment)
	{
		$request = Request::instance();
		$files = $request->file('i');
		$data=$request->param();
		
		/*if(isset($files)){
			//封面图
			$filePath = 'image/comment';
	        $filePaths = ROOT_PATH . 'public/static' . DS . 'upload' . DS .$filePath;
		    if(!file_exists($filePaths)){
		        mkdir($filePaths,0777,true);
		    }
		    //多张图片
		    foreach ($files as $file) { 	
            	$info = $file->move($filePaths);
            	if($info){
			        $imgpath = 'static/upload/'.$filePath.'/'.$info->getSaveName();
			        $imgpath = str_replace("\\", "/", $imgpath);
			        $image = \think\Image::open($imgpath);
			        //$data['image'] = $imgpath;
		  		}else{  
		            // 上传失败获取错误信息  
		     		echo $file->getError();  
		   		}  
			}
		   
      	}*/

      	if ($data['opinion'] == 'G') {
      		//好评
      		$data['Highpraise'] = $data['content'];
      	}else if($data['opinion'] == 'N'){
      		//中评
      		$data['mediumreview'] = $data['content'];
      	}else if($data['opinion'] == 'B'){
      		//差评
      		$data['negativecomment'] = $data['content'];
      	}

      	$data['user_id'] = session('user')['u_id'];
      	$data['create_time'] = time();
      	$res = $comment->allowField(true)->save($data);
      	if ($res) {
      		Order::where('id' , $data['eid'])->update(['status'=>5]);
      		return $this->success('评价成功' , 'index/order/index');
      	}else{
      		return $this->error('评价失败，请重新评价' , 'index/order/evaluate?id='.$data['eid']);
      	}

	}

	//删除订单
	public function delOrder()
	{
		if (empty($_POST)) {
			return;
		}
		$id = $_POST['id'];
		$res = Order::where('id',$id)->update(['status'=>7]);
		if ($res) {
			return json(['status'=>1,'msg'=>'订单已删除','url'=>'index/order/index']);
		}else{
			return json(['status'=>0,'msg'=>'订单删除失败','url'=>'index/order/index']);
		}
	}
	public function joinOrder()
	{
		$user_id = session('user')['u_id'];
		$order_num = input('post.order_num');
		$count = count(Order::all(['user_id'=>$user_id,'order_num_alias'=>$order_num]));
		if($count){
			for($i=0;$i<$count;$i++){
				$res = Order::where('user_id',$user_id)->where('order_num_alias',$order_num)->update(['status'=>0]);
			}
			return json(['status'=>1,'msg'=>'添加订单成功']);
		}else{
			return json(['status'=>0,'msg'=>'添加订单失败']);
		}
	}
}