<?php
include_once 'fun_dbms.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    if (!ini_get('register_globals')) {
       $superglobals = array($_SERVER, $_ENV,
           $_FILES, $_COOKIE, $_POST, $_GET);
       if (isset($_SESSION)) {
           array_unshift($superglobals, $_SESSION);
       }
       foreach ($superglobals as $superglobal) {
           extract($superglobal, EXTR_SKIP);
       }
    }
    session_start();
    include("fun_web.php");
    $user = getSessionParam($param_name,"");
    if(strlen($user)<=0)
        die();
    $a_ret = array();
    $conn = f_get_connection();
    if($conn)	{
        $fm = getRequestParam("FRM_MODE","refresh");
        $tid = getRequestParam("HIDDEN_ID",0);
        $sql = "";
        switch($fm) {
            case "insert":
		$sql = "INSERT INTO m_transactions (transaction_name, t_type_id, currency_id, transaction_sum, transaction_date, user_id, open_date, place_id, budget_id) VALUES(";
		$s = value4db(urldecode(getRequestParam("t_name","Покупка!")));
		$sql .= "'$s',";
		$s = getRequestParam("t_type",1);
		$sql .= "$s,";
		$s = getRequestParam("t_curr",2);
		$sql .= "$s,";
		$s1 = getRequestParam("t_sum",0);
		if(strpos($s1,",")===false)	{
			//print "$fm - Fuck $s in ,\n";
			$s = $s1;
		}
		else	{
			$s = str_replace(",",".",$s1);
		}
		$sql .= "$s,";
		$td = getRequestParam("t_date",date("Y-m-d H:i:s"));
		$sql .= "'$td',";
		$s = getRequestParam("t_user",1);
		$sql .= "$s,";
		$sql .= "'$td',";
		$s = getRequestParam("t_place",0);
		$sql .= "$s,";
		$s = getRequestParam("t_budget",0);
		$sql .= "$s)";
                mysql_query($sql, $conn);
                $a_ret['id'] = mysql_insert_id($conn);
                mysql_query("commit",$conn);
                break;
            case "update":
		$sql = "UPDATE m_transactions SET ";
		$s = urlencode (getRequestParam("t_name","Покупка!"));
		$sql .= "transaction_name='$s',";
		$s = getRequestParam("t_type",1);
		$sql .= "t_type_id=$s,";
		$s = getRequestParam("t_curr",2);
		$sql .= "currency_id=$s,";
		$s1 = getRequestParam("t_sum",0);
		if(strpos($s1,",")===false)   {
			//print "$fm - Fuck $s in ,\n";
			$s = $s1;
		}
		else	{
			$s = str_replace(",",".",$s1);
		}
		$sql .= "transaction_sum=$s,";
		$td = getRequestParam("t_date",date("Y-m-d H:i:s"));
		$sql .= "transaction_date='$td',";
		$s = getRequestParam("t_user",1);
		$sql .= "user_id=$s,";
		$s = getRequestParam("t_place",0);
		$sql .= "place_id=$s,";
		$s = getRequestParam("t_budget",1);
		$sql .= "budget_id=$s where transaction_id=";
		$s = getRequestParam("HIDDEN_ID",0);
		$sql .= $tid;
                mysql_query($sql, $conn);
                mysql_query("commit",$conn);
                $a_ret['id'] = $tid;
                break;
            case "delete":
                $sql = "delete from m_transactions where transaction_id=$tid";
                mysql_query($sql, $conn);
                mysql_query("commit",$conn);
                $a_ret['id'] = $tid;
                break;
            case "refresh":
            default :
                $sql = "select t_type_id, currency_id, transaction_sum, ".
                    "user_id, open_date, close_date, place_id, budget_id ".
                    "transaction_date, from m_transactions where transaction_id=$tid";
                $result = mysql_query($sql);
                if($result) {
                    $line = mysql_fetch_array($result, MYSQL_ASSOC);
                    if ($line)  {
                        foreach ($line as $col_name => $col_value) {
                            $a_ret[$col_name] = $col_value;
                        }
                    }
                }
                break;
        }
        echo json_encode($a_ret);
    }
    else {
        die();
    }

