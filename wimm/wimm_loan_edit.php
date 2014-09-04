<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
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
	$id = $_REQUEST["HIDDEN_ID"];
	if(strlen($id)<1)
		$id = "0";
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
function rchange()
{
    wimm_loan_edit.l_2date.disabled = !wimm_loan_edit.returned.checked;
    if(wimm_loan_edit.returned.checked)
        wimm_loan_edit.l_cdate.value = wimm_loan_edit.l_2date.value;
    else
        wimm_loan_edit.l_cdate.value = "";
}

function doCancel(s1)
{
    wimm_loan_edit.FRM_MODE.value=s1;
}

</script>
    <body>
    <form name="wimm_loan_edit" action="wimm_loans.php" method="post">
        <?php
        // put your code here
	include_once 'fun_dbms.php';
	print_body_title($t);
	print "<input name=\"FRM_MODE\" type=\"hidden\" value=\"$frm_mode\">\n";
	print "<input name=\"HIDDEN_ID\" type=\"hidden\" value=\"$id\">\n";
	print "<input name=\"l_type\" type=\"hidden\" value=\"1\">\n";
	print "<input name=\"l_cdate\" type=\"hidden\" value=\"\">\n";
	$uid = getSessionParam("UID",0);
	if($uid==0)
		$uid = getRequestParam("UID",1);
	print "<input name=\"UID\" type=\"hidden\" value=\"$uid\">\n";
	$sql = "SELECT loan_name, loan_sum, loan_rate, loan_type, start_date, end_date, " .
                "user_id, place_id, budget_id, currency_id, close_date  FROM m_loans";
	$sql = $sql . " WHERE loan_id=$id";
	$conn = f_get_connection();
	if($conn)	{
		$res = $conn->query($sql);
		if($res)	{
			$row = $res->fetch(PDO::FETCH_ASSOC);
		}
	}
	print "<TABLE WIDTH=\"100%\" class=\"hidden\">\n";
	// user
	print "<TR class=\"hidden\">\n";
	$s = f_get_col($row,"user_id",$uid);
	print "<TD WIDTH=\"30%\" class=\"hidden\">Пользователь:</TD><TD class=\"hidden\"><select size=\"1\" name=\"l_user\">\n";
	$sql = "select user_id, user_name from m_users where close_date is null";
	f_set_sel_options2($conn, $sql, $s, $s, 2);
	print "</select></TD></TR>\n";
	// name
	print "<TR class=\"hidden\">\n";
	$s = f_get_col($row,"loan_name","Кредит!");
	print "<TD WIDTH=\"30%\" class=\"hidden\">Наименование:</TD><TD class=\"hidden\"><input name=\"l_name\" type=\"text\" value=\"$s\" autofocus></TD>\n";
	print "</TR>\n";
	print "<TR class=\"hidden\">\n";
	$s = f_get_col($row,"loan_rate",5);
	print "<TD WIDTH=\"30%\" class=\"hidden\">Процентная ставка:</TD><TD class=\"hidden\"><input name=\"l_rate\" type=\"text\" value=\"$s\"></TD>\n";
	print "</TR>\n";
	print "<TR class=\"hidden\">\n";
	$s = f_get_col($row,"currency_id",2);
	print "<TD class=\"hidden\">Валюта:</TD><TD class=\"hidden\"><select size=\"1\" name=\"l_curr\">\n";
	$sql = "SELECT currency_id, concat(currency_name,' (',currency_abbr,')') as c_name FROM m_currency WHERE close_date is null";
	f_set_sel_options2($conn, $sql, $s, 2, 2);
	print "</select></TD>\n";
	print "</TR>\n";
	print "<TR class=\"hidden\">\n";
	$s = f_get_col($row,"loan_sum","0");
	print "<TD class=\"hidden\">Сумма:</TD><TD class=\"hidden\"><input name=\"l_sum\" type=\"text\" value=\"$s\"></TD>\n";
	print "</TR>\n";
	print "<TR class=\"hidden\">\n";
	$s = f_get_col($row,"start_date",date("Y-m-d H:i:s"));
	print "<TD class=\"hidden\">Кредит взят:</TD><TD class=\"hidden\"><input name=\"l_bdate\" type=\"text\" value=\"$s\"></TD>\n";
	print "</TR>\n";
	print "<TR class=\"hidden\">\n";
	$s = f_get_col($row,"end_date",date("Y-m-d H:i:s"));
	print "<TD class=\"hidden\">Кредит надо вернуть до:</TD><TD class=\"hidden\"><input name=\"l_edate\" type=\"text\" value=\"$s\"></TD>\n";
	print "</TR>\n";
	print "<TR class=\"hidden\">\n";
	$s = f_get_col($row,"place_id","1");
	print "<TD class=\"hidden\">Место выдачи:</TD><TD class=\"hidden\"><select size=\"1\" name=\"l_place\">\n";
	$sql = "SELECT place_id, place_name FROM m_places WHERE close_date is null";
	f_set_sel_options2($conn, $sql, $s, 1, 2);
	print "</select></TD>\n";
	print "</TR>\n";
	print "<TR class=\"hidden\">\n";
	$s = f_get_col($row,"budget_id","1");
	print "<TD class=\"hidden\">Бюджет:</TD><TD class=\"hidden\"><select size=\"1\" name=\"l_budget\">\n";
	$sql = "SELECT budget_id, budget_name FROM m_budget WHERE close_date is null";
	f_set_sel_options2($conn, $sql, $s, 1, 2);
	print "</select></TD>\n";
	print "</TR>\n";
	print "<TR class=\"hidden\">\n";
	$s = f_get_col($row,"close_date","");
	print "<TD class=\"hidden\"><input name=\"returned\" type=\"checkbox\" ";
        if(strlen($s)>0)    {
            print "checked ";
            $s1 = $s;
        }
        else {
            $s1 = date("Y-m-d H:i:s");
        }
        print "onchange=\"rchange()\">Кредит возвращён:</TD><TD class=\"hidden\"><input name=\"l_2date\" type=\"text\" value=\"$s1\"";
        if(strlen($s)<1)
            print " disabled ";
        print "></TD>\n</TR>\n";

	print "</TABLE>\n";
	print "<input type=\"submit\" value=\"$frm_btn\">\n";
	print "<input type=\"submit\" value=\"Отмена\" onclick=\"doCancel('refresh')\">\n";
	print "<input type=\"reset\" value=\"Сброс\">\n";
        ?>
    </form>
    </body>
</html>
