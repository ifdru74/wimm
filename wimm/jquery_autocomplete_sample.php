<?php
$lang_messages = array(
    'en'=>array(
        "name"=>"English",
        'title'=>'Autocomplete test',
        'autocomplete' => array(
            'one', 'two', 'three','four', 'one good day', 'one bad day'
        ),
        'simple_autocomplete'=>'A field with autocomplete:',
        'complex_autocomplete'=>'A field with more complicated autocomplete:',
        'PAGE_LANG'=>'Language:',
        'item_title' => 'Click to remove'
    ),
    'ru'=>array(
        "name"=>"Русский",
        'title'=>'Тест автодополнения',
        'autocomplete' => array(
            'один', 'два', 'три','четыре', 'один хороший день', 'один плохой день'
        ),
        'simple_autocomplete'=>'Поле с автодополнением:',
        'complex_autocomplete'=>'Поле с более сложным автодополнением:',
        'PAGE_LANG'=>'Язык:',
        'item_title' => 'Нажмите чтобы удалить'
    )
    );
/**
 * filter array with index
 * @param array $a - array to filter
 * @param mixed $idx - array index
 * @param mixed $def_val - default value
 * @return mixed array element or false
 */
function filter_array($a, $idx, $def_val)
{
    if(strlen($idx)>0)
    {
        if(isset($a) && is_array($a) && key_exists($idx, $a))
        {
            return $a[$idx];
        }
    }
    return $def_val;
}

function get_message_string($a, $lang, $id)
{
    if($b = filter_array($a, $lang, FALSE))
    {
        if($c=filter_array($b, $id, FALSE))
        {
            return $c;
        }
    }
    return '';
}
$session_id = filter_array($_SESSION, 'LOCAL_ID', FALSE);
$mode = filter_array($_REQUEST, 'PAGE_MODE', FALSE);
$lang = filter_array($_SESSION, 'PAGE_LANG', FALSE);
if(!$lang || !filter_array($lang_messages, $lang, FALSE))
{
    $lang = 'en';
}
if(strcmp($mode,'AC_LIST')==0)
{
    $aret = array();
    $aret[] =  array('id' => -1, 'text' => $lang);
//    if($session_id)
//    {
        $filter = filter_array($_REQUEST, 'FILTER', FALSE);
        $l = strlen($filter);
        for($i=0; $i<count($lang_messages[$lang]['autocomplete']); $i++)
        {
            $item_text = $lang_messages[$lang]['autocomplete'][$i];
            if($l<1 || strcmp(substr($item_text,0,$l), $filter)===0)
            {
                $aret[] =  array('id' => $i, 'text' => $item_text);
            }
        }
        echo json_encode($aret);
//    }
//    else {
//        $a_ref[] =  array('id' => 'error', 'text' => 'bad session');
//        echo json_encode($aret);
//    }
    flush();
    die();
}
$lang = filter_array($_REQUEST, 'PAGE_LANG', FALSE);
if(!$lang || !filter_array($lang_messages, $lang, FALSE))
{
    $lang = 'en';
}
$_SESSION['PAGE_LANG'] = $lang;
$_SESSION['LOCAL_ID'] = uniqid('AC_SESSION_');
?>
<!DOCTYPE html>
<!--
 © 2017 Alexey Shtykov
-->
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="STYLESHEET" href="css/jquery_autocomplete_ifd.css" type="text/css"/>
        <title><?php echo get_message_string($lang_messages, $lang, 'title'); ?></title>
        <style>
            P.X
            {
                background-color: lightcyan;
                color: darkgreen;
            }
            P.X:hover
            {
                border: black;
                background-color: darkgreen;
                color: lightcyan;
            }
        </style>
    </head>
    <body onload="init_autocomplete();">
        <form id='autocomplete_test' name="autocomplete_test" method="POST" >
            <fieldset>
            <label for="PAGE_LANG"><?php echo get_message_string($lang_messages, $lang, 'PAGE_LANG'); ?></label>
            <select id="PAGE_LANG" name="PAGE_LANG" onchange="this.form.submit();" style="width: 150px;">
        <?php
        
        foreach ($lang_messages as $key => $value) {
            if(strcmp($key,$lang)==0)
                $sel = "selected";
            else
                $sel = "";
            print "            <option value=\"$key\" $sel>{$value['name']}</option>" . PHP_EOL;
            
        }
        ?>
            </select>
            </fieldset>
            <fieldset>
                <div scroll_height="100" for="" selected_ac_item="" class="ac_list" id="ac1"></div>
                <label for="t_item_id_txt"><?php echo get_message_string($lang_messages, $lang, 'simple_autocomplete'); ?></label>
                <input class="form_field valid sendable" type="hidden" id="t_item_id" 
                       pattern="^[1-9][0-9]*$" focus_on="t_item_id">
                <input type="text" class="form-control form_field txt"
                       autocomplete="off" bound_id="t_item_id" ac_src="<?php echo $_SERVER['PHP_SELF'];?>" 
                       ac_params="PAGE_MODE=AC_LIST;FILTER=" id="t_item_id_txt" scroll_height="10">
            </fieldset>
            <fieldset>
                <div scroll_height="100" for="" selected_ac_item="" class="ac_list" id="ac2"></div>
                <label for="complex_autocomplete"><?php echo get_message_string($lang_messages, $lang, 'complex_autocomplete'); ?></label>
                <input type="text" class="form-control form_field txt"
                       autocomplete="off" bound_id="autocomplete_dest" ac_src="<?php echo $_SERVER['PHP_SELF'];?>" 
                       ac_params="PAGE_MODE=AC_LIST;FILTER=" id="complex_autocomplete" scroll_height="10"
                       bound_node_type='P' bound_node_class='X' bound_node_name='AC_ITEM_' bound_dest='autocomplete_dest'>
                <div id='autocomplete_dest'>
                    
                </div>
            </fieldset>
        </form>
        <?php
        // put your code here
        ?>
<?php    
    if(strpos($_SERVER['HTTP_USER_AGENT'],"MSIE ")!==FALSE)   {
?>        
            <script language="JavaScript" type="text/JavaScript" src="js/jquery-1.11.1.js"></script>
            <script language="JavaScript" type="text/JavaScript" src="js/json2.js"></script>
<?php    
    }
    else {
?>        
            <script language="JavaScript" type="text/JavaScript" src="js/jquery-2.1.1.js"></script>
<?php    
    }
?>        
        <script language="JavaScript" type="text/JavaScript" src="js/jqc_autocomplete_ifd.js"></script>
        <script language="JavaScript" type="text/JavaScript">
            var pAc1;
            function init_autocomplete()
            {
                pAc1 = new AutoCompleteIFD("ac1", ".txt", "<?php echo get_message_string($lang_messages, $lang, 'item_title'); ?>");
            }
        </script>
    </body>
</html>