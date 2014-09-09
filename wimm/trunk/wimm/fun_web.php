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
function filter_array($a, $idx, $def_val)
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
    	return false;
    }
    return $user;
}

function showError($errmsg)
{
    print "<P class=\"error\">$errmsg</P>\n";
}

function print_head($title)
{
    $sOut = "<!DOCTYPE html>\n<html>" . PHP_EOL . PHP_EOL;
    $sOut .= "<head>" . PHP_EOL;
    $sOut .= "\t<link rel=\"STYLESHEET\" href=\"css/wimm.css\" type=\"text/css\"/>" . PHP_EOL;
    $sOut .= "\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">" . PHP_EOL;
    $sOut .= "\t<meta name=\"GENERATOR\" content=\"IFD\">" . PHP_EOL;
    $sOut .= "\t<link rel=\"SHORTCUT ICON\" href=\"picts/favicon.ico\">" . PHP_EOL;
    $sOut .= "\t<title>$title</title>" . PHP_EOL;
    $sOut .= "\t<script type=\"text/javascript\" src=\"js/form_common.js\"></script>" . PHP_EOL;
    $sOut .= "</head>" . PHP_EOL;
    echo $sOut;
}

function print_body_title($title)
{
    print "<TABLE WIDTH=\"100%\" class=\"hidden\">\n";
    print "\t<TR class=\"hidden\">\n";
    print "\t\t<TD class=\"hidden\" width=\"75%\"><H2>$title</H2></TD>\n";
    if(strcmp(basename($_SERVER['PHP_SELF']),"wimm_auth.php")!=0)	{
        $uname=getSessionParam("UNAME","?");
        print "<TD class=\"hidden\" width=\"25%\" VALIGN=\"TOP\" ALIGN=\"RIGHT\">";
        print "<FONT SIZE=\"-2\">Действующий пользователь:$uname</FONT>";
        print "<IMG id=\"show_bar_i\" TITLE=\"Показать панель навигации\" SRC=\"picts/drop_down.gif\" onclick=\"showNavBar('nav_bar_d','show_bar_i')\"></TD>\n";
    }
    print "\t</TR>\n";
    print "</TABLE>\n";
    print "<DIV id=\"nav_bar_d\" style=\"display:none\">\n";
    print "<TABLE WIDTH=\"100%\" class=\"hidden\"><TR class=\"hidden\">\n";
    print "<TD class=\"hidden\"><A HREF=\"wimm_places.php\" TITLE=\"Места надо знать!\">Места</A></TD>";
    print "<TD class=\"hidden\"><A HREF=\"wimm_budgets.php\" TITLE=\"Бюджеты!\">Бюджеты</A></TD>";
    print "<TD class=\"hidden\"><A HREF=\"wimm_report.php\" TITLE=\"Отчёт!\">Отчёт</A></TD>";
    print "<TD class=\"hidden\"><A HREF=\"wimm_ttypes.php\" TITLE=\"Типы транзакций!\">Типы затрат</A></TD>";
    print "<TD class=\"hidden\"><A HREF=\"tree.php\" TITLE=\"Дерево типов!\">Дерево типов</A></TD>";
    print "<TD class=\"hidden\"><A HREF=\"wimm_loans.php\" TITLE=\"Кредиты, набранные по жадности!\">Кредиты</A></TD>";
    print "\t</TR>\n";
    print "</TABLE>\n";
    print "</DIV>\n";
}

function print_title($title)
{
    print "<div style=\"margin: 0px auto;\">\n";
    print "\t\t<H2 style=\"width: 30%; margin: 0px auto;\">$title</H2>\n";
    print "</div>\n";
}

function update_param($rname,$sname,$defval)
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
    echo "<!-- $s -->" . PHP_EOL;
    return (strpos($s,"MSIE ")>0);
}
