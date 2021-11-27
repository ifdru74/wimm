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
	include("fun_web.php");
	print_head("Фотографии с камеры");
?>

<body>
<form name="f_get_f_list" action="file_list.php" method="post">
<TABLE WIDTH="100%" class="hidden" BORDER="0">
<?php

$beg_date = getRequestParam("BEG_DATE",date("Y-m-d H:i:00"));
$end_date = getRequestParam("END_DATE",date("Y-m-d H:i:59"));
$dirname = "/home/webcam/cam1";
print "<TR class=\"hidden\"><TD WIDTH=\"30%\" class=\"hidden\">Начало диапазона:</TD><TD class=\"hidden\"><input name=\"BEG_DATE\" type=\"text\" value=\"$beg_date\"></TD></TR>\n";
print "<TR class=\"hidden\"><TD class=\"hidden\">Окончание диапазона:</TD><TD class=\"hidden\"><input name=\"END_DATE\" type=\"text\" value=\"$end_date\"></TD></TR>\n";
print "<TR class=\"hidden\"><TD class=\"hidden\">Каталог:</TD><TD class=\"hidden\">$dirname</TD></TR>\n";
print "<TR class=\"hidden\"><TD class=\"hidden\"><input type=\"submit\" value=\"Применить\"></TD><TD class=\"hidden\"><input type=\"reset\" value=\"Сброс\"></TD></TR>\n";
?>
</TABLE>
</form>
<TABLE WIDTH="100%" BORDER="1" class="visual">
<?php
$b = str_replace("-","",$beg_date);
$b2 = str_replace(":","",$b);
$b = str_replace(" ","",$b2);
sscanf(substr($b,4),"%d",$nb);
$e = str_replace("-","",$end_date);
$e2 = str_replace(":","",$e);
$e = str_replace(" ","",$e2);
sscanf(substr($e,4),"%d",$ne);
$pat = "cam1" . substr($b,0,4);
$pn = strlen($pat);
//print "<TR><TD>$nb</TD></TR>\n";
//print "<TR><TD>$ne</TD></TR>\n";
$d = opendir($dirname);
if($d)	{
	do	{		$s = readdir($d);
		if(strlen($s))	{			if(strncmp($s,$pat,$pn)==0)	{
				$p = strpos($s,".");
				if($p>=$pn)	{					$n = substr($s,$pn,$p-$pn-2);
					sscanf($s,"%d",$ns);				}
				else	{
					$n = strchr($s,".",true);
					$ns = 0;
				}
				$n1 = (0+$n);				$n2 = (0+$nb);
				$n3 = (0+$ne);
				$b1 = ($n1>=$n2);
				$b2 = ($n1<=$n3);
				if($b1===true&&$b2===true)	{					print "<TR><TD><a target=\"blank\" href=\"file_jpeg.php?file=$s\">$s</a></TD></TR>\n";
				}
			}		}	}	while(strlen($s)>0);	closedir($d);}
else	{	die("Unable to open dir " . $dirname);
}
?>
</TABLE>
</body>

</html>