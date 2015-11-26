<?php
    include_once ("fun_web.php");
    $uid = page_pre();
    if($uid===FALSE)
        die();
    include_once 'fun_dbms.php';
    $inc = get_include_path();
    set_include_path($inc . ";trunk\\wimm\\cls\\table");
    include_once 'table.php';
    $p_title = "Валюты, в которых тратятся деньги";
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
                $(".row_sel").click(function(e)
                {
                    $('#dlg_box_cap').text('Редактировать');
                    $("#dialog_box").modal('show');
                    $('#DEL_BTN').show();
                    $('#FRM_MODE').val('update');
                    table_row_selected("#"+e.currentTarget.id, "#edit_form");
                    $("#HIDDEN_ID").val(e.currentTarget.id);
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
                document.getElementById('curr_name').focus();
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
        <form id="edit_form" name="currencies" method="post">
            <div id="dialog_box" class="ui-widget-content modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header" id="dlg_box_cap">Редактирование</div>
                        <div class="modal-body" id="dlg_box_text">
                            <div class="form-group">
                                <label for="curr_name">Наименование:</label>
                                <input type="text" class="form-control form_field" name="curr_name" id="curr_name" 
                                       bind_row_type="label" bind_row_id="CNAME_" value="">
                            </div>
                            <div class="form-group" style="height: 40px;display: block">
                                <label for="curr_abbr">Сокращённое наименование:</label>
                                <input type="text" class="form-control form_field" name="curr_abbr" id="curr_abbr" 
                                       bind_row_type="title" bind_row_id="CNAME_" value="">
                            </div>
                            <div class="form-group">
                                <label for="curr__sign">Знак валюты:</label>
                                <input type="text" class="form-control form_field" name="curr_sign" 
                                       bind_row_type="label" bind_row_id="CSIGN_"  id="curr_sign" value="">
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
            
        <?php
        $conn = f_get_connection();
        if($conn)	{
            $fm = "refresh";
            if(getRequestParam("btn_refresh",FALSE)===FALSE)
            {
                $fm = getRequestParam("FRM_MODE","refresh");
            }
            $sql = "";
            switch ($fm)
            {
                case "insert":
                    $sql = "insert into m_currency(currency_name, currency_abbr, currency_sign, open_date, user_id) values(";
                    $sql .= ("'" . value4db(getRequestParam("curr_name","Рубль?")) . "', ");
                    $sql .= ("'" . value4db(getRequestParam("curr_abbr","?")) . "', ");
                    $sql .= ("'" . value4db(getRequestParam("curr_sign","$")) . "', ");
                    $sql .= "#NOW#, $uid)";
                    break;
                case "update":
                    $sql = "UPDATE m_currency SET ";
                    $s = value4db(getRequestParam("curr_name","Рубль?"));
                    $sql .= "currency_name='$s', ";
                    $s = value4db(getRequestParam("curr_abbr","?"));
                    $sql .= "currency_abbr='$s', ";
                    $s = value4db(getRequestParam("curr_sign","$"));
                    $sql .= "currency_sign='$s' ";
                    $sql .= "where currency_id=";
                    $s = value4db(getRequestParam("HIDDEN_ID",0));
                    $sql .= $s;
                    break;
                case "delete":
                    $s = value4db(getRequestParam("HIDDEN_ID",0));
                    $sql = "update m_currency set close_date=#NOW# where currency_id=$s";
                    break;
            }
            $hfmt = "<input id=\"%s\" name=\"%s\" type=\"hidden\" value=\"%s\">" . PHP_EOL;
            $sqlf = formatSQL($conn, $sql);
            printf($hfmt, "SQL", "SQL", $sqlf);
            if(strlen($sql)>0)	{
                $conn->query($sqlf);
                //$conn->commit();
            }
            printf($hfmt, "FRM_MODE", "FRM_MODE", "refresh");
            printf($hfmt, "HIDDEN_ID", "HIDDEN_ID", "0");
            print_buttons("onAdd();");
            $tb = new table();
            $tb->setValue(tbase::$PN_CLASS, "table table-bordered table-responsive table-striped visual2");
            $tb->setIndent(3);
            $tb->addColumn(new tcol("Наименование"), TRUE);
            $tb->addColumn(new tcol("Знак валюты"), TRUE);
            $tb->addColumn(new tcol("Дата создания"), TRUE);
            $tb->addColumn(new tcol("Дата закрытия"), TRUE);
            $tb->addColumn(new tcol("Кто автор"), TRUE);
            $tb->body->setValue(tbody::$PN_ROW_CLASS, "table-hover");
            $fmt_str = "<input name='ROW_ID' ID='=currency_id' type='radio' value='=currency_id' class='row_sel'>" .
                    "<LABEL class='td' TITLE='=currency_abbr' id='CNAME_=currency_id' FOR='=currency_id'>=currency_name</LABEL>";
            $tb->addColumn(new tcol($fmt_str), FALSE);
            $tb->addColumn(new tcol("<LABEL class='td' id=\"CSIGN_=currency_id\" FOR=\"=currency_id\">=currency_sign</LABEL>"), FALSE);
            $tb->addColumn(new tcol("<LABEL class='td' TITLE=\"=open_date\" id=\"ODATE_=currency_id\" FOR=\"=currency_id\">=fopen_date</LABEL>"), FALSE);
            $tb->addColumn(new tcol("<LABEL class='td' TITLE=\"=close_date\" id=\"CDATE_=currency_id\" FOR=\"=currency_id\">=fclose_date</LABEL>"), FALSE);
            $tb->addColumn(new tcol("<LABEL class='td' TITLE=\"=user_id\" id=\"USER_=currency_id\" FOR=\"=currency_id\">=user_name</LABEL>"), FALSE);

            $sql = "select currency_id, currency_name, currency_abbr, currency_sign, tp.open_date, tp.close_date, user_name, tp.user_id from m_currency tp, m_users tu where tp.user_id=tu.user_id and tp.close_date is null order by currency_name";
            $res = $conn->query($sql);
            $sm = 0;
            $sd = 0;
            $c_class = "dark";
            echo $tb->htmlOpen();
            if($res)	{
                while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                    $row['fopen_date'] = f_get_disp_date($row['open_date']);
                    $row['fclose_date'] = f_get_disp_date($row['close_date']);
                    echo $tb->htmlRow($row);
                    $sm ++;
                }
            }
            else	{
                $message  = f_get_error_text($conn, "Invalid query: ");
                print "<TR><TD COLSPAN=\"6\">$message</TD></TR>\n";
            }
            print "<TR class=\"white_bold\"><TD COLSPAN=\"3\" TITLE=\"Запрос выполнен " . date("d.m.Y H:i:s") . "\">Количество валют</TD><TD COLSPAN=\"4\">$sm</TD></TR>\n";
            echo $tb->htmlClose();
            print_buttons("onAdd();");
        }
        // put your code here
        ?>
        </form>
    </body>
</html>
