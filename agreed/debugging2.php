<?php

$Post_data = spgateway_encrypt ($key, $Iv, $Post_data_str); // encryption function         D
$ef_url = '<form Method = "the POST" Action = "'. $ Gateway. '">';
$Def_url. = "<Input type = 'hidden' name = 'MerchantID_' vAlue = '". $Merchant_id. "'>";
$Def_url. = "<Input type = 'hidden' name = 'Pos_' value = '". $Pos. "'>";
    $Def_url. = "<Input type = 'hidden' name = 'PostData_' value = '". $ Post_data. "'>";         . $ def_url = "<input type = 'submit' value = ' Go to authorize '>";        $ Def_url. = "</ Form> <br />";         Echo $ def_url;