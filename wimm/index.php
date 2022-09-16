<?php
    include_once ("fun_web.php");
    $uid = page_pre();
    if ($uid === FALSE) {
        die();
    }
    $values = ['uid'=>$uid, 'row_bound'=>TRUE];
    include_once 'fun_dbms.php';
    include_once 'table.php';
    $curDir = dirname($_SERVER['PHP_SELF']);
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
<?php
    /**
     * @var DateTime current date and time to fill empty values from 
     *      BDATE/BEG_DATE and EDATE/END_DATE
     */
    $date_be = new DateTime();
    $cdfmt = 'Y-m-01';//str_replace('d','01',getSessionParam('locale_date_format', 'd.m.Y'));
    $bd = update_param("BDATE", "BEG_DATE", $date_be->format($cdfmt));
    $date_be->add(new DateInterval('P1M'));
    $ed = update_param("EDATE", "END_DATE", $date_be->format($cdfmt));
    $bdate_head = DateTime::createFromFormat('Y-m-d', $bd);
    $hdfmt = getSessionParam('locale_date_format', 'd.m.Y');
    $edate_head = DateTime::createFromFormat('Y-m-d', $ed);
    print_body_title('Расходы с ' . $bdate_head->format($hdfmt) . ' по ' . 
                $edate_head->format($hdfmt));
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
                    echo '    <div><label for="dup_r">Дублирование записи:'. 
                            '</label>' . PHP_EOL;
                    echo '        <a id="dup_r" href="wimm_edit2.php?REC_ID='.
                            $vdml.'"'. PHP_EOL;
                    echo '           title="Используйте эту ссылку для просмотра"'.
                            PHP_EOL;
                    echo '           target="_blank">'.$vdml.'</a>'.PHP_EOL;
                    echo '    </div>'.PHP_EOL;
                    break;
                case 'sql':
                    print "	<input ID=\"SQL\" type=\"hidden\" ". 
                            "value=\"$vdml\">\n";
                    break;
                case DML_RET_DBG:
                    print "<div id='$kdml'>$vdml</div>" . PHP_EOL;
            }
        }
        $s = "";
?>
	<form id="expenses" name="expenses" method="post" accept-charset="utf-8">
            <input id="FRM_MODE" name="FRM_MODE" type="hidden" value="refresh">
            <input id="HIDDEN_ID" name="HIDDEN_ID" type="hidden" value="0">
            <input name="UID" type="hidden" value="<?php echo getRequestParam("UID", "");?>">
            <!-- Import -->
            <DIV class="ui-widget-content modal fade" id="import_box" role="dialog">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <DIV class="modal-header" id="dlg_box_cap">Импорт текста</DIV>
                        <DIV class="modal-body" id="dlg_box_text" >
                            <div class="form-group">
                                <label for="txt2Import">Текст для импорта:</label>
                                <textarea id="txt2Import" rows="12" cols="60"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="sel_for">Оплата за:</label>
                                <select size="1" id="sel_for">
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sel_type">Тип платежа:</label>
                                <select size="1" id="sel_type">
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sel_amount">Сумма платежа:</label>
                                <select size="1" id="sel_amount">
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sel_dtm">Дата и время платежа:</label>
                                <select size="1" id="sel_dtm">
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sel_where">Получатель платежа:</label>
                                <select size="1" id="sel_where">
                                </select>
                            </div>
                        </DIV>
                        <DIV class="modal-footer" id="dlg_box_btns">
                            <button class="btn" id="DETECT_BTN" type="button"
                                    onclick="detectFields();">
                                <span class="glyphicon glyphicon-list"></span> Разобрать
                            </button>
                            <button class="btn" id="TRANSFER_BTN" type="button"
                                    onclick="transferData('#dialog_box','#import_box','<?php echo get_autocomplete_url();?>');"
                                    data-dismiss="modal">
                                <span class="glyphicon glyphicon-ok"></span> Перенести
                            </button>
                            <button class="btn" type="button"
                                    onclick="doCancelImport('#import_box');"
                                    data-dismiss="modal">
                                <span class="glyphicon glyphicon-erase"></span> Отмена
                            </button>
                        </DIV>
                    </div><!--modal-content-->
                </div><!--modal-dialog-->
            </div><!--dialog-box-->
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
	f_set_sel_options2($conn, formatSQL($conn, $sql), $values['uid'], $values['uid'], 2);
?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="t_budget_txt">Бюджет:</label>
                                <input type="hidden" name="t_budget" id="t_budget" class="form_field valid sendable" value=""
                                       bind_row_type="value" bind_row_id="T_BUDG_"
                                       pattern="^[1-9][0-9]*$" focus_on="t_budget_txt" onchange="budget_update();">
                                <input type="text" class="form-control form_field txt" value=""
                                       autocomplete="off" bound_id="t_budget" ac_src="<?php echo get_autocomplete_url();?>" 
                                       ac_params="type=t_budget;ac_filter=" id="t_budget_txt"
                                       bind_row_type="title" bind_row_id="T_BUDG_">
                            </div>
                            <div class="form-group">
                                <label for="t_name">Наименование:</label>
                                <input class="form-control form_field valid sendable" name="t_name" id="t_name" 
                                       type="text" bind_row_type="label" bind_row_id="TNAME_" 
                                       pattern="^(?!\s*$).+" value="">
                            </div>
                            <div class="form-group">
                                <label for="t_type_txt">Тип:</label>
                                <input class="form_field valid sendable" type="hidden" name="t_type" id="t_type" 
                                       bind_row_type="value" bind_row_id="T_TYPE_" value=""
                                       pattern="^[1-9][0-9]*$" focus_on="t_type_txt">
                                <input type="text" name="t_type_name" class="form-control form_field txt" value=""
                                       autocomplete="off" bound_id="t_type" ac_src="<?php echo get_autocomplete_url();?>" 
                                       ac_params="type=t_type;ac_filter=" id="t_type_txt" scroll_height="10"
                                       bind_row_type="title" bind_row_id="TNAME_">
                            </div>
                            <div class="form-group">
                                <label for="t_curr_txt">Валюта:</label>
                                <input type="hidden" name="t_curr" id="t_curr" class="form_field valid sendable" value=""
                                       bind_row_type="value" bind_row_id="T_CURR_"
                                       pattern="^[1-9][0-9]*$" focus_on="t_curr_txt">
                                <input type="text" class="form-control form_field txt" value=""
                                       autocomplete="off" bound_id="t_curr" ac_src="<?php echo get_autocomplete_url();?>" 
                                       ac_params="type=t_curr;ac_filter=" id="t_curr_txt"
                                       bind_row_type="title" bind_row_id="T_CURR_">
                            </div>
                            <div class="form-group">
                                <label for="t_sum">Сумма:</label>
                                <input class="form-control form_field valid sendable" id="t_sum" name="t_sum" 
                                       type="number" value="" bind_row_type="title" bind_row_id="T_SUMM_"
                                       pattern="^[1-9]\d*([.,]\d{1,2})?$"
                                       step="0.01">
                            </div>
                            <div class="form-group">
                                <label for="t_date">Дата:</label>
                                <input class="dtp form-control form_field valid sendable" id="t_date" 
                                       name="t_date" type="text" value="" bind_row_type="title" 
                                       pattern="^[1-2]\d{3}-([0][1-9]|[1][0-2])-([0][1-9]|[1-2][0-9]|[3][0-1]) ([0-1][0-9]|[2][0-3]):[0-5][0-9]:[0-5][0-9]$" 
                                       bind_row_id="T_DATE_" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="t_place_txt">Место:</label>
                                <input type="hidden" name="t_place" id="t_place" class="form_field valid sendable" value=""
                                       bind_row_type="value" bind_row_id="T_PLACE_"
                                       pattern="^[1-9][0-9]*$" focus_on="t_place_txt">
                                <input type="text" class="form-control form_field txt" value=""
                                       autocomplete="off" bound_id="t_place" ac_src="<?php echo get_autocomplete_url();?>" 
                                       ac_params="type=t_place;ac_filter=" id="t_place_txt"
                                       bind_row_type="label" bind_row_id="TP_NAME_">
                            </div>
                            <div class="form-group">
                                <input type="checkbox" id="use_credit" name="use_credit" 
                                       bind_row_type="value" bind_row_id="T_CRED_"
                                       focus_on="t_credit_txt" value="" class="form_field sendable" 
                                       onclick="toggle_credit();">
                                <label for="use_credit">В кредит</label>
                                <label for="t_credit_txt">:</label>
                                <input type="text" class="form-control form_field txt" value=""
                                       autocomplete="off" bound_id="use_credit" ac_src="<?php echo get_autocomplete_url();?>" 
                                       ac_params="type=t_credit;ac_filter=" id="t_credit_txt"  style="display:none"
                                       bind_row_type="title" bind_row_id="T_CRED_">
                            </div>
                        </DIV>
                        <DIV class="modal-footer" id="dlg_box_btns">
                            <button class="btn" id="OK_BTN" type="button"
                                    onclick="if(fancy_form_validate('expenses')) tx_submit('<?php echo $curDir;?>/wimm_edit2.php');">
                                <span class="glyphicon glyphicon-save">
                                </span> Сохранить
                            </button>
                            <button class="btn" type="button"
                                    onclick="submit_myform('expenses','wimm_edit2.php','refresh');"
                                    data-dismiss="modal">
                                <span class="glyphicon glyphicon-edit">
                                </span> Подробнее...
                            </button>
                            <button class="btn" type="button"
                                    onclick="openImport();">
                                <span class="glyphicon glyphicon-magnet">
                                </span> Импорт текста...
                            </button>
                            <button class="btn" id="DEL_BTN" type="button"
                                    onclick="del_click2();">
                                <span class="glyphicon glyphicon-remove">
                                </span> Удалить
                            </button>
                            <button class="btn" type="button"
                                    onclick="doCancel2();"
                                    data-dismiss="modal">
                                <span class="glyphicon glyphicon-erase">
                                </span> Отмена
                            </button>
                        </DIV>
                    </div>
                </div>
            </DIV>
            <DIV id="buttonz">
<?php
	$bg = getRequestParam("f_budget","-1");
        $bc = getRequestParam("f_credit","-1");
	print_filter($conn, $bd, $ed, $bg, $bc);
        print_buttons("add_click2();");
?>
            </DIV>
<?php
        $tb = new table();
        $tb->setValue(tbase::$PN_CLASS, "table table-bordered table-responsive table-striped visual2");
        $tb->setIndent(3);
        // header
        $tc1 = new tcol("Описание");
        $tc1->setValue("WIDTH", "38%");
        $tb->addColumn($tc1, TRUE);
        $tc2 = new tcol("Сумма");
        $tc2->setValue("WIDTH", "10%");
        $tb->addColumn($tc2, TRUE);
        $tc3 = new tcol("Дата и время");
        $tc3->setValue("WIDTH", "15%");
        $tb->addColumn($tc3, TRUE);
        $tc4 = new tcol("Кто");
        $tc4->setValue("WIDTH", "17%");
        $tb->addColumn($tc4, TRUE);
        $tc5 = new tcol("Где");
        $tc5->setValue("WIDTH", "20%");
        $tb->addColumn($tc5, TRUE);
        // body
        $tb->body->setValue(tbody::$PN_ROW_CLASS, "table-hover");
        $fmt_str = "<input class='row_sel' name=\"ROW_ID\" ID=\"=transaction_id\" type=\"radio\" value=\"=transaction_id\">" .
                "<label class='td' TITLE=\"=t_type_name\" id=\"TNAME_=transaction_id\" FOR=\"=transaction_id\">=transaction_name</label>";
        $tb->addColumn(new tcol($fmt_str), FALSE);
        $fmt_str2 = '<label class="td =tl_class" ID="T_SUMM_=transaction_id" FOR="=transaction_id" title="=transaction_sum">=sum_txt</label>'.
                        '<input type="hidden" id="T_TYPE_=transaction_id" value="=t_type_id">'.
                        '<input type="hidden" title="=currency_name" id="T_CURR_=transaction_id" value="=t_cid">';
        $tb->addColumn(new tcol($fmt_str2), FALSE);
        $tb->addColumn(new tcol('<label class="td" id="T_DATE_=transaction_id" for="=transaction_id" TITLE="=transaction_date">=disp_date</label>'), FALSE);
        $tb->addColumn(new tcol("<label class='td' id=\"L_USR_=transaction_id\"FOR=\"=transaction_id\">=user_name</label>" .
                        "<input type=\"hidden\" id=\"T_USR_=transaction_id\" value=\"=user_id\">"), FALSE);
        $tb->addColumn(new tcol("<label class='td' id=\"TP_NAME_=transaction_id\" TITLE=\"=place_descr\" FOR=\"=transaction_id\">=place_name</label>".
                        "<input type=\"hidden\" id=\"T_PLACE_=transaction_id\" value=\"=place_id\">".
                        "<input type=\"hidden\" id=\"T_BUDG_=transaction_id\" value=\"=budget_id\" title=\"=budget_name\">".
                        "<input type=\"hidden\" id=\"T_CRED_=transaction_id\" value=\"=loan_id\" title=\"=loan_name\">"), FALSE);
?>
            <input type="hidden" id="bg" value="<?php echo $bg;?>">
<?php
    echo $tb->htmlOpen();
	//print "<TR><TD COLSPAN=\"6\">Подключён</TD></TR>\n";
        $a_vg = false;
	$sql = "select transaction_id, t_type_name, transaction_name, transaction_sum, "
                . "type_sign, transaction_date, user_name, place_name, place_descr, "
                . "t.currency_id t_cid, #CONCAT#(mcu.currency_name #||# ' (' #||# mcu.currency_abbr #||# ')') as currency_name, "
                . "mcu.currency_sign, mb.currency_id as bc_id, t.place_id, t.budget_id, "
                . "t.user_id, t.t_type_id, mb.budget_name, t.loan_id, ml.loan_name "
                . "from m_transactions t left join m_loans ml on t.loan_id=ml.loan_id, m_transaction_types tt, m_users tu, "
                . "m_places tp, m_currency mcu, m_budget mb "
                . "where t.t_type_id=tt.t_type_id and t.user_id=tu.user_id and "
                . "t.place_id=tp.place_id and t.currency_id=mcu.currency_id and "
                . "t.budget_id=mb.budget_id and "
                . "#TODATE#(transaction_date#ISO_DATETIME#)>=#TODATE#('$bd'#ISO_DATE#) and "
                . "#TODATE#(transaction_date#ISO_DATETIME#)<#TODATE#('$ed'#ISO_DATE#) ";
	if($bg>0)
        {
            $sql .= " and t.budget_id=$bg ";
	}
	if($bc>0)
        {
            $sql .= " and t.loan_id=$bc ";
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
                if($cid!=$bid)
                {
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
	$income_total = number_format($sd,2,',',' ');
        if ($sd > 0) {
            $c_class = "tl_plus";
        }//tl_plus
        else {
            $c_class = "tl_none";
        } //tl_none
	print "Итого, доходы:</TD><TD COLSPAN=\"4\"><LABEL class=\"$c_class\">"
                . "$income_total</LABEL></TD></TR>" . PHP_EOL;
	print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" ALIGN=\"RIGHT\">";
	$expenses_total = number_format($sm,2,',',' ');
        if ($sm > 0) {
            $c_class = "tl_minus";
        }//tl_minus
        else {
            $c_class = "tl_none";
        } //tl_none
	print "Итого, расходы:</TD><TD COLSPAN=\"4\"><LABEL class=\"$c_class\">"
                . "$expenses_total</LABEL></TD></TR>" . PHP_EOL;
	$sr = $sd - $sm;
	$c_class = "white_bold";
	if($sr>0)
        {
            $c_class = "tl_plus";//tl_plus
            $diff_total = number_format($sr,2,","," ");
        }
	else
        {
            if ($sr < 0) {
                $c_class = "tl_minus";
            }//tl_minus
            else {
                $c_class = "tl_none";
            } //tl_none
            $diff_total = number_format($sr*-1,2,","," ");
        }
        print "<TR  class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"Расходы - "
            . "Доходы\" ALIGN=\"RIGHT\">";
        print "Итого, разница:</TD><TD COLSPAN=\"4\"><LABEL class=\"$c_class\">"
                . "$diff_total</LABEL></TD></TR>" . PHP_EOL;
	echo $tb->htmlClose();
	//print_buttons("add_click2();");
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
    <script language="JavaScript" type="text/JavaScript" src="js/form_common.js"></script>
<?php    
    if(isMSIE())   {
        echo '<script language="JavaScript" type="text/JavaScript" '
            . 'src="js/jquery-1.11.1.js"></script>' . PHP_EOL;
        echo '<script language="JavaScript" type="text/JavaScript" '
            . 'src="js/json2.js"></script>' . PHP_EOL;
    }
    else {
        echo '<script language="JavaScript" type="text/JavaScript" '
            . 'src="js/jquery-2.1.1.js"></script>' . PHP_EOL;
    }
?>        
    <script language="JavaScript" type="text/JavaScript" src="js/jquery-ui.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="js/index_aj.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="js/jquery_autocomplete_ifd.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="js/bootstrap.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="js/invoice_text_import.js"></script>
    <script language="JavaScript" type="text/JavaScript">
        var tmr;
        var tmr2;
        var to;
        function focus_fun()
        {
            //console.log("focus_fun() begin");
            if($('#FRM_MODE').val()=='insert')
            {
                document.getElementById('t_user').focus();
                var d = new Date();
                var s = d.toISOString().replace("T"," ");
                document.getElementById('t_date').value = s.substr(0,19);
            }
            else
            {
                if($("#use_credit").val().length>0)
                {
                    $("#use_credit").prop("checked",true);
                    $("#l_credit_txt").show();
                    $("#t_credit_txt").show();
                }
                document.getElementById('t_name').focus();
            }
            clearTimeout(tmr);
            //console.log("focus_fun() end");
        }
        function onLoad2()
        {
            console.log("page loaded");
            to = 500;
            $(window).scroll(function(e) {
                var height = $(window).scrollTop();
                var h = $("#buttonz").offset();
                //console.log("nav:" + h.top);
                if(height>50)
                {
                    $(".btn_up").show();
                    $("#buttonz").addClass("filt_fixed");
                }
                else
                {
                    $(".btn_up").hide();
                    $("#buttonz").removeClass("filt_fixed");
                }
            });
            $("body").keydown(function(e) {
                onPageKey(e.keyCode);
            });
            setHandlers(".dtp");
			$('#t_sum').keydown(function(e)
			{
				switch(e.keyCode)
				{
					case 48:
					case 49:
					case 50:
					case 51:
					case 52:
					case 53:
					case 54:
					case 55:
					case 56:
					case 57:
						//console.log('keydown:Digit');
						break;
					case 96:
					case 97:
					case 98:
					case 99:
					case 100:
					case 101:
					case 102:
					case 103:
					case 104:
					case 105:
						//console.log('keydown:Numpad Digit');
						break;
					case 190:
						break;
					case 110:
					case 188:
						//console.log('keydown:replace , to .');
						e.keyCode = 190;
						e.charCode= 190;
						e.which   = 190;
						e.preventDefault();
						var e1 = jQuery.Event( "keydown", { keyCode: 190 } );
						//jQuery( '#t_sum' ).trigger(e1);//$('#t_sum').trigger(e1);
						e1.currentTarget = e.currentTarget;
						e1.data = e.data;
						e1.delegateTarget = e.delegateTarget;
						e1.metaKey = e.metaKey;
						//e1.namespace = e.namespace;
						e1.pageX = e.pageX;
						e1.pageY = e.pageY;
						e1.relatedTarget = e.relatedTarget;
						e1.result = e.result;
						e1.target = e.target;
						e1.timeStamp = e.timeStamp;
						e1.which = e.which;
						$( '#t_sum' ).trigger(e1);
						break;
					default:
						//console.log('keydown:Num code:' + e.keyCode.toString());
						break;
				}
			});
			$('#t_sum').keyup(function(e)
			{
				switch(e.keyCode)
				{
					case 48:
					case 49:
					case 50:
					case 51:
					case 52:
					case 53:
					case 54:
					case 55:
					case 56:
					case 57:
						//console.log('keyup:Digit');
						break;
					case 96:
					case 97:
					case 98:
					case 99:
					case 100:
					case 101:
					case 102:
					case 103:
					case 104:
					case 105:
						//console.log('keyup:Numpad Digit');
						break;
					case 190:
						break;
					case 110:
					case 188:
						//console.log('keyup:replace , to .');
						e.keyCode = 190;
						e.charCode= 190;
						e.which   = 190;
						e.preventDefault();
						var e1 = jQuery.Event( "keyup", { keyCode: 190 } );
						e1.currentTarget = e.currentTarget;
						e1.data = e.data;
						e1.delegateTarget = e.delegateTarget;
						e1.metaKey = e.metaKey;
						//e1.namespace = e.namespace;
						e1.pageX = e.pageX;
						e1.pageY = e.pageY;
						e1.relatedTarget = e.relatedTarget;
						e1.result = e.result;
						e1.target = e.target;
						e1.timeStamp = e.timeStamp;
						e1.which = e.which;
						$( '#t_sum' ).trigger(e1);
						break;
					default:
						//console.log('keyup:Num code:' + e.keyCode.toString());
						break;
				}
			});
            $('#dialog_box').draggable();
            $('#import_box').draggable();
            ac_init("ac", ".txt");
            $(".row_sel").click(function(e)
            {
                $('.dlg_box').show();
                $('#dlg_box_cap').text('Изменение записи');
                table_row_selected("#"+e.currentTarget.id, "#expenses");
                $('#HIDDEN_ID').val(e.currentTarget.id);
                $('#FRM_MODE').val('update');
                $("#dialog_box").modal('show');
                tmr = setTimeout(function(){ focus_fun(); }, to);
            });
            $('#dlg_box_text').show(function f()
            {
                //console.log("shown() begin with:"+gFilterEvent);
                if(gFilterEvent==false)
                {
                    //console.log("shown() begin");
                    tmr = setTimeout(function(){ focus_fun(); }, to);
                    //console.log("shown() end");
                }
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
                tx_submit('<?php echo $curDir;?>/wimm_edit2.php');
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
            tmr = setTimeout(function(){ focus_fun(); }, to);
        }
        function toggle_credit()
        {
            if($("#use_credit").prop("checked"))
            {
                $("#t_credit_txt").removeAttr('disabled');
		$("#t_credit_txt").show();
		//console.log("toggle_credit() show");
            }
            else
            {
                $("#use_credit").val("");
                $("#t_credit_txt").val("");
                $("#t_credit_txt").prop("disabled","true");
		$("#t_credit_txt").hide();
		//console.log("toggle_credit() hide");
            }
        }
        function parseCurrency(jsonData, textStatus, jqXHR, boxID)
        {
            if(!jsonData)
            {
                //console.log('entering parseCurrency() - null result');
                return ;
            }
            //console.log('entering parseCurrency()');
            var arr = jsonData;
            if(arr.length===1)
            {
                if(arr[0] && arr[0].id && arr[0].text)
                {
                    $("#t_curr").val(arr[0].id);
                    $("#t_curr_txt").val(arr[0].text);
                    //console.log('result ' + arr[0].id + ' set' + arr[0].text);
                }
                else
                {
                    console.log('invalid array');
                }
            }
            else
            {
                console.log('invalid array length');
            }
            //console.log('leaving parseCurrency()');
        }
        
        function budget_update2()
        {
            clearTimeout(tmr2);
            //console.log('entering budget_update2()');
            var v = $("#t_curr").val();
            var b = $("#t_budget").val();
            if(!v && b)
            {
                const query_src = "<?php echo get_autocomplete_url();?>";
                var d = new Date();
                var query_str = "type=t_budcur&ac_filter="+b+"&d="+d;
                console.log('params parsed: ' + query_str);
                console.log('query to: ' + query_src);
                // got query string - send request
                ac_jqxhr =  $.ajax({
                    type: "POST",
                    url: query_src,
                    cache: false,
                    dataType: "json",
                    data: query_str,
                    success: function(jsonData, textStatus, jqXHR){
                        parseCurrency(jsonData, textStatus, jqXHR, 'boxID');
                    }
                });
            }
            //console.log('leaving budget_update2()');
        }
        function budget_update()
        {
            //console.log('entering budget_update()');
            tmr2 = setTimeout(function(){ budget_update2(); }, to);
            //console.log('leaving budget_update()');
        }
    </script>
    </body>

</html>
