<?php
error_reporting(7);
set_time_limit(60);



$url='http://m5.22a.im/'.$_GET['vid'];
$UserAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
$curl = curl_init();	//创建一个新的CURL资源
curl_setopt($curl, CURLOPT_URL, $url);	//设置URL和相应的选项
curl_setopt($curl, CURLOPT_HEADER, 0);  //0表示不输出Header，1表示输出
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);	//设定是否显示头信息,1显示，0不显示。
//如果成功只将结果返回，不自动输出任何内容。如果失败返回FALSE
 
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_ENCODING, '');	//设置编码格式，为空表示支持所有格式的编码
//header中“Accept-Encoding: ”部分的内容，支持的编码格式为："identity"，"deflate"，"gzip"。
 
curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
//设置这个选项为一个非零值(象 “Location: “)的头，服务器会把它当做HTTP头的一部分发送(注意这是递归的，PHP将发送形如 “Location: “的头)。
 
$data = curl_exec($curl); 

preg_match("/<title>(.*)<\/title>/i",$data, $title);
$title= str_replace("恋恋影视","2mm.video",$title[1]);
//echo curl_errno($curl); //返回0时表示程序执行成功


//开始伪造IP和来路
function GetIP(){
$ip=false;
  if(!empty($_SERVER["HTTP_CLIENT_IP"])){
    $ip = $_SERVER["HTTP_CLIENT_IP"];
  }
  if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
    if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
    for ($i = 0; $i < count($ips); $i++) {
      if (!eregi ("^(10│172.16│192.168).", $ips[$i])) {
        $ip = $ips[$i];
        break;
      }
    }
  }
  return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}
$headers['CLIENT-IP'] = GetIP(); 
$headers['X-FORWARDED-FOR'] = GetIP();
 
$headerArr = array(); 
foreach( $headers as $n => $v ) { 
    $headerArr[] = $n .':' . $v;  
}
 
ob_start();
$url1='http://m5.22a.im/'.$_GET['vid'];
$ch = curl_init();
curl_setopt ($ch, CURLOPT_URL, $url1);
curl_setopt ($ch, CURLOPT_HTTPHEADER , $headerArr );  //构造IP
curl_setopt ($ch, CURLOPT_REFERER, "http://m5.22a.im/ ");   //构造来路
curl_setopt( $ch, CURLOPT_HEADER, 1);
 
curl_exec($ch);
curl_close ($ch);
$out = ob_get_contents();
ob_clean();
 
echo $out;
//伪造结束

$domain = ''; //域名防盗上线的时候使用，多个来源域名使用|分开，不然会提示错误
$play = true; //是否显示播放器
 

if(!empty($domain) && !preg_match("~$domain~i",$_SERVER['HTTP_REFERER'])){
	exit('error');
}

if($url1){
	$url = dstripslashes($url1);
	$info = V2mm::parse($url);
	if($_GET['json']){
		$info = json_encode($info);
	}
	if($info['video'][0]['url'] && $play){
	//echo "<meta name=\"referrer\" content=\"never\" /><video src='{$info['video'][0]['url']}' controls autoplay>";
	}else{
		print_r($info);
	}
}

//下面是接口的核心代码了，不要随便动，上面是输出结果的部分

class V2mm {
    public static function parse($url) {
        if (!$url) return false;
        $html = $vid = "";
        $data = $video = array();
        $ip = GetIP();
        $header = array(
            'CLIENT-IP:' . $ip,
            'X-FORWARDED-FOR:' . $ip,
        );
        preg_match("~.+/([a-zA-Z0-9]+)~i", $url, $id);
        if ($id[1]) {
            $url = "http://h.syasn.com/?p=111111111&n=" . $id[1];
            $url = "http://h.syasn.com/?p=222222222&n=" . $id[1];
            $html = self::httpget($url,'',[],$_SERVER['HTTP_USER_AGENT']);
            preg_match_all("~='(.*?)'~i", $html, $hash);
            //print_r($hash);
            preg_match("~(^.*?[a-z]+)(.+$)~i", $id[1], $name);
            if (strlen(preg_replace("~\d+~i", "", $name[0])) > 1) {
                $name[1] = preg_replace("~[^a-zA-Z]+~i", "", $name[0]);
            }
            if (count($hash[1]) == 5 && count(array_filter($name)) == 3) {
                //$videourl = "https://dpl.aq-cn.com:88/{$hash[1][1]}/".time()."/{$hash[1][4]}/{$hash[1][0]}/{$hash[1][3]}/{$hash[1][2]}/{$name[1]}/{$name[0]}.mp4";
                if (preg_match("~\d+v~i", $name[1])) {
                    $videourl = "https://d4.syasn.com/{$name[1]}/{$name[0]}.mp4?k1=$ip&k2=csf2avzddt&k3={$hash[1][1]}&k4={$hash[1][2]}&k5={$name[0]}&k6={$hash[1][3]}&k7={$hash[1][4]}";
                } else {
                    $videourl = "https://z.syasn.com/{$name[1]}/{$name[0]}.mp4?k1=$ip&k2=csf2avzddt&k3={$hash[1][1]}&k4={$hash[1][2]}&k5={$name[0]}&k6={$hash[1][3]}&k7={$hash[1][4]}";
                    //$videourl = "https://dpl.aq-cn.com:88/{$hash[1][0]}/".time()."/{$hash[1][1]}/{$hash[1][2]}/{$hash[1][3]}/{$hash[1][4]}/{$name[1]}/{$name[0]}.mp4";
                    
                }
                $video[] = array(
                    'url' => $videourl,
                    'thumb' => '',
                    'desc' => '如果不能正常下载或观看视频，请使用Chrome浏览器！',
                );
                $data['pages'] = 0;
                $data['total'] = count($video);
                $data['video'] = $video;
                return $data;
            }
        }
        return false;
    }
    public static function httpget($url, $data = "", $header = array(), $ua) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 99);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        if ($data) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        $temp = curl_exec($ch);
        $json = @json_decode($temp, 1);
        if (!empty($json)) {
            return $json;
        }
        return $temp;
    }
}

function dstripslashes($string) {
    if (empty($string)) return $string;
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = dstripslashes($val);
        }
    } else {
        $string = stripslashes($string);
    }
    return $string;
}
if ($title=="404"){$title="资源不存在！2mm.video";$info['video'][0]['url']="https://pan.ahslwl.com/file/18531/uOq8CXNn/404.mp4";}else{}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="shortcut icon" href="fav.ico" type="image/x-icon" />
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
<title><?php echo $title;?></title>
<style type="text/css">body,html,.video{background-color:#000;padding: 0;margin: 0;width:100%;height:100%;}</style>
<script type="text/javascript" src="ckx/ckplayer.js"></script>
</head>
<body ondragstart="window.event.returnValue=false" oncontextmenu="window.event.returnValue=false" onselectstart="event.returnValue=false" style="overflow-y:hidden;">
<div class="video"></div>
<script type="text/javascript">var vid = "<?php echo "{$info['video'][0]['url']}";?>"; </script>
<script type="text/javascript" src="ckx/player.js" charset="utf-8"></script>
</body>
</html>