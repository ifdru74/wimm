<!DOCTYPE html>
<?php
    include_once ("fun_web.php");
    $uid = page_pre();
    if($uid===FALSE)
        die();
    include_once 'fun_dbms.php';
    $inc = get_include_path();
    set_include_path($inc . ";trunk\\wimm\\cls\\table");
    include_once 'table.php';
    $p_title = "Кредиты";
    print_head($p_title);
    $conn = f_get_connection();
function print_buttons($conn, $bd="",$ed="", $bg="-1")
{	print "<TABLE WIDTH=\"100%\" class=\"hidden\">\n";
	if(strlen($bd)>0)	{
		print "\t<TR class=\"hidden\">\n";
		print "\t\t<TD class=\"hidden\" COLSPAN=\"2\">Дата начала периода:<input name=\"BDATE\" type=\"date\" value=\"$bd\"></TD>\n";
		print "\t\t<TD class=\"hidden\" COLSPAN=\"2\">Дата окончания периода:<input name=\"EDATE\" type=\"date\" value=\"$ed\"></TD>\n";
		print "\t\t<TD class=\"hidden\" COLSPAN=\"2\">Бюджет:<select size=\"1\" name=\"f_budget\">\n";
		$sql = "SELECT budget_id, budget_name FROM m_budget WHERE close_date is null";
		f_set_sel_options2($conn, $sql, $bg, 1, 2);
		print "</select></TD>\n";
		print "\t</TR>\n";
	}
	print "\t<TR class=\"hidden\">\n";
	print "\t\t<TD class=\"hidden\"><input name='btn_refresh' type=\"submit\" value=\"Обновить\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Добавить\" onclick=\"$('#dialog_box').show();$('#DEL_BTN').hide();$('#HIDDEN_ID').val('');$('.form_field').val('');$('#FRM_MODE').val('insert');document.getElementById('l_user').focus();\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"reset\" value=\"Снять выделение\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Выход\" onclick=\"send_submit('exit')\"></TD>\n";
	print "\t</TR>\n";
	print "</TABLE>\n";
}
?>
    <body onload="onLoad();">
        <script language="JavaScript" type="text/JavaScript" src="js/jquery-ui.js"></script>
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
            <div id="dialog_box" class="dlg_box ui-widget-content" style="width:525px;display:none;" onshow='document.getElementById("curr_name").focus();'>
                <div class="dlg_box_cap">Редактирование кредита</div>
                <div class="dlg_box_text">
                    <div class="dialog_row">
                        <label class="dialog_lbl" for="l_user">Кто взял:</label>
                        <select size="1" name="l_user" id="l_user" class="dialog_ctl form_field">
                            <?php
                            $sql = "select user_id, user_name from m_users where close_date is null";
                            f_set_sel_options2($conn, $sql, -1, -1, 6);
                            ?>
                        </select>
                    </div>
                    <div class="dialog_row">
                        <label class="dialog_lbl" for="l_name">Наименование:</label>
                        <input type="text" name="l_name" id="l_name" class="dialog_ctl form_field" 
                               bind_row_type="label" bind_row_id="LNAME_" value="">
                    </div>
                    <div class="dialog_row">
                        <label class="dialog_lbl" for="l_rate">Ставка, %:</label>
                        <input type="number" name="l_rate" id="l_rate" class="dialog_ctl form_field" value="">
                    </div>
                    <div class="dialog_row">
                        <label class="dialog_lbl" for="curr_to_name">Валюта:</label>
                        <input type="hidden" name="l_curr" id="l_curr" value=""
                               bind_row_type="title" bind_row_id="TNAME_">
                        <input type="text" class="dialog_ctl form_field txt" id="curr_to_name" 
                               bind_row_type="label" bind_row_id="TNAME_" value=""
                               autocomplete="off" bound_id="l_curr" ac_src="/wimm2/ac_ref.php"
                               ac_params="type=t_curr;filter=">
                    </div>
                    <div class="dialog_row">
                        <label class="dialog_lbl" for="l_sum">Сумма:</label>
                        <input type="number" name="l_sum" id="l_sum" class="dialog_ctl form_field" value="">
                    </div>
                    <div class="dialog_row">
                        <label class="dialog_lbl" for="l_bdate">Взят:</label>
                        <input type="date" name="l_bdate" id="l_bdate" class="dialog_ctl form_field" value="">
                    </div>
                    <div class="dialog_row">
                        <label class="dialog_lbl" for="l_edate">Вернуть до:</label>
                        <input type="date" name="l_edate" id="l_edate" class="dialog_ctl form_field" value="">
                    </div>
                    <div class="dialog_row">
                        <label class="dialog_lbl" for="place_name">Кто выдал:</label>
                        <input type="hidden" name="l_place" id="l_place" value=""
                               bind_row_type="title" bind_row_id="TNAME_">
                        <input type="text" class="dialog_ctl form_field txt" id="place_name" 
                               bind_row_type="label" bind_row_id="TNAME_" value=""
                               autocomplete="off" bound_id="l_place" ac_src="/wimm2/ac_ref.php"
                               ac_params="type=t_place;filter=">
                    </div>
                    <div class="dialog_row">
                        <label class="dialog_lbl" for="t_budget_txt">Бюджет:</label>
                        <input type="hidden" name="l_budget" id="t_budget" value="">
                        <input type="text" class="dialog_ctl txt" value=""
                               autocomplete="off" bound_id="l_budget" ac_src="/wimm2/ac_ref.php" 
                               ac_params="type=t_budget;filter=" id="t_budget_txt">
                    </div>
                    <div class="dialog_row">
                        <input type="checkbox" id="returned">
                        <label style="width:100px;" for="returned">Возвращён:</label>
                        <input type="date" name="l_2date" id="l_2date" class="dialog_ctl form_field" value="">
                    </div>
                </div>
                <div class="dlg_box_btns">
                    <input id="OK_BTN" class="DLG_BTN" type="submit" value="Сохранить">
                    <input id="DEL_BTN" type="button" value="Удалить" onclick="send_submit('delete');">
                    <input type="button" value="Отмена" onclick="$('#dialog_box').hide();">
                </div>
            </div>
        <?php
        // put your code here
        $fm = "refresh";
        if(getRequestParam("btn_refresh",FALSE)===FALSE)
        {
            $fm = getRequestParam("FRM_MODE","refresh");
        }
        $hfmt = "<input id=\"%s\" name=\"%s\" type=\"hidden\" value=\"%s\">" . PHP_EOL;
        printf($hfmt, "FRM_MODE", "FRM_MODE", "refresh");
        printf($hfmt, "HIDDEN_ID", "HIDDEN_ID", "0");
        if($conn)	{
            if(strcmp($fm,"insert")==0)	{
		$sql = "INSERT INTO m_loans (place_id, loan_name, start_date, end_date, loan_rate, loan_type, " .
                        "close_date, user_id, currency_id, budget_id, loan_sum) VALUES(";
		$s = getRequestParam("l_place",1);
		$sql .= "$s,";
		$s = getRequestParam("l_name","Кредит!");
		$sql .= "'$s',";
		$td = getRequestParam("l_bdate",date("Y-m-d H:i:s"));
		$sql .= "'$td',";
		$td = getRequestParam("l_edate","");
                if(strlen($td)>0)
                    $sql .= "'$td',";
                else
                    $sql .= "NULL,";
		$s = getRequestParam("l_rate",5);
		$sql .= "$s,";
		$s = getRequestParam("l_type",1);
		$sql .= "$s,";
		$td = getRequestParam("l_cdate","");
                if(strlen($td)>0)
                    $sql .= "'$td',";
                else
                    $sql .= "NULL,";
		$s = getRequestParam("l_user",1);
		$sql .= "$s,";
		$s = getRequestParam("l_curr",2);
		$sql .= "$s,";
		$s = getRequestParam("l_budget",1);
		$sql .= "$s,";
		$s = getRequestParam("l_sum",0);
		$sql .= "$s";
                $sql .= ")";
            }	else if(strcmp($fm,"update")==0)	{//!!!!!!!!!!!!!!!!!!
                $sql = "update m_loans set ";
                $s = getRequestParam("l_place",-1);
                if($s!=-1)
                    $sql .= "place_id=$s, ";
		$s = getRequestParam("l_name","");
                if(strlen($s)>0)
                    $sql .= "loan_name='$s', ";
		$td = getRequestParam("l_bdate",date("Y-m-d H:i:s"));
		$sql .= "start_date='$td'";
		$td = getRequestParam("l_edate","");
                if(strlen($td)>0)
                    $sql .= ", end_date='$td'";
                else
                    $sql .= ", end_date=NULL";
		$s = getRequestParam("l_rate",-1);
                if($s!=-1)
                    $sql .= ", loan_rate=$s ";
		$s = getRequestParam("l_type",-1);
                if($s!=-1)
                    $sql .= ", loan_type=$s ";
		$td = getRequestParam("l_cdate","");
                if(strlen($td)>0)
                    $sql .= ", close_date='$td'";
                else
                    $sql .= ", close_date=NULL";
		$s = getRequestParam("l_user",-1);
                if($s!=-1)
                    $sql .= ", user_id=$s ";
		$s = getRequestParam("l_curr",-1);
                if($s!=-1)
                    $sql .= ", currency_id=$s ";
		$s = getRequestParam("l_budget",-1);
                if($s!=-1)
                    $sql .= ", budget_id=$s ";
		$s = getRequestParam("l_sum",-1);
                if($s!=-1)
                    $sql .= ", loan_sum=$s ";
                $s = getRequestParam("HIDDEN_ID",0);
		$sql .= "where loan_id=$s";
            }   else if(strcmp($fm,"delete")==0)	{
		$s = getRequestParam("HIDDEN_ID",0);
		$sql = "delete from m_loans where loan_id=$s";                
            }
            printf($hfmt, "SQL", "SQL", $sql);
            if(strlen($sql)>0)	{
                $conn->query(formatSQL($conn, $sql));
                //$conn->commit();
            }
        }
        $dtm = new DateTime();
        $ldfmt = 'Y-m-01';//str_replace('d','01',getSessionParam('locale_date_format', 'd.m.Y'));
	$bd = update_param("BDATE", "BEG_DATE", $dtm->format($ldfmt));
        $dtm->add(new DateInterval('P1M'));
	$ed = update_param("EDATE", "END_DATE", $dtm->format($ldfmt));
        $dtm = DateTime::createFromFormat('Y-m-d', $bd);
        $ldfmt = getSessionParam('locale_date_format', 'd.m.Y');
        $dtm2 = DateTime::createFromFormat('Y-m-d', $ed);
	print_body_title('Кредиты, активные с ' . $dtm->format($ldfmt) . ' по ' . $dtm2->format($ldfmt));
	$bg = getRequestParam("f_budget","-1");
	print_buttons($conn, $bd,$ed,$bg);
        $tb = new table();
        $tb->setValue(tbase::$PN_CLASS, "visual");
        $tb->setIndent(3);
        $tb->addColumn(new tcol("Наименование"), TRUE);
        $tb->addColumn(new tcol("Сумма"), TRUE);
        $tb->addColumn(new tcol("Взят"), TRUE);
        $tb->addColumn(new tcol("Срок до"), TRUE);
        $tb->addColumn(new tcol("Кто"), TRUE);
        $tb->addColumn(new tcol("Где"), TRUE);
        $tb->body->setValue(tbody::$PN_ROW_CLASS, "expenses");
        $fmt_str = "<input name='ROW_ID' ID='=loan_id' type='radio' value='=loan_id' class='row_sel'>" .
                "<LABEL id='LNAME_=loan_name' FOR='=loan_id'>=loan_name</LABEL>".
                '<input type="hidden" id="CURR_=loan_id" title="=t_cid" value="=currency_name">'.
                '<input type="hidden" id="BUDG_=loan_id" title="=bc_id" value="=budget_name">';
        $tb->addColumn(new tcol($fmt_str), FALSE);
        $tb->addColumn(new tcol("<LABEL id=\"FRATE_=loan_id\" FOR=\"=loan_id\" TITLE=\"=loan_rate\">=loan_sum</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL id=\"CBDATE_=loan_id\" FOR=\"=loan_id\">=start_date</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL id=\"CEDATE_=loan_id\" FOR=\"=loan_id\">=end_date</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL id=\"USR_=loan_id\" FOR=\"=loan_id\" TITLE=\"=user_id\">=user_name</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL id=\"PLACE_=loan_id\" FOR=\"=loan_id\" TITLE=\"=place_id\">=place_name</LABEL>"), FALSE);
        $sql = "select loan_id, loan_name, loan_sum, loan_rate, start_date, end_date, user_name, ml.user_id, ".
                "place_name, ml.currency_id t_cid, mcu.currency_sign, mcu.currency_name, ".
                "mb.budget_name, mb.currency_id as bc_id, ml.place_id " .
                "from m_loans ml, m_users mu, m_places mp, m_currency mcu, m_budget mb ".
                "where ml.user_id=mu.user_id and ml.place_id=mp.place_id and ".
                "ml.currency_id=mcu.currency_id and ml.budget_id=mb.budget_id and ".
                "ml.close_date is null";
	if($bg>0)	{
		$sql .= " and ml.budget_id=$bg ";
	}
        $sql .= " and (start_date between '$bd' and '$ed' or end_date between '$bd' and '$ed') ";
	$sql .= " order by end_date";
        printf($hfmt, "SQL2", "SQL2", $sql);
        echo $tb->htmlOpen();
	$res = $conn->query($sql);
	$sm = 0;
	$sd = 0;
	if($res)	{		//print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                    echo $tb->htmlRow($row);
		}	
        }
	else	{
            $message  = f_get_error_text($conn, "Invalid query: ");
            print "<TR><TD COLSPAN=\"6\">$message</TD></TR>\n";
	}
	print "<TR class=\"white_bold\"><TD COLSPAN=\"3\" TITLE=\"Запрос выполнен " . date("d.m.Y H:i:s") . "\" ALIGN=\"RIGHT\">";
	$t = number_format($sd,2,","," ");
	print "Итого, набрали кредитов на:</TD><TD COLSPAN=\"3\">&nbsp;$t</TD></TR>\n";
	$t = number_format($sm,2,","," ");
        echo $tb->htmlClose();
	print_buttons($conn);
       ?>
        </form>
    </body>
</html>
