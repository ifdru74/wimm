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
	//auth_check('UID');
	$p_title = "Редактор того, на что тратятся деньги";
	print_head($p_title);
?>
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
		ttypes.FRM_MODE.value="insert";
		ttypes.HIDDEN_ID.value="0";
		ttypes.submit();
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
</script>
<body>

<?php
function print_buttons($bd="")
{
	if(strlen($bd)>0)	{
		print "\t<TR class=\"hidden\">\n";
		print "\t\t<TD class=\"hidden\" COLSPAN=\"3\">Наименование:<input name=\"p_name\" type=\"text\" size=\"50\" value=\"\"></TD>\n";
		print "\t\t<TD class=\"hidden\" COLSPAN=\"3\">Родитель:<input name=\"p_descr\" type=\"text\" size=\"50\" value=\"\"></TD>\n";
		print "\t</TR>\n";
	}
	print "<TABLE WIDTH=\"100%\" class=\"hidden\">\n";
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
    include_once 'fun_dbms.php';

	$uid = getSessionParam("UID",0);
	if($uid==0)
		$uid = getRequestParam("UID",1);
	$conn = f_get_connection();
	$fm = getRequestParam("FRM_MODE","refresh");
	$sql = "";
	if(strcmp($fm,"insert")==0)	{
		$sql = "INSERT INTO m_transaction_types (t_type_name, open_date, place_descr, user_id) VALUES(";
		$s = getRequestParam("p_name","Место?");
		$sql .= "'$s',";
		$td = date("Y-m-d H:i:s");
		$sql .= "'$td',";
		$s = getRequestParam("p_descr",1);
		$sql .= "'$s',";
		$sql .= "$uid)";
	}	else if(strcmp($fm,"update")==0)	{
		$sql = "UPDATE m_transaction_types SET ";
		$s = getRequestParam("p_name","Место?");
		$sql .= "t_type_name='$s',";
		$s = getRequestParam("t_type",1);
		$sql .= "place_name='$s' ";
		$sql .= "where t_type_id=";
		$s = getRequestParam("HIDDEN_ID",0);
		$sql .= $s;
	}
	else if(strcmp($fm,"delete")==0)	{
		$s = getRequestParam("HIDDEN_ID",0);
		//$sql = "delete from m_transaction_types where t_type_id=$s";
                $sql = "update m_transaction_types set close_date=NOW() where t_type_id=$s";
	}
	print_body_title($p_title);
	print "<form name=\"ttypes\" action=\"wimm_ttypes.php\" method=\"post\">\n";
	if(strlen($sql)>0)	{
		print "	<input name=\"SQL\" type=\"hidden\" value=\"$sql\">\n";
		//mysql_query($sql, $conn);
		//mysql_query("commit",$conn);
	}
	print "<input name=\"FRM_MODE\" type=\"hidden\" value=\"refresh\">\n";
	print "<input name=\"HIDDEN_ID\" type=\"hidden\" value=\"0\">\n";
	print "<input name=\"UID\" type=\"hidden\" value=\"" . $uid ."\">\n";
	print_buttons("edit_boxes");
	print "<TABLE WIDTH=\"100%\" BORDER=\"1\">\n";
	print "<TR>\n";
	print "<TH WIDTH=\"5%\">&nbsp</TH>\n";
	print "<TH WIDTH=\"45%\">Наименование</TH>\n";
	print "<TH WIDTH=\"10%\">Родитель</TH>\n";
	print "<TH WIDTH=\"5%\">Знак</TH>\n";
	print "<TH WIDTH=\"15%\">Дата создания</TH>\n";
	print "<TH  WIDTH=\"20%\">Кто автор</TH>\n";
	print "</TR>\n";
	$sql = "SELECT t_type_id, t_type_name, parent_type_id, type_sign, mtt.open_date, is_repeat, period, user_name FROM m_transaction_types mtt, m_users mu where mtt.user_id=mu.user_id";
	$res = $conn->query($sql);
	$sm = 0;
	$sd = 0;
	$c_class = "dark";
	if($res)	{
            //print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
            $plus_pict = "picts/plus.gif";
            $minus_pict = "picts/minus.gif";
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                print "<TR class=\"$c_class\">\n";
                if(strcmp($c_class,"dark")==0)	{
                    $c_class = "white";
                }
                else	{
                    $c_class = "dark";
                }
                $s = $row['t_type_id'];
                $sn = $row['t_type_name'];
                $sd = $row['parent_type_id'];
                print "<TD><input name=\"ID$s\" ID=\"$s\"type=\"radio\" value=\"$s\" onclick=\"doSel('$s','$sn','$sd')\">";
                print "</TD>\n";
                $s = $row['t_type_name'];
                print "<TD>$s</TD>\n";
                $s = $row['parent_type_id'];
                print "<TD>$s</TD>\n";
                $s = $row['type_sign'];
                print "<TD TITLE=\"$s\"><CENTER>";
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
                print "<TD>$s</TD>\n";
                $s = $row['user_name'];
                print "<TD>$s</TD>\n";
                print "</TR>\n";
            }
	}
	else	{
            $message  = f_get_error_text($conn, "Invalid query: ");
            print "<TR><TD COLSPAN=\"6\">$message</TD></TR>\n";
	}
        print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"Запрос выполнен " . date("d.m.Y H:i:s") . "\">Итого</TD><TD COLSPAN=\"4\">$sm</TD></TR>\n";
	print "</TABLE>\n";
	print_buttons();
	print "</form>\n";



?>

</body>

</html>