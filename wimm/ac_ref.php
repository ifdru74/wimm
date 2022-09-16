<?php

    include("fun_web.php");
    include("fun_dbms.php");
    $uid = page_pre();
    $aret = array();
    if($uid!==FALSE)
    {
        $sql = FALSE;
        $rep_id = getRequestParam("except", FALSE);
        $filter = value4db(getRequestParam("ac_filter", FALSE));
        $selector = value4db(getRequestParam("type", FALSE));
        switch($selector)
        {
            case "t_type":
                $sql = "SELECT t_type_id as r_id, t_type_name as r_name FROM m_transaction_types  WHERE close_date is null";
                if($filter!==FALSE)
                {
                    $sql .= " and t_type_name like '$filter%'";
                }
                if($rep_id!==FALSE)
                {
                    $sql .= " and t_type_id<>" . value4db($rep_id);
                }
                break;
            case "t_curr":
                $sql = "SELECT currency_id as r_id, #CONCAT#(currency_name #||# ' (' #||# currency_abbr #||# ')') as r_name FROM m_currency WHERE close_date is null";
                if($filter!==FALSE)
                {
                    $sql .= " and #CONCAT#(currency_name #||# ' (' #||# currency_abbr #||# ')') like '$filter%'";
                }
                if($rep_id!==FALSE)
                {
                    $sql .= " and currency_id<>" . value4db($rep_id);
                }
                break;
            case "t_place":
                $sql = "SELECT place_id as r_id, place_name as r_name FROM m_places WHERE close_date is null";
                if($filter!==FALSE)
                {
                    $sql .= " and (place_name like '$filter%' or place_descr like '%$filter%')";
                }
                if($rep_id!==FALSE)
                {
                    $sql .= " and place_id<>" . value4db($rep_id);
                }
                break;
            case "t_place_inn":
                $sql = "SELECT place_id as r_id, place_name as r_name FROM m_places WHERE close_date is null";
                if($filter!==FALSE)
                {
                    $sql .= " and inn like '$filter%'";
                }
                break;
            case "t_budget":
                $sql = "SELECT budget_id as r_id, budget_name as r_name FROM m_budget WHERE close_date is null";
                if($filter!==FALSE)
                {
                    $sql .= " and budget_name like '$filter%'";
                }
                if($rep_id!==FALSE)
                {
                    $sql .= " and budget_id<>" . value4db($rep_id);
                }
                break;
            case "t_credit":
                $sql = "SELECT loan_id as r_id, loan_name as r_name FROM m_loans WHERE close_date is null and #NOW# between start_date and end_date";
                if($filter!==FALSE)
                {
                    $sql .= " and loan_name like '$filter%'";
                }
                if($rep_id!==FALSE)
                {
                    $sql .= " and loan_id<>" . value4db($rep_id);
                }
                break;
            case "t_goods":
                $sql = "SELECT good_id as r_id, good_name as r_name FROM m_goods WHERE close_date is null and #NOW# between open_date and close_date";
                if($filter!==FALSE)
                {
                    $sql .= " and good_name like '$filter%'";
                }
                if($rep_id!==FALSE)
                {
                    $sql .= " and good_id<>" . value4db($rep_id);
                }
                break;
            case "t_budcur":
                if($filter!==FALSE)
                {
                    $rep_id = value4db($filter);
                    $sql = "SELECT c.currency_id as r_id, "
                            . "#CONCAT#(c.currency_name #||# ' (' #||# c.currency_abbr #||# ')') as r_name "
                            . "FROM m_budget b, m_currency c "
                            . "WHERE b.close_date is null "
                            . "and budget_id=$rep_id "
                            . "and b.currency_id=c.currency_id";
                }
                break;
            default :
                break;
        }
        if($sql!==FALSE)
        {
            include_once 'fun_dbms.php';
            $sql .= " order by 2";
            $conn = f_get_connection();
            $stmt = $conn->query(formatSQL($conn,$sql));
            if($stmt)
            {
                while($row = $stmt->fetch(PDO::FETCH_ASSOC))
                {
                    $aret[] = array('id' => $row['r_id'], 'text' => $row['r_name']);
                }
            }
            if(count($aret)<1)
            {
                //$aret[] = array('id' => 'error', 'text' => "no values for $filter, $rep_id");
                $aret[] = array('sql' => formatSQL($conn,$sql));
            }
        }
        else {
            $aret[] = array('id' => 'error', 'text' => "empty selector", 
                            'type'=>$selector, 'filter'=>$filter);
        }
    }
    else {
        $aret[] = array('id' => 'error', 'text' => "invalid session");
    }
    echo json_encode($aret);
    flush();
    die();
