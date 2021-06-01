<?php
@ob_start();
@set_time_limit(0);

//获取请求url
if (isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
    } else {
        if (isset($_SERVER['argv'])) {
            $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['argv'][0];
        } else {
            $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
    }
}

$weburi = str_replace('/http/','', $uri);

//获取浏览器user_agent 
$user_agent = $_SERVER["HTTP_USER_AGENT"];
  if (empty($user_agent)) {
    $user_agent = "Mozilla/5.0 (Linux; Android 7.1; vivo 1716 Build/N2G47H) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.98 Mobile Safari/537.36";
  }

//获取响应体是否为视频媒体等大文件，若为大文件分块输出。若为网页一次性全部输出。
$web1 = get_url_mime_type($weburi);

//判断来源是否为网页
if (stripos($web1['type'] , 'video') !== false) {
    
    //分块输出二进制文件

    foreach ($headerarr['header'] as $value) {
        if (stripos($value, 'HTTP/') !== false || stripos($value, 'Content-Encoding') !== false ||
            stripos($value, 'Host') !== false) {} else {
            header($value);
        }
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $weburi);

    curl_setopt($ch, CURLOPT_TIMEOUT, 600);

    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

    curl_setopt($ch, CURLOPT_HEADER, 0);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, get_RequestHeader($weburi));

    curl_setopt($ch, CURLOPT_HEADERFUNCTION, "read_head");

    curl_setopt($ch, CURLOPT_WRITEFUNCTION, "read_body");

    ob_clean();

    $output = curl_exec($ch);

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

    $header = substr($output, 0, $header_size);

    curl_close($ch);

} else {

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $weburi);

    curl_setopt($ch, CURLOPT_HEADER, 1);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);

    //数据提交操作
    switch ($_SERVER["REQUEST_METHOD"]) {

        case "POST":
            curl_setopt($ch, CURLOPT_POST, true);
            $postData = array();
            parse_str(file_get_contents("php://input"), $postData);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            break;

        case "PUT":
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_INFILE, fopen("php://input", "r"));
            break;
    }

    //前端解码。后端不参与。
    //curl_setopt($ch, CURLOPT_ENCODING, " ");

    //转发浏览器请求头
    curl_setopt($ch, CURLOPT_HTTPHEADER, get_RequestHeader($weburi));

    //执行curl
    $output = curl_exec($ch);

    define('HTTP__CODE',curl_getinfo($ch, CURLINFO_HTTP_CODE));
    
    define('HTTP__TYPE',curl_getinfo($ch, CURLINFO_CONTENT_TYPE));

    //关闭curl回话。
    curl_close($ch);

    //分离响应头与网页主体
    list($header, $content) = explode("\r\n\r\n", $output, 2);

    //输出响应头
    $header_arr = explode(PHP_EOL, $header);
    
    
    header('--access-control-allow-origin: *');
    header('access-control-allow-origin: *');
    header('access-control-expose-headers: *');
    
  

  foreach ($header_arr as $value) {

        if (stripos($value, 'ocation:') !== false) {
            header('--'.strtolower($value));
        } elseif(stripos($value, 'cookie') !== false){
            
            
        }
        
        else {
            header($value);
        }

        if (HTTP__CODE == '302') {
            header('--s: '.HTTP__CODE);
        }

    }
    
    echo $content;
}


function read_body(&$ch,&$string) {
    global $loadedsize;
    $rtn = strlen($string);
    $loadedsize += ($rtn/1024);
    print($string);
    @ob_flush();
    @flush();
    if (0 != connection_status()) {
        curl_close($ch);
        exit();
    }
    @$string = NULL;
    //@unset($string);
    return $rtn;
}
function read_head(&$ch,&$header) {

    if (!strpos($header,"Cache") && !strpos($header,"ocation"))
        @header(substr($header,0,strpos($header,"\r")));
    return strlen($header);
}

function get_RequestHeader($host) {
    $headers = [];
    parse_str($_SERVER['HTTP_REFERER'],$refArray);
    array_shift($refArray);
    
    foreach ($refArray as $key => $value) {
        $headers[] = $key.': '.$value;
    }
    
    return $headers;
}

function get_url_mime_type($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_RANGE, '0-1024');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_ENCODING, " ");
    $output = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headerarr['header'] = explode(PHP_EOL, substr($output, 0, $header_size));
    $headerarr['type'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    return $headerarr;
    curl_close($ch);
}

?>