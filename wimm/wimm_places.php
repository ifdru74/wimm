<?php
    include_once ("fun_web.php");
    include_once 'wimm_config.php';
    $uid = page_pre();
    if($uid===FALSE)    {
        die();
    }
    include_once 'fun_dbms.php';
    include_once 'table.php';
    $p_title = "Редактор мест, где тратятся деньги";
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
        <script language="JavaScript" type="text/JavaScript" src="js/index_aj.js"></script>
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
                $('#dialog_box').draggable();
                $(".row_sel").click(function(e)
                {
                    console.log("row_sel.click");
                    $('#DEL_BTN').show();
                    $('#FRM_MODE').val('update');
                    table_row_selected("#"+e.currentTarget.id, "#edit_form");
                    $("#HIDDEN_ID").val(e.currentTarget.id);
                    $('#dlg_box_cap').text('Изменить');
                    //$('.dlg_box').show();
                    //document.getElementById('p_name').focus();
                    $("#dialog_box").modal('show');
                    console.log("row_sel.click");
                });
            }
            function send_submit(frm_mode)
            {
                console.log("send_submit");
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
                        if($("#p_name").val().length<1)
                        {
                            alert('Надо заполнить Наименование');
                            $("#p_name").select();
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
                //$('.dlg_box').show();
                document.getElementById('p_name').focus();
                //$("#dialog_box").modal('show');
                console.log("onAdd");
            }

        </script>
        <div class="container">
<?php
print_body_title($p_title);
$conn = f_get_connection();
if($conn)	{
?>
        <form id='edit_form' name="places" action="wimm_places.php" method="post">
            <div id="dialog_box" class="ui-widget-content modal fade" role="dialog">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header" id="dlg_box_cap">Изменить</div>
                        <div class="modal-body" id="dlg_box_text">
                            <div class="form-group">
                                <label for="p_name">Наименование:</label>
                                <input type="text" id="p_name" name="p_name" value=""
                                       class="form-control form_field" bind_row_type="label" bind_row_id="PNAME_">
                            </div>
                            <div class="form-group">
                                <label for="p_descr">Описание:</label>
                                <input type="text" id="p_descr" name="p_descr" value=""
                                       class="form-control form_field" bind_row_type="title" bind_row_id="PNAME_">
                            </div>
                            <div class="form-group">
                                <label for="p_inn">ИНН:</label>
                                <input type="number" id="p_inn" name="p_inn" value=""
                                       class="form-control form_field" 
									   bind_row_type="label" bind_row_id="INN_"
									   pattern="^\d{12}$">
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
                            <button class="btn" type="button" data-dismiss="modal">
                                <span class="glyphicon glyphicon-erase"></span> Отмена
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="FRM_MODE" id='FRM_MODE' value="refresh">
            <input type="hidden" name="HIDDEN_ID" id='HIDDEN_ID' value="0">
            <input type="hidden" name="UID" id='UID' value="<?php echo $uid; ?>">
            <DIV id="buttonz">
<?php
	print_buttons("onAdd();");
?>                
            </DIV>
<?php
	$fm = "refresh";
        if(getRequestParam("btn_refresh",FALSE)===FALSE)
        {
            $fm = getRequestParam("FRM_MODE","refresh");
        }
        if(strcmp($fm,'refresh')!=0)
        {
            include_once 'wimm_dml.php';
            $a_dml = places_dml($conn, $fm);
            embed_diag_out($a_dml);
        }
        else {
            $a_dml = array();
        }
        $tb = new table();
        $tb->setValue(tbase::$PN_CLASS, "table table-bordered table-responsive table-striped visual2");
        $tb->setIndent(3);
        $tb->addColumn(new tcol("Наименование"), TRUE);
        $tb->addColumn(new tcol("ИНН"), TRUE);
        $tb->addColumn(new tcol("Дата создания"), TRUE);
        $tb->addColumn(new tcol("Дата закрытия"), TRUE);
        $tb->addColumn(new tcol("Кто автор"), TRUE);
        $tb->body->setValue(tbody::$PN_ROW_CLASS, "table-hover");
        // column 1
        $fmt_str = "<input name='ROW_ID' ID='=place_id' type='radio' value='=place_id' class='row_sel' =checked>" .
                "<label class='td' TITLE='=place_descr' id='PNAME_=place_id' FOR='=place_id'>=place_name</label>";
        $tb->addColumn(new tcol($fmt_str), FALSE);
        $tb->addColumn(new tcol("<label class='td' id=\"INN_=place_id\" FOR=\"=place_id\">=inn</label>"), FALSE);
        $tb->addColumn(new tcol("<label class='td' id=\"ODATE_=place_id\" FOR=\"=place_id\" title=\"open_date\">=fopen_date</label>"), FALSE);
        $tb->addColumn(new tcol("<label class='td' id=\"CDATE_=place_id\" FOR=\"=place_id\" title=\"close_date\">=fclose_date</label>"), FALSE);
        $tb->addColumn(new tcol("<label class='td' id=\"USR_=place_id\" FOR=\"=place_id\" title=\"user_id\">=user_name</label>"), FALSE);
	$sql = "select place_id, place_name, tp.open_date, tp.close_date, "
                . "place_descr, tp.user_id, user_name, inn "
                . "from m_places tp "
                . "join m_users tu on tp.user_id=tu.user_id "
                . "where tp.close_date is null order by place_name";
        include_once 'QueryRunner.php';
        $query = new QueryRunner($conn, $sql, FALSE);
	$sm = 0;
	$sd = 0;
	echo $tb->htmlOpen();
	if($query->isGood())    {
            $query->execute();
            while ($row = $query->fetch())  {
                if(key_exists('dup_id', $a_dml))    {
                    if(strcmp($row['place_id'],$a_dml['dup_id'])==0 )   {
                        $row['checked']='checked';
                    }
                }
                $row['fopen_date'] = f_get_disp_date($row['open_date']);
                $row['fclose_date'] = f_get_disp_date($row['close_date']);
                echo $tb->htmlRow($row);
                $sm ++;
            }
        }   else    {
            $message  = f_get_error_text($conn, "Invalid query: ");
            echo $tb->htmlError($message);
	}
        print "<TR class=\"white_bold\"><TD COLSPAN=\"2\" TITLE=\"Запрос выполнен " . 
                date("d.m.Y H:i:s") . 
                "\">Количество мест</TD><TD COLSPAN=\"3\">$sm</TD></TR>\n";
        if(key_exists('retcode', $a_dml) && $a_dml['retcode']<0)
        {
            foreach ($a_dml as $kdml => $vdml) {
                switch($kdml)
                {
                    case 'retcode':
                    case 'error':
                    case 'dbg_out':
                        echo $tb->htmlError("<div>$vdml</div>" . PHP_EOL);
                        break;
                }
            }
        }
	echo $tb->htmlClose();
}

?>
            </div>
        </form>
    </body>
</html>
