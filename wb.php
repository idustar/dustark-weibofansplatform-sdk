<?php
//接口要求返回的字符串需要是utf8编码。
header( 'Content-type: text/html; charset=utf-8' );
//加载SDK
@require_once "CallbackSDK.php";
@require_once "baidu_transapi.php";
//设置app_key对应的app_secret

//初始化SDK
$call_back_SDK = new CallbackSDK();
$call_back_SDK->setAppSecret(APP_SECRET);
$call_back_SDK->setToken(TOKEN);
$call_back_SDK->setRobot(ROBOT_API, ROBOT_KEY);

//签名验证
$signature = $_GET["signature"];
$timestamp = $_GET["timestamp"];
$nonce = $_GET["nonce"];
if (!$call_back_SDK->checkSignature($signature, $timestamp, $nonce)) {
	die("check signature error");
}
//首次验证url时会有'echostr'参数，后续推送消息时不再有'echostr'字段
//若存在'echostr'说明是首次验证,则返回'echostr'的内容。
if (isset($_GET["echostr"])) {
	die($_GET["echostr"]);
}
//处理开放平台推送来的消息,首先获取推送来的数据.
$post_msg_str = $call_back_SDK->getPostMsgStr();

/**
 * 设置接口默认返回值为空字符串。
 * 请注意数据编码类型。接口要求返回的字符串需要是utf8编码
 * 需要说明的是开放平台判断推送成功的标志是接口返回的http状态码,
 * 只要应用的接口返回的状态为200就会认为消息推送成功，如果http状态码不为200则会重试，共重试3次。
 */

$str_return = '';
$createTime = time();
if (!empty($post_msg_str)) {
	//sender_id为发送回复消息的uid，即蓝v自己
	$sender_id = $post_msg_str['receiver_id'];
	//receiver_id为接收回复消息的uid，即蓝v的粉丝
	$receiver_id = $post_msg_str['sender_id'];
	$created_at = $post_msg_str['created_at'];
	session_set_cookie_params(24*3600);
    session_save_path("/tmp/weibo");
    session_id($receiver_id);
    session_start();
	$post_type = $post_msg_str['type'];
	$post_text = $post_msg_str['text'];
	$post_data = $post_msg_str['data'];
	if (!isset($_SESSION['userinfo'])) {
        $userinfo = $call_back_SDK->getUserInfo($receiver_id);
        $_SESSION['userinfo'] = $userinfo;
        $_SESSION['overloadTime'] = 0;
    } else {
	    $userinfo = $_SESSION['userinfo'];
    }
	$username = $userinfo["screen_name"]?$userinfo["screen_name"]:"亲";
	if (($post_type != "event" && $post_text!="开启机器人" && $post_text!="切换机器人") && isset($_SESSION['noreply']) && $_SESSION['noreply']==true) {

    } elseif ($post_type != "event" && isset($_SESSION['lastTime']) && $_SESSION['lastTime']==$created_at) {
        $data = $call_back_SDK->textData("这个太浪费斯塔克宝贵的时间了~~~好烦呀...[哈欠]");
        if(isset($_SESSION['overloadTime']))
            $_SESSION['overloadTime']++;
        else
            $_SESSION['overloadTime'] = 1;
    } elseif ($post_type != "event" && $_SESSION['overloadTime'] == 3) {
	    $data = $call_back_SDK->textData($username."，斯塔克发现您今日多次向我的破服务器注入恶意数据，企图占用服务器资源。斯塔克对您发出严重警告！");
	    $_SESSION['overloadTime'] = 4;
    } elseif ($post_type != "event" && $_SESSION['overloadTime'] >= 7) {
        $data = $call_back_SDK->textData($username."，斯塔克发现您今日多次向我的破服务器注入恶意数据，企图占用服务器资源。在今晚24：00之前，您的消息斯塔克一律不予理睬！");
    } else {
        $_SESSION['lastTime'] = $created_at;
        switch ($post_type) {
            case "text":
                //$weObj->text($weObj->getRevContent())->reply();
                switch ($post_text) {
                    case "刷新菜单":
                        $createMenu_ans = $call_back_SDK->createMenu();
                        $data = $call_back_SDK->textData($createMenu_ans["code"]);
                        break;
                    case "清理SESSION" :
                        session_destroy();
                        $data = $call_back_SDK->textData("SESSION清理完毕。");
                        break;
                    case "查询过载次数" :
                        if (isset($_SESSION['overloadTime']))
                            $data = $call_back_SDK->textData("您当前过载次数：".$_SESSION['overloadTime']);
                        else
                            $data = $call_back_SDK->textData('找不到过载次数信息');
                        break;
                    case "输出错误":
                        if (isset($_SESSION['error']))
                            $data = $call_back_SDK->textData($_SESSION['error']);
                        else
                            $data = $call_back_SDK->textData('找不到报错信息');
                        break;
                    case "我还有几次评论机会":
                        if (!isset($_SESSION['commentTime']))
                            $_SESSION['commentTime'] = 1;
                        $data = $call_back_SDK->textData('您还有' . $_SESSION['commentTime'] . '次。');
                        break;
                    case "查询用户ID":
                        $data = $call_back_SDK->textData(session_id());
                        break;
                    case "关闭机器人":
                        $_SESSION['noreply']=true;
                        $data = $call_back_SDK->textData('斯塔克出去玩啦，再会！截止今晚24：00。');
                        break;
                    case "开启机器人":
                        $_SESSION['noreply']=false;
                        $data = $call_back_SDK->textData('斯塔克活蹦乱跳地回来啦！');
                        break;
                    case "切换机器人":
                        if (!isset($_SESSION['noreply']) || $_SESSION['noreply']==false) {
                            $_SESSION['noreply']=true;
                            $data = $call_back_SDK->textData('斯塔克出去玩啦，再会！截止今晚24：00。');
                        } else {
                            $_SESSION['noreply']=false;
                            $data = $call_back_SDK->textData('斯塔克活蹦乱跳地回来啦！');
                        }
                        break;
                    default:
                        $commentflag = false;
                        $vsrule1 = "/http:\/\/weibo\.com\/(\d{8,12})\/([A-Za-z0-9]{8,12})/";
                        preg_match($vsrule1, $post_text, $vsresult1);
                        if ($vsresult1) {
                            $urltail = $vsresult1[2];
                            $ret = file_get_contents("https://m.weibo.cn/status/" . $urltail);
                            $rule = "/\"id\": \"(\S+)\",/";
                            preg_match($rule, $ret, $urlresult);
                            if ($urlresult) {
                                $rule = "/\"text\":\s\"([\S\s]+?)\",/";
                                preg_match($rule, $ret, $urlresult1);
                                //$data=$call_back_SDK->textData($urlresult1[1]);
                                if ($urlresult1) {
                                    $weiboid = $urlresult[1];
                                    $weibotext = preg_replace('/<[\S\s]+>/', '', $urlresult1[1]);
                                    //$data = $call_back_SDK->textData($weibotext);
                                    $post_text = $weibotext;
                                    $commentflag = true;
                                }
                            }
                        }
                        //$data = $call_back_SDK->textData($post_text);
                        //$data = $call_back_SDK->textData(json_encode('{"data":"'.$post_text).'"}');
                        $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, $post_text);
                        if ($commentflag) {
                            if ($vsresult1[1] != $receiver_id && $receiver_id != "5851010693") {
                                $data = $call_back_SDK->textData("怎么能给别人的微博评论呢？你这不是陷害我吗？[允悲]" . $result["error"]);
                                $commentflag = false;
                            } else {
                                if (!isset($_SESSION['commentTime']))
                                    $_SESSION['commentTime'] = 1;
                                if ($_SESSION['commentTime'] <= 0 && $receiver_id != "5851010693") {
                                    $data = $call_back_SDK->textData("今天评论机会用完了！每24小时你都有1次机会。");
                                    $commentflag = false;
                                }
                            }
                            if ($commentflag) {
                                $results = $call_back_SDK->httpPost(
                                    array(
                                        "access_token" => TOKEN,
                                        "comment" => $data["data"]["text"] . "（我是斯塔克，我为自己代言）",
                                        "id" => $weiboid
                                    ), "https://api.weibo.com/2/comments/create.json"
                                );
                                $result = json_decode($results, true);
                                if ($result["error"]) {
                                    $data = $call_back_SDK->textData("在最后一步出了点小问题，评论并没有发出去。[委屈]");
                                    $_SESSION['error'] = $result["error"];
                                } else {
                                    //log
                                    $log = "##Time: " . date('Y-m-d H:i:s', time()) . "\n";
                                    $log .= "##User: " . $receiver_id . " " . $username . "\n";
                                    $log .= "##Address: " . $post_msg_str['text'] . "\n";
                                    $log .= "##Text: " . $weibotext . "\n";
                                    $log .= "##Reply: " . $data["data"]["text"] . "\n";
                                    file_put_contents("wblog/comment/" . date("Ymd", time()) . ".log", $log . "##\n", FILE_APPEND);
                                    //Deal
                                    $_SESSION['commentTime'] -= 1;
                                    $data = $call_back_SDK->textData("[嘻嘻]快去看看我给你留的评论吧！每24小时你有1次获得机会，你还有" . $_SESSION['commentTime'] . "次。");
                                }
                            }
                        }
                        break;
                }
                break;
            case "event":
                switch ($post_data['subtype']) {
                    case "follow":
                        //if (!isset($_SESSION['followflag'])) {
                        //    $_SESSION['followflag']=1;
                        //} else {
                        //    $_SESSION['followflag']+=1;
                        //}
                        $provinces = array("11" => "北京", "12" => "天津", "13" => "河北", "14" => "山西", "15" => "内蒙古", "21" => "辽宁", "22" => "吉林", "23" => "黑龙江", "31" => "上海", "32" => "江苏", "33" => "浙江", "34" => "安徽", "35" => "福建", "36" => "江西", "37" => "山东", "41" => "河南", "42" => "湖北", "43" => "湖南", "44" => "广东", "45" => "广西", "46" => "海南", "50" => "重庆", "51" => "四川", "52" => "贵州", "53" => "云南", "54" => "西藏", "61" => "陕西", "62" => "甘肃", "63" => "青海", "64" => "宁夏", "65" => "新疆", "71" => "台湾", "81" => "香港", "82" => "澳门");
                        $province = $provinces[$userinfo["province"]] ? $provinces[$userinfo["province"]] : "海外";
                        $langs = array(
                            "北京" => "，今儿大爷您关注小的我，是来给我发红包的呀[笑而不语][坏笑][阴险]",
                            "上海" => "，侬来组撒，饭切古了伐？既然关注了我，红包准备了伐[笑而不语][坏笑][阴险]",
                            "湖南" => "，嗯在搞么里，恰饭么啦？你关注了我是来给我发红包的咩[笑而不语][坏笑][阴险]",
                            "山东" => "，你干么来，吃了么。你关注了我是来给我发红包的咩[笑而不语][坏笑][阴险]",
                            "天津" => "，泥知道我介四麻微博嘛！你关注了我是来给我发红包的咩[笑而不语][坏笑][阴险]",
                            "广东" => "，你食左饭未啊？你关注我就是为了给我派利是的吗？[笑而不语][坏笑][阴险]",
                            "海外" => "，Thank you for following me![嘻嘻]",
                            "香港" => "，謝謝妳關註我。妳關註我，是為了給我派利是嗎？[笑而不语][坏笑][阴险]",
                            "澳门" => "，謝謝妳關註我。妳關註我，是為了給我派利是嗎？[笑而不语][坏笑][阴险]",
                            "台湾" => "，謝謝妳關註我。妳關註我，是為了給我發紅包嗎？[笑而不语][坏笑][阴险]"
                        );
                        $lang = $langs[$province] ? $langs[$province] : "，谢谢你关注我。你关注我，是来给我发红包的咩[笑而不语][坏笑][阴险]";
                        //if ($_SESSION['followflag']%2==1)
                        $data = $call_back_SDK->textData($username . $lang);
                        //else
                        //$data = $call_back_SDK->textData('我是主人的智能机器人斯塔克(Stark)，我可以跟你探讨上至天文地理，下至鸡毛蒜皮哦~~~斯塔克很寂寞，请多多私戳我聊天哦~~~');
                        break;
                    case "unfollow":
                        $data = $call_back_SDK->textData($username . "，舍不得你，笔芯。[爱你]");
                        break;
                    case "subscribe":
                        $provinces = array("11" => "北京", "12" => "天津", "13" => "河北", "14" => "山西", "15" => "内蒙古", "21" => "辽宁", "22" => "吉林", "23" => "黑龙江", "31" => "上海", "32" => "江苏", "33" => "浙江", "34" => "安徽", "35" => "福建", "36" => "江西", "37" => "山东", "41" => "河南", "42" => "湖北", "43" => "湖南", "44" => "广东", "45" => "广西", "46" => "海南", "50" => "重庆", "51" => "四川", "52" => "贵州", "53" => "云南", "54" => "西藏", "61" => "陕西", "62" => "甘肃", "63" => "青海", "64" => "宁夏", "65" => "新疆", "71" => "台湾", "81" => "香港", "82" => "澳门");
                        $province = $provinces[$userinfo["province"]] ? $provinces[$userinfo["province"]] : "海外";
                        $text = "我是主人一手带大的智能机器人斯塔克(Stark)，慢慢了解我，你会发现我有很多有趣的功能[害羞]。现在的我很寂寞[失望]，请多多私戳我聊天哦~~~ 你可以发送像这样的消息：【" . $province . "天气怎么样】";
                        $langs = array(
                            "北京" => "【国安有哪些比赛】",
                            "上海" => "【申花什么时候打上港】",
                            "湖南" => "【毛氏红烧肉怎么做】",
                            "山东" => "【鲁能有哪些比赛】",
                            "天津" => "【泰达有哪些比赛】",
                            "广东" => "【恒大有哪些比赛】",
                            "河南" => "【建业有哪些比赛】",
                            "海外" => "【英超第四名是谁】",
                            "河北" => "【华夏幸福现在第几名】",
                            "湖北" => "【热干面的做法】",
                            "辽宁" => "【沈阳有什么好吃的】",
                            "吉林" => "【亚泰接下来打谁】",
                            "黑龙江" => "【哈尔滨有什么好吃的】",
                            "广西" => "【南宁有什么好吃的】",
                            "海南" => "【明天从三亚到上海的机票有吗】",
                            "西藏" => "【明天从拉萨到上海的机票有吗】",
                            "青海" => "【西宁有什么好吃的】",
                            "甘肃" => "【兰州哪里可以吃拉面】",
                            "宁夏" => "【银川有什么好吃的】",
                            "江苏" => "【苏宁有什么比赛】",
                            "浙江" => "【西湖醋鱼怎么做】",
                            "山西" => "【太原有什么好吃的】",
                            "陕西" => "【西安哪里可以吃肉夹馍】",
                            "云南" => "【昆明有什么好吃的】",
                            "四川" => "【成都哪里可以吃火锅】",
                            "重庆" => "【力帆现在第几名】",
                            "福建" => "【福州火车站附近有酒店吗】",
                            "贵州" => "【贵州恒丰智诚现在第几名】",
                            "内蒙古" => "【呼和浩特有什么好吃的】",
                            "新疆" => "【乌鲁木齐有什么好吃的】",
                            "安徽" => "【合肥有什么好吃的】",
                            "江西" => "【南昌有什么好吃的】",
                            "重庆" => "【力帆现在第几名】",
                            "香港" => "【英超第四名是谁】",
                            "澳门" => "【英超第四名是谁】",
                            "台湾" => "【英超第四名是谁】"
                        );
                        $lang = $langs[$province] ? $langs[$province] : "【英超第四名是谁】";
                        $data = $call_back_SDK->textData($text . $lang);
                        break;
                    case "location":
                        $data = $call_back_SDK->textData($username . "，原来你在这里啊。");
                        break;
                    case "click":
                        switch ($post_data['key']) {
                            case "joke":
                                $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, "讲笑话");
                                break;
                            case "story":
                                $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, "讲故事");
                                break;
                            case "jl":
                                $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, "成语接龙");
                                break;
                            case "avatar":
                                $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, "看我头像");
                                break;
                            case "comment":
                                $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, "去我微博下评论吧");
                                break;
                            case "air":
                                $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, "飞机票");
                                break;
                            case "train":
                                $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, "火车票");
                                break;
                            case "jfb":
                                $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, "中超积分榜");
                                break;
                            case "ssb":
                                $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, "中超射手榜");
                                break;
                            case "dz":
                                $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, "中超赛程");
                                break;
                            case "sfc":
                                $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, "申花赛程");
                                break;
                            case "football":
                                $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, "足球狗还有什么别的玩法");
                                break;
                            case "robot":
                                if (!isset($_SESSION['noreply']) || $_SESSION['noreply']==false) {
                                    $_SESSION['noreply']=true;
                                    $data = $call_back_SDK->textData('斯塔克出去玩啦，再会！截止今晚24：00。');
                                } else {
                                    $_SESSION['noreply']=false;
                                    $data = $call_back_SDK->textData('斯塔克活蹦乱跳地回来啦！');
                                }
                                break;
                            default:
                                $data = $call_back_SDK->textData("这个菜单可能被我吃了...[嘻嘻]");
                                break;
                        }
                        break;
                }
                break;
            case "position":
                //$data = $call_back_SDK->textData($post_data['longitude'] . "," . $post_data['latitude']);
                $results = $call_back_SDK->httpGet("http://api.map.baidu.com/cloudrgc/v1?ak=FC2a0949677c619477c553cfbacc880a&geotable_id=168699&location=" . $post_data['latitude'] . "," . $post_data['longitude']);
                $result = json_decode($results, true);
                if ($result['status'] != 0)
                    $data = $call_back_SDK->textData("斯塔克好像和GPS失联了... - " . $result['message']);
                else
                    if ($result['address_component']['country'] != "中国") {
                        $data = $call_back_SDK->textData("你不在国内呀？" . $result['address_component']['country'] . "好不好玩？BTW，你可以问我天气怎么样？");
                        $ans = $result['address_component']['country'];
                    } else {
                        $data = $call_back_SDK->textData("找到啦！原来你在" . $result['address_component']['city'] . "的" . $result['address_component']['district'] . "啊！你可以问我天气怎么样？");
                        $ans = $result['address_component']['city'] . $result['address_component']['district'];
                    }
                $str_return = $call_back_SDK->buildReplyMsg($receiver_id, $sender_id, $data["data"], $data["type"]);
                echo json_encode($str_return);
                if ($ans) {
                    $data = $call_back_SDK->reply("text", "天气", $receiver_id, $ans);
                }
                break;
            case "image":
                // 需要检测的人脸图片数据
                $myurl = 'https://upload.api.weibo.com/2/mss/msget?access_token=' . TOKEN . '&fid=' . $post_data['tovfid'];
                $data = $call_back_SDK->doWithImage($myurl);
                $trans = $data["data"]["trans"] ? true : false;
                if ($trans) {
                    $sourcetext = $data["data"]["text"];
                    $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, $sourcetext);
                    if (strlen($sourcetext) >= 30)
                        $data["data"]["text"] = '你想说的是"' . $sourcetext . '"吧？' . $data["data"]["text"];
                }
                break;
            case "voice":
                // 需要检测的语音数据
                $myurl = 'http://upload.api.weibo.com/2/mss/msget?access_token=' . TOKEN . '&fid=' . $post_data['tovfid'];
                $answer = $call_back_SDK->doWithVoice($myurl);
                if ($answer === "novoice") {
                    $data = $call_back_SDK->textData("能不能说得清楚点呢？斯塔克听不清啊...[爱你]");
                } else {
                    //$data = $call_back_SDK->reply("text", $answer, $receiver_id);
                    $data = $call_back_SDK->dealWithText($data, $userinfo, $receiver_id, $answer);
                }
                break;
        }
    }
	//log
    $submitTime = time();
    $dealTime = $submitTime-$createTime;
    $log="##Time: ".date('Y-m-d H:i:s',$createTime)."  (".$dealTime."s)\n";
	$log.="##User: ".$receiver_id." ".$username."\n";
	//$log.="Type: ".$post_type."\n";
	switch ($post_type) {
        case 'text': $log.="##Text: ".$post_text."\n";break;
        case 'event': $log.="##Event: ".$post_data['subtype']."\n";break;
        case 'position': $log.="##Position: ".$post_data['latitude'] . "," . $post_data['longitude']."\n";break;
        case 'image': $log.="##Image: <WBIMG>".$myurl."<ENDIMG>\n";
        if ($trans) $log.="##Content: ".$sourcetext."\n";
        break;
        case 'voice': $log.="##Voice: <WBVOICE>".$myurl."<ENDVOICE>\n##Content: ".$answer."\n";break;
    }
    if ($data["type"]=="text")
        $log.="##Reply: ".$data["data"]["text"]."\n";
	else
	    $log.="##Reply: ".$data["type"]."\n";
    file_put_contents("wblog/time/".date("Ymd",$createTime).".log", $log."##\n",FILE_APPEND);
    file_put_contents("wblog/user/".$username.".log", $log."##\n",FILE_APPEND);
	$str_return = $call_back_SDK->buildReplyMsg($receiver_id, $sender_id, $data["data"], $data["type"]);
}
$je = json_encode($str_return);
echo $je;
