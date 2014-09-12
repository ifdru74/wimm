<?php
    header("Cache-Control: no-cache, must-revalidate");
    include_once 'fun_dbms.php';
    include_once 'fun_web.php';
//    $t = time() - 60;
//    header("Expires: " . date("D, d M Y H:i:s T", $t));

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    session_start();
    init_superglobals();
    $user = getSessionParam("UID","");
    $a_ret = array();
    $fm = getRequestParam("FRM_MODE","refresh");
    $a_ret['request'] = $fm;
    if(strlen($user)<=0)    {
        //die("{id:-1,err:'empty user'}");
        $a_ret['err'] = 'empty user';
        die(json_encode($a_ret));
    }
    $conn = f_get_connection();
    $xxx = "";
    if($conn)	{
        $tid = getRequestParam("HIDDEN_ID",0);
        $t_name = value4db(urldecode(getRequestParam("t_name","")));
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
                $result = $conn->query($sql);
                if($result) {
                    $line = $result->fetch(PDO::FETCH_ASSOC);
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
        die(json_encode($a_ret));
    }
    else {
        //die("{id:-1,err:'bad connection'}");
        $a_ret['err'] = 'bad connection';
        die(json_encode($a_ret));
    }

