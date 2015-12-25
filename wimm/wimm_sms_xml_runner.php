<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if(count($argv)<2)
{
    die('usage: ' . $argv[0] . ' <xml file name>');
}
$incp = get_include_path();
$dir = dirname($argv[0]);
echo $dir . PHP_EOL;
if(strcmp($dir,".")!=0)
{
    $incp = $dir . PATH_SEPARATOR . $incp;
}
set_include_path($incp);
include_once 'wimm_sms_xml_core.php';
$conn = FALSE;
$aret = parseSMSXml($argv[1]);
if($aret['xml']!==FALSE)
{
    echo 'process file:' . $argv[1] . PHP_EOL;
    processSMSXml($aret['xml'],$conn);
}
else {
    echo $aret['message'] . PHP_EOL;
}
exit();
