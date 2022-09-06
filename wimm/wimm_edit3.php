<?php
    include_once ("fun_web.php");
    $self_request = $_SERVER['REQUEST_URI'];
    $acref_request = dirname($self_request) . "/ac_ref.php";
    $uid = page_pre();
    if($uid===FALSE)
        die();
    include_once 'fun_dbms.php';
    $page_title = "Уточнить расходы";
    include_once 'table.php';
    /**
     * @var $conn PDO 
     */
    $values = array();
    $drecs = array();
    $conn = f_get_connection();
    $tr_id = getRequestParam('HIDDEN_ID', FALSE);
    if($tr_id===FALSE)
    {
        $t = getRequestParam("t",FALSE);
        $s = getRequestParam("s",FALSE);
        if($t && $s)
        {
            $values['uid'] = $uid;
            $values['row_bound'] = FALSE;
            $td = value4db(substr($t,0,4) . '-' . substr($t,4,2) . '-' . 
                    substr($t,6,2) . ' ' . substr($t,9,2) . ':' . substr($t,11,2));
            $values['transaction_date'] = $td . ':00';
            $values['transaction_sum'] = $s;
            $sql_pd = "select transaction_id, t_type_name, transaction_name, transaction_sum, "
                . "type_sign, transaction_date, user_name, place_name, place_descr, "
                . "t.currency_id t_cid, #CONCAT#(mcu.currency_name #||# ' (' #||# mcu.currency_abbr #||# ')') as currency_name, "
                . "mcu.currency_sign, mb.currency_id as bc_id, t.place_id, t.budget_id, "
                . "t.user_id, t.t_type_id, mb.budget_name, t.loan_id, ml.loan_name "
                . "from m_transactions t left join m_loans ml on t.loan_id=ml.loan_id, m_transaction_types tt, m_users tu, "
                . "m_places tp, m_currency mcu, m_budget mb "
                . "where t.t_type_id=tt.t_type_id and t.user_id=tu.user_id and "
                . "t.place_id=tp.place_id and t.currency_id=mcu.currency_id and "
                . "t.budget_id=mb.budget_id "
                . "and transaction_sum={$values['transaction_sum']}"
                . "and substr(transaction_date,1,16)='$td'";
            $fsqld = formatSQL($conn, $sql_pd);
            $res = $conn->query($fsqld);
            if($res)	{
                $row = array();
                $count = 0;
                while($row =  $res->fetch(PDO::FETCH_ASSOC))
                {
                    if($count>0)
                    {
                        $drecs[$row['transaction_id']] = $row;
                    }
                    $count ++;
                }
                if($count===1)
                {
                    $tr_id = $row['transaction_id'];
                    $values = $row;
                }
            }
        }
        else
        {
            $url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/index.php";
            header("Location: $url");
            die();
        }
    }
    else
    {
	$sql = "select transaction_id, t_type_name, transaction_name, transaction_sum, "
                . "type_sign, transaction_date, user_name, place_name, place_descr, "
                . "t.currency_id t_cid, #CONCAT#(mcu.currency_name #||# ' (' #||# mcu.currency_abbr #||# ')') as currency_name, "
                . "mcu.currency_sign, mb.currency_id as bc_id, t.place_id, t.budget_id, "
                . "t.user_id, t.t_type_id, mb.budget_name, t.loan_id, ml.loan_name "
                . "from m_transactions t left join m_loans ml on t.loan_id=ml.loan_id, m_transaction_types tt, m_users tu, "
                . "m_places tp, m_currency mcu, m_budget mb "
                . "where t.t_type_id=tt.t_type_id and t.user_id=tu.user_id and "
                . "t.place_id=tp.place_id and t.currency_id=mcu.currency_id and "
                . "t.budget_id=mb.budget_id and t.transaction_id=$tr_id";
        $fsql = formatSQL($conn, $sql);
        $res = $conn->query($fsql);
        if($res)	{
            if(!($values =  $res->fetch(PDO::FETCH_ASSOC)))
            {
                $values = array();
            }
        }
    }
    $values['uid'] = $uid;
    $values['row_bound'] = FALSE;
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
            <script language="JavaScript" type="text/JavaScript" src="js/index_aj.js"></script>
            <script language="JavaScript" type="text/JavaScript" src="js/jqc_autocomplete_ifd.js"></script>
            <script language="JavaScript" type="text/JavaScript" src="js/bootstrap.js"></script>
            <script language="JavaScript" type="text/JavaScript">
                var tmr;
                var to;
                var pAc;
                function focus_fun()
                {
                    console.log("focus_fun() begin");
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
                    console.log("focus_fun() end");
                }
                function onLoad2()
                {
                    to = 500;
                    $("body").keydown(function(e) {
                        onPageKey(e.keyCode);
                    });
                    setHandlers(".dtp");
                    pAc = new AutoCompleteIFD("ac", ".txt");
                }
                function doCancel2()
                {
                    $('#GOOD_MODE').val('refresh');
                }
                function add_click2()
                {
                    $('#dlg_box_cap').text('Добавление записи');
                    $('#DEL_BTN').hide();
                    $('#GOOD_ID').val('');
                    $('.gfield').val('');
                    $('#GOOD_MODE').val('insert_good');
                }
                function submit_form(i_type)
                {
                    var v_form = document.getElementById('expenses');
                    if(i_type=='home')
                    {
                        $('#FRM_MODE').val(i_type);
                        v_form.action = 'index.php';
                        v_form.submit();
                    }
                    else
                    {
                        $('#FRM_MODE').val(i_type);
                        v_form.submit();
                    }
                }
                function del_click2()
                {
                    var s1 = $('#GOOD_ID').val();
                    if(s1!=null && s1.length>0)
                    {
                        $('#GOOD_MODE').val('delete_good');
                        tx_submit('<?php echo dirname($_SERVER['PHP_SELF']);?>/wimm_edit3.php');
                    }
                    else
                    {
                        alert("Запись для удаления не выбрана");
                    }
                }
            </script>
        <?php
            print_body_title($page_title);
        ?>
            <form id="expenses" name="expenses" method="post" accept-charset="utf-8">
            <input id="FRM_MODE" name="FRM_MODE" type="hidden" value="refresh">
            <input name="UID" type="hidden" value="<?php echo getRequestParam("UID", "");?>">
        <?php
        if(count($drecs)>1)
        {
            echo '<div class="form-group">' . PHP_EOL;
            echo '<label for="HIDDEN_ID">Пользователь:</label>';
            echo '<select class="form-control form_field valid sendable" size="1" id="HIDDEN_ID" name="HIDDEN_ID"' .
                    'pattern="^[1-9][0-9]*$">';
            foreach ($drecs as $key => $value) {
                print "<OPTION value=\"$key\">{$value['transaction_name']}|{$value['t_type_name']}|{$value['user_name']}|{$value['place_name']}</OPTION>" . PHP_EOL;
            }
            echo '</div>' . PHP_EOL;
        }
        else
        {
            echo '<input id="HIDDEN_ID" name="HIDDEN_ID" type="hidden" value="<?php echo $tr_id;?>">' . PHP_EOL;
        }
                include_once 'wimm_form.php';
        ?>
            <button type="submit" class="btn btn-default quick_acc" name="btn_refresh" formnovalidate="" title="Обновить страницу">
                <span class="glyphicon glyphicon-refresh"></span> Обновить
            </button>
            <button type="button" class="btn" name="btn_save" onclick="submit_form('save');" title="Сохранить изменения и вернуться к расходам">
                <span class="glyphicon glyphicon-save"></span> Сохранить
            </button>            
            <button type="reset" class="btn quick_acc" title="Отменить изменения на странице">
                <span class="glyphicon glyphicon-erase"></span> Отмена
            </button>
            <button type="button" class="btn quick_acc" onclick="add_click2();" data-toggle="modal" data-target="#dialog_box" title="Добавить товар">
                <span class="glyphicon glyphicon-plus"></span> Добавить
            </button>
            <button type="button" class="btn" name="btn_return" onclick="submit_form('home');"  title="Вернуться к расходам">
                <span class="glyphicon glyphicon-home"></span> Вернуться
            </button>
            <DIV class="ui-widget-content modal fade" id="dialog_box" role="dialog">
                <input id="GOOD_ID" name="GOOD_ID" type="hidden" value="">
                <input id="GOOD_MODE" name="GOOD_MODE" type="hidden" value="">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <DIV class="modal-header" id="dlg_box_cap">Изменение товара</DIV>
                        <DIV class="modal-body" id="dlg_box_text" >
                            <div class="form-group">
                                <label for="g_name">Наименование:</label>
                                <input class="form_field valid sendable gfield" type="hidden" id="g_type" 
                                       bind_row_type="value" bind_row_id="T_TYPE_" value=""
                                       pattern="^[1-9][0-9]*$" focus_on="g_name">
                                <input type="text" class="form-control form_field txt gfield" value=""
                                       autocomplete="off" bound_id="g_type" ac_src="<?php echo get_autocomplete_url();?>" 
                                       ac_params="type=t_goods;filter=" id="g_name" scroll_height="10"
                                       bind_row_type="title" bind_row_id="TNAME_">
                            </div>
                            <div class="form-group">
                                <label for="g_count">Количество:</label>
                                <input class="form-control form_field valid sendable gfield" type="text" id="g_count" 
                                       bind_row_type="value" bind_row_id="T_TYPE_" value=""
                                       pattern="^[1-9][0-9]*$">
                            </div>
                            <div class="form-group">
                                <label for="g_count">Вес:</label>
                                <input class="form-control form_field valid sendable gfield" type="text" id="g_weight" 
                                       bind_row_type="value" bind_row_id="T_TYPE_" value=""
                                       pattern="^[1-9][0-9]*[,\.][0-9]*$">
                            </div>
                            <div class="form-group">
                                <label for="g_count">Стоимость:</label>
                                <input class="form-control form_field valid sendable gfield" type="text" id="g_price" 
                                       bind_row_type="value" bind_row_id="T_TYPE_" value=""
                                       pattern="^[1-9][0-9]*[,\.][0-9]*$">
                            </div>
                        </DIV>
                        <DIV class="modal-footer" id="dlg_box_btns">
                            <button class="btn" id="OK_BTN" type="button"
                                    onclick="if(fancy_form_validate('expenses')) tx_submit('<?php echo dirname($_SERVER['PHP_SELF']);?>/wimm_edit3.php');">
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
        <?php
        $tb = new table();
        $tb->setValue(tbase::$PN_CLASS, "table table-bordered table-responsive table-striped visual2");
        $tb->setIndent(3);
        $tb->addColumn(new tcol("№"), TRUE);
        $tb->addColumn(new tcol("Артикул"), TRUE);
        $tb->addColumn(new tcol("Наименование"), TRUE);
        $tb->addColumn(new tcol("Количество"), TRUE);
        $tb->addColumn(new tcol("Вес"), TRUE);
        $tb->addColumn(new tcol("Цена"), TRUE);
        $tb->body->setValue(tbody::$PN_ROW_CLASS, "table-hover");
        echo $tb->htmlOpen();
        if($tr_id)
        {
            $sql = "select good_id, good_idx, store_barcode, transaction_sum, "
                    . "purchased_count, purchased_weight "
                    . "from m_transaction_goods tg "
                    . "where transaction_id=$tr_id and "
                    . "close_date is null order by good_idx";
            $fsql = formatSQL($conn, $sql);
            echo "<input type='hidden' id='main_sql' value=\"$fsql\">\n";
            $res = $conn->query($fsql);
            if($res)
            {
                while ($row =  $res->fetch(PDO::FETCH_ASSOC)) {
                    echo $tb->htmlRow($row);
                }
            }
            else
            {
                $message  = f_get_error_text($conn, "Invalid query: ");
                print "<TR><TD COLSPAN=\"6\">$message</TD></TR>\n";
            }
        }
	echo $tb->htmlClose();
        ?>
                
            </form>
        </div>
    </body>
</html>
