<?php
    $t = time() + 10;
    header("Expires: " . date("D, d M Y H:i:s T", $t));
    include_once './fun_web.php';
    init_superglobals();
    session_start();
    //auth_check('UID');
    $p_title = "Редактор мест, где тратятся деньги";
    //print_head($p_title);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="STYLESHEET" href="css/wimm.css" type="text/css"/>
        <link rel="SHORTCUT ICON" href="picts/favicon.ico">
        <title><?php echo $p_title;?></title>
        <script language="JavaScript" type="text/JavaScript" src="js/form_common.js"></script>
    </head>
    <body onload="$('#dialog_box').draggable();">
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
<script language="JavaScript" type="text/JavaScript">
function doSel(s1, s2, s3)
{
    if(s1!==null&&s1!=undefined&&s1.length>0)   {
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
        $('#HIDDEN_ID').val(s1);
        s2 = "#RPL_" + s1;
        $('#p_name').val($(s2).text());
        $('#p_descr').val($(s2).attr('title'));
        $('#dlg_box_cap').text('Изменить место');
        $('#OK_BTN').show();
        $('#DEL_BTN').show();
        $('#ADD_BTN').hide();
    }
    else    {
        $('#dlg_box_cap').text('Добавить место');
        $('#OK_BTN').hide();
        $('#DEL_BTN').hide();
        $('#ADD_BTN').show();
        $('#dialog_box').show();
    }
    var objDiv = document.getElementById("dialog_box");
    var y = (f_get_scroll_y()+200).toString()+"px";
    objDiv.style.top = y;
    //var x = (window.innerWidth||document.body.clientWidth);
    var x = (f_get_scroll_x()-50)/2+500;
    if(x<0)
        x = 500;
    x = x.toString()+"px";
    objDiv.style.left = x;
    //$('#dialog_box').show();
    objDiv.style.display="inline";
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
<?php
include_once 'fun_dbms.php';
function print_buttons($bd="")
{
?>
    <div>
        <div class="dialog_row">
            <input type="submit" value="Обновить">
            <input type="button" value="Добавить" onclick="doSel('');">
            <input type="reset" value="Снять выделение">
            <input type="button" value="Выход" onclick="doEdit('exit');">
        </div>
    </div>
<?php
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
		$conn->query(formatSQL($conn, $sql));
	}
?>
    <div id="dialog_box" class="dlg_box ui-widget-content" style="width:500px;display:none;height:100px;" >
        <div class="dlg_box_cap" id="dlg_box_cap">Изменить</div>
        <div class="dlg_box_text" id="dlg_box_text">
            <div class="dialog_row">
                <label for="p_name">Наименование:</label>
                <input type="text" id="p_name" name="p_name" value="" size="50">
            </div>
            <div class="dialog_row">
                <label for="p_descr">Описание:</label>
                <input type="text" id="p_descr" name="p_descr" value="" size="50" style="float: right; margin-right: 15px;">
            </div>
        </div>
        <div class="dlg_box_btns">
            <input id="ADD_BTN" type="button" value="Сохранить" onclick="doEdit('add');">
            <input id="OK_BTN" type="button" value="Изменить" onclick="doEdit('edit');">
            <input id="DEL_BTN" type="button" value="Удалить" onclick="doEdit('del');">
            <input id="CANCEL_BTN" type="button" value="Отмена" onclick="$('#HIDDEN_ID').val(); $('#dialog_box').hide();">
        </div>
    </div>
<?php
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
			print "<TD><input name=\"ROW_ID\" ID=\"ROW_$s\" type=\"radio\" value=\"$s\" onclick=\"doSel('$s')\">";
			print "</TD>\n";
			print "<TD><label TITLE=\"$sd\" ID=\"RPL_$s\" for=\"ROW_$s\">$sn</label></TD>\n";
			$t = $row['open_date'];
			$s1 = f_get_disp_date($t);
			print "<TD><label TITLE=\"$t\" ID=\"ROL_$s\" for=\"ROW_$s\">$s1</label></TD>\n";
			$t = $row['close_date'];
			$s1 = f_get_disp_date($t);
			print "<TD><label TITLE=\"$t\" ID=\"REL_$s\" for=\"ROW_$s\">$s1</label></TD>\n";
			$s1 = $row['user_name'];
			print "<TD><label TITLE=\"$t\" ID=\"RUL_$s\" for=\"ROW_$s\">$s1</label></TD>\n";
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
