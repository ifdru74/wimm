<?php
    include_once ("fun_web.php");
    include_once 'fun_dbms.php';
    include_once 'wimm_dml.php';
    $uid = page_pre();
    if($uid===FALSE)
        die();
    $values = array();
    $values['uid'] = $uid;
    $values['row_bound'] = TRUE;
    $fm = "refresh";
    if(getRequestParam("btn_refresh",FALSE)===FALSE)
    {
        $fm = getRequestParam("FRM_MODE","refresh");
    }
    $rec_id = value4db(getRequestParam("REC_ID",FALSE));
    if(!$rec_id)
    {
        $rec_id = value4db(getRequestParam("HIDDEN_ID",false));
    }
    /**
     * @var $conn PDO 
     */
    $conn = f_get_connection();
    if((strcmp($fm,'delete')==0 || strcmp($fm,'update')==0) && $rec_id)
    {
        $a_ret = transaction_dml($conn, $fm);
        echo json_encode($a_ret);
        flush();
        die();
    }
?>
<!DOCTYPE html>
<html lang="ru-RU">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="STYLESHEET" href="css/wimm.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/bootstrap.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/jquery_autocomplete_ifd.css" type="text/css"/>
        <link rel="SHORTCUT ICON" href="picts/favicon.ico">
        <title>Изменить запись</title>
    </head>
    <body onload="onLoad2();">
        <h2>Изменение записи</h2>
        <form id="expenses" name="expenses" method="post" accept-charset="utf-8" action="index.php">
        <div class="container">
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
            <DIV id="dialog_box">
<?php    
    $row = array('transaction_id'=>false, 't_type_name'=>'', 
        'transaction_name'=>'','transaction_sum'=>0, 'transaction_date'=>'',
        'user_name'=>'', 'place_name'=>'', 't_cid'=>'', 'currency_name'=>'',
        'place_id'=>'', 'budget_id'=>'', 'budget_name'=>'', 'loan_id'=>'',
        'loan_name'=>''
        );
    if($conn)	{
        $a_dml = transaction_dml($conn, $fm);
        if($rec_id)
        {
            $sql = "select transaction_id, t_type_name, transaction_name, "
                    . "transaction_sum, DATE(transaction_date) as T_DATE, "
                    . "TIME(transaction_date) as T_TIME, user_name, "
                    . "place_name, t.currency_id t_cid, "
                    . "#CONCAT#(mcu.currency_name #||# ' (' #||# "
                    . "mcu.currency_abbr #||# ')') as currency_name, "
                    . "t.place_id, t.budget_id, t.user_id, t.t_type_id, "
                    . "mb.budget_name, t.loan_id, ml.loan_name "
                    . "from m_transactions t "
                    . "left join m_loans ml on t.loan_id=ml.loan_id, "
                    . "m_transaction_types tt, m_users tu, "
                    . "m_places tp, m_currency mcu, m_budget mb "
                    . "where t.t_type_id=tt.t_type_id and t.user_id=tu.user_id and "
                    . "t.place_id=tp.place_id and t.currency_id=mcu.currency_id and "
                    . "t.budget_id=mb.budget_id and transaction_id=$rec_id";
            $fsql = formatSQL($conn, $sql);
            $res = $conn->query($fsql);
            if($res)	{
                if ($row =  $res->fetch(PDO::FETCH_ASSOC)) {
                    echo '<!-- good resut for record id "' . $rec_id . '"-->' . PHP_EOL;
                }
            }
            else
            {
                echo '<!-- invalid resut for record id "' . $rec_id . '"-->' . PHP_EOL;
            }
        }
        else
        {
            echo '<!-- invalid record id "' . $rec_id . '"-->' . PHP_EOL;
        }
    }
    else
    {
        echo '<!-- not connected-->' . PHP_EOL;
    }
?>
                    <input id="FRM_MODE" name="FRM_MODE" type="hidden" value="refresh">
                    <input id="HIDDEN_ID" name="HIDDEN_ID" type="hidden" value="0">
                    <input name="UID" type="hidden" value="<?php echo $uid;?>">
                    <div scroll_height="100" for="" selected_ac_item="" 
                         class="ac_list" id="ac"></div>
                    <div class="form-group">
                        <label for="t_user">Пользователь:</label>
                        <select class="form-control form_field valid sendable" 
                                size="1" id="t_user" name="t_user">
<?php
	$sql = "select user_id, user_name from m_users where close_date is null";
	f_set_sel_options2($conn, formatSQL($conn, $sql), $values['uid'], $values['uid'], 2);
?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="t_budget_txt">Бюджет:</label>
                        <input type="hidden" name="t_budget" id="t_budget" class="form_field valid sendable" 
                               value="<?php echo $row['budget_id'];?>"
                               pattern="^[1-9][0-9]*$" focus_on="t_budget_txt" onchange="budget_update();">
                        <input type="text" class="form-control form_field txt" 
                               value="<?php echo $row['budget_name'];?>"
                               autocomplete="off" bound_id="t_budget" ac_src="<?php echo get_autocomplete_url();?>" 
                               ac_params="type=t_budget;filter=" id="t_budget_txt"
                               bind_row_type="title" bind_row_id="T_BUDG_">
                    </div>
                    <div class="form-group">
                        <label for="t_name">Наименование:</label>
                        <input class="form-control form_field valid sendable" 
                               name="t_name" id="t_name" type="text" pattern="^(?!\s*$).+" 
                               value="<?php echo $row['transaction_name'];?>">
                    </div>
                    <div class="form-group">
                        <label for="t_type_txt">Тип:</label>
                        <input class="form_field valid sendable" type="hidden" 
                               name="t_type" id="t_type" 
                               value="<?php echo $row['t_type_id'];?>"
                               pattern="^[1-9][0-9]*$" focus_on="t_type_txt">
                        <input type="text" name="t_type_name" class="form-control form_field txt" 
                               value="<?php echo $row['t_type_name'];?>"
                               autocomplete="off" bound_id="t_type" 
                               ac_src="<?php echo get_autocomplete_url();?>" 
                               ac_params="type=t_type;filter=" id="t_type_txt" 
                               scroll_height="10">
                    </div>
                    <div class="form-group">
                        <label for="t_curr_txt">Валюта:</label>
                        <input type="hidden" name="t_curr" id="t_curr" 
                               class="form_field valid sendable" 
                               value="<?php echo $row['t_cid'];?>"
                               pattern="^[1-9][0-9]*$" focus_on="t_curr_txt">
                        <input type="text" class="form-control form_field txt" 
                               value="<?php echo $row['currency_name'];?>"
                               autocomplete="off" bound_id="t_curr" 
                               ac_src="<?php echo get_autocomplete_url();?>" 
                               ac_params="type=t_curr;filter=" id="t_curr_txt">
                    </div>
                    <div class="form-group">
                        <label for="t_sum">Сумма:</label>
                        <input class="form_field valid sendable" id="t_sum" 
                               name="t_sum" type="number" 
                               value="<?php echo substr($row['transaction_sum'], 0, strlen($row['transaction_sum'])-3);?>"
                               min="1" step="1">
                        <label for="f_sum">.</label>
                        <input class="form_field valid sendable" id="f_sum" 
                               name="f_sum" type="number" 
                               value="<?php echo substr($row['transaction_sum'], strlen($row['transaction_sum'])-2);?>"
                               min="0" max="99" step="1">
                    </div>
                    <div class="form-group">
                        <label for="t_date">Дата:</label>
                        <input class="form_field valid sendable" 
                               id="t_date" name="t_date" type="date" 
                               value="<?php echo $row['T_DATE'];?>" 
                               pattern="^[0-9]{4,4}-([0][1-9]|[1][0-2])-([0][1-9]|[1-2][0-9]|[3][0-1])$" 
                               autocomplete="off">
                        <label for="t_time">Время:</label>
                        <input class="form_field valid sendable" 
                               id="t_time" name="t_date" type="time" 
                               value="<?php echo $row['T_TIME'];?>" 
                               pattern="^([0-1][0-9]|[2][0-3]):[0-5][0-9]:[0-5][0-9]$"
                               placeholder="hrs:mins:secs"
                               autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="t_place_txt">Место:</label>
                        <input type="hidden" name="t_place" id="t_place" 
                               class="form_field valid sendable" 
                               value="<?php echo $row['place_id'];?>"
                               pattern="^[1-9][0-9]*$" focus_on="t_place_txt">
                        <input type="text" class="form-control form_field txt" 
                               value="<?php echo $row['place_name'];?>"
                               autocomplete="off" ac_src="<?php echo get_autocomplete_url();?>" 
                               ac_params="type=t_place;filter=" id="t_place_txt">
                    </div>
                    <div class="form-group">
                        <input type="checkbox" id="use_credit" name="use_credit" 
                               focus_on="t_credit_txt" 
                               value="<?php echo $row['loan_id'];?>" 
                               class="form_field sendable" 
                               onclick="toggle_credit();"
                               <?php if(strlen($row['loan_name'])>0) echo 'checked="checked"';?>>
                        <label for="use_credit">В кредит</label>
                        <label for="t_credit_txt">:</label>
                        <input type="text" class="form-control form_field txt" 
                               value="<?php echo $row['loan_name'];?>"
                               autocomplete="off" bound_id="use_credit" 
                               ac_src="<?php echo get_autocomplete_url();?>" 
                               ac_params="type=t_credit;filter=" id="t_credit_txt"
                               style="<?php if(strlen($row['loan_name'])<1) echo 'style="display:none"';?>">
                    </div>
            </DIV>
            <DIV class="modal-footer" id="dlg_box_btns">
                <button class="btn" id="OK_BTN" type="button"
                        onclick="if(fancy_form_validate('expenses')) tx_submit('<?php echo dirname($_SERVER['PHP_SELF']);?>/wimm_edit2.php');">
                    <span class="glyphicon glyphicon-save"></span> Сохранить
                </button>
                <button class="btn" type="button"
                        onclick="openImport('#dialog_box','#import_box', '#txt2Import');">
                    <span class="glyphicon glyphicon-edit"></span> Импорт текста...
                </button>
                <button class="btn" type="submit" data-dismiss="modal">
                    <span class="glyphicon glyphicon-erase"></span> Отмена
                </button>
            </DIV>
        </div>
        </form>
    </body>
    <script language="JavaScript" type="text/JavaScript" src="js/form_common.js"></script>
<?php    
    if(isMSIE())   {
        echo '    <script language="JavaScript" type="text/JavaScript" '
           . 'src="js/jquery-1.11.1.js"></script>' . PHP_EOL;
        echo '    <script language="JavaScript" type="text/JavaScript" '
           . 'src="js/json2.js"></script>' . PHP_EOL;
    }
    else {
        echo '    <script language="JavaScript" type="text/JavaScript" '
           . 'src="js/jquery-2.1.1.js"></script>' . PHP_EOL;
    }
?>
    <script language="JavaScript" type="text/JavaScript" src="js/jquery-ui.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="js/index_aj.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="js/jquery_autocomplete_ifd.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="js/invoice_text_import.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="js/bootstrap.js"></script>
    <script language="JavaScript" type="text/JavaScript">
        var tmr;
        var tmr2;
        var to;
        function onLoad2()
        {
            to = 500;
            $("body").keydown(function(e) {
                onPageKey(e.keyCode);
            });
            setHandlers(".dtp");
            $('#dialog_box').draggable();
            ac_init("ac", ".txt");
        }
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
            clearTimeout(tmr);
            console.log("focus_fun() end");
        }
        function parseCurrency(jsonData, textStatus, jqXHR, boxID)
        {
            if(!jsonData)
            {
                console.log('entering parseCurrency() - null result');
                return ;
            }
            console.log('entering parseCurrency()');
            var arr = jsonData;
            if(arr.length===1)
            {
                if(arr[0] && arr[0].id && arr[0].text)
                {
                    $("#t_curr").val(arr[0].id);
                    $("#t_curr_txt").val(arr[0].text);
                    console.log('result ' + arr[0].id + ' set' + arr[0].text);
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
            console.log('leaving parseCurrency()');
        }
        function budget_update2()
        {
            clearTimeout(tmr2);
            console.log('entering budget_update2()');
            var v = $("#t_curr").val();
            var b = $("#t_budget").val();
            if(!v && b)
            {
                var query_src = "<?php echo get_autocomplete_url();?>";
                var d = new Date();
                var query_str = "type=t_budcur&filter="+b+"&d="+d;
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
            console.log('leaving budget_update2()');
        }
        function budget_update()
        {
            console.log('entering budget_update()');
            tmr2 = setTimeout(function(){ budget_update2(); }, to);
            console.log('leaving budget_update()');
        }
        function toggle_credit()
        {
            if($("#use_credit").prop("checked"))
            {
                $("#t_credit_txt").prop("disabled","false");
            }
            else
            {
                $("#use_credit").val("");
                $("#t_credit_txt").val("");
                $("#t_credit_txt").prop("disabled","true");
            }
        }
        function doCancel2()
        {
            $('#dialog_box').hide();
            $('#FRM_MODE').val('refresh');
            
        }
    </script>
</html>