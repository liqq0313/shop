<?php
namespace app\admin\controller;
use app\index\model\User;
use think\Request;
class User extends Base
{
	public function index()
	{
		$data = User::all();
		$num = count($data);
		$this->assign('data',$data);
		$this->assign('num',$num);
		return $this->fetch();
	}

	public function addUser()
	{
		if (request()->post()) {
			$insert_data = [];
			$insert_data['username'] = input('username');
            $insert_data['email'] = input('email');
            $insert_data['phone'] = input('phone');
            $insert_data['password'] = md5(input('password'));
            $insert_data['status'] = input('status');
            $insert_data['create_time'] = time();
            $res = User::get(['phone'=>$insert_data['phone']]);
            if ($res) {
            	return json(['status'=>0,'msg'=>'添加失败，该手机号已被注册']);
            }
            $res = User::create($insert_data);
            if ($res) {
            	return json(['status'=>1,'msg'=>'添加成功']);
            }else{
            	return json(['status'=>0,'msg'=>'添加失败']);
            }
            
    	} else {
    		return $this->fetch('user-add');
    	}
	}

	public function delUser()
	{
		$id = Request::instance()->param('uid');
		$res = User::get($id)->delete();
    	if($res)
    	{
    		return json(['status'=>1,'msg'=>'删除成功']);
    	}else{
    		return json(['status'=>0,'msg'=>'删除失败']);
    	}
	}

    public function updateUserStatus()
    {
        $request = Request::instance();
        $id = $request->param('uid');
        $status = $request->param('status');
        $res = User::where('u_id',$id)->update(['status'=>$status]);
        if($res)
        {
            return json(['status'=>1,'msg'=>'修改成功']);
        }else{
            return json(['status'=>0,'msg'=>'修改失败']);
        }
    }
}