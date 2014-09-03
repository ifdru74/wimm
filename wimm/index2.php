<?php
    include_once ("fun_web.php");
    include_once ("fun_mysql.php");
    init_superglobals();
    session_start();
    //auth_check('UID');
    /**
     * @var $conn PDO 
     */
    $conn = f_get_connection();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="windows-1251">
        <link rel="STYLESHEET" href="css/wimm.css" type="text/css"/>
        <link rel="SHORTCUT ICON" href="picts/favicon.ico">
        <title>Семейный бюджет</title>
    </head>
<body>
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
    <script language="JavaScript" type="text/JavaScript">
        function sel_row(row_id)
        {
            var objDiv = document.getElementById("dialog_box");
            objDiv.style.top = (f_get_scroll_y()+200).toString()+"px";
            //var x = (window.innerWidth||document.body.clientWidth);
            var x = (f_get_scroll_x()-600)/2+500;
            if(x<0)
                x = 500;
            objDiv.style.left = x.toString()+"px";
            objDiv.style.display="inline";
            var s1;
            if(row_id.length<1) {
                $("#HIDDEN_ID").val(0);
                $("#OK_BTN").val("Добавить");
                $("#FRM_MODE").val("insert");
                $("#dlg_box_cap").text("Добавление записи");
                s1 = "";
                $("#t_sum").val(s1);
                $("#t_curr").val(s1);
                $("#t_date").val(s1);
                s1 = "<?php echo getRequestParam("UID", "");?>";
                $("#t_user").val(s1);
            }
            else    {
                $("#HIDDEN_ID").val(row_id);
                $("#OK_BTN").val("Изменить");
                $("#FRM_MODE").val("update");
                $("#dlg_box_cap").text("Изменение записи");
                $("#t_name").val($("TNAME[" + row_id + "]").text());
                $("#t_sum").val($("T_SUMM[" + row_id + "]").text());
            }
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
            <input id="BDATE" name="BDATE" type="text" value="<?php echo $bd;?>">
            <label for="EDATE">Дата окончания периода:</label>
            <input id="EDATE" name="EDATE" type="text" value="<?php echo $ed;?>">
            <label for="f_budget">Бюджет:</label>
            <select size="1" id="f_budget" name="f_budget">
<?php
            $sql = "SELECT budget_id, budget_name FROM m_budget WHERE close_date is null";
            f_set_sel_options2($conn, $sql, $bg, 1, 2);
?>
            </select>
        </div>
<?php
    }
?>
        <div style="display: block; width: 100%;">
            <input type="submit" value="Обновить">
            <input type="button" value="Добавить" onclick="sel_row('');">
            <input type="button" value="Удалить" onclick="doEdit('del');">
            <input type="reset" value="Снять выделение">
            <input type="button" value="Выход" onclick="submit_myform('expenses','wimm_exit.php','exit');">
        </div>
    </div>
<?php
}
if($conn)	{
	$fm = getRequestParam("FRM_MODE","refresh");
	$sql = "";
	if(strcmp($fm,"insert")==0)	{
		$sql = "INSERT INTO m_transactions (transaction_name, t_type_id, currency_id, transaction_sum, transaction_date, user_id, open_date, place_id, budget_id) VALUES(";
		$s = value4db(urldecode(getRequestParam("t_name","Покупка!")));
		$sql .= "'$s',";
		$s = getRequestParam("t_type",1);
		$sql .= "$s,";
		$s = getRequestParam("t_curr",2);
		$sql .= "$s,";
		$s1 = getRequestParam("t_sum",0);
		if(strpos($s1,",")===false)	{
			$s = $s1;
		}
		else	{
			$s = str_replace(",",".",$s1);
		}
		$sql .= "$s,";
		$td = getRequestParam("t_date",date("Y-m-d H:i:s"));
		$sql .= "'$td',";
		$s = getRequestParam("t_user",1);
		$sql .= "$s,";
		$sql .= "'$td',";
		$s = getRequestParam("t_place",0);
		$sql .= "$s,";
		$s = getRequestParam("t_budget",0);
		$sql .= "$s)";
	}
        $cd = getdate();
	$m = $cd['mon'];
	$y = $cd['year'];
	if(strlen($m)<2)
		$m = "0" . $m;
	$bd = update_param("BDATE", "BEG_DATE", "$y-$m-01");
	if($m==12)	{
		$m = "01";
		$y ++;
	}
	else	{
		$m ++;
	}
	if(strlen($m)<2)
		$m = "0" . $m;
	$ed = update_param("EDATE", "END_DATE", "$y-$m-01");
	print_body_title("Расходы с $bd по $ed");
	if(strlen($sql)>0)	{
		print "	<input ID=\"SQL\" type=\"hidden\" value=\"$sql\">\n";
		$conn->query($sql);
		$conn->commit();
	}
?>
	<form id="expenses" name="expenses" method="post">
            <input id="FRM_MODE" name="FRM_MODE" type="hidden" value="refresh">
            <input id="HIDDEN_ID" name="HIDDEN_ID" type="hidden" value="0">
            <input name="UID" type="hidden" value="<?php echo getRequestParam("UID", "");?>">
            <DIV class="dlg_box" id="dialog_box" style="width:600px;display:none;">
                <DIV class="dlg_box_cap" id="dlg_box_cap">Изменение записи</DIV>
                <DIV class="dlg_box_text" id="dlg_box_text" >
                    <TABLE WIDTH="100%" class="hidden">
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_user">Пользователь:</label>
                            <select class="dialog_ctl" size="1" id="t_user" name="t_user">
<?php
	$sql = "select user_id, user_name from m_users where close_date is null";
	f_set_sel_options2($conn, $sql, $s, $s, 2);
?>
                            </select>
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_name">Наименование:</label>
                            <input class="dialog_ctl" name="t_name" id="t_name" type="text" value="">
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_type">Тип:</label>
                            <select class="dialog_ctl" size="1" name="t_type" id="t_type">
<?php
	$sql = "SELECT t_type_id, t_type_name FROM m_transaction_types  WHERE close_date is null";
	f_set_sel_options2($conn, $sql, $s, 1, 2);
?>
                            </select>
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_curr">Валюта:</label>
                            <select class="dialog_ctl" size="1" id="t_curr" name="t_curr">
<?php
	$sql = "SELECT currency_id, concat(currency_name,' (',currency_abbr,')') as c_name FROM m_currency WHERE close_date is null";
	f_set_sel_options2($conn, $sql, $s, 2, 2);
?>
                            </select>
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_sum">Сумма:</label>
                            <input class="dialog_ctl" id="t_sum" name="t_sum" type="text" value="">
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_date">Дата:</label>
                            <input class="dialog_ctl" id="t_date" name="t_date" type="text" value="">
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_place">Место:</label>
                            <select class="dialog_ctl" size="1" id="t_place" name="t_place">
<?php
	$sql = "SELECT place_id, place_name FROM m_places WHERE close_date is null";
	f_set_sel_options2($conn, $sql, $s, 1, 2);
?>
                            </select>
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_budget">Бюджет:</label>
                            <select class="dialog_ctl" size="1" id="t_budget" name="t_budget">
<?php
	$sql = "SELECT budget_id, budget_name FROM m_budget WHERE close_date is null";
	f_set_sel_options2($conn, $sql, $s, 1, 2);
?>
                            </select>
                        </div>
                    </TABLE>
                </DIV>
                <DIV class="dlg_box_btns" id="dlg_box_btns">
                    <input id="OK_BTN" type="submit" value="ОК">
                    <input type="button" value="Отмена" onclick="$('#dialog_box').hide();">
                </DIV>
            </DIV>
<?php
	$bg = getRequestParam("f_budget","-1");
	print_buttons($conn, $bd,$ed,$bg);
?>
            <TABLE WIDTH="100%" BORDER="1">
                <thead>
                    <TR>
                        <TH WIDTH="5%">&nbsp</TH>
                        <TH WIDTH="33%">Описание</TH>
                        <TH WIDTH="10%">Сумма</TH>
                        <TH WIDTH="15%">Дата и время</TH>
                        <TH WIDTH="17%">Кто</TH>
                        <TH WIDTH="20%">Где</TH>
                    </TR>
                </thead>
                <tbody>
<?php
	//print "<TR><TD COLSPAN=\"6\">Подключён</TD></TR>\n";
	$sql = "select transaction_id, t_type_name, transaction_name, transaction_sum, Type_sign, transaction_date, user_name, place_name, " .
                " place_descr, t.currency_id t_cid, mcu.currency_sign, mb.currency_id as bc_id " .
                " from m_transactions t, m_transaction_types tt, m_users tu, m_places tp, m_currency mcu, m_budget mb " .
                " where t.t_type_id=tt.t_type_id and t.user_id=tu.user_id and t.place_id=tp.place_id and t.currency_id=mcu.currency_id and " .
                " t.budget_id=mb.budget_id and transaction_date>='$bd' and  transaction_date<'$ed' ";
	if($bg>0)	{
		//print "<TR><TD COLSPAN=\"6\">budget_id=$bg</TD></TR>\n";
		$sql .= " and t.budget_id=$bg ";
	}
	$sql .= "order by transaction_date";
        $res = $conn->query($sql);
	//$res = mysql_query($sql,$conn);
	$sm = 0;
	$sd = 0;
	$c_class = "dark";
	$plus_pict = "picts/plus.gif";
	$minus_pict = "picts/minus.gif";
	$locale_info = localeconv();
	if($res)	{		//print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
            while ($row =  $res->fetch(PDO::FETCH_ASSOC)) {
                print "<TR class=\"expenses\">\n";
                $row_pk = $row['transaction_id'];
                print "<TD><input name=\"ROW_ID\" ID=\"ROW_$row_pk\" type=\"radio\" value=\"$row_pk\" onclick=\"sel_row('$row_pk');\">";
                print "<input class=\"multiselect\" style=\"display:none;\" name=\"MROW[$row_pk]\" ID=\"CHK_$row_pk\" type=\"radio\" value=\"$row_pk\"></TD>\n";
                $t = $row['t_type_name'];
                $s = $row['transaction_name'];
                print "<TD TITLE=\"$t\"><LABEL id=\"TNAME[$row_pk]\" FOR=\"ROW_$row_pk\">$s</LABEL></TD>\n";
                $cid = $row['t_cid'];
                $bid = $row['bc_id'];
                if($cid!=$bid) {
                    $cs = $row['currency_sign'];
                    $s = f_get_exchange_rate($conn, $row['t_cid'],$row['transaction_date'],$row['transaction_sum'] );
                }
                else    {
                    $cs = "";
                    $s = $row['transaction_sum'];
                }
                $ts = $row['Type_sign'];
                if($ts>0)	{
                        $pn = $plus_pict;
                        $sd += $s;
                }
                else	if($ts<0)	{
                        $pn = $minus_pict;
                        $sm += $s;
                }
                else	{
                        $pn = "";
                }
                $t = number_format($s,2,","," ");
                print "<TD TITLE=\"$t\">";
                if(strlen($pn)>0)	{
                        print "<IMG SRC=$pn>&nbsp;";
                }
                $t = number_format($row['transaction_sum'],2,","," ");
                print "$cs<LABEL ID=\"T_SUMM[$row_pk]\">$t</LABEL></TD>\n";
                $t = $row['transaction_date'];
                $s = f_get_disp_date($t);
                print "<TD TITLE=\"$t\">$s</TD>\n";
                $s = $row['user_name'];
                print "<TD>$s</TD>\n";
                $s = $row['place_name'];
                $t = $row['place_descr'];
                print "<TD TITLE=\"$t\">$s</TD>\n";
                print "</TR>\n";
            }	
        }
	else	{
            $message  = f_get_error_text($conn, "Invalid query: ");
            print "<TR><TD COLSPAN=\"6\">$message</TD></TR>\n";
	}
	print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"Запрос выполнен " . date("d.m.Y H:i:s") . "\" ALIGN=\"RIGHT\">";
	$t = number_format($sd,2,","," ");
	print "Итого, доходы:</TD><TD COLSPAN=\"4\"><IMG SRC=$plus_pict>&nbsp;$t</TD></TR>\n";
	print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" ALIGN=\"RIGHT\">";
	$t = number_format($sm,2,","," ");
	print "Итого, расходы:</TD><TD COLSPAN=\"4\"><IMG SRC=$minus_pict>&nbsp;$t</TD></TR>\n";
	$sr = $sd - $sm;
	$t = number_format($sr,2,","," ");
	$c_class = "white_bold";
	if($sr<0)
		$c_class = $minus_pict;
	else
		$c_class = $plus_pict;
	print "<TR  class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"Расходы - Доходы\" ALIGN=\"RIGHT\">";
	print "Итого, разница:</TD><TD COLSPAN=\"4\"><IMG SRC=\"$c_class\">&nbsp;$t</TD></TR>\n";
	print "</tbody></TABLE>\n";
	print_buttons($conn);
}

?>
                    </form>
</body>

</html>
