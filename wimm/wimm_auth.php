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
	print_head("Авторизация");
	if($uid!=0)	{
?>
    <BODY onload="bodyOnLoad();">
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
	}
	else	{
		session_destroy();
?>
    <BODY onload="bodyOnLoad();">
        <script language="JavaScript" type="text/JavaScript">
            function bodyOnLoad()
            {
                document.getElementById('dtst').value = (new Date(2015,01,05)).toLocaleString();
            }
        </script>
<?php
		print_title("Авторизация");
?>
    <form name="auth" action="wimm_auth.php" method="post">
        <input type="hidden" id="dtst" name="dtst" value="">
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
                <input style="float: right;" id="UNAME" name="UNAME" type="text" value="<?php echo $user_name;?>" autofocus="on">
            </div>
            <DIV style="height: 30px; width:300px;margin: 0px auto;">
                <label for="UPASS">Пароль:</LABEL>
                <input style="float: right;" id="UPASS" name="UPASS" type="password" value="">
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
