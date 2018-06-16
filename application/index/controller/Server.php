<?php
namespace app\index\controller;
class Server extends Auth
{
	public function mail_query()
	{
		return $this->fetch();
	}
}