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
	print_head("Отчёт по затратам в нескольких разрезах");
?>
<script language="JavaScript" type="text/JavaScript">
function doEdit(s1)
{	if(s1=="exit")	{
		expenses.action="index.php";
		expenses.submit();
	}
}
</script>
<body>
<?php
include ("fun_mysql.php");
function print_buttons($bd="",$ed="", $bg="-1")
{	print "<TABLE WIDTH=\"100%\" class=\"hidden\">\n";
	print "\t<TR class=\"hidden\">\n";
	if(strlen($bd)>0)	{
		print "\t\t<TD class=\"hidden\">Дата начала периода:<input name=\"BDATE\" type=\"text\" value=\"$bd\"></TD>\n";
		print "\t\t<TD class=\"hidden\">Дата окончания периода:<input name=\"EDATE\" type=\"text\" value=\"$ed\"></TD>\n";
		print "\t\t<TD class=\"hidden\">Бюджет:<select size=\"1\" name=\"f_budget\">\n";
		$sql = "SELECT budget_id, budget_name FROM m_budget WHERE close_date is null";
		f_set_sel_options2($sql, $bg, 1, 2);
		print "</select></TD>\n";
	}
	print "\t\t<TD class=\"hidden\"><input type=\"submit\" value=\"Обновить\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Выход\" onclick=\"doEdit('exit')\"></TD>\n";
	print "\t</TR>\n";
	print "</TABLE>\n";
}
$conn = f_get_connection();
if($conn)	{
	$fm = getRequestParam("FRM_MODE","refresh");
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
	print_body_title("Где потратили деньги с $bd по $ed");
	print "<form name=\"expenses\" action=\"wimm_report.php\" method=\"post\">\n";
	if(strlen($sql)>0)	{
		print "	<input name=\"SQL\" type=\"hidden\" value=\"$sql\">\n";
		mysql_query($sql, $conn);
		mysql_query("commit",$conn);
	}
	print "<input name=\"FRM_MODE\" type=\"hidden\" value=\"refresh\">\n";
	$bg = getRequestParam("f_budget","-1");
	print_buttons($bd,$ed,$bg);
	print "<TABLE WIDTH=\"100%\" BORDER=\"1\">\n";
	print "<TR>\n";
	print "<TH>Место</TH>\n";
	print "<TH>Сумма</TH>\n";
	print "<TH TITLE=\"относится к последней покупке\">Дата и время</TH>\n";
	print "<TH>Количество</TH>\n";
	print "</TR>\n";
	//print "<TR><TD COLSPAN=\"6\">Подключён</TD></TR>\n";
	$sql = "select mtp.place_name, sum(transaction_sum) as s_um, max(mt.transaction_date) as ltd, count(*) as cnt from m_transactions mt, m_transaction_types  mtt, m_places mtp where mt.t_type_id=mtt.t_type_id and mtt.Type_sign<0 and mt.place_id=mtp.place_id and transaction_date>='$bd' and  transaction_date<'$ed' ";
	if($bg>0)	{
		$sql .= " and mt.budget_id=$bg ";
	}
	$sql .= " group by mt.place_id order by 2 desc";
	$res = $conn->query($sql);
	$sm = 0;
	$sd = 0;
	$c_class = "dark";
	$plus_pict = "picts/plus.gif";
	$minus_pict = "picts/minus.gif";
	if($res)	{		//print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                print "<TR class=\"$c_class\">\n";
                if(strcmp($c_class,"dark")==0)	{
                    $c_class = "white";
                }
                else	{
                    $c_class = "dark";
                }
                $s = $row['place_name'];
                print "<TD>$s</TD>\n";
                $s = $row['s_um'];
                print "<TD>$s</TD>\n";
                $s = $row['ltd'];
                print "<TD>$s</TD>\n";
                $s = $row['cnt'];
                print "<TD>$s</TD>\n";
                print "</TR>\n";
            }
        }
	else	{
            $message  = f_get_error_text($conn, "Invalid query: ");
            print "<TR><TD COLSPAN=\"6\">SQL=\"$sql\"<BR>$message</TD></TR>\n";
	}
	print "</TABLE>\n";
	print_title("На что потратили деньги с $bd по $ed");
	print "<TABLE WIDTH=\"100%\" BORDER=\"1\">\n";
	print "<TR>\n";
	print "<TH>Статья расходов</TH>\n";
	print "<TH>Сумма</TH>\n";
	print "<TH TITLE=\"относится к последней покупке\">Дата и время</TH>\n";
	print "<TH>Количество</TH>\n";
	print "</TR>\n";
	//print "<TR><TD COLSPAN=\"6\">Подключён</TD></TR>\n";
	$sql = "select mtt.t_type_name, sum(transaction_sum) as s_um, max(mt.transaction_date) as ltd, count(*) as cnt from m_transactions mt, m_transaction_types  mtt where mt.t_type_id=mtt.t_type_id and mtt.Type_sign<0 and transaction_date>='$bd' and  transaction_date<'$ed' ";
	if($bg>0)	{
		$sql .= " and mt.budget_id=$bg ";
	}
	$sql .= " group by mt.t_type_id order by 2 desc";
	$res =$conn->query($sql);
	$sm = 0;
	$sd = 0;
	$c_class = "dark";
	if($res)	{
            //print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                print "<TR class=\"$c_class\">\n";
                if(strcmp($c_class,"dark")==0)	{
                    $c_class = "white";
                }
                else	{
                    $c_class = "dark";
                }
                $s = $row['t_type_name'];
                print "<TD>$s</TD>\n";
                $s = $row['s_um'];
                print "<TD>$s</TD>\n";
                $s = $row['ltd'];
                print "<TD>$s</TD>\n";
                $s = $row['cnt'];
                print "<TD>$s</TD>\n";
                print "</TR>\n";
            }
        }
	else	{
            $message  = f_get_error_text($conn, "Invalid query: ");
            print "<TR><TD COLSPAN=\"6\">SQL=\"$sql\"<BR>$message</TD></TR>\n";
	}
	print "</TABLE>\n";
	print_title("Потребительская активность с $bd по $ed");
	print "<TABLE WIDTH=\"100%\" BORDER=\"1\">\n";
	print "<TR>\n";
	print "<TH>Кто</TH>\n";
	print "<TH>Сумма</TH>\n";
	print "<TH TITLE=\"относится к последней покупке\">Дата и время</TH>\n";
	print "<TH>Количество</TH>\n";
	print "</TR>\n";
	//print "<TR><TD COLSPAN=\"6\">Подключён</TD></TR>\n";
	$sql = "select mu.user_name, sum(transaction_sum) as s_um, max(mt.transaction_date) as ltd, count(*) as cnt, mtt.Type_sign from m_transactions mt, m_users mu, m_transaction_types mtt where mt.user_id=mu.user_id and mt.t_type_id=mtt.t_type_id and transaction_date>='$bd' and transaction_date<'$ed' ";
	if($bg>0)	{
		$sql .= " and mt.budget_id=$bg ";
	}
	$sql .= " group by mt.user_id, mtt.Type_sign order by mu.user_name";
	$res = $conn->query($sql);
	$sm = 0;
	$sd = 0;
	$c_class = "dark";
	if($res)	{
            //print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                print "<TR class=\"$c_class\">\n";
                if(strcmp($c_class,"dark")==0)	{
                    $c_class = "white";
                }
                else	{
                    $c_class = "dark";
                }
                $s = $row['user_name'];
                print "<TD>$s</TD>\n";
                $s = $row['s_um'];
                print "<TD>";
                $t = $row['Type_sign'];
                if($t<0)
                        print "<IMG SRC=\"$minus_pict\">";
                else if($t>0)
                        print "<IMG SRC=\"$plus_pict\">";
                print "$s</TD>\n";
                $s = $row['ltd'];
                print "<TD>$s</TD>\n";
                $s = $row['cnt'];
                print "<TD>$s</TD>\n";
                print "</TR>\n";
            }
        }
	else	{
            $message  = f_get_error_text($conn, "Invalid query: ");
            print "<TR><TD COLSPAN=\"6\">SQL=\"$sql\"<BR>$message</TD></TR>\n";
	}
	print "</TABLE>\n";
	print_buttons();
	print "</form>\n";
}

?>
</body>

</html>
