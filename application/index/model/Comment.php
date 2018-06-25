<?php
namespace app\index\model;
use think\Model;
class Comment extends Model
{
	public function getUser()
	{
		return $this->hasOne('User','u_id','user_id');
	}
}