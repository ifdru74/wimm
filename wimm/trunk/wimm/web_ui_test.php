<?php
header("Expires: " . date("D, d M Y H:i:s T"), TRUE);
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$inc = get_include_path();
set_include_path($inc . ";cls\\table");
include_once 'fun_web.php';
include_once 'table.php';
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
$conn = new PDO('sqlite:C:\\Projects\\Java\\LJRssReader\\rss_cache.db');
$sql = "select rss_item_id, rss_item_updated, rss_item_seen, rss_item_title, rss_item_link, rss_item_descr, rss_item_comments from rss_item where feed_id=1";
$tb = new table();
$tb->setValue(tbase::$PN_CLASS, "visual");
$tb->setValue("id","table_id");
$tb->setValue("name","table_name");
$tb->setIndent(3);
$tb->body->setValue(tbody::$PN_ROW_CLASS, "expenses");
// header
$tb->addColumn(new tcol("Заголовок"), TRUE);
$tb->addColumn(new tcol("Обновлено"), TRUE);
$tb->addColumn(new tcol("Просмотрено"), TRUE);
$tb->head->setColClass("dark");
// body
$tb->addColumn(new tcol('<input class="row_sel" type="radio" id="row_=rss_item_id" name="trow" value="=rss_item_id"><a target="_blank" href="=rss_item_link">=rss_item_title</a>'), FALSE);
$tb->addColumn(new tcol('<label for="row_=rss_item_id" id="uid_=rss_item_id" title="=rss_item_descr">=rss_item_updated</label>'), FALSE);
$tb->addColumn(new tcol('<label for="row_=rss_item_id" id="vid_=rss_item_id" title="=rss_item_comments">=rss_item_seen</label>'), FALSE);
?>
    <script language="JavaScript" type="text/JavaScript" src="js/jquery_autocomplete_ifd.js"></script>
    <script language="JavaScript" type="text/JavaScript">

        function delete_row()
        {
            var id=$("#e_row_id").val();
            $("#row_"+id).parent().parent().remove();
        }
        function row_selected(sel_id, form_id)
        {
            var db_id = $(sel_id).val();
            var form_fields = $(form_id).find(".form_field");
            var i;
            var bt;
            var bi;
            for(i=0; i<form_fields.length; i++)
            {
                bt = form_fields[i].getAttribute("bind_row_type");
                bi = "#" + form_fields[i].getAttribute("bind_row_id") + db_id;
                console.log("bind type="+bt+", id="+bi);
                switch(bt)
                {
                case 'value':
                    form_fields[i].value = ($(bi).val());
                    break;
                case 'label':
                    form_fields[i].value = ($(bi).text());
                    break;
                case 'title':
                    form_fields[i].value = ($(bi).attr('title'));
                    break;
                }
            }
        }
        function onLoad()
        {
            ac_init("ac", ".txt");
            $(".row_sel").click(function(e)
            {
                $('#edit_box').show();
                row_selected("#"+e.currentTarget.id, "#edit_id");
            });
        }
    </script>
    <form name="edit_name" id="edit_id" get_target="form_get.php" set_target="form_set.php">
        <div id="edit_box" class="dlg_box" style="position: fixed; display: none;">
            <div class="dlg_box_cap">Редактор</div>
            <div class="dlg_box_text">
                <div class="dialog_row">
                    <div class="dialog_lbl"><label>xxx:</label></div>
                    <input class="form_field" type="text" id="e_row_id" value="" bind_row_type="value" bind_row_id="row_">
                </div>
                <div class="dialog_row">
                    <div class="dialog_lbl"><label>yyy:</label></div>
                    <input class="form_field" type="text" id="e_row_up" value="" bind_row_type="label" bind_row_id="uid_">
                </div>
                <div class="dialog_row">
                    <div class="dialog_lbl"><label>zzz:</label></div>
                    <input class="form_field" type="text" id="e_row_se" value="" bind_row_type="label" bind_row_id="vid_">
                </div>
                <div class="dialog_row" style="display: inline-block; height: 100px;">
                    <textarea class="form_field" id="e_row_descr" bind_row_type="title" bind_row_id="uid_" rows="5" cols="50"></textarea>
                </div>
            </div>
            <div class="dlg_box_btns">
                <input class="dialog_btn" type="button" value="OK">
                <input class="dialog_btn" type="button" value="Cancel" onclick="$('#edit_box').hide();">
                <input class="dialog_btn" type="button" value="Delete" onclick="delete_row();">
            </div>
        </div>
    </form>
    <form name="acf" id="acf" method="POST">
        <div scroll_height="100" for="" selected_ac_item="" class="ac_list" id="ac"></div>
        <input class="txt" autofocus="on" name="txt" id="txt" value="<?php echo $v;?>" 
               autocomplete="off" size="40" bound_id="txt1" ac_src="/wimm/ac_default.php" ac_params="type=first;str=">
        <input type="submit" name="s1" value="s1">
        <input class="txt" name="txt1" id="txt1" value="" autocomplete="off" size="30" 
               bound_dest="recv_ac2" bound_node_type="div" bound_node_class="ac_disp_item" 
               ac_src="/wimm/ac_default.php" ac_params="type=second;str=">
<?php
    $main_style = "float:none; clear: both; display: flex;";
    if(isMSIE())
        $main_style = str_replace("flex", "block", $main_style);
?>
        <div id="recv_ac2" style="<?php echo $main_style;?>">
        </div>
        <input type="submit" name="s2" value="s2">
        <input type="reset" name="r2" onclick='hideAcBox("ac");'>
    <!--
         * INPUT.bound_dest       - DOM element that receive selected autocomplete item
         * INPUT.bound_node_type  - newly created DOM element type for selected autocomplete item
         * INPUT.bound_node_class - newly created DOM element class for selected autocomplete item
         * INPUT.bound_id  - HIDDEN/TEXT control, received item id
         * INPUT.ac_src    - autocomplete source URL
         * INPUT.ac_params - semicolon separated string of parameters "name=value"     
    -->
<?php

    echo $tb->htmlOpen();
    foreach ($conn->query($sql) as $row)
    {
        echo $tb->htmlRow($row);
    }
    echo $tb->htmlClose();
    $conn = null;
?>
    
    </form>
</body>
</html>