<?php
$gateWayUrl      = 'https://ccore.spgateway.com/API/CreditCard';
$MerchantID_     = 'MS3709347';
$PostData_       = '';
$Pos_            = 'String';
$Version         = '1.1';
$TimeStamp       = strtotime(date('Y-m-d h:i'));
$MerchantOrderNo = 1234567890;
$Amt             = 20;
$ProdDesc        = 'This is the product description';
$PayerEmail      = 'mrjesuserwinsuarez@gmail.com';
$CardNo          = 4000221111111111;
$EXP             = 2020;
$CVC             = 333;
$HashKey         = 'YK5drj7GZuYiSgfoPlc24OhHJj5g6I35';
$HashIV          = 't8jUsqArVyJOPZcF';

$mer_array = array (
    'MerchantID' => $MerchantID_,
    'TimeStamp' => $TimeStamp,
    'MerchantOrderNo' => $MerchantOrderNo,
    'Version'=>$Version,
    'Amt' => $Amt,
);

ksort($mer_array);

print "<pre>";

PRINT "<br>url order " . $mer_array . '<br>';

print_r($mer_array );
$check_merstr = http_build_query ($mer_array, '', '&');
$CheckVAlue_str = "HashKey=$HashKey&$check_merstr&HashIV=$HashIV";
$PostData_ = strtoupper(hash("sha256",$CheckVAlue_str));

print "<br> This is the converted values ";
print $PostData_;

$settings = [
//'PostData_'=>$PostData_,
//'MerchantID_' => $MerchantID_,
//'gateWayUrl' => $gateWayUrl,
//'MerchantID' => $MerchantID_,
'PostData' => $PostData_,
//'Pos_' => $Pos_,
//'Version' => $Version,
//'TimeStamp' => $TimeStamp,
//'MerchantOrderNo' => $MerchantOrderNo,
//'Amt' => $Amt,
//'ProdDesc' => $ProdDesc,
//'PayerEmail' => $PayerEmail,
//'CardNo' => $CardNo,
//'EXP' => $EXP,
//'CVC' => $CVC,
//'HashKey' => $HashKey,
//'HashIV' => $HashIV,
];
?>



<Form action='<?php print $gateWayUrl; ?>' method='POST' accept-charset="ISO-8859-1">
    <?php foreach($settings as $key => $value): ?>
        <input type='text' name='<?php print $key;  ?>' value='<?php print $value; ?>' />
    <?php endforeach; ?>
    <Input type='submit' value='Submit' />
</form>





