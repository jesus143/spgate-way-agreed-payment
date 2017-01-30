<?php
$date                = new DateTime();
$hashKey             = 'YK5drj7GZuYiSgfoPlc24OhHJj5g6I35';
$hashIV              = 't8jUsqArVyJOPZcF';
$merchantID          = 'MS3709347';
$timestamp           = $date->getTimestamp();
$cASH_ReturnUrl      = ''; //'http://www.google.com/return-url';
$cASH_NotifyURL      = '';  //'http://www.google.com/notify-url';
$cASH_Client_BackUrl = ''; //'http://www.google.com/back-url';
$Amount              = 20;
$merchantOrderNo     = 12385566788;
$responseStatus      = 'String';
$version             = '1.2';
$itemDesc            = 'This is the item description';
$debug               = false;
$settings = [

    /*
     * 智付寶商店代號
     */

    'HashKey' => $hashKey,

    'Amt' => $Amount,

    'Debug' => $debug,

    /*
     * 智付寶商店代號
     */
    'MerchantID' => $merchantID,


    'MerchantOrderNo' => $merchantOrderNo,



    'TimeStamp' => $date->getTimestamp(),

    /*
     * 回傳格式
     *
     * json | html
     */
    'RespondType' => $responseStatus,

    /*
     * 串接版本
     */
    'Version' => $version,

    /*
     * 語系
     *
     * zh-tw | en
     */
    'LangType' => 'zh-tw',

    /*
     * 是否需要登入智付寶會員
     */
    'LoginType' => true,

    /*
     * 交易秒數限制
     *
     * default: null
     * null: 不限制
     * 秒數下限為 60 秒，當秒數介於 1~59 秒時，會以 60 秒計算
     */
    'TradeLimit' => null,

    /*
     * 繳費-有效天數
     *
     * default: 7
     * maxValue: 180
     */
    'ExpireDays' => 7,

    /*
     * 繳費-有效時間(僅適用超商代碼交易)
     *
     * default: 235959
     * 格式為 date('His') ，例：235959
     */
    'ExpireTime' => '235959',

    /*
     * 付款完成-後導向頁面
     *
     * 僅接受 port 80 or 443
     */
    'ReturnURL' => $cASH_ReturnUrl, //env('CASH_ReturnUrl') != null ? env('CASH_ReturnUrl') : null,

    /*
     * 付款完成-後的通知連結
     *
     * 以幕後方式回傳給商店相關支付結果資料
     * 僅接受 port 80 or 443
     */
    'NotifyURL' => $cASH_NotifyURL, //env('CASH_NotifyURL') != null ? env('APP_URL') . env('CASH_NotifyURL') : null,

    /*
     * 商店取號網址
     *
     * 此參數若為空值，則會顯示取號結果在智付寶頁面。
     * default: null
     */
    'CustomerURL' => null,

    /*
     * 付款取消-返回商店網址
     *
     * default: null
     */
    'ClientBackURL' => $cASH_Client_BackUrl, //env('CASH_Client_BackUrl') != null ? env('APP_URL') . env('CASH_Client_BackUrl') : null,

    /*
     * 付款人電子信箱是否開放修改
     *
     * default: false
     */
    'EmailModify' => false,

    /*
     * 商店備註
     *
     * 1.限制長度為 300 字。
     * 2.若有提供此參數，將會於 MPG 頁面呈現商店備註內容。
     * default: null
     */
    'OrderComment' => '商店備註',

    'StoreOrderN',

    'ItemDesc' => $itemDesc,

    /*
     * 智付寶商店代號
     */
    'HashIV' => $hashIV,
];
$check_arr = array('MerchantID' => $settings['MerchantID'], 'TimeStamp' =>  $settings['TimeStamp'], 'MerchantOrderNo' => $settings['MerchantOrderNo'], 'Version' => $settings['Version'], 'Amt' => $settings['Amt']);
ksort($check_arr);
$check_merstr = http_build_query($check_arr, '', '&');
$checkvalue_str = "HashKey=" . $settings['HashKey'] . "&" . $check_merstr . "&HashIV=" . $settings['HashIV'];
$CheckValue = strtoupper(hash("sha256", $checkvalue_str));
$settings["CheckValue"] = $CheckValue;





function build_http_query_custom( $query ){
    $query_array = array();
    foreach( $query as $key => $key_value ){
        $query_array[] = urlencode( $key ) . '=' . urlencode( $key_value );
    }
    return implode( '&', $query_array );
}
?>
<Form action='https://ccore.spgateway.com/MPG/mpg_gateway' method='POST' accept-charset="ISO-8859-1">
    <?php foreach($settings as $key => $value): ?>
        <input type='text' name='<?php print $key;  ?>' value='<?php print $value; ?>' />
    <?php endforeach; ?>
    <Input type='submit' value='Submit' />
</form>
