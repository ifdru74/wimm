<?php
	if (!ini_get('register_globals')) {
		print "<P>SUPERGLOBALS not registered!</P>\n";
	}
        phpinfo ();
?>
