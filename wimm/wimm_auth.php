<?php
	include("fun_web.php");
        init_superglobals();
	session_start();
	$uid = 0;
	include_once 'fun_dbms.php';
	$user_name = getRequestParam("UNAME","");
	$cnt = "-1";
	if(strlen($user_name)>0)	{
            $conn = f_get_connection();
	    if($conn)	{
	    	$uname=value4db($user_name);
			$upasswd=value4db(getRequestParam("UPASS",""));
	    	$sql = formatSQL($conn, "select user_id as cnt, user_name from m_users where user_login='$uname' and user_password=#PASSWORD#('$upasswd')");
	    	$res = $conn->query($sql);
	    	if($res)	{
                    $row = $res->fetch(PDO::FETCH_ASSOC);
                    if($row)	{
                        $cnt = $row['cnt'];
                        $user_name = $row['user_name'];
                        $posted_date = getRequestParam('dtst', FALSE);
                        if(strpos($posted_date, '05')===0)
                        {
                            // day first
                            $_SESSION['locale_date_format'] = 'd.m.Y';
                        }
                        else
                        {
                            // month first
                            $_SESSION['locale_date_format'] = 'm.d.Y';
                        }
                        if($cnt!=0)	{
                            $uid = $cnt;
                            $_SESSION["UID"] = $cnt;
                            $_SESSION["UNAME"] = $user_name;
                            $url = "http://" . $_SERVER['HTTP_HOST']
                                    . dirname($_SERVER['PHP_SELF'])
                                    . "/index.php";
                            header("Location: $url");
                        }	    		}
                    else
                        $cnt = "!row";
                }
	    	else
                    $cnt = "!res";
            }
            else
                $cnt = "!conn";
	}
	else
            $cnt = strlen($user_name) . " - " .$user_name;        
	$page_title = "Авторизация";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="STYLESHEET" href="css/wimm.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/bootstrap.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/jquery_autocomplete_ifd.css" type="text/css"/>
        <link rel="SHORTCUT ICON" href="picts/favicon.ico">
        <title><?php echo $page_title;?></title>
<?php    
    if(isMSIE())   {
?>        
        <script language="JavaScript" type="text/JavaScript" src="js/jquery-1.11.1.js"></script>
        <script language="JavaScript" type="text/JavaScript" src="js/json2.js"></script>
<?php    
    }
    else {
?>        
        <script language="JavaScript" type="text/JavaScript" src="js/jquery-2.1.1.js"></script>
<?php    
    }
?>        
        <script language="JavaScript" type="text/JavaScript" src="js/jquery-ui.js"></script>
        <script language="JavaScript" type="text/JavaScript"></script>
    </head>
    <BODY onload="bodyOnLoad();">
<?php    
	if($uid!=0)
        {
?>
        <script language="JavaScript" type="text/JavaScript">
            function bodyOnLoad()
            {
                document.getElementById('dtst').value = (new Date(2015,01,05)).toLocaleString();
                auth.action = 'index.php';
                auth.submit();
            }
        </script>
        <form name="auth" action="index.php" method="post">
            <P>Вы должны автоматически перейти на другую страницу. Если этого не происходит нажмите на кнопку</P>
            <input type="submit" value="Вход">
        </form>
<?php
            print_title($page_title);
	}
	else	{
		session_destroy();
?>
        <script language="JavaScript" type="text/JavaScript">
            function bodyOnLoad()
            {
                document.getElementById('dtst').value = (new Date(2015,01,05)).toLocaleString();
            }
        </script>
<?php
		print_title("Авторизация");
?>
    <form name="auth" action="wimm_auth.php" method="post" >
        <input type="hidden" id="dtst" name="dtst" value="">
        <DIV style="max-width: 600px; min-width: 300px; margin: 0px auto;">
<?php
		if(strcmp($cnt,"1")!=0)	{
                    print "<TR class=\"hidden\"><TD COLSPAN=\"2\" class=\"hidden\">";
                    if($cnt!=0)
                    {
                        showError("Неверное имя пользователя или пароль ($cnt)");
                    }
                    print "</TD></TR>\n";
                }
?>
            <DIV class="form-group">
                <label for="UNAME">Имя пользователя:</LABEL>
                <input id="UNAME" name="UNAME" type="text" 
                       class="form-control" value="<?php echo $user_name;?>" 
                       autofocus="on">
            </div>
            <DIV class="form-group">
                <label for="UPASS">Пароль:</LABEL>
                <input id="UPASS" name="UPASS" type="password" 
                       class="form-control" value="">
            </DIV>
            <DIV class="form-group">
                <button type="submit" class="btn btn-default">
                    <span class="glyphicon glyphicon-log-in"></span> Вход
                </button>
                <input type="reset" class="btn" value="Очистить">
            </DIV>
        </div>
    </form>
<?php
	}
?>
    </body>
</html>
