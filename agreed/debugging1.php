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
$version             = '1.1';
$itemDesc            = 'This is the item description';
$debug               = false;

require_once("debugging1-settings.php");

print "<pre>";
    print_r($settings);
print "</pre>";


$check_arr = array('MerchantID_' => $settings['MerchantID_'], 'TimeStamp' =>  $settings['TimeStamp'], 'MerchantOrderNo' => $settings['MerchantOrderNo'], 'Version' => $settings['Version'], 'Amt' => $settings['Amt']);
ksort($check_arr);
$check_merstr = http_build_query($check_arr, '', '&');
$checkvalue_str = "HashKey=" . $settings['HashKey'] . "&" . $check_merstr . "&HashIV=" . $settings['HashIV'];
print "<br> url " . $checkvalue_str . ' <br>';
$CheckValue = strtoupper(hash("sha256", $checkvalue_str));
$settings["PostData_"] = $CheckValue;

 ?>


<Form action='https://ccore.spgateway.com/API/CreditCard' method='POST' accept-charset="ISO-8859-1">
    <table>
    <?php foreach($settings as $key => $value): ?>
        <td>
            <label><?php print $key;  ?></label>
        </td>
        <td>
        <input type='text' name='<?php print $key;  ?>' value='<?php print $value; ?>' />
        </td><tr>

    <?php endforeach; ?>
    </table>
    <Input type='submit' value='Submit' />
</form>
