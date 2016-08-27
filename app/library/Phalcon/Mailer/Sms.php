<?php
namespace Phalcon\Mailer;
/*--------------------------------
功能:		HTTP接口 发送短信
修改日期:	2011-03-04
说明:		http://api.sms.cn/mt/?uid=用户账号&pwd=MD5位32密码&mobile=号码&mobileids=号码编号&content=内容
状态:
	100 发送成功
	101 验证失败
	102 短信不足
	103 操作失败
	104 非法字符
	105 内容过多
	106 号码过多
	107 频率过快
	108 号码内容空
	109 账号冻结
	110 禁止频繁单条发送
	112 号码不正确
	120 系统升级
--------------------------------*/

class Sms
{
    private $user = "wujie";
    private $Key = "cbc59d96ffcc2f98ec0c";
//$http = 'http://api.sms.cn/mtutf8/';		//短信接口
//$uid = '';							//用户账号
//$pwd = '';							//密码
//$mobile	 = '';	//号码，以英文逗号隔开
//$mobileids	 = '';	//号码唯一编号
//$content = '内容';		//内容
//即时发送
//$res = sendSMS($http,$uid,$pwd,$mobile,$content,$mobileids);
//echo $res;
//echo 111;

//定时发送

//$time = '2010-05-27 12:11';
//$uid='';//用户名
//$pwd='';//用户明文密码
//$res = sendSMS($uid,$pwd,$mobile,$content);
//echo $res;

    public function send1($mobile, $content, $time = '', $mobileids, $mid = '')
    {
        $http = 'http://api.sms.cn/mtutf8/';
        $data = array
        (
            'uid' => "用户名",                    //用户账号
            'pwd' => md5('密码' . '用户名'),            //MD5位32密码,密码和用户名拼接字符
            'mobile' => $mobile,                //号码
            'content' => $content,            //内容
            'mobileids' => $mobileids,
            'time' => $time,                    //定时发送
        );

        $re = $this->postSMS($http, $data);            //POST方式提交

        return $re;
    }

    public function send($mobile, $content)
    {
        $Uid = $this->user;
        $Key = strtoupper(MD5($this->Key));
        $smsText = $this->_safe_replace($content);//内容
        $url = 'http://utf8.sms.webchinese.cn/';
        $file_contents = httpPost($url, array("Uid" => $Uid, "KeyMD5" => $Key, "smsMob" => $mobile, "smsText" => $smsText));
        return $file_contents > 0 ? true : false;
    }

    public function smsNum()
    {
        $Uid = $this->user;
        $url = 'http://sms.webchinese.cn/web_api/SMS/?Action=SMS_Num';
        return httpPost($url, array("Uid" => $Uid, "Key" => $this->Key));
    }

    function postSMS($url, $data = '')
    {

        $port = "";
        $post = "";
        $row = parse_url($url);
        $host = $row['host'];
        $port = $row['port'] ? $row['port'] : 80;
        $file = $row['path'];
        while (list($k, $v) = each($data)) {
            $post .= rawurlencode($k) . "=" . rawurlencode($v) . "&";    //转URL标准码
        }
        $post = substr($post, 0, -1);
        $len = strlen($post);
        $fp = @fsockopen($host, $port, $errno, $errstr, 10);
        if (!$fp) {
            return "$errstr ($errno)\n";
        } else {
            $receive = '';
            $out = "POST $file HTTP/1.1\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Content-type: application/x-www-form-urlencoded\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Content-Length: $len\r\n\r\n";
            $out .= $post;
            fwrite($fp, $out);
            while (!feof($fp)) {
                $receive .= fgets($fp, 128);
            }
            fclose($fp);
            $receive = explode("\r\n\r\n", $receive);
            unset($receive[0]);
            return implode("", $receive);
        }
    }

    /**
     * 安全过滤函数
     *
     * @param $string
     * @return string
     */
    private function _safe_replace($string)
    {
        $string = str_replace('%20', '', $string);
        $string = str_replace('%27', '', $string);
        $string = str_replace('%2527', '', $string);
        $string = str_replace('*', '', $string);
        $string = str_replace('"', '&quot;', $string);
        $string = str_replace("'", '', $string);
        $string = str_replace('"', '', $string);
        $string = str_replace(';', '', $string);
        $string = str_replace('<', '&lt;', $string);
        $string = str_replace('>', '&gt;', $string);
        $string = str_replace("{", '', $string);
        $string = str_replace('}', '', $string);
        $string = str_replace('\\', '', $string);
        //return urlencode($string);
        return $string;
    }

}

?>