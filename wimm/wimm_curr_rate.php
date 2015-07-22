<?php
    include_once ("fun_web.php");
    $uid = page_pre();
    if($uid===FALSE)
        die();
    include_once 'fun_dbms.php';
    $inc = get_include_path();
    set_include_path($inc . ";trunk\\wimm\\cls\\table");
    include_once 'table.php';
    $p_title = "Обменный курс";
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
    <body onload="onLoad();">
        <script language="JavaScript" type="text/JavaScript" src="js/jquery_autocomplete_ifd.js"></script>
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
        <form id="edit_form" name="curr_rates" method="post">
            <div scroll_height="100" for="" selected_ac_item="" class="ac_list" id="ac"></div>
            <div id="dialog_box" class="dlg_box" style="width:525px;display:none;" onshow='document.getElementById("curr_name").focus();'>
                <div class="dlg_box_cap">Редактирование курса обмена</div>
                <div class="dlg_box_text">
                    <div class="dialog_row" style="height: 50px; display: block">
                        <fieldset>
                            <legend>Меняем:</legend>
                            <input type="number" class="form_field" name="from_rate" id="from_rate" 
                                   bind_row_type="label" bind_row_id="FRATE_" value="">
                            <input type="hidden" name="tf_curr" id="tf_curr" value=""
                                   bind_row_type="title" bind_row_id="FNAME_">
                            <input type="text" class="form_field txt" name="curr_from_name" id="curr_from_name" 
                                   bind_row_type="label" bind_row_id="FNAME_" value="" size="45"
                                   autocomplete="off" bound_id="tf_curr" ac_src="/wimm2/ac_ref.php"
                                   ac_params="type=t_curr;filter=">
                        </fieldset>
                    </div>
                    <div class="dialog_row" style="height: 50px;display: block">
                        <fieldset>
                            <legend>На:</legend>
                            <input type="number" class="form_field" name="to_rate" id="to_rate" 
                                   bind_row_type="label" bind_row_id="TRATE_" value="">
                            <input type="hidden" name="tt_curr" id="tt_curr" value=""
                                   bind_row_type="title" bind_row_id="TNAME_">
                            <input type="text" class="form_field txt" name="curr_to_name" id="curr_to_name" 
                                   bind_row_type="label" bind_row_id="TNAME_" value="" size="45"
                                   autocomplete="off" bound_id="tt_curr" ac_src="/wimm2/ac_ref.php"
                                   ac_params="type=t_curr;filter=">
                        </fieldset>
                    </div>
                    <div class="dialog_row" style="height: 50px;display: block">
                        <fieldset>
                            <legend>Действует:</legend>
                            <label for="curr_name">С:</label>
                            <input type="datetime" class="form_field" name="dt_open" id="dt_open" 
                                   bind_row_type="title" bind_row_id="ODATE_" value="">
                            <label for="curr_name">По:</label>
                            <input type="datetime" class="form_field" name="dt_close" id="dt_close" 
                                   bind_row_type="title" bind_row_id="CDATE_" value="">
                        </fieldset>
                    </div>
                </div>
                <div class="dlg_box_btns">
                    <input id="OK_BTN" class="DLG_BTN" type="submit" value="Сохранить">
                    <input id="DEL_BTN" type="button" value="Удалить" onclick="send_submit('delete');">
                    <input type="button" value="Отмена" onclick="$('#dialog_box').hide();">
                </div>
            </div>
        <?php
        /**
         * formats datetime field value
         * @param string $field_name
         * @param string $def_date
         * @return string formatted datetime value
         */
        function getDateFormValue($field_name, $def_date)
        {
            $dtf = value4db(getRequestParam($field_name,FALSE));
            if(!$dtf)
            {
                $dtf = $def_date;
            }
            else
            {
                $dtf = "'" . $dtf . "'";
            }
            return $dtf;
        }
        $conn = f_get_connection();
        if($conn)	{
            $fm = "refresh";
            if(getRequestParam("btn_refresh",FALSE)===FALSE)
            {
                $fm = getRequestParam("FRM_MODE","refresh");
            }
            $sql = "";
            $def_date = "#NOW#";
            switch ($fm)
            {
                case "insert":
                    $sql = "insert into m_currency_rate(currency_from, exchange_rate_from, currency_to, exchange_rate_to, open_date, close_date, user_id) values(";
                    $sql .= (str_replace("aci_","", value4db(getRequestParam("tf_curr",0))) . ", ");
                    $sql .= (value4db(getRequestParam("from_rate",0)) . ", ");
                    $sql .= (str_replace("aci_","", value4db(getRequestParam("tt_curr",0))) . ", ");
                    $sql .= (value4db(getRequestParam("to_rate",0)) . ", ");
                    $sql .= (getDateFormValue("dt_open",$def_date) . ", ");
                    $sql .= (getDateFormValue("dt_close",$def_date) . ", ");
                    $sql .= " $uid)";
                    break;
                case "update":
                    $sql = "UPDATE m_currency_rate SET ";
                    $s = str_replace("aci_","", value4db(getRequestParam("tf_curr",0)));
                    $sql .= "currency_from=$s, ";
                    $s = value4db(getRequestParam("from_rate",0));
                    $sql .= "exchange_rate_from=$s, ";
                    $s = str_replace("aci_","", value4db(getRequestParam("tt_curr",0)));
                    $sql .= "currency_to=$s, ";
                    $s = value4db(getRequestParam("to_rate",0));
                    $sql .= "exchange_rate_to=$s, ";
                    $s = getDateFormValue("dt_open",$def_date);
                    $sql .= "open_date=$s, ";
                    $s = getDateFormValue("dt_close",$def_date);
                    $sql .= "close_date=$s ";
                    $sql .= "where currency_rate_id=";
                    $s = value4db(getRequestParam("HIDDEN_ID",0));
                    $sql .= $s;
                    break;
                case "delete":
                    $s = value4db(getRequestParam("HIDDEN_ID",0));
                    $sql = "delete from m_currency_rate where currency_rate_id=$s";
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
            $tb->addColumn(new tcol("Меняем валюту"), TRUE);
            $tb->addColumn(new tcol("Меняем кол-во"), TRUE);
            $tb->addColumn(new tcol("На валюту"), TRUE);
            $tb->addColumn(new tcol("На кол-во"), TRUE);
            $tb->addColumn(new tcol("Дата создания"), TRUE);
            $tb->addColumn(new tcol("Дата закрытия"), TRUE);
            $tb->addColumn(new tcol("Кто автор"), TRUE);
            $tb->body->setValue(tbody::$PN_ROW_CLASS, "expenses");
            $fmt_str = "<input name='ROW_ID' ID='=currency_rate_id' type='radio' value='=currency_rate_id' class='row_sel'>" .
                    "<LABEL TITLE='=currency_from' id='FNAME_=currency_rate_id' FOR='=currency_rate_id'>=f_name</LABEL>";
            $tb->addColumn(new tcol($fmt_str), FALSE);
            $tb->addColumn(new tcol("<LABEL id=\"FRATE_=currency_rate_id\" FOR=\"=currency_rate_id\">=exchange_rate_from</LABEL>"), FALSE);
            $tb->addColumn(new tcol("<LABEL TITLE='=currency_to' id='TNAME_=currency_rate_id' FOR='=currency_rate_id'>=t_name</LABEL>"), FALSE);
            $tb->addColumn(new tcol("<LABEL id=\"TRATE_=currency_rate_id\" FOR=\"=currency_rate_id\">=exchange_rate_to</LABEL>"), FALSE);
            $tb->addColumn(new tcol("<LABEL TITLE=\"=open_date\" id=\"ODATE_=currency_rate_id\" FOR=\"=currency_rate_id\">=open_date</LABEL>"), FALSE);
            $tb->addColumn(new tcol("<LABEL TITLE=\"=close_date\" id=\"CDATE_=currency_rate_id\" FOR=\"=currency_rate_id\">=close_date</LABEL>"), FALSE);
            $tb->addColumn(new tcol("<LABEL TITLE=\"=user_id\" id=\"USER_=currency_rate_id\" FOR=\"=currency_rate_id\">=user_name</LABEL>"), FALSE);

            $sql = "select currency_rate_id, currency_from, f.currency_name || ' (' || f.currency_abbr || ')' as f_name, exchange_rate_from, " .
                    "currency_to, t.currency_name || ' (' || t.currency_abbr || ')' as t_name, exchange_rate_to, " .
                    "tp.open_date, tp.close_date, user_name, tp.user_id " .
                    "from m_currency_rate tp, m_users tu, m_currency f, m_currency t  " .
                    "where tp.user_id=tu.user_id and " .
                    "currency_from = f.currency_id and currency_to = t.currency_id " .
                    "order by f_name, t_name, tp.open_date, tp.close_date";
            printf($hfmt, "SQL2", "SQL2", $sql);
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
            print "<TR class=\"white_bold\"><TD COLSPAN=\"3\" TITLE=\"Запрос выполнен " . date("d.m.Y H:i:s") . "\">Количество обменных курсов</TD><TD COLSPAN=\"4\">$sm</TD></TR>\n";
            echo $tb->htmlClose();
            print_buttons();
        }
        ?>
        </form>
    </body>
</html>
