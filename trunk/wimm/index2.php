<?php
    include_once ("fun_web.php");
    include_once 'fun_dbms.php';
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
        <title>�������� ������</title>
    </head>
    <body onload="$('#dialog_box').draggable();$('#OK_BTN').draggable();">
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
                $("#OK_BTN").val("��������");
                $("#dlg_box_cap").text("���������� ������");
                s1 = "";
                $("#t_sum").val(s1);
                $("#t_curr").val(s1);
                $("#t_date").val(s1);
                s1 = "<?php echo getRequestParam("UID", "");?>";
                $("#t_user").val(s1);
            }
            else    {
                $("#HIDDEN_ID").val(row_id);
                $("#OK_BTN").val("��������");
                $("#dlg_box_cap").text("��������� ������");
                $("#t_name").val($("#TNAME_" + row_id).text());
                $("#t_sum").val($("#T_SUMM_" + row_id).attr('title'));
                $("#t_user").val($("#T_USR_" + row_id).val());
                $("#t_place").val($("#T_PLACE_" + row_id).val());
                $("#t_budget").val($("#T_BUDG_" + row_id).val());
                $("#t_date").val($("#T_DATE_" + row_id).attr('title'));
                $('#t_type').val($("#T_TYPE_" + row_id).val());
                $('#t_curr').val($("#T_CURR_" + row_id).val());
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
            <label for="BDATE">���� ������ �������:</label>
            <input id="BDATE" name="BDATE" type="text" value="<?php echo $bd;?>">
            <label for="EDATE">���� ��������� �������:</label>
            <input id="EDATE" name="EDATE" type="text" value="<?php echo $ed;?>">
            <label for="f_budget">������:</label>
            <select size="1" id="f_budget" name="f_budget" onchange="$('#expenses').submit();">
<?php
            $sql = "SELECT budget_id, budget_name FROM m_budget WHERE close_date is null";
            f_set_sel_options2($conn, $sql, $bg, $bg, 2);
?>
            </select>
        </div>
<?php
    }
?>
        <div style="display: block; width: 100%;">
            <input type="submit" value="��������">
            <input type="button" value="��������" onclick="sel_row('');">
            <input type="button" value="�������" onclick="doEdit('del');">
            <input type="reset" value="����� ���������">
            <input type="button" value="�����" onclick="submit_myform('expenses','wimm_exit.php','exit');">
        </div>
    </div>
<?php
}
if($conn)	{
	$fm = getRequestParam("FRM_MODE","refresh");
	$sql_dml = "";
	if(strcmp($fm,"insert")==0)	{
		$sql_dml = "INSERT INTO m_transactions (transaction_name, t_type_id, currency_id, transaction_sum, transaction_date, user_id, open_date, place_id, budget_id) VALUES(";
		$s = value4db(urldecode(getRequestParam("t_name","�������!")));
		$sql_dml .= "'$s',";
		$s = getRequestParam("t_type",1);
		$sql_dml .= "$s,";
		$s = getRequestParam("t_curr",2);
		$sql_dml .= "$s,";
		$s1 = getRequestParam("t_sum",0);
		if(strpos($s1,",")===false)	{
			$s = $s1;
		}
		else	{
			$s = str_replace(",",".",$s1);
		}
		$sql_dml .= "$s,";
		$td = getRequestParam("t_date",date("Y-m-d H:i:s"));
		$sql_dml .= "'$td',";
		$s = getRequestParam("t_user",1);
		$sql_dml .= "$s,";
		$sql_dml .= "'$td',";
		$s = getRequestParam("t_place",0);
		$sql_dml .= "$s,";
		$s = getRequestParam("t_budget",0);
		$sql_dml .= "$s)";
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
	print_body_title("������� � $bd �� $ed");
	if(strlen($sql_dml)>0)	{
		print "	<input ID=\"SQL\" type=\"hidden\" value=\"$sql_dml\">\n";
		$conn->query($sql_dml);
		$conn->commit();
	}
        $s = "";
?>
	<form id="expenses" name="expenses" method="post">
            <input id="FRM_MODE" name="FRM_MODE" type="hidden" value="refresh">
            <input id="HIDDEN_ID" name="HIDDEN_ID" type="hidden" value="0">
            <input name="UID" type="hidden" value="<?php echo getRequestParam("UID", "");?>">
            <DIV class="dlg_box" id="dialog_box" style="width:600px;display:none;" class='ui-widget-content'>
                <DIV class="dlg_box_cap" id="dlg_box_cap">��������� ������</DIV>
                <DIV class="dlg_box_text" id="dlg_box_text" >
                    <TABLE WIDTH="100%" class="hidden">
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_user">������������:</label>
                            <select class="dialog_ctl" size="1" id="t_user" name="t_user">
<?php
	$sql = "select user_id, user_name from m_users where close_date is null";
	f_set_sel_options2($conn, $sql, $s, $s, 2);
?>
                            </select>
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_name">������������:</label>
                            <input class="dialog_ctl" name="t_name" id="t_name" type="text" value="">
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_type">���:</label>
                            <select class="dialog_ctl" size="1" name="t_type" id="t_type">
<?php
	$sql = "SELECT t_type_id, t_type_name FROM m_transaction_types  WHERE close_date is null";
	f_set_sel_options2($conn, $sql, $s, 1, 2);
?>
                            </select>
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_curr">������:</label>
                            <select class="dialog_ctl" size="1" id="t_curr" name="t_curr">
<?php
	$sql = "SELECT currency_id, concat(currency_name,' (',currency_abbr,')') as c_name FROM m_currency WHERE close_date is null";
	f_set_sel_options2($conn, $sql, $s, 2, 2);
?>
                            </select>
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_sum">�����:</label>
                            <input class="dialog_ctl" id="t_sum" name="t_sum" type="text" value="">
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_date">����:</label>
                            <input class="dialog_ctl" id="t_date" name="t_date" type="text" value="">
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_place">�����:</label>
                            <select class="dialog_ctl" size="1" id="t_place" name="t_place">
<?php
	$sql = "SELECT place_id, place_name FROM m_places WHERE close_date is null";
	f_set_sel_options2($conn, $sql, $s, 1, 2);
?>
                            </select>
                        </div>
                        <div class="dialog_row">
                            <label class="dialog_lbl" for="t_budget">������:</label>
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
                    <input id="OK_BTN" type="submit" value="��">
                    <input type="button" value="������" onclick="$('#dialog_box').hide();">
                </DIV>
            </DIV>
<?php
	$bg = getRequestParam("f_budget","-1");
	print_buttons($conn, $bd, $ed, $bg);
?>
            <input type="hidden" id="bg" value="<?php echo $bg;?>">
            <TABLE WIDTH="100%" BORDER="1">
                <thead>
                    <TR>
                        <TH WIDTH="5%">&nbsp</TH>
                        <TH WIDTH="33%">��������</TH>
                        <TH WIDTH="10%">�����</TH>
                        <TH WIDTH="15%">���� � �����</TH>
                        <TH WIDTH="17%">���</TH>
                        <TH WIDTH="20%">���</TH>
                    </TR>
                </thead>
                <tbody>
<?php
	//print "<TR><TD COLSPAN=\"6\">���������</TD></TR>\n";
	$sql = "select transaction_id, t_type_name, transaction_name, transaction_sum, Type_sign, transaction_date, user_name, place_name, " .
                " place_descr, t.currency_id t_cid, mcu.currency_sign, mb.currency_id as bc_id, t.place_id, t.budget_id, t.user_id, t.t_type_id " .
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
	$plus_pict = "picts/plus.gif";
	$minus_pict = "picts/minus.gif";
	$locale_info = localeconv();
	if($res)	{		//print "<TR><TD COLSPAN=\"6\">������ �����</TD></TR>\n";
            while ($row =  $res->fetch(PDO::FETCH_ASSOC)) {
                print "<TR class=\"expenses\">\n";
                $row_pk = $row['transaction_id'];
                $row_id = "ROW_" . $row_pk;
                print "<TD><input name=\"ROW_ID\" ID=\"$row_id\" type=\"radio\" value=\"$row_pk\" onclick=\"sel_row('$row_pk');\">";
                print "<input class=\"multiselect\" style=\"display:none;\" name=\"MROW[$row_pk]\" ID=\"CHK_$row_pk\" type=\"radio\" value=\"$row_pk\"></TD>\n";
                $t = $row['t_type_name'];
                $s = $row['transaction_name'];
                print "<TD TITLE=\"$t\"><LABEL id=\"TNAME_$row_pk\" FOR=\"$row_id\">$s</LABEL></TD>\n";
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
                $ts = $row['transaction_sum'];
                $t = number_format($ts,2,","," ");
                printf('%s<LABEL ID="T_SUMM_%s" FOR="%s" title="%s">%s</LABEL>'.
                        '<input type="hidden" id="T_TYPE_%s" value="%s">'.
                        '<input type="hidden" id="T_CURR_%s" value="%s"></TD>%s',
                        $cs, $row_pk, $row_id, $ts, $t, $row_pk, 
                        filter_array($row, 't_type_id', ''), $row_pk, $cid, PHP_EOL);
                $t = $row['transaction_date'];
                $s = f_get_disp_date($t);
                print "<TD id=\"T_DATE_$row_pk\" TITLE=\"$t\">$s</TD>\n";
                $s = $row['user_name'];
                $u = filter_array($row, 'user_id', '');
                print "<TD><label FOR=\"$row_id\">$s</label>" .
                        "<input type=\"hidden\" id=\"T_USR_$row_pk\" value=\"$u\">".
                        "</TD>" . PHP_EOL;
                $s = $row['place_name'];
                $t = $row['place_descr'];
                $p = filter_array($row, 'place_id', '');
                $b = filter_array($row, 'budget_id', '');
                $sCol = "<TD TITLE=\"$t\"><label FOR=\"$row_id\">$s</label>".
                        "<input type=\"hidden\" id=\"T_PLACE_$row_pk\" value=\"$p\">".
                        "<input type=\"hidden\" id=\"T_BUDG_$row_pk\" value=\"$b\"></TD>" . PHP_EOL;
                print $sCol;
                print "</TR>\n";
            }	
        }
	else	{
            $message  = f_get_error_text($conn, "Invalid query: ");
            print "<TR><TD COLSPAN=\"6\">$message</TD></TR>\n";
	}
	print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"������ �������� " . date("d.m.Y H:i:s") . "\" ALIGN=\"RIGHT\">";
	$t = number_format($sd,2,","," ");
	print "�����, ������:</TD><TD COLSPAN=\"4\"><IMG SRC=$plus_pict>&nbsp;$t</TD></TR>\n";
	print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" ALIGN=\"RIGHT\">";
	$t = number_format($sm,2,","," ");
	print "�����, �������:</TD><TD COLSPAN=\"4\"><IMG SRC=$minus_pict>&nbsp;$t</TD></TR>\n";
	$sr = $sd - $sm;
	$t = number_format($sr,2,","," ");
	$c_class = "white_bold";
	if($sr<0)
		$c_class = $minus_pict;
	else
		$c_class = $plus_pict;
	print "<TR  class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"������� - ������\" ALIGN=\"RIGHT\">";
	print "�����, �������:</TD><TD COLSPAN=\"4\"><IMG SRC=\"$c_class\">&nbsp;$t</TD></TR>\n";
	print "</tbody></TABLE>\n";
	print_buttons($conn);
}

?>
                    </form>
</body>

</html>
