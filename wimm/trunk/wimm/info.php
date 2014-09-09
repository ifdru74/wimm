<?php
/*	if (!ini_get('register_globals')) {
		print "<P>SUPERGLOBALS not registered!</P>\n";
	}*/
        //phpinfo ();
	$s = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)";
	$x = strpos($s,"MSIE ");
	if(!$x)
		echo "nex!" . PHP_EOL;
	echo isset($x) . PHP_EOL;
	echo is_null($x) . PHP_EOL;
	echo strpos($s,"MSIE ") . PHP_EOL;
?>
