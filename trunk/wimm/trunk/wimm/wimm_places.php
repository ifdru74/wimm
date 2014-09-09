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
	$p_title = "Редактор мест, где тратятся деньги";
	print_head($p_title);
?>
<script language="JavaScript" type="text/JavaScript">
function doSel(s1, s2, s3)
{
	sid=places.HIDDEN_ID.value;
	coll = places.elements;
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
	places.HIDDEN_ID.value=s1;
	places.p_name.value=s2;
	places.p_descr.value=s3;
}

function doEdit(s1)
{
	if(s1=="add")	{
		places.FRM_MODE.value="insert";
		places.HIDDEN_ID.value="0";
		places.submit();
	}
	else if(s1=="edit")	{
		coll = places.elements;
		for(i=0; i<coll.length; i++)             {
			v = coll.item(i);
			s_1 = v.name.substr(0,2);
			s2 = v.id;
			if(s_1=="ID")	{
				if(v.checked)	{
					places.HIDDEN_ID.value=s2;
					places.FRM_MODE.value="update";
					break;
				}
			}
		}
		if(places.FRM_MODE.value=="update")
			places.submit();
		else
			alert("Запись для редактирования не выбрана");
	}	else if(s1=="del")	{
		coll = places.elements;
		for(i=0; i<coll.length; i++)             {
			v = coll.item(i);
			s_1 = v.name.substr(0,2);
			s2 = v.id;
			if(s_1=="ID")	{
				if(v.checked)	{
					places.HIDDEN_ID.value=s2;
					places.FRM_MODE.value="delete";
					break;
				}
			}
		}
		if(places.FRM_MODE.value=="delete")
			places.submit();
		else
			alert("Запись для удаления не выбрана");
	}	else if(s1=="exit")	{
		places.FRM_MODE.value="return";
		places.action="index.php";
		places.submit();
	}
}
</script>
<body>
<?php
include_once 'fun_dbms.php';
function print_buttons($bd="")
{
	if(strlen($bd)>0)	{
		print "\t<TR class=\"hidden\">\n";
		print "\t\t<TD class=\"hidden\" COLSPAN=\"3\">Наименование:<input name=\"p_name\" type=\"text\" size=\"50\" value=\"\"></TD>\n";
		print "\t\t<TD class=\"hidden\" COLSPAN=\"3\">Описание:<input name=\"p_descr\" type=\"text\" size=\"50\" value=\"\"></TD>\n";
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

$uid = getSessionParam("UID",0);
if($uid==0)
	$uid = getRequestParam("UID",1);
$conn = f_get_connection();
if($conn)	{
	$fm = getRequestParam("FRM_MODE","refresh");
	$sql = "";
	if(strcmp($fm,"insert")==0)	{
		$sql = "INSERT INTO m_places (place_name, open_date, place_descr, user_id) VALUES(";
		$s = getRequestParam("p_name","Место?");
		$sql .= "'$s',";
		$td = date("Y-m-d H:i:s");
		$sql .= "'$td',";
		$s = getRequestParam("p_descr",1);
		$sql .= "'$s',";
		$sql .= "$uid)";
	}	else if(strcmp($fm,"update")==0)	{
		$sql = "UPDATE m_places SET ";
		$s = getRequestParam("p_name","Место?");
		$sql .= "place_name='$s',";
		$s = getRequestParam("p_descr",1);
		$sql .= "place_descr='$s' ";
		$sql .= "where place_id=";
		$s = getRequestParam("HIDDEN_ID",0);
		$sql .= $s;
	}
	else if(strcmp($fm,"delete")==0)	{
		$s = getRequestParam("HIDDEN_ID",0);
		//$sql = "delete from m_places where place_id=$s";
                $sql = "update m_places set close_date=#NOW# where place_id=$s";
	}
	print_body_title($p_title);
	print "<form name=\"places\" action=\"wimm_places.php\" method=\"post\">\n";
	if(strlen($sql)>0)	{
		print "	<input name=\"SQL\" type=\"hidden\" value=\"$sql\">\n";
		$conn->query(formatSQL($sql));
		$conn->commit();
	}
	print "<input name=\"FRM_MODE\" type=\"hidden\" value=\"refresh\">\n";
	print "<input name=\"HIDDEN_ID\" type=\"hidden\" value=\"0\">\n";
	print "<input name=\"UID\" type=\"hidden\" value=\"" . $uid ."\">\n";
	print_buttons("edit_boxes");
	print "<TABLE WIDTH=\"100%\" BORDER=\"1\">\n";
	print "<TR>\n";
	print "<TH>&nbsp</TH>\n";
	print "<TH>Наименование</TH>\n";
	print "<TH>Дата создания</TH>\n";
	print "<TH>Дата закрытия</TH>\n";
	print "<TH>Кто автор</TH>\n";
	print "</TR>\n";
	//print "<TR><TD COLSPAN=\"6\">Подключён</TD></TR>\n";
	$sql = "select place_id, place_name, tp.open_date, tp.close_date, place_descr, user_name from m_places tp, m_users tu where tp.user_id=tu.user_id order by place_name";
	$res = $conn->query($sql);
	$sm = 0;
	$sd = 0;
	$c_class = "dark";
	if($res)	{
		//print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
			print "<TR class=\"$c_class\">\n";
			if(strcmp($c_class,"dark")==0)	{
				$c_class = "white";			}
			else	{
				$c_class = "dark";
			}
			$s = $row['place_id'];
			$sn = $row['place_name'];
			$sd = $row['place_descr'];
			print "<TD><input name=\"ID$s\" ID=\"$s\"type=\"radio\" value=\"$s\" onclick=\"doSel('$s','$sn','$sd')\">";
			print "</TD>\n";
			print "<TD TITLE=\"$sd\">$sn</TD>\n";
			$t = $row['open_date'];
			$s = f_get_disp_date($t);
			print "<TD TITLE=\"$t\">$s</TD>\n";
			$t = $row['close_date'];
			$s = f_get_disp_date($t);
			print "<TD TITLE=\"$t\">$s</TD>\n";
			$s = $row['user_name'];
			print "<TD>$s</TD>\n";
			print "</TR>\n";
			$sm ++;
		}	}
	else	{
		$message  = f_get_error_text($conn, "Invalid query: ");
		print "<TR><TD COLSPAN=\"6\">$message</TD></TR>\n";
	}	print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"Запрос выполнен " . date("d.m.Y H:i:s") . "\">Количество мест</TD><TD COLSPAN=\"4\">$sm</TD></TR>\n";
	print "</TABLE>\n";
	print_buttons();
	print "</form>\n";
}

?>
</body>

</html>
