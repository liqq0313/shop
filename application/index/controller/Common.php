<?php
namespace app\index\controller;
class Common
{
	static public function getTree($data , $root = 0 , $pk = 'id' , $pid = 'pid')
	{
		$tree = '';
		foreach($data as $k => $v)
		{
			if($v[$pid] == $root)
			{
				$v['son'] = self::getTree($data , $v[$pk] , $pk , $pid);
				$tree[] = $v;
			}
		}
		return $tree;
	}
}