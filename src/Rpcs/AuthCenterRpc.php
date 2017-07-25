<?php
namespace GouuseCore\Rpcs;

use GouuseCore\Rpcs\BaseRpc;

/**
 * 用户中心SDK
 * @author zhangyubo
 *
 */
class AuthCenterRpc extends BaseRpc
{
	protected $host;
	
	function __construct() {
		$this->host = '';
		
	}
	
	/**
	 * 校验token
	 * @param unknown $token
	 * @return \GouuseCore\Rpcs\number[]|\GouuseCore\Rpcs\string[]|mixed
	 */
	function check($token) {
		$url = $this->host . '/auth_center/v3/check';
		$token = explode(' ', $token);
		$token = end($token);
		$header[] = 'Authorization: bearer '.$token;
		$result = $this->postOutside($url, $header, []);
		return $result;
	}
	
	/**
	 * 登录
	 * @param string $account
	 * @param string $password
	 * @return \GouuseCore\Rpcs\number[]|\GouuseCore\Rpcs\string[]|mixed
	 */
	function login(string $account, string $password) {
		$url = $this->host . '/auth_center/v3/login';
		$result = $this->postOutside($url, [], [ 'account' => $account, 'password' => $password ]);
		return $result;
	}
	

}