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
        include_once 'fun_dbms.php';
	auth_check('UID');
	$id = $_REQUEST["HIDDEN_ID"];
	if(strlen($id)<1)
		$id = "0";
    if(key_exists("AJAX",$_POST))   {
        $a_ret = array();
        $a_ret['res'] = 0;
        $a_ret['error'] = '';
        $conn = f_get_connection();
	if($conn)	{
            $sql = "SELECT transaction_name, t_type_id, currency_id, transaction_sum, ".
                    "transaction_date, user_id, place_id, budget_id FROM m_transactions ".
                    " WHERE transaction_id=$id";
            $res = mysql_query($sql,$conn);
            if($res)	{
                $row = mysql_fetch_assoc($res);
                if($row)    {
                    foreach ($row as $key => $value) {
                        $a_ret[$key] = $value;
                        $a_ret['res'] ++;
                    }
                }
                else    {
                    $a_ret['error'] = 'Не удалось получить результат';
                }
                mysql_free_result($res);
            }
            else {
                $a_ret['error'] = mysql_error($conn);
            }
        }
        else {
            $a_ret['error'] = 'Ошибка соединения с БД';
        }
        die(json_encode($a_ret));
    }
	if($id==0)	{
		$t = "Добавление записи";
		$frm_mode="insert";
		$frm_btn="Добавить";
	}
	else	{
		$t = "Изменение записи";
		$frm_mode="update";
		$frm_btn="Изменить";
	}
	print_head("$t");
?>
<script language="JavaScript" type="text/JavaScript">
function doCancel(s1)
{	wimm_edit.FRM_MODE.value=s1;
}
function doFocus()
{
	wimm_edit.t_name.focus();
}

</script>
<body onbodyload='doFocus()'>
<form name="wimm_edit" action="index.php" method="post">

<?php
	print_body_title($t);
	print "<input name=\"FRM_MODE\" type=\"hidden\" value=\"$frm_mode\">\n";
	print "<input name=\"HIDDEN_ID\" type=\"hidden\" value=\"$id\">\n";
	$uid = getSessionParam("UID",0);
	if($uid==0)
		$uid = getRequestParam("UID",1);
	print "<input name=\"UID\" type=\"hidden\" value=\"$uid\">\n";
	$sql = "SELECT transaction_name, t_type_id, currency_id, transaction_sum, transaction_date, user_id, place_id, budget_id FROM m_transactions";
	$sql = $sql . " WHERE transaction_id=$id";
	$conn = f_get_connection();
	if($conn)	{
		$res = mysql_query($sql,$conn);
		if($res)	{
			$row = mysql_fetch_assoc($res);
		}
	}
	print "<DIV style=\"margin: 0px auto;\">\n";
	// user
	print "<DIV class=\"dialog_row\">\n";
	$s = f_get_col($row,"user_id",$uid);
	print "<label class=\"dialog_lbl\">Пользователь:</label>".
                "<select class=\"dialog_ctl\" size=\"1\" name=\"t_user\">\n";
	$sql = "select user_id, user_name from m_users where close_date is null";
	f_set_sel_options2($sql, $s, $s, 2);
	print "</select></div>\n";
	// name
	$s = f_get_col($row,"transaction_name","");
	print "<DIV class=\"dialog_row\">\n";
	print "<label class=\"dialog_lbl\">Наименование:</label>".
                "<input class=\"dialog_ctl\" name=\"t_name\" type=\"text\" value=\"$s\" autofocus>\n";
	print "</div>\n";
	print "<DIV class=\"dialog_row\">\n";
	$s = f_get_col($row,"t_type_id","1");
	print "<label class=\"dialog_lbl\">Тип:</label>".
                "<select class=\"dialog_ctl\" size=\"1\" name=\"t_type\">\n";
	$sql = "SELECT t_type_id, t_type_name FROM m_transaction_types  WHERE close_date is null";
	f_set_sel_options2($sql, $s, 1, 2);
	print "</select></div>\n";
	print "<DIV class=\"dialog_row\">\n";
	$s = f_get_col($row,"currency_id","2");
	print "<label class=\"dialog_lbl\">Валюта:</label>".
                "<select class=\"dialog_ctl\" size=\"1\" name=\"t_curr\">\n";
	$sql = "SELECT currency_id, concat(currency_name,' (',currency_abbr,')') as c_name FROM m_currency WHERE close_date is null";
	f_set_sel_options2($sql, $s, 2, 2);
	print "</select></div>\n";
	print "<DIV class=\"dialog_row\">\n";
	$s = f_get_col($row,"transaction_sum","0");
	print "<label class=\"dialog_lbl\">Сумма:</label>".
                "<input class=\"dialog_ctl\" name=\"t_sum\" type=\"text\" value=\"$s\"></div>\n";
	print "<DIV class=\"dialog_row\">\n";
	$s = f_get_col($row,"transaction_date",date("Y-m-d H:i:s"));
	print "<label class=\"dialog_lbl\">Дата:</label>".
                "<input class=\"dialog_ctl\" name=\"t_date\" type=\"text\" value=\"$s\"></div>\n";
	$s = f_get_col($row,"user_id","1");
	//print "<input name=\"t_user\" type=\"hidden\" value=\"$uid\"><BR>\n";
	print "<DIV class=\"dialog_row\">\n";
	$s = f_get_col($row,"place_id","1");
	print "<label class=\"dialog_lbl\">Место:</label>".
                "<select class=\"dialog_ctl\" size=\"1\" name=\"t_place\">\n";
	$sql = "SELECT place_id, place_name FROM m_places WHERE close_date is null";
	f_set_sel_options2($sql, $s, 1, 2);
	print "</select></div>\n";

	print "<DIV class=\"dialog_row\">\n";
	$s = f_get_col($row,"budget_id","1");
	print "<label class=\"dialog_lbl\">Бюджет:</label><TD class=\"hidden\">".
                "<select class=\"dialog_ctl\" size=\"1\" name=\"t_budget\">\n";
	$sql = "SELECT budget_id, budget_name FROM m_budget WHERE close_date is null";
	f_set_sel_options2($sql, $s, 1, 2);
	print "</select></div>\n";

	print "<DIV class=\"dialog_row\">\n";
	print "<input class=\"dialog_btn\" type=\"submit\" value=\"$frm_btn\">\n";
	print "<input class=\"dialog_btn\" type=\"submit\" value=\"Отмена\" onclick=\"doCancel('refresh')\">\n";
	print "<input class=\"dialog_btn\" type=\"reset\" value=\"Сброс\">\n";
	print "</div>\n";
	print "</div>\n";

?>
</form>

</body>

</html>
