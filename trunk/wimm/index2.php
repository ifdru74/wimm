<?php
	if (!ini_get('register_globals')) {
	   $superglobals = array($_SERVER, $_ENV,
	       $_FILES, $_COOKIE, $_POST, $_GET);
	   if (isset($_SESSION)) {
	       array_unshift($superglobals, $_SESSION);
	   }
	   foreach ($superglobals as $superglobal) {
	       extract($superglobal, EXTR_SKIP);
	   }
	}
	session_start();
	include("fun_web.php");
	auth_check('UID');
	print_head("Семейный бюджет");
?>
<body>
    <script language="JavaScript" type="text/JavaScript" src="js/form_common.js"></script>
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
            set_elem_value("HIDDEN_ID", row_id);
            if(row_id.length<1) {
                set_elem_value("OK_BTN", "Добавить");
                set_elem_value("FRM_MODE", "insert");
                set_elem_text("dlg_box_cap","Добавление записи");
                set_elem_value("t_sum", "");
                set_elem_value("t_name", "");
                set_elem_value("t_user", "");
                set_elem_value("t_curr", "");
                set_elem_value("t_date", "");
            }
            else    {
                set_elem_value("OK_BTN", "Изменить");
                set_elem_value("FRM_MODE", "update");
                set_elem_text("dlg_box_cap","Добавление записи");
                field = document.getElementById("t_name");
                field.value = get_elem_text("TNAME[" + row_id + "]");
                field = document.getElementById("t_sum");
                field.value = get_elem_text("T_SUMM[" + row_id + "]");
                field = document.getElementById("t_sum");
                set_elem_text("dlg_box_cap","Изменение записи");
            }

        }
        function doEdit(s1)
        {	if(s1=="add")	{
                        expenses.HIDDEN_ID.value="0";
                        expenses.action="wimm_edit.php";
                        expenses.submit();
                }
                else if(s1=="edit")	{
                        expenses.action="wimm_edit.php";
                        coll = expenses.elements;
                        if(expenses.FRM_MODE.value==="update" && expenses.HIDDEN_ID.value.length>0)
                                expenses.submit();
                        else
                                alert("Запись для редактирования не выбрана");
                }	else if(s1=="del")	{
                        coll = expenses.elements;
                        for(i=0; i<coll.length; i++)             {
                                v = coll.item(i);
                                s_1 = v.name.substr(0,2);
                                s2 = v.id;
                                if(s_1=="ID")	{
                                        if(v.checked)	{
                                                expenses.HIDDEN_ID.value=s2;
                                                expenses.FRM_MODE.value="delete";
                                                break;
                                        }
                                }
                        }
                        if(expenses.FRM_MODE.value=="delete")
                                expenses.submit();
                        else
                                alert("Запись для удаления не выбрана");
                }	else if(s1=="exit")	{
                        expenses.action="wimm_exit.php";
                        expenses.submit();
                }
        }
    </script>
<?php
include ("fun_mysql.php");
function print_buttons($bd="",$ed="", $bg="-1")
{	
    print "<TABLE WIDTH=\"100%\" class=\"hidden\">\n";
    if(strlen($bd)>0)	{
            print "\t<TR class=\"hidden\">\n";
            print "\t\t<TD class=\"hidden\" COLSPAN=\"2\">Дата начала периода:<input name=\"BDATE\" type=\"text\" value=\"$bd\"></TD>\n";
            print "\t\t<TD class=\"hidden\" COLSPAN=\"2\">Дата окончания периода:<input name=\"EDATE\" type=\"text\" value=\"$ed\"></TD>\n";
            print "\t\t<TD class=\"hidden\" COLSPAN=\"2\">Бюджет:<select size=\"1\" name=\"f_budget\">\n";
            $sql = "SELECT budget_id, budget_name FROM m_budget WHERE close_date is null";
            f_set_sel_options2($sql, $bg, 1, 2);
            print "</select></TD>\n";
            print "\t</TR>\n";
    }
    print "\t<TR class=\"hidden\">\n";
    print "\t\t<TD class=\"hidden\"><input type=\"submit\" value=\"Обновить\"></TD>\n";
    print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Добавить\" onclick=\"sel_row('')\"></TD>\n";
    print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Изменить\" onclick=\"doEdit('edit')\"></TD>\n";
    print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Удалить\" onclick=\"doEdit('del')\"></TD>\n";
    print "\t\t<TD class=\"hidden\"><input type=\"reset\" value=\"Снять выделение\"></TD>\n";
    print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Выход\" onclick=\"doEdit('exit')\"></TD>\n";
    print "\t</TR>\n";
    print "</TABLE>\n";
}
$conn = f_get_connection();
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
			//print "$fm - Fuck $s in ,\n";
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
	print "<form name=\"expenses\" method=\"post\">\n";
	if(strlen($sql)>0)	{
		print "	<input name=\"SQL\" type=\"hidden\" value=\"$sql\">\n";
		mysql_query($sql, $conn);
		mysql_query("commit",$conn);
	}
	print "<input id=\"FRM_MODE\" name=\"FRM_MODE\" type=\"hidden\" value=\"refresh\">\n";
	print "<input id=\"HIDDEN_ID\" name=\"HIDDEN_ID\" type=\"hidden\" value=\"0\">\n";
	print "<input name=\"UID\" type=\"hidden\" value=\"" . $_REQUEST["UID"] ."\">\n";
        print "\t<DIV class=\"dlg_box\" id=\"dialog_box\" style=\"width:600px;display:none;\">\n";
        print "\t\t<DIV class=\"dlg_box_cap\" id=\"dlg_box_cap\">Изменение записи</DIV>\n";
        print "\t\t<DIV class=\"dlg_box_text\" id=\"dlg_box_text\" >\n";
	print "<TABLE WIDTH=\"100%\" class=\"hidden\">\n";
	print "<TR class=\"hidden\">\n";
	print "<TD WIDTH=\"30%\" class=\"hidden\">Пользователь:</TD><TD class=\"hidden\"><select size=\"1\" id=\"t_user\" name=\"t_user\">\n";
	$sql = "select user_id, user_name from m_users where close_date is null";
	f_set_sel_options2($sql, $s, $s, 2);
	print "</select></TD></TR>\n";
	print "<TR class=\"hidden\">\n";
	print "<TD WIDTH=\"30%\" class=\"hidden\">Наименование:</TD><TD class=\"hidden\"><input name=\"t_name\" id=\"t_name\" type=\"text\" value=\"$s\"></TD>\n";
	print "</TR>\n";
	print "<TR class=\"hidden\">\n";
	print "<TD class=\"hidden\">Тип:</TD><TD class=\"hidden\"><select size=\"1\" name=\"t_type\">\n";
	$sql = "SELECT t_type_id, t_type_name FROM m_transaction_types  WHERE close_date is null";
	f_set_sel_options2($sql, $s, 1, 2);
	print "</select></TD>\n";
	print "</TR>\n";
	print "<TR class=\"hidden\">\n";
	print "<TD class=\"hidden\">Валюта:</TD><TD class=\"hidden\"><select size=\"1\" name=\"t_curr\">\n";
	$sql = "SELECT currency_id, concat(currency_name,' (',currency_abbr,')') as c_name FROM m_currency WHERE close_date is null";
	f_set_sel_options2($sql, $s, 2, 2);
	print "</select></TD>\n";
	print "</TR>\n";
	print "<TR class=\"hidden\">\n";
	print "<TD class=\"hidden\">Сумма:</TD><TD class=\"hidden\"><input id=\"t_sum\" name=\"t_sum\" type=\"text\" value=\"$s\"></TD>\n";
	print "</TR>\n";
	print "<TR class=\"hidden\">\n";
	print "<TD class=\"hidden\">Дата:</TD><TD class=\"hidden\"><input id=\"t_date\" name=\"t_date\" type=\"text\" value=\"$s\"></TD>\n";
	print "</TR>\n";
	print "<TR class=\"hidden\">\n";
	print "<TD class=\"hidden\">Место:</TD><TD class=\"hidden\"><select size=\"1\" id=\"t_place\" name=\"t_place\">\n";
	$sql = "SELECT place_id, place_name FROM m_places WHERE close_date is null";
	f_set_sel_options2($sql, $s, 1, 2);
	print "</select></TD>\n";
	print "</TR>\n";

	print "<TR class=\"hidden\">\n";
	print "<TD class=\"hidden\">Бюджет:</TD><TD class=\"hidden\"><select size=\"1\" id=\"t_budget\" name=\"t_budget\">\n";
	$sql = "SELECT budget_id, budget_name FROM m_budget WHERE close_date is null";
	f_set_sel_options2($sql, $s, 1, 2);
	print "</select></TD>\n";
	print "</TR>\n";

	print "</TABLE>\n";
        print "\t\t</DIV>\n";
        print "\t\t<DIV class=\"dlg_box_btns\" id=\"dlg_box_btns\">\n";
        print "<input id=\"OK_BTN\" type=\"submit\" value=\"ОК\">\n";
        print "<input type=\"button\" value=\"Отмена\" onclick=\"hide_elem('dialog_box');\">\n";
        print "\t\t</DIV>\n";
        print "\t</DIV>";
	$bg = getRequestParam("f_budget","-1");
	print_buttons($bd,$ed,$bg);
	print "<TABLE WIDTH=\"100%\" BORDER=\"1\">\n";
	print "<TR>\n";
	print "<TH WIDTH=\"5%\">&nbsp</TH>\n";
	print "<TH WIDTH=\"33%\">Описание</TH>\n";
	print "<TH WIDTH=\"10%\">Сумма</TH>\n";
	print "<TH WIDTH=\"15%\">Дата и время</TH>\n";
	print "<TH WIDTH=\"17%\">Кто</TH>\n";
	print "<TH WIDTH=\"20%\">Где</TH>\n";
	print "</TR>\n";
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
	$res = mysql_query($sql,$conn);
	$sm = 0;
	$sd = 0;
	$c_class = "dark";
	$plus_pict = "picts/plus.gif";
	$minus_pict = "picts/minus.gif";
	$locale_info = localeconv();
	if($res)	{		//print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
		while ($row = mysql_fetch_assoc($res)) {
                        print "<TR class=\"$c_class\">\n";
			if(strcmp($c_class,"dark")==0)	{
				$c_class = "white";
			}
			else	{
				$c_class = "dark";
			}
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
                            $s = f_get_exchange_rate($row['t_cid'],$row['transaction_date'],$row['transaction_sum'] );
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
		}	}
	else	{		$message  = f_get_error_text($conn, "Invalid query: ");
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
	print "</TABLE>\n";
	print_buttons();
	print "</form>\n";
}

?>
</body>

</html>
