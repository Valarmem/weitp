<?php
namespace Home\Controller;
use Think\Controller;
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
			$this->reponseMsg();
		}
    }

    public function reponseMsg() {
    	// 1.get post data(xml) from wechat
    	// $postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
    	$postArr = file_get_contents("php://input");
    	$tmpstr = $postArr;
    	// 2.process message type ,and set return type and content
    	/**
		* <xml>
			<ToUserName><![CDATA[toUser]]></ToUserName>
			<FromUserName><![CDATA[FromUser]]></FromUserName>
			<CreateTime>123456789</CreateTime>
			<MsgType><![CDATA[event]]></MsgType>
			<Event><![CDATA[subscribe]]></Event>
			</xml>
    	 */
    	
    	$postObj = simplexml_load_string($postArr);
    	// $postObj->ToUserName = '';
    	// $postObj->FromUserName = '';
    	// $postObj->CreateTime = '';
    	// $postObj->MsgType = '';
    	// $postObj->MsgType = '';
    	// $postObj->Event = '';
    	
    	// 3.check if the message is event message
    	if (strtolower($postObj->MsgType) == 'event') {
    		# if it is a subscribe event,return user success messages!
    		if (strtolower($postObj->Event == 'subscribe')) {
    			$toUser = $postObj->FromUserName;
    			$fromUser = $postObj->ToUserName;
    			$time = time();
    			$msgType = 'text';
    			// $content = 'Welcome to Our Baobao Family!';
    			$content = 'PA:'.$postObj->ToUserName.'\n Openid:'.$postObj->FromUserName.'\n MsgType:'.$tmpstr;


    			$template = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							</xml>";
				$info = sprintf($template,$toUser,$fromUser,$time,$msgType,$content);
				echo $info;
    		}
    	}
    	/*if (strtolower($postObj->MsgType)=='text') {
    	}*/
	    //user send a keuword "article",response an article with a image
	    if (strtolower($postObj->MsgType)=='text' && trim($postObj->Content)=='article') {
	    	$toUser = $postObj->FromUserName;
	    	$fromUser = $postObj->ToUserName;
	    	$time = time();
	    	$msgType = 'news';

	    	$data = [
	    		[
	    			'title'         => 'Baobao Zone',
	    			'description'   => 'Baobao is the future',
	    			'picurl'		=> 'https://b-ssl.duitang.com/uploads/item/201411/08/20141108201327_sCxZX.jpeg',
	    			'url'			=> 'http://www.baidu.com'
	    		],
	    		[
	    			'title'         => 'Zone',
	    			'description'   => 'We Brought A Zoo',
	    			'picurl'		=> 'https://b-ssl.duitang.com/uploads/item/201411/08/20141108201327_sCxZX.jpeg',
	    			'url'			=> 'http://www.baidu.com'
	    		],
	    		[
	    			'title'         => 'Miao',
	    			'description'   => 'Miao',
	    			'picurl'		=> 'https://b-ssl.duitang.com/uploads/item/201411/08/20141108201327_sCxZX.jpeg',
	    			'url'			=> 'http://www.sina.com'
	    		],
	    		[
	    			'title'         => 'Taobao',
	    			'description'   => 'Sell right',
	    			'picurl'		=> 'https://b-ssl.duitang.com/uploads/item/201411/08/20141108201327_sCxZX.jpeg',
	    			'url'			=> 'http://www.taobao.com'
	    		]
	    	];
	    	$template = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<ArticleCount>".count($data)."</ArticleCount>
						<Articles>";

			foreach ($data as $val) {
				$template .= "<item>
							 <Title><![CDATA[".$val['title']."]]></Title> 
							 <Description><![CDATA[".$val['description']."]]></Description>
							 <PicUrl><![CDATA[".$val["picurl"]."]]></PicUrl>
							 <Url><![CDATA[".$val['url']."]]></Url>
							 </item>";
			}
			$template .= "</Articles>
						 </xml>";
			echo sprintf($template,$toUser,$fromUser,$time,$msgType);
	    }else{

    		switch ( trim($postObj->Content) ) {
    			case 'baobao':
    				$content = "Baobao is very cute";
    				break;
    			case 'tel':
    				$content = "15245669853";
    				break;
    			case 'search':
    				$content = "<a href='http://www.baidu.com'>search</a>";
    				break;
    			
    			default:
    				$content = "Welcome to the big family";
    				break;
    		}
			$template = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Content><![CDATA[%s]]></Content>
						</xml>";
			$toUser = $postObj->FromUserName;
			$fromUser = $postObj->ToUserName;
			$time = time();
			$msgType = 'text';
			$info = sprintf($template,$toUser,$fromUser,$time,$msgType,$content);
			echo $info;
	    }
    }

    function http_curl() {
    	// get baidu.com
    	// 1.init curl
    	$ch = curl_init();
    	$url = 'http://www.baidu.com';
    	// 2.config
    	curl_setopt($ch,CURLOPT_URL,$url);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); //return
    	// 3.get
    	$output = curl_exec($ch);
    	// 4.close
    	curl_close($ch);
    	var_dump($output);
    }

    function getWxAccessToken()	{
    	// 1 url address
    	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appId."&secret=".$this->appSecret;
    	// 2 init
    	$ch = curl_init();
    	// 3 set params
    	curl_setopt($ch,CURLOPT_URL,$url);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    	// 4 execute 
    	$res = curl_exec($ch);
    	// 5 close curl
    	curl_close($ch);
    	
    	if ( curl_errno($ch) ) {
    		var_dump(curl_errno($ch));
    	}
    	$arr = json_decode($res,true);
    	return $arr;

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


}