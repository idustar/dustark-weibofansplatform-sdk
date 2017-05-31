<?php

class CallbackSDK {
    private $app_secret = "";
    private $token = "";
    private $robot_api = "";
    private $robot_key = "";

    /**
     * 设置app_key对应的app_secret。
     * @param $app_secret
     */
    public function setAppSecret($app_secret) {
        $this->app_secret = $app_secret;
    }
    public function setToken($token) {
        $this->token = $token;
    }
    public function setRobot($rapi, $rkey) {
        $this->robot_api = $rapi;
        $this->robot_key = $rkey;
    }
    /**
     * 获取推送来的的数据
     * 必须使用 $GLOBALS['HTTP_RAW_POST_DATA']方法获取post过来的原始数据来解析.
     * @return mixed
     */
    public function getPostMsgStr() {
        $init = file_get_contents('php://input');
        //log
        //$f  = file_put_contents("wb.log", $init."\n",FILE_APPEND);
        return json_decode($init, true);
    }

    /**
     * 验证签名
     * @param $signature
     * @param $timestamp
     * @param $nonce
     * @return bool
     */
    function checkSignature($signature, $timestamp, $nonce) {
        $tmpArr = array($this->app_secret, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = sha1(implode($tmpArr));
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 组装返回数据
     * @param $receiver_id
     * @param $sender_id
     * @param $data
     * @param $type
     * @return array
     */
    function buildReplyMsg($receiver_id, $sender_id, $data, $type) {
        $newdata = json_encode($data);
        //log
        //$f  = file_put_contents("wb.log", "reply：".$newdata."\n",FILE_APPEND);

        return $msg = array(
            "sender_id" => $sender_id,
            "receiver_id" => $receiver_id,
            "type" => $type,
            //data字段需要进行urlencode编码
            "data" => urlencode($newdata)
        );
    }

    /**
     * 生成text类型的回复消息内容
     * @param $text
     * @return array
     */
    function textData($text) {
        return $data = array(
        "data" => array("text" => $text), "type" => "text");
    }

    /**
     * 生成article类型的回复消息内容
     * @param $article
     * @return array
     */
    function articleData($articles) {
        return $data = array(
        "data" => array("articles" => $articles), "type" => "articles");

    }

    /**
     * 生成position类型的回复消息内容
     * @param $longitude
     * @param $latitude
     * @return array
     */
    function positionData($longitude, $latitude) {
        return $data = array(
        "data" => array(
            "longitude" => $longitude,
            "latitude" => $latitude
            ), "type" => "position"
        );
    }
    
    
    function getUserInfo($uid = "5851010693") {
            $result = $this->httpGet("https://api.weibo.com/2/users/show.json" . "?access_token=" . $this->token . "&uid=" . $uid);
            return json_decode($result, true);
    }
    
    
    function createMenu() {
        $data = array(
            "button" => [
                array(
                    "name" => "陪你玩",
                    "sub_button" => [
                        array(
                            "type" => "click",
                            "name" => "开启/关闭机器人",
                            "key" => "robot"
                        ),
                        array(
                            "type" => "click",
                            "name" => "讲个故事",
                            "key" => "story"
                        ),
                        array(
                            "type" => "click",
                            "name" => "玩成语接龙",
                            "key" => "jl"
                        ),
                        array(
                            "type" => "click",
                            "name" => "看我头像",
                            "key" => "avatar"
                        ),
                        array(
                            "type" => "view",
                            "name" => "玩2048",
                            "url" => "http://dustark.cn/2048"
                        ),
                    ]
                ),

                array(
                    "name" => "逗你笑",
                    "sub_button" => [
                        array(
                            "type" => "click",
                            "name" => "来我微博下评论",
                            "key" => "comment"
                        ),
                        array(
                            "type" => "view",
                            "name" => "猜照片中的关系",
                            "url" => "http://e.msxiaobing.com/webapps/imagegame?p=ZQBQAFkAQgB%2bAEYAdABhAFEAUgB7AAYABwBnAHUAYQBuAHgAaQAJADIANwA%3d"
                        ),
                        array(
                            "type" => "view",
                            "name" => "拼我的颜值",
                            "url" => "http://e.msxiaobing.com/webapps/imagegame?p=ZQBQAFkAQgB%2bAEYAdABhAFEAUgB7AAYABwB5AGEAbgB6AGgAaQAJADIANwA%3d"
                        ),
                        array(
                            "type" => "click",
                            "name" => "订飞机票",
                            "key" => "air"
                        ),
                        array(
                            "type" => "click",
                            "name" => "订火车票",
                            "key" => "train"
                        ),
                    ]
                ),

                array(
                    "name" => "我是足球狗",
                    "sub_button" => [
                        array(
                            "type" => "click",
                            "name" => "中超积分榜",
                            "key" => "jfb"
                        ),
                        array(
                            "type" => "click",
                            "name" => "中超射手榜",
                            "key" => "ssb"
                        ),
                        array(
                            "type" => "click",
                            "name" => "中超对阵",
                            "key" => "dz"
                        ),
                        array(
                            "type" => "click",
                            "name" => "申花赛程",
                            "key" => "sfc"
                        ),
                        array(
                            "type" => "click",
                            "name" => "足球狗更多玩法",
                            "key" => "football"
                        ),
                    ]
                )
            ]
        );
        $result = $this->httpPost(array(
            "access_token" => $this->token,
            "menus" => json_encode($data)
        ), "https://m.api.weibo.com/2/messages/menu/create.json");

        $parse = json_decode($result, true);
        if ($parse['result']) {
            return array(
                "code" => 200,
                "message" => "菜单创建成功"
            );
        } else {
            return array(
                "code" => 400,
                "message" => $parse
            );
        }
    }
    
        
    function sendMessage($data, $receiver_id){
        $result = $this->httpPost(array(
            "access_token" => $this->token,
            "receiver_id" => $receiver_id,
            "type" => $data["type"],
            //data字段需要进行urlencode编码
            "data" => json_encode($data["data"])
        ),"https://m.api.weibo.com/2/messages/reply.json");
        return json_decode($result,true);
    }
        
        
        
        
     function doWithImage($myurl) {
        $results = $this->httpGetWithPic($myurl, "imageapi/imagetag");
        $result = json_decode($results, true);
        $flag = 0;
        if ($result['tags']){
            //$data = $call_back_SDK->textData($results);
            $ans = "";
            $tags = $result["tags"];
            foreach ($tags as $tag) {
                //if ((int)$tag['tag_confidence']
                $ans .= ($tag["tag_name"]."、");
                if ($tag["tag_name"]=="男孩"||$tag["tag_name"]=="女孩"||$tag["tag_name"]=="大头照"||$tag["tag_name"]=="合照"||$tag["tag_name"]=="西服") {
                    $flag = 1;
                } elseif ($tag["tag_name"]=="文本") {
                    $flag = 2;
                    break;
                }
            }
            //$fans = substr($ans, 0, strlen($ans) - 1);
            $r_text = "我在你的图片中找到了".rtrim($ans, "、")."等关键词。";
            $r_more = "";
            switch (rand(1,5)) {
                case 1:
                    $r_more = "我是不是很聪明？[憧憬]";
                    break;
                case 2:
                    $r_more = "哎呀你现在是不是很佩服我啊[憧憬]";
                    break;
                case 3:
                    $r_more = "斯塔克已经被自己的独特能力折服了[允悲][允悲][允悲]";
                    break;
                case 4:
                    $r_more = "不用问也知道我猜的很准确[允悲]";
                    break;
                default:
                    $r_more = "嘿嘿嘿，我是不是非常非常非常非常之厉害（期待脸）[憧憬]";
                    break;
            }
            $data = $this->textData($r_text.$r_more);
            
        } else {
            $data = $this->textData("眼睛进沙子了。竟然看不懂这张图片了。");
        }
        
        if ($flag == 1) {
            $results = $this->httpGetWithPic($myurl, "api/detectface");
            $result = json_decode($results, true);
            $face = $result['face'][0];
            if ($face) {
                $ans = "好一个近".(string)$face['age']."岁的";
                if ($face['glass']==true) {
                    $ans .= "戴着眼镜、";
                }
                if ($face['expression']>80) {
                    $ans .= "咧嘴大笑的";
                } else if ($face['expression']>65) {
                    $ans .= "喜笑颜开的";
                } else if ($face['expression']>50) {
                    $ans .= "满面桃花的";
                } else if ($face['expression']>30) {
                    $ans .= "风度翩翩的";
                } else {
                    $ans .= "严肃认真的";
                }
                if ($face['gender']<50 && $face['age']>60) {
                    $ans .= "老奶奶";
                } else if ($face['gender']<50 && $face['age']>40) {
                    $ans .= "大妈";
                } else if ($face['gender']<50 && $face['age']>30) {
                    $ans .= "阿姨";
                } else if ($face['gender']<50 && $face['age']>22) {
                    $ans .= "漂亮姐姐";
                } else if ($face['gender']<50) {
                    $ans .= "小姑娘";
                } else if ($face['age']>60) {
                    $ans .= "老爷叔";
                } else if ($face['age']>40) {
                    $ans .= "怪蜀黍";
                } else if ($face['age']>30) {
                    $ans .= "老男孩";
                } else if ($face['age']>20) {
                    $ans .= "美男子";
                } else {
                    $ans .= "小鲜肉";
                }
                
                if ($face["beauty"] == 100) {
                    $ans .= "，你的魅力值爆表了!（".$face["beauty"]."分)";
                } else if ($face["beauty"] >= 90) {
                    $ans .= "，你的魅力让斯塔克深深吸引，无法自拔!（".$face["beauty"]."分)";
                } else if ($face["beauty"] >= 80) {
                    $ans .= "，你的魅力惊艳到我了!（".$face["beauty"]."分)";
                } else if ($face["beauty"] >= 60) {
                    $ans .= "，对你的魅力我基本还是很认可滴!（".$face["beauty"]."分)";
                } else if ($face["beauty"] >= 40) {
                    $ans .= "，你的魅力还需要提高啊!（".$face["beauty"]."分)";
                } else if ($face["beauty"] >= 20) {
                    $ans .= "，你得去锻炼一下你的魅力呀!（".$face["beauty"]."分)";
                } else {
                    $ans .= "，你的魅力低到极点啦!（".$face["beauty"]."分)";
                } 
                $data = $this->textData($ans);
            }
        } elseif ($flag == 2){
            $pic = $myurl;
            $results = $this->httpGet('http://dustark.cn/wblog/pictotext/demo/DemoAipOcr.php?url=' . urlencode($pic));
            $result = json_decode($results, true);
            $ans = "";
            $words = $result["words_result"];
            foreach ($words as $word) {
                $ans .= $word["words"];
            }
            if ($ans != "") {
                $data = $this->textData($ans);
                $data["data"]["trans"]=true;
            }

        }
        return $data;
    }
    function doWithVoice($myurl) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://vop.baidu.com/server_api');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Cookie: BAIDUID=2DD8BD51E1E107F3F5339DD984FF7A40:FG=1",
            "Content-Type: application/json; charset=utf-8",
         ]
        );
        $json_array = [
                                "rate" => "8000",
                                "token" => "24.b7f05528adced58db47727f5e1b9b6ff.2592000.1498120343.282335-9681591",
                                "channel" => "1",
                                "url" => $myurl,
                                "cuid" => "71828391019293939888823",
                                "format" => "amr"
                        ]; 
        $body = json_encode($json_array);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $results = curl_exec($ch);
        $result = json_decode($results, true);
        if ($result["result"]) {
            return $result["result"][0];
        } else {
            return "novoice";
        }
    } 
    function dealWithText($data, $userinfo, $receiver_id, $source_post="") {
        //处理vs
        $vsrule = "/^(\S+)VS(\S+)$/";
        preg_match($vsrule, $source_post, $vsresult);
        if ($vsresult) {
            $data = $this->textData("mpi teams ".$vsresult[1]." ".$vsresult[2]);
        } else {
            $data = $this->reply("text", $source_post, $receiver_id);
        }
        //return $this->textData($source_post);
        //处理翻译
        if ($data["data"]["text"]=="我不会说英语的啦，你还是说中文吧。" 
        || $data["data"]["text"]=="都能认出是英文说明不文盲吔"
        || $data["data"]["text"]=="都能认出是英文说明不文盲啦"
        || $data["data"]["text"]=="对不起，没听清楚，请再说一遍吧。" ){
            $result = translate($source_post,"auto","zh");
            $data = $this->reply("text", $result["trans_result"][0]["dst"], $receiver_id);
            $transto = $result["from"];
            $transfrom = $result["to"];
            if ($data["data"]["text"]=="我不会说英语的啦，你还是说中文吧。" 
            || $data["data"]["text"]=="都能认出是英文说明不文盲吔"
            || $data["data"]["text"]=="都能认出是英文说明不文盲啦"
            || $data["data"]["text"]=="对不起，没听清楚，请再说一遍吧。") {
                switch (rand(1,3)) {
                    case 1:
                        $data = $this->textData("你说的这是什么语言呀，斯塔克怎么什么都看不懂...[困]");
                        break;
                    case 2:
                        $data = $this->textData("你把斯塔克搞糊涂了...[困]");
                        break;
                    default:
                        $data = $this->textData("斯塔克面前是一堆鸟语，不会分析了...主人快救我...[困]");
                        break;
                }
            } else {
                $result = translate($data["data"]["text"],$transfrom,$transto);
                $data["data"]["text"] = $result["trans_result"][0]["dst"];
            }
        }
        //正则替换
        $patterns[0] = "/@name/";
        $patterns[1] = "/@avatar/";
        $patterns[2] = "/@des/";
        $patterns[3] = "/@loc/";
        $replacements[3] = $userinfo["screen_name"];
        $replacements[2] = $userinfo["avatar_hd"];
        $replacements[1] = $userinfo["description"];
        $replacements[0] = $userinfo["location"];
        $data["data"]["text"] = preg_replace($patterns, $replacements, $data["data"]["text"]);
        //API匹配
        $rule  = "/^mpi\s(\S+)\s*(\S[\S\s]*)/";
        preg_match($rule,$data["data"]["text"],$preg_result);  
        if ($preg_result) {
            $api = $preg_result[1];
            $attr = $preg_result[2];
            switch ($api) {
                case "touxiang":
                    $data = $this->doWithImage($userinfo["avatar_hd"]);
                    break;
                case "league-next":
                    $results = $this->httpPost(array("key"=>"ccfaea5a3a58a3fc395b0919a021b9fb", "league"=>$attr), 'http://op.juhe.cn/onebox/football/league');
                    //$results='{"reason":"查询成功","result":{"key":"中超","tabs":{"saicheng1":"第10轮赛程","saicheng2":"第11轮赛程","saicheng3":null,"jifenbang":"积分榜","sheshoubang":"射手榜"},"views":{"saicheng1":[{"c1":"已结束","c2":"05-19周五","c3":"18:00","c4T1":"北京国安","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c4R":"2-2","c4T2":"广州富力","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-19\/doc-ifyfkqks4341511.shtml?cre=360.ala.zc.sc","liveid":"906977"},{"c1":"已结束","c2":"05-19周五","c3":"20:00","c4T1":"广州恒大","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4R":"2-1","c4T2":"江苏苏宁","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/jiangsu\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-19\/doc-ifyfkqiv6569267.shtml?cre=360.ala.zc.sc","liveid":"906978"},{"c1":"已结束","c2":"05-20周六","c3":"15:30","c4T1":"上海申花","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c4R":"1-3","c4T2":"上海上港","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6588464.shtml?cre=360.ala.zc.sc","liveid":"906982"},{"c1":"已结束","c2":"05-20周六","c3":"15:30","c4T1":"延边富德","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/yanbian\/?cre=360.ala.zc.sc","c4R":"1-1","c4T2":"山东鲁能","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqks4363472.shtml?cre=360.ala.zc.sc","liveid":"906980"},{"c1":"已结束","c2":"05-20周六","c3":"19:35","c4T1":"重庆力帆","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/lifan\/?cre=360.ala.zc.sc","c4R":"0-1","c4T2":"天津权健","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6592220.shtml?cre=360.ala.zc.sc","liveid":"906981"},{"c1":"已结束","c2":"05-20周六","c3":"19:35","c4T1":"河北华夏","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4R":"2-0","c4T2":"辽宁开新","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/liaoning\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqks4367391.shtml?cre=360.ala.zc.sc","liveid":"906979"},{"c1":"已结束","c2":"05-21周日","c3":"15:30","c4T1":"长春亚泰","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/changchun\/?cre=360.ala.zc.sc","c4R":"1-1","c4T2":"天津泰达","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-21\/doc-ifyfkqiv6619583.shtml?cre=360.ala.zc.sc","liveid":"906984"},{"c1":"已结束","c2":"05-21周日","c3":"19:35","c4T1":"河南建业","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/henan\/?cre=360.ala.zc.sc","c4R":"0-1","c4T2":"贵州恒丰","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-21\/doc-ifyfkqiv6625599.shtml?cre=360.ala.zc.sc","liveid":"906983"}],"saicheng2":[{"c1":"未开赛","c2":"05-26周五","c3":"18:00","c4T1":"广州恒大","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"重庆力帆","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/lifan\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157658","liveid":"906986"},{"c1":"未开赛","c2":"05-26周五","c3":"20:00","c4T1":"广州富力","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"上海申花","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157657","liveid":"906985"},{"c1":"未开赛","c2":"05-27周六","c3":"15:30","c4T1":"辽宁开新","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/liaoning\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"延边富德","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/yanbian\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157660","liveid":"906988"},{"c1":"未开赛","c2":"05-27周六","c3":"15:30","c4T1":"贵州恒丰","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"江苏苏宁","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/jiangsu\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157663","liveid":"906991"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"河北华夏","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"长春亚泰","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/changchun\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157659","liveid":"906987"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"天津权健","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"天津泰达","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157662","liveid":"906990"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"上海上港","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"北京国安","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157664","liveid":"906992"},{"c1":"未开赛","c2":"05-28周日","c3":"19:35","c4T1":"山东鲁能","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"河南建业","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/henan\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157661","liveid":"906989"}],"saicheng3":null,"jifenbang":[{"c1":"1","c2":"广州恒大","c2L":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c3":"10","c41":"8","c42":"1","c43":"1","c5":"10","c6":"25"},{"c1":"2","c2":"上海上港","c2L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c3":"10","c41":"7","c42":"2","c43":"1","c5":"15","c6":"23"},{"c1":"3","c2":"河北华夏","c2L":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c3":"10","c41":"5","c42":"4","c43":"1","c5":"9","c6":"19"},{"c1":"4","c2":"广州富力","c2L":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c3":"10","c41":"5","c42":"3","c43":"2","c5":"3","c6":"18"},{"c1":"5","c2":"山东鲁能","c2L":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c3":"9","c41":"5","c42":"2","c43":"2","c5":"7","c6":"17"},{"c1":"6","c2":"北京国安","c2L":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c3":"10","c41":"4","c42":"3","c43":"3","c5":"0","c6":"15"},{"c1":"7","c2":"天津权健","c2L":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c3":"10","c41":"3","c42":"4","c43":"3","c5":"-2","c6":"13"},{"c1":"8","c2":"贵州恒丰","c2L":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c3":"10","c41":"3","c42":"3","c43":"4","c5":"-3","c6":"12"},{"c1":"9","c2":"上海申花","c2L":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c3":"9","c41":"3","c42":"2","c43":"4","c5":"1","c6":"11"},{"c1":"10","c2":"天津亿利","c2L":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c3":"10","c41":"2","c42":"4","c43":"4","c5":"-6","c6":"10"}],"sheshoubang":[{"c1":"1","c2":"扎哈维","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=56838","c3":"广州富力","c3L":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c4":"9","c5":"1"},{"c1":"2","c2":"高拉特","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=61622","c3":"广州恒大","c3L":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4":"7","c5":"5"},{"c1":"3","c2":"耶拉维奇","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=62419","c3":"贵州恒丰","c3L":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c4":"6","c5":"1"},{"c1":"4","c2":"伊尔马兹","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=39931","c3":"北京国安","c3L":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c4":"6","c5":"1"},{"c1":"5","c2":"武磊","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=116730","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"6","c2":"莫雷诺","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=77063","c3":"上海申花","c3L":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"7","c2":"阿洛伊西奥","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=100817","c3":"河北华夏","c3L":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"8","c2":"胡尔克","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=53645","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"1"},{"c1":"9","c2":"帕托","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=45215","c3":"天津权健","c3L":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c4":"5","c5":"1"},{"c1":"10","c2":"埃尔克森","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=61165","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"1"}]}},"error_code":0}';
                    $result = json_decode($results, true);
                    if (!$result["result"]) {
                        $data = $this->textData("斯塔克用尽全身力气也找不到".$attr."赛程。[允悲]");
                        return $data;
                    }
                    $views = $result["result"]["views"]["saicheng1"];
                    $tabs = $result["result"]["tabs"];
                    $ans = $userinfo["screen_name"]."，这是".$result["result"]["key"].$tabs["saicheng1"]."：";
                    $played = "";
                    $playing = "";
                    $gotoplay = "";
                    foreach ($views as $view) {
                        if ($view["c1"]=="已结束") {
                            $viewtext = $view["c4T1"]." ".$view["c4R"]." ".$view["c4T2"];
                            $played.= $viewtext."，";
                        } elseif ($view["c1"]=="未开赛") {
                            $viewtext = $view["c4T1"]." ".$view["c4R"]." ".$view["c4T2"]."（".$view["c2"]." ".$view["c3"]."）";
                            $gotoplay.=$viewtext."，";
                        } else {
                            $viewtext = $view["c4T1"]." ".$view["c4R"]." ".$view["c4T2"]."（".$view["c2"]." ".$view["c3"]."）";
                            $playing.=$viewtext."，";
                        }
                    }
                    if ($playing != "") {
                        $ans.="正在进行的有".$playing;
                    }
                    if ($gotoplay != "") {
                        $ans.="还未开球的有".$gotoplay;
                    }
                    if ($played != "") {
                        $ans.="已经结束的有" . $played;
                    }
                    $ans.="查看".$result["result"]["tabs"]["saicheng2"]."可回复【".$attr."之后赛程】，您可以回复球队名+赛程（如【".$result["result"]["views"]["jifenbang"][rand(0,9)]["c2"]."赛程】) 查球队赛程，回复主队名VS客队名（如【皇马VS巴萨】）查本赛季双方对阵，回复赛事名+积分榜/射手榜（如【西甲射手榜】）查看相关信息。";
                    $data = $this->textData($ans);
                    break;
                case "league-nextnext":
                    $results = $this->httpPost(array("key"=>"ccfaea5a3a58a3fc395b0919a021b9fb", "league"=>$attr), 'http://op.juhe.cn/onebox/football/league');
                    //$results='{"reason":"查询成功","result":{"key":"中超","tabs":{"saicheng1":"第10轮赛程","saicheng2":"第11轮赛程","saicheng3":null,"jifenbang":"积分榜","sheshoubang":"射手榜"},"views":{"saicheng1":[{"c1":"已结束","c2":"05-19周五","c3":"18:00","c4T1":"北京国安","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c4R":"2-2","c4T2":"广州富力","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-19\/doc-ifyfkqks4341511.shtml?cre=360.ala.zc.sc","liveid":"906977"},{"c1":"已结束","c2":"05-19周五","c3":"20:00","c4T1":"广州恒大","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4R":"2-1","c4T2":"江苏苏宁","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/jiangsu\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-19\/doc-ifyfkqiv6569267.shtml?cre=360.ala.zc.sc","liveid":"906978"},{"c1":"已结束","c2":"05-20周六","c3":"15:30","c4T1":"上海申花","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c4R":"1-3","c4T2":"上海上港","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6588464.shtml?cre=360.ala.zc.sc","liveid":"906982"},{"c1":"已结束","c2":"05-20周六","c3":"15:30","c4T1":"延边富德","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/yanbian\/?cre=360.ala.zc.sc","c4R":"1-1","c4T2":"山东鲁能","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqks4363472.shtml?cre=360.ala.zc.sc","liveid":"906980"},{"c1":"已结束","c2":"05-20周六","c3":"19:35","c4T1":"重庆力帆","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/lifan\/?cre=360.ala.zc.sc","c4R":"0-1","c4T2":"天津权健","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6592220.shtml?cre=360.ala.zc.sc","liveid":"906981"},{"c1":"已结束","c2":"05-20周六","c3":"19:35","c4T1":"河北华夏","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4R":"2-0","c4T2":"辽宁开新","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/liaoning\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqks4367391.shtml?cre=360.ala.zc.sc","liveid":"906979"},{"c1":"已结束","c2":"05-21周日","c3":"15:30","c4T1":"长春亚泰","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/changchun\/?cre=360.ala.zc.sc","c4R":"1-1","c4T2":"天津泰达","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-21\/doc-ifyfkqiv6619583.shtml?cre=360.ala.zc.sc","liveid":"906984"},{"c1":"已结束","c2":"05-21周日","c3":"19:35","c4T1":"河南建业","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/henan\/?cre=360.ala.zc.sc","c4R":"0-1","c4T2":"贵州恒丰","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-21\/doc-ifyfkqiv6625599.shtml?cre=360.ala.zc.sc","liveid":"906983"}],"saicheng2":[{"c1":"未开赛","c2":"05-26周五","c3":"18:00","c4T1":"广州恒大","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"重庆力帆","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/lifan\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157658","liveid":"906986"},{"c1":"未开赛","c2":"05-26周五","c3":"20:00","c4T1":"广州富力","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"上海申花","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157657","liveid":"906985"},{"c1":"未开赛","c2":"05-27周六","c3":"15:30","c4T1":"辽宁开新","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/liaoning\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"延边富德","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/yanbian\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157660","liveid":"906988"},{"c1":"未开赛","c2":"05-27周六","c3":"15:30","c4T1":"贵州恒丰","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"江苏苏宁","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/jiangsu\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157663","liveid":"906991"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"河北华夏","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"长春亚泰","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/changchun\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157659","liveid":"906987"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"天津权健","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"天津泰达","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157662","liveid":"906990"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"上海上港","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"北京国安","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157664","liveid":"906992"},{"c1":"未开赛","c2":"05-28周日","c3":"19:35","c4T1":"山东鲁能","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"河南建业","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/henan\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157661","liveid":"906989"}],"saicheng3":null,"jifenbang":[{"c1":"1","c2":"广州恒大","c2L":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c3":"10","c41":"8","c42":"1","c43":"1","c5":"10","c6":"25"},{"c1":"2","c2":"上海上港","c2L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c3":"10","c41":"7","c42":"2","c43":"1","c5":"15","c6":"23"},{"c1":"3","c2":"河北华夏","c2L":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c3":"10","c41":"5","c42":"4","c43":"1","c5":"9","c6":"19"},{"c1":"4","c2":"广州富力","c2L":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c3":"10","c41":"5","c42":"3","c43":"2","c5":"3","c6":"18"},{"c1":"5","c2":"山东鲁能","c2L":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c3":"9","c41":"5","c42":"2","c43":"2","c5":"7","c6":"17"},{"c1":"6","c2":"北京国安","c2L":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c3":"10","c41":"4","c42":"3","c43":"3","c5":"0","c6":"15"},{"c1":"7","c2":"天津权健","c2L":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c3":"10","c41":"3","c42":"4","c43":"3","c5":"-2","c6":"13"},{"c1":"8","c2":"贵州恒丰","c2L":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c3":"10","c41":"3","c42":"3","c43":"4","c5":"-3","c6":"12"},{"c1":"9","c2":"上海申花","c2L":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c3":"9","c41":"3","c42":"2","c43":"4","c5":"1","c6":"11"},{"c1":"10","c2":"天津亿利","c2L":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c3":"10","c41":"2","c42":"4","c43":"4","c5":"-6","c6":"10"}],"sheshoubang":[{"c1":"1","c2":"扎哈维","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=56838","c3":"广州富力","c3L":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c4":"9","c5":"1"},{"c1":"2","c2":"高拉特","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=61622","c3":"广州恒大","c3L":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4":"7","c5":"5"},{"c1":"3","c2":"耶拉维奇","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=62419","c3":"贵州恒丰","c3L":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c4":"6","c5":"1"},{"c1":"4","c2":"伊尔马兹","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=39931","c3":"北京国安","c3L":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c4":"6","c5":"1"},{"c1":"5","c2":"武磊","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=116730","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"6","c2":"莫雷诺","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=77063","c3":"上海申花","c3L":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"7","c2":"阿洛伊西奥","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=100817","c3":"河北华夏","c3L":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"8","c2":"胡尔克","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=53645","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"1"},{"c1":"9","c2":"帕托","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=45215","c3":"天津权健","c3L":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c4":"5","c5":"1"},{"c1":"10","c2":"埃尔克森","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=61165","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"1"}]}},"error_code":0}';
                    $result = json_decode($results, true);
                    if (!$result["result"]) {
                        $data = $this->textData("斯塔克费尽毕生修炼的精气也找不到".$attr."赛程。[允悲]");
                        return $data;
                    }
                    $views = $result["result"]["views"]["saicheng2"];
                    $tabs = $result["result"]["tabs"];
                    $ans = $userinfo["screen_name"]."，这是".$result["result"]["key"].$tabs["saicheng2"]."：";
                    $played = "";
                    $playing = "";
                    $gotoplay = "";
                    foreach ($views as $view) {
                        if ($view["c1"]=="已结束") {
                            $viewtext = $view["c4T1"]." ".$view["c4R"]." ".$view["c4T2"];
                            $played.= $viewtext."，";
                        } elseif ($view["c1"]=="未开赛") {
                            $viewtext = $view["c4T1"]." ".$view["c4R"]." ".$view["c4T2"]."（".$view["c2"]." ".$view["c3"]."）";
                            $gotoplay.=$viewtext."，";
                        } else {
                            $viewtext = $view["c4T1"]." ".$view["c4R"]." ".$view["c4T2"]."（".$view["c2"]." ".$view["c3"]."）";
                            $playing.=$viewtext."，";
                        }
                    }
                    if ($playing != "") {
                        $ans.="正在进行的有".$playing;
                    }
                    if ($gotoplay != "") {
                        $ans.="还未开球的有".$gotoplay;
                    }
                    if ($played != "") {
                        $ans.="已经结束的有" . $played;
                    }
                    $ans.="查看".$result["result"]["tabs"]["saicheng1"]."可回复【".$attr."赛程】，您可以回复球队名+赛程（如【".$result["result"]["views"]["jifenbang"][rand(0,9)]["c2"]."赛程】) 查球队赛程，回复主队名VS客队名（如【皇马VS巴萨】）查本赛季双方对阵，回复赛事名+积分榜/射手榜（如【西甲射手榜】）查看相关信息。";
                    $data = $this->textData($ans);
                    break;
                case "league-jfb":
                    $results = $this->httpPost(array("key"=>"ccfaea5a3a58a3fc395b0919a021b9fb", "league"=>$attr), 'http://op.juhe.cn/onebox/football/league');
                    //$results='{"reason":"查询成功","result":{"key":"中超","tabs":{"saicheng1":"第10轮赛程","saicheng2":"第11轮赛程","saicheng3":null,"jifenbang":"积分榜","sheshoubang":"射手榜"},"views":{"saicheng1":[{"c1":"已结束","c2":"05-19周五","c3":"18:00","c4T1":"北京国安","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c4R":"2-2","c4T2":"广州富力","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-19\/doc-ifyfkqks4341511.shtml?cre=360.ala.zc.sc","liveid":"906977"},{"c1":"已结束","c2":"05-19周五","c3":"20:00","c4T1":"广州恒大","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4R":"2-1","c4T2":"江苏苏宁","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/jiangsu\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-19\/doc-ifyfkqiv6569267.shtml?cre=360.ala.zc.sc","liveid":"906978"},{"c1":"已结束","c2":"05-20周六","c3":"15:30","c4T1":"上海申花","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c4R":"1-3","c4T2":"上海上港","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6588464.shtml?cre=360.ala.zc.sc","liveid":"906982"},{"c1":"已结束","c2":"05-20周六","c3":"15:30","c4T1":"延边富德","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/yanbian\/?cre=360.ala.zc.sc","c4R":"1-1","c4T2":"山东鲁能","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqks4363472.shtml?cre=360.ala.zc.sc","liveid":"906980"},{"c1":"已结束","c2":"05-20周六","c3":"19:35","c4T1":"重庆力帆","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/lifan\/?cre=360.ala.zc.sc","c4R":"0-1","c4T2":"天津权健","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6592220.shtml?cre=360.ala.zc.sc","liveid":"906981"},{"c1":"已结束","c2":"05-20周六","c3":"19:35","c4T1":"河北华夏","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4R":"2-0","c4T2":"辽宁开新","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/liaoning\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqks4367391.shtml?cre=360.ala.zc.sc","liveid":"906979"},{"c1":"已结束","c2":"05-21周日","c3":"15:30","c4T1":"长春亚泰","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/changchun\/?cre=360.ala.zc.sc","c4R":"1-1","c4T2":"天津泰达","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-21\/doc-ifyfkqiv6619583.shtml?cre=360.ala.zc.sc","liveid":"906984"},{"c1":"已结束","c2":"05-21周日","c3":"19:35","c4T1":"河南建业","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/henan\/?cre=360.ala.zc.sc","c4R":"0-1","c4T2":"贵州恒丰","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-21\/doc-ifyfkqiv6625599.shtml?cre=360.ala.zc.sc","liveid":"906983"}],"saicheng2":[{"c1":"未开赛","c2":"05-26周五","c3":"18:00","c4T1":"广州恒大","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"重庆力帆","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/lifan\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157658","liveid":"906986"},{"c1":"未开赛","c2":"05-26周五","c3":"20:00","c4T1":"广州富力","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"上海申花","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157657","liveid":"906985"},{"c1":"未开赛","c2":"05-27周六","c3":"15:30","c4T1":"辽宁开新","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/liaoning\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"延边富德","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/yanbian\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157660","liveid":"906988"},{"c1":"未开赛","c2":"05-27周六","c3":"15:30","c4T1":"贵州恒丰","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"江苏苏宁","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/jiangsu\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157663","liveid":"906991"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"河北华夏","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"长春亚泰","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/changchun\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157659","liveid":"906987"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"天津权健","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"天津泰达","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157662","liveid":"906990"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"上海上港","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"北京国安","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157664","liveid":"906992"},{"c1":"未开赛","c2":"05-28周日","c3":"19:35","c4T1":"山东鲁能","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"河南建业","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/henan\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157661","liveid":"906989"}],"saicheng3":null,"jifenbang":[{"c1":"1","c2":"广州恒大","c2L":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c3":"10","c41":"8","c42":"1","c43":"1","c5":"10","c6":"25"},{"c1":"2","c2":"上海上港","c2L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c3":"10","c41":"7","c42":"2","c43":"1","c5":"15","c6":"23"},{"c1":"3","c2":"河北华夏","c2L":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c3":"10","c41":"5","c42":"4","c43":"1","c5":"9","c6":"19"},{"c1":"4","c2":"广州富力","c2L":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c3":"10","c41":"5","c42":"3","c43":"2","c5":"3","c6":"18"},{"c1":"5","c2":"山东鲁能","c2L":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c3":"9","c41":"5","c42":"2","c43":"2","c5":"7","c6":"17"},{"c1":"6","c2":"北京国安","c2L":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c3":"10","c41":"4","c42":"3","c43":"3","c5":"0","c6":"15"},{"c1":"7","c2":"天津权健","c2L":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c3":"10","c41":"3","c42":"4","c43":"3","c5":"-2","c6":"13"},{"c1":"8","c2":"贵州恒丰","c2L":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c3":"10","c41":"3","c42":"3","c43":"4","c5":"-3","c6":"12"},{"c1":"9","c2":"上海申花","c2L":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c3":"9","c41":"3","c42":"2","c43":"4","c5":"1","c6":"11"},{"c1":"10","c2":"天津亿利","c2L":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c3":"10","c41":"2","c42":"4","c43":"4","c5":"-6","c6":"10"}],"sheshoubang":[{"c1":"1","c2":"扎哈维","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=56838","c3":"广州富力","c3L":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c4":"9","c5":"1"},{"c1":"2","c2":"高拉特","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=61622","c3":"广州恒大","c3L":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4":"7","c5":"5"},{"c1":"3","c2":"耶拉维奇","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=62419","c3":"贵州恒丰","c3L":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c4":"6","c5":"1"},{"c1":"4","c2":"伊尔马兹","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=39931","c3":"北京国安","c3L":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c4":"6","c5":"1"},{"c1":"5","c2":"武磊","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=116730","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"6","c2":"莫雷诺","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=77063","c3":"上海申花","c3L":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"7","c2":"阿洛伊西奥","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=100817","c3":"河北华夏","c3L":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"8","c2":"胡尔克","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=53645","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"1"},{"c1":"9","c2":"帕托","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=45215","c3":"天津权健","c3L":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c4":"5","c5":"1"},{"c1":"10","c2":"埃尔克森","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=61165","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"1"}]}},"error_code":0}';
                    $result = json_decode($results, true);
                    if (!$result["result"]) {
                        $data = $this->textData("哎呀，".$attr."积分榜好像被我当早饭吃了。[允悲]");
                        return $data;
                    }
                    $views = $result["result"]["views"]["jifenbang"];
                    $ans = $userinfo["screen_name"]."，".$result["result"]["key"]."群雄逐鹿，谁能争霸！";
                    foreach ($views as $view) {
                        $ans.=$view["c2"].$view["c41"]."胜".$view["c42"]."平".$view["c43"]."负积".$view["c6"]."分居第".$view["c1"]."位，";
                    }
                    $ans.="查看".$result["result"]["tabs"]["saicheng1"]."可回复【".$attr."赛程】，您可以回复球队名+赛程（如【".$views[rand(0,9)]["c2"]."赛程】) 查球队赛程，回复【".$attr."射手榜】查看射手榜。";
                    $data = $this->textData($ans);
                    break;
                case "league-ss":
                    $results = $this->httpPost(array("key"=>"ccfaea5a3a58a3fc395b0919a021b9fb", "league"=>$attr), 'http://op.juhe.cn/onebox/football/league');
                    //$results='{"reason":"查询成功","result":{"key":"中超","tabs":{"saicheng1":"第10轮赛程","saicheng2":"第11轮赛程","saicheng3":null,"jifenbang":"积分榜","sheshoubang":"射手榜"},"views":{"saicheng1":[{"c1":"已结束","c2":"05-19周五","c3":"18:00","c4T1":"北京国安","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c4R":"2-2","c4T2":"广州富力","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-19\/doc-ifyfkqks4341511.shtml?cre=360.ala.zc.sc","liveid":"906977"},{"c1":"已结束","c2":"05-19周五","c3":"20:00","c4T1":"广州恒大","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4R":"2-1","c4T2":"江苏苏宁","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/jiangsu\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-19\/doc-ifyfkqiv6569267.shtml?cre=360.ala.zc.sc","liveid":"906978"},{"c1":"已结束","c2":"05-20周六","c3":"15:30","c4T1":"上海申花","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c4R":"1-3","c4T2":"上海上港","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6588464.shtml?cre=360.ala.zc.sc","liveid":"906982"},{"c1":"已结束","c2":"05-20周六","c3":"15:30","c4T1":"延边富德","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/yanbian\/?cre=360.ala.zc.sc","c4R":"1-1","c4T2":"山东鲁能","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqks4363472.shtml?cre=360.ala.zc.sc","liveid":"906980"},{"c1":"已结束","c2":"05-20周六","c3":"19:35","c4T1":"重庆力帆","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/lifan\/?cre=360.ala.zc.sc","c4R":"0-1","c4T2":"天津权健","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6592220.shtml?cre=360.ala.zc.sc","liveid":"906981"},{"c1":"已结束","c2":"05-20周六","c3":"19:35","c4T1":"河北华夏","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4R":"2-0","c4T2":"辽宁开新","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/liaoning\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqks4367391.shtml?cre=360.ala.zc.sc","liveid":"906979"},{"c1":"已结束","c2":"05-21周日","c3":"15:30","c4T1":"长春亚泰","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/changchun\/?cre=360.ala.zc.sc","c4R":"1-1","c4T2":"天津泰达","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-21\/doc-ifyfkqiv6619583.shtml?cre=360.ala.zc.sc","liveid":"906984"},{"c1":"已结束","c2":"05-21周日","c3":"19:35","c4T1":"河南建业","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/henan\/?cre=360.ala.zc.sc","c4R":"0-1","c4T2":"贵州恒丰","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-21\/doc-ifyfkqiv6625599.shtml?cre=360.ala.zc.sc","liveid":"906983"}],"saicheng2":[{"c1":"未开赛","c2":"05-26周五","c3":"18:00","c4T1":"广州恒大","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"重庆力帆","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/lifan\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157658","liveid":"906986"},{"c1":"未开赛","c2":"05-26周五","c3":"20:00","c4T1":"广州富力","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"上海申花","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157657","liveid":"906985"},{"c1":"未开赛","c2":"05-27周六","c3":"15:30","c4T1":"辽宁开新","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/liaoning\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"延边富德","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/yanbian\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157660","liveid":"906988"},{"c1":"未开赛","c2":"05-27周六","c3":"15:30","c4T1":"贵州恒丰","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"江苏苏宁","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/jiangsu\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157663","liveid":"906991"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"河北华夏","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"长春亚泰","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/changchun\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157659","liveid":"906987"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"天津权健","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"天津泰达","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157662","liveid":"906990"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"上海上港","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"北京国安","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157664","liveid":"906992"},{"c1":"未开赛","c2":"05-28周日","c3":"19:35","c4T1":"山东鲁能","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"河南建业","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/henan\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157661","liveid":"906989"}],"saicheng3":null,"jifenbang":[{"c1":"1","c2":"广州恒大","c2L":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c3":"10","c41":"8","c42":"1","c43":"1","c5":"10","c6":"25"},{"c1":"2","c2":"上海上港","c2L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c3":"10","c41":"7","c42":"2","c43":"1","c5":"15","c6":"23"},{"c1":"3","c2":"河北华夏","c2L":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c3":"10","c41":"5","c42":"4","c43":"1","c5":"9","c6":"19"},{"c1":"4","c2":"广州富力","c2L":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c3":"10","c41":"5","c42":"3","c43":"2","c5":"3","c6":"18"},{"c1":"5","c2":"山东鲁能","c2L":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c3":"9","c41":"5","c42":"2","c43":"2","c5":"7","c6":"17"},{"c1":"6","c2":"北京国安","c2L":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c3":"10","c41":"4","c42":"3","c43":"3","c5":"0","c6":"15"},{"c1":"7","c2":"天津权健","c2L":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c3":"10","c41":"3","c42":"4","c43":"3","c5":"-2","c6":"13"},{"c1":"8","c2":"贵州恒丰","c2L":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c3":"10","c41":"3","c42":"3","c43":"4","c5":"-3","c6":"12"},{"c1":"9","c2":"上海申花","c2L":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c3":"9","c41":"3","c42":"2","c43":"4","c5":"1","c6":"11"},{"c1":"10","c2":"天津亿利","c2L":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c3":"10","c41":"2","c42":"4","c43":"4","c5":"-6","c6":"10"}],"sheshoubang":[{"c1":"1","c2":"扎哈维","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=56838","c3":"广州富力","c3L":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c4":"9","c5":"1"},{"c1":"2","c2":"高拉特","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=61622","c3":"广州恒大","c3L":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4":"7","c5":"5"},{"c1":"3","c2":"耶拉维奇","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=62419","c3":"贵州恒丰","c3L":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c4":"6","c5":"1"},{"c1":"4","c2":"伊尔马兹","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=39931","c3":"北京国安","c3L":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c4":"6","c5":"1"},{"c1":"5","c2":"武磊","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=116730","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"6","c2":"莫雷诺","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=77063","c3":"上海申花","c3L":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"7","c2":"阿洛伊西奥","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=100817","c3":"河北华夏","c3L":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"8","c2":"胡尔克","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=53645","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"1"},{"c1":"9","c2":"帕托","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=45215","c3":"天津权健","c3L":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c4":"5","c5":"1"},{"c1":"10","c2":"埃尔克森","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=61165","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"1"}]}},"error_code":0}';
                    $result = json_decode($results, true);
                    if (!$result["result"]) {
                        $data = $this->textData("".$attr."的射手榜哪里去了？你看见了没？反正我是没看见。[允悲]");
                        return $data;
                    }
                    $views = $result["result"]["views"]["sheshoubang"];
                    $ans = $userinfo["screen_name"]."，看看如今的".$result["result"]["key"]."哪位射手当家！";
                    foreach ($views as $view) {
                        $ans.=$view["c2"]."(".$view["c3"].")进".$view["c4"]."(".$view["c5"].")球居第".$view["c1"]."位，";
                    }
                    $ans.="回复【".$attr."积分榜】查看积分榜。";
                    $data = $this->textData($ans);
                    break;
                case "team":
                    $results = $this->httpPost(array("key"=>"ccfaea5a3a58a3fc395b0919a021b9fb", "team"=>$attr), 'http://op.juhe.cn/onebox/football/team');
                    //$results='{"reason":"查询成功","result":{"key":"上海上港","list":[{"c1":"亚冠","c2":"05-10","c3":"18:00","c4T1":"西悉尼流浪者","c4T1URL":"http:\/\/match.sports.sina.com.cn\/football\/team.php?id=61083","c4R":"3-2","c4T2":"上海上港","c4T2URL":"http:\/\/match.sports.sina.com.cn\/football\/team.php?id=41300","c51":"","c52":"视频暂无","c52Link":"","c53":"全场战报","c53Link":"http:\/\/sports.sina.com.cn\/china\/afccl\/2017-05-10\/doc-ifyfeius7787006.shtml?cre=360.ala.yg.team","c54":"","c54Link":""},{"c1":"中超","c2":"05-14","c3":"19:35","c4T1":"天津亿利","c4T1URL":"http:\/\/match.sports.sina.com.cn\/football\/team.php?id=148","c4R":"0-1","c4T2":"上海上港","c4T2URL":"http:\/\/match.sports.sina.com.cn\/football\/team.php?id=41300","c51":"","c52":"视频暂无","c52Link":"","c53":"全场战报","c53Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-14\/doc-ifyfeivp5701686.shtml?cre=360.ala.yg.team","c54":"","c54Link":""},{"c1":"中超","c2":"05-20","c3":"15:30","c4T1":"上海申花","c4T1URL":"http:\/\/match.sports.sina.com.cn\/football\/team.php?id=144","c4R":"1-3","c4T2":"上海上港","c4T2URL":"http:\/\/match.sports.sina.com.cn\/football\/team.php?id=41300","c51":"","c52":"视频暂无","c52Link":"","c53":"全场战报","c53Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6588464.shtml?cre=360.ala.yg.team","c54":"","c54Link":""},{"c1":"亚冠","c2":"05-24","c3":"20:00","c4T1":"上海上港","c4T1URL":"http:\/\/match.sports.sina.com.cn\/football\/team.php?id=41300","c4R":"VS","c4T2":"江苏苏宁","c4T2URL":"http:\/\/match.sports.sina.com.cn\/football\/team.php?id=179","c51":"","c52":"视频暂无","c52Link":"","c53":"图文直播","c53Link":"http:\/\/match.sports.sina.com.cn\/livecast\/g\/live.php?id=160791","c54":"","c54Link":""},{"c1":"中超","c2":"05-27","c3":"19:35","c4T1":"上海上港","c4T1URL":"http:\/\/match.sports.sina.com.cn\/football\/team.php?id=41300","c4R":"VS","c4T2":"北京国安","c4T2URL":"http:\/\/match.sports.sina.com.cn\/football\/team.php?id=136","c51":"","c52":"视频暂无","c52Link":"","c53":"图文直播","c53Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157664","c54":"","c54Link":""},{"c1":"亚冠","c2":"05-31","c3":"20:00","c4T1":"江苏苏宁","c4T1URL":"http:\/\/match.sports.sina.com.cn\/football\/team.php?id=179","c4R":"VS","c4T2":"上海上港","c4T2URL":"http:\/\/match.sports.sina.com.cn\/football\/team.php?id=41300","c51":"","c52":"视频暂无","c52Link":"","c53":"图文直播","c53Link":"http:\/\/match.sports.sina.com.cn\/livecast\/g\/live.php?id=160798","c54":"","c54Link":""}]},"error_code":0}';
                    $result = json_decode($results, true);
                    if (!$result["result"]) {
                        $data = $this->textData("斯塔克大脑一片空白，你还是去搜一下".$attr."的赛程吧。[允悲] http://www.sodasoccer.com/search/index.jsp?key=".urlencode($attr));
                        return $data;
                    }
                    $views = $result["result"]["list"];
                    $title = $result["result"]["key"];
                    $ans = $userinfo["screen_name"]."，这是".$title."的近期赛程：";
                    $played = "";
                    $gotoplay = "";
                    foreach ($views as $view) {
                        if ($view["c4R"]!="VS") {
                            if ($view['c4T1']==$title) {
                                $viewtext = "主场 ".$view["c4R"]." ".$view["c4T2"]."（".$view['c1']." ".$view['c2']."）";
                            } else {
                                $viewtext = "客场 ".$view["c4R"]." ".$view["c4T1"]."（".$view['c1']." ".$view['c2']."）";
                            }
                            $played.= $viewtext."，";
                        } else {
                            if ($view['c4T1']==$title) {
                                $viewtext = "将于 ".$view["c2"]." ".$view["c3"]." 主场迎战".$view["c4T2"]."（".$view["c1"]."）";
                            } else {
                                $viewtext = "将于 ".$view["c2"]." ".$view["c3"]." 做客挑战".$view["c4T1"]."（".$view["c1"]."）";
                            }
                            $gotoplay.=$viewtext."，";
                        }
                    }
                    if ($gotoplay != "") {
                        $ans.=$title.$gotoplay;
                    }
                    if ($played != "") {
                        $ans.="最近结束比赛中，" . $title . $played;
                    }
                    $ans.="查本赛季双方对阵，可回复主队名VS客队名（如【申花VS上港】）。";
                    $data = $this->textData($ans);
                    break;
                case "teams":
                    $rule  = "/^(\S+)\s(\S+)/";
                    preg_match($rule,$attr,$preg_result1);
                    if(!$preg_result1) {
                        $data = $this->$this->textData($attr."？格式不对哦！");
                        return $data;
                    }
                    $trans = array(
                        "申花" => "上海申花",
                        "上海绿地申花" => "上海申花",
                        "骚花" => "上海申花",
                        "丧戆" => "上海上港",
                        "上港" => "上海上港",
                        "恒大" => "广州恒大",
                        "富力" => "广州富力",
                        "建业" => "河南建业",
                        "泰达" => "天津泰达",
                        "亿利" => "天津泰达",
                        "天津亿利" => "天津泰达",
                        "亚泰" => "长春亚泰",
                        "力帆" => "重庆力帆",
                        "重庆" => "重庆力帆",
                        "重庆当代力帆" => "重庆力帆",
                        "天津" => "天津泰达",
                        "长春" => "长春亚泰",
                        "河南" => "河南建业",
                        "上海" => "上海申花",
                        "皇马" => "皇家马德里",
                        "巴萨" => "巴塞罗那",
                        "马竞" => "马德里竞技",
                        "毕巴" => "毕尔巴鄂竞技",
                        "拉科" => "拉科鲁尼亚",
                        "瓦伦" => "瓦伦西亚",
                        "贝蒂斯" => "皇家贝蒂斯",
                        "黄潜" => "比利亚雷亚尔",
                        "希洪" => "希洪竞技",
                        "塞尔塔" => "维戈塞尔塔",
                        "曼彻斯特联" => "曼联",
                        "曼彻斯特城" => "曼城",
                        "托特纳姆热刺" => "热刺",
                        "拜仁" => "拜仁慕尼黑",
                        "大巴黎" => "巴黎圣日耳曼",
                        "尤文" => "尤文图斯",
                        "多特" => "多特蒙德",
                        "米兰" => "AC米兰",
                        "国米" => "国际米兰"
                    );
                    $preg_result1[1]=$trans[$preg_result1[1]]?$trans[$preg_result1[1]]:$preg_result1[1];
                    $preg_result1[2]=$trans[$preg_result1[2]]?$trans[$preg_result1[2]]:$preg_result1[2];
                    $results = $this->httpPost(array("key"=>"ccfaea5a3a58a3fc395b0919a021b9fb", "hteam"=>$preg_result1[1], "vteam"=>$preg_result1[2]), 'http://op.juhe.cn/onebox/football/combat');
                    //$results='{"reason":"查询成功","result":{"key":"上海上港天津泰达","list":[{"date":"05-14周日","time":"19:35","team1":"天津泰达","team1icon":"http:\/\/i2.sinaimg.cn\/ty\/livecast\/csl2\/tjtd_small_24x24.jpg","score":"0-1","team2":"上海上港","team2icon":"http:\/\/www.sinaimg.cn\/ty\/deco\/2013\/0307\/dongya.jpg","link1content":"视频暂无","link1url":"","link2content":"全场战报","link2url":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-14\/doc-ifyfeivp5701686.shtml?cre=bd.ala.zc.vs","link3content":"","link3url":"","live":"1","team1url":"","team2url":""},{"date":"09-09周六","time":"19:35","team1":"上海上港","team1icon":"http:\/\/www.sinaimg.cn\/ty\/deco\/2013\/0307\/dongya.jpg","score":"VS","team2":"天津泰达","team2icon":"http:\/\/i2.sinaimg.cn\/ty\/livecast\/csl2\/tjtd_small_24x24.jpg","link1content":"视频暂无","link1url":"","link2content":"图文直播","link2url":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157764","link3content":"","link3url":"","live":"","team1url":"","team2url":""}]},"error_code":0}';
                    $result = json_decode($results, true);
                    if (!$result["result"]) {
                        $data = $this->textData("斯塔克大脑一片空白，你还是去搜一下".$preg_result1[1]."和".$preg_result1[2]."的交锋情况吧。".urlencode("http://baidu.com/s?wd=".urlencode($preg_result1[1])."VS".urlencode($preg_result1[2])));
                        return $data;
                    }
                    $views = $result["result"]["list"];
                    $title = $preg_result1[1];
                    $ans = $userinfo["screen_name"]."，".$preg_result1[1]."和".$preg_result1[2]."本赛季兵刃相见：";
                    $played = "";
                    $gotoplay = "";
                    foreach ($views as $view) {
                        if ($view["score"]!="VS") {
                            if ($view['team1']==$title) {
                                $viewtext = "已于 ".$view["date"]." ".$view["time"]." 主场 ".$view["score"]." ".$view["team2"];
                            } else {
                                $viewtext = "已于 ".$view["date"]." ".$view["time"]." 客场 ".$view["score"]." ".$view["team2"];
                            }
                            $played.= $viewtext."，";
                        } else {
                            if ($view['team2']==$title) {
                                $viewtext = "将于 ".$view["date"]." ".$view["time"]." 主场迎战".$view["team1"];
                            } else {
                                $viewtext = "将于 ".$view["date"]." ".$view["time"]." 做客挑战".$view["team1"];
                            }
                            $gotoplay.=$viewtext."，";
                        }
                    }
                    if ($gotoplay != "") {
                        $ans.=$title.$gotoplay;
                    }
                    if ($played != "") {
                        $ans.=$title . $played;
                    }
                    $ans.="查球队赛程，可回复球队名+赛程（如【".$preg_result1[1]."赛程】）。";
                    $data = $this->textData($ans);
                    break;
                case "league-rank":
                    $rule  = "/^(\S+)\s(\S+)/";
                    preg_match($rule,$attr,$preg_result1);
                    if(!$preg_result1) {
                        $data = $this->$this->textData($attr."？格式不对哦！[失望]");
                        return $data;
                    }
                    $rank = (int)$preg_result1[2];
                    if ($rank>10) {
                        $data = $this->textData("目前只支持查询".$preg_result1[1]."前十名哦！[失望]");
                        return $data;
                    }
                    $results = $this->httpPost(array("key"=>"ccfaea5a3a58a3fc395b0919a021b9fb", "league"=>$preg_result1[1]), 'http://op.juhe.cn/onebox/football/league');
                    //$results='{"reason":"查询成功","result":{"key":"中超","tabs":{"saicheng1":"第10轮赛程","saicheng2":"第11轮赛程","saicheng3":null,"jifenbang":"积分榜","sheshoubang":"射手榜"},"views":{"saicheng1":[{"c1":"已结束","c2":"05-19周五","c3":"18:00","c4T1":"北京国安","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c4R":"2-2","c4T2":"广州富力","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-19\/doc-ifyfkqks4341511.shtml?cre=360.ala.zc.sc","liveid":"906977"},{"c1":"已结束","c2":"05-19周五","c3":"20:00","c4T1":"广州恒大","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4R":"2-1","c4T2":"江苏苏宁","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/jiangsu\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-19\/doc-ifyfkqiv6569267.shtml?cre=360.ala.zc.sc","liveid":"906978"},{"c1":"已结束","c2":"05-20周六","c3":"15:30","c4T1":"上海申花","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c4R":"1-3","c4T2":"上海上港","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6588464.shtml?cre=360.ala.zc.sc","liveid":"906982"},{"c1":"已结束","c2":"05-20周六","c3":"15:30","c4T1":"延边富德","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/yanbian\/?cre=360.ala.zc.sc","c4R":"1-1","c4T2":"山东鲁能","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqks4363472.shtml?cre=360.ala.zc.sc","liveid":"906980"},{"c1":"已结束","c2":"05-20周六","c3":"19:35","c4T1":"重庆力帆","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/lifan\/?cre=360.ala.zc.sc","c4R":"0-1","c4T2":"天津权健","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6592220.shtml?cre=360.ala.zc.sc","liveid":"906981"},{"c1":"已结束","c2":"05-20周六","c3":"19:35","c4T1":"河北华夏","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4R":"2-0","c4T2":"辽宁开新","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/liaoning\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqks4367391.shtml?cre=360.ala.zc.sc","liveid":"906979"},{"c1":"已结束","c2":"05-21周日","c3":"15:30","c4T1":"长春亚泰","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/changchun\/?cre=360.ala.zc.sc","c4R":"1-1","c4T2":"天津泰达","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-21\/doc-ifyfkqiv6619583.shtml?cre=360.ala.zc.sc","liveid":"906984"},{"c1":"已结束","c2":"05-21周日","c3":"19:35","c4T1":"河南建业","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/henan\/?cre=360.ala.zc.sc","c4R":"0-1","c4T2":"贵州恒丰","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-21\/doc-ifyfkqiv6625599.shtml?cre=360.ala.zc.sc","liveid":"906983"}],"saicheng2":[{"c1":"未开赛","c2":"05-26周五","c3":"18:00","c4T1":"广州恒大","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"重庆力帆","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/lifan\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157658","liveid":"906986"},{"c1":"未开赛","c2":"05-26周五","c3":"20:00","c4T1":"广州富力","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"上海申花","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157657","liveid":"906985"},{"c1":"未开赛","c2":"05-27周六","c3":"15:30","c4T1":"辽宁开新","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/liaoning\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"延边富德","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/yanbian\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157660","liveid":"906988"},{"c1":"未开赛","c2":"05-27周六","c3":"15:30","c4T1":"贵州恒丰","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"江苏苏宁","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/jiangsu\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157663","liveid":"906991"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"河北华夏","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"长春亚泰","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/changchun\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157659","liveid":"906987"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"天津权健","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"天津泰达","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157662","liveid":"906990"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"上海上港","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"北京国安","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157664","liveid":"906992"},{"c1":"未开赛","c2":"05-28周日","c3":"19:35","c4T1":"山东鲁能","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"河南建业","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/henan\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157661","liveid":"906989"}],"saicheng3":null,"jifenbang":[{"c1":"1","c2":"广州恒大","c2L":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c3":"10","c41":"8","c42":"1","c43":"1","c5":"10","c6":"25"},{"c1":"2","c2":"上海上港","c2L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c3":"10","c41":"7","c42":"2","c43":"1","c5":"15","c6":"23"},{"c1":"3","c2":"河北华夏","c2L":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c3":"10","c41":"5","c42":"4","c43":"1","c5":"9","c6":"19"},{"c1":"4","c2":"广州富力","c2L":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c3":"10","c41":"5","c42":"3","c43":"2","c5":"3","c6":"18"},{"c1":"5","c2":"山东鲁能","c2L":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c3":"9","c41":"5","c42":"2","c43":"2","c5":"7","c6":"17"},{"c1":"6","c2":"北京国安","c2L":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c3":"10","c41":"4","c42":"3","c43":"3","c5":"0","c6":"15"},{"c1":"7","c2":"天津权健","c2L":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c3":"10","c41":"3","c42":"4","c43":"3","c5":"-2","c6":"13"},{"c1":"8","c2":"贵州恒丰","c2L":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c3":"10","c41":"3","c42":"3","c43":"4","c5":"-3","c6":"12"},{"c1":"9","c2":"上海申花","c2L":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c3":"9","c41":"3","c42":"2","c43":"4","c5":"1","c6":"11"},{"c1":"10","c2":"天津亿利","c2L":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c3":"10","c41":"2","c42":"4","c43":"4","c5":"-6","c6":"10"}],"sheshoubang":[{"c1":"1","c2":"扎哈维","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=56838","c3":"广州富力","c3L":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c4":"9","c5":"1"},{"c1":"2","c2":"高拉特","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=61622","c3":"广州恒大","c3L":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4":"7","c5":"5"},{"c1":"3","c2":"耶拉维奇","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=62419","c3":"贵州恒丰","c3L":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c4":"6","c5":"1"},{"c1":"4","c2":"伊尔马兹","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=39931","c3":"北京国安","c3L":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c4":"6","c5":"1"},{"c1":"5","c2":"武磊","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=116730","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"6","c2":"莫雷诺","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=77063","c3":"上海申花","c3L":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"7","c2":"阿洛伊西奥","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=100817","c3":"河北华夏","c3L":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"8","c2":"胡尔克","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=53645","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"1"},{"c1":"9","c2":"帕托","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=45215","c3":"天津权健","c3L":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c4":"5","c5":"1"},{"c1":"10","c2":"埃尔克森","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=61165","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"1"}]}},"error_code":0}';
                    $result = json_decode($results, true);
                    if (!$result["result"]) {
                        $data = $this->textData("斯塔克用尽全身力气也找不到".$attr."。[允悲]");
                        return $data;
                    }
                    $views = $result["result"]["views"]["jifenbang"];
                    $ans = $userinfo["screen_name"]."，".$result["result"]["key"]."群雄逐鹿，";
                    $view = $views[$rank-1];
                    $ans.=$view["c2"].$view["c41"]."胜".$view["c42"]."平".$view["c43"]."负积".$view["c6"]."分居第".$view["c1"]."位!";
                    $data = $this->textData($ans);
                    break;
                case "league-rank2":
                    $rule  = "/^(\S+)\s(\S+)/";
                    preg_match($rule,$attr,$preg_result1);
                    if(!$preg_result1) {
                        $data = $this->$this->textData($attr."？格式不对哦！");
                        return $data;
                    }
                    $results = $this->httpPost(array("key"=>"ccfaea5a3a58a3fc395b0919a021b9fb", "league"=>$preg_result1[1]), 'http://op.juhe.cn/onebox/football/league');
                    //$results='{"reason":"查询成功","result":{"key":"中超","tabs":{"saicheng1":"第10轮赛程","saicheng2":"第11轮赛程","saicheng3":null,"jifenbang":"积分榜","sheshoubang":"射手榜"},"views":{"saicheng1":[{"c1":"已结束","c2":"05-19周五","c3":"18:00","c4T1":"北京国安","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c4R":"2-2","c4T2":"广州富力","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-19\/doc-ifyfkqks4341511.shtml?cre=360.ala.zc.sc","liveid":"906977"},{"c1":"已结束","c2":"05-19周五","c3":"20:00","c4T1":"广州恒大","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4R":"2-1","c4T2":"江苏苏宁","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/jiangsu\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-19\/doc-ifyfkqiv6569267.shtml?cre=360.ala.zc.sc","liveid":"906978"},{"c1":"已结束","c2":"05-20周六","c3":"15:30","c4T1":"上海申花","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c4R":"1-3","c4T2":"上海上港","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6588464.shtml?cre=360.ala.zc.sc","liveid":"906982"},{"c1":"已结束","c2":"05-20周六","c3":"15:30","c4T1":"延边富德","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/yanbian\/?cre=360.ala.zc.sc","c4R":"1-1","c4T2":"山东鲁能","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqks4363472.shtml?cre=360.ala.zc.sc","liveid":"906980"},{"c1":"已结束","c2":"05-20周六","c3":"19:35","c4T1":"重庆力帆","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/lifan\/?cre=360.ala.zc.sc","c4R":"0-1","c4T2":"天津权健","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6592220.shtml?cre=360.ala.zc.sc","liveid":"906981"},{"c1":"已结束","c2":"05-20周六","c3":"19:35","c4T1":"河北华夏","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4R":"2-0","c4T2":"辽宁开新","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/liaoning\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqks4367391.shtml?cre=360.ala.zc.sc","liveid":"906979"},{"c1":"已结束","c2":"05-21周日","c3":"15:30","c4T1":"长春亚泰","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/changchun\/?cre=360.ala.zc.sc","c4R":"1-1","c4T2":"天津泰达","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-21\/doc-ifyfkqiv6619583.shtml?cre=360.ala.zc.sc","liveid":"906984"},{"c1":"已结束","c2":"05-21周日","c3":"19:35","c4T1":"河南建业","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/henan\/?cre=360.ala.zc.sc","c4R":"0-1","c4T2":"贵州恒丰","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-21\/doc-ifyfkqiv6625599.shtml?cre=360.ala.zc.sc","liveid":"906983"}],"saicheng2":[{"c1":"未开赛","c2":"05-26周五","c3":"18:00","c4T1":"广州恒大","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"重庆力帆","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/lifan\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157658","liveid":"906986"},{"c1":"未开赛","c2":"05-26周五","c3":"20:00","c4T1":"广州富力","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"上海申花","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157657","liveid":"906985"},{"c1":"未开赛","c2":"05-27周六","c3":"15:30","c4T1":"辽宁开新","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/liaoning\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"延边富德","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/yanbian\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157660","liveid":"906988"},{"c1":"未开赛","c2":"05-27周六","c3":"15:30","c4T1":"贵州恒丰","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"江苏苏宁","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/jiangsu\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157663","liveid":"906991"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"河北华夏","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"长春亚泰","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/changchun\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157659","liveid":"906987"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"天津权健","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"天津泰达","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157662","liveid":"906990"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"上海上港","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"北京国安","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157664","liveid":"906992"},{"c1":"未开赛","c2":"05-28周日","c3":"19:35","c4T1":"山东鲁能","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"河南建业","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/henan\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157661","liveid":"906989"}],"saicheng3":null,"jifenbang":[{"c1":"1","c2":"广州恒大","c2L":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c3":"10","c41":"8","c42":"1","c43":"1","c5":"10","c6":"25"},{"c1":"2","c2":"上海上港","c2L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c3":"10","c41":"7","c42":"2","c43":"1","c5":"15","c6":"23"},{"c1":"3","c2":"河北华夏","c2L":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c3":"10","c41":"5","c42":"4","c43":"1","c5":"9","c6":"19"},{"c1":"4","c2":"广州富力","c2L":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c3":"10","c41":"5","c42":"3","c43":"2","c5":"3","c6":"18"},{"c1":"5","c2":"山东鲁能","c2L":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c3":"9","c41":"5","c42":"2","c43":"2","c5":"7","c6":"17"},{"c1":"6","c2":"北京国安","c2L":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c3":"10","c41":"4","c42":"3","c43":"3","c5":"0","c6":"15"},{"c1":"7","c2":"天津权健","c2L":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c3":"10","c41":"3","c42":"4","c43":"3","c5":"-2","c6":"13"},{"c1":"8","c2":"贵州恒丰","c2L":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c3":"10","c41":"3","c42":"3","c43":"4","c5":"-3","c6":"12"},{"c1":"9","c2":"上海申花","c2L":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c3":"9","c41":"3","c42":"2","c43":"4","c5":"1","c6":"11"},{"c1":"10","c2":"天津亿利","c2L":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c3":"10","c41":"2","c42":"4","c43":"4","c5":"-6","c6":"10"}],"sheshoubang":[{"c1":"1","c2":"扎哈维","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=56838","c3":"广州富力","c3L":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c4":"9","c5":"1"},{"c1":"2","c2":"高拉特","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=61622","c3":"广州恒大","c3L":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4":"7","c5":"5"},{"c1":"3","c2":"耶拉维奇","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=62419","c3":"贵州恒丰","c3L":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c4":"6","c5":"1"},{"c1":"4","c2":"伊尔马兹","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=39931","c3":"北京国安","c3L":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c4":"6","c5":"1"},{"c1":"5","c2":"武磊","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=116730","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"6","c2":"莫雷诺","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=77063","c3":"上海申花","c3L":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"7","c2":"阿洛伊西奥","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=100817","c3":"河北华夏","c3L":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"8","c2":"胡尔克","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=53645","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"1"},{"c1":"9","c2":"帕托","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=45215","c3":"天津权健","c3L":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c4":"5","c5":"1"},{"c1":"10","c2":"埃尔克森","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=61165","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"1"}]}},"error_code":0}';
                    $result = json_decode($results, true);
                    if (!$result["result"]) {
                        $data = $this->textData("斯塔克用尽全身力气也找不到".$attr."排名。[允悲]");
                        return $data;
                    }
                    $views = $result["result"]["views"]["jifenbang"];
                    $ans = $userinfo["screen_name"]."，".$result["result"]["key"]."风云再起，";
                    $flag = false;
                    foreach ($views as $view) {
                        if ($view["c2"]==$preg_result1[2]) {
                            $flag = true;
                            $ans.=$view["c2"].$view["c41"]."胜".$view["c42"]."平".$view["c43"]."负积".$view["c6"]."分居第".$view["c1"]."位!";
                        }
                    }
                    if (!$flag) $ans.="但很遗憾，我们在前十名中没有看到".$preg_result1[2]."的身影。[允悲]";
                    $data = $this->textData($ans);
                    break;
                case "laoda":
                    $rule  = "/^(\S+)\s(\S+)\s(\S+)\s(\S+)/";
                    preg_match($rule,$attr,$preg_result1);
                    if(!$preg_result1) {
                        $data = $this->$this->textData($attr."？格式不对哦！");
                        return $data;
                    }
                    $results = $this->httpPost(array("key"=>"ccfaea5a3a58a3fc395b0919a021b9fb", "league"=>$preg_result1[1]), 'http://op.juhe.cn/onebox/football/league');
                    //$results='{"reason":"查询成功","result":{"key":"中超","tabs":{"saicheng1":"第10轮赛程","saicheng2":"第11轮赛程","saicheng3":null,"jifenbang":"积分榜","sheshoubang":"射手榜"},"views":{"saicheng1":[{"c1":"已结束","c2":"05-19周五","c3":"18:00","c4T1":"北京国安","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c4R":"2-2","c4T2":"广州富力","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-19\/doc-ifyfkqks4341511.shtml?cre=360.ala.zc.sc","liveid":"906977"},{"c1":"已结束","c2":"05-19周五","c3":"20:00","c4T1":"广州恒大","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4R":"2-1","c4T2":"江苏苏宁","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/jiangsu\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-19\/doc-ifyfkqiv6569267.shtml?cre=360.ala.zc.sc","liveid":"906978"},{"c1":"已结束","c2":"05-20周六","c3":"15:30","c4T1":"上海申花","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c4R":"1-3","c4T2":"上海上港","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6588464.shtml?cre=360.ala.zc.sc","liveid":"906982"},{"c1":"已结束","c2":"05-20周六","c3":"15:30","c4T1":"延边富德","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/yanbian\/?cre=360.ala.zc.sc","c4R":"1-1","c4T2":"山东鲁能","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqks4363472.shtml?cre=360.ala.zc.sc","liveid":"906980"},{"c1":"已结束","c2":"05-20周六","c3":"19:35","c4T1":"重庆力帆","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/lifan\/?cre=360.ala.zc.sc","c4R":"0-1","c4T2":"天津权健","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqiv6592220.shtml?cre=360.ala.zc.sc","liveid":"906981"},{"c1":"已结束","c2":"05-20周六","c3":"19:35","c4T1":"河北华夏","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4R":"2-0","c4T2":"辽宁开新","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/liaoning\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-20\/doc-ifyfkqks4367391.shtml?cre=360.ala.zc.sc","liveid":"906979"},{"c1":"已结束","c2":"05-21周日","c3":"15:30","c4T1":"长春亚泰","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/changchun\/?cre=360.ala.zc.sc","c4R":"1-1","c4T2":"天津泰达","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-21\/doc-ifyfkqiv6619583.shtml?cre=360.ala.zc.sc","liveid":"906984"},{"c1":"已结束","c2":"05-21周日","c3":"19:35","c4T1":"河南建业","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/henan\/?cre=360.ala.zc.sc","c4R":"0-1","c4T2":"贵州恒丰","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"全场战报","c52Link":"http:\/\/sports.sina.com.cn\/china\/j\/2017-05-21\/doc-ifyfkqiv6625599.shtml?cre=360.ala.zc.sc","liveid":"906983"}],"saicheng2":[{"c1":"未开赛","c2":"05-26周五","c3":"18:00","c4T1":"广州恒大","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"重庆力帆","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/lifan\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157658","liveid":"906986"},{"c1":"未开赛","c2":"05-26周五","c3":"20:00","c4T1":"广州富力","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"上海申花","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157657","liveid":"906985"},{"c1":"未开赛","c2":"05-27周六","c3":"15:30","c4T1":"辽宁开新","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/liaoning\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"延边富德","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/yanbian\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157660","liveid":"906988"},{"c1":"未开赛","c2":"05-27周六","c3":"15:30","c4T1":"贵州恒丰","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"江苏苏宁","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/jiangsu\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157663","liveid":"906991"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"河北华夏","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"长春亚泰","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/changchun\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157659","liveid":"906987"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"天津权健","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"天津泰达","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157662","liveid":"906990"},{"c1":"未开赛","c2":"05-27周六","c3":"19:35","c4T1":"上海上港","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"北京国安","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157664","liveid":"906992"},{"c1":"未开赛","c2":"05-28周日","c3":"19:35","c4T1":"山东鲁能","c4T1URL":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c4R":"VS","c4T2":"河南建业","c4T2URL":"http:\/\/sports.sina.com.cn\/csl\/henan\/?cre=360.ala.zc.sc","c51":"视频暂无","c51Link":"","c52":"图文直播","c52Link":"http:\/\/match.sports.sina.com.cn\/livecast\/n\/live.php?id=157661","liveid":"906989"}],"saicheng3":null,"jifenbang":[{"c1":"1","c2":"广州恒大","c2L":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c3":"10","c41":"8","c42":"1","c43":"1","c5":"10","c6":"25"},{"c1":"2","c2":"上海上港","c2L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c3":"10","c41":"7","c42":"2","c43":"1","c5":"15","c6":"23"},{"c1":"3","c2":"河北华夏","c2L":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c3":"10","c41":"5","c42":"4","c43":"1","c5":"9","c6":"19"},{"c1":"4","c2":"广州富力","c2L":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c3":"10","c41":"5","c42":"3","c43":"2","c5":"3","c6":"18"},{"c1":"5","c2":"山东鲁能","c2L":"http:\/\/sports.sina.com.cn\/csl\/shandong\/?cre=360.ala.zc.sc","c3":"9","c41":"5","c42":"2","c43":"2","c5":"7","c6":"17"},{"c1":"6","c2":"北京国安","c2L":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c3":"10","c41":"4","c42":"3","c43":"3","c5":"0","c6":"15"},{"c1":"7","c2":"天津权健","c2L":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c3":"10","c41":"3","c42":"4","c43":"3","c5":"-2","c6":"13"},{"c1":"8","c2":"贵州恒丰","c2L":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c3":"10","c41":"3","c42":"3","c43":"4","c5":"-3","c6":"12"},{"c1":"9","c2":"上海申花","c2L":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c3":"9","c41":"3","c42":"2","c43":"4","c5":"1","c6":"11"},{"c1":"10","c2":"天津亿利","c2L":"http:\/\/sports.sina.com.cn\/csl\/tianjin\/?cre=360.ala.zc.sc","c3":"10","c41":"2","c42":"4","c43":"4","c5":"-6","c6":"10"}],"sheshoubang":[{"c1":"1","c2":"扎哈维","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=56838","c3":"广州富力","c3L":"http:\/\/sports.sina.com.cn\/csl\/fuli\/?cre=360.ala.zc.sc","c4":"9","c5":"1"},{"c1":"2","c2":"高拉特","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=61622","c3":"广州恒大","c3L":"http:\/\/sports.sina.com.cn\/csl\/guangzhou\/?cre=360.ala.zc.sc","c4":"7","c5":"5"},{"c1":"3","c2":"耶拉维奇","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=62419","c3":"贵州恒丰","c3L":"http:\/\/sports.sina.com.cn\/csl\/zhicheng\/?cre=360.ala.zc.sc","c4":"6","c5":"1"},{"c1":"4","c2":"伊尔马兹","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=39931","c3":"北京国安","c3L":"http:\/\/sports.sina.com.cn\/csl\/beijing\/?cre=360.ala.zc.sc","c4":"6","c5":"1"},{"c1":"5","c2":"武磊","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=116730","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"6","c2":"莫雷诺","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=77063","c3":"上海申花","c3L":"http:\/\/sports.sina.com.cn\/csl\/shanghai\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"7","c2":"阿洛伊西奥","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=100817","c3":"河北华夏","c3L":"http:\/\/sports.sina.com.cn\/csl\/huaxia\/?cre=360.ala.zc.sc","c4":"5","c5":"0"},{"c1":"8","c2":"胡尔克","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=53645","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"1"},{"c1":"9","c2":"帕托","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=45215","c3":"天津权健","c3L":"http:\/\/sports.sina.com.cn\/csl\/quanjian\/?cre=360.ala.zc.sc","c4":"5","c5":"1"},{"c1":"10","c2":"埃尔克森","c2L":"http:\/\/match.sports.sina.com.cn\/football\/csl\/player.php?id=61165","c3":"上海上港","c3L":"http:\/\/sports.sina.com.cn\/csl\/eastasia\/?cre=360.ala.zc.sc","c4":"5","c5":"1"}]}},"error_code":0}';
                    $result = json_decode($results, true);
                    if (!$result["result"]) {
                        $data = $this->textData("斯塔克不知道".$preg_result1[2]."和".$preg_result1[3]."到底谁是".$preg_result1[4]."。[允悲]");
                        return $data;
                    }
                    $views = $result["result"]["views"]["jifenbang"];
                    $ans = "关于".$preg_result1[2]."和".$preg_result1[3]."到底谁是".$preg_result1[4]."的问题，";
                    $flag1 = 100;
                    $flag2 = 100;
                    foreach ($views as $view) {
                        if ($view["c2"]==$preg_result1[2]) {
                            $flag1 = (int)$view["c1"];
                            $ans.=$view["c2"].$view["c41"]."胜".$view["c42"]."平".$view["c43"]."负积".$view["c6"]."分居第".$view["c1"]."位；";
                        }
                        if ($view["c2"]==$preg_result1[3]) {
                            $flag2 = (int)$view["c1"];
                            $ans.=$view["c2"].$view["c41"]."胜".$view["c42"]."平".$view["c43"]."负积".$view["c6"]."分居第".$view["c1"]."位；";
                        }
                    }
                    if ($flag1==100) $ans.="前十名中没有看到".$preg_result1[2]."的身影；";
                    if ($flag2==100) $ans.=$preg_result1[2]."没有出现在积分榜前几名中；";
                    if ($preg_result1[4]=="上海滩老大" || $preg_result1[4]=="沪上代表") {
                        $ans.="不是赢下一场德比就成为上海滩老大，或者一时的积分榜领先就成为沪上代表。就如见证一个孩子成长一般，我们见证着申花23年来的每一个脚印。而申花，也在不知不觉中，带给了这座城市几代人难以替代的回忆。风里雨里，我们坚定；几多困难，我们不曾放弃；申花，是最正确的选择。";
                    } elseif ($preg_result1[4]=="津门代表") {
                        $ans.="不是赢下一场德比就成为津门代表，或者一时的积分榜领先就成为天津老大。一家游走于法律边缘的企业不可能代表天津，泰达，才是天津的正统。";
                    } elseif ($flag1<$flag2) {
                        $ans.="由此我们可以看出，".$preg_result1[2]."才是".$preg_result1[4]."。";
                    } elseif ($flag2<$flag1) {
                        $ans .= "那么我们可以得出结论，" . $preg_result1[3] . "才是" . $preg_result1[4] . "。";
                    } else {
                        $ans .= "哎呀，谁是".$preg_result1[4]."，斯塔克也很纠结啊。[委屈]";
                    }
                    $data = $this->textData($ans);
                    break;
                default:
                    $data = $this->textData('哎呀，斯塔克问答不上来这个问题了。[害羞]');
                    break;
            }
        }
        return $data;
    }
        
        
     function reply($type, $info, $userid, $loc="") {
            $result = $this->httpPost(array(
                "key" => $this->robot_key,
                "info" => $info,
                "userid" => $userid,
                "loc" => $loc
            ), $this->robot_api);
            $parse = json_decode($result, true);
            switch ($parse["code"]) {
                case "100000":
                    $data = $this->textData($parse["text"]);
                    break;
                case "200000":
                    $data = $this->textData($parse["text"]."。详情：".$parse["url"]);
                    break;
                case "302000":
                    $article_data = array();
                    $items = $parse["list"];
                    $i = 0;
                    foreach ($items as $item) {
                        $article_data[i] = array(
                            "display_name" => $item['article'],
                            "summary" => "来源: ".$item['source'],
                            "image" => $item['icon'],
                            "url" => $item['detailurl']
                        );
                        $i = $i + 1;
                    }
                    $data = $this->articleData($article_data);
                    break;
                case "308000":
                        $article_data = array();
                        $article_data[0] = array(
                                "display_name" => $parse['list']['name'],
                                "summary" => $parse['list']['info'],
                                "image" => $parse['list']['icon'],
                                "url" => $parse['list']['detailurl'],
                        );
                        $data = $this->articleData($article_data);
                    break;
                case "40001":
                    $data = $this->textData('你好像触发了什么新技能？斯塔克无能为力了。[哼]');
                    break;
                case "40002":
                    $data = $this->textData('哎呀，你说大声一点嘛。我听不见。[怒骂]');
                    break;
                case "40004":
                    $data = $this->textData('本宫今天累了，不陪你聊天了。[睡]');
                    break;
                case "40007":
                    $data = $this->textData('你给朕发的都是什么玩意!!![怒骂]');
                    break;
                default:
                    $data = $this->textData('人家好像脑子短路了，快告诉主人让他给我换电池[悲伤]');
                    break;
            }
            return $data;
    }      
        
        
        
        
        
        
        
   
        
        
        
        
        
        
        
    /**
     * POST请求
     */
    function httpPost($param, $url, $timeout = 30) {
        $ch = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        #curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        #curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $passwd);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $strPOST);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * get请求
     */
     function httpGet($url) {
            $oCurl = curl_init();
            if(stripos($url,"https://")!==FALSE){
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
            }
            curl_setopt($oCurl, CURLOPT_URL, $url);
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
            $sContent = curl_exec($oCurl);
            curl_close($oCurl);
            return $sContent;
    }
    
    function httpGetWithPic($myurl, $last) {
        // Get cURL resource
        $ch = curl_init();
        // Set url
        curl_setopt($ch, CURLOPT_URL, 'https://api.youtu.qq.com/youtu/'.$last);
        // Set method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        // Set options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: SQVSpFqIGNFRK3h9BjDlqOHe4V9hPTEwMDgzNjIzJms9QUtJRHpuUnIyekFBT09FbzlFcWV6NXZaeTdWZlpFZXBkUnM0JmU9MTQ5ODA1MzM2NCZ0PTE0OTU0NjEzNjQmcj04MjQxNzM0MjAmdT3igJgzODA3NDI5OTTigJk=",
            "Content-Type: application/json; charset=utf-8",
         ]
        );
        // Create body
        $json_array = [
                                "url" => $myurl,
                                "app_id" => "10083623",
                                "seq" => "57737"
                        ]; 
        $body = json_encode($json_array);
        // Set body
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        // Send the request & save response to $resp
        $resp = curl_exec($ch);
        // Close request to clear up some resources
        curl_close($ch);
        return $resp;
    }
}