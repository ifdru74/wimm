<?php
/*
	Purpose: mysql common functions code
*/
function f_get_connection()
{	$conn = mysql_connect("localhost","wimm","wimm1");
	if($conn)	{
		mysql_select_db("wimm", $conn);
		mysql_query ('SET NAMES CP1251',$conn);
		mysql_query ('SET COLLATION_CONNECTION=CP1251_GENERAL_CI',$conn);
	}
	return $conn;}

function f_get_error_text($conn,$caption)
{	if(strlen($caption)<1)
		$caption = "Invalid query:";
		$message  = $caption . mysql_error($conn);
	return $message;
}

function f_get_disp_date($sdate)
{	if(strlen($sdate)>0)	{
		$s = substr($sdate,8,2);
		$s = $s . "/" . substr($sdate,5,2);
		$s = $s . " " . substr($sdate,11,8);
	}
	if(strlen($s)<1)
		$s = "&nbsp;";
	return $s;
}

function f_get_col($row,$colname,$def_val)
{	$s = $def_val;
	if(strlen($colname)>0)	{
		if(isset($row))	{
			if(isset($row[$colname]))	{
				if(!is_null($row[$colname]))	{
					$s = $row[$colname];
				}
			}
		}
	}
	return $s;
}

function f_set_sel_options2($sql, $sel, $sel2, $indent=2)
{
        $ind_s = "\t";
        for($i=1; $i<$indent; $i++)
            $ind_s .= "\t";
        print "$ind_s<OPTION value=\"0\">-Не выбран-</OPTION>\r\n";
        $result = mysql_query($sql . " order by 2");
        if($result)           {
                while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
                        $o = "";
                        foreach ($line as $col_value) {
                                if(strlen($o)>0)
                                    $odn = $col_value;
                                else
                                    $o = $col_value;
                        }
                        if(strcmp($o,$sel)==0||strcmp($odn,$sel2)==0)
                                print "$ind_s<OPTION value=\"$o\" selected>$odn</OPTION>\r\n";
                        else
                                print "$ind_s<OPTION value=\"$o\">$odn</OPTION>\r\n";
                }
                mysql_free_result($result);
        }
}

function f_get_exchange_rate($curr_from, $pay_date, $sum)
{
    $exch = 0;
    $ret = $sum;
    $result = mysql_query("select exchange_rate_from/exchange_rate_to*$sum as s_u_m from m_currency_rate where '$pay_date' between open_date and close_date and currency_to=$curr_from");
    if($result)           {
        $line = mysql_fetch_array($result, MYSQL_ASSOC);
        if ($line) {
            foreach ($line as $col_value) {
                $ret = $col_value;
                $exch = 1;
            }
        }
        mysql_free_result($result);
    }
    if($exch!=1)    {
        $result = mysql_query("select exchange_rate_to/exchange_rate_from*$sum as s_u_m from m_currency_rate where '$pay_date' between open_date and close_date and currency_from=$curr_from");
        if($result) {
            $line = mysql_fetch_array($result, MYSQL_ASSOC);
            if ($line) {
                foreach ($line as $col_value) {
                    $ret = $col_value;
                    $exch = 1;
                }
            }
        }
    }
    return $ret;
}

function value4db($val)
{
    // replace everithing that could break out DB
    // replace double quote
    $s1 = str_replace("/*","", $val);
    $s2 = str_replace("--","&mdash;", $s1);
    return addslashes (str_replace("\\","", $s2));
}
?>
