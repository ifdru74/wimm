<?php
/*
	Purpose: web common functions code
*/

/**
 * filter array with index
 * @param array $a - array to filter
 * @param mixed $idx - array index
 * @param mixed $def_val - default value
 * @return mixed array element or false
 */
function filter_array($a, $idx, $def_val=FALSE)
{
    if(strlen($idx)>0)
    {
        if(isset($a) && is_array($a) && key_exists($idx, $a))
        {
            return $a[$idx];
        }
    }
    return $def_val;
}

/**
 * get request ($POST or $GET) param
 * @param string $paramName
 * @param mixed $pdef_val
 * @return mixed
 */
function   getRequestParam($paramName, $pdef_val="")
{
//    $ret_val = $pdef_val;
//    if(strlen($paramName)>0)	{
//        if(isset($_REQUEST[$paramName]))	{
//            if(!is_null($_REQUEST[$paramName]))	{
//                $ret_val = $_REQUEST[$paramName];
//            }
//        }
//    }
//    return $ret_val;
    return filter_array($_REQUEST, $paramName, $pdef_val);
}

/**
 * get session param
 * @param string $paramName
 * @param mixed $pdef_val
 * @return mixed
 */
function   getSessionParam($paramName, $pdef_val)
{
//    $ret_val = $pdef_val;
//    if(strlen($paramName)>0)	{
//        if(isset($_SESSION[$paramName]))
//            if(!is_null($_SESSION[$paramName]))
//                $ret_val = $_SESSION[$paramName];
//        }
//    return $ret_val;
    return filter_array($_SESSION, $paramName, $pdef_val);
}

/**
 * check whether user authorized or not via session parameter
 * @param string $param_name
 * @return parameter value or false
 */
function auth_check($param_name)
{
    $user = getSessionParam($param_name,"");
    if(strlen($user)<=0)	{
    	$url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/wimm_auth.php";
    	header("Location: $url");
    	$user = FALSE;
    }
    return $user;
}

function showError($errmsg)
{
    echo "<P class=\"error\">$errmsg</P>" . PHP_EOL;
}

function print_head($title)
{
    $sOut = "<!DOCTYPE html>\n<html>" . PHP_EOL;
    $sOut .= "<head>" . PHP_EOL;
    $sOut .= "\t<link rel=\"STYLESHEET\" href=\"css/wimm.css\" type=\"text/css\"/>" . PHP_EOL;
    $sOut .= "\t<link rel=\"STYLESHEET\" href=\"css/jquery_autocomplete_ifd.css\" type=\"text/css\"/>" . PHP_EOL;
    $sOut .= "\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">" . PHP_EOL;
    $sOut .= "\t<meta name=\"GENERATOR\" content=\"IFD\">" . PHP_EOL;
    $sOut .= "\t<link rel=\"SHORTCUT ICON\" href=\"picts/favicon.ico\">" . PHP_EOL;
    $sOut .= "\t<title>$title</title>" . PHP_EOL;
    $sOut .= "\t<script type=\"text/javascript\" src=\"js/form_common.js\"></script>" . PHP_EOL;
    if(isMSIE())    {
        $sOut .= "\t<script type=\"text/javascript\" src=\"js/jquery-1.11.1.js\"></script>" . PHP_EOL;
    }
    else {
        $sOut .= "\t<script type=\"text/javascript\" src=\"js/jquery-2.1.1.js\"></script>" . PHP_EOL;
    }
    $sOut .= "\t<script type=\"text/javascript\" src=\"js/json2.js\"></script>" . PHP_EOL;
    $sOut .= "</head>" . PHP_EOL;
    echo $sOut;
}

function print_body_title($title)
{
    $sOut = "<TABLE WIDTH=\"100%\" class=\"hidden\">" . PHP_EOL;
    $sOut .= "\t<TR class=\"hidden\">" . PHP_EOL;
    $sOut .= "\t\t<TD class=\"hidden\" width=\"75%\"><H2>$title</H2></TD>" . PHP_EOL;
    if(strcmp(basename($_SERVER['PHP_SELF']),"wimm_auth.php")!=0)	{
        $uname=getSessionParam("UNAME","?");
        $sOut .= "<TD class=\"hidden\" width=\"25%\" VALIGN=\"TOP\" ALIGN=\"RIGHT\">";
        $sOut .= "<FONT SIZE=\"-2\">Действующий пользователь:$uname</FONT>";
        $sOut .= "<IMG id=\"show_bar_i\" TITLE=\"Показать панель навигации\" SRC=\"picts/drop_down.gif\" onclick=\"showNavBar('nav_bar_d','show_bar_i')\"></TD>" . PHP_EOL;
    }
    $sOut .= "\t</TR>" . PHP_EOL;
    $sOut .= "</TABLE>" . PHP_EOL;
    $sOut .= "<DIV id=\"nav_bar_d\" style=\"display:none\">" . PHP_EOL;
    $sOut .= "<TABLE WIDTH=\"100%\" class=\"hidden\"><TR class=\"hidden\">" . PHP_EOL;
    $sOut .= "<TD class=\"hidden\"><A HREF=\"wimm_places.php\" TITLE=\"Места надо знать!\">Места</A></TD>";
    $sOut .= "<TD class=\"hidden\"><A HREF=\"wimm_budgets.php\" TITLE=\"Бюджеты!\">Бюджеты</A></TD>";
    $sOut .= "<TD class=\"hidden\"><A HREF=\"wimm_report.php\" TITLE=\"Отчёт!\">Отчёт</A></TD>";
    $sOut .= "<TD class=\"hidden\"><A HREF=\"wimm_ttypes.php\" TITLE=\"Типы транзакций!\">Типы затрат</A></TD>";
    $sOut .= "<TD class=\"hidden\"><A HREF=\"wimm_currency.php\" TITLE=\"Валюты!\">Валюты</A></TD>";
    $sOut .= "<TD class=\"hidden\"><A HREF=\"wimm_curr_rate.php\" TITLE=\"Курсы валют!\">Обменник</A></TD>";
    $sOut .= "<TD class=\"hidden\"><A HREF=\"tree.php\" TITLE=\"Дерево типов!\">Дерево типов</A></TD>";
    $sOut .= "<TD class=\"hidden\"><A HREF=\"wimm_loans.php\" TITLE=\"Кредиты, набранные по жадности!\">Кредиты</A></TD>";
    $sOut .= "\t</TR>" . PHP_EOL;
    $sOut .= "</TABLE>" . PHP_EOL;
    $sOut .= "</DIV>" . PHP_EOL;
    echo $sOut;
}

function print_title($title)
{
    $sOut = "<div style=\"margin: 0px auto;\">" . PHP_EOL;
    $sOut .= "\t\t<H2 style=\"width: 30%; margin: 0px auto;\">$title</H2>" . PHP_EOL;
    $sOut .= "</div>" . PHP_EOL;
    echo $sOut;
}

/**
 * update session param
 * @param string $rname request parameter name
 * @param string $sname session parameter name
 * @param mixed $defval default value
 * @return mixed - new parameter value
 */
function update_param($rname, $sname, $defval='')
{
    $ed = getRequestParam($rname,getSessionParam($sname,$defval));
    if(!key_exists($sname,$_SESSION) || strcmp($_SESSION[$sname],$ed)!=0)
        $_SESSION[$sname] = $ed;
    return $ed;
}

function text4sql($txt)
{
    return str_replace(str_replace(str_replace(str_replace(str_replace($txt,"--","&mdash;")),'/*',''),'*/',''),"'","\'");
}

/**
 * converts superglobals
 */
function init_superglobals()
{
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
}

function isMSIE()
{
    $s = $_SERVER['HTTP_USER_AGENT'];
//    echo "<!-- $s -->" . PHP_EOL;
    return (strpos($s,"MSIE ")!==FALSE);
}

/**
 * starts page pre-init and session, converts superglobals
 */
function page_pre()
{
    $t = time() + 10;
    header("Expires: " . date("D, d M Y H:i:s T", $t));
    init_superglobals();
    session_start();
    return auth_check('UID');
}
