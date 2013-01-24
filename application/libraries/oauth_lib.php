<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * 用于使用第三方账号登录，并创建本地账号。
 * 添加部分代码可以调用第三方网站接口。
 * @author baiyuxiong
 *
 */
class Oauth_lib 
{

	var $ci;
	var $sitesConfig;
	var $oauth_client;
	
    function __construct()
    {
    	$this->ci =& get_instance();
    	$this->ci->load->helper('bcore');
		$this->ci->load->config('oauth');
    	$this->sitesConfig = $this->ci->config->item('sites_enabled');
    	
    	ll('oauth_client');
    	$this->oauth_client = $this->ci->oauth_client;
    }
    
    function loadLoginIcons()
    {
    	$data['sitesConfig'] = $this->sitesConfig;
    	if(!empty($data['sitesConfig']))
    	{
    		lv('lib/oauth/loginicon',$data);
    	}
    }
    
    function login($site)
    {
    	if($site && array_key_exists ($site, $this->sitesConfig))
    	{  	
	    	
	    	$siteConfig = $this->sitesConfig[$site];
	    	
	    	if(strlen($siteConfig['client_id']) == 0 || strlen($siteConfig['client_secret']) == 0)
	    	{
	    		lv('lib/oauth/configError');
	    		return;
	    	}
	    	$this->oauth_client->server = $site;
	    	$this->oauth_client->redirect_uri = site_url('oauth/login/'.$site);
	    	$this->oauth_client->client_id = $siteConfig['client_id'];
	    	$this->oauth_client->client_secret = $siteConfig['client_secret'];
	    	$this->oauth_client->scope = $siteConfig['scope'];
	    	
	    	if(($success = $this->oauth_client->Initialize()))
	    	{
	    		if(($success = $this->oauth_client->Process()))
	    		{
	    			if(strlen($this->oauth_client->authorization_error))
	    			{
	    				$oauth_client->error = $this->oauth_client->authorization_error;
	    				$success = false;
	    			}
	    			elseif(strlen($this->oauth_client->access_token))
	    			{
	    				$success = true;
	    			}
	    		}
	    		$success = $this->oauth_client->Finalize($success);
	    	}
	    	
	    	if($success)
	    	{
	    		$this->snsUserInfo($site);
	    	}
	    	else
	    	{
	    		echo HtmlSpecialChars($this->ci->oauth_client->error);
	    	}
    	}
    }
	
    /*
     * 登录成功后记录账号信息，
     */
    function snsUserInfo($site)
    {
    	//获取第三方网站上的账号信息用于创建本地账号，目前只支持国内网站
    	switch ($site)
    	{
    		case '163':
    			$this->oauth_client->CallAPI(
    					'https://api.t.163.com/users/show.json',
    					'GET', array(), array('FailOnAccessError'=>true), $user);
		    	$data['oid'] = $user->id;
		    	$data['ousername'] = $user->name;
		    	$data['ogender'] = $user->gender;
		    	$data['orealName'] = $user->realName;
		    	$data['oemail'] = $user->email;
		    	$data['location'] = $user->location;
    			break;
    		case '360':
    			$this->oauth_client->CallAPI(
    					'https://openapi.360.cn/user/me.json',
    					'GET', array(), array('FailOnAccessError'=>true), $user);
    			$data['oid'] = $user->id;
    			$data['ousername'] = $user->name;
    			$data['ogender'] = $user->sex;
    			$data['location'] = $user->area;
    			$data['avatar'] = $user->avatar;
    			break;
    		case 'douban':
    			$this->oauth_client->CallAPI(
    					'https://api.douban.com/v2/user/~me',
    					'GET', array(), array('FailOnAccessError'=>true), $user);
    			$data['oid'] = $user->uid;
    			$data['ousername'] = $user->name;
    			$data['avatar'] = $user->avatar;
    			break;
    		case 'qq':
    			$this->oauth_client->CallAPI(
    					'https://graph.qq.com/oauth2.0/me',
    					'GET', array(), array('FailOnAccessError'=>true), $resStr);
    			// $temp = 'callback( {"client_id":"11111","openid":"A771DD90CF82FFDDD79D9D46DDF6B3F3"} );';
    			if (strpos($resStr, "callback") !== false)
    			{
    				$lpos = strpos($resStr, "(");
    				$rpos = strrpos($resStr, ")");
    				$str  = substr($resStr, $lpos + 1, $rpos - $lpos -1);
    				$tempObj = json_decode($str);
    				$this->oauth_client->GetAccessToken($accessToken);
    				$params = array(
    						'format' => 'json',
    						'oauth_consumer_key' => $this->oauth_client->client_secret,
    						'access_token' => $accessToken['value'],
    						'openid' => $tempObj->openid,
    						'clientip' => $this->input->ip_address(),
    						'oauth_version' => '2.a',
    						'scope' => 'all'
    				);
    				$this->oauth_client->CallAPI(
    						'http://open.t.qq.com/api/user/info',
    						'GET', $params, array('FailOnAccessError'=>true), $user);
    				
    				$userData = $user->data;
    				//openid与用户对应，唯一确定这个用户
    				$data['oid'] = $userData->openid;
    				$data['ousername'] = $userData->name;
    				$data['ogender'] = $userData->sex;
    				$data['orealName'] = $userData->nick;
    				$data['oemail'] = $userData->email;
    				$data['location'] = $userData->location;
    			}
    			
    			break;
    		case 'renren':
    			$params = array(
    					"v" => "1.0",
    					'method'=>'users.getInfo',
    					"access_token"=>$client->access_token,
    					'format' => 'json'
    			);
    			ksort($params);
    			reset($params);
    			
    			foreach($params AS $k=>$v){
    				$arr[$k]=$v;
    				$str .= $k.'='.$v;
    			}
    			$sig = md5($str.$client->client_secret);
    			unset($params['access_token']);
    			$params['sig'] = $sig;
    			
    			$this->oauth_client->CallAPI(
    					'http://api.renren.com/restserver.do',
    					'POST', $params, array('FailOnAccessError'=>true), $user);
    			$user = json_decode($user);
    			$userObj = $user[0];
    			
    			$data['oid'] = $userObj->uid;
    			$data['ousername'] = $userObj->name;
    			$data['avatar'] = $userObj->headurl;
    			$data['ogender'] = $userObj->sex;
    			$data['orealName'] = $userObj->name;
    			break;
    		case 'weibo':
    			$this->oauth_client->CallAPI(
    					'https://api.weibo.com/oauth2/get_token_info',
    					'POST', array(), array('FailOnAccessError'=>true), $user);
    			
    			$this->oauth_client->CallAPI(
    					'https://api.weibo.com/2/users/show.json',
    					'GET', array('uid' => $user->uid), array('FailOnAccessError'=>true), $userDetail);
    			
    			$data['oid'] = $userDetail->id;
    			$data['ousername'] = $userDetail->screen_name;
    			$data['avatar'] = $userDetail->profile_image_url;
    			$data['ogender'] = $userDetail->gender;
    			$data['location'] = $userDetail->location;
    			$data['orealName'] = $userDetail->name;
    			break;
    		case 'fetion':
    			
    			break;
    		default:break;
    	}
    	$data = array(
    		'access_token' => $this->oauth_client->access_token,
    		'expires_in' => $this->oauth_client->access_token_expiry,
    		'refresh_token' => $this->oauth_client->access_token,
    		'scope' => $this->oauth_client->scope
    	);

    	$data['site'] = $site;
    	
    	print_r($user);
    	echo HtmlSpecialChars($this->oauth_client->error);
    	//$this->load->model('oauth_model');
    	//$this->ci->oauth_model->createUser($data);
    }

}

?>