<?php
namespace app\index\controller;
use think\Request;
class Item extends Auth
{
	public function show()
	{
		//var_dump($_GET);
		//var_dump(Request::instance()->param('id'));
		return $this->fetch();
	}
}