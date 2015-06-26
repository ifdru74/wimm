<?php
    include("fun_web.php");
    //auth_check('UID');
    include_once 'fun_dbms.php';

    $conn = f_get_connection();
    $fm = "refresh";
    if(getRequestParam("btn_refresh",FALSE)===FALSE)
    {
        $fm = getRequestParam("FRM_MODE","refresh");
    }
    $uid = page_pre();
    if($uid===FALSE)    die();
    $p_title = "Редактор того, на что тратятся деньги";
    print_head($p_title);
?>
<body onload="onLoad();">
    <script language="JavaScript" type="text/JavaScript" src="js/jquery_autocomplete_ifd.js"></script>
    <script language="JavaScript" type="text/JavaScript">
        function doSel(s1, s2, s3)
        {
            sid=ttypes.HIDDEN_ID.value;
            coll = ttypes.elements;
            for(i=0; i<coll.length; i++)             {
                v = coll.item(i);
                s_1 = v.name.substr(0,2);
                s_2 = v.id;
                if(s_1=="ID")	{
                    if(s_2==sid)	{
                        v.checked=false;
                        break;
                    }
                }
            }
            ttypes.HIDDEN_ID.value=s1;
            ttypes.p_name.value=s2;
            ttypes.p_descr.value=s3;
        }

        function doEdit(s1)
        {
            if(s1=="add")	{
                $("#FRM_MODE").val("insert");
                $("#HIDDEN_ID").val("0");
                $("#dlg_box").show();
            }
            else if(s1=="edit")	{
                coll = ttypes.elements;
                for(i=0; i<coll.length; i++)             {
                    v = coll.item(i);
                    s_1 = v.name.substr(0,2);
                    s2 = v.id;
                    if(s_1=="ID")	{
                        if(v.checked)	{
                            ttypes.HIDDEN_ID.value=s2;
                            ttypes.FRM_MODE.value="update";
                            break;
                        }
                    }
                }
                if(ttypes.FRM_MODE.value=="update")
                    ttypes.submit();
                else
                    alert("Запись для редактирования не выбрана");
            }	else if(s1=="del")	{
                coll = ttypes.elements;
                for(i=0; i<coll.length; i++)             {
                    v = coll.item(i);
                    s_1 = v.name.substr(0,2);
                    s2 = v.id;
                    if(s_1=="ID")	{
                        if(v.checked)	{
                            ttypes.HIDDEN_ID.value=s2;
                            ttypes.FRM_MODE.value="delete";
                            break;
                        }
                    }
                }
                if(ttypes.FRM_MODE.value=="delete")
                    ttypes.submit();
                else
                    alert("Запись для удаления не выбрана");
            }	else if(s1=="exit")	{
                ttypes.FRM_MODE.value="return";
                ttypes.action="index.php";
                ttypes.submit();
            }
        }
        function onLoad()
        {
            ac_init("ac", ".txt");
            $(".row_sel").click(function(e)
            {
                $('.dlg_box').show();
                table_row_selected("#"+e.currentTarget.id, "#edit_form");
                $("#HIDDEN_ID").val(e.currentTarget.id);
                var s = $("#ls_"+e.currentTarget.id).attr('title');
                console.log("bind item="+"#ls_"+e.currentTarget.id+", title="+s.toString());
                switch(s.toString())
                {
                    case "1":
                        $('#p_s_p').prop('checked', true);
                        console.log('#p_s_pi');
//                        $('#p_s_mi').prop('checked', false);
                        break;
                    case "-1":
                        $('#p_s_m').prop('checked', true);
                        console.log('#p_s_mi');
//                        $('#p_s_pi').prop('checked', false);
                        break;
                    default:
                        console.log('#not+-');
                        $('#p_s_m').prop('checked', false);
                        $('#p_s_p').prop('checked', false);
                }
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
                    if($("#p_name").val().length<1)
                    {
                        alert('Надо заполнить Наименование');
                        $("#p_name").select();
                        s2 = false;
                    }
                    break;
            }
            if(s2)
                $('#edit_form').submit();
        }
    </script>
    <form id="edit_form" name="edit_box" action="wimm_ttypes.php" method="post">
        <div scroll_height="100" for="" selected_ac_item="" class="ac_list" id="ac"></div>
        <div id="dlg_box" class="dlg_box" style="position: absolute; width: 510px; display: none">
            <div class="dlg_box_cap">Редактор</div>
            <div class="dlg_box_text" style="display: inline-block;">
                <div class="dialog_row">
                    <div class="dialog_lbl"><label for="">Наименование:</label></div>
                    <div class="dialog_ctl">
                        <input class="form_field" type="text" name="p_name" id=add 
                               size="50" value="" bind_row_type="label" bind_row_id="ln_">
                    </div>
                </div>
                <div class="dialog_row">
                    <div class="dialog_lbl"><label for="">Родитель:</label></div>
                    <div class="dialog_ctl">
                        <input class="form_field txt" type="text" name="parent_text" id="parent_text" 
                               size="50" value="" bind_row_type="label" bind_row_id="lp_" autocomplete="off"
                               bound_id="p_descr" ac_src="/wimm2/ac_ref.php" ac_params="type=t_type;except=#HIDDEN_ID;filter=">
                        <input type="hidden" name="p_descr" id="p_descr" value="">
                    </div>
                </div>
                <div class="dialog_row">
                    <input type="radio" name="p_sign" id="p_s_m" value="-1"><img id="p_s_mi" src="picts/minus.gif">
                    <input type="radio" name="p_sign" id="p_s_p" value="1" ><img id="p_s_pi" src="picts/plus.gif">
                </div>
            </div>
            <div class="dlg_box_btns">
                <input class="dialog_btn" type="button" value="Сохранить" id="OK_BTN" onclick="$('#edit_form').submit();">
                <input class="dialog_btn" type="button" value="Отмена" id="CANCEL_BTN" onclick="$('#dlg_box').hide();$('#FRM_MODE').val('refresh');">
                <input class="dialog_btn" type="button" value="Удалить" id="DEL_BTN" onclick="send_submit('delete');">
            </div>
        </div>
<?php
function print_buttons($bd="")
{
	print "<TABLE WIDTH=\"100%\" class=\"hidden\">\n";
	print "\t<TR class=\"hidden\">\n";
	print "\t\t<TD class=\"hidden\"><input name=\"btn_refresh\" type=\"submit\" value=\"Обновить\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Добавить\" onclick=\"doEdit('add')\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"reset\" value=\"Снять выделение\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Выход\" onclick=\"send_submit('exit');\"></TD>\n";
	print "\t</TR>\n";
	print "</TABLE>\n";
}
	$sql = "";
        switch ($fm)    {
        case "insert":
		$sql = "INSERT INTO m_transaction_types (t_type_name, open_date, parent_type_id, type_sign, user_id) VALUES('";
		$sql .= getRequestParam("p_name","Место?");
		$sql .= "', #NOW#,";
		$sql .= "'" . getRequestParam("p_descr",1) . "',";
                $sql .= getRequestParam("p_sign",0) . ",";
		$sql .= "$uid)";
                break;
        case "update":
		$sql = "UPDATE m_transaction_types SET ";
		$s = getRequestParam("p_name","Место?");
		$sql .= "t_type_name='$s' ";
		$s = getRequestParam("p_descr","");
                if(strlen($s)>0)
                    $sql .= ", parent_type_id=$s ";
                $sql .= ", type_sign=" . getRequestParam("p_sign",0) . ' ';
		$sql .= "where t_type_id=";
		$s = getRequestParam("HIDDEN_ID",0);
		$sql .= $s;
                break;
        case "delete":
		$s = getRequestParam("HIDDEN_ID",0);
		//$sql = "delete from m_transaction_types where t_type_id=$s";
                $sql = "update m_transaction_types set close_date=#NOW# where t_type_id=$s";
	}
	print_body_title($p_title);
	if(strlen($sql)>0)	{
		print "	<input name=\"SQL\" type=\"hidden\" value=\"$sql\">\n";
                $conn->query(formatSQL($conn, $sql));
                //$conn->commit();
	}
	print "<input id=\"FRM_MODE\" name=\"FRM_MODE\" type=\"hidden\" value=\"refresh\">\n";
	print "<input id=\"HIDDEN_ID\" name=\"HIDDEN_ID\" type=\"hidden\" value=\"0\">\n";
	print "<input id=\"UID\" name=\"UID\" type=\"hidden\" value=\"" . $uid ."\">\n";
	print_buttons("edit_boxes");
	print "<TABLE class=\"visual\">\n";
	print "<thead><TR>\n";
	print "<TH WIDTH=\"45%\">Наименование</TH>\n";
	print "<TH WIDTH=\"15%\">Родитель</TH>\n";
	print "<TH WIDTH=\"5%\">Знак</TH>\n";
	print "<TH WIDTH=\"15%\">Дата создания</TH>\n";
	print "<TH  WIDTH=\"20%\">Кто автор</TH>\n";
	print "</TR></thead><tbody>\n";
	$sql = "SELECT t_type_id, t_type_name, parent_type_id, type_sign, mtt.open_date, is_repeat, period, user_name FROM m_transaction_types mtt, m_users mu where mtt.user_id=mu.user_id";
	$res = $conn->query($sql);
	$sm = 0;
	$sd = 0;
	if($res)	{
            //print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
            $plus_pict = "picts/plus.gif";
            $minus_pict = "picts/minus.gif";
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                print "<TR class=\"expenses\">\n";
                $s = $row['t_type_id'];
                $sn = $row['t_type_name'];
                $sd = $row['parent_type_id'];
                print "<TD><input name=\"rowid\" ID=\"$s\" type=\"radio\" value=\"$s\" class=\"row_sel\">";
                $s = $row['t_type_name'];
                print "<label for='{$row['t_type_id']}' id=ln_{$row['t_type_id']}>$s</label></TD>\n";
                $s = $row['parent_type_id'];
                print "<TD><label for='{$row['t_type_id']}' id=lp_{$row['t_type_id']}>$s</label></TD>\n";
                $s = $row['type_sign'];
                print "<TD id=\"ls_{$row['t_type_id']}\" TITLE=\"$s\"><CENTER>";
                if($s<0)
                    print "<IMG SRC=\"$minus_pict\">";
                else
                    if($s>0)
                        print "<IMG SRC=\"$plus_pict\">";
                    else
                        print "&nbsp;";
                print "</CENTER></TD>\n";
                $t = $row['open_date'];
                $s = f_get_disp_date($t);
                print "<TD><label for='{$row['t_type_id']}'>$s</label></TD>\n";
                $s = $row['user_name'];
                print "<TD><label for='{$row['t_type_id']}'>$s</label></TD>\n";
                print "</TR>\n";
            }
	}
	else	{
            $message  = f_get_error_text($conn, "Invalid query: ");
            print "<TR><TD COLSPAN=\"6\">$message</TD></TR>\n";
	}
	print "</tbody></TABLE>\n";
	print_buttons();
?>
    </form>
</body>

</html>