<?php
/*
	Purpose: mysql common functions code
*/
/**
 * opens DB connection
 * @return PDO
 */
function f_get_connection()
{
    $dsn = 'mysql:dbname=wimm;host=localhost';
    $user = 'wimm';
    $password = 'wimm1';
    try {
        $conn = new PDO($dsn, $user, $password);
        $conn->query("SET NAMES CP1251");
        $conn->query("SET COLLATION_CONNECTION=CP1251_GENERAL_CI");
    } catch (PDOException $e) {
        //echo 'Connection failed: ' . $e->getMessage();
        $conn = false;
    }
    return $conn;
}

/**
 * 
 * @param PDO $conn
 * @param string $caption
 * @return string
 */
function f_get_error_text($conn,$caption)
{
    if(strlen($caption)<1)
		$caption = "Invalid query:";
    $message  = $caption . $conn->errorInfo();
    return $message;
}

/**
 * 
 * @param string $sdate
 * @return string
 */
function f_get_disp_date($sdate)
{
    if(strlen($sdate)>0)	{
        $s = substr($sdate,8,2);
        $s = $s . "/" . substr($sdate,5,2);
        $s = $s . " " . substr($sdate,11,8);
    }
    if(strlen($s)<1)
        $s = "&nbsp;";
    return $s;
}

/**
 * 
 * @param array $row
 * @param string $colname
 * @param mixed $def_val
 * @return mixed
 */
function f_get_col($row,$colname,$def_val)
{
    $s = $def_val;
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

/**
 * 
 * @param PDO $conn
 * @param string $sql
 * @param mixed $sel
 * @param mixed $sel2
 * @param integer $indent
 */
function f_set_sel_options2($conn, $sql, $sel, $sel2, $indent=2)
{
    $ind_s = "\t";
    for($i=1; $i<$indent; $i++)
        $ind_s .= "\t";
    print "$ind_s<OPTION value=\"0\">-Не выбран-</OPTION>\r\n";
    $sql2 = $sql . " order by 2";
    try {
        $selitem = "";
        $opt_fmt = "%s<OPTION value=\"%s\" selected>%s</OPTION>" . PHP_EOL;
        foreach ($conn->query($sql2) as $line)
        {
            $o = "";
            foreach ($line as $col_value) {
                if(strlen($o)>0)
                    $odn = $col_value;
                else
                    $o = $col_value;
            }
            if(strcmp($o,$sel)==0||strcmp($odn,$sel2)==0)
                $selitem = "selected";//print "$ind_s<OPTION value=\"$o\" selected>$odn</OPTION>\r\n";
            else
                $selitem = "";//print "$ind_s<OPTION value=\"$o\">$odn</OPTION>\r\n";
            printf($opt_fmt,$ind_s, $o, $odn);
        }        
    } catch (PDOException $ex) {
        printf($opt_fmt, $ind_s, $ex->getCode(), $ex->getMessage());
    }
}

/**
 * 
 * @param PDO $conn
 * @param integer $curr_from
 * @param string $pay_date
 * @param mixed $sum
 * @return mixed
 */
function f_get_exchange_rate($conn, $curr_from, $pay_date, $sum)
{
    $exch = 0;
    $ret = $sum;
    try {
        $sql = "select exchange_rate_from/exchange_rate_to*$sum as s_u_m from m_currency_rate where '$pay_date' between open_date and close_date and currency_to=$curr_from";
        foreach($conn->query($sql) as $line)
        {
            foreach ($line as $col_value) {
                $ret = $col_value;
                $exch = 1;
                break;
            }
            break;
        }
        if($exch!=1)    {
            $sql = "select exchange_rate_to/exchange_rate_from*$sum as s_u_m from m_currency_rate where '$pay_date' between open_date and close_date and currency_from=$curr_from";
            foreach($conn->query($sql) as $line)
            {
                foreach ($line as $col_value) {
                    $ret = $col_value;
                    break;
                }
                break;
            }
        }
    } catch (PDOException $ex) {

    }
    return $ret;
}

/**
 * 
 * @param string $val
 * @return string
 */
function value4db($val)
{
    // replace everithing that could break out DB
    // replace double quote
    $s1 = str_replace("/*","", $val);
    $s2 = str_replace("--","&mdash;", $s1);
    return addslashes (str_replace("\\","", $s2));
}

/**
 * 
 * @param PDO $conn
 * @param string $val
 * @return string
 */
function formatSQL($conn, $sql) {
    switch($conn->getAttribute(PDO::ATTR_DRIVER_NAME))
    {
        case "sqlite":
            return str_replace("#NOW#", "datetime()", $sql);
            break;
        case "mysql":
            return str_replace("#NOW#", "NOW()", $sql);
            break;
        case "oracle":
            return str_replace("#NOW#", "SYSDATE", $sql);
            break;
    }
    return $sql;
}