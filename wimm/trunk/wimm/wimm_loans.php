<!DOCTYPE html>
<?php
include ("fun_mysql.php");
function print_buttons($bd="",$ed="", $bg="-1")
{	print "<TABLE WIDTH=\"100%\" class=\"hidden\">\n";
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
	print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Добавить\" onclick=\"doEdit('add')\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Изменить\" onclick=\"doEdit('edit')\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Удалить\" onclick=\"doEdit('del')\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"reset\" value=\"Снять выделение\"></TD>\n";
	print "\t\t<TD class=\"hidden\"><input type=\"button\" value=\"Выход\" onclick=\"doEdit('exit')\"></TD>\n";
	print "\t</TR>\n";
	print "</TABLE>\n";
}
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
	print_head("Кредиты");
?>
<script language="JavaScript" type="text/JavaScript">
function doSel(s1, s2, s3)
{
	sid=loans.HIDDEN_ID.value;
	coll = loans.elements;
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
	loans.HIDDEN_ID.value=s1;
	loans.p_name.value=s2;
	loans.p_descr.value=s3;
}

function doEdit(s1)
{
	if(s1=="add")	{
		loans.FRM_MODE.value="insert";
                loans.action="wimm_loan_edit.php";
		loans.HIDDEN_ID.value="0";
		loans.submit();
	}
	else if(s1=="edit")	{
                loans.action="wimm_loan_edit.php";
		coll = loans.elements;
		for(i=0; i<coll.length; i++)             {
			v = coll.item(i);
			s_1 = v.name.substr(0,2);
			s2 = v.id;
			if(s_1=="ID")	{
				if(v.checked)	{
					loans.HIDDEN_ID.value=s2;
					loans.FRM_MODE.value="update";
					break;
				}
			}
		}
		if(loans.FRM_MODE.value=="update")
			loans.submit();
		else
			alert("Запись для редактирования не выбрана");
	}	else if(s1=="del")	{
		coll = loans.elements;
		for(i=0; i<coll.length; i++)             {
			v = coll.item(i);
			s_1 = v.name.substr(0,2);
			s2 = v.id;
			if(s_1=="ID")	{
				if(v.checked)	{
					loans.HIDDEN_ID.value=s2;
					loans.FRM_MODE.value="delete";
					break;
				}
			}
		}
		if(loans.FRM_MODE.value=="delete")
			loans.submit();
		else
			alert("Запись для удаления не выбрана");
	}	else if(s1=="exit")	{
		loans.FRM_MODE.value="return";
		loans.action="index.php";
		loans.submit();
	}
}
</script>
    <body>
        <?php
        // put your code here
        $fm = getRequestParam("FRM_MODE","refresh");
        print "<input type=hidden name=form_mode_from value=\"$fm\">\n";
        $conn = f_get_connection();
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
	print_body_title("Кредиты, активные с $bd по $ed");
	print "<form name=\"loans\" action=\"wimm_loans.php\" method=\"post\">\n";
	if(strlen($sql)>0)	{
		print "	<input name=\"SQL\" type=\"hidden\" value=\"$sql\">\n";
		$conn->query($sql);
		$conn->commit();
	}
	print "<input name=\"FRM_MODE\" type=\"hidden\" value=\"refresh\">\n";
	print "<input name=\"HIDDEN_ID\" type=\"hidden\" value=\"0\">\n";
	print "<input name=\"UID\" type=\"hidden\" value=\"" . $_REQUEST["UID"] ."\">\n";
	$bg = getRequestParam("f_budget","-1");
	print_buttons($bd,$ed,$bg);
 	print "<TABLE WIDTH=\"100%\" BORDER=\"1\">\n";
	print "<TR>\n";
	print "<TH WIDTH=\"5%\">&nbsp</TH>\n";
	print "<TH WIDTH=\"20%\">Наименование</TH>\n";
	print "<TH WIDTH=\"10%\">Сумма</TH>\n";
	print "<TH WIDTH=\"15%\">Взят</TH>\n";
	print "<TH WIDTH=\"15%\">Срок до</TH>\n";
	print "<TH WIDTH=\"15%\">Кто</TH>\n";
	print "<TH WIDTH=\"20%\">Где</TH>\n";
	print "</TR>\n";
        $sql = "select loan_id, loan_name, loan_sum, start_date, end_date, user_name, ".
                "place_name, ml.currency_id t_cid, mcu.currency_sign, mb.currency_id as bc_id " .
                "from m_loans ml, m_users mu, m_places mp, m_currency mcu, m_budget mb ".
                "where ml.user_id=mu.user_id and ml.place_id=mp.place_id and ".
                "ml.currency_id=mcu.currency_id and ml.budget_id=mb.budget_id and ".
                "ml.close_date is null";
	if($bg>0)	{
		$sql .= " and ml.budget_id=$bg ";
	}
	$sql .= " order by end_date";
        print "	<input name=\"SQL2\" type=\"hidden\" value=\"$sql\">\n";
	$res = $conn->query($sql);
	$sm = 0;
	$sd = 0;
	$c_class = "dark";
	if($res)	{		//print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                    print "<TR class=\"$c_class\">\n";
			if(strcmp($c_class,"dark")==0)	{
				$c_class = "white";
			}
			else	{
				$c_class = "dark";
			}
			$s = $row['loan_id'];
			print "<TD><input name=\"ID$s\" ID=\"$s\"type=\"radio\" value=\"$s\"></TD>\n";			$t = $row['t_type_name'];
			$s = $row['loan_name'];
			print "<TD>$s</TD>\n";

                        $cid = $row['t_cid'];
                        $bid = $row['bc_id'];
                        if($cid!=$bid) {
                            $cs = $row['currency_sign'];
                            $s = f_get_exchange_rate($row['t_cid'],$row['start_date'],$row['loan_sum'] );
                        }
                        else    {
                            $cs = "";
                            $s = $row['loan_sum'];
                        }
                        
                        $sd += $s;
                        $t = number_format($s,2,","," ");
			print "<TD>$t</TD>\n";
			$t = $row['start_date'];
			$s = f_get_disp_date($t);
			print "<TD TITLE=\"$t\">$s</TD>\n";
			$t = $row['end_date'];
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
	print "Итого, набрали кредитов на:</TD><TD COLSPAN=\"5\">&nbsp;$t</TD></TR>\n";
	$t = number_format($sm,2,","," ");
	print "</TABLE>\n";
	print_buttons();
	print "</form>\n";
       ?>
    </body>
</html>
