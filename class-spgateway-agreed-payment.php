<?php

// print "<H1> tESTING PLUGIN </H1>";
/**
 * spgateway Payment Gateway
 * Plugin URI: http://www.spgateway.com/
 * Description: spgateway 收款模組
 * Version: 1.0.0
 * Author URI: http://www.spgateway.com/
 * Author: 智付通 spgateway
 * Plugin Name:   Spgateway Agreed Payment
 * @class 		spgateway
 * @extends		WC_Payment_Gateway
 * @version
 * @author 	Pya2go Libby
 * @author 	Pya2go Chael
 * @author  Spgateway Geoff
 */


/**
 * Requirements:
 * This plugin require a template for the response, currently template located at
 * spgateway-manage-response plugin name and you can just copy the template file there and add to theme folder
 * current active then u need to create a page name "spgateway payment response" and url link should like this
 * http://demo4.iamrockylin.com/spgateway-payment-response/ after the product purchase here spgateway will redirect
 */



//print "<br> <br><br><br><br><br> This is the path of the files path " . ABSPATH;
//print "<pre>";
//print_r($_SERVER);
//print "</pre>";
//exit;

require_once(ABSPATH . "/wp-includes/user.php");
require_once(ABSPATH . "/wp-includes/pluggable.php");
require_once(ABSPATH . "/wp-content/plugins/spgate-way-agreed-payment/helper.php" );



//exit;

add_action('plugins_loaded', 'spgateway_gateway_agreed_init', 0);

function spgateway_gateway_agreed_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_spgateway_agreed_payment extends WC_Payment_Gateway
    {

        /**
         * Constructor for the gateway.
         *
         * @access public
         * @return void
         */
        public function __construct()
        {
            // Check ExpireDate is validate or not
            if (isset($_POST['woocommerce_spgateway_agreed_payment_ExpireDate']) && (!preg_match('/^\d*$/', $_POST['woocommerce_spgateway_agreed_payment_ExpireDate']) || $_POST['woocommerce_spgateway_agreed_payment_ExpireDate'] < 1 || $_POST['woocommerce_spgateway_agreed_payment_ExpireDate'] > 180)) {
                $_POST['woocommerce_spgateway_agreed_payment_ExpireDate'] = 7;
            }

            $this->id = 'spgateway_agreed_payment';
            $this->icon = apply_filters('woocommerce_spgateway_agreed_payment_icon', plugins_url('icon/spgateway.png', __FILE__));
            $this->has_fields = false;
            $this->method_title = __('Spgateway Agreed Payment', 'woocommerce');

            // Load the form fields.
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();

            // Define user set variables
            $this->title = $this->settings['title'];
            $this->LangType = $this->settings['LangType'];
            $this->description = $this->settings['description'];
            $this->MerchantID = trim($this->settings['MerchantID']);
            $this->HashKey = trim($this->settings['HashKey']);
            $this->HashIV = trim($this->settings['HashIV']);
            $this->ExpireDate = $this->settings['ExpireDate'];
            $this->TestMode = $this->settings['TestMode'];
            $this->notify_url = add_query_arg('wc-api', 'WC_spgateway_agreed_payment', home_url('/')) . '&callback=return';


            // Test Mode
            if ($this->TestMode == 'yes') {
                $this->gateway = "https://ccore.spgateway.com/MPG/mpg_gateway"; //測試網址
            } else {
                $this->gateway = "https://core.spgateway.com/MPG/mpg_gateway"; //正式網址
            }

            // Actions
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
            add_action('woocommerce_api_wc_' . $this->id, array($this, 'receive_response')); //api_"class名稱(小寫)"
            add_action('woocommerce_checkout_update_order_meta', array($this, 'electronic_invoice_fields_update_order_meta'));

        }


        /**
         * Initialise Gateway Settings Form Fields
         *
         * @access public
         * @return void
         * 後台欄位設置
         */
        function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('啟用/關閉', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('啟動 Spgateway 收款模組', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('標題', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('客戶在結帳時所看到的標題', 'woocommerce'),
                    'default' => __('Spgateway Agreed Payment', 'woocommerce')
                ),
                'LangType' => array(
                    'title' => __('支付頁語系', 'woocommerce'),
                    'type' => 'select',
                    'options' => array(
                        'en' => 'En',
                        'zh-tw' => '中文'
                    )
                ),
                'description' => array(
                    'title' => __('客戶訊息', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('', 'woocommerce'),
                    'default' => __('透過 Spgateway 付款。<br>會連結到 Spgateway 頁面。', 'woocommerce')
                ),
                'MerchantID' => array(
                    'title' => __('Merchant ID', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('請填入您Spgateway商店代號', 'woocommerce')
                ),
                'HashKey' => array(
                    'title' => __('Hash Key', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('請填入您Spgateway的HashKey', 'woocommerce')
                ),
                'HashIV' => array(
                    'title' => __('Hash IV', 'woocommerce'),
                    'type' => 'text',
                    'description' => __("請填入您Spgateway的HashIV", 'woocommerce')
                ),
                'ExpireDate' => array(
                    'title' => __('繳費有效期限(天)', 'woocommerce'),
                    'type' => 'text',
                    'description' => __("請設定繳費有效期限(1~180天), 預設為7天", 'woocommerce'),
                    'default' => 7
                ),
                'TestMode' => array(
                    'title' => __('測試模組', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('啟動測試模組', 'woocommerce'),
                    'default' => 'yes'
                )
            );
        }

        /**
         * Admin Panel Options
         * - Options for bits like 'title' and availability on a country-by-country basis
         *
         * @access public
         * @return void
         */
        public function admin_options() {

            ?>
            <h3><?php _e('智付通 spgateway 收款模組', 'woocommerce'); ?></h3>
            <p><?php _e('此模組可以讓您使用智付通的spgateway收款功能', 'woocommerce'); ?></p>
            <table class="form-table">
                <?php
                // Generate the HTML For the settings form.
                $this->generate_settings_html();
                ?>
                <script>
                    var invalidate = function(){
                            jQuery(this).css('border-color', 'red');
                            jQuery('#'+this.id+'_error_msg').show();
                            jQuery('input[type="submit"]').prop('disabled', 'disabled');
                        },
                        validate = function(){
                            jQuery(this).css('border-color', '');
                            jQuery('#'+this.id+'_error_msg').hide();
                            jQuery('input[type="submit"]').prop('disabled', '');
                        }

                    jQuery('#woocommerce_spgateway_ExpireDate')
                        .bind('keypress', function(e){
                            if(e.charCode < 48 || e.charCode > 57){
                                return false;
                            }
                        })
                        .bind('blur', function(e){
                            if(!this.value){
                                validate.call(this);
                            }
                        });

                    jQuery('#woocommerce_spgateway_ExpireDate')
                        .bind('input', function(e){
                            if(!this.value){
                                validate.call(this);
                                return false;
                            }

                            if(this.value < 1 || this.value > 180){
                                invalidate.call(this);
                            } else {
                                validate.call(this);
                            }
                        })
                        .bind('blur', function(e){
                            if(!this.value){
                                this.value = 7;
                                validate.call(this);
                            }
                        })
                        .after('<span style="display: none;color: red;" id="woocommerce_spgateway_ExpireDate_error_msg">請輸入範圍內1~180的數字</span>')
                </script>
            </table><!--/.form-table-->
            <?php
        }


        /**
         * Get spgateway Args for passing to spgateway
         *
         * @access public
         * @param mixed $order
         * @return array
         *
         * MPG參數格式
         */
        function get_spgateway_args($order) {

            global $woocommerce;

            $merchantid = $this->MerchantID; //商店代號
            $respondtype = "String"; //回傳格式
            $timestamp = time(); //時間戳記
            $version = "1.1"; //串接版本
            $order_id = $order->id;
            $amt = $order->get_total(); //訂單總金額
            $logintype = "0"; //0:不需登入智付通會員，1:須登入智付通會員
            //商品資訊
            $item_name = $order->get_items();
            $item_cnt = 1;
            $itemdesc = "";
            foreach ($item_name as $item_value) {
                if ($item_cnt != count($item_name)) {
                    $itemdesc .= $item_value['name'] . " × " . $item_value['qty'] . "，";
                } elseif ($item_cnt == count($item_name)) {
                    $itemdesc .= $item_value['name'] . " × " . $item_value['qty'];
                }

                //支付寶、財富通參數
                $spgateway_args_1["Count"] = $item_cnt;
                $spgateway_args_1["Pid$item_cnt"] = $item_value['product_id'];
                $spgateway_args_1["Title$item_cnt"] = $item_value['name'];
                $spgateway_args_1["Desc$item_cnt"] = $item_value['name'];
                $spgateway_args_1["Price$item_cnt"] = $item_value['line_subtotal'] / $item_value['qty'];
                $spgateway_args_1["Qty$item_cnt"] = $item_value['qty'];

                $item_cnt++;
            }

            //CheckValue 串接
            $check_arr = array('MerchantID' => $merchantid, 'TimeStamp' => $timestamp, 'MerchantOrderNo' => $order_id, 'Version' => $version, 'Amt' => $amt);

//           print "<pre>";
//            print_r($check_arr);
//            print "</pre>";
            //按陣列的key做升幕排序
            ksort($check_arr);
            //排序後排列組合成網址列格式
            $check_merstr = http_build_query($check_arr, '', '&');
            $checkvalue_str = "HashKey=" . $this->HashKey . "&" . $check_merstr . "&HashIV=" . $this->HashIV;
            $CheckValue = strtoupper(hash("sha256", $checkvalue_str));

            $buyer_name = $order->billing_last_name . $order->billing_first_name;
            $total_fee = $order->order_total;
            $tel = $order->billing_phone;
            $spgateway_args_2 = array(
                "HashKey"=>$this->HashKey,
                "HashIV"=>$this->HashIV,
                "MerchantID" => $merchantid,
                "RespondType" => $respondtype,
                "CheckValue" => $CheckValue,
                "TimeStamp" => $timestamp,
                "Version" => $version,
                "MerchantOrderNo" => $order_id,
                "Amt" => $amt,
                "ItemDesc" => $itemdesc,
                "ExpireDate" => date('Ymd', time()+intval($this->ExpireDate)*24*60*60),
                "Email" => $order->billing_email,
                "LoginType" => $logintype,
                "NotifyURL" => $this->notify_url, //幕後
                "ReturnURL" => $this->get_return_url($order), //幕前(線上)
                "ClientBackURL" => $this->get_return_url($order), //取消交易
                "CustomerURL" => $this->get_return_url($order), //幕前(線下)
                "Receiver" => $buyer_name, //支付寶、財富通參數
                "Tel1" => $tel, //支付寶、財富通參數
                "Tel2" => $tel, //支付寶、財富通參數
                "LangType" => $this->LangType,
                'CREDIT' => true,
                'UNIONPAY' => false,
                'WEBATM' => false,
                'VACC'=>false,
                'CVS'=>false,
                'BARCODE'=>false,
                'CREDITAGREEMENT'=>1,
                "TokenTerm" => $order->billing_email,

            );

            $spgateway_args = array_merge($spgateway_args_1, $spgateway_args_2);
            $spgateway_args = apply_filters('woocommerce_spgateway_args', $spgateway_args);
            return $spgateway_args;
        }

        function agreed_spgateway_encrypt($key="", $iv="", $str="")
        {
            $str = trim(bin2hex (mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$key,$this->agreed_addpadding($str),MCRYPT_MODE_CBC,$iv)));
            Return  $str;
        }

        function agreed_addpadding ($string, $blocksize=32)
        {
            $len = strlen($string);
            $pad = $blocksize - ($len % $blocksize);
            $string .= Str_repeat(chr($pad), $pad);
            Return $string;
        }

        function get_current_full_url(){
            $url =  "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
            return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
        }


        protected function isCreditCardCodes($creditCardInfo) {
            if(empty($creditCardInfo)) {
                return '<div class="alert alert-danger" style="color:white" >Field  required</div>';
            } else if (strlen($creditCardInfo) <> 16) {
                return '<div  class="alert alert-danger" style="color:white" >Code must be 16 characters</div>';
            } else {
                return '';
            }
        }

        protected function isCVV($cvv) {
            if(empty($cvv)) {
                return '<div class="alert alert-danger" style="color:white" >Field  required</div>';
            } else if (strlen($cvv) <> 3) {
                return '<div class="alert alert-danger" style="color:white" >Code must be 3 characters</div>';
            } else {
                return '';
            }
        }

        protected function isEmpty($value)
        {
            if(empty($value)) {
                return '<div class="alert alert-danger" style="color:white" >Field  required</div>';
            } else {
                return '';
            }
        }


        function process_spgateway_credit_card_post_and_order($spgateway_args, $order_id) {

            ?>

            <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>


            <?php



            $date                = new DateTime();



            $card_number                 = str_replace(" ", "", $_POST['card_number']);
            $card_code_cvv               = $_POST['card_code'];
            $spgateway_agreed_month_exp  = $_POST['spgateway_agreed_month'];
            $spgateway_agreed_year_exp   = $_POST['spgateway_agreed_year'];
            $MerchantOrderNo             = $date->getTimestamp();



            $MerchantID_ = $spgateway_args['MerchantID'];
            $ItemDesc= $spgateway_args['ItemDesc'];
            $Email = $spgateway_args['Email'];
            $Count = $spgateway_args['Count'];
            $Pos_ =  'JSON';
            $totalPayable = 0;
            $Card6No =  substr($card_number, 0, 6);
            $Card4No =  substr($card_number, strlen($card_number)-3, strlen($card_number));
            $TokenLife = '2021';
            $TOkenSwitch = 'get';
            $Version = '1.0';

            //get total order payable

            for($i=1; $i<= $Count; $i++) {
                $totalPayable += $spgateway_args['Price' . $i];
            }



            $settings = [
                'MerchantID_' =>$MerchantID_,
                'Pos_' =>$Pos_,
                'CardNo' =>$card_number, //'4000221111111111',
                'EXP' =>$spgateway_agreed_year_exp,//'2021',
                'CVC' =>$card_code_cvv,//'333',
                'HashKey'=>$this->HashKey, //'YK5drj7GZuYiSgfoPlc24OhHJj5g6I35',
                'HashIV'=>$this->HashIV,
                'GatewayUrl' => $this->gateway, //'https://ccore.spgateway.com/API/CreditCard',
                'NotifyURL'=>'http://www.google.com/response',
                'ReturnURL'=>'http://www.google.com/response',
                'ClientBackURL'=>'http://www.google.com/response',
                'CustomerURL'=>'http://www.google.com/response'
            ];


            $input_array = [
                'Version' =>$Version,
                'ProdDesc' =>$ItemDesc,
                'Amt' =>$totalPayable,
                'MerchantOrderNo' =>$MerchantOrderNo,
                'TimeStamp' => $date->getTimestamp(),
                'PayerEmail' =>$Email,
                'TOkenSwitch' =>$TOkenSwitch,
                'TokenTerm' =>$Email,
                'TokenLife' =>$TokenLife,
                'Card6No' => $Card6No,
                'Card4No' => $Card4No,
                'EXP' =>$spgateway_agreed_year_exp,
                'CardNo' =>$card_number,
                'CVC' =>$card_code_cvv,
                'NotifyURL'=>'http://www.google.com/response',
                'ReturnURL'=>'http://www.google.com/response',
                'ClientBackURL'=>'http://www.google.com/response',
                'CustomerURL'=>'http://www.google.com/response'
            ];



            $Post_data_str = http_build_query ($input_array);
            $Post_data = $this->agreed_spgateway_encrypt ($settings['HashKey'], $settings['HashIV'], $Post_data_str);
            ?>


                <form action="<?php print $settings['GatewayUrl']; ?>" method="POST" id="spgateway-agreed-payment" name="spgateway-agreed-payment" >
                    <Input type = 'text' name = 'MerchantID_' vAlue = '<?php print $settings['MerchantID_']; ?>' /> <br>
                    <Input type = 'text' name = 'Pos_' value = '<?php print $settings['Pos_']; ?>' /> <br>
                    <Input type = 'text' name = 'PostData_' value = '<?php print $Post_data; ?>' /> <br>
                    <input type = 'submit' value = ' Go to authorize '>
                </form>
                <script>



//                    $(document).ready(function(){
//                       alert("tes");
                        // Assign handlers immediately after making the request,
                        // and remember the jqxhr object for this request

//                        $.post( '<?php //print $settings['GatewayUrl']; ?>//', $( "#spgateway-agreed-payment" ).serialize(), function( data ) {
//                                alert("loadedd");
//                        }, "json");

//                    });
                        setTimeout(
                            function(){
                                document.forms['spgateway-agreed-payment'].submit()
                            },5000
                        );
                </script>

            <?php

//            print "<pre>";
//
//            print_r($input_array);
//            print_r($settings);
//
//            print "</pre>";
//
//            print "<pre>";
//            print_r($spgateway_args);
//            print_r($_POST);
//
//            print "<br> generated results<br>";
//            print "</pre>";





        }

        function generate_spgateway_form_card($order, $customerInfo)
        {

            $CreditCardType = (!empty($_POST['CreditCardType'])) ? $_POST['CreditCardType'] : null;
            $card_number    = (!empty($_POST['card_number'])) ? $_POST['card_number'] : null;
            $card_code      = (!empty($_POST['card_code'])) ? $_POST['card_code'] : null;
            $spgateway_agreed_month      = (!empty($_POST['spgateway_agreed_month'])) ? $_POST['spgateway_agreed_month'] : null;
            $spgateway_agreed_year      = (!empty($_POST['spgateway_agreed_year'])) ? $_POST['spgateway_agreed_year'] : null;


            //            print $_POST['spgateway_agreed_year'];
            $months  = [
                '01','02','03','04','05','06','07','08','09','10','11','12'
            ];

            $year  = [
                17=>'2017', 18=>'2018', 19=>'2019', 20=>'2020', 21=>'2021', 22=>'2022', 23=>'2023'
            ];
            //        print "year" . $spgateway_agreed_year;

            ?>

            <!-- Latest compiled and minified CSS -->
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

            <!-- Optional theme -->
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

            <!-- Latest compiled and minified JavaScript -->
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>



            <form action="<?php print $this->get_current_full_url(); ?>" method="post" >
                <div class="container wrapper">
                    <div class="row cart-head">
                        <div class="container pull-left" style="width: 50%;">
                            <div class="panel panel-info">
                                <div class="panel-heading"><span><i class="glyphicon glyphicon-lock"></i></span> Secure Payment</div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <div class="col-md-12"><strong>Card Type:</strong></div>
                                        <div class="col-md-12">
                                            <select id="CreditCardType" name="CreditCardType" class="form-control">
                                                <option value="5">Credit Card</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-12"><strong>Credit Card Number:</strong></div>
                                        <div class="col-md-12"><input value="<?php print $card_number; ?>" type="number"  class="form-control" name="card_number"   required></div>
                                        <div class="col-md-12">  <?php print $this->isCreditCardCodes($_POST['card_number']) ?></div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-12"><strong>Card CVV:</strong></div>
                                        <div class="col-md-12">
                                            <input type="number" value="<?php print $card_code; ?>"  class="form-control" name="card_code"    required>
                                            <?php print $this->isCVV($_POST['card_code']) ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-12">
                                            <strong>Expiration Date</strong>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            <select class="form-control" name="spgateway_agreed_month">
                                                <option value="">Month</option>
                                                <?php foreach($months as $month): ?>

                                                    <option value="<?php print $month; ?>" <?php print ($spgateway_agreed_month == $month) ? 'selected' : null;  ?> ><?php print $month; ?></option>

                                                <?php endforeach; ?>

                                            </select>

                                            <?php print $this->isEmpty($_POST['spgateway_agreed_month']) ?>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            <select class="form-control" name="spgateway_agreed_year" required>
                                                <option value="">Year</option>
                                                <?php foreach($year as $key => $y): ?>
                                                    <option value="<?php print $key; ?>" <?php print ($spgateway_agreed_year == $key) ? 'selected' : null;  ?> ><?php print $y; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php print $this->isEmpty($_POST['spgateway_agreed_year']) ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-12" style="padding-top:10px;">
                                            <span>Pay secure using your credit card.</span>
                                        </div>
                                        <div class="col-md-12" style="padding-top:10px;" >
                                            <ul class="cards">
                                                <li class="visa hand">Visa</li>
                                                <li class="mastercard hand">MasterCard</li>
                                                <li class="amex hand">Amex</li>
                                            </ul>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-6 col-sm-6 col-xs-12"><br>
                                            <button type="submit" class="btn btn-primary btn-submit-fix">Pay Now</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row cart-footer">

                    </div>
                </div>
            </form>

            <style>

                .container {
                }

                /* images*/
                ol, ul {
                    list-style: none;
                }
                .hand {
                    cursor: pointer;
                    cursor: pointer;
                }
                .cards{
                    padding-left:0;
                }
                .cards li {
                    -webkit-transition: all .2s;
                    -moz-transition: all .2s;
                    -ms-transition: all .2s;
                    -o-transition: all .2s;
                    transition: all .2s;
                    background-image: url('//c2.staticflickr.com/4/3713/20116660060_f1e51a5248_m.jpg');
                    background-position: 0 0;
                    float: left;
                    height: 32px;
                    margin-right: 8px;
                    text-indent: -9999px;
                    width: 51px;
                }
                .cards .mastercard {
                    background-position: -51px 0;
                }
                .cards li {
                    -webkit-transition: all .2s;
                    -moz-transition: all .2s;
                    -ms-transition: all .2s;
                    -o-transition: all .2s;
                    transition: all .2s;
                    background-image: url('//c2.staticflickr.com/4/3713/20116660060_f1e51a5248_m.jpg');
                    background-position: 0 0;
                    float: left;
                    height: 32px;
                    margin-right: 8px;
                    text-indent: -9999px;
                    width: 51px;
                }
                .cards .amex {
                    background-position: -102px 0;
                }
                .cards li {
                    -webkit-transition: all .2s;
                    -moz-transition: all .2s;
                    -ms-transition: all .2s;
                    -o-transition: all .2s;
                    transition: all .2s;
                    background-image: url('//c2.staticflickr.com/4/3713/20116660060_f1e51a5248_m.jpg');
                    background-position: 0 0;
                    float: left;
                    height: 32px;
                    margin-right: 8px;
                    text-indent: -9999px;
                    width: 51px;
                }
                .cards li:last-child {
                    margin-right: 0;
                }
                /* images end */



                /*
                 * BOOTSTRAP
                 */

                .panel-footer{
                    background:#fff;
                }
                .btn{
                    border-radius: 1px;
                }
                .btn-sm, .btn-group-sm > .btn{
                    border-radius: 1px;
                }
                .input-sm, .form-horizontal .form-group-sm .form-control{
                    border-radius: 1px;
                }

                .panel-info {
                    border-color: #999;
                }

                .panel-heading {
                    border-top-left-radius: 1px;
                    border-top-right-radius: 1px;
                }
                .panel {
                    border-radius: 1px;
                }
                .panel-info > .panel-heading {
                    color: #eee;
                    border-color: #999;
                }
                .panel-info > .panel-heading {
                    background-image: linear-gradient(to bottom, #555 0px, #888 100%);
                }

                hr {
                    border-color: #999 -moz-use-text-color -moz-use-text-color;
                }

                .panel-footer {
                    border-bottom-left-radius: 1px;
                    border-bottom-right-radius: 1px;
                    border-top: 1px solid #999;
                }

                .btn-link {
                    color: #888;
                }

                hr{
                    margin-bottom: 10px;
                    margin-top: 10px;
                }

                /** MEDIA QUERIES **/
                @media only screen and (max-width: 989px){
                    .span1{
                        margin-bottom: 15px;
                        clear:both;
                    }
                }

                @media only screen and (max-width: 764px){
                    .inverse-1{
                        float:right;
                    }
                }

                @media only screen and (max-width: 586px){
                    .cart-titles{
                        display:none;
                    }
                    .panel {
                        margin-bottom: 1px;
                    }
                }

                .form-control {
                    border-radius: 1px;
                }

                @media only screen and (max-width: 486px){


                }
            </style>

            <?php
        }


        /**
         * Generate the spgateway button link (POST method)
         *
         * @access public
         * @param mixed $order_id
         * @return string
         */
        function generate_spgateway_form($order_id) {

            global $woocommerce;
            $order = new WC_Order($order_id);
            $spgateway_args = $this->get_spgateway_args($order);
            $item_name = $order->get_items();
            $sendRightKeyWord = 'sendright';
            $name = '';
            $items = $order->get_product_from_item( $item_name );

            $spgateway_args['ReturnURL'] = '';

            // Create new wp user if not exist
            $_SESSION['new_user']['user_id'] =  spgateway_createNewWpUser($order_id);

            // Assign member to a wishlist membership level
            spgateway_acc_assignment_to_membership_level(get_user_by( 'email', spgateway_acc_get_customer_info($order_id)['email'] )->data->ID, $spgateway_args['Pid1'] );

            //            exit;
            //$spgateway_args['ReturnURL'] = spgateway_set_return_url(['itemName'=>$item_name, 'sendRightKeyWord'=>$sendRightKeyWord, 'orderId'=>$order_id]);

                        // create user's account
            //            $customerInfo = spgateway_get_customer_info($order_id);
            //            $status = spgateway_createNewWpUser( [
            //                'first_name'=>$customerInfo['firstName'],
            //                'last_name'=> $customerInfo['lastName'],
            //                'user_email'=>$customerInfo['email'],
            //                'user_login' =>$customerInfo['email'],
            //                'display_name'=>$customerInfo['firstName'] . ' ' . $customerInfo['lastName']
            //            ]);


            // Start Ui

            //            $this->generate_spgateway_form_card($order, $customerInfo);


            //            if(
            //                $this->isCreditCardCodes($_POST['card_number']) == null and
            //                $this->isCVV($_POST['card_code']) == null and
            //                $this->isEmpty($_POST['spgateway_agreed_month']) == null and
            //                $this->isEmpty($_POST['spgateway_agreed_year']) == null
            //            ) {
            //                $this->process_spgateway_credit_card_post_and_order($spgateway_args, $order_id);
            //            }


            // End Ui


            $_SESSION['spgateway_args'] = $spgateway_args;


            $spgateway_args['ReturnURL'] = get_site_url() . '/spgateway-payment-response';

            $spgateway_args['NotifyURL'] = get_site_url() . '/spgateway-notify-url-request/?order_id='.$order_id;

            //            $pa_koostis_value = get_post_meta($product->id);
            // make filter to detect if this is sendright product then if so, we need to redirect to thank you page
            // for sendright registration
            // $spgateway_args['ReturnURL'] = get_site_url() . '/thank-you?orderId='.$order_id;
            //                         print "<pre>";
            // print "product title " . $spgateway_args['Title1'];
            // print "spgateway arg";
            //                         print_r($_product);
            //                                     print_r($item_nam);
            //            print_r($spgateway_args);
                        //                                     print_r($order);
            //                                     print "</pre>";
            //            exit;
            $spgateway_gateway = $this->gateway;
            $spgateway_args_array = array();
            foreach ($spgateway_args as $key => $value) {
                $spgateway_args_array[] = '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
            }

            // create users account

            return '<form id="spgateway" name="spgateway" action=" ' . $spgateway_gateway . ' " method="post" target="_top">' . implode('', $spgateway_args_array) . '
  				    <input type="submit" class="button-alt" id="submit_spgateway_payment_form" value="' . __('前往 spgateway 支付頁面', 'spgateway') . '" />
  				    </form>' . "<script>setTimeout(\"document.forms['spgateway'].submit();\",\"10\")</script>";
        }


        /**
         * Output for the order received page.
         *
         * @access public
         * @return void
         */
        function thankyou_page() {

            // PRINT "<PRE>";
            // print "post";
            // print_r($_POST);
            // print "get";
            // print_r($_GET);
            // print "session";
            // print_r($_SESSION);
            // PRINT "</PRE>";
            // if(wp_mail("mrjesuserwinsuarez@gmail.com", "test", "test")) {
            //     print "invoice sent to email";
            // } else {
            //     print "invoice not sent to email";
            // }
            // print "This is the thank you page";
            // print "<script> Thank you page loaded</script>";



            // exit;
            if(isset($_REQUEST['order-received']) && isset($_REQUEST['key']) && preg_match('/^wc_order_/', $_REQUEST['key']) && isset($_REQUEST['page_id'])){
                $order = new WC_Order($_REQUEST['order-received']);
            }

            if (isset($_REQUEST['PaymentType']) && ($_REQUEST['PaymentType'] == "CREDIT" || $_REQUEST['PaymentType'] == "WEBATM")) {
                if (in_array($_REQUEST['Status'], array('SUCCESS', 'CUSTOM'))) {
                    echo "交易成功<br>";
                } else {
                    isset($order) && $order->remove_order_items();
                    isset($order) && $order->update_status('failed');
                    echo "交易失敗，請重新填單<br>錯誤代碼：" . $_REQUEST['Status'] . "<br>錯誤訊息：" . $_REQUEST['Message'];
                }
            } else if (isset($_REQUEST['PaymentType']) && ($_REQUEST['PaymentType'] == "VACC")) {
                if ($_REQUEST['BankCode'] != "" && $_REQUEST['CodeNo'] != "") {
                    echo "付款方式：ATM<br>";
                    echo "取號成功<br>";
                    echo "銀行代碼：" . $_REQUEST['BankCode'] . "<br>";
                    echo "繳費代碼：" . $_REQUEST['CodeNo'] . "<br>";
                } else {
                    isset($order) && $order->remove_order_items();
                    isset($order) && $order->update_status('failed');
                    echo "交易失敗，請重新填單<br>錯誤代碼：" . $_REQUEST['Status'] . "<br>錯誤訊息：" . $_REQUEST['Message'];
                }
            } else if (isset($_REQUEST['PaymentType']) && ($_REQUEST['PaymentType'] == "CVS")) {
                if ($_REQUEST['CodeNo'] != "") {
                    echo "付款方式：超商代碼<br>";
                    echo "取號成功<br>";
                    echo "繳費代碼：" . $_REQUEST['CodeNo'] . "<br>";
                } else {
                    isset($order) && $order->remove_order_items();
                    isset($order) && $order->update_status('failed');
                    echo "交易失敗，請重新填單<br>錯誤代碼：" . $_REQUEST['Status'] . "<br>錯誤訊息：" . $_REQUEST['Message'];
                }
            } else if (isset($_REQUEST['PaymentType']) && ($_REQUEST['PaymentType'] == "BARCODE")) {
                if ($_REQUEST['Barcode_1'] != "" || $_REQUEST['Barcode_2'] != "" || $_REQUEST['Barcode_3'] != "") {
                    echo "付款方式：條碼<br>";
                    echo "取號成功<br>";
                    echo "請前往信箱列印繳費單<br>";
                } else {
                    isset($order) && $order->remove_order_items();
                    isset($order) && $order->update_status('failed');
                    echo "交易失敗，請重新填單<br>錯誤代碼：" . $_REQUEST['Status'] . "<br>錯誤訊息：" . $_REQUEST['Message'];
                }
            } else if (isset($_REQUEST['PaymentType']) && ($_REQUEST['PaymentType'] == "ALIPAY" || $_REQUEST['PaymentType'] == "TENPAY")) {
                if (in_array($_REQUEST['Status'], array('SUCCESS', 'CUSTOM'))) {
                    echo "交易成功<br>";
                    if ($_REQUEST['ChannelID'] == "ALIPAY") {
                        echo "跨境通路類型：支付寶<br>";
                    } else if ($_REQUEST['ChannelID'] == "TENPAY") {
                        echo "跨境通路類型：財富通<br>";
                    }
                    echo "跨境通路交易序號：" . $_REQUEST['ChannelNO'] . "<br>";
                } else {
                    isset($order) && $order->remove_order_items();
                    isset($order) && $order->update_status('failed');
                    echo "交易失敗，請重新填單<br>錯誤代碼：" . $_REQUEST['Status'] . "<br>錯誤訊息：" . $_REQUEST['Message'];
                }
            } else if ($_REQUEST['Status'] == 'CUSTOM') {
                echo "付款方式：{$_REQUEST['PaymentType']}<br>";
            } else if ($_REQUEST['Status'] == "" && $_REQUEST['Message'] == "") {
                // isset($order) && $order->cancel_order();
                echo "交易取消<br>";
            } else {
                isset($order) && $order->cancel_order();
                echo "交易失敗，請重新填單<br>錯誤代碼：" . $_REQUEST['Status'] . "<br>錯誤訊息：" . $_REQUEST['Message'];
            }

            print "<h1> Send Invoice to customer<h1>";
            print "<h1> Display design for thank you page</h1>";

            //            exit;
        }


        function addpadding($string, $blocksize = 32) {
            $len = strlen($string);
            $pad = $blocksize - ($len % $blocksize);
            $string .= str_repeat(chr($pad), $pad);
            return $string;
        }


        function curl_work($url = "", $parameter = "") {
            $curl_options = array(
                CURLOPT_URL => $url,
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => "Google Bot",
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_SSL_VERIFYHOST => FALSE,
                CURLOPT_POST => "1",
                CURLOPT_POSTFIELDS => $parameter
            );
            $ch = curl_init();
            curl_setopt_array($ch, $curl_options);
            $result = curl_exec($ch);
            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_errno($ch);
            curl_close($ch);

            $return_info = array(
                "url" => $url,
                "sent_parameter" => $parameter,
                "http_status" => $retcode,
                "curl_error_no" => $curl_error,
                "web_info" => $result
            );
            return $return_info;
        }


        function receive_response() {  //接收回傳參數驗證
            $re_MerchantOrderNo = trim($_REQUEST['MerchantOrderNo']);
            $re_MerchantID = $_REQUEST['MerchantID'];
            $re_Status = $_REQUEST['Status'];
            $re_TradeNo = $_REQUEST['TradeNo'];
            $re_CheckCode = $_REQUEST['CheckCode'];
            $re_Amt = $_REQUEST['Amt'];

            $order = new WC_Order($re_MerchantOrderNo);
            $Amt = $order->get_total();

            //CheckCode 串接
            $code_arr = array('MerchantID' => $this->MerchantID, 'TradeNo' => $re_TradeNo, 'MerchantOrderNo' => $re_MerchantOrderNo, 'Amt' => $Amt);
            //按陣列的key做升幕排序
            ksort($code_arr);
            //排序後排列組合成網址列格式
            $code_merstr = http_build_query($code_arr, '', '&');
            $checkcode_str = "HashIV=" . $this->HashIV . "&" . $code_merstr . "&HashKey=" . $this->HashKey;
            $CheckCode = strtoupper(hash("sha256", $checkcode_str));

            //檢查回傳狀態是否為成功
            if (in_array($re_Status, array('SUCCESS', 'CUSTOM'))) {
                //檢查CheckCode是否跟自己組的一樣
                if ($CheckCode == $re_CheckCode) {
                    //檢查金額是否一樣
                    if ($Amt == $re_Amt) {
                        //全部確認過後，修改訂單狀態(處理中，並寄通知信)
                        $order->payment_complete();
                        $msg = "訂單修改成功";
                    } else {
                        $msg = "金額不一致";
                    }
                } else {
                    $msg = "checkcode碼錯誤";
                }
            } else if ($re_Status == "CUSTOM") {
                //檢查CheckCode是否跟自己組的一樣
                if ($CheckCode == $re_CheckCode) {
                    //檢查金額是否一樣
                    if ($Amt == $re_Amt) {
                        $msg = "訂單處理成功";
                    } else {
                        $msg = "金額不一致";
                    }
                } else {
                    $msg = "checkcode碼錯誤";
                }
            } else {
                $msg = "訂單處理失敗";
            }

            if (isset($_GET['callback'])) {
                echo $msg;
                exit; //一定要有離開，才會被正常執行
            }
        }

        /**
         * Output for the order received page.
         *
         * @access public
         * @return void
         */
        function receipt_page($order) {
            echo '<p>' . __('Processing order<br>', 'spgateway') . '</p>';
            echo $this->generate_spgateway_form($order);
        }

        /**
         * Process the payment and return the result
         *
         * @access public
         * @param int $order_id
         * @return array
         */
        function process_payment($order_id) {
            global $woocommerce;
            $order = new WC_Order($order_id);

            // Empty awaiting payment session
            unset($_SESSION['order_awaiting_payment']);
            //$this->receipt_page($order_id);
            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(true)
            );
        }

        /**
         * Payment form on checkout page
         *
         * @access public
         * @return void
         */
        function payment_fields() {
            if ($this->description)
                echo wpautop(wptexturize($this->description));
        }


        function check_spgateway_response() {

            echo "ok";
        }

    }
    /**
     * Add the gateway to WooCommerce
     *
     * @access public
     * @param array $methods
     * @package		WooCommerce/Classes/Payment
     * @return array
     */
    function add_spgateway_agreed_payment_gateway($methods) {
        $methods[] = 'WC_spgateway_agreed_payment';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_spgateway_agreed_payment_gateway');

}