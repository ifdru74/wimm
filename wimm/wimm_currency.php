<?php
    include_once ("fun_web.php");
    $uid = page_pre();
    if($uid===FALSE)
        die();
    include_once 'fun_dbms.php';
    $inc = get_include_path();
    set_include_path($inc . ";trunk\\wimm\\cls\\table");
    include_once 'table.php';
    $p_title = "Валюты, в которых тратятся деньги";
    print_head($p_title);
function print_buttons($bd="")
{
	print "<TABLE WIDTH=\"100%\" class=\"hidden\">\n";
	print "\t<TR class=\"hidden\">\n";
	print "\t\t<TD class=\"hidden\"><input name='btn_refresh' type=\"submit\" value=\"Обновить\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Добавить\" onclick=\"$('#dialog_box').show();$('#DEL_BTN').hide();$('#HIDDEN_ID').val('');$('.form_field').val('');$('#FRM_MODE').val('insert');document.getElementById('curr_name').focus();\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"reset\" value=\"Снять выделение\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Выход\" onclick=\"send_submit('exit')\"></TD>\n";
	print "\t</TR>\n";
	print "</TABLE>\n";
}
?>
    <body onload="onLoad()">
        <script language="JavaScript" type="text/JavaScript">
            function onLoad()
            {
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
        <form id="edit_form" name="currencies" method="post">
            <div id="dialog_box" class="dlg_box" style="width:600px;display:none;" onshow='document.getElementById("curr_name").focus();'>
                <div class="dlg_box_cap">Редактирование</div>
                <div class="dlg_box_text">
                    <div class="dialog_row">
                        <label class="dialog_lbl" for="curr_name">Наименование:</label>
                        <input type="text" class="dialog_ctl form_field" name="curr_name" id="curr_name" 
                               bind_row_type="label" bind_row_id="CNAME_" value="">
                    </div>
                    <div class="dialog_row" style="height: 40px;display: block">
                        <label class="dialog_lbl" for="curr_abbr">Сокращённое наименование:</label>
                        <input type="text" class="dialog_ctl form_field" name="curr_abbr" id="curr_abbr" 
                               bind_row_type="title" bind_row_id="CNAME_" value="">
                    </div>
                    <div class="dialog_row">
                        <label class="dialog_lbl" for="curr__sign">Знак валюты:</label>
                        <input type="text" class="dialog_ctl form_field" name="curr_sign" 
                               bind_row_type="label" bind_row_id="CSIGN_"  id="curr_sign" value="">
                    </div>
                </div>
                <div class="dlg_box_btns">
                    <input id="OK_BTN" class="DLG_BTN" type="submit" value="Сохранить">
                    <input id="DEL_BTN" type="button" value="Удалить" onclick="send_submit('delete');">
                    <input type="button" value="Отмена" onclick="$('#dialog_box').hide();">
                </div>
            </div>
            
        <?php
        $conn = f_get_connection();
        if($conn)	{
            $fm = "refresh";
            if(getRequestParam("btn_refresh",FALSE)===FALSE)
            {
                $fm = getRequestParam("FRM_MODE","refresh");
            }
            $sql = "";
            switch ($fm)
            {
                case "insert":
                    $sql = "insert into m_currency(currency_name, currency_abbr, currency_sign, open_date, user_id) values(";
                    $sql .= ("'" . value4db(getRequestParam("curr_name","Рубль?")) . "', ");
                    $sql .= ("'" . value4db(getRequestParam("curr_abbr","?")) . "', ");
                    $sql .= ("'" . value4db(getRequestParam("curr_sign","$")) . "', ");
                    $sql .= "#NOW#, $uid)";
                    break;
                case "update":
                    $sql = "UPDATE m_currency SET ";
                    $s = value4db(getRequestParam("curr_name","Рубль?"));
                    $sql .= "currency_name='$s', ";
                    $s = value4db(getRequestParam("curr_abbr","?"));
                    $sql .= "currency_abbr='$s', ";
                    $s = value4db(getRequestParam("curr_sign","$"));
                    $sql .= "currency_sign='$s' ";
                    $sql .= "where currency_id=";
                    $s = value4db(getRequestParam("HIDDEN_ID",0));
                    $sql .= $s;
                    break;
                case "delete":
                    $s = value4db(getRequestParam("HIDDEN_ID",0));
                    $sql = "update m_currency set close_date=#NOW# where currency_id=$s";
                    break;
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
            $tb->addColumn(new tcol("Знак валюты"), TRUE);
            $tb->addColumn(new tcol("Дата создания"), TRUE);
            $tb->addColumn(new tcol("Дата закрытия"), TRUE);
            $tb->addColumn(new tcol("Кто автор"), TRUE);
            $tb->body->setValue(tbody::$PN_ROW_CLASS, "expenses");
            $fmt_str = "<input name='ROW_ID' ID='=currency_id' type='radio' value='=currency_id' class='row_sel'>" .
                    "<LABEL TITLE='=currency_abbr' id='CNAME_=currency_id' FOR='=currency_id'>=currency_name</LABEL>";
            $tb->addColumn(new tcol($fmt_str), FALSE);
            $tb->addColumn(new tcol("<LABEL id=\"CSIGN_=currency_id\" FOR=\"=currency_id\">=currency_sign</LABEL>"), FALSE);
            $tb->addColumn(new tcol("<LABEL TITLE=\"=open_date\" id=\"ODATE_=currency_id\" FOR=\"=currency_id\">=fopen_date</LABEL>"), FALSE);
            $tb->addColumn(new tcol("<LABEL TITLE=\"=close_date\" id=\"CDATE_=currency_id\" FOR=\"=currency_id\">=fclose_date</LABEL>"), FALSE);
            $tb->addColumn(new tcol("<LABEL TITLE=\"=user_id\" id=\"USER_=currency_id\" FOR=\"=currency_id\">=user_name</LABEL>"), FALSE);

            $sql = "select currency_id, currency_name, currency_abbr, currency_sign, tp.open_date, tp.close_date, user_name, tp.user_id from m_currency tp, m_users tu where tp.user_id=tu.user_id and tp.close_date is null order by currency_name";
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
            print "<TR class=\"white_bold\"><TD COLSPAN=\"3\" TITLE=\"Запрос выполнен " . date("d.m.Y H:i:s") . "\">Количество валют</TD><TD COLSPAN=\"4\">$sm</TD></TR>\n";
            echo $tb->htmlClose();
            print_buttons();
        }
        // put your code here
        ?>
        </form>
    </body>
</html>
