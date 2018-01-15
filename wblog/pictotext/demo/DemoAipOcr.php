<?php

// 引入文字识别OCR SDK
require_once '../AipOcr.php';


// 初始化
$aipOcr = new AipOcr(APP_ID, API_KEY, SECRET_KEY);

// 身份证识别
//echo json_encode($aipOcr->idcard(file_get_contents('idcard.jpg'), true), JSON_PRETTY_PRINT);

// 银行卡识别 
// echo json_encode($aipOcr->bankcard(file_get_contents('bankcard.jpg')));

// 通用文字识别(含文字位置信息)
// echo json_encode($aipOcr->general(file_get_contents('general.png')));


// 通用文字识别(不含文字位置信息)
$url = $_GET['url'];
if ($url == "")
    echo '{"error_code":"SDK109","error_msg":"url is empty."}';
else
    echo json_encode($aipOcr->basicGeneral(file_get_contents(urldecode($url))));

// 网图OCR识别
// echo json_encode($aipOcr->webImage(file_get_contents('general.png')));

// 生僻字OCR识别
// echo json_encode($aipOcr->enhancedGeneral(file_get_contents('general.png')));
