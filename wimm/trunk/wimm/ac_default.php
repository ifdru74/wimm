<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if (key_exists('type', $_REQUEST)) {
    $sel = $_REQUEST['type'];
}//filter_input(INPUT_REQUEST, "type", FILTER_DEFAULT);
else {
    $sel = '';
}
if (key_exists('str', $_REQUEST)) {
    $bs = urldecode($_REQUEST['str']);
}//filter_input(INPUT_REQUEST, "str", FILTER_DEFAULT)
else {
    $bs = '';
}
$a = array('first' => array(1 => "one", 2 => "two", 3 => "three", 4 => "four", 5 => "five", 6 => "ones"),
    'second' => array(1 => "apple", 2 => "banana", 3 => "orange", 4 => "lemon", 5 => "nut", 
        6=>"scroll", 7=>"scroll", 8=>"scroll", 9=>"scroll", 10=>"scroll", 11=>"scroll", 12=>"scroll", 13=>"scroll"));
$aret = array();
if (key_exists($sel, $a)) {
    $ta = $a[$sel];
    foreach ($ta as $key => $value) {
        if(strpos($value, $bs)==0)
            $aret[] = array('id' => $key, 'text' => $value);
    }
} else {
    $aret[] = array('id' => 'error', 'text' => "no values for $sel");
}
if (count($aret) > 0) {
    $sout = json_encode($aret);
    echo $sout;
}