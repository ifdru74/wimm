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
	$fn = getRequestParam("file","file.jpeg");
	$filename="/home/webcam/cam1/" . $fn;
	$fr = fopen($filename,"rb");
	if($fr)	{
		header("Content-Type: image/jpeg");
		header("Content-Length: " . filesize($filename));
		fpassthru($fr);
		fclose($fr);
	}
	else	{		$em = "unable to open file " . $fn;
		print_head($em);
		print "<body>$em</body></html>";
	}

?>
