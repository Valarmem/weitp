<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\WeixinModel;

class IndexController extends Controller {
	private $appId = 'wx97ff90f9f8af2b8d';
	private $appSecret = 'd4624c36b6795d1d99dcf0547af5443d';

    public function index(){
		$nonce = $_GET['nonce'];
		$token = "baobao";
		$timestamp = $_GET['timestamp'];
		$echostr = $_GET['echostr'];
		$signature = $_GET['signature'];

		$tmpArray = [];
		$tmpArray = [$nonce,$timestamp,$token];
		sort($tmpArray,SORT_STRING);
		// Step2: sha1 three strings
		$str = sha1(implode($tmpArray)) ;
		
		// Step3: compare sha1 with signature
		if ($str == $signature && $echostr) {
			echo $echostr;
			exit;
		}else {
			$this->responseMsg();
		}
    }

    public function responseMsg() {

    	$weixin = new WeixinModel();
    	$weixin->responseMsg();
    }

    function http_curl($url,$type='get',$res='json',$data='') {
    	$ch = curl_init();
    	curl_setopt($ch,CURLOPT_URL,$url);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); //return
    	if ($type == 'post') {
    		curl_setopt($ch, CURLOPT_POST, 1);
    		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    	}
    	// 3.get
    	$output = curl_exec($ch);
    	// 4.close
    	curl_close($ch);
    	if ($res == 'json') {
    		if (curl_errno($ch)!=null) {
    			return curl_errno($ch);
    		}
     		return json_decode($output,true);
    	}
    }

    public function curl_qrcode($url) {
    	$ch = curl_init();
    	curl_setopt($ch,CURLOPT_URL,$url);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); //return
    	// 3.get
    	$output = curl_exec($ch);
    	// 4.close
    	curl_close($ch);
		return $output;
    }

    function getWxAccessToken()	{
    	// put access_token into session/cookie
    	if ($_SESSION['access_token'] && $_SESSION['expire_time']>time()) {
    	// // 	// acess_token is not out of date
    		return $_SESSION['access_token'];
    	}else{
	   		// access_token is out of date,reset access_token
	    	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appId."&secret=".$this->appSecret;
	    	$arr = $this->http_curl($url);
	    	$_SESSION['access_token'] = $arr['access_token'];
	    	$_SESSION['expire_time'] = time()+7000;
	    	return $arr['access_token'];
    	}

    }

    public function getWxServerIp()	{
    	$accessToken = "pjVg-rx3OvM8Q59ZRUhPSDgRmyVcDiAiMvBpQ3Bh2Lapuj-xaytwb-xCDIXgv1LqR8G7OYRJvTMGt3U1RnzuBNyOJsY2T5_hywB3yrz9JZN8fMf_w50cKJzFk5loNtooHVLfAHAPDT";
    	$url = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=".$accessToken;

    	$arr = $this->get_https($url);
    	var_dump($arr);

    }

    public function get_https($url)	{
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	$res = curl_exec($ch);
    	curl_close($ch);
    	if (curl_errno($ch)) {
    		var_dump(curl_errno($ch));
    	}
    	$arr = json_decode($res,true);
    	return $arr;
    }

    public function definedItem() {
    	// create wechat menu
    	// get api interface by curl post/get
    	echo $this->getWxAccessToken();
    	$url = ' https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$accessToken;
    	$postArr = array(
    			'button'=>array(
	    			array(
	    				'name' => urlencode('今日歌曲'),
	    				'type' => 'click',
	    				'key'  => 'V1001_TODAY_MUSIC'
	    			),	// the first menu
	    			array(
	    				'name'       => urlencode('菜单'),
	    				'sub_button' => array(
	    					array(
	    						'name' => urlencode('搜索'),
	    						'type' => 'view',
	    						'url'  => 'http://www.baidu.com'
	    					),
	    					array(
	    						'name' => 'wxa',
	    						'type' => 'miniprogram',
	    						'url'  => 'http://mp.weixin.qq.com',
	    						'appid'=> 'wx286b93c14bbf93aa',
	    						'pagepath' => 'pages/lunar/index.html'
	    					),
	    					array(
	    						'name' => urlencode('赞一下我们'),
	    						'type' => 'click',
	    						'key'  => 'V1001_GOOD'
	    					)
	    				)

	    			),	// the second menu
	    			array(
	    				'name' => 'QQ',
	    				'type' => 'view',
	    				'url'  => 'http://www.qq.com'
	    			)	// the third menu
	    		)
    		);
    	echo $postJson = urldecode(json_encode($postArr));
    	var_dump($res = $this->http_curl($url,'post','json',$postJson));
    }

    public function getTempQrCode()	{
    	//1 get ticket
    	// wechat has four tickets ,including global access_token,web auth access_token,js-SDK jsapi_ticketand qrcode ticket
    	// temp qrcode
    	$accessToken = $this->getWxAccessToken();
    	$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$accessToken;
    	$postArr = [
    		'expire_seconds' => 604800, //24*60*60*7
    		'action_name' => 'QR_SCENE',
    		'action_info' => [
    			'scene'=> [
    				'scene_id'=>2000,
    			],
    		],
    	];

    	$postJson = json_encode($postArr);
    	$res = $this->http_curl($url,'post','json',$postJson);
    	$ticket = $res['ticket'];


    	// 2 use ticket to get qrcode
    	$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ticket);
    	// $res = $this->curl_qrcode($url);
    	// var_dump($res);
    	echo "<img src=\"$url\">";
    }

    public function getPermanentQrcode()
    {
    	/**
    	 * http请求方式: POST	
    	 * URL: https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKENPOST数据格式：json
    	 * POST数据例子：{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 123}}}
    	 * 或者也可以使用以下POST数据创建字符串形式的二维码参数：
    	 * 	{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "123"}}}
    	 */
    	
    	$accessToken = $this->getWxAccessToken();
    	$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$accessToken;
    	$postArr = [
    		"action_name" => "QR_LIMIT_SCENE",
    		"action_info" => [
    			"scene" => [
    				"scene_id" => 123,
    			],
    		],
    	];
    	$postJson = json_encode($postArr);
    	$res = $this->http_curl($url,'post','json',$postJson);
    	$ticket = $res['ticket'];

    	$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ticket);
    	echo "<img src='{$url}'>";
    }


}