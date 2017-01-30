<?php

$settings = [
    'MerchantID_' =>'MS3709347',
    'PostData_' =>'',
    'Pos_' =>'String',
    'TimeStamp' => $date->getTimestamp(),
    'Version' =>'1.1',
    'MerchantOrderNo' =>201406010002,
    'Amt' =>200,
    'ProdDesc' =>'This is the description of the product',
    'PayerEmail' =>'jesus@suppershortcut.com',
    'CardNo' =>'4000221111111111',
    'EXP' =>'2021',
    'CVC' =>'333',
    'TokenSwitch' =>'',
    'TokenTerm' =>'',
    'TokenLife' =>'',
    'HashKey'=>'YK5drj7GZuYiSgfoPlc24OhHJj5g6I35',
    'HashIV'=>'t8jUsqArVyJOPZcF',
];


//$settings = [

//    /*
//     * 智付寶商店代號
//     */
//
//    'HashKey' => $hashKey,
//
//    'Amt' => $Amount,
//
//    'Debug' => $debug,
//
//    /*
//     * 智付寶商店代號
//     */
//    'MerchantID_' => $merchantID,
//
//
//    'MerchantOrderNo' => $merchantOrderNo,
//
//
//
//    'TimeStamp' => $date->getTimestamp(),
//
//    /*
//     * 回傳格式
//     *
//     * json | html
//     */
//    'Pos_' => $responseStatus,
//
//    /*
//     * 串接版本
//     */
//    'Version' => $version,
//
//    /*
//     * 語系
//     *
//     * zh-tw | en
//     */
//    'LangType' => 'zh-tw',
//
//    /*
//     * 是否需要登入智付寶會員
//     */
//    'LoginType' => true,
//
//    /*
//     * 交易秒數限制
//     *
//     * default: null
//     * null: 不限制
//     * 秒數下限為 60 秒，當秒數介於 1~59 秒時，會以 60 秒計算
//     */
//    'TradeLimit' => null,
//
//    /*
//     * 繳費-有效天數
//     *
//     * default: 7
//     * maxValue: 180
//     */
//    'ExpireDays' => 7,
//
//    /*
//     * 繳費-有效時間(僅適用超商代碼交易)
//     *
//     * default: 235959
//     * 格式為 date('His') ，例：235959
//     */
//    'ExpireTime' => '235959',
//
//    /*
//     * 付款完成-後導向頁面
//     *
//     * 僅接受 port 80 or 443
//     */
//    'ReturnURL' => $cASH_ReturnUrl, //env('CASH_ReturnUrl') != null ? env('CASH_ReturnUrl') : null,
//
//    /*
//     * 付款完成-後的通知連結
//     *
//     * 以幕後方式回傳給商店相關支付結果資料
//     * 僅接受 port 80 or 443
//     */
//    'NotifyURL' => $cASH_NotifyURL, //env('CASH_NotifyURL') != null ? env('APP_URL') . env('CASH_NotifyURL') : null,
//
//    /*
//     * 商店取號網址
//     *
//     * 此參數若為空值，則會顯示取號結果在智付寶頁面。
//     * default: null
//     */
//    'CustomerURL' => null,
//
//    /*
//     * 付款取消-返回商店網址
//     *
//     * default: null
//     */
//    'ClientBackURL' => $cASH_Client_BackUrl, //env('CASH_Client_BackUrl') != null ? env('APP_URL') . env('CASH_Client_BackUrl') : null,
//
//    /*
//     * 付款人電子信箱是否開放修改
//     *
//     * default: false
//     */
//    'EmailModify' => false,
//
//    /*
//     * 商店備註
//     *
//     * 1.限制長度為 300 字。
//     * 2.若有提供此參數，將會於 MPG 頁面呈現商店備註內容。
//     * default: null
//     */
//    'OrderComment' => '商店備註',
//
//    'StoreOrderN',
//
//    'ItemDesc' => $itemDesc,
//
//    /*
//     * 智付寶商店代號
//     */
//    'HashIV' => $hashIV,
//];