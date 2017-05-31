<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" /> <!-- 优先使用 IE 最新版本和 Chrome -->
    <meta name ="viewport" content ="initial-scale=1, maximum-scale=3, minimum-scale=1, user-scalable=no"> <!-- `width=device-width` 会导致 iPhone 5 添加到主屏后以 WebApp 全屏模式打开页面时出现黑边 http://bigc.at/ios-webapp-viewport-meta.orz -->
    <title>Robot Stark Log</title>

    <style>
        html {
            max-width: 100%;
            font-size: 14px;
            word-break:break-all;
            font-family: "Helvetica Neue", Helvetica, Arial, "PingFang SC", "Hiragino Sans GB", "Heiti SC", "Microsoft YaHei", "WenQuanYi Micro Hei", sans-serif;
        }
        p {

        }
        .nav{width:100%;height:90px;position:fixed;top:0;left:0;padding-left:20px;background: white;}
        h2{line-height: 20px;height:20px;}
        #txtHint{padding-top:80px;}
        img{
            height: 400px;
        }
        .im {
            width: 80%;
            max-width: 30px;
            height: 30px;
        }
    </style>
    <script type="text/javascript">
        var xmlhttp;
        var now;
        var htmlsource;
        function loadXMLDoc(url)
        {
            now = url;
            xmlhttp=null;
            if (window.XMLHttpRequest)
            {// code for Firefox, Opera, IE7, etc.
                xmlhttp=new XMLHttpRequest();
            }
            else if (window.ActiveXObject)
            {// code for IE6, IE5
                xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
            }
            if (xmlhttp!=null)
            {
                xmlhttp.onreadystatechange=state_Change;
                xmlhttp.open("GET",url,true);
                xmlhttp.send(null);
            }
            else
            {
                alert("Your browser does not support XMLHTTP.");
            }
            //$('html,body').animate({scrollTop:$('.bottom').offset().top}, 800);
        }
        //toolTip("<img src='$1'></img>");
        function state_Change()
        {
            if (xmlhttp.readyState==4)
            {// 4 = "loaded"
                if (xmlhttp.status==200)
                {// 200 = "OK"
                    htmlsource = xmlhttp.responseText
                    document.getElementById('txtHint').innerHTML=htmlsource
                    document.getElementById('txtHint').innerHTML=htmlsource.replace(/\#\#/g,'<br>').replace(/<WBIMG>(.+)<ENDIMG>/g,
                        '<img class="im" src="$1" onMouseOver="toolTip(\'<img src=$1></img>\')" onMouseOut="toolTip()"></img> <a href="$1">预览</a>').replace(/<WBVOICE>(.+)<ENDVOICE>/g, '<a href=$1>预览</a>')
                    scrollBy(0,document.body.scrollHeight)
                }
                else
                {
                    alert("Problem retrieving data:" + xmlhttp.statusText);
                }
            }
        }

        function changeselect(value) {
            if (value==="time") {
                var myselect = document.getElementById("time");
                var index = myselect.selectedIndex;
                var init = myselect.options[index].value;
                if (index!=0) loadXMLDoc(init);
                document.getElementById("time").style.display = "inline";
            }
            else
                document.getElementById("time").style.display="none";
            if (value==="user") {
                var myselect = document.getElementById("user");
                var index = myselect.selectedIndex;
                var init = myselect.options[index].value;
                if (index!=0) loadXMLDoc(init);
                document.getElementById("user").style.display = "inline";
            }
            else
                document.getElementById("user").style.display="none";
            if (value==="comment") {
                var myselect = document.getElementById("comment");
                var index = myselect.selectedIndex;
                var init = myselect.options[index].value;
                if (index!=0) loadXMLDoc(init);
                document.getElementById("comment").style.display = "inline";
            }
            else
                document.getElementById("comment").style.display="none";
        }

    </script>

</head>
<body>
    <nav class="nav">
    <h2>Stark Robot Log Reader</h2>

    <?php
    $hostdir_time=dirname(__FILE__)."/wblog/time";
    $hostdir_user=dirname(__FILE__)."/wblog/user";
    $hostdir_comment=dirname(__FILE__)."/wblog/comment";
    //获取本文件目录的文件夹地址
    $filesnames_time = scandir($hostdir_time);
    $filesnames_user = scandir($hostdir_user);
    $filesnames_comment = scandir($hostdir_comment);
    //获取也就是扫描文件夹内的文件及文件夹名存入数组 $filesnames
    ?>

        <select id="choose" name="choose" onchange="changeselect(this.value)">
            <option disabled>类型</option>
            <option selected value="time">时间</option>
            <option value="user">用户</option>
            <option value="comment">评论</option>
        </select>
        <select id="time" name="time" onchange="loadXMLDoc(this.value)">
            <?php
            //print_r ($filesnames);
            echo "<option disabled>时间</option>";
            foreach ($filesnames_time as $name) {
            //echo $name;
                if (preg_match('/.log$/',$name)) {
                    $url="http://dustark.cn/wblog/time/".$name;
                    $aurl= "<option selected value=\"".$url."\">".substr($name,0, strlen($name)-4)."</option>";
                    echo $aurl;
                }
            }
            ?>
        </select>
        <select id="user" name="user" style="display:none" onchange="loadXMLDoc(this.value)">
            <?php
            echo "<option disabled>用户</option>";
            foreach ($filesnames_user as $name) {
            //echo $name;
                if (preg_match('/.log$/',$name)) {
                    $url="http://dustark.cn/wblog/user/".$name;
                    $aurl= "<option selected value=\"".$url."\">".substr($name,0, strlen($name)-4)."</option>";
                    echo $aurl;
                }
            }
            ?>
        </select>
        <select id="comment" name="comment" style="display:none" onchange="loadXMLDoc(this.value)">
            <?php
            echo "<option disabled>评论</option>";
            foreach ($filesnames_comment as $name) {
            //echo $name;
                if (preg_match('/.log$/',$name)) {
                    $url="http://dustark.cn/wblog/comment/".$name;
                    $aurl= "<option selected value=\"".$url."\">".substr($name,0, strlen($name)-4)."</option>";
                    echo $aurl;
                }
            }
            ?>
        </select>
    <button onclick="loadXMLDoc(now)">刷新</button>
    </nav>
    <p>
        <div id="txtHint"><b>请选择您要打开的LOG文件</b></div>
    </p>
    <div class="box bottom"></div>

</body>
<script type="text/javascript" src="ToolTip.js"></script>
<script>
//    document.ready(function($){
//        changeselect('time')
//    });
    window.onload=function() {
        changeselect('time')
    }

</script>
</html>