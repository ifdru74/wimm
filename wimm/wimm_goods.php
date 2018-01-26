<?php
    include_once ("fun_web.php");
    $self_request = $_SERVER['REQUEST_URI'];
    $acref_request = dirname($self_request) . "/ac_ref.php";
    $uid = page_pre();
    if($uid===FALSE)
        die();
    include_once 'fun_dbms.php';
    /**
     * @var $conn PDO 
     */
    $conn = f_get_connection();
    $fm = "refresh";
    if(getRequestParam("btn_refresh",FALSE)===FALSE)
    {
        $fm = getRequestParam("FRM_MODE","refresh");
    }
    $sql_dml = "";
    $good_id = 0;
    $a_ret = array();
    include_once 'wimm_dml.php';
    if(strcmp($fm,"refresh")!=0)
    {
        $a_ret = goods_dml($conn, $fm);
    }
    switch($fm)
    {
        case 'update':
        case 'delete':
            die(json_encode($a_ret));
    }
    $page_title = "Справочник товаров";
    include_once 'table.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="STYLESHEET" href="css/wimm.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/bootstrap.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/jquery_autocomplete_ifd.css" type="text/css"/>
        <link rel="SHORTCUT ICON" href="picts/favicon.ico">
        <meta charset="UTF-8">
        <title><?php echo $page_title;?></title>
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
            <script language="JavaScript" type="text/JavaScript" src="js/jqc_autocomplete_ifd.js"></script>
            <script language="JavaScript" type="text/JavaScript" src="js/bootstrap.js"></script>
            <script language="JavaScript" type="text/JavaScript">
                var pAc;
                /**
                 * 
                 * @param {object} s_msg
                 * @returns {undefined}
                 */
                function console_debug_log(s_msg)
                {
                    if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1 ||
                            navigator.userAgent.toLowerCase().indexOf('chrome') > -1)   {
                        console.log(s_msg);
                    }
                }
                function onLoad2()
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
                    pAc = new AutoCompleteIFD("ac", ".txt");
                    pAc.call_change = true;
                    $(".row_sel").click(function(e)
                    {
                        $('.dlg_box').show();
                        $('#dlg_box_cap').text('Изменение записи');
                        table_row_selected("#"+e.currentTarget.id, "#goods");
                        $('#HIDDEN_ID').val(e.currentTarget.id);
                        $('#FRM_MODE').val('update');
                        $("#dialog_box").modal('show');
                    });                
                }
                function add_click2()
                {
                    $('#dlg_box_cap').text('Добавление записи');
                    $('#DEL_BTN').hide();
                    $('#HIDDEN_ID').val('');
                    $('.form_field').val('');
                    $('#FRM_MODE').val('insert');
                    document.getElementById('g_user').focus();
                }
                function doCancel2()
                {
                    $('#FRM_MODE').val('refresh');
                }
                function del_click2()
                {
                    var s1 = $('#HIDDEN_ID').val();
                    if(s1!=null && s1.length>0)
                    {
                        $('#FRM_MODE').val('delete');
                        tx_submit('<?php echo $self_request;?>');
                    }
                    else
                    {
                        alert("Запись для удаления не выбрана");
                    }
                }
                function onTxComplete(jqXHR, textStatus )
                {
                    if(textStatus.indexOf("success")==0)    {
                        var row_id = $("#HIDDEN_ID").val();
                        if(row_id!==null&&row_id!==undefined&&row_id.length>0)  {
                            var i, v;
                            var s1 = '{"id":"1","err":"error"}';
                            console_debug_log('response=' + jqXHR.responseText);
                            if(jqXHR.responseText!==null && jqXHR.responseText!==undefined && 
                                    jqXHR.responseText.length>0)  {
                                i = JSON.parse(jqXHR.responseText);
                                console_debug_log("id="+i.id);
                                console_debug_log("error="+i.err);
                                v = $("#FRM_MODE").val();
                                console_debug_log("mode="+v);
                                if(v.indexOf("delete")==0)
                                {
                                    $("#"+row_id).parent().parent().remove();
                                }
                                else {
                                    var form_fields = $('#goods').find(".form_field");
                                    for(i=0; i<form_fields.length; i++)
                                    {
                                        var bt = form_fields[i].getAttribute("bind_row_type");
                                        var bi = "#" + form_fields[i].getAttribute("bind_row_id") + row_id;
                                        var v = form_fields[i].value;
                                        if(v.substr(0,4)=='aci_')
                                        {
                                            v = v.substr(4);
                                        }
                                        if(form_fields[i].id.indexOf('t_date')==0)
                                        {
                                            $(bi).attr('title', v);
                                            $(bi).text(format_date(v));
                                        }
                                        else
                                        {
                                            switch(bt)
                                            {
                                            case 'value':
                                                $(bi).val(v);
                                                break;
                                            case 'label':
                                                $(bi).text(v);
                                                break;
                                            case 'title':
                                                $(bi).attr('title', v);
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            else
                                alert("Пустой ответ!");
                        }
                        else
                        {
                            console_debug_log("new row.");
                            console_debug_log('response=' + jqXHR.responseText);
                            v = $("#FRM_MODE").val();
                            console_debug_log("mode="+v);
                            if(v.indexOf("insert")==0)
                            {
                                if(jqXHR.responseText!==null && jqXHR.responseText!==undefined && 
                                        jqXHR.responseText.length>0)  {
                                    i = JSON.parse(jqXHR.responseText);
                                    console_debug_log("id="+i.id);
                                    console_debug_log("error="+i.err);
                                }                
                            }
                        }
                    }
                    else {
                        console_debug_log(textStatus);
                    }

                    jqxr = null;
                    //$("#dialog_box").hide();
                    $("#dialog_box").modal('hide');
                }
                function tx_submit(submitURL)
                {
                    var i;
                    var f,v;
                    var inv_idx = -1;
                    var msg = "";
                    var currentdate = new Date(); 
                    v = $("#FRM_MODE").val();
                    if(v=='insert')
                    {
                        $('#goods').submit();
                        return;
                    }
                    var reqStr = "time=" + Date.now() + "&FRM_MODE=" + v + 
                            "&HIDDEN_ID=" + $("#HIDDEN_ID").val();
                    var fields = $('#goods').find('.sendable');
                    if(fields!=null && fields!=undefined)
                    {
                        for(i=0; i<fields.length; i++)
                        {
                            try {
                                f = fields[i].getAttribute("id");;
                                v = fields[i].value;
                                if(v.substr(0,4)=='aci_')
                                {
                                    v = v.substr(4);
                                }
                                reqStr += ("&" + f.toString() + "=" + encodeURIComponent(v));
                            } catch (e) {
                                inv_idx = i;
                                msg = e.toString();
                            }
                        }
                    }
                    console_debug_log("request:"+reqStr);
                    if(inv_idx>=0&&inv_idx<a_fields.length)  {
                        if(msg.length>0)
                            alert(msg);
                        console_debug_log("error at:"+inv_idx.toString());
                        return;
                    }
                    console_debug_log("query!");
                    jqxr = $.ajax({
                        type: "POST",
                        url: submitURL,
                        data: reqStr,
                        complete: onTxComplete
                    });
                    console_debug_log("end query!");
                }
            </script>
            <!-- <?php echo formatSQL($conn, $sql_dml); ?>-->
        <?php
        // put your code here
            print_body_title($page_title);
        ?>
            <form id="goods" name="goods" method="post" accept-charset="utf-8">
                <input id="FRM_MODE" name="FRM_MODE" type="hidden" value="refresh">
                <input id="HIDDEN_ID" name="HIDDEN_ID" type="hidden" value="0">
                <DIV class="ui-widget-content modal fade" id="dialog_box" role="dialog">
                    <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <DIV class="modal-header" id="dlg_box_cap">Изменение записи</DIV>
                            <DIV class="modal-body" id="dlg_box_text" >
                                <div scroll_height="100" for="" selected_ac_item="" class="ac_list" id="ac"></div>
                                <div class="form-group">
                                    <label for="t_user">Пользователь:</label>
                                    <select class="form-control form_field valid sendable" size="1" id="g_user" name="g_user"
                                            bind_row_type="title" bind_row_id="G_NAME_" pattern="^[1-9][0-9]*$">
<?php
	$sql = "select user_id, user_name from m_users where close_date is null";
	f_set_sel_options2($conn, formatSQL($conn, $sql), $uid, $uid, 2);
?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="t_name">Наименование:</label>
                                    <input class="form-control form_field valid sendable" name="g_name" id="g_name" 
                                           type="text" bind_row_type="label" bind_row_id="G_NAME_" 
                                           pattern="^(?!\s*$).+" value="">
                                </div>
                                <div class="form-group">
                                    <label for="t_name">Штрихкод:</label>
                                    <input class="form-control form_field valid sendable" name="g_barcode" id="g_barcode" 
                                           type="text" bind_row_type="label" bind_row_id="G_CODE_" pattern="(^$|\b[1-6][0-9]{8})" value="">
                                </div>
                                <div class="form-group">
                                    <label for="t_type">Тип:</label>
                                    <input class="form_field valid sendable" type="hidden" name="g_type" id="g_type" 
                                           bind_row_type="title" bind_row_id="G_TYPE_" value=""
                                           pattern="^[1-9][0-9]*$" focus_on="g_type_name">
                                    <input type="text" name="g_type_name" class="form-control form_field txt" value=""
                                           autocomplete="off" bound_id="g_type" ac_src="<?php echo get_autocomplete_url();?>" 
                                           ac_params="type=t_type;filter=" id="g_type_name" scroll_height="10"
                                           bind_row_type="label" bind_row_id="G_TYPE_">
                                </div>
                                <div class="form-group">
                                    <label for="t_name">Товаров в упаковке (штук):</label>
                                    <input class="form-control form_field valid sendable" name="g_count" id="g_count" 
                                           type="text" bind_row_type="label" bind_row_id="G_COUNT_" 
                                           pattern="^[1-9][0-9]*$" value="">
                                </div>
                                <div class="form-group">
                                    <label for="t_name">Вес упаковки (килограмм):</label>
                                    <input class="form-control form_field valid sendable" name="g_weight" id="g_weight" 
                                           type="text" bind_row_type="label" bind_row_id="G_WEIGHT_" 
                                           pattern="^[-+]?[0-9]*\.?[0-9]+$" value="">
                                </div>
                            </DIV>
                            <DIV class="modal-footer" id="dlg_box_btns">
                                <button class="btn" id="OK_BTN" type="button"
                                        onclick="if(fancy_form_validate('goods')) tx_submit('<?php echo $self_request;?>');">
                                    <span class="glyphicon glyphicon-save"></span> Сохранить
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
            <DIV id="buttonz">
<?php
	print_buttons("add_click2();");
?>                
            </DIV>
<?php
        embed_diag_out($a_ret);
        if(key_exists('dup_id', $a_ret))
        {
            showError("Такой товар ({$a_ret['dup_id']}) уже есть! Он отмечен в таблице.");
        }
        
//        print_buttons("add_click2();");
        $tb = new table();
        $tb->setValue(tbase::$PN_CLASS, "table table-bordered table-responsive table-striped visual2");
        $tb->setIndent(3);
        $tc = new tcol("Наименование");
        $tb->addColumn($tc, TRUE);
        $tc = new tcol("Штрихкод");
        $tb->addColumn($tc, TRUE);
        $tc = new tcol("Тип");
        $tb->addColumn($tc, TRUE);
        $tc = new tcol("Кол-во в упаковке");
        $tb->addColumn($tc, TRUE);
        $tc = new tcol("Вес упаковки");
        $tb->addColumn($tc, TRUE);
        $tb->body->setValue(tbody::$PN_ROW_CLASS, "table-hover");
        $fmt_str = "<input class='row_sel' name=\"ROW_ID\" ID=\"=good_id\" type=\"radio\" value=\"=good_id\" =checked>" .
                "<label class='td' id=\"G_NAME_=good_id\" FOR=\"=good_id\" title=\"=user_id\">=good_name</label>";
        $tb->addColumn(new tcol($fmt_str), FALSE);
        $tb->addColumn(new tcol('<label class="td" style="font-family: monospace;" id="G_CODE_=good_id" for="=good_id">=good_barcode</span>'), FALSE);
        $tb->addColumn(new tcol('<label class="td" title="=good_type_id" id="G_TYPE_=good_id" for="=good_id">=t_type_name</span>'), FALSE);
        $tb->addColumn(new tcol('<label class="td" id="G_COUNT_=good_id" for="=good_id">=item_count</span>'), FALSE);
        $tb->addColumn(new tcol('<label class="td" id="G_WEIGHT_=good_id" for="=good_id">=net_weight</span>'), FALSE);
        echo $tb->htmlOpen();
        $sql = "select good_id, good_barcode, good_name, item_count, net_weight, "
                . "g.user_id, good_type_id, t_type_name from m_goods g, m_transaction_types t "
                . "where g.good_type_id=t.t_type_id and g.close_date is NULL order by good_name";
        $fsql = formatSQL($conn, $sql);
        echo "<input type='hidden' id='main_sql' value=\"$fsql\">\n";
        $res = $conn->query($fsql);
        if($res)
        {
            while ($row =  $res->fetch(PDO::FETCH_ASSOC)) {
                if(key_exists('dup_id', $a_ret))
                {
                    if(strcmp($row['good_id'],$a_ret['dup_id'])==0 )
                    {
                        $row['checked']='checked';
                    }
                }
                echo $tb->htmlRow($row);
            }
        }
	else
        {
            $message  = f_get_error_text($conn, "Invalid query: ");
            print "<TR><TD COLSPAN=\"5\">$message</TD></TR>\n";
	}
	echo $tb->htmlClose();
//	print_buttons("add_click2();");
?>
            </form>
        </div>
    </body>
</html>
