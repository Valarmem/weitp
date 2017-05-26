<?php
namespace Home\Model;
// use Think\Model;

class WeixinModel /*extends Model*/ {
	
	public function responseMsg() 
	{
    	// 1.get post data(xml) from wechat
    	// $postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
    	$postArr = file_get_contents("php://input");
    	$tmpstr = $postArr;
    	// 2.process message type ,and set return type and content
    	$postObj = simplexml_load_string($postArr);
    	// 3.check if the message is event message
    	if (strtolower($postObj->MsgType) == 'event') {
    		# if it is a subscribe event,return user success messages!
    		if (strtolower($postObj->Event == 'subscribe')) {
    			$this->responseOnSubscribe($postObj);
    		}
    	}
    	
	    //user send a keuword "article",response an article with a image
	    if (strtolower($postObj->MsgType)=='text' && trim($postObj->Content)=='article') {
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
	    	
	    	$this->responseNews($postObj,$data);

	    }elseif (strtolower($postObj->MsgType)=='text' && trim($postObj->Content)=='article') {
	    	
	    	$url = '';
	    	$header = array(
	    		'apikey'=>'',
	    	);
	    	$ch = curl_init();
	    	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	    	curl_setopt($ch, CURLOPT_URL, $url);
	    	$res = curl_exec($ch);

	    	$arr = json_decode($res,true);
	    	$content = $arr['retData']['weather'].'\n'.$arr['retData']['temp'].'\n'.$arr['retData']['1_tmp'].'<br>'.$arr['retData']['h_tmp'];
	    	
	    	$this->responseNews($postObj,$data);
	    }else{

    		$this->responseText($postObj);
	    }
    }

    // response news with images
    public function responseNews($postObj,$data=[]) {
    	$toUser = $postObj->FromUserName;
    	$fromUser = $postObj->ToUserName;
    	$time = time();
    	$msgType = 'news';
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
    }

    // response single text
    public function responseText($postObj,$content='')	{
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

    public function responseOnSubscribe($postObj,$data=[]) {

  //   	// response a single message
  //   	$toUser = $postObj->FromUserName;
		// $fromUser = $postObj->ToUserName;
		// $time = time();
		// $msgType = 'text';
		// // $content = 'Welcome to Our Baobao Family!';
		// $content = 'PA:'.$postObj->ToUserName.'\n Openid:'.$postObj->FromUserName.'\n MsgType:'.$tmpstr;


		// $template = "<xml>
		// 			<ToUserName><![CDATA[%s]]></ToUserName>
		// 			<FromUserName><![CDATA[%s]]></FromUserName>
		// 			<CreateTime>%s</CreateTime>
		// 			<MsgType><![CDATA[%s]]></MsgType>
		// 			<Content><![CDATA[%s]]></Content>
		// 			</xml>";
		// $info = sprintf($template,$toUser,$fromUser,$time,$msgType,$content);
		// echo $info;

		// return a news with an image
		$data = [ 
					['title'         => 'Baobao Zone',
	    			'description'   => 'Baobao is the future',
	    			'picurl'		=> 'https://b-ssl.duitang.com/uploads/item/201411/08/20141108201327_sCxZX.jpeg',
	    			'url'			=> 'http://www.baidu.com']  
	    		];
		$this->responseNews($postObj,$data);

    }
}