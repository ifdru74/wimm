<?php
include_once('wimm_config.php');
include_once('fun_dbms.php');
$sql="SELECT currency_id as r_id, #CONCAT#(currency_name #||# ' (' #||# currency_abbr #||# ')') as r_name FROM m_currency WHERE close_date is null";
echo $sql . PHP_EOL;
$conn = f_get_connection();
echo formatSQL($conn, $sql) . PHP_EOL;
?>
