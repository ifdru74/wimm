<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once 'fun_web.php';

print_head("WEB UI test");
?>
<body onload="onLoad();">
<?php 
$v = getRequestParam("txt");
$s = "";
if(strlen(getRequestParam("s1"))>0)
    $s = "Submit 1st";
else {
    if(strlen(getRequestParam("s2"))>0)
        $s = "Submit 2nd";    
}
if(strlen($s)>0)
    echo "<p>$s</p>";
?>
    <script language="JavaScript" type="text/JavaScript" src="js/jquery_autocomplete_ifd.js"></script>
    <script language="JavaScript" type="text/JavaScript">

        function onLoad()
        {
            ac_init("ac", ".txt");
        }
    </script>
    <form name="acf" id="acf" method="POST">
        <div scroll_height="100" for="" selected_ac_item="" class="ac_list" id="ac"></div>
        <input class="txt" autofocus="on" name="txt" id="txt" value="<?php echo $v;?>" 
               autocomplete="off" size="40" bound_id="txt1" ac_src="ac_default.php" ac_params="type=first;str=">
        <input type="submit" name="s1" value="s1">
        <input class="txt" name="txt1" id="txt1" value="" autocomplete="off" size="30" 
               bound_dest="recv_ac2" bound_node_type="div" bound_node_class="ac_disp_item" 
               ac_src="ac_default.php" ac_params="type=second;str=">
<?php
    $main_style = "float:none; clear: both; display: flex;";
    if(isMSIE())
        $main_style = str_replace("flex", "block", $main_style);
?>
        <div id="recv_ac2" style="<?php echo $main_style;?>">
        </div>
        <input type="submit" name="s2" value="s2">
        <input type="reset" name="r2" onclick='hideAcBox("ac");'>
    </form>
    <!--
         * INPUT.bound_dest       - DOM element that receive selected autocomplete item
         * INPUT.bound_node_type  - newly created DOM element type for selected autocomplete item
         * INPUT.bound_node_class - newly created DOM element class for selected autocomplete item
         * INPUT.bound_id  - HIDDEN/TEXT control, received item id
         * INPUT.ac_src    - autocomplete source URL
         * INPUT.ac_params - semicolon separated string of parameters "name=value"     
    -->
</body>
</html>