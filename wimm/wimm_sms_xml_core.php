<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * parse XML file sintax
 * @param string $xml_file_name
 * @return mixed FALSE or array(SimpleXMLElement, string)
 */
function parseSMSXml($xml_file_name)
{
    $xml = FALSE;
    $message = "Процесс пошёл. Не закрывайте вкладку до получения уведомления о завершении.";
    if(file_exists($xml_file_name))
    {
        libxml_use_internal_errors();
        $xml = simplexml_load_file($xml_file_name);
        if($xml===FALSE)
        {
            $message = "Не удалось разобрать файл.";
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                $err_str = "";
                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $err_str .= "Warning $error->code: ";
                        break;
                     case LIBXML_ERR_ERROR:
                        $err_str .= "Error $error->code: ";
                        break;
                    case LIBXML_ERR_FATAL:
                        $err_str .= "Fatal Error $error->code: ";
                        break;
                }
                $err_str .= trim($error->message) . "\n  Line: $error->line" . "\n  Column: $error->column";
                if ($error->file) {
                    $err_str .= "\n  File: $error->file";
                }
                $message .= '<p>' . $err_str . '</p>';
            }
        }
    }
    else
    {
        $message = "Не удалось загрузить файл";
    }
    return array("xml" => $xml, "message"=>$message);
}

/**
 * log string to file
 * @param resource $f
 * @param string $file_msg
 * @return mixed FALSE on failure or bytes written
 */
function log2file($f, $file_msg)
{
    return fwrite($f, $file_msg . PHP_EOL, strlen($file_msg)+1);
}

/**
 * 
 * @param string   $field_name - name of the field
 * @param array    $field_definitions - array of field definitions
 * @param array    $positions - positions
 * @param string   $txt - parsed text string
 * @param integer  $rownum - row number
 * @param resource $f - file handler
 * @return mixed
 */
function get_following_position($field_name, $field_definitions, $positions, $txt, $rownum, $f)
{
    $pa = FALSE;
    if(key_exists(FMT_FOLLOWING, $field_definitions[$field_name]))
    {
        $fwn = $field_definitions[$field_name][FMT_FOLLOWING];
        if($fwn===FALSE)
        {
            $pa = strlen($txt);
            log2file($f,"1ARRAY:following field position is EOL for field '$field_name' at row $rownum");
        }
        else
        {
            if(is_array($fwn))
            {
                foreach ($fwn as $fn) {
                    if(key_exists($fn, $positions))
                    {
                        $pa = $positions[$fn][STR_POS_BEFORE];
                        log2file($f,"2ARRAY:following field '$fn' position is $pa for field '$field_name' at row $rownum");
                        break;
                    }
                }
                if($pa===FALSE)
                {
                    log2file($f,"3ARRAY:no following field 'ARRAY' position for field '$field_name' at row $rownum");
                }
            }
            else
            {
                if(key_exists($fwn, $positions))
                {
                    $pa = $positions[$fwn][STR_POS_BEFORE];
                    log2file($f,"4ARRAY:following field '$fwn' position is $pa for field '$field_name' at row $rownum");
                }
                else
                {
                    log2file($f,"5ARRAY:no following field '$fwn' position for field '$field_name' at row $rownum");
                }
            }
        }
    }
    return $pa;
}
/**
 * converts XML file contents into DB
 * @param SimpleXMLElement $xml
 * @param PDO $conn
 * @return mixed FALSE or array(SimpleXMLElement, string)
 */
function processSMSXml($xml,$conn)
{
    if($xml)
    {
        // got xml - parse it
        $sms_formats = array(
            'Citialert' => array(
                'type' =>  array(FMT_TRANSFORM => array('Spisanie:'=>'expenses', 
                    'Pokupka:'=>'expenses',
                    'Spisanie po kreditnoy karte \*\*[0-9]{4} Summa:'=>'expensess')),
                'balance' => array(FMT_REGEX => array('Balans:'),
                                   FMT_VAL_REGEXP => "[0-9]{1,9}.[0-9]{2} RUB",
                                   FMT_REMOVABLE => ' RUB',
                                   FMT_FOLLOWING => FALSE),
                'date' => array(
                        FMT_REGEX => array('Data:'),
                        FMT_DATEFMT => 'DD/MM/YY',
                        FMT_FOLLOWING => 'balance'),
                'place' => array(FMT_REGEX => array('Torgovaya tochka:'),
                                 FMT_FOLLOWING => 'date'),
                'operation' => array(FMT_REGEX => array('Operaciya:'),
                                 FMT_FOLLOWING => 'place'),
                'account' => array(FMT_REGEX => array(' so scheta ',' schet '),
                                 FMT_FOLLOWING => array('operation', 'place')),
                'amount' => array(FMT_REGEX => 'type',
                                   FMT_VAL_REGEXP => "[0-9]{1,9}.[0-9]{2} RUB",
                                   FMT_REMOVABLE => ' RUB',
                                   FMT_FOLLOWING => 'account'),
                'currency' => array(' RUB'),
                'date_from_sms' => 'readable_date'
                ),
            '900' => array(
                'account' => array(FMT_EXTRACT => array('ECMC[0-9]{4}','VISA[0-9]{4}'),
                                 FMT_FOLLOWING => 'date'),
                'type' =>  array(FMT_TRANSFORM => array('списание'=>'expenses', 
                    'выдача наличных'=>'expenses',
                    'покупка'=>'expenses',
                    'отмена покупки'=>'refund',
                    'зачисление'=>'refund',
                    'оплата услуг'=>'expenses',
                    'оплата Мобильного банка'=>'expenses',
                    'операция зачисления на сумму'=>'refund',
                    'операция списания на сумму'=>'expenses',
                    'Остаток общей задолженности по отчету'=>'info',
                    'Вход в Сбербанк Онлайн'=>'info')),
                'date' => array(
                            FMT_REGEX => 'account',//FMT_REGEX => array('ECMC5[0-9]{3}','VISA4[0-9]{3}'),
                            FMT_DATEFMT => 'DD.MM.YY HH24:MI',
                            FMT_FOLLOWING => 'type'),
                'balance' => array(FMT_REGEX => array('Баланс:'),
                                   FMT_VAL_REGEXP => "[0-9]{1,9}.[0-9]{2}р.?",
                                   FMT_REMOVABLE => array('р.', 'р'),
                                   FMT_FOLLOWING => FALSE),
                'currency' => array('р'),
                'amount' => array(FMT_REGEX => 'type',
                                   FMT_VAL_REGEXP => "[0-9]{1,9}.[0-9]{2}р.?",
                                   FMT_REMOVABLE => array('р.', 'р'),
                                   FMT_FOLLOWING => 'balance'),
                'place' => array(FMT_REGEX => 'amount',
                                 FMT_FOLLOWING => 'balance')
            )
        );
        $f = fopen("parse.log","w");
        $rownum = 0;
        $positions = array();
        foreach ($sms_formats as $key => $fmt) {
            foreach ($fmt as $poskey => $pval) {
                $positions[$poskey] = 0;
            }
        }
        echo $xml->getName() . PHP_EOL;
        if(strcmp($xml->getName(),"smses")==0 )
        {
            echo $xml['count'] . PHP_EOL;
            foreach ($xml->children() as $sms)
            {
                if(strcmp($sms->getName(),"sms")==0 )
                {
                    $file_msg = "";
                    $tr = array();
                    $a = (string)$sms['address'];
                    if(array_key_exists($a, $sms_formats))
                    {
                        $txt = (string)$sms['body'];
                        $fmt = $sms_formats[$a];
                        $tr['type'] = "unknown";
                        log2file($f,"--------------READING ROW $rownum");
                        foreach ($fmt as $fname => $fvalue) 
                        {
                            log2file($f,"--------------EXTRACTING FIELD '$fname'");
                            $positions[$fname] = array(STR_POS_BEFORE=>FALSE, 
                                STR_POS_AFTER=>FALSE,
                                STR_VAL_LEN=>0);
                            switch($fname)
                            {
                                case 'currency':
                                    break;  // skip
                                case 'date_from_sms':
                                    $tr['date'] = (string)$sms[$fmt["date_from_sms"]];
                                    break;
                                default:
                                    if(key_exists(FMT_REGEX, $fmt[$fname]))
                                    {
                                        if(is_array($fmt[$fname][FMT_REGEX]))
                                        {   // iterate regular expressions
                                            foreach ($fmt[$fname][FMT_REGEX] as $fplv)
                                            {
                                                if(preg_match("/" . $fplv . "/", $txt, $matches, PREG_OFFSET_CAPTURE)==1)//'after'
                                                {
                                                    $positions[$fname][STR_POS_BEFORE] = $matches[0][1];
                                                    $positions[$fname][STR_POS_AFTER] = $matches[0][1] + strlen($matches[0][0]);//'after'
                                                    $pa = get_following_position($fname, $fmt, $positions, $txt, $rownum, $f);
                                                    if($pa)
                                                    {
                                                        $tr[$fname] = trim(substr($txt, $positions[$fname][STR_POS_AFTER], $pa - $positions[$fname][STR_POS_AFTER]));
                                                        $positions[$fname][STR_VAL_LEN] = strlen($tr[$fname]);
                                                    }
                                                    break;
                                                }
                                            }
                                        }
                                        else
                                        {   // no action at this iteration
                                            $pa = $pb = FALSE;
                                            if($fmt[$fname][FMT_REGEX]===FALSE)
                                            {
                                                $pb = 0;
                                            }
                                            else
                                            {   // followed by another field
                                                $fwn = $fmt[$fname][FMT_REGEX];
                                                if(key_exists($fwn, $positions))
                                                {
                                                    $pb = $positions[$fwn][STR_POS_AFTER];
                                                }
                                                else
                                                {
                                                    log2file($f,"4VALUE:no previous field '$fwn' position for field '$fname' at row $rownum");
                                                }
                                            }
                                            $pa = get_following_position($fname, $fmt, $positions, $txt, $rownum, $f);
                                            if($pa && $pb)
                                            {
                                                if($positions[$fname][STR_POS_BEFORE]===FALSE)
                                                {
                                                    $positions[$fname][STR_POS_BEFORE] = $pb;
                                                }
                                                $vstr = substr($txt, $pb, $pa - $pb);
                                                log2file($f,"raw value '$vstr' cut by $pb ,$pa for field $fname at row $rownum");
                                                if(key_exists(FMT_VAL_REGEXP, $fmt[$fname]))
                                                {
                                                    if(preg_match("/" .$fmt[$fname][FMT_VAL_REGEXP] . "/", $vstr, $matches, PREG_OFFSET_CAPTURE)==1)
                                                    {
                                                        $tr[$fname] = $matches[0][0];
                                                        $positions[$fname][STR_VAL_LEN] = strlen($matches[0][0]);
                                                        if($positions[$fname][STR_POS_AFTER]===FALSE)
                                                        {
                                                            $positions[$fname][STR_POS_AFTER] = $pb + strlen($matches[0][0]) + 1;
                                                        }
                                                    }
                                                    else
                                                    {
                                                        log2file($f,"7VALUE:value regexp '" . $fmt[$fname][FMT_VAL_REGEXP] . "' don't match '$vstr' at row $rownum");
                                                    }
                                                }
                                                else
                                                {
                                                    $tr[$fname] = trim($vstr);
                                                    $positions[$fname][STR_VAL_LEN] = strlen($tr[$fname]);
                                                }
                                            }
                                            else
                                            {
                                                log2file($f,"8VALUE:position before '$pb' or after '$pa' is not valid for field '$fname' at row $rownum");
                                            }
                                        }
                                    }
                                    else
                                    {
                                        if(key_exists(FMT_EXTRACT, $fmt[$fname]))
                                        {
                                            foreach ($fmt[$fname][FMT_EXTRACT] as $fplv)
                                            {
                                                if(preg_match("/" . $fplv . "/", $txt, $matches, PREG_OFFSET_CAPTURE)==1)//'after'
                                                {
                                                    $positions[$fname][STR_POS_BEFORE] = $matches[0][1];
                                                    $positions[$fname][STR_POS_AFTER] = $matches[0][1] + strlen($matches[0][0]);//'after'
                                                    $tr[$fname] = $matches[0][0];
                                                    $positions[$fname][STR_VAL_LEN] = strlen($tr[$fname]);
                                                    break;
                                                }
                                            }
                                            
                                        }
                                        else
                                        {
                                            if(key_exists(FMT_TRANSFORM, $fmt[$fname]))
                                            {
                                                foreach ($fmt[$fname][FMT_TRANSFORM] as $key => $value) {
                                                    if(preg_match("/" . $key . "/", $txt, $matches, PREG_OFFSET_CAPTURE)==1)
                                                    {
                                                        $tr[$fname] = $value;
                                                        $positions[$fname][STR_POS_BEFORE] = $matches[0][1];
                                                        $positions[$fname][STR_POS_AFTER] = $matches[0][1] + strlen($matches[0][0]);//'after'
                                                        $positions[$fname][STR_VAL_LEN] = strlen($tr[$fname]);
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                            }
                            if(key_exists($fname, $tr))
                            {
                                if(key_exists(FMT_REMOVABLE, $fmt[$fname]))
                                {
                                    if(is_array($fmt[$fname][FMT_REMOVABLE]))
                                    {
                                        foreach ($fmt[$fname][FMT_REMOVABLE] as $rvalue) {
                                            $tr[$fname] = str_replace($rvalue, "", $tr[$fname]);
                                        }
                                    }
                                    else
                                    {
                                        $tr[$fname] = str_replace($fmt[$fname][FMT_REMOVABLE], "", $tr[$fname]);
                                    }
                                }
                            }
                        }
                    }
                    else 
                    {
                        $file_msg = "ERR: unsupported message format " . $sms['address'];
                    }
                    print_r($tr);
                    $rl = strlen($file_msg);
                    if($rl>0)
                    {
                        fputs ($f, $file_msg . PHP_EOL, $rl+1);
                    }
                    else
                    {
                        log2file($f, 'Empty row:' . $rownum);
                    }
                    $rownum ++;
                }
                else
                {
                    echo 'got node: ' . $sms->getName() . PHP_EOL;
                }
            }
        }
        else
        {
            echo 'got root node: ' . $xml->getName() . PHP_EOL;
        }
        fclose($f);
    }
    else
    {
        echo 'XML is FALSE!' . PHP_EOL;
    }
}

    if(!defined("STR_POS_BEFORE"))
    {
        define("STR_POS_BEFORE",'before');
    }

    if(!defined("STR_POS_AFTER"))
    {
        define("STR_POS_AFTER",'after');
    }
    if(!defined("STR_VAL_LEN"))
    {
        define("STR_VAL_LEN",'val_len');
    }
    if(!defined("FMT_REGEX"))
    {
        define("FMT_REGEX",'regex');
    }
    if(!defined("FMT_FOLLOWING"))
    {
        define("FMT_FOLLOWING",'following');
    }
    if(!defined("FMT_REMOVABLE"))
    {
        define("FMT_REMOVABLE",'removalble');
    }
    if(!defined("FMT_DATEFMT"))
    {
        define("FMT_DATEFMT",'format');
    }
    if(!defined("FMT_VAL_REGEXP"))
    {
        define("FMT_VAL_REGEXP",'vregex');
    }
    if(!defined("FMT_EXTRACT"))
    {
        define("FMT_EXTRACT",'extract');
    }
    if(!defined("FMT_TRANSFORM"))
    {
        define("FMT_TRANSFORM",'transform');
    }
