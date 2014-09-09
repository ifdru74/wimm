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
	include("fun_web.php");
	auth_check('UID');
	print_head("Статьи бюджета");
?>
<SCRIPT LANGUAGE="JavaScript" type='text/javascript'>
function showSubTree(elem,ebtn)
{
        if (elem.style.display == "inline")
        {
		ebtn.src="picts/p_tree.gif";
                elem.style.display="none";
                document.getElementById('show_subtree').value=0;
        }
        else
        {
		ebtn.src="picts/m_tree.gif";
                elem.style.display="inline";
                document.getElementById('show_subtree').value=1;
        }
}
function nodeclick(elem,ebtn)
{
	document.getElementById('type1').value=elem;
	document.getElementById('type2').value=ebtn;
}
</SCRIPT>
<BODY>
<FORM name="tree_frm" method="POST" action="tree.php">
<INPUT id="show_subtree" TYPE="HIDDEN" value="0">
<?php
	include ("fun_mysql.php");
	function f_print_level($conn, $parent_id, $level, $div_style, $prefix)
	{
		$p_path="picts/";
		$pic_st = $p_path . "vl_tree.gif";
		$plus_pict = $p_path . "plus.gif";
		$minus_pict = $p_path . "minus.gif";
		$empty_pict = $p_path . "e_tree.gif";
		// set line prefix
		$pre = $prefix;
  		// prepare sql
  		$sql = "SELECT t_type_id, t_type_name, type_sign FROM m_transaction_types mtt where t_type_id<>parent_type_id and parent_type_id=$parent_id";
		// execute SQL
		$res = mysql_query($sql,$conn);
		if($res)	{
			$r = mysql_fetch_assoc($res);
			do {
				$row = $r;
				if(!$row)
					continue;
				$r = mysql_fetch_assoc($res);
				$iid = $row['t_type_id'];
				$idispname = $row['t_type_name'];
				$isign = $row['type_sign'];
				// set subtree and button names
				$btn_name="subtree_btn$iid";
				$div_name="subtree_div$iid";
				if($r)
					$a_name = "$prefix<IMG SRC=\"" . $p_path . "t_tree.gif" . "\">";
				else
					$a_name = "$prefix<IMG SRC=\"" . $p_path . "a_tree.gif" . "\">";
		        // if there's child nodes - show "[+]" else show "-"
		        $res2 = mysql_query("select count(*) as c1 from m_transaction_types where parent_type_id=$iid",$conn);
		        if($res2)	{
		        	$row2 = mysql_fetch_assoc($res2);
		        	if($row2)	{
		        		$child_cnt = $row2['c1'];
		        	}
		        	else
		        		$child_cnt = -2;
		        }
				else	{
					$message  = f_get_error_text($conn, "Invalid query: ");
					echo "<input name=\"item_$iid_err\" type=\"hidden\" value=\"$message\">\n";
					$child_cnt = -1;
				}
				if($child_cnt<=0)	{
					$pic_ch = $p_path . "hl_tree.gif";
					print "$a_name<IMG id=\"$btn_name\" SRC=\"$pic_ch\">";
				}
				else	{
					$pic_ch = $p_path . "p_tree.gif";
					print "$a_name<IMG id=\"$btn_name\" SRC=\"$pic_ch\" onclick=\"showSubTree(document.getElementById('$div_name'),document.getElementById('$btn_name'));\">";
				}
				echo "<input name=\"child_cnt\" type=\"hidden\" value=\"$child_cnt\">\n";
				if($isign>0)
					print "<IMG SRC=\"$plus_pict\">";
				else	{
					if($isign<0)
						print "<IMG SRC=\"$minus_pict\">";
					else
						print "<IMG SRC=\"$empty_pict\">";
				}
				print "<INPUT id=\"check$iid\" TYPE=\"CHECKBOX\" value=\"off\">$idispname<BR>\n";
				if($child_cnt>0)	{
					print "<DIV id=\"$div_name\" style=\"$div_style\">\n";
					if($r)
						$pre = $prefix . "<IMG SRC=\"$pic_st\">";
					else
						$pre = $prefix . "<IMG SRC=\"$empty_pict\">";
					f_print_level($conn, $iid, $level+1,"display:none", $pre);
					print "</DIV>\n";
				}
			}while ($r&&$row);
		}
		else	{
			$message  = f_get_error_text($conn, "Invalid query: ");
			echo "<input name=\"error$level\" type=\"hidden\" value=\"$message\">\n";
			echo "<input name=\"sql$level\" type=\"hidden\" value=\"$sql\">\n";
		}

	}
	// main code
	print "<TABLE WIDTH=\"100%\" BORDER=\"1\"><TR><TD WIDTH=\"40%\">\n";
	$conn = f_get_connection();
	$p_path="picts/";
	$empty_pict = $p_path . "m_tree.gif";
	echo "<IMG id=\"subtree_btn\" SRC=\"$empty_pict\" onclick=\"showSubTree(document.getElementById('subtree'),document.getElementById('subtree_btn'));\">\n";
	echo "<IMG SRC=\"" . $p_path . "e_tree.gif" . "\">";
	echo "<INPUT id=\"check1\" TYPE=\"CHECKBOX\" value=\"off\">Транзакции<BR>\n";
	echo "<DIV id=\"subtree\" style=\"display:inline\">\n";
	f_print_level($conn, 1, 1, "display:none","");
	echo "</DIV>\n";
	print "</TD>\n";
	print "<TD WIDTH=\"60%\" VALIGN=\"TOP\">\n";
	echo "<CENTER><H3>Статья бюджета</H3></CENTER>";
	echo "<input name=\"tt_id\" type=\"hidden\" value=\"0\">\n";
	echo "<P>Наименование:<input name=\"tt_name\" type=\"text\" value=\"\"></P>\n";
	print "<P>Тип:<select size=\"1\" name=\"tt_sign\">\n";
	print "  <option value=\"0\">Транзакция</option>\n";
	print "  <option value=\"1\">Доход</option>\n";
	print "  <option value=\"-1\">Расход</option>\n";
	print "</select>\n</P>";
	print "</TD>";
	print "</TR></TABLE>";
?>
</FORM>
</BODY>
</HTML>