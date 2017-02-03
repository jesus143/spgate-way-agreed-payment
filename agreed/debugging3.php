<?php


$date = new DateTime();

$settings = [
    'HashIV' => 't8jUsqArVyJOPZcF',
    'HashKey' => 'YK5drj7GZuYiSgfoPlc24OhHJj5g6I35',
    'LoginType'=>0,
    "RespondType" => 'String',
    "ItemDesc" => 'This is the description',
    "Email" =>  'mrjesuserwinsuarez@gmail.com',
    "Receiver" => 'Jesus Erwin Suarez',
    "Tel1" => '222-3831',
    "Tel2" => '222-3831',
    'CREDIT' => true,
    'UNIONPAY' => true,
    'WEBATM' => true,
    'VACC'=>true,
    'CVS'=>true,
    'BARCODE'=>true,
    'LangType' => 'TW',
    'CREDITAGREEMENT'=>1,
    'TokenTerm'=>'mrjesuserwinsuarez@gmail.com',

];
$gatewayUrl  =  'https://ccore.spgateway.com/MPG/mpg_gateway';
    $Check_code = array (
        "TimeStamp" => $date->getTimestamp(),
        "MerchantOrderNo" =>$date->getTimestamp(),
        'Version'=>'1.1',
        'Amt' => 200
    );


    Ksort($Check_code);
    $Check_str = http_build_query($Check_code, '', '&');
    PRINT "<br>CHECK VALUE STR " . $Check_str;
    $checkvalue_str = "HashKey=" . $settings['HashKey']  . "&" . $Check_str . "&HashIV=" . $settings['HashIV'];
$CheckValue = strtoupper(hash("sha256", $checkvalue_str));



$settings['CheckValue'] = $CheckValue;



$spgateway_args = array_merge($Check_code, $settings);


  ?>
<pre>

    <?php

        print_r($spgateway_args);

    ?>
</pre>



<form action="<?php print $gatewayUrl; ?>" method="POST" id="spgateway" name="spgateway"  >
    <?php foreach($spgateway_args as $name => $setting): ?>
        <input type="text" value="<?php print htmlspecialchars($setting); ?>" name="<?php print htmlspecialchars($name); ?>" />
    <?php endforeach; ?>

    <input type="submit" value="pay order" />
</form>



