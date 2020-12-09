<?php


namespace App\Handlers;
use GuzzleHttp\Client;
use Overtrue\Pinyin\Pinyin;

class SlugTranslateHandler
{
    public function translate($text){
        $http = new Client;

        //初始化配置信息
        $api = 'https://fanyi-api.baidu.com/api/trans/vip/doctrans';
        $appid = config('services.baidu_translate.appid');
        $key = config('services.baidu_translate.key');
        $slat = time();

        //如果没有配置百度翻译，自动使用兼容的拼音方案
        if (empty($appid) || empty($key)){
            return $this->pinyin($text);
        }

        //根据文档生成sign
        //http://api.fanyi.baidu.com/api/trans/product/apidoc
        //appid+q+salt+密钥 的MD5值
        $sign = md5($appid.$text.$slat.$key);

        //构建请求函数
        $query = http_build_query([
            "q" => $text,
            "from" => "zh",
            "to" => "en",
            "appid"=>$appid,
            "salt" => $slat,
            "sign" => $sign
        ]);

        //发送HTTP Get 请求
        $response = $http->get($api.$query);

        $result = json_decode($response->getBody(),true);
        /**
        获取结果，如果请求成功，dd($result) 结果如下：

        array:3 [▼
        "from" => "zh"
        "to" => "en"
        "trans_result" => array:1 [▼
        0 => array:2 [▼
        "src" => "XSS 安全漏洞"
        "dst" => "XSS security vulnerability"
        ]
        ]
        ]

         **/

        //尝试获取翻译结果
        if (isset($result['trans_result'][0]['dst'])){
            return \Str::slug($result['trans_result'][0]['dst']);
        }else{
            return $this->pinyin($text);
        }
    }

    public function pinyin($text){
        return \Str::slug(app(Pinyin::class)->permalink($text));
    }

}