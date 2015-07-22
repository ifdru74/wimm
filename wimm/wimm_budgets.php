<?php
    include_once ("fun_web.php");
    $uid = page_pre();
    if($uid===FALSE)
        die();
    include_once 'fun_dbms.php';
    $inc = get_include_path();
    set_include_path($inc . ";trunk\\wimm\\cls\\table");
    include_once 'table.php';
    $p_title = "Редактор бюджетов, в рамках которых тратятся деньги";
    print_head($p_title);
?>
<body onload="onLoad();">
    <script language="JavaScript" type="text/JavaScript">
            function onLoad()
            {
                ac_init("ac", ".txt");
                $(".row_sel").click(function(e)
                {
                    $('.dlg_box').show();
                    $('#DEL_BTN').show();
                    $('#FRM_MODE').val('update');
                    table_row_selected("#"+e.currentTarget.id, "#edit_form");
                    $("#HIDDEN_ID").val(e.currentTarget.id);
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
                        if($("#b_name").val().length<1)
                        {
                            alert('Надо заполнить Наименование');
                            $("#b_name").select();
                            s2 = false;
                        }
                        break;
                }
                console.log("mode="+$('#FRM_MODE').val());
                if(s2)
                    $('#edit_form').submit();
            }

    </script>
    <script language="JavaScript" type="text/JavaScript" src="js/jquery_autocomplete_ifd.js"></script>
    <form id="edit_form" name="places" action="wimm_budgets.php" method="post">
        <div class="dlg_box ui-widget-content" id="dialog_box" style="width:600px;display:none;">
            <DIV class="dlg_box_cap" id="dlg_box_cap">Изменение записи</DIV>
            <DIV class="dlg_box_text" id="dlg_box_text" >
                <div class="dialog_row">
                    <label class="dialog_lbl" for="b_name">Наименование:</label>
                    <input class="dialog_ctl form_field" name="b_name" id="b_name" type="text" 
                           bind_row_type="label" bind_row_id="BNAME_" value="">
                </div>
                <div class="dialog_row">
                    <label class="dialog_lbl" for="b_descr">Описание:</label>
                    <input class="dialog_ctl form_field" name="b_descr" id="b_descr" type="text" 
                           bind_row_type="title" bind_row_id="BNAME_" value="">
                </div>
            </div>
            <DIV class="dlg_box_btns" id="dlg_box_btns">
                <input id="OK_BTN" type="button" value="Сохранить" onclick="send_submit();">
                <input id="DEL_BTN" type="button" value="Удалить" onclick="send_submit('delete');">
                <input type="button" value="Отмена" onclick="$('#dialog_box').hide();">
            </DIV>
        </div>
<?php
function print_buttons($bd="")
{
	print "<TABLE WIDTH=\"100%\" class=\"hidden\">\n";
	print "\t<TR class=\"hidden\">\n";
	print "\t\t<TD class=\"hidden\"><input name='btn_refresh' type=\"submit\" value=\"Обновить\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Добавить\" onclick=\"$('#dialog_box').show();$('#DEL_BTN').hide();$('#HIDDEN_ID').val('');$('#FRM_MODE').val('insert');\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"reset\" value=\"Снять выделение\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Выход\" onclick=\"send_submit('exit')\"></TD>\n";
	print "\t</TR>\n";
	print "</TABLE>\n";
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
            $sql = "INSERT INTO m_budget (budget_name, open_date, budget_descr, user_id) VALUES(";
            $s = getRequestParam("b_name","Бюджет?");
            $sql .= "'$s',";
            $td = date("Y-m-d H:i:s");
            $sql .= "'$td',";
            $s = getRequestParam("b_descr",1);
            $sql .= "'$s',";
            $sql .= "$uid)";
        } else if(strcmp($fm,"update")==0)	{
            $sql = "UPDATE m_budget SET ";
            $s = getRequestParam("b_name","Бюджет?");
            $sql .= "budget_name='$s',";
            $s = getRequestParam("b_descr",1);
            $sql .= "budget_descr='$s' ";
            $sql .= "where budget_id=";
            $s = getRequestParam("HIDDEN_ID",0);
            $sql .= $s;
	}
	else if(strcmp($fm,"delete")==0)	{
            $s = getRequestParam("HIDDEN_ID",0);
            //$sql = "delete from m_budget where budget_id=$s";
            $sql = "update m_budget set close_date=#NOW# where budget_id=$s";
	}
	print_body_title($p_title);
        $hfmt = "<input id=\"%s\" name=\"%s\" type=\"hidden\" value=\"%s\">" . PHP_EOL;
        printf($hfmt, "SQL", "SQL", $sql);
	if(strlen($sql)>0)	{
            $conn->query(formatSQL($conn, $sql));
            //$conn->commit();
	}
        printf($hfmt, "FRM_MODE", "FRM_MODE", "refresh");
        printf($hfmt, "HIDDEN_ID", "HIDDEN_ID", "0");
	print_buttons("edit_boxes");
        $tb = new table();
        $tb->setValue(tbase::$PN_CLASS, "visual");
        $tb->setIndent(3);
        $tb->addColumn(new tcol("Наименование"), TRUE);
        $tb->addColumn(new tcol("Дата создания"), TRUE);
        $tb->addColumn(new tcol("Дата закрытия"), TRUE);
        $tb->addColumn(new tcol("Кто автор"), TRUE);
        $tb->body->setValue(tbody::$PN_ROW_CLASS, "expenses");
        $fmt_str = "<input name='ROW_ID' ID='=budget_id' type='radio' value='=budget_id' class='row_sel'>" .
                "<LABEL TITLE='=budget_descr' id='BNAME_=budget_id' FOR='=budget_id'>=budget_name</LABEL>";
        $tb->addColumn(new tcol($fmt_str), FALSE);
        $tb->addColumn(new tcol("<LABEL TITLE=\"=open_date\" id=\"ODATE_=budget_id\" FOR=\"=budget_id\">=fopen_date</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL TITLE=\"=close_date\" id=\"CDATE_=budget_id\" FOR=\"=budget_id\">=fclose_date</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL TITLE=\"=user_id\" id=\"USER_=budget_id\" FOR=\"=budget_id\">=user_name</LABEL>"), FALSE);
        
	$sql = "select budget_id, budget_name, budget_descr, tp.open_date, tp.close_date, user_name, tp.user_id from m_budget tp, m_users tu where tp.user_id=tu.user_id and tp.close_date is null order by budget_name";
	$res = $conn->query($sql);
	$sm = 0;
	$sd = 0;
	$c_class = "dark";
        echo $tb->htmlOpen();
	if($res)	{
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                $row['fopen_date'] = f_get_disp_date($row['open_date']);
                $row['fclose_date'] = f_get_disp_date($row['close_date']);
                echo $tb->htmlRow($row);
                $sm ++;
            }
        }
	else	{
            $message  = f_get_error_text($conn, "Invalid query: ");
            print "<TR><TD COLSPAN=\"6\">$message</TD></TR>\n";
	}
        print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"Запрос выполнен " . date("d.m.Y H:i:s") . "\">Количество мест</TD><TD COLSPAN=\"4\">$sm</TD></TR>\n";
	echo $tb->htmlClose();
	print_buttons();
}

?>
        </form>
    </body>

</html>
