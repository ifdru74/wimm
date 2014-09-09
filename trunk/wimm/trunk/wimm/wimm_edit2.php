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
    $user = getSessionParam("UID","");
    if(strlen($user)<=0)
        die();
    $a_ret = array();
    $conn = f_get_connection();
    $xxx = "";
    if($conn)	{
        $fm = getRequestParam("FRM_MODE","refresh");
        $tid = getRequestParam("HIDDEN_ID",0);
        $t_name = iconv("utf-8", "CP1251", value4db(urldecode(getRequestParam("t_name",""))));
        $sql = "";
        switch($fm) {
            case "insert":
		$sql = "INSERT INTO m_transactions (transaction_name, t_type_id, currency_id, transaction_sum, transaction_date, user_id, open_date, place_id, budget_id) VALUES(";
		$s = $t_name;
                $xxx = $s;
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
                $xxx = $sql;
                $conn->exec($sql);
                $a_ret['id'] = $conn->lastInsertId;
                $x = $conn->errorInfo();
                $a_ret['err'] = $x[2];
                break;
            case "update":
		$sql = "UPDATE m_transactions SET ";
		$s = $t_name;
                $xxx = $s;
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
                //$xxx = $sql;
                $conn->exec($sql);
                $a_ret['id'] = $tid;
                $x = $conn->errorInfo();
                $a_ret['err'] = $x[2];
                break;
            case "delete":
                $sql = "delete from m_transactions where transaction_id=$tid";
                $xxx = $sql;
                $conn->exec($sql);
                $a_ret['id'] = $tid;
                $x = $conn->errorInfo();
                $a_ret['err'] = $x[2];
                break;
            case "refresh":
            default :
                $sql = "select t_type_id, currency_id, transaction_sum, ".
                    "user_id, open_date, close_date, place_id, budget_id ".
                    "transaction_date, from m_transactions where transaction_id=$tid";
                $xxx = $sql;
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
        $a_ret['xxx'] = $xxx;
        $a_ret['tname'] = $t_name;
        echo json_encode($a_ret);
    }
    else {
        die();
    }

