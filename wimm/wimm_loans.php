<!DOCTYPE html>
<?php
    include_once ("fun_web.php");
    $uid = page_pre();
    if($uid===FALSE)
        die();
    include_once 'fun_dbms.php';
    include_once 'table.php';
    $conn = f_get_connection();
    $dtm = new DateTime();
    $ldfmt = 'Y-m-01';//str_replace('d','01',getSessionParam('locale_date_format', 'd.m.Y'));
    $bd = update_param("BDATE", "BEG_DATE", $dtm->format($ldfmt));
    $dtm->add(new DateInterval('P1M'));
    $ed = update_param("EDATE", "END_DATE", $dtm->format($ldfmt));
    $dtm = DateTime::createFromFormat('Y-m-d', $bd);
    $ldfmt = getSessionParam('locale_date_format', 'd.m.Y');
    $dtm2 = DateTime::createFromFormat('Y-m-d', $ed);
    $p_title = 'Кредиты, активные с ' . $dtm->format($ldfmt) . ' по ' . $dtm2->format($ldfmt);
    print_head($p_title);
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="STYLESHEET" href="css/wimm.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/bootstrap.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/jquery_autocomplete_ifd.css" type="text/css"/>
        <link rel="SHORTCUT ICON" href="picts/favicon.ico">
        <title><?php echo $p_title; ?></title>
    </head>
    <body onload="onLoad();">
        <div class="container">
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
	print_body_title($p_title);
?>        
        <script language="JavaScript" type="text/JavaScript" src="js/jquery-ui.js"></script>
        <script language="JavaScript" type="text/JavaScript" src="js/form_common.js"></script>
        <script language="JavaScript" type="text/JavaScript" src="js/jquery_autocomplete_ifd.js"></script>
        <script language="JavaScript" type="text/JavaScript" src="js/bootstrap.js"></script>
        <script language="JavaScript" type="text/JavaScript">
            function onLoad()
            {
                $(window).scroll(function(e) {
                    var height = $(window).scrollTop();
                    var h = $("#buttonz").offset();
                    console.log("nav:" + h.top);
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
                $('#dialog_box').draggable();
                ac_init("ac", ".txt");
                $(".row_sel").click(function(e)
                {
                    $('#dlg_box_cap').text('Редактировать');
                    $('#DEL_BTN').show();
                    $('#FRM_MODE').val('update');
                    table_row_selected("#"+e.currentTarget.id, "#edit_form");
                    $("#HIDDEN_ID").val(e.currentTarget.id);
                    $("#dialog_box").modal('show');
                    document.getElementById('curr_name').focus();
                });
            }
            function onAdd()
            {
                $('#dlg_box_cap').text('Добавить');
                $('#DEL_BTN').hide();
                $('#HIDDEN_ID').val('');
                $('.form_field').val('');
                $('#FRM_MODE').val('insert');
                document.getElementById('l_user').focus();
            }
            function send_submit(frm_mode)
            {
                if(frm_mode!=null && frm_mode.length>0)
                {
                    $('#FRM_MODE').val(frm_mode);
                }
                var s1 = $('#FRM_MODE').val();
                var s2 = true;
                var my_form;
                switch(s1)
                {
                    case 'exit':
                        my_form = document.getElementById('edit_form');
                        if(my_form!=null)
                            my_form.action='index.php';
                        $('#FRM_MODE').val('return');
                        //my_form.submit();
                        break;
                    case 'del':
                        if($( ".row_sel:checked" ).length<1)
                        {
                            alert('Строка для удаления не выбрана');
                            s2 = false;
                        }
                        break;
                    case 'edit':
                    case 'add':
                        if($("#curr_name").val().length<1)
                        {
                            alert('Надо заполнить Наименование');
                            $("#curr_name").select();
                            s2 = false;
                        }
                        break;
                }
                console.log("mode="+$('#FRM_MODE').val());
                if(s2)
                    $('#edit_form').submit();
            }
        </script>
        <form id="edit_form" name="curr_rates" method="post" role="form">
            <div id="dialog_box" class="ui-widget-content modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header" id="dlg_box_cap">Редакторовать</div>
                        <div class="modal-body" id="dlg_box_text">
                            <div scroll_height="100" for="" selected_ac_item="" class="ac_list" id="ac"></div>
                            <div class="form-group">
                                <label class="control-label" for="l_user">Кто взял:</label>
                                <select size="1" name="l_user" id="l_user" class="form-control form_field"
                                        bind_row_type="title" bind_row_id="USR_" >
                                    <?php
                                    $sql = "select user_id, user_name from m_users where close_date is null";
                                    f_set_sel_options2($conn, $sql, -1, -1, 6);
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="l_name">Наименование:</label>
                                <input type="text" name="l_name" id="l_name" class="form-control form_field" 
                                       bind_row_type="label" bind_row_id="LNAME_" value="">
                            </div>
                            <div class="form-group">
                                <label for="l_rate">Ставка, %:</label>
                                <input type="number" name="l_rate" id="l_rate" 
                                       class="form-control form_field" value=""
                                       bind_row_type="title" bind_row_id="FRATE_">
                            </div>
                            <div class="form-group">
                                <label for="curr_to_name">Валюта:</label>
                                <input type="hidden" name="l_curr" id="l_curr" value=""
                                       bind_row_type="title" bind_row_id="CURR_"
                                       class="form_field">
                                <input type="text" class="form-control form_field txt" 
                                       id="curr_to_name" bind_row_type="value" 
                                       bind_row_id="CURR_" value="" autocomplete="off" 
                                       bound_id="l_curr" ac_src="<?php echo get_autocomplete_url();?>"
                                       ac_params="type=t_curr;filter=">
                            </div>
                            <div class="form-group">
                                <label for="l_sum">Сумма:</label>
                                <input type="number" name="l_sum" id="l_sum" 
                                       class="form-control form_field" value=""
                                       bind_row_type="label" bind_row_id="FRATE_">
                            </div>
                            <div class="form-group">
                                <label for="l_bdate">Взят:</label>
                                <input type="date" name="l_bdate" id="l_bdate" 
                                       class="form-control form_field" value=""
                                       bind_row_type="label" bind_row_id="CBDATE_">
                            </div>
                            <div class="form-group">
                                <label for="l_edate">Вернуть до:</label>
                                <input type="date" name="l_edate" id="l_edate" 
                                       class="form-control form_field" value=""
                                       bind_row_type="label" bind_row_id="CEDATE_">
                            </div>
                            <div class="form-group">
                                <label for="place_name">Кто выдал:</label>
                                <input type="hidden" name="l_place" id="l_place" value=""
                                       bind_row_type="title" bind_row_id="PLACE_"
                                       class="form_field valid sendable"
                                       pattern="^[1-9][0-9]*$" focus_on="t_place_txt">
                                <input type="text" class="form-control form_field txt" 
                                       id="place_name" bind_row_type="label" 
                                       bind_row_id="PLACE_" value="" autocomplete="off" 
                                       bound_id="l_place" ac_src="<?php echo get_autocomplete_url();?>"
                                       ac_params="type=t_place;filter=">
                            </div>
                            <div class="form-group">
                                <label for="t_budget_txt">Бюджет:</label>
                                <input type="hidden" name="l_budget" id="l_budget" 
                                       bind_row_type="title" bind_row_id="BUDG_"
                                       value="" class="form_field valid sendable">
                                <input type="text" class="form-control form_field txt" value=""
                                       bind_row_type="value" bind_row_id="BUDG_"
                                       autocomplete="off" bound_id="l_budget" 
                                       ac_src="<?php echo get_autocomplete_url();?>" 
                                       ac_params="type=t_budget;filter=" id="t_budget_txt">
                            </div>
                            <div class="form-group">
                                <input type="checkbox" id="returned">
                                <label for="returned">Возвращён:</label>
                                <input type="date" name="l_2date" id="l_2date" 
                                       class="form-control form_field" value=""
                                       bind_row_type="title" bind_row_id="CEDATE_">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn" id="OK_BTN" type="submit">
                                <span class="glyphicon glyphicon-save"></span> Сохранить
                            </button>
                            <button class="btn" id="DEL_BTN" type="button"
                                    onclick="send_submit('delete');">
                                <span class="glyphicon glyphicon-remove"></span> Удалить
                            </button>
                            <button class="btn" type="button"
                                    onclick="$('#FRM_MODE').val('refresh');"
                                    data-dismiss="modal">
                                <span class="glyphicon glyphicon-erase"></span> Отмена
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <DIV id="buttonz">
<?php
	print_buttons("onAdd();");
?>                
            </DIV>
        <?php
        // put your code here
        $fm = "refresh";
        if(getRequestParam("btn_refresh",FALSE)===FALSE)
        {
            $fm = getRequestParam("FRM_MODE","refresh");
        }
        $hfmt = "<input id=\"%s\" name=\"%s\" type=\"hidden\" value=\"%s\">" . PHP_EOL;
        printf($hfmt, "FRM_MODE", "FRM_MODE", "refresh");
        printf($hfmt, "HIDDEN_ID", "HIDDEN_ID", "0");
        include_once 'wimm_dml.php';
        if(strcmp($fm, 'refresh')!=0)
        {
            $a_ret = loan_dml($conn, $fm);
            embed_diag_out($a_ret);
        }
//        if($conn)	{
//            if(strcmp($fm,"insert")==0)	{
//		$sql = "INSERT INTO m_loans (place_id, loan_name, start_date, end_date, loan_rate, loan_type, " .
//                        "close_date, user_id, currency_id, budget_id, loan_sum) VALUES(";
//		$s = getRequestParam("l_place",1);
//		$sql .= "$s,";
//		$s = getRequestParam("l_name","Кредит!");
//		$sql .= "'$s',";
//		$td = getRequestParam("l_bdate",date("Y-m-d H:i:s"));
//		$sql .= "'$td',";
//		$td = getRequestParam("l_edate","");
//                if(strlen($td)>0)
//                    $sql .= "'$td',";
//                else
//                    $sql .= "NULL,";
//		$s = getRequestParam("l_rate",5);
//		$sql .= "$s,";
//		$s = getRequestParam("l_type",1);
//		$sql .= "$s,";
//		$td = getRequestParam("l_cdate","");
//                if(strlen($td)>0)
//                    $sql .= "'$td',";
//                else
//                    $sql .= "NULL,";
//		$s = getRequestParam("l_user",1);
//		$sql .= "$s,";
//		$s = getRequestParam("l_curr",2);
//		$sql .= "$s,";
//		$s = getRequestParam("l_budget",1);
//		$sql .= "$s,";
//		$s = getRequestParam("l_sum",0);
//		$sql .= "$s";
//                $sql .= ")";
//            }	else if(strcmp($fm,"update")==0)	{//!!!!!!!!!!!!!!!!!!
//                $sql = "update m_loans set ";
//                $s = getRequestParam("l_place",-1);
//                if($s!=-1)
//                    $sql .= "place_id=$s, ";
//		$s = getRequestParam("l_name","");
//                if(strlen($s)>0)
//                    $sql .= "loan_name='$s', ";
//		$td = getRequestParam("l_bdate",date("Y-m-d H:i:s"));
//		$sql .= "start_date='$td'";
//		$td = getRequestParam("l_edate","");
//                if(strlen($td)>0)
//                    $sql .= ", end_date='$td'";
//                else
//                    $sql .= ", end_date=NULL";
//		$s = getRequestParam("l_rate",-1);
//                if($s!=-1)
//                    $sql .= ", loan_rate=$s ";
//		$s = getRequestParam("l_type",-1);
//                if($s!=-1)
//                    $sql .= ", loan_type=$s ";
//		$td = getRequestParam("l_cdate","");
//                if(strlen($td)>0)
//                    $sql .= ", close_date='$td'";
//                else
//                    $sql .= ", close_date=NULL";
//		$s = getRequestParam("l_user",-1);
//                if($s!=-1)
//                    $sql .= ", user_id=$s ";
//		$s = getRequestParam("l_curr",-1);
//                if($s!=-1)
//                    $sql .= ", currency_id=$s ";
//		$s = getRequestParam("l_budget",-1);
//                if($s!=-1)
//                    $sql .= ", budget_id=$s ";
//		$s = getRequestParam("l_sum",-1);
//                if($s!=-1)
//                    $sql .= ", loan_sum=$s ";
//                $s = getRequestParam("HIDDEN_ID",0);
//		$sql .= "where loan_id=$s";
//            }   else if(strcmp($fm,"delete")==0)	{
//		$s = getRequestParam("HIDDEN_ID",0);
//		$sql = "delete from m_loans where loan_id=$s";                
//            }
//            printf($hfmt, "SQL", "SQL", $sql);
//            if(strlen($sql)>0)	{
//                $conn->query(formatSQL($conn, $sql));
//                //$conn->commit();
//            }
//        }
	$bg = getRequestParam("f_budget","-1");
	print_filter($conn, $bd, $ed, $bg);
//        print_buttons("onAdd();");
        $tb = new table();
        $tb->setValue(tbase::$PN_CLASS, "table table-bordered table-responsive table-striped visual2");
        $tb->setIndent(3);
        $tb->addColumn(new tcol("Наименование"), TRUE);
        $tb->addColumn(new tcol("Сумма"), TRUE);
        $tb->addColumn(new tcol("Взят"), TRUE);
        $tb->addColumn(new tcol("Срок до"), TRUE);
        $tb->addColumn(new tcol("Кто"), TRUE);
        $tb->addColumn(new tcol("Где"), TRUE);
        $tb->body->setValue(tbody::$PN_ROW_CLASS, "table-hover");
        $fmt_str = "<input name='ROW_ID' ID='=loan_id' type='radio' value='=loan_id' class='row_sel'>" .
                "<LABEL class='td' id='LNAME_=loan_id' FOR='=loan_id'>=loan_name</LABEL>".
                '<input type="hidden" id="CURR_=loan_id" title="=t_cid" value="=currency_name">'.
                '<input type="hidden" id="BUDG_=loan_id" title="=bc_id" value="=budget_name">';
        $tb->addColumn(new tcol($fmt_str), FALSE);
        $tb->addColumn(new tcol("<LABEL class='td' id=\"FRATE_=loan_id\" FOR=\"=loan_id\" TITLE=\"=loan_rate\">=loan_sum</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL class='td' id=\"CBDATE_=loan_id\" FOR=\"=loan_id\">=start_date</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL class='td' id=\"CEDATE_=loan_id\" FOR=\"=loan_id\" title=\"=close_date\">=end_date</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL class='td' id=\"USR_=loan_id\" FOR=\"=loan_id\" TITLE=\"=user_id\">=user_name</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL class='td' id=\"PLACE_=loan_id\" FOR=\"=loan_id\" TITLE=\"=place_id\">=place_name</LABEL>"), FALSE);
        $sql = "select loan_id, loan_name, loan_sum, loan_rate, start_date, end_date, user_name, ml.user_id, ".
                "place_name, ml.currency_id t_cid, mcu.currency_sign, mcu.currency_name, ".
                "mb.budget_name, mb.currency_id as bc_id, ml.place_id, ml.close_date " .
                "from m_loans ml, m_users mu, m_places mp, m_currency mcu, m_budget mb ".
                "where ml.user_id=mu.user_id and ml.place_id=mp.place_id and ".
                "ml.currency_id=mcu.currency_id and ml.budget_id=mb.budget_id and ".
                "ml.close_date is null";
	if($bg>0)	{
		$sql .= " and ml.budget_id=$bg ";
	}
        $sql .= " and (start_date between '$bd' and '$ed' or end_date between '$bd' and '$ed') ";
	$sql .= " order by end_date";
        printf($hfmt, "SQL2", "SQL2", $sql);
        echo $tb->htmlOpen();
	$res = $conn->query($sql);
	$sm = 0;
	$sd = 0;
	if($res)	{		//print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                    echo $tb->htmlRow($row);
		}	
        }
	else	{
            $message  = f_get_error_text($conn, "Invalid query: ");
            print "<TR><TD COLSPAN=\"6\">$message</TD></TR>\n";
	}
	print "<TR class=\"white_bold\"><TD COLSPAN=\"3\" TITLE=\"Запрос выполнен " . date("d.m.Y H:i:s") . "\" ALIGN=\"RIGHT\">";
	$t = number_format($sd,2,","," ");
	print "Итого, набрали кредитов на:</TD><TD COLSPAN=\"3\">&nbsp;$t</TD></TR>\n";
	$t = number_format($sm,2,","," ");
        echo $tb->htmlClose();
//	print_buttons("onAdd();");
       ?>
        </form>
    </body>
</html>
