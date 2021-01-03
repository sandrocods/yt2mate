<?php
/* Class Youtube Downloader , Unofficial Api | Code by sandroputraa
 * https://www.y2mate.com/
 * 31-12-2020
 */
class Ytdownload
{
    const HEADER = [
        "Accept: */*",
        "Accept-Language: en-US,en;q=0.9,id;q=0.8",
        "Connection: keep-alive",
        "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
        "Host: www.y2mate.com",
        "Origin: https://www.y2mate.com",
        "Referer: https://www.y2mate.com/id4",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36",
        "X-Requested-With: XMLHttpRequest"
    ];

    private static function curl(
        $url,
        $method = null,
        $postfields = null,
        $followlocation = null,
        $headers = null,
        $conf_proxy = null
    ) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if ($conf_proxy !== null) {
            curl_setopt($ch, CURLOPT_PROXY, $conf_proxy['Proxy']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $conf_proxy['Proxy_Port']);
            if ($conf_proxy['Proxy_Type'] == 'SOCKS4') {
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
            }
            if ($conf_proxy['Proxy_Type'] == 'SOCKS5') {
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            }
            if ($conf_proxy['Proxy_Type'] == 'HTTP') {
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLOPT_HTTPPROXYTUNNEL);
            }
            if ($conf_proxy['Auth'] !== null) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $conf_proxy['Auth']['Username'].':'.$conf_proxy['Auth']['Password']);
                curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            }
        }
        if ($followlocation !== null) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $followlocation['Max']);
        }
        if ($method == "PUT") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        }
        if ($method == "GET") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        }
        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        }
        if ($headers !== null) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $result = curl_exec($ch);
        $header = substr($result, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        $body = substr($result, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        $cookies = array();
        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        return array(
            'HttpCode' => $httpcode,
            'Header' => $header,
            'Body' => $body,
            'Cookie' => $cookies,
            'Requests Config' => [
                    'Url' => $url,
                    'Header' => $headers,
                    'Method' => $method,
                    'Post' => $postfields
            ]
        );
    }

    private static function getStr($string, $start, $end)
    {
        $str = explode($start, $string);
        $str = explode($end, ($str[1]));
        return $str[0];
    }

    public static function Analyze($url = null)
    {
        $Analyze = Ytdownload::curl('https://www.y2mate.com/mates/id4/analyze/ajax', 'POST', 'url='.$url.'&q_auto=0&ajax=1', null, Self::HEADER, null );
        if (!strpos($Analyze['Body'] , 'Error')) {
            preg_match_all('/<a href=\\\\\"#\\\\\" rel=\\\\\"nofollow\\\\\">(.*?)<\\\\\/a> <\\\\\/td> <td>(.*?)<\\\\\/td>/m', $Analyze['Body'], $output_size);
            preg_match_all('/data-fquality=\\\\\"(.*?)\\\\\">/m', $Analyze['Body'], $output_data_fquality);
            for ($i=0; $i <count($output_size[1]); $i++) {
                $resolution[] = $output_size[1][$i];
                $size[] = $output_size[2][$i];
                $fquality[] = $output_data_fquality[1][$i];
            }
            return [
                'Status' => true,
                'Data' => [
                    'Title' => Ytdownload::getStr($Analyze['Body'], '<div class=\"caption text-left\"> <b>', '<\/b>'),
                    'k_data_vid' => Ytdownload::getStr($Analyze['Body'], 'k_data_vid = \"', '\";'),
                    'k__id' => Ytdownload::getStr($Analyze['Body'], 'k__id = \"', '\";'),
                    'Resolution' => $resolution,
                    'Size' => $size,
                    'f-quality' => $fquality
                ]
            ];
        } else {
            return [
               'Status' => false,
               'Data' => 'Video not found / Another error'
           ];
        }
    }
    public static function Getlink($k_data_vid = null, $k__id = null, $quality = '128')
    {
        $quality_set = ($quality == '128') ? $quality_set = 'mp3' : $quality_set = 'mp4';
        $Getlink = Ytdownload::curl('https://www.y2mate.com/mates/id4/convert', 'POST', 'type=youtube&_id='.$k__id.'&v_id='.$k_data_vid.'&ajax=1&token=&ftype='.$quality_set.'&fquality='.$quality.'', null, Self::HEADER, null);
        if (strpos($Getlink['Body'] , 'Error')) {
            return [
                'Status' => false,
                'Data' => $Getlink['Body']
            ];
        } elseif (strpos($Getlink['Body'], 'btn btn-success btn-file')) {
            $link = Ytdownload::getStr($Getlink['Body'], '<a href=\"', '\" rel=\"nofollow\" type=\"button\"');
            return [
                'Status' => true,
                'Data' => [
                    'Link' => str_replace('\\', '', $link)
                ]
                ];
        } else {
            return [
                 'Status' => false,
                 'Data' => $Getlink['Body']
             ];
        }
    }
}