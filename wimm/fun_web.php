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

function print_body_title($title='')
{
    $sOut = '';
    if(strcmp(basename($_SERVER['PHP_SELF']),"wimm_auth.php")!=0)	{
        $uname=getSessionParam("UNAME","?");
    }
?>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#nav_bar_d">
                    <span class="glyphicon glyphicon-menu-hamburger"></span>
                </button>
                <a class='navbar-brand' href='wimm_user.php'><?php echo $uname;?></a>
            </div>
            <div class="collapse navbar-collapse" id="nav_bar_d">
                <ul class="nav navbar-nav">
<?php
    $indent = "                        ";
    $sOut = "";
    $a_nav_links = array(
        'index.php'=>array('Расходы',"Куда пропали мои деньги?"),
        'wimm_places.php'=>array('Места',"Места надо знать!"),
        'wimm_budgets.php'=>array('Бюджеты',"Бюджеты!"),
        'wimm_report.php'=>array('Отчёт',"Отчёт!"),
        'wimm_ttypes.php'=>array('Типы затрат',"Типы транзакций!"),
        'wimm_goods.php'=>array('Товары',"Что продают в магазинах!"),
        'wimm_currency.php'=>array('Валюты',"Валюты!"),
        'wimm_curr_rate.php'=>array('Обменник',"Курсы валют!"),
//       'tree.php'=>array('Дерево типов',"Дерево типов!"),
        'wimm_loans.php'=>array('Кредиты' ,"Кредиты, набранные по жадности!")
        );
        foreach ($a_nav_links as $nkey => $nvalue) {
            if(strpos($_SERVER["SCRIPT_FILENAME"], $nkey)!==FALSE)
            {
                $link_class = 'class="active"';
                $link_href="#";
                $sOut .= sprintf('<li class="active"><a title="%s">%s</a></li>',$nvalue[1], $nvalue[0]);
            }
            else
            {
                $sOut .= sprintf('<li><a href="%s" title="%s">%s</a></li>', $nkey, $nvalue[1], $nvalue[0]);
            }
        }
        echo $sOut . PHP_EOL;
?>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <form action="wimm_exit.php" method="post">
                            <input type="hidden" name="FRM_MODE" value="exit">
                            <button type="submit" class="btn navbar-btn" title="Завершить работу">
                                <span class="glyphicon glyphicon-log-out"></span> Выход
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<?php
    if(strlen($title)>0)
    {
        echo "	<H2>$title</H2>" . PHP_EOL;
    }
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

function getUsedLocale()
{
//    return $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $ret = getSessionParam('user_locale', FALSE);
    if($ret===FALSE)
    {
        $default = 'ru';
        $language = array();
        if (($list = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']))) {
            if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $list, $list)) {
                $language = array_combine($list[1], $list[2]);
                foreach ($language as $n => $v)
                    $language[$n] = $v ? $v : 1;
                arsort($language, SORT_NUMERIC);
            }
        }
        $langs=array(
            'ru'=>array('ru','be','uk','ky','ab','mo','et','lv'),
            'de'=>'de',
            'en'=>'en'
        );
        $languages=array();
        foreach ($langs as $lang => $alias) {
            if (is_array($alias)) {
                foreach ($alias as $alias_lang) {
                    $languages[strtolower($alias_lang)] = strtolower($lang);
                }
            }
            else {
                $languages[strtolower($alias)]=strtolower($lang);
            }
        }

        foreach ($language as $l => $v) {
            $s = strtok($l, '-'); // убираем то что идет после тире в языках вида "en-us, ru-ru"
            if (isset($languages[$s]))
            {
                $_SESSION['user_locale'] = $languages[$s];
                return $languages[$s];
            }
        }
        $_SESSION['user_locale'] = $default;
        return $default;    
    }
    return $ret;
}

/**
 * creates table buttons
 * @param string $add_btn_js - javascript for addd button
 */
function print_buttons($add_btn_js)
{
?>
        <div class="btn_cont form-group">
            <button type="submit" class="btn btn-default quick_acc" name="btn_refresh" formnovalidate title="Обновить">
                <span class="glyphicon glyphicon-refresh"></span> Обновить
            </button><br>
<?php
    if($add_btn_js!==FALSE)
    {
?>
            <button type="button" class="btn quick_acc" onclick="<?php echo $add_btn_js;?>"
                    data-toggle="modal" data-target="#dialog_box" title="Добавить">
                <span class="glyphicon glyphicon-plus"></span> Добавить
            </button><br>
            <button type="reset" class="btn quick_acc" title="Снять выделение">
                <span class="glyphicon glyphicon-unchecked"></span> Снять выделение
            </button><br>
<?php
    }
?>
        </div>
<?php
}

/**
 * creates table filter
 * @param PDO $conn  - current connection
 * @param string $bd - period begin date
 * @param string $ed - period end date
 * @param string $bg - selected budget
 */
function print_filter($conn, $bd="",$ed="", $bg="-1")
{
?>
    <div style="display: block; width: 100%;">
<?php
    if(strlen($bd)>0)	{
?>
        <div  class="form-group form-inline filt_cont">
            <label for="BDATE">Дата начала периода:</label>
            <input class="dtp form-control" id="BDATE" name="BDATE" type="date" value="<?php echo $bd;?>" pattern="^[0-9]{4,4}-([0][1-9]|[1][0-2])-([0][1-9]|[1-2][0-9]|[3][0-1])$">
            <label for="EDATE">Дата окончания периода:</label>
            <input class="dtp form-control" id="EDATE" name="EDATE" type="date" value="<?php echo $ed;?>" pattern="^[0-9]{4,4}-([0][1-9]|[1][0-2])-([0][1-9]|[1-2][0-9]|[3][0-1])$">
            <label for="f_budget">Бюджет:</label>
            <select size="1" id="f_budget" name="f_budget" onchange="$('#FRM_MODE').val('refresh'); $('#expenses').submit();" class="form-control">
<?php
            $sql = "SELECT budget_id, budget_name FROM m_budget WHERE close_date is null";
            f_set_sel_options2($conn, $sql, $bg, $bg, 2);
?>
            </select>
        </div>
    </div>
<?php
    }
}
