<?php

/**
 * replace autocomplete aci_ pattern from value
 * @param string $val value
 * @return string
 */
function clear_autocomplete($val)
{
    return str_replace("aci_", "", $val);
}

/**
 * clear number from useless characters like <,> and <space>
 * @param string $val number value
 * @return string
 */
function clear_number($val)
{
    if(strpos($val,",")===false)
    {
        return str_replace(" ", '', $val);
    }
    return str_replace(" ", '', str_replace(",",".",$val));
}

/**
 * execute DML statement (insert/update/delete)
 * @param PDO $conn - connection
 * @param string $sql_dml - SQL statement to execute
 * @param array $a_params - statement parameters
 * @param array $a_ret - return values
 * @return array (an updated copy of $a_ret)
 */
function perform_dml($conn, $sql_dml, $a_params, $a_ret)
{
    $a_ret[DML_RET_SQL] = formatSQL($conn, $sql_dml);
    $stmt = $conn->prepare($a_ret[DML_RET_SQL]);
    if($stmt)
    {
        $a_ret[DML_RET_DBG] = '';
        foreach ($a_params as $pkey => $pvalue)
        {
            if(strpos($a_ret[DML_RET_SQL], ":$pkey")===FALSE)
            {
                $a_ret[DML_RET_DBG] .= ("<div id=\"hide_$pkey\" style=\"display:none;\">$pvalue</div>" . PHP_EOL);
                unset($a_params[$pkey]);
            }
            else
            {
                $a_ret[DML_RET_DBG] .= ("<div id=\"show_$pkey\" style=\"display:none;\">$pvalue</div>" . PHP_EOL);
            }
        }
        $a_ret[DML_RETCODE] = $stmt->execute($a_params);
        if($a_ret[DML_RETCODE]===FALSE)
        {
            $a_ret[DML_RETCODE] = -1;
            $a_ret[DML_RET_ERR] = 'SQL error';
            foreach ($stmt->errorInfo() as $key => $value) {
                $a_ret[DML_RET_ERR] .= $value . PHP_EOL;
            }
        }
        else
        {
            if($a_ret[DML_RETCODE]==0)
            {
                $a_ret[DML_RET_ERR] = 'no rows affected';
            }
            else
            {
                if(stripos(trim($sql_dml), 'insert')==0)
                {
                    $a_ret[DML_RET_INS] = $conn->lastInsertId();
                }
            }
        }
    }
    return $a_ret;
}

/**
 * updates sum of the credit
 * @param PDO     $conn   - PDO connection
 * @param integer $t_type - transaction type for credit direction
 * @param integer $t_curr - transaction currency
 * @param real    $t_sum  - transaction sum
 * @param integer $loan_id - loan to update
 */
function update_credit_dml($conn, $t_type, $t_curr, $t_sum, $loan_id)
{
    $t_bits = f_get_single_value($conn, "select type_bits from m_transaction_types where t_type_id=$t_type", FALSE);
    if($t_bits!==FALSE)
    {
        if($t_bits&2)
        {
            $t_sum *= -1;
        }
    }
    $sql = "update m_loans set loan_sum=loan_sum+($t_sum) where loan_id=$loan_id and currency_id=$t_curr";
    $cnt = $conn->exec(formatSQL($conn, $sql));
}
/**
 * DML operations for m_transactions
 * @param PDO $conn - DBMS connection
 * @param string $fm - form mode
 * @return array
 *  dbg_out - debug output
 *  dup_id  - ID of duplicated record
 *  sql     - formatted SQL
 *  retcode - return code (rows affected)
 *  error   - error text
 */
function transaction_dml($conn, $fm, $a_src=FALSE)
{
    if(!is_array($a_src))
    {
        $a_src = $_REQUEST;
    }
    $a_ret = array();
    $div_fmt = '<div id="show_%s" style="display:none;">%s</div>' . PHP_EOL;
    $sql_dml = "";
    $dup_id = false;
    $params = array();
    $params['t_name'] = value4db(urldecode(filter_array($a_src, "t_name","Покупка!")));
    $a_ret[DML_RET_DBG] = sprintf($div_fmt, "t_name", $params['t_name']);
    $params['t_type'] = str_replace("aci_", "", filter_array($a_src, "t_type",1));
    $a_ret[DML_RET_DBG] .= sprintf($div_fmt, "t_type", $params['t_type']);
    $params['t_curr'] = str_replace("aci_", "", filter_array($a_src, "t_curr",2));
    $a_ret[DML_RET_DBG] .= sprintf($div_fmt, "t_curr", $params['t_curr']);
    $params['t_date'] = filter_array($a_src, "t_date",date("Y-m-d H:i:s"));//2014-09-01 20:30:57
    $params['t_open'] = $params['t_date'];
    $a_ret[DML_RET_DBG] .= sprintf($div_fmt, "t_date", $params['t_date']);
    $params['td1'] = substr($params['t_date'], 0, 16);
    $params['t_user'] = str_replace("aci_", "", filter_array($a_src, "t_user",1));
    $a_ret[DML_RET_DBG] .= sprintf($div_fmt, "t_user", $params['t_user']);
    $params['t_place'] = str_replace("aci_", "", filter_array($a_src, "t_place",0));
    $a_ret[DML_RET_DBG] .= sprintf($div_fmt, "t_place", $params['t_place']);
    $params['t_budget'] = str_replace("aci_", "", filter_array($a_src, "t_budget",0));
    $a_ret[DML_RET_DBG] .= sprintf($div_fmt, "t_budget", $params['t_budget']);
    $params['t_sum'] = clear_number(filter_array($a_src, "t_sum",0));
    $tid = value4db(filter_array($a_src, "HIDDEN_ID",FALSE));
    $loan_id = value4db(filter_array($a_src, "use_credit",FALSE));
    if($loan_id!==FALSE && strlen($loan_id)<1)
    {
        $loan_id = FALSE;
    }
    else
    {
        $params['loan_id'] = clear_number($loan_id);
    }
    if($tid!==FALSE)
    {
        $params['id'] = $tid;
    }
    switch($fm)
    {
        case DML_INS:
            $sql_pd = "SELECT transaction_id FROM m_transactions ".
                    "where t_type_id={$params['t_type']} and currency_id={$params['t_curr']} ".
                    "and transaction_sum={$params['t_sum']} ".
                    "and substr(transaction_date,1,16)='{$params['td1']}';";
            $row_pd = f_get_single_value($conn, $sql_pd, FALSE);
            if($row_pd)
            {
                $a_ret[DML_RET_DUP] = $row_pd;
            }
            if(!key_exists(DML_RET_DUP, $a_ret)) {
                $sql_dml = "INSERT INTO m_transactions (transaction_name, ".
                        "t_type_id, currency_id, transaction_sum, transaction_date, ".
                        "user_id, open_date, place_id, budget_id";
                if($loan_id)
                    $sql_dml .= ", loan_id";
                $sql_dml .= ") VALUES(substr(:t_name,1,45), :t_type, :t_curr, :t_sum, :t_date, :t_user, :t_open, "
                        . ":t_place, :t_budget";
                if(key_exists("use_credit", $a_src))
                    $sql_dml .= ", :loan_id)";
                else
                    $sql_dml .= ")";
            }
            break;
        case DML_DEL:
            if(key_exists('id', $params))
            {
                $sql_dml = "delete from m_transactions where transaction_id=:id";
            }
            break;
        case DML_UPD:
            if(key_exists('id', $params))
            {
                $sql_dml = "update m_transactions set transaction_name=substr(:t_name,1,45), "
                        . " t_type_id=:t_type, "
                        . " transaction_date=:t_date, "
                        . " currency_id=:t_curr, "
                        . " transaction_sum=:t_sum, "
                        . " user_id=:t_user, "
                        . " place_id=:t_place, ";
                if($loan_id)
                    $sql_dml .= " loan_id=:loan_id, ";
                $sql_dml .= " budget_id=:t_budget "
                        . " where transaction_id=:id";
            }
            break;
    }
    if(strlen($sql_dml)>0)
    {
        $a_ret = perform_dml($conn, $sql_dml, $params, $a_ret);
        if(strcmp($fm, DML_INS)==0)
        {
            if(key_exists(DML_RET_INS, $a_ret))
                $a_ret['id'] = $a_ret[DML_RET_INS];
        }
        else
        {
            $a_ret['id'] = $params['id'];
        }
        $a_ret['SQL'] = $sql_dml;
        $a_ret['t_sum'] = $params['t_sum'];
        if($loan_id && strcmp($fm, DML_DEL)!=0)
        {
            update_credit_dml($conn, $params['t_type'], $params['t_curr'], 
                    $params['t_sum'], $params['loan_id']);
            $a_ret['credit']=$params['loan_id'];
        }
    }
    return $a_ret;
}

/**
 * DML operations for m_places
 * @param PDO $conn - DBMS connection
 * @param string $fm - form mode
 * @return array
 *  dbg_out - debug output
 *  dup_id  - ID of duplicated record
 *  sql     - formatted SQL
 *  retcode - return code (rows affected)
 *  error   - error text
 */
function places_dml($conn, $fm, $a_src=FALSE)
{
    if(!is_array($a_src))
    {
        $a_src = $_REQUEST;
    }    
    $sql_dml = "";
    $a_ret = array();
    $params['p_name']  = trim(value4db(urldecode(filter_array($a_src, "p_name","Место?"))));
    $params['p_descr'] = trim(value4db(filter_array($a_src, "p_descr",'Описание?')));
    $params['p_inn']   = trim(value4db(filter_array($a_src, "p_inn",'')));
    $params['po_date'] = date("Y-m-d H:i:s");
    $params['p_user']   = getSessionParam('UID', FALSE);
    $params['p_id']   = trim(value4db(filter_array($a_src, "HIDDEN_ID",FALSE)));
    switch($fm)
    {
        case DML_INS:
            $dup_id = f_get_single_value($conn, 
                    "select place_id from m_places where "
                    . "(place_name='{$params['p_name']}' or inn='{$params['p_inn']}') "
                    . "and close_date is null ", FALSE);
            if($dup_id)
            {
                $a_ret[DML_RET_DUP] = $dup_id;
            }
            else
            {
                $sql_dml = "INSERT INTO m_places (place_name, open_date, "
                . "place_descr, inn, user_id) VALUES(substr(:p_name,1,100), :po_date, "
                . ":p_descr, :p_inn, :p_user)";
            }
            break;
        case DML_UPD:
            $sql_dml = "UPDATE m_places SET inn=:p_inn, place_name=substr(:p_name,1,100), "
                . "place_descr=:p_descr where place_id=:p_id";
            break;
        case DML_DEL:
            $sql_dml = "update m_places set close_date=#NOW# where place_id=:p_id";
            break;
    }
    if(strlen($sql_dml)>0)
    {
        $a_ret[DML_RET_SQL] = formatSQL($conn, $sql_dml);
        $stmt = $conn->prepare($a_ret[DML_RET_SQL]);
        if($stmt)
        {
            $a_ret[DML_RET_DBG] = '';
            foreach ($params as $pkey => $pvalue)
            {
                if(strpos($a_ret[DML_RET_SQL], $pkey)===FALSE)
                {
                    unset($params[$pkey]);
                }
                else
                {
                    $a_ret[DML_RET_DBG] .= ("<div id=\"show_$pkey\" style=\"display:none;\">$pvalue</div>" . PHP_EOL);
                }
            }
            $a_ret[DML_RETCODE] = $stmt->execute($params);
            if($a_ret[DML_RETCODE]===FALSE)
            {
                $a_ret[DML_RETCODE] = -1;
                $a_ret[DML_RET_ERR] = 'SQL error';
                foreach ($stmt->errorInfo() as $key => $value) {
                    $a_ret[DML_RET_ERR] .= $value . PHP_EOL;
                }
            }
            else
            {
                if($a_ret[DML_RETCODE]==0)
                {
                    $a_ret[DML_RET_ERR] = 'no rows affected';
                }
            }
        }
    }
    return $a_ret;
}

/**
 * DML operations for m_budget
 * @param PDO $conn - DBMS connection
 * @param string $fm - form mode
 * @return array(
 *  dbg_out => debug output
 *  dup_id  => ID of duplicated record
 *  sql     => formatted SQL
 *  retcode => return code (rows affected)
 *  error   => error text)
 */
function budget_dml($conn, $fm, $a_src=FALSE)
{
    if(!is_array($a_src))
    {
        $a_src = $_REQUEST;
    }
    $sql_dml = "";
    $a_ret = array();
    $params = array();
    $params['b_name'] = value4db(filter_array($a_src, "b_name","Бюджет?"));
    $params['b_descr'] = value4db(filter_array($a_src, "b_descr",""));
    $params['b_curr'] = str_replace("aci_", "", value4db(filter_array($a_src, "b_curr_id",1)));
    $params['b_id'] = value4db(filter_array($a_src, "HIDDEN_ID",0));
    $params['b_parent'] = str_replace("aci_", "", value4db(filter_array($a_src, "b_parent_id",1)));
    switch($fm)
    {
        case DML_INS:
            $dup_id = f_get_single_value($conn, 
                    "select budget_id from m_budget where "
                    . "budget_name='{$params['b_name']}' "
                    . "and close_date is null ", FALSE);
            if($dup_id)
            {
                $a_ret[DML_RET_DUP] = $dup_id;
            }
            else
            {
                $params['b_user'] = value4db(getSessionParam("UID",1));
                $sql_dml = "INSERT INTO m_budget (budget_name, open_date, "
                        . "budget_descr, currency_id, user_id, parent_id, security) "
                        . "VALUES(substr(:b_name,1,200), #NOW#, :b_descr, "
                        . ":b_curr, :b_user, :b_parent, '')";
            }
            break;
        case DML_UPD:
            $sql_dml = "update m_budget set budget_name=:b_name, "
                . "open_date=:bo_date, budget_descr=:b_descr, "
                . "currency_id=:b_curr, parent_id=:b_parent where budget_id=:b_id";
            break;
        case DML_DEL:
            $sql_dml = "update m_budget set close_date=#NOW# where budget_id=:b_id";
            break;
    }
    if(strlen($sql_dml)>0)
    {
        $a_ret = perform_dml($conn, $sql_dml, $params, $a_ret);
    }
    return $a_ret;
}

/**
 * DML operations for m_places
 * @param PDO $conn - DBMS connection
 * @param string $fm - form mode
 * @return array
 *  dbg_out - debug output
 *  dup_id  - ID of duplicated record
 *  sql     - formatted SQL
 *  retcode - return code (rows affected)
 *  error   - error text
 */
function ttypes_dml($conn, $fm, $a_src=FALSE)
{
    if(!is_array($a_src))
    {
        $a_src = $_REQUEST;
    }
    $sql = "";
    $a_ret = array();
    $params = array();
    $params['t_name'] = value4db(filter_array($a_src, "p_name","Транзакция?"));
    $params['t_parent'] = value4db(filter_array($a_src, "p_descr",""));
    $params['t_sign'] = value4db(filter_array($a_src, "p_sign",0));
    $params['t_id'] = value4db(filter_array($a_src, "HIDDEN_ID",0));
    $a_bits = filter_array($a_src, "t_bits",FALSE);
    $params['t_bits'] = 0;
    if(is_array($a_bits))
    {
        foreach ($a_bits as $bit_value) {
            $params['t_bits'] += value4db($bit_value);
        }
    }
    switch ($fm)    {
        case DML_INS:
            $dup_id = f_get_single_value($conn, 
                    "select t_type_id from m_transaction_types where "
                    . "t_type_name='{$params['t_name']}' "
                    . "and close_date is null ", FALSE);
            if($dup_id)
            {
                $a_ret[DML_RET_DUP] = $dup_id;
            }
            else
            {
                $params['t_user'] = value4db(getSessionParam("UID",1));
                $sql = "INSERT INTO m_transaction_types (t_type_name, open_date, "
                        . "parent_type_id, type_sign, user_id, type_bits) VALUES(:t_name,"
                        . "#NOW#, :t_parent, :t_sign, :t_user, :t_bits)";
            }
            break;
        case DML_UPD:
            $sql = "UPDATE m_transaction_types SET t_type_name=:t_name, type_bits=:t_bits, ";
            if(strlen($params['t_parent'])>0)
            {
                $sql .= "parent_type_id=:t_parent, ";
            }
            $sql .= 'type_sign=:t_sign where t_type_id=:t_id';
            break;
        case DML_DEL:
            $sql = "update m_transaction_types set close_date=#NOW# where t_type_id=:t_id";
    }
    if(strlen($sql)>0)
    {
        $a_ret = perform_dml($conn, $sql, $params, $a_ret);
    }
    return $a_ret;
}

/**
 * DML operations for m_goods
 * @param PDO $conn - DBMS connection
 * @param string $fm - form mode
 * @return array
 *  dbg_out - debug output
 *  dup_id  - ID of duplicated record
 *  sql     - formatted SQL
 *  retcode - return code (rows affected)
 *  error   - error text
 */
function goods_dml($conn, $fm, $a_src=FALSE)
{
    if(!is_array($a_src))
    {
        $a_src = $_REQUEST;
    }
    $sql_dml = "";
    $a_ret = array();
    $a_ret = array();
    $params = array();
    $params['g_barcode'] = value4db(filter_array($a_src, "g_barcode",""));
    $params['g_name'] = value4db(filter_array($a_src, "g_name",""));
    $params['g_count'] = value4db(filter_array($a_src, "g_count",0));
    $params['g_weight'] = value4db(filter_array($a_src, "g_weight",0));
    $params['g_type'] = value4db(filter_array($a_src, "g_type",0));
    $params['g_user'] = value4db(getSessionParam("UID",1));
    $params['g_id'] = value4db(filter_array($a_src, "HIDDEN_ID",0));
    switch($fm)
    {
        case DML_INS:
            $dup_id = f_get_single_value_parm($conn, 
                            "select good_id from m_goods where "
                            . "(((:g_barcode is null or length(:g_barcode)<1) and good_name=SUBSTR(:g_name,1,45)) "
                            . "or (length(:g_barcode)>0 and good_barcode=:g_barcode) ) "
                            . "and close_date is null ", 
                            array(':g_barcode'=>$params['g_barcode'],
                                ':g_name'=>$params['g_name']), 
                            FALSE);
            if($dup_id)
            {
                $a_ret[DML_RET_DUP] = $dup_id;
            }
            else
            {
                $params['g_user'] = value4db(filter_array($a_src, "g_user",1));
                $sql_dml = "insert into m_goods (good_barcode, good_name, "
                    . "item_count, net_weight, user_id, good_type_id, open_date) "
                    . "values(:g_barcode, SUBSTR(:g_name,1,45), :g_count, :g_weight, :g_user, "
                    . ":g_type, #NOW#)";
            }
            //$good_id = $conn->lastInsertId();
            break;
        case DML_UPD:
            $sql_dml = "update m_goods set good_barcode=:g_barcode, "
                    . "good_name=SUBSTR(:g_name,1,45), item_count=:g_count, "
                    . "net_weight=:g_weight, user_id=:g_user, "
                    . "good_type_id=:g_type where good_id=:g_id";
            break;
        case DML_DEL:
            $sql_dml = "update m_goods set close_date=#NOW# where good_id=:g_id";
            break;
    }
    if(strlen($sql_dml)>0)
    {
        $a_ret = perform_dml($conn, $sql_dml, $params, $a_ret);
    }
    return $a_ret;
}

/**
 * unified DML diagnostic facility
 * @param array $a_ret - DML result
 * @return boolean FALSE - no diagnostic output inserted
 *                 TRUE - diagnostic inserted
 */
function embed_diag_out($a_ret)
{
    if(key_exists('production', $_SESSION))
    {
        return FALSE;
    }
    foreach ($a_ret as $key => $value) {
        switch($key)
        {
            case DML_RET_DUP:
            case DML_RETCODE:
            case DML_RET_SQL:
                print "<input type='hidden' id='$key' value='$value'>" . PHP_EOL;
                break;
            default :
                print "<!-- $key $value -->" . PHP_EOL;
        }
    }
    return TRUE;
}

/**
 * DML operations for m_currency
 * @param PDO $conn - DBMS connection
 * @param string $fm - form mode
 * @param array  $a_src - data source array
 * @return array
 *  dbg_out - debug output
 *  dup_id  - ID of duplicated record
 *  sql     - formatted SQL
 *  retcode - return code (rows affected)
 *  error   - error text
 */
function currency_dml($conn, $fm, $a_src=FALSE)
{
    if(!is_array($a_src))
    {
        $a_src = $_REQUEST;
    }
    $sql = "";
    $a_ret = array();
    $a_ret = array();
    $params = array();
    $params['c_name'] = value4db(filter_array($a_src, "curr_name","Рубль"));
    $params['c_abbr'] = value4db(filter_array($a_src, "curr_abbr",'?'));
    $params['c_sign'] = value4db(filter_array($a_src, "curr_sign",'$'));
    $params['c_id'] = value4db(filter_array($a_src, "HIDDEN_ID",0));
    switch ($fm)
    {
        case DML_INS:
            $dup_id = f_get_single_value_parm($conn, 
                            "select currency_id from m_currency where "
                            . "(currency_name=:c_name or currency_abbr=:c_abbr)"
                            . "and close_date is null ", 
                            array(':c_name'=>$params['c_name'],
                                ':c_abbr'=>$params['c_abbr']), 
                            FALSE);
            if($dup_id)
            {
                $a_ret[DML_RET_DUP] = $dup_id;
            }
            else
            {
                $params['c_uid'] = value4db(getSessionParam('UID', FALSE));
                $sql = "insert into m_currency(currency_name, currency_abbr, "
                     . "currency_sign, open_date, user_id) values(:c_name, :c_abbr, "
                     . ":c_sign, #NOW#, :c_uid)";
            }
            break;
        case DML_UPD:
            $sql = "update m_currency SET currency_name=:c_name, "
                . "currency_abbr=:c_abbr, currency_sign=:c_sign "
                . "where currency_id=:c_id";
            break;
        case DML_DEL:
            $sql = "update m_currency set close_date=#NOW# where currency_id=:c_id";
            break;
    }
    if(strlen($sql)>0)
    {
        $a_ret = perform_dml($conn, $sql, $params, $a_ret);
    }
    return $a_ret;
}

/**
 * formats datetime field value
 * @param array  $a_src
 * @param string $field_name
 * @param string $def_date
 * @return string formatted datetime value
 */
function getDateFormValue($a_src, $field_name, $def_date)
{
    $dtf = value4db(filter_array($a_src, $field_name,FALSE));
    if(!$dtf)
    {
        $dtf = $def_date;
    }
    return $dtf;
}

/**
 * currency exchange DML
 * @param PDO $conn
 * @param string $fm - work mode
 * @param type $a_src - data source array
 * @return array
 *  dbg_out - debug output
 *  dup_id  - ID of duplicated record
 *  sql     - formatted SQL
 *  retcode - return code (rows affected)
 *  error   - error text
 */
function exchange_dml($conn, $fm, $a_src=FALSE)
{
    if(!is_array($a_src))
    {
        $a_src = $_REQUEST;
    }
    $sql = "";
    $a_ret = [];
    $a_ret = [];
    $params = array();
    $def_date = date("Y-m-d H:i:s");
    $params['tf_curr']   = value4db(filter_array($a_src, "tf_curr",0));
    $params['from_rate'] = value4db(filter_array($a_src, "from_rate",0));
    $params['tt_curr']   = value4db(filter_array($a_src, "tt_curr",0));
    $params['to_rate']   = value4db(filter_array($a_src, "to_rate",0));
    $params['dt_open']   = getDateFormValue($a_src, "dt_open",$def_date);
    $params['dt_close']  = getDateFormValue($a_src, "dt_close",$def_date);
    $params['rate_id']   = value4db(filter_array($a_src, "HIDDEN_ID",0));
    $a_bits = filter_array($a_src, "rate_bits",FALSE);
    $params['rate_bits'] = 0;
    $params['t_rate_bits'] = '';
    if(is_array($a_bits))
    {
        foreach ($a_bits as $bit_value) {
            if(is_numeric($bit_value))
            {
                $params['rate_bits'] += value4db($bit_value);
            }
            $params['t_rate_bits'] .= ("_'" . value4db($bit_value) . "'") ;
        }
    }
    switch ($fm)
    {
        case DML_INS:
            $sql = "insert into m_currency_rate(currency_from, exchange_rate_from, "
                . "currency_to, exchange_rate_to, open_date, close_date, "
                . "user_id, rate_bits) values(:tf_curr, "
                . ":from_rate, :tt_curr, :to_rate, :dt_open, :dt_close, :uid, :rate_bits)";
            $params['uid'] = value4db(getSessionParam('UID', FALSE));
            break;
        case DML_UPD:
            $sql = "UPDATE m_currency_rate SET currency_from=:tf_curr, "
                . "exchange_rate_from=:from_rate, currency_to=:tt_curr, "
                . "exchange_rate_to=:to_rate, open_date=:dt_open, "
                . "close_date=:dt_close, rate_bits=:rate_bits "
                . "where currency_rate_id=:rate_id";
            break;
        case DML_DEL:
            $sql = "delete from m_currency_rate where currency_rate_id=:rate_id";
            break;
    }
    if(strlen($sql)>0)
    {
        $a_ret = perform_dml($conn, $sql, $params, $a_ret);
    }
    return $a_ret;
}

/**
 * currency exchange DML
 * @param PDO $conn
 * @param string $fm - work mode
 * @param type $a_src - data source array
 * @return array
 *  dbg_out - debug output
 *  dup_id  - ID of duplicated record
 *  sql     - formatted SQL
 *  retcode - return code (rows affected)
 *  error   - error text
 */
function loan_dml($conn, $fm, $a_src=FALSE)
{
    if(!is_array($a_src))
    {
        $a_src = $_REQUEST;
    }
    $sql = "";
    $a_ret = array();
    $a_ret = array();
    $params = array();
    $def_date = date("Y-m-d H:i:s");
    $params['l_place']   = value4db(filter_array($a_src, "l_place",1));
    $params['l_name']   = value4db(filter_array($a_src, "l_name","Кредит!"));
    $params['l_bdate']   = value4db(filter_array($a_src, "l_bdate",$def_date));
    $params['l_edate']   = value4db(filter_array($a_src, "l_edate",NULL));
    $params['l_rate']   = value4db(filter_array($a_src, "l_rate",5));
    $params['l_type']   = value4db(filter_array($a_src, "l_type",1));
    $x = value4db(filter_array($a_src, "l_cdate",NULL));
    if(!is_null($x) && strlen($x)>0)
    {
        $params['l_cdate'] = $x;
    }
    else {
        $params['l_cdate'] = NULL;
    }
    $params['l_user']   = value4db(filter_array($a_src, "l_user",1));
    $params['l_curr']   = value4db(filter_array($a_src, "l_curr",2));
    $params['l_budget']   = value4db(filter_array($a_src, "l_budget",1));
    $params['l_sum']   = value4db(filter_array($a_src, "l_sum",0));
    $params['loan_id']   = value4db(filter_array($a_src, "HIDDEN_ID",0));
    $params['l_lim']   = value4db(filter_array($a_src, "l_lim",0));
    switch ($fm)
    {
        case DML_INS:
            $dup_id = f_get_single_value_parm($conn, 
                    "select loan_id from m_loans where place_id=:l_place and "
                    . ":l_bdate > start_date and "
                    . "(end_date IS NULL or :l_edate < end_date) and "
                    . "currency_id=:l_curr and loan_sum=:l_sum", 
                    array(':l_place' => $params['l_place'], 
                          ':l_bdate' => $params['l_bdate'], 
                          ':l_edate' => $params['l_edate'], 
                          ':l_curr'  => $params['l_curr'], 
                          ':l_sum'   => $params['l_sum']), 
                    FALSE);
            if($dup_id)
            {
                $a_ret[DML_RET_DUP] = $dup_id;
            }
            else
            {
                $sql = "insert INTO m_loans (place_id, loan_name, start_date, end_date, "
                    . "loan_rate, loan_type, close_date, user_id, currency_id, "
                    . "budget_id, loan_sum, open_date, loan_limit) VALUES(:l_place, :l_name, :l_bdate, "
                    . ":l_edate, :l_rate, :l_type, :l_cdate, :l_user, :l_curr, "
                    . ":l_budget, :l_sum, #NOW#, :l_lim)";
            }
            break;
        case DML_UPD:
            $sql = "update m_loans SET place_id=:l_place, loan_name=:l_name, "
                . "start_date=:l_bdate, end_date=:l_edate, loan_rate=:l_rate, "
                . "loan_type=:l_type, close_date=:l_cdate, user_id=:l_user, "
                . "currency_id=:l_curr, budget_id=:l_budget, loan_sum=:l_sum, "
                . "loan_limit=:l_lim "
                . "where loan_id=:loan_id";
            break;
        case DML_DEL:
            $sql = "delete from m_loans where loan_id=:loan_id";
            break;
        case 'recalc':
            $sql = "update m_loans set loan_sum=(select sum(t.transaction_sum*IF(tt.type_bits&2!=0,-1,1)) from m_transactions t, m_transaction_types tt where t.loan_id=:loan_id and t.t_type_id=tt.t_type_id) where loan_id=:loan_id";
            break;
    }
    if(strlen($sql)>0)
    {
        $a_ret = perform_dml($conn, $sql, $params, $a_ret);
    }
    return $a_ret;
}

$a_constants = array(
    "DML_INS" => "insert",
    "DML_UPD" => "update",
    "DML_DEL" => "delete",
    "DML_RET_DBG" => "dbg_out",
    'DML_RET_SQL' => 'sql',
    'DML_RETCODE' => 'retcode',
    'DML_RET_ERR' => 'error',
    'DML_RET_INS' => 'insert_id',
    'DML_RET_DUP' => 'dup_id'
);

foreach ($a_constants as $key => $value) {
    if(!defined($key))
    {
        define($key, $value);
    }    
}
