<?php
    include_once ("fun_web.php");
    $uid = page_pre();
    if($uid===FALSE)
        die();
    include_once 'fun_dbms.php';
    include_once 'table.php';
    /**
     * @var $conn PDO 
     */
    $conn = f_get_connection();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="STYLESHEET" href="css/wimm.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/bootstrap.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/jquery_autocomplete_ifd.css" type="text/css"/>
        <link rel="SHORTCUT ICON" href="picts/favicon.ico">
        <title>Семейный бюджет</title>
    </head>
    <body onload="onLoad2();">
        <div class="container">
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
    <script language="JavaScript" type="text/JavaScript" src="js/index_aj.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="js/jquery_autocomplete_ifd.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="js/bootstrap.js"></script>
    <script language="JavaScript" type="text/JavaScript">
        function onLoad2()
        {
            $("body").keydown(function(e) {
                onPageKey(e.keyCode);
            });
            setHandlers(".dtp");
            $('#dialog_box').draggable();
            ac_init("ac", ".txt");
            $(".row_sel").click(function(e)
            {
                $('.dlg_box').show();
                $('#dlg_box_cap').text('Изменение записи');
                table_row_selected("#"+e.currentTarget.id, "#expenses");
                $('#HIDDEN_ID').val(e.currentTarget.id);
                $('#FRM_MODE').val('update');
                $("#dialog_box").modal('show');
            });
        }
        function doCancel2()
        {
            $('#dialog_box').hide();
            $('#FRM_MODE').val('refresh');
        }
        function del_click2()
        {
            var s1 = $('#HIDDEN_ID').val();
            if(s1!=null && s1.length>0)
            {
                $('#FRM_MODE').val('delete');
                tx_submit('/wimm2/wimm_edit2.php');
            }
            else
            {
                alert("Запись для удаления не выбрана");
            }
        }
        function add_click2()
        {
            $('#dlg_box_cap').text('Добавление записи');
            $('#DEL_BTN').hide();
            $('#HIDDEN_ID').val('');
            $('.form_field').val('');
            $('#FRM_MODE').val('insert');
            //$('.dlg_box').show();
            document.getElementById('t_user').focus();
            //$("#dialog_box").modal();
        }
    </script>
<?php
        $dtm = new DateTime();
        $ldfmt = 'Y-m-01';//str_replace('d','01',getSessionParam('locale_date_format', 'd.m.Y'));
	$bd = update_param("BDATE", "BEG_DATE", $dtm->format($ldfmt));
        $dtm->add(new DateInterval('P1M'));
	$ed = update_param("EDATE", "END_DATE", $dtm->format($ldfmt));
	        $dtm = DateTime::createFromFormat('Y-m-d', $bd);
        $ldfmt = getSessionParam('locale_date_format', 'd.m.Y');
        $dtm2 = DateTime::createFromFormat('Y-m-d', $ed);
	print_body_title('Расходы с ' . $dtm->format($ldfmt) . ' по ' . $dtm2->format($ldfmt));
if($conn)	{
        $fm = "refresh";
        if(getRequestParam("btn_refresh",FALSE)===FALSE)
        {
            $fm = getRequestParam("FRM_MODE","refresh");
        }
        include_once 'wimm_dml.php';
        $a_dml = transaction_dml($conn, $fm);
        foreach ($a_dml as $kdml => $vdml) {
            switch($kdml)
            {
                case 'dup_id':
?>
    <div>Дублирование записи <?php echo $vdml;?></div>
<?php            
                    break;
                case 'dup_id':
                    echo $vdml;
                    break;
                case 'sql':
                    print "	<input ID=\"SQL\" type=\"hidden\" value=\"$vdml\">\n";
                    break;
            }
        }
        $s = "";
?>
	<form id="expenses" name="expenses" method="post" accept-charset="utf-8">
            <input id="FRM_MODE" name="FRM_MODE" type="hidden" value="refresh">
            <input id="HIDDEN_ID" name="HIDDEN_ID" type="hidden" value="0">
            <input name="UID" type="hidden" value="<?php echo getRequestParam("UID", "");?>">
            <!-- Modal -->
            <DIV class="ui-widget-content modal fade" id="dialog_box" role="dialog">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <DIV class="modal-header" id="dlg_box_cap">Изменение записи</DIV>
                        <DIV class="modal-body" id="dlg_box_text" >
                            <div scroll_height="100" for="" selected_ac_item="" class="ac_list" id="ac"></div>
                            <div class="form-group">
                                <label for="t_user">Пользователь:</label>
                                <select class="form-control form_field valid sendable" size="1" id="t_user" name="t_user"
                                        bind_row_type="value" bind_row_id="T_USR_" pattern="^[1-9][0-9]*$">
<?php
	$sql = "select user_id, user_name from m_users where close_date is null";
	f_set_sel_options2($conn, formatSQL($conn, $sql), $uid, $uid, 2);
?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="t_name">Наименование:</label>
                                <input class="form-control form_field valid sendable" name="t_name" id="t_name" 
                                       type="text" bind_row_type="label" bind_row_id="TNAME_" 
                                       pattern="^(?!\s*$).+" value="">
                            </div>
                            <div class="form-group">
                                <label for="t_type">Тип:</label>
                                <input class="form_field valid sendable" type="hidden" name="t_type" id="t_type" 
                                       bind_row_type="value" bind_row_id="T_TYPE_" value=""
                                       pattern="^[1-9][0-9]*$" focus_on="t_type_txt">
                                <input type="text" name="t_type_name" class="form-control form_field txt" value=""
                                       autocomplete="off" bound_id="t_type" ac_src="/wimm2/ac_ref.php" 
                                       ac_params="type=t_type;filter=" id="t_type_txt" scroll_height="10"
                                       bind_row_type="title" bind_row_id="TNAME_">
                            </div>
                            <div class="form-group">
                                <label for="t_curr">Валюта:</label>
                                <input type="hidden" name="t_curr" id="t_curr" class="form_field valid sendable" value=""
                                       bind_row_type="value" bind_row_id="T_CURR_"
                                       pattern="^[1-9][0-9]*$" focus_on="t_curr_txt">
                                <input type="text" class="form-control form_field txt" value=""
                                       autocomplete="off" bound_id="t_curr" ac_src="/wimm2/ac_ref.php" 
                                       ac_params="type=t_curr;filter=" id="t_curr_txt"
                                       bind_row_type="title" bind_row_id="T_CURR_">
                            </div>
                            <div class="form-group">
                                <label for="t_sum">Сумма:</label>
                                <input class="form-control form_field valid sendable" id="t_sum" name="t_sum" 
                                       type="text" value="" bind_row_type="title" bind_row_id="T_SUMM_"
                                       pattern="^[1-9][0-9]*[.,]?[0-9]?[0-9]?$">
                            </div>
                            <div class="form-group">
                                <label for="t_date">Дата:</label>
                                <input class="dtp form-control form_field valid sendable" id="t_date" 
                                       name="t_date" type="text" value="" bind_row_type="title" 
                                       pattern="^[0-9]{4,4}-([0][1-9]|[1][0-2])-([0][1-9]|[1-2][0-9]|[3][0-1]) ([0-1][0-9]|[2][0-3]):[0-5][0-9]:[0-5][0-9]$" 
                                       bind_row_id="T_DATE_">
                            </div>
                            <div class="form-group">
                                <label for="t_place">Место:</label>
                                <input type="hidden" name="t_place" id="t_place" class="form_field valid sendable" value=""
                                       bind_row_type="value" bind_row_id="T_PLACE_"
                                       pattern="^[1-9][0-9]*$" focus_on="t_place_txt">
                                <input type="text" class="form-control form_field txt" value=""
                                       autocomplete="off" bound_id="t_place" ac_src="/wimm2/ac_ref.php" 
                                       ac_params="type=t_place;filter=" id="t_place_txt"
                                       bind_row_type="label" bind_row_id="TP_NAME_">
                            </div>
                            <div class="form-group">
                                <label for="t_budget">Бюджет:</label>
                                <input type="hidden" name="t_budget" id="t_budget" class="form_field valid sendable" value=""
                                       bind_row_type="value" bind_row_id="T_BUDG_"
                                       pattern="^[1-9][0-9]*$" focus_on="t_budget_txt">
                                <input type="text" class="form-control form_field txt" value=""
                                       autocomplete="off" bound_id="t_budget" ac_src="/wimm2/ac_ref.php" 
                                       ac_params="type=t_budget;filter=" id="t_budget_txt"
                                       bind_row_type="title" bind_row_id="T_BUDG_">
                            </div>
                        </DIV>
                        <DIV class="modal-footer" id="dlg_box_btns">
                            <button class="btn" id="OK_BTN" type="button"
                                    onclick="if(fancy_form_validate('expenses')) tx_submit('/wimm2/wimm_edit2.php');">
                                <span class="glyphicon glyphicon-save"></span> Сохранить
                            </button>
                            <button class="btn" type="button"
                                    onclick="submit_myform('expenses','wimm_edit3.php','refresh');"
                                    data-dismiss="modal">
                                <span class="glyphicon glyphicon-edit"></span> Подробнее...
                            </button>
                            <button class="btn" id="DEL_BTN" type="button"
                                    onclick="del_click2();">
                                <span class="glyphicon glyphicon-remove"></span> Удалить
                            </button>
                            <button class="btn" type="button"
                                    onclick="doCancel2();"
                                    data-dismiss="modal">
                                <span class="glyphicon glyphicon-erase"></span> Отмена
                            </button>
                        </DIV>
                    </div>
                </div>
            </DIV>
<?php
	$bg = getRequestParam("f_budget","-1");
	print_filter($conn, $bd, $ed, $bg);
        print_buttons("add_click2();");
        $tb = new table();
        $tb->setValue(tbase::$PN_CLASS, "table table-bordered table-responsive table-striped visual2");
        $tb->setIndent(3);
        // header
        $tc = new tcol("Описание");
        $tc->setValue("WIDTH", "38%");
        $tb->addColumn($tc, TRUE);
        $tc = new tcol("Сумма");
        $tc->setValue("WIDTH", "10%");
        $tb->addColumn($tc, TRUE);
        $tc = new tcol("Дата и время");
        $tc->setValue("WIDTH", "15%");
        $tb->addColumn($tc, TRUE);
        $tc = new tcol("Кто");
        $tc->setValue("WIDTH", "17%");
        $tb->addColumn($tc, TRUE);
        $tc = new tcol("Где");
        $tc->setValue("WIDTH", "20%");
        $tb->addColumn($tc, TRUE);
        // body
        $tb->body->setValue(tbody::$PN_ROW_CLASS, "table-hover");
        $fmt_str = "<input class='row_sel' name=\"ROW_ID\" ID=\"=transaction_id\" type=\"radio\" value=\"=transaction_id\">" .
                "<label class='td' TITLE=\"=t_type_name\" id=\"TNAME_=transaction_id\" FOR=\"=transaction_id\">=transaction_name</span>";
        $tb->addColumn(new tcol($fmt_str), FALSE);
        $fmt_str2 = '<label class="td =tl_class" ID="T_SUMM_=transaction_id" FOR="=transaction_id" title="=transaction_sum">=sum_txt</span>'.
                        '<input type="hidden" id="T_TYPE_=transaction_id" value="=t_type_id">'.
                        '<input type="hidden" title="=currency_name" id="T_CURR_=transaction_id" value="=t_cid">';
        $tb->addColumn(new tcol($fmt_str2), FALSE);
        $tb->addColumn(new tcol('<label class="td" id="T_DATE_=transaction_id" for="=transaction_id" TITLE="=transaction_date">=disp_date</span>'), FALSE);
        $tb->addColumn(new tcol("<label class='td' id=\"L_USR_=transaction_id\"FOR=\"=transaction_id\">=user_name</span>" .
                        "<input type=\"hidden\" id=\"T_USR_=transaction_id\" value=\"=user_id\">"), FALSE);
        $tb->addColumn(new tcol("<label class='td' id=\"TP_NAME_=transaction_id\" TITLE=\"=place_descr\" FOR=\"=transaction_id\">=place_name</span>".
                        "<input type=\"hidden\" id=\"T_PLACE_=transaction_id\" value=\"=place_id\">".
                        "<input type=\"hidden\" id=\"T_BUDG_=transaction_id\" value=\"=budget_id\" title=\"=budget_name\">"), FALSE);
?>
            <input type="hidden" id="bg" value="<?php echo $bg;?>">
<?php
    echo $tb->htmlOpen();
	//print "<TR><TD COLSPAN=\"6\">Подключён</TD></TR>\n";
        $a_vg = false;
	$sql = "select transaction_id, t_type_name, transaction_name, transaction_sum, type_sign, transaction_date, user_name, place_name, " .
                " place_descr, t.currency_id t_cid, mcu.currency_name || ' (' || mcu.currency_abbr || ')' as currency_name, mcu.currency_sign, mb.currency_id as bc_id, " .
                " t.place_id, t.budget_id, t.user_id, t.t_type_id, mb.budget_name " .
                " from m_transactions t, m_transaction_types tt, m_users tu, m_places tp, m_currency mcu, m_budget mb " .
                " where t.t_type_id=tt.t_type_id and t.user_id=tu.user_id and t.place_id=tp.place_id and t.currency_id=mcu.currency_id and " .
                " t.budget_id=mb.budget_id and #TODATE#(transaction_date#ISO_DATETIME#)>=#TODATE#('$bd'#ISO_DATE#) and  #TODATE#(transaction_date#ISO_DATETIME#)<#TODATE#('$ed'#ISO_DATE#) ";
	if($bg>0)	{
		//print "<TR><TD COLSPAN=\"6\">budget_id=$bg</TD></TR>\n";
		$sql .= " and t.budget_id=$bg ";
	}
	$sql .= "order by transaction_date";
        $fsql = formatSQL($conn, $sql);
        $res = $conn->query($fsql);
	//$res = mysql_query($sql,$conn);
	$sm = 0;
	$sd = 0;
	$plus_pict = "picts/plus.gif";
	$minus_pict = "picts/minus.gif";
	$locale_info = localeconv();
	if($res)	{
            while ($row =  $res->fetch(PDO::FETCH_ASSOC)) {
                $cid = $row['t_cid'];
                $bid = $row['bc_id'];
                $ns = $row['transaction_sum'];
                if($cid!=$bid) {
//                    $row['sum_txt'] = number_format(
//                            f_get_exchange_rate($conn, $row['t_cid'],$row['transaction_date'],$row['transaction_sum'] ),2,","," ");
                    $row['sum_txt'] = $row['currency_sign'] . number_format($ns, 2, ",", " ");
                    $row['transaction_sum'] = f_get_exchange_rate($conn, $row['t_cid'],$row['transaction_date'], $ns );
                }
                else    {
                    $row['sum_txt'] = number_format($ns, 2, ",", " ");
                }
                $ts = $row['type_sign'];
                if($ts>0)	{
                    $row['tl_class'] = "tl_plus";//tl_plus
                    $pn = $plus_pict;
                    $sd += $row['transaction_sum'];
                }
                else	if($ts<0)	{
                    $row['tl_class'] = "tl_minus";//tl_minus
                    $pn = $minus_pict;
                    $sm += $row['transaction_sum'];
                }
                else	{
                    $row['tl_class'] = "tl_none";//tl_none
                        $pn = "";
                }
                $row['disp_date'] = f_get_disp_date($row['transaction_date']);
                echo $tb->htmlRow($row);
            }	
        }
	else	{
            $message  = f_get_error_text($conn, "Invalid query: ");
            print $tb->htmlError("$message - $sql");
	}
	print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"Запрос выполнен " . date("d.m.Y H:i:s") . "\" ALIGN=\"RIGHT\">";
	$t = number_format($sd,2,","," ");
        if($sd>0)
            $c_class = "tl_plus";//tl_plus
        else
            $c_class = "tl_none";//tl_none
	print "Итого, доходы:</TD><TD COLSPAN=\"4\"><LABEL class=\"$c_class\">$t</LABEL></TD></TR>" . PHP_EOL;
	print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" ALIGN=\"RIGHT\">";
	$t = number_format($sm,2,","," ");
        if($sm>0)
            $c_class = "tl_minus";//tl_minus
        else
            $c_class = "tl_none";//tl_none
	print "Итого, расходы:</TD><TD COLSPAN=\"4\"><LABEL class=\"$c_class\">$t</LABEL></TD></TR>" . PHP_EOL;
	$sr = $sd - $sm;
	$c_class = "white_bold";
	if($sr>0)
        {
            $c_class = "tl_plus";//tl_plus
            $t = number_format($sr,2,","," ");
        }
	else
        {
            if($sr<0)
                $c_class = "tl_minus";//tl_minus
            else
                $c_class = "tl_none";//tl_none
            $t = number_format($sr*-1,2,","," ");
        }
	print "<TR  class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"Расходы - Доходы\" ALIGN=\"RIGHT\">";
	print "Итого, разница:</TD><TD COLSPAN=\"4\"><LABEL class=\"$c_class\">$t</LABEL></TD></TR>" . PHP_EOL;
	echo $tb->htmlClose();
	print_buttons("add_click2();");
        if($a_vg)   {
            $a_ctls = array('transaction_name'=>'t_name_a',
                'transaction_sum'=>'t_sum_a',
                'transaction_date'=>'t_date_a',
                't_cid'=>'t_curr_a',
                't_type_id'=>'t_type_a',
                'place_id'=>'t_place_a',
                'budget_id'=>'t_budget_a');
            foreach ($a_vg as $vkey => $vvalue) {
                if(key_exists($vkey, $a_ctls))   {
                    $a2 = array_flip($vvalue);
                    printf('<input type="hidden" id="%s" value="%s">%s', 
                            $a_ctls[$vkey], current($a2), PHP_EOL);
                }
            }
        }
}

        echo "<input type='hidden' id='main_sql' value=\"$fsql\">\n";
?>
        </form>
        </div>
    </body>

</html>
