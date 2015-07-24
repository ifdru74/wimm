<?php
    include_once ("fun_web.php");
    $uid = page_pre();
    if($uid===FALSE)
        die();
    include_once 'fun_dbms.php';
    $inc = get_include_path();
    set_include_path($inc . ";trunk\\wimm\\cls\\table");
    include_once 'table.php';
    $p_title = "Редактор мест, где тратятся деньги";
    print_head($p_title);
?>
    <body onload="onLoad();">
        <script language="JavaScript" type="text/JavaScript" src="js/jquery-ui.js"></script>
        <script language="JavaScript" type="text/JavaScript" src="js/jquery_autocomplete_ifd.js"></script>
        <script language="JavaScript" type="text/JavaScript">
            function onLoad()
            {
                $('#dialog_box').draggable();
                $(".row_sel").click(function(e)
                {
                    $('.dlg_box').show();
                    $('#DEL_BTN').show();
                    $('#FRM_MODE').val('update');
                    table_row_selected("#"+e.currentTarget.id, "#edit_form");
                    $("#HIDDEN_ID").val(e.currentTarget.id);
                    document.getElementById('curr_name').focus();
                });
            }
            function send_submit(frm_mode)
            {
                if(frm_mode!=null && frm_mode.length>0)
                {
                    $('#FRM_MODE').val(frm_mode);
                }
                var s1 = $('#FRM_MODE').val();
                var s2 = true;
                var my_form;
                switch(s1)
                {
                    case 'exit':
                        my_form = document.getElementById('edit_form');
                        if(my_form!=null)
                            my_form.action='index.php';
                        $('#FRM_MODE').val('return');
                        //my_form.submit();
                        break;
                    case 'del':
                        if($( ".row_sel:checked" ).length<1)
                        {
                            alert('Строка для удаления не выбрана');
                            s2 = false;
                        }
                        break;
                    case 'edit':
                    case 'add':
                        if($("#curr_name").val().length<1)
                        {
                            alert('Надо заполнить Наименование');
                            $("#curr_name").select();
                            s2 = false;
                        }
                        break;
                }
                console.log("mode="+$('#FRM_MODE').val());
                if(s2)
                    $('#edit_form').submit();
            }

        </script>
<?php
function print_buttons($bd="")
{
?>
    <div>
        <div class="dialog_row">
            <input name="btn_refresh" type="submit" value="Обновить">
            <input type="button" value="Добавить" onclick="$('#dialog_box').show();$('#DEL_BTN').hide();$('#HIDDEN_ID').val('');$('.form_field').val('');$('#FRM_MODE').val('insert');document.getElementById('p_name').focus();">
            <input type="reset" value="Снять выделение">
            <input type="button" value="Выход" onclick="send_submit('exit');">
        </div>
    </div>
<?php
}

$conn = f_get_connection();
if($conn)	{
    $fm = "refresh";
        if(getRequestParam("btn_refresh",FALSE)===FALSE)
        {
            $fm = getRequestParam("FRM_MODE","refresh");
        }
	$sql = "";
	if(strcmp($fm,"insert")==0)	{
		$sql = "INSERT INTO m_places (place_name, open_date, place_descr, inn, user_id) VALUES(";
		$s = (value4db(getRequestParam("p_name",'Место?')));
		$sql .= "'$s',";
		$td = date("Y-m-d H:i:s");
		$sql .= "'$td',";
		$s = (value4db(getRequestParam("p_descr",'Описание?')));
		$sql .= "'$s',";
		$s = (value4db(getRequestParam("p_inn",'0?')));
		$sql .= "'$s',";
		$sql .= "$uid)";
	}	else if(strcmp($fm,"update")==0)	{
		$sql = "UPDATE m_places SET ";
		$s = (value4db(getRequestParam("p_inn",'0?')));
		$sql .= "inn='$s',";
		$s = (value4db(getRequestParam("p_name",'Место?')));
		$sql .= "place_name='$s',";
		$s = (value4db(getRequestParam("p_descr",'Описание?')));
		$sql .= "place_descr='$s' ";
		$sql .= "where place_id=";
		$s = getRequestParam("HIDDEN_ID",0);
		$sql .= $s;
	}
	else if(strcmp($fm,"delete")==0)	{
		$s = getRequestParam("HIDDEN_ID",0);
		//$sql = "delete from m_places where place_id=$s";
                $sql = "update m_places set close_date=#NOW# where place_id=$s";
	}
	print_body_title($p_title);
?>
        <form id='edit_form' name="places" action="wimm_places.php" method="post">
            <div id="dialog_box" class="dlg_box ui-widget-content" style="width:500px;display:none;" >
                <div class="dlg_box_cap" id="dlg_box_cap">Изменить</div>
                <div class="dlg_box_text" id="dlg_box_text">
                    <div class="dialog_row">
                        <label class="dialog_lbl" for="p_name">Наименование:</label>
                        <input type="text" id="p_name" name="p_name" value=""
                               class="dialog_ctl form_field" bind_row_type="label" bind_row_id="PNAME_">
                    </div>
                    <div class="dialog_row">
                        <label class="dialog_lbl" for="p_descr">Описание:</label>
                        <input type="text" id="p_descr" name="p_descr" value=""
                               class="dialog_ctl form_field" bind_row_type="title" bind_row_id="PNAME_">
                    </div>
                    <div class="dialog_row">
                        <label class="dialog_lbl" for="p_inn">ИНН:</label>
                        <input type="text" id="p_descr" name="p_inn" value=""
                               class="dialog_ctl form_field" bind_row_type="label" bind_row_id="INN_">
                    </div>
                </div>
                <div class="dlg_box_btns">
                    <input id="OK_BTN" type="submit" value="Сохранить">
                    <input id="DEL_BTN" type="button" value="Удалить" onclick="send_submit('delete');">
                    <input id="CANCEL_BTN" type="button" value="Отмена" onclick="$('#HIDDEN_ID').val(); $('#dialog_box').hide();">
                </div>
            </div>
            <input type="hidden" name="FRM_MODE" id='FRM_MODE' value="refresh">
            <input type="hidden" name="HIDDEN_ID" id='HIDDEN_ID' value="0">
            <input type="hidden" name="UID" id='UID' value="<?php echo $uid; ?>">
<?php
	if(strlen($sql)>0)	{
            print "            <input name=\"SQL\" type=\"hidden\" value=\"$sql\">\n";
            $conn->query(formatSQL($conn, $sql));
	}
	print_buttons("edit_boxes");
        $tb = new table();
        $tb->setValue(tbase::$PN_CLASS, "visual");
        $tb->setIndent(3);
        $tb->addColumn(new tcol("Наименование"), TRUE);
        $tb->addColumn(new tcol("ИНН"), TRUE);
        $tb->addColumn(new tcol("Дата создания"), TRUE);
        $tb->addColumn(new tcol("Дата закрытия"), TRUE);
        $tb->addColumn(new tcol("Кто автор"), TRUE);
        $tb->body->setValue(tbody::$PN_ROW_CLASS, "expenses");
        // column 1
        $fmt_str = "<input name='ROW_ID' ID='=place_id' type='radio' value='=place_id' class='row_sel'>" .
                "<LABEL TITLE='=place_descr' id='PNAME_=place_id' FOR='=place_id'>=place_name</LABEL>";
        $tb->addColumn(new tcol($fmt_str), FALSE);
        $tb->addColumn(new tcol("<LABEL id=\"INN_=place_id\" FOR=\"=place_id\">=inn</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL id=\"ODATE_=place_id\" FOR=\"=place_id\" title=\"open_date\">=fopen_date</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL id=\"CDATE_=place_id\" FOR=\"=place_id\" title=\"close_date\">=fclose_date</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL id=\"USR_=place_id\" FOR=\"=place_id\" title=\"user_id\">=user_name</LABEL>"), FALSE);
	$sql = "select place_id, place_name, tp.open_date, tp.close_date, place_descr, tp.user_id, user_name, inn from m_places tp, m_users tu where tp.user_id=tu.user_id order by place_name";
	$res = $conn->query($sql);
	$sm = 0;
	$sd = 0;
	echo $tb->htmlOpen();
	if($res)
        {
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) 
            {
                $row['fopen_date'] = f_get_disp_date($row['open_date']);
                $row['fclose_date'] = f_get_disp_date($row['close_date']);
                echo $tb->htmlRow($row);
                $sm ++;
            }
        }
	else	{
		$message  = f_get_error_text($conn, "Invalid query: ");
		print "<TR><TD COLSPAN=\"6\">$message</TD></TR>\n";
	}	print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"Запрос выполнен " . date("d.m.Y H:i:s") . "\">Количество мест</TD><TD COLSPAN=\"3\">$sm</TD></TR>\n";
	echo $tb->htmlClose();
	print_buttons();
}

?>
        </form>
    </body>
</html>
