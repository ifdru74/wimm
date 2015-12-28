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
function transaction_dml($conn, $fm)
{
    $a_ret = array();
    $div_fmt = '<div id="show_%s" style="display:none;">%s</div>' . PHP_EOL;
    $sql_dml = "";
    $dup_id = false;
    $params = array();
    $params['t_name'] = value4db(urldecode(getRequestParam("t_name","Покупка!")));
    $a_ret['dbg_out'] = sprintf($div_fmt, "t_name", $params['t_name']);
    $params['t_type'] = str_replace("aci_", "", getRequestParam("t_type",1));
    $a_ret['dbg_out'] .= sprintf($div_fmt, "t_type", $params['t_type']);
    $params['t_curr'] = str_replace("aci_", "", getRequestParam("t_curr",2));
    $a_ret['dbg_out'] .= sprintf($div_fmt, "t_curr", $params['t_curr']);
    $params['td'] = getRequestParam("t_date",date("Y-m-d H:i:s"));//2014-09-01 20:30:57
    $a_ret['dbg_out'] .= sprintf($div_fmt, "t_date", $params['td']);
    $params['td1'] = substr($params['td'], 0, 16);
    $params['t_user'] = str_replace("aci_", "", getRequestParam("t_user",1));
    $a_ret['dbg_out'] .= sprintf($div_fmt, "t_user", $params['t_user']);
    $params['t_place'] = str_replace("aci_", "", getRequestParam("t_place",0));
    $a_ret['dbg_out'] .= sprintf($div_fmt, "t_place", $params['t_place']);
    $params['t_budget'] = str_replace("aci_", "", getRequestParam("t_budget",0));
    $a_ret['dbg_out'] .= sprintf($div_fmt, "t_budget", $params['t_budget']);
    $params['t_sum'] = clear_number(getRequestParam("t_sum",0));
    $tid = value4db(getRequestParam("HIDDEN_ID",FALSE));
    if($tid)
    {
        $params['tid'] = $tid;
    }
    switch($fm)
    {
        case "insert":
            $sql_pd = "SELECT transaction_id FROM m_transactions ".
                    "where t_type_id={$params['t_type']} and currency_id={$params['t_curr']} ".
                    "and transaction_sum={$params['t_sum']} ".
                    "and substr(transaction_date,1,16)='{$params['td1']}';";
            $row_pd = f_get_single_value($conn, $sql_pd, FALSE);
            if($row_pd)
            {
                $a_ret['dup_id'] = $row_pd;
            }
            if(!key_exists('dup_id', $a_ret)) {
                $sql_dml = "INSERT INTO m_transactions (transaction_name, ".
                        "t_type_id, currency_id, transaction_sum, transaction_date, ".
                        "user_id, open_date, place_id, budget_id) VALUES(:t_name,"
                        . ":t_type, :t_curr, :t_sum, :t_date, :t_user, :t_open, "
                        . ":t_place, :t_budget)";
            }
            break;
        case 'delete':
            $sql_dml = "delete from m_transactions where transaction_id=:tid";
            break;
        case 'update':
            if(strlen($s)>0 && $s>0)
            {
                $sql_dml = "update m_transactions set transaction_name=:t_name, "
                        . " t_type_id=:t_type, "
                        . " currency_id=:t_curr, "
                        . " transaction_sum=:t_sum, "
                        . " user_id=:t_user, "
                        . " place_id=:t_place, "
                        . " budget_id=:t_budget "
                        . " where transaction_id=:tid";
            }
            break;
    }
    if(strlen($sql_dml)>0)
    {
        $a_ret['sql'] = formatSQL($conn, $sql_dml);
        $stmt = $conn->prepare($a_ret['sql']);
        if($stmt)
        {
            $a_ret['retcode'] = $stmt->execute($params);
            if($a_ret['retcode']===FALSE)
            {
                $a_ret['retcode'] = -1;
                $a_ret['error'] = 'SQL error';
            }
            else
            {
                if($a_ret['retcode']==0)
                {
                    $a_ret['error'] = 'no rows affected';
                }
            }
        }
    }
    return $a_ret;
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
function places_dml($conn, $fm)
{
    $sql_dml = "";
    $a_ret = array();
    $params[':p_name']  = trim(value4db(urldecode(getRequestParam("p_name","Место?"))));
    $params[':p_descr'] = trim(value4db(getRequestParam("p_descr",'Описание?')));
    $params[':p_inn']   = trim(value4db(getRequestParam("p_inn",'')));
    $params[':po_date'] = date("Y-m-d H:i:s");
    $params[':p_user']   = getSessionParam('UID', FALSE);
    $params[':p_id']   = trim(value4db(getRequestParam("HIDDEN_ID",FALSE)));
    switch($fm)
    {
        case 'insert':
            $dup_id = f_get_single_value($conn, 
                    "select place_id from m_places where "
                    . "(place_name='{$params[':p_name']}' of inn='{$params[':p_inn']}') "
                    . "and close_date is null ", FALSE);
            if($dup_id)
            {
                $a_ret['dup_id'] = $dup_id;
            }
            else
            {
                $sql_dml = "INSERT INTO m_places (place_name, open_date, "
                . "place_descr, inn, user_id) VALUES(:p_name, :po_date, "
                . ":p_descr, :p_inn, :p_user)";
            }
            break;
        case 'update':
            $sql_dml = "UPDATE m_places SET inn=:p_inn, place_name=:p_name, "
                . "place_descr=:p_descr where place_id=:p_id";
            break;
        case 'delete':
            $sql_dml = "update m_places set close_date=#NOW# where place_id=:p_id";
            break;
    }
    if(strlen($sql_dml)>0)
    {
        $a_ret['sql'] = formatSQL($conn, $sql_dml);
        $stmt = $conn->prepare($a_ret['sql']);
        if($stmt)
        {
            $a_ret['dbg_out'] = '';
            foreach ($params as $pkey => $pvalue)
            {
                if(strpos($a_ret['sql'], $pkey)===FALSE)
                {
                    unset($params[$pkey]);
                }
                else
                {
                    $a_ret['dbg_out'] .= ("<div id=\"show_$pkey\" style=\"display:none;\">$pvalue</div>" . PHP_EOL);
                }
            }
            $a_ret['retcode'] = $stmt->execute($params);
            if($a_ret['retcode']===FALSE)
            {
                $a_ret['retcode'] = -1;
                $a_ret['error'] = 'SQL error';
                foreach ($stmt->errorInfo() as $key => $value) {
                    $a_ret['error'] .= $value . PHP_EOL;
                }
            }
            else
            {
                if($a_ret['retcode']==0)
                {
                    $a_ret['error'] = 'no rows affected';
                }
            }
        }
    }
    return $a_ret;
}