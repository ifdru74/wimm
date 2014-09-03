<?php
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
	$uid = 0;
	include("fun_web.php");
	include("fun_mysql.php");
	$user_name = getRequestParam("UNAME","");
	$cnt = "-1";
//	if(strlen($user_name)>0)	{
//		$conn = f_get_connection();
//	    if($conn)	{
//	    	$uname=mysql_real_escape_string($user_name,$conn);
//			$upasswd=mysql_real_escape_string(getRequestParam("UPASS",""),$conn);
//	    	$sql = "select user_id as cnt, user_name from m_users where user_login='$uname' and user_password=PASSWORD('$upasswd')";
//	    	$res = mysql_query($sql,$conn);
//	    	if($res)	{
//	    		$row = mysql_fetch_assoc($res);
//	    		if($row)	{
//	    			$cnt = $row['cnt'];
//	    			$user_name = $row['user_name'];
//	    			if($cnt!=0)	{
//					$uid = $cnt;
//	    				$_SESSION["UID"] = $cnt;
//	    				$_SESSION["UNAME"] = $user_name;
//	    				$url = "http://" . $_SERVER['HTTP_HOST']
//	    					. dirname($_SERVER['PHP_SELF'])
//	    					. "/index.php";
//	    				header("Location: $url");
//	    			}	    		}
//	    		else
//	    			$cnt = "!row";	    	}
//	    	else
//	    		$cnt = "!res";
//		}
//		else
//			$cnt = "!conn";
//	}
//	else
//		$cnt = strlen($user_name) . " - " .$user_name;
	if(strlen($user_name)>0)	{
            $conn = f_get_connection();
	    if($conn)	{
	    	$uname=value4db($user_name);
			$upasswd=value4db(getRequestParam("UPASS",""));
	    	$sql = "select user_id as cnt, user_name from m_users where user_login='$uname' and user_password=PASSWORD('$upasswd')";
	    	$res = $conn->query($sql);
	    	if($res)	{
                    $row = $res->fetch(PDO::FETCH_ASSOC);
                    if($row)	{
                        $cnt = $row['cnt'];
                        $user_name = $row['user_name'];
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
	print_head("Авторизация");
	if($uid!=0)	{
?>
    <BODY onload="bodyOnLoad();">
        <script language="JavaScript" type="text/JavaScript">
            function bodyOnLoad()
            {	auth.action = 'index.php';
                    auth.submit();
            }
        </script>
        <form name="auth" action="index.php" method="post">
            <P>Вы должны автоматически перейти на другую страницу. Если этого не происходит нажмите на кнопку</P>
            <input name="UID" type="hidden" value="<?php echo $uid;?>">
            <input type="submit" value="Вход">
        </form>
<?php
	}
	else	{
		session_destroy();
?>
    <BODY>
<?php
		print_title("Авторизация");
?>
    <form name="auth" action="wimm_auth.php" method="post">
        <DIV style="margin: 0px auto;">
<?php
		if(strcmp($cnt,"1")!=0)	{
                    print "<TR class=\"hidden\"><TD COLSPAN=\"2\" class=\"hidden\">";
                    if($cnt!=0)
                        showError("Неверное имя пользователя или пароль ($cnt)");
                    print "</TD></TR>\n";
                }
?>
            <DIV style="height: 30px; width:300px;margin: 0px auto;">
                <label for="UNAME">Имя пользователя:</LABEL>
                <input style="float: right;" id="UNAME" name="UNAME" type="text" value="<?php echo $user;?>" autofocus="on">
            </div>
            <DIV style="height: 30px; width:300px;margin: 0px auto;">
                <label for="UPASS">Пароль:</LABEL>
                <input style="float: right;" id="UPASS" name="UPASS" type="password" value="<?php echo $passwd;?>">
            </DIV>
            <DIV style="height: 30px; width:300px;margin: 0px auto;">
                <input style="width:100px;float: left;" type="submit" value="Вход">
                <input style="width:100px;float: right;" type="reset" value="Очистить">
            </DIV>
        </div>
    </form>
<?php
	}
?>
    </body>
</html>
