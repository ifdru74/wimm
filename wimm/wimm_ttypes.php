<?php
    include("fun_web.php");
    //auth_check('UID');
    include_once 'fun_dbms.php';

    $conn = f_get_connection();
    $fm = "refresh";
    if(getRequestParam("btn_refresh",FALSE)===FALSE)
    {
        $fm = getRequestParam("FRM_MODE","refresh");
    }
    $uid = page_pre();
    if($uid===FALSE)    die();
    $p_title = "Редактор того, на что тратятся деньги";
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

            function doEdit(s1)
            {
                if(s1=="insert")	{
                    $("#FRM_MODE").val("insert");
                    $('#dlg_box_cap').text('Добавить');
                    $("#HIDDEN_ID").val("0");
                    $(".form_field").val('');
                    $('#p_s_p').prop('checked', false);
                    $('#p_s_m').prop('checked', false);
                    $('#DEL_BTN').hide();
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
                    $('#FRM_MODE').val('update');
                    $("#dialog_box").modal('show');
                    $('#DEL_BTN').show();
                    table_row_selected("#"+e.currentTarget.id, "#edit_form");
                    $("#HIDDEN_ID").val(e.currentTarget.id);
                    var s = $("#ls_"+e.currentTarget.id).attr('title');
                    console.log("bind item="+"#ls_"+e.currentTarget.id+", title="+s.toString());
                    switch(s.toString())
                    {
                        case "1":
                            $('#p_s_p').prop('checked', true);
                            break;
                        case "-1":
                            $('#p_s_m').prop('checked', true);
                            break;
                        default:
                            $('#p_s_m').prop('checked', false);
                            $('#p_s_p').prop('checked', false);
                    }
                    var tb = $("#ltb_"+e.currentTarget.id).attr('title');
                    console.log("type_bits="+tb);
                    if(tb&2)
                    {
                        $('#credit_pay').prop('checked', true);
                    }
                    else
                    {
                        $('#credit_pay').prop('checked', false);
                    }
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
                    case 'delete':
                        if($( ".row_sel:checked" ).length<1)
                        {
                            alert('Строка для удаления не выбрана');
                            s2 = false;
                        }
                        break;
                    case 'update':
                    case 'insert':
                        if($("#p_name").val().length<1)
                        {
                            alert('Надо заполнить Наименование');
                            $("#p_name").select();
                            s2 = false;
                        }
                        break;
                }
                if(s2)
                    $('#edit_form').submit();
            }
        </script>
        <form id="edit_form" name="edit_box" action="wimm_ttypes.php" method="post" class="main_form">
            <div id="dialog_box" class="ui-widget-content modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header" id="dlg_box_cap">Редакторовать</div>
                        <div class="modal-body" id="dlg_box_text">
                            <div scroll_height="100" for="" selected_ac_item="" class="ac_list" id="ac"></div>
                            <div class="form-group">
                                <label for="p_name">Наименование:</label>
                                <input class="form-control form_field" type="text" name="p_name" id="p_name" 
                                       size="50" value="" bind_row_type="label" bind_row_id="ln_">
                            </div>
                            <div class="form-group">
                                <label for="">Родитель:</label>
                                <input class="form-control form_field txt" type="text" name="parent_text" id="parent_text" 
                                       size="50" value="" bind_row_type="label" bind_row_id="lp_" autocomplete="off"
                                       bound_id="p_descr" ac_src="/wimm2/ac_ref.php" ac_params="type=t_type;except=#HIDDEN_ID;filter=">
                                <input class="form_field" type="hidden" name="p_descr" 
                                       bind_row_type="title" bind_row_id="lp_" 
                                       id="p_descr" value="">
                            </div>
                            <div class="form-group">
                                <div class="panel panel-primary">
                                    <div class="panel-heading">Тип операции</div>
                                    <div class="panel-body">
                                        <input type="radio" name="p_sign" id="p_s_m" value="-1"><label for="p_s_m"><img id="p_s_mi" src="picts/minus.gif">Расходы</label>
                                        <input type="radio" name="p_sign" id="p_s_p" value="1" ><label for="p_s_p"><img id="p_s_pi" src="picts/plus.gif">Доходы</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="panel panel-primary">
                                    <div class="panel-heading">Дополнительно</div>
                                    <div class="panel-body">
                                        <!--input type="checkbox" name="credit_pur" id="credit_pur" value="1"><label for="credit_bue">Покупка в кредит</label -->
                                        <input type="checkbox" name="t_bits[]" id="credit_pay" value="2" ><label for="credit_pay">Погашение кредита</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn" id="OK_BTN" type="button"
                                    onclick="$('#edit_form').submit();">
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
	print_buttons("doEdit('insert');");
?>                
            </DIV>
<?php
        $a_ret = array();
        if(strcmp($fm,"refresh")!=0)
        {
            include_once 'wimm_dml.php';
            $a_ret = ttypes_dml($conn, $fm);
            embed_diag_out($a_ret);
        }
        if(key_exists('dup_id', $a_ret))
        {
            showError("Такой место ({$a_ret['dup_id']}) уже есть! Оно отмечено в таблице.");
        }
	print "<input id=\"FRM_MODE\" name=\"FRM_MODE\" type=\"hidden\" value=\"refresh\">\n";
	print "<input id=\"HIDDEN_ID\" name=\"HIDDEN_ID\" type=\"hidden\" value=\"0\">\n";
//	print_buttons("doEdit('insert');");
	print "<TABLE class=\"table table-bordered table-responsive table-striped visual2\">\n";
	print "<thead><TR>\n";
	print "<TH WIDTH=\"45%\">Наименование</TH>\n";
	print "<TH WIDTH=\"15%\">Родитель</TH>\n";
	print "<TH WIDTH=\"5%\">Знак</TH>\n";
	print "<TH WIDTH=\"15%\">Дата создания</TH>\n";
	print "<TH  WIDTH=\"20%\">Кто автор</TH>\n";
	print "</TR></thead><tbody>\n";
	$sql = "SELECT t_type_id, t_type_name, parent_type_id, type_sign, mtt.open_date, is_repeat, period, user_name, type_bits "
                . "FROM m_transaction_types mtt, m_users mu "
                . "where mtt.user_id=mu.user_id and mtt.close_date is NULL";
	$res = $conn->query($sql);
	$sm = 0;
	$sd = 0;
        $parent_id=false;
        $parent_name='';
	if($res)	{
            //print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
            $plus_pict = "picts/plus.gif";
            $minus_pict = "picts/minus.gif";
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                print "<TR class=\"table-hover\">\n";
                $s = $row['t_type_id'];
                $sn = $row['t_type_name'];
                $sd = $row['parent_type_id'];
                print "<TD><input name=\"rowid\" ID=\"$s\" type=\"radio\" value=\"$s\" class=\"row_sel\"";
                if(key_exists('dup_id', $a_ret) && strcmp($row['t_type_id'], $a_ret['dup_id'])==0)
                {
                    echo ' checked';
                }
                $s = $row['t_type_name'];
                print "><label class='td' for='{$row['t_type_id']}' id=ln_{$row['t_type_id']}>$s</label></TD>\n";
                $s = $row['parent_type_id'];
                $p = '';
                if($s==$parent_id)
                {
                    $p = $parent_name;
                }
                else
                {
                    $p = f_get_single_value($conn, "select t_type_name from m_transaction_types where t_type_id=$s", "");
                    if(strlen($p)>0)
                    {
                        $parent_id = $s;
                        $parent_name = $p;
                    }
                }
                print "<TD><label class='td' for='{$row['t_type_id']}' id=lp_{$row['t_type_id']} title='$s'>$p</label></TD>\n";
                $s = $row['type_sign'];
                print "<TD id=\"ls_{$row['t_type_id']}\" TITLE=\"$s\"><CENTER>";
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
                print "<TD><label class='td' for='{$row['t_type_id']}'>$s</label></TD>\n";
                $s = $row['user_name'];
                $tb = $row['type_bits'];
                print "<TD><label class='td' id='ltb_{$row['t_type_id']}' for='{$row['t_type_id']}' title='$tb'>$s</label></TD>\n";
                print "</TR>\n";
            }
	}
	else	{
            $message  = f_get_error_text($conn, "Invalid query: ");
            print "<TR><TD COLSPAN=\"6\">$message</TD></TR>\n";
	}
	print "</tbody></TABLE>\n";
?>
        </form>
    </div>
</body>

</html>