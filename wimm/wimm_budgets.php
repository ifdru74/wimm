<?php
    include_once ("fun_web.php");
    $uid = page_pre();
    if($uid===FALSE)
        die();
    include_once 'fun_dbms.php';
    include_once 'table.php';
    $p_title = "Редактор бюджетов, в рамках которых тратятся деньги";
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
        <script language="JavaScript" type="text/JavaScript" src="js/bootstrap.js"></script>
        <script language="JavaScript" type="text/JavaScript" src="js/index_aj.js"></script>
        <script language="JavaScript" type="text/JavaScript" src="js/jquery_autocomplete_ifd.js"></script>
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
                ac_init("ac", ".txt");
                $(".row_sel").click(function(e)
                {
                    $('#DEL_BTN').show();
                    $('#FRM_MODE').val('update');
                    table_row_selected("#"+e.currentTarget.id, "#edit_form");
                    $("#HIDDEN_ID").val(e.currentTarget.id);
                    $("#dialog_box").modal('show');
                    document.getElementById('b_name').focus();
                });
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
                        if($("#b_name").val().length<1)
                        {
                            alert('Надо заполнить Наименование');
                            $("#b_name").select();
                            s2 = false;
                        }
                        break;
                }
                console.log("mode="+$('#FRM_MODE').val());
                if(s2)
                    $('#edit_form').submit();
            }
            function onAdd()
            {
                $('#DEL_BTN').hide();
                $('#HIDDEN_ID').val('');
                $('.form_field').val('');
                $('#FRM_MODE').val('insert');
                $('#dlg_box_cap').text('Добавить');
                document.getElementById('b_name').focus();
            }

        </script>
        <script language="JavaScript" type="text/JavaScript" src="js/jquery_autocomplete_ifd.js"></script>
        <div class="container">
<?php    
    print_body_title($p_title);
?>        
    <form id="edit_form" name="places" action="wimm_budgets.php" method="post">
        <div class="ui-widget-content modal fade" id="dialog_box" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <DIV class="modal-header" id="dlg_box_cap">Изменение записи</DIV>
                    <DIV class="modal-body" id="dlg_box_text" >
                        <div scroll_height="100" for="" selected_ac_item="" class="ac_list" id="ac"></div>
                        <div class="form-group">
                            <label for="b_name">Наименование:</label>
                            <input class="form-control form_field" name="b_name" id="b_name" type="text" 
                                   bind_row_type="label" bind_row_id="BNAME_" value="">
                        </div>
                        <div class="form-group">
                            <label for="b_descr">Описание:</label>
                            <input class="form-control form_field" name="b_descr" id="b_descr" type="text" 
                                   bind_row_type="title" bind_row_id="BNAME_" value="">
                        </div>
                        <div class="form-group">
                            <label for="t_curr_txt">Валюта:</label>
                            <input type="hidden" name="b_curr_id" id="b_curr_id" class="form_field valid sendable" value=""
                                   bind_row_type="title" bind_row_id="T_CURR_"
                                   pattern="^[1-9][0-9]*$" focus_on="t_curr_txt">
                            <input type="text" class="form-control form_field txt" value=""
                                   autocomplete="off" bound_id="b_curr_id" ac_src="<?php echo get_autocomplete_url();?>" 
                                   ac_params="type=t_curr;ac_filter=" id="t_curr_txt"
                                   bind_row_type="label" bind_row_id="T_CURR_">                            
                        </div>
                        <div class="form-group">
                            <label for="b_parent">Родитель:</label>
                            <input type="hidden" name="b_parent_id" id="b_parent_id" class="form_field valid sendable" value=""
                                   bind_row_type="title" bind_row_id="T_PARENT_"
                                   pattern="^[1-9][0-9]*$" focus_on="b_parent">
                            <input type="text" class="form-control form_field txt" value=""
                                   autocomplete="off" bound_id="b_parent_id" ac_src="<?php echo get_autocomplete_url();?>" 
                                   ac_params="type=t_budget;ac_filter=" id="b_parent"
                                   bind_row_type="label" bind_row_id="T_PARENT_">
                        </div>
                    </div>
                    <DIV class="modal-footer" id="dlg_box_btns">
                        <button class="btn" id="OK_BTN" type="button"
                                onclick="send_submit();">
                            <span class="glyphicon glyphicon-save"></span> Сохранить
                        </button>
                        <button class="btn" id="DEL_BTN" type="button"
                                onclick="send_submit('delete');">
                            <span class="glyphicon glyphicon-remove"></span> Удалить
                        </button>
                        <button class="btn" type="button" data-dismiss="modal">
                            <span class="glyphicon glyphicon-erase"></span> Отмена
                        </button>
                    </DIV>
                </div>
            </div>
        </div>
            <DIV id="buttonz">
<?php
	print_buttons("onAdd();");
?>                
            </DIV>
<?php
$conn = f_get_connection();
if($conn)	{
        $fm = "refresh";
        if(getRequestParam("btn_refresh",FALSE)===FALSE)
        {
            $fm = getRequestParam("FRM_MODE","refresh");
        }
        $hfmt = "<input id=\"%s\" name=\"%s\" type=\"hidden\" value=\"%s\">" . PHP_EOL;
        $a_ret = array();
        if(strcmp($fm,'refresh')!=0)
        {
            include_once 'wimm_dml.php';
            $a_ret = budget_dml($conn, $fm);
            embed_diag_out($a_ret);
        }
        printf($hfmt, "FRM_MODE", "FRM_MODE", "refresh");
        printf($hfmt, "HIDDEN_ID", "HIDDEN_ID", "0");
//	print_buttons("onAdd();");
        if(key_exists('dup_id', $a_ret))
        {
            showError("Такой бюджет ({$a_ret['dup_id']}) уже есть! Он отмечен в таблице.");
        }
        $tb = new table();
        $tb->setValue(tbase::$PN_CLASS, "table table-bordered table-responsive table-striped visual2");
        $tb->setIndent(3);
        $tb->addColumn(new tcol("Наименование"), TRUE);
        $tb->addColumn(new tcol("Родитель"), TRUE);
        $tb->addColumn(new tcol("Валюта"), TRUE);
        $tb->addColumn(new tcol("Дата создания"), TRUE);
        $tb->addColumn(new tcol("Дата закрытия"), TRUE);
        $tb->addColumn(new tcol("Кто автор"), TRUE);
        $tb->body->setValue(tbody::$PN_ROW_CLASS, "table-hover");
        $fmt_str = "<input name='ROW_ID' ID='=budget_id' type='radio' value='=budget_id' class='row_sel' =checked>" .
                "<LABEL class='td' TITLE='=budget_descr' id='BNAME_=budget_id' FOR='=budget_id'>=budget_name</LABEL>";
        $tb->addColumn(new tcol($fmt_str), FALSE);
        $tb->addColumn(new tcol("<LABEL class='td' TITLE=\"=parent_id\" id=\"T_PARENT_=budget_id\" FOR=\"=budget_id\">=parent_name</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL class='td' TITLE=\"=currency_id\" id=\"T_CURR_=budget_id\" FOR=\"=budget_id\">=currency_name</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL class='td' TITLE=\"=open_date\" id=\"ODATE_=budget_id\" FOR=\"=budget_id\">=fopen_date</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL class='td' TITLE=\"=close_date\" id=\"CDATE_=budget_id\" FOR=\"=budget_id\">=fclose_date</LABEL>"), FALSE);
        $tb->addColumn(new tcol("<LABEL class='td' TITLE=\"=user_id\" id=\"USER_=budget_id\" FOR=\"=budget_id\">=user_name</LABEL>"), FALSE);
        
	$sql = "select budget_id, 
		   budget_name, 
		   budget_descr, 
		   tp.open_date, 
		   tp.close_date, 
		   user_name, 
		   tp.user_id, 
		   tp.currency_id, 
		   CONCAT( mcu.currency_name ,' (', mcu.currency_abbr , ')') as currency_name, 
		   tp.parent_id 
                from m_budget tp 
                join m_users tu on tp.user_id=tu.user_id
                join m_currency mcu on  tp.currency_id=mcu.currency_id
                where tp.close_date is null 
                order by budget_name";
	$res = $conn->query($sql);
	$sm = 0;
	$sd = 0;
	$c_class = "dark";
        echo $tb->htmlOpen();
	if($res)	{
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                if(key_exists('dup_id', $a_ret))
                {
                    if(strcmp($row['budget_id'],$a_ret['dup_id'])==0 )
                    {
                        $row['checked']='checked';
                    }
                }
                $row['fopen_date'] = f_get_disp_date($row['open_date']);
                $row['fclose_date'] = f_get_disp_date($row['close_date']);
                $row['parent_name'] = f_get_single_value_parm($conn, 
                        "select budget_name from m_budget where budget_id=:bid", 
                        array(':bid'=>$row['parent_id']), "");
                echo $tb->htmlRow($row);
                $sm ++;
            }
        }
	else	{
            $message  = f_get_error_text($conn, "Invalid query: ");
            print "<TR><TD COLSPAN=\"6\">$message</TD></TR>\n";
	}
        print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"Запрос выполнен " . date("d.m.Y H:i:s") . "\">Количество бюджетов</TD><TD COLSPAN=\"4\">$sm</TD></TR>\n";
	echo $tb->htmlClose();
//	print_buttons("onAdd();");
}

?>
        </form>
        </div>
    </body>

</html>
