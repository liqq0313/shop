<?php
namespace app\index\model;
use think\Model;
use app\index\controller\Common;
class Address extends Model
{
	public function getAdd()
	{
		return $this->hasmany('address');
	}
}