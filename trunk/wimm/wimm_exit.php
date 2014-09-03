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
    $dest_page = "wimm_auth.php";
    session_start();
    include("fun_web.php");
    $uid = getSessionParam("UID",0);
    if($uid!=0)	{
        update_param("UID",0);
        session_destroy();
    }
    $url = "http://" . $_SERVER['HTTP_HOST']
            . dirname($_SERVER['PHP_SELF'])
            . "/" . $dest_page;
    header("Location: $url");
    print_head("Завершение сеанса");
?>
<script language="JavaScript" type="text/JavaScript">
function bodyOnLoad()
{
	auth.submit();
}
</script>

<body onload="bodyOnLoad()">

<?php
	print "<form name=\"auth\" action=\"$dest_page\" method=\"post\">\n";
	print "<P>Вы должны автоматически перейти на другую страницу. Если этого не происходит нажмите на кнопку</P>\n";
	print "<input type=\"submit\" value=\"Выход\">\n";
	print "</form>\n";

?>

</body>

</html>
