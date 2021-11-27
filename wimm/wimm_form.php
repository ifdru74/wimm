<?php
/**
 * print table row bind attributes if enabled
 * @param string $attr_name  - row attribute name
 * @param string $bind_type  - row attribute type
 * @param array  $values     - row values array
 * @param array  $field_name - field name/index in $values array
 */
function row_bind_attr($attr_name, $bind_type, $values, $field_name)
{
    $estr = '';
    $is_bound = filter_array($values, 'row_bound', FALSE);
    if($is_bound)
    {
        $estr = " bind_row_type=\"$bind_type\" bind_row_id=\"$attr_name\" ";
    }
    $field_val = filter_array($values, $field_name, FALSE);
    if($field_val)
    {
        $estr .= " value=\"$field_val\" ";
    }
    if(strlen($estr)>0)
    {
        echo $estr;
    }
}
?>
                            <div scroll_height="100" for="" selected_ac_item="" class="ac_list" id="ac"></div>
                            <div class="form-group">
                                <label for="t_user">Пользователь:</label>
                                <select class="form-control form_field valid sendable" size="1" id="t_user" name="t_user"
                                        pattern="^[1-9][0-9]*$">
<?php
        row_bind_attr("T_USR_", "value", $values, PHP_EOL);
	$sql = "select user_id, user_name from m_users where close_date is null";
	f_set_sel_options2($conn, formatSQL($conn, $sql), $values['uid'],$values['uid'], 2);
?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="t_budget">Бюджет:</label>
                                <input type="hidden" name="t_budget" id="t_budget" class="form_field valid sendable"
                                       <?php row_bind_attr("T_BUDG_", "value", $values,'budget_id');?>
                                       pattern="^[1-9][0-9]*$" focus_on="t_budget_txt" onchange="budget_update();">
                                <input type="text" class="form-control form_field txt"
                                       autocomplete="off" bound_id="t_budget" ac_src="<?php echo get_autocomplete_url();?>" 
                                       <?php row_bind_attr("T_BUDG_", "title", $values,'budget_name');?>
                                       ac_params="type=t_budget;filter=" id="t_budget_txt">
                            </div>
                            <div class="form-group">
                                <label for="t_name">Наименование:</label>
                                <input class="form-control form_field valid sendable" name="t_name" id="t_name" 
                                       type="text" <?php row_bind_attr("TNAME_", "label", $values, 'transaction_name');?> 
                                       pattern="^(?!\s*$).+">
                            </div>
                            <div class="form-group">
                                <label for="t_type">Тип:</label>
                                <input class="form_field valid sendable" type="hidden" name="t_type" id="t_type" 
                                       <?php row_bind_attr("T_TYPE_", "value", $values,'t_type_id');?>
                                       pattern="^[1-9][0-9]*$" focus_on="t_type_txt">
                                <input type="text" name="t_type_name" class="form-control form_field txt"
                                       autocomplete="off" bound_id="t_type" ac_src="<?php echo get_autocomplete_url();?>" 
                                       <?php row_bind_attr("TNAME_", "title", $values,'t_type_name');?> 
                                       ac_params="type=t_type;filter=" id="t_type_txt" scroll_height="10">
                            </div>
                            <div class="form-group">
                                <label for="t_curr">Валюта:</label>
                                <input type="hidden" name="t_curr" id="t_curr" class="form_field valid sendable"
                                       <?php row_bind_attr("T_CURR_", "value", $values,'t_cid');?>
                                       pattern="^[1-9][0-9]*$" focus_on="t_curr_txt">
                                <input type="text" class="form-control form_field txt"
                                       autocomplete="off" bound_id="t_curr" ac_src="<?php echo get_autocomplete_url();?>" 
                                       <?php row_bind_attr("T_CURR_", "title", $values,'currency_name');?>
                                       ac_params="type=t_curr;filter=" id="t_curr_txt">
                            </div>
                            <div class="form-group">
                                <label for="t_sum">Сумма:</label>
                                <input class="form-control form_field valid sendable" id="t_sum" name="t_sum" 
                                       <?php row_bind_attr("T_SUMM_", "title", $values,'transaction_sum');?>
                                       type="text" pattern="^[1-9][0-9]*[.,]?[0-9]?[0-9]?$">
                            </div>
                            <div class="form-group">
                                <label for="t_date">Дата:</label>
                                <input class="dtp form-control form_field valid sendable" id="t_date" 
                                       name="t_date" type="text" 
                                       <?php row_bind_attr("T_DATE_", "title", $values,'transaction_date');?>
                                       pattern="^[0-9]{4,4}-([0][1-9]|[1][0-2])-([0][1-9]|[1-2][0-9]|[3][0-1]) ([0-1][0-9]|[2][0-3]):[0-5][0-9]:[0-5][0-9]$" 
                                       autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="t_place">Место:</label>
                                <input type="hidden" name="t_place" id="t_place" class="form_field valid sendable"
                                       <?php row_bind_attr("T_PLACE_", "value", $values,'place_id');?>
                                       pattern="^[1-9][0-9]*$" focus_on="t_place_txt">
                                <input type="text" class="form-control form_field txt"
                                       autocomplete="off" bound_id="t_place" ac_src="<?php echo get_autocomplete_url();?>" 
                                       <?php row_bind_attr("TP_NAME_", "label", $values,'place_name');?>
                                       ac_params="type=t_place;filter=" id="t_place_txt">
                            </div>
                            <div class="form-group">
                                <input type="checkbox" id="use_credit" name="use_credit" 
                                       <?php 
                                       row_bind_attr("T_CRED_", "value", $values,'loan_id');
                                       $li = filter_array($values, 'loan_id',FALSE);
                                       if($li) echo ' checked="checked" ';
                                       ?>
                                       focus_on="t_credit_txt" class="form_field sendable" 
                                       onclick="toggle_credit();">
                                <label for="use_credit">В кредит:</label>
                                <input type="text" class="form-control form_field txt"
                                       <?php 
                                       row_bind_attr("T_CRED_", "title", $values,'loan_name');
                                       $ln = filter_array($values, 'loan_name',FALSE);
                                       if(!$ln) echo ' disabled="true" ';?>
                                       autocomplete="off" bound_id="use_credit" ac_src="<?php echo get_autocomplete_url();?>" 
                                       ac_params="type=t_credit;filter=" id="t_credit_txt">
                            </div>
<?php
?>
                            <script language="JavaScript" type="text/JavaScript">
                                function toggle_credit()
                                {
                                    if($("#use_credit").prop("checked"))
                                    {
                                        $("#t_credit_txt").prop("disabled","");
                                        $("#t_credit_txt").focus();
                                    }
                                    else
                                    {
                                        $("#use_credit").val("");
                                        $("#t_credit_txt").val("");
                                        $("#t_credit_txt").prop("disabled","disabled");
                                    }
                                }
                            </script>