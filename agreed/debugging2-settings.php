<?php


$date                = new DateTime();

$settings = [
    'MerchantID_' =>'MS3709347',
    'Pos_' =>'String',
    'CardNo' =>'4000221111111111',
    'EXP' =>'2021',
    'CVC' =>'333',
    'HashKey'=>'YK5drj7GZuYiSgfoPlc24OhHJj5g6I35',
    'HashIV'=>'t8jUsqArVyJOPZcF',
    'GatewayUrl' => 'https://ccore.spgateway.com/API/CreditCard',
];


$input_array = [
    'Version' =>'1.0',
    'ProdDesc' =>'This is the description of the product',
    'Amt' =>200,
    'MerchantOrderNo' =>2014060100012,
    'TimeStamp' => $date->getTimestamp(),
    'PayerEmail' =>'jesus@supershortcut.com',
    'TOkenSwitch' =>'get',
    'TokenTerm' =>'',
    'TokenLife' =>'1912',
    'Card6No' =>'111111',
    'Card4No' =>'111111',
    'Exp' =>'2017',
    'CardNo' =>'4000221111111111',
    'EXP' =>'2021',
    'CVC' =>'333'
];

$Post_data_str = http_build_query ($input_array);
$Post_data = spgateway_encrypt ($settings['HashKey'], $settings['HashIV'], $Post_data_str);
//print $Post_data;
?>


<form action="<?php print $settings['GatewayUrl']; ?>" method="POST" >
    <Input type = 'text' name = 'MerchantID_' vAlue = '<?php print $settings['MerchantID_']; ?>' /> <br>
    <Input type = 'text' name = 'Pos_' value = '<?php print $settings['Pos_']; ?>' /> <br>
    <Input type = 'text' name = 'PostData_' value = '<?php print $Post_data; ?>' /> <br>
    <input type = 'submit' value = ' Go to authorize '>
</form>

<?php
Function spgateway_encrypt($key="", $iv="", $str="")
{
    $str = trim(bin2hex (mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$key,addpadding($str),MCRYPT_MODE_CBC,$iv)));
    Return  $str;
}
Function addpadding ($string, $blocksize=32)
{
    $len = strlen($string);
    $pad = $blocksize - ($len % $blocksize);
    $string .= Str_repeat(chr($pad), $pad);
    Return $string;
}