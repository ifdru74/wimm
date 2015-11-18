<?php
    include_once ("fun_web.php");
    $uid = page_pre();
    if($uid===FALSE)
        die();
    include_once 'fun_dbms.php';
    $inc = get_include_path();
    set_include_path($inc . ";trunk\\wimm\\cls\\table");
    include_once 'table.php';
    /**
     * @var $conn PDO 
     */
    $conn = f_get_connection();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="STYLESHEET" href="css/wimm.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/jquery_autocomplete_ifd.css" type="text/css"/>
        <link rel="SHORTCUT ICON" href="picts/favicon.ico">
        <title>Семейный бюджет</title>
    </head>
    <body onload="onLoad2();">
    <script language="JavaScript" type="text/JavaScript" src="js/form_common.js"></script>
<?php    
    if(isMSIE())   {
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
    <script language="JavaScript" type="text/JavaScript" src="js/jquery-ui.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="js/index_aj.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="js/jquery_autocomplete_ifd.js"></script>
    <script language="JavaScript" type="text/JavaScript">
        function onLoad2()
        {
            $("body").keydown(function(e) {
                onPageKey(e.keyCode);
            });
            setHandlers(".dtp");
            $('#dialog_box').draggable();
            ac_init("ac", ".txt");
            $(".row_sel").click(function(e)
            {
                $('.dlg_box').show();
                table_row_selected("#"+e.currentTarget.id, "#expenses");
                $('#HIDDEN_ID').val(e.currentTarget.id);
            });
        }
        function doCancel2()
        {
            $('#dialog_box').hide();
            $('#FRM_MODE').val('refresh');
        }
        function del_click2()
        {
            var s1 = $('#HIDDEN_ID').val();
            if(s1!=null && s1.length>0)
            {
                $('#FRM_MODE').val('delete');
                $('#expenses').submit();
            }
            else
            {
                alert("Запись для удаления не выбрана");
            }
        }
        function add_click2()
        {
            $('#dialog_box').show();
            $('#DEL_BTN').hide();
            $('#HIDDEN_ID').val('');
            $('.form_field').val('');
            $('#FRM_MODE').val('insert');
            document.getElementById('t_user').focus();
        }
    </script>
<?php
function print_buttons($conn, $bd="",$ed="", $bg="-1")
{	
?>
    <div style="display: block; width: 100%;">
<?php
    if(strlen($bd)>0)	{
?>
        <div style="display: block; width: 100%;">
            <label for="BDATE">Дата начала периода:</label>
            <input class="dtp" id="BDATE" name="BDATE" type="date" value="<?php echo $bd;?>">
            <label for="EDATE">Дата окончания периода:</label>
            <input class="dtp" id="EDATE" name="EDATE" type="date" value="<?php echo $ed;?>">
            <label for="f_budget">Бюджет:</label>
            <select size="1" id="f_budget" name="f_budget" onchange="$('#FRM_MODE').val('refresh'); $('#expenses').submit();">
<?php
            $sql = "SELECT budget_id, budget_name FROM m_budget WHERE close_date is null";
            f_set_sel_options2($conn, $sql, $bg, $bg, 2);
?>
            </select>
        </div>
<?php
    }
?>
        <div class="dialog_row">
            <input name="btn_refresh" type="submit" value="Обновить">
            <input type="button" value="Добавить" onclick="add_click2();">
            <input type="reset" value="Снять выделение">
            <input type="button" value="Выход" onclick="submit_myform('expenses','wimm_exit.php','exit');">
        </div>
    </div>
<?php
}
if($conn)	{
        $fm = "refresh";
        if(getRequestParam("btn_refresh",FALSE)===FALSE)
        {
            $fm = getRequestParam("FRM_MODE","refresh");
        }
	$sql_dml = "";
        $dup_id = false;
        $t_name = value4db(urldecode(getRequestParam("t_name","Покупка!")));
        $t_type = str_replace("aci_", "", getRequestParam("t_type",1));
        $t_curr = str_replace("aci_", "", getRequestParam("t_curr",2));
        $td = getRequestParam("t_date",date("Y-m-d H:i:s"));//2014-09-01 20:30:57
        $td1 = substr($td, 0, 16);
        $t_user = str_replace("aci_", "", getRequestParam("t_user",1));
        $t_place = str_replace("aci_", "", getRequestParam("t_place",0));
        $t_budget = str_replace("aci_", "", getRequestParam("t_budget",0));
        switch($fm)
        {
            case "insert":
		$s2 = getRequestParam("t_sum",0);
		if(strpos($s2,",")===false)	{
                    $t_sum = $s2;
		}
		else	{
                    $t_sum = str_replace(",",".",$s2);
		}
                $sql_pd = "SELECT transaction_id FROM m_transactions ".
                        "where t_type_id=$t_type and currency_id=$t_curr ".
                        "and transaction_sum=$t_sum ".
                        "and substr(transaction_date,1,16)='$td1';";
                $res_pd = $conn->query($sql_pd);
                if($res_pd) {
                    $row_pd = $res_pd->fetch(PDO::FETCH_ASSOC);
                    if($row_pd) {
                        $dup_id = $row_pd['transaction_id'];
                    }
                }
                if($dup_id===false) {
                    $sql_dml = "INSERT INTO m_transactions (transaction_name, ".
                            "t_type_id, currency_id, transaction_sum, transaction_date, ".
                            "user_id, open_date, place_id, budget_id) VALUES(";
                    $sql_dml .= "'$t_name',";
                    $sql_dml .= "$t_type,";
                    $sql_dml .= "$t_curr,";
                    $sql_dml .= "$t_sum,";
                    $sql_dml .= "'$td',";
                    $sql_dml .= "$t_user,";
                    $sql_dml .= "'$td',";
                    $sql_dml .= "$t_place,";
                    $sql_dml .= "$t_budget)";
                }
                else {
?>
    <div>Дублирование записи <?php echo $dup_id;?></div>
<?php
                    
                }
            case 'delete':
                $s = value4db(getRequestParam("HIDDEN_ID",0));
                $sql_dml = "delete from m_transactions where transaction_id=$s";
                break;
            case 'update':
                $s = value4db(getRequestParam("HIDDEN_ID",0));
                if(strlen($s)>0 && $s>0)
                {
                    $sql_dml = 'update m_transactions set ';
                    $sql_dml .= " transaction_name='" . $t_name . "', ";
                    $sql_dml .= " t_type_id=$t_type, ";
                    $sql_dml .= " currency_id=$t_curr, ";
                    $sql_dml .= " transaction_sum=$t_sum, ";
                    $sql_dml .= " user_id=$t_user, ";
                    $sql_dml .= " place_id=$t_place, ";
                    $sql_dml .= " budget_id=$t_budget ";
                    $sql_dml .= " where transaction_id=$s";
                }
                break;
	}
        $dtm = new DateTime();
        $ldfmt = 'Y-m-01';//str_replace('d','01',getSessionParam('locale_date_format', 'd.m.Y'));
	$bd = update_param("BDATE", "BEG_DATE", $dtm->format($ldfmt));
        $dtm->add(new DateInterval('P1M'));
	$ed = update_param("EDATE", "END_DATE", $dtm->format($ldfmt));
	        $dtm = DateTime::createFromFormat('Y-m-d', $bd);
        $ldfmt = getSessionParam('locale_date_format', 'd.m.Y');
        $dtm2 = DateTime::createFromFormat('Y-m-d', $ed);
	print_body_title('Расходы с ' . $dtm->format($ldfmt) . ' по ' . $dtm2->format($ldfmt));
	if(strlen($sql_dml)>0)	{
		print "	<input ID=\"SQL\" type=\"hidden\" value=\"$sql_dml\">\n";
		$conn->exec(formatSQL($conn, $sql_dml));
	}
        $s = "";
?>
	<form id="expenses" name="expenses" method="post" accept-charset="utf-8">
            <div scroll_height="100" for="" selected_ac_item="" class="ac_list" id="ac"></div>
            <input id="FRM_MODE" name="FRM_MODE" type="hidden" value="refresh">
            <input id="HIDDEN_ID" name="HIDDEN_ID" type="hidden" value="0">
            <input name="UID" type="hidden" value="<?php echo getRequestParam("UID", "");?>">
            <DIV class="dlg_box ui-widget-content" id="dialog_box" style="width:600px;display:none;position: absolute;top:200px;left:300px;margin-left:0px">
                <DIV class="dlg_box_cap" id="dlg_box_cap">Изменение записи</DIV>
                <DIV class="dlg_box_text" id="dlg_box_text" >
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_user">Пользователь:</label>
                            <select class="dialog_ctl form_field" size="1" id="t_user" name="t_user"
                                    bind_row_type="value" bind_row_id="T_USR_">
<?php
	$sql = "select user_id, user_name from m_users where close_date is null";
	f_set_sel_options2($conn, formatSQL($conn, $sql), $uid, $uid, 2);
?>
                            </select>
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_name">Наименование:</label>
                            <input class="dialog_ctl form_field" name="t_name" id="t_name" 
                                   type="text" bind_row_type="label" bind_row_id="TNAME_" value="">
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_type">Тип:</label>
                            <input class="form_field" type="hidden" name="t_type" id="t_type" 
                                   bind_row_type="value" bind_row_id="T_TYPE_" value="">
                            <input type="text" name="t_type_name" class="dialog_ctl form_field txt" value=""
                                   autocomplete="off" bound_id="t_type" ac_src="/wimm2/ac_ref.php" 
                                   ac_params="type=t_type;filter=" id="t_type_txt" scroll_height="10"
                                   bind_row_type="title" bind_row_id="TNAME_">
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_curr">Валюта:</label>
                            <input type="hidden" name="t_curr" id="t_curr" class="form_field" value=""
                                   bind_row_type="value" bind_row_id="T_CURR_">
                            <input type="text" class="dialog_ctl form_field txt" value=""
                                   autocomplete="off" bound_id="t_curr" ac_src="/wimm2/ac_ref.php" 
                                   ac_params="type=t_curr;filter=" id="t_curr_txt"
                                   bind_row_type="title" bind_row_id="T_CURR_">
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_sum">Сумма:</label>
                            <input class="dialog_ctl form_field" id="t_sum" name="t_sum" 
                                   type="text" value="" bind_row_type="title" bind_row_id="T_SUMM_">
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_date">Дата:</label>
                            <input class="dtp dialog_ctl form_field" id="t_date" 
                                   name="t_date" type="text" value="" bind_row_type="title" 
                                   pattern="^[0-9]{4,4}-([0][1-9]|[1][0-2])-([0][1-9]|[1-2][0-9]|[3][0-1]) ([0-1][0-9]|[2][0-3]):[0-5][0-9]:[0-5][0-9]$" 
                                   bind_row_id="T_DATE_">
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_place">Место:</label>
                            <input type="hidden" name="t_place" id="t_place" class="form_field" value=""
                                   bind_row_type="value" bind_row_id="T_PLACE_">
                            <input type="text" class="dialog_ctl form_field txt" value=""
                                   autocomplete="off" bound_id="t_place" ac_src="/wimm2/ac_ref.php" 
                                   ac_params="type=t_place;filter=" id="t_place_txt"
                                   bind_row_type="label" bind_row_id="TP_NAME_">
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_budget">Бюджет:</label>
                            <input type="hidden" name="t_budget" id="t_budget" class="form_field" value=""
                                   bind_row_type="value" bind_row_id="T_BUDG_">
                            <input type="text" class="dialog_ctl form_field txt" value=""
                                   autocomplete="off" bound_id="t_budget" ac_src="/wimm2/ac_ref.php" 
                                   ac_params="type=t_budget;filter=" id="t_budget_txt"
                                   bind_row_type="title" bind_row_id="T_BUDG_">
                        </div>
                </DIV>
                <DIV class="dlg_box_btns" id="dlg_box_btns">
                    <input id="OK_BTN" type="button" value="Сохранить" onclick="tx_submit('/wimm2/wimm_edit2.php');">
                    <input id="DEL_BTN" type="button" value="Удалить" onclick="del_click2()">
                    <input type="button" value="Отмена" onclick="doCancel2();">
                </DIV>
            </DIV>
<?php
	$bg = getRequestParam("f_budget","-1");
	print_buttons($conn, $bd, $ed, $bg);
        $tb = new table();
        $tb->setValue(tbase::$PN_CLASS, "visual");
        $tb->setIndent(3);
        // header
        $tc = new tcol("Описание");
        $tc->setValue("WIDTH", "38%");
        $tb->addColumn($tc, TRUE);
        $tc = new tcol("Сумма");
        $tc->setValue("WIDTH", "10%");
        $tb->addColumn($tc, TRUE);
        $tc = new tcol("Дата и время");
        $tc->setValue("WIDTH", "15%");
        $tb->addColumn($tc, TRUE);
        $tc = new tcol("Кто");
        $tc->setValue("WIDTH", "17%");
        $tb->addColumn($tc, TRUE);
        $tc = new tcol("Где");
        $tc->setValue("WIDTH", "20%");
        $tb->addColumn($tc, TRUE);
        // body
        $tb->body->setValue(tbody::$PN_ROW_CLASS, "expenses");
        $fmt_str = "<input class='row_sel' name=\"ROW_ID\" ID=\"=transaction_id\" type=\"radio\" value=\"=transaction_id\">" .
                "<LABEL TITLE=\"=t_type_name\" id=\"TNAME_=transaction_id\" FOR=\"=transaction_id\">=transaction_name</LABEL>";
        $tb->addColumn(new tcol($fmt_str), FALSE);
        $fmt_str2 = '<LABEL class="=tl_class" ID="T_SUMM_=transaction_id" FOR="=transaction_id" title="=transaction_sum">=sum_txt</LABEL>'.
                        '<input type="hidden" id="T_TYPE_=transaction_id" value="=t_type_id">'.
                        '<input type="hidden" title="=currency_name" id="T_CURR_=transaction_id" value="=t_cid">';
        $tb->addColumn(new tcol($fmt_str2), FALSE);
        $tb->addColumn(new tcol('<label id="T_DATE_=transaction_id" for="=transaction_id" TITLE="=transaction_date">=disp_date</label>'), FALSE);
        $tb->addColumn(new tcol("<label id=\"L_USR_=transaction_id\"FOR=\"=transaction_id\">=user_name</label>" .
                        "<input type=\"hidden\" id=\"T_USR_=transaction_id\" value=\"=user_id\">"), FALSE);
        $tb->addColumn(new tcol("<label id=\"TP_NAME_=transaction_id\" TITLE=\"=place_descr\" FOR=\"=transaction_id\">=place_name</label>".
                        "<input type=\"hidden\" id=\"T_PLACE_=transaction_id\" value=\"=place_id\">".
                        "<input type=\"hidden\" id=\"T_BUDG_=transaction_id\" value=\"budget_id\" title=\"=budget_name\">"), FALSE);
?>
            <input type="hidden" id="bg" value="<?php echo $bg;?>">
<?php
    echo $tb->htmlOpen();
	//print "<TR><TD COLSPAN=\"6\">Подключён</TD></TR>\n";
        $a_vg = false;
	$sql = "select transaction_id, t_type_name, transaction_name, transaction_sum, type_sign, transaction_date, user_name, place_name, " .
                " place_descr, t.currency_id t_cid, mcu.currency_name, mcu.currency_sign, mb.currency_id as bc_id, " .
                " t.place_id, t.budget_id, t.user_id, t.t_type_id, mb.budget_name " .
                " from m_transactions t, m_transaction_types tt, m_users tu, m_places tp, m_currency mcu, m_budget mb " .
                " where t.t_type_id=tt.t_type_id and t.user_id=tu.user_id and t.place_id=tp.place_id and t.currency_id=mcu.currency_id and " .
                " t.budget_id=mb.budget_id and #TODATE#(transaction_date#ISO_DATETIME#)>=#TODATE#('$bd'#ISO_DATE#) and  #TODATE#(transaction_date#ISO_DATETIME#)<#TODATE#('$ed'#ISO_DATE#) ";
	if($bg>0)	{
		//print "<TR><TD COLSPAN=\"6\">budget_id=$bg</TD></TR>\n";
		$sql .= " and t.budget_id=$bg ";
	}
	$sql .= "order by transaction_date";
        $res = $conn->query(formatSQL($conn, $sql));
	//$res = mysql_query($sql,$conn);
	$sm = 0;
	$sd = 0;
	$plus_pict = "picts/plus.gif";
	$minus_pict = "picts/minus.gif";
	$locale_info = localeconv();
	if($res)	{		//print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
            while ($row =  $res->fetch(PDO::FETCH_ASSOC)) {
                $cid = $row['t_cid'];
                $bid = $row['bc_id'];
                if($cid!=$bid) {
                    $row['sum_txt'] = $row['currency_sign'] . number_format(
                            f_get_exchange_rate($conn, $row['t_cid'],$row['transaction_date'],$row['transaction_sum'] ),2,","," ");
                }
                else    {
                    $row['sum_txt'] = number_format($row['transaction_sum'],2,","," ");
                }
                $ts = $row['type_sign'];
                if($ts>0)	{
                    $row['tl_class'] = "tl_plus";
                    $pn = $plus_pict;
                    $sd += $row['transaction_sum'];
                }
                else	if($ts<0)	{
                    $row['tl_class'] = "tl_minus";
                    $pn = $minus_pict;
                    $sm += $row['transaction_sum'];
                }
                else	{
                    $row['tl_class'] = "tl_none";
                        $pn = "";
                }
                $row['disp_date'] = f_get_disp_date($row['transaction_date']);
                echo $tb->htmlRow($row);
            }	
        }
	else	{
            $message  = f_get_error_text($conn, "Invalid query: ");
            print "<TR><TD COLSPAN=\"6\">$message - $sql</TD></TR>\n";
	}
	print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"Запрос выполнен " . date("d.m.Y H:i:s") . "\" ALIGN=\"RIGHT\">";
	$t = number_format($sd,2,","," ");
        if($sd>0)
            $c_class = "tl_plus";
        else
            $c_class = "tl_none";
	print "Итого, доходы:</TD><TD COLSPAN=\"4\"><LABEL class=\"$c_class\">$t</LABEL></TD></TR>" . PHP_EOL;
	print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" ALIGN=\"RIGHT\">";
	$t = number_format($sm,2,","," ");
        if($sm>0)
            $c_class = "tl_minus";
        else
            $c_class = "tl_none";
	print "Итого, расходы:</TD><TD COLSPAN=\"4\"><LABEL class=\"$c_class\">$t</LABEL></TD></TR>" . PHP_EOL;
	$sr = $sd - $sm;
	$c_class = "white_bold";
	if($sr>0)
        {
            $c_class = "tl_plus";
            $t = number_format($sr,2,","," ");
        }
	else
        {
            if($sr<0)
                $c_class = "tl_minus";
            else
                $c_class = "tl_none";
            $t = number_format($sr*-1,2,","," ");
        }
	print "<TR  class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"Расходы - Доходы\" ALIGN=\"RIGHT\">";
	print "Итого, разница:</TD><TD COLSPAN=\"4\"><LABEL class=\"$c_class\">$t</LABEL></TD></TR>" . PHP_EOL;
	echo $tb->htmlClose();
	print_buttons($conn);
        if($a_vg)   {
            $a_ctls = array('transaction_name'=>'t_name_a',
                'transaction_sum'=>'t_sum_a',
                'transaction_date'=>'t_date_a',
                't_cid'=>'t_curr_a',
                't_type_id'=>'t_type_a',
                'place_id'=>'t_place_a',
                'budget_id'=>'t_budget_a');
            foreach ($a_vg as $vkey => $vvalue) {
                if(key_exists($vkey, $a_ctls))   {
                    $a2 = array_flip($vvalue);
                    printf('<input type="hidden" id="%s" value="%s">%s', 
                            $a_ctls[$vkey], current($a2), PHP_EOL);
                }
            }
        }
}

        echo "<input type='hidden' id='main_sql' value=\"" . formatSQL($conn, $sql) . "\">\n";
?>
        </form>
    </body>

</html>
