<?php
    include("fun_web.php");
    $uid = page_pre();
    if($uid===FALSE)
        die();
    $dtm = new DateTime();
    $ldfmt = 'Y-m-01';//str_replace('d','01',getSessionParam('locale_date_format', 'd.m.Y'));
    $bd = update_param("BDATE", "BEG_DATE", $dtm->format($ldfmt));
    $dtm->add(new DateInterval('P1M'));
    $ed = update_param("EDATE", "END_DATE", $dtm->format($ldfmt));
    $dtm = DateTime::createFromFormat('Y-m-d', $bd);
    $ldfmt = getSessionParam('locale_date_format', 'd.m.Y');
    $dtm2 = DateTime::createFromFormat('Y-m-d', $ed);
    $bfd = $dtm->format($ldfmt);
    $efd = $dtm2->format($ldfmt);
    $bg = getRequestParam("f_budget","-1");
    $p_title = "Отчёт по затратам с $bfd по $efd";
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="STYLESHEET" href="css/wimm.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/bootstrap.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/jquery_autocomplete_ifd.css" type="text/css"/>
        <link rel="SHORTCUT ICON" href="picts/favicon.ico">
        <title><?php echo $p_title; ?></title>
    </head>
    <body>
        <script language="JavaScript" type="text/JavaScript" src="js/form_common.js"></script>
<?php    
    if(isMSIE())   {
?>        
        <script language="JavaScript" type="text/JavaScript" src="js/jquery-1.11.1.js"></script>
<?php    
    }
    else {
?>        
        <script language="JavaScript" type="text/JavaScript" src="js/jquery-2.1.1.js"></script>
<?php    
    }
?>        
        <script language="JavaScript" type="text/JavaScript" src="js/jquery-ui.js"></script>
        <script language="JavaScript" type="text/JavaScript" src="js/bootstrap.js"></script>
        <script language="JavaScript" type="text/JavaScript">
            function doEdit(s1)
            {	if(s1=="exit")	{
                            expenses.action="index.php";
                            expenses.submit();
                    }
            }
        </script>
        <div class="container">
            <form name="expenses" action="wimm_report.php" method="post">

<?php
    print_body_title('');
    include_once 'fun_dbms.php';

    $table_class = "table table-bordered table-responsive table-striped visual2";
    $row_class = "table-hover";
    $conn = f_get_connection();
    if($conn)	{
        print_filter($conn, $bd,$ed,$bg);
        print_buttons(FALSE);
        print "<h2>Где потратили деньги с $bfd по $efd</h2>" . PHP_EOL;
            $fm = getRequestParam("FRM_MODE","refresh");
            print "<form name=\"expenses\" action=\"wimm_report.php\" method=\"post\">\n";
            print "<input name=\"FRM_MODE\" type=\"hidden\" value=\"refresh\">\n";
            print "<TABLE class=\"$table_class\">\n";
            print "<TR>\n";
            print "<TH>Место</TH>\n";
            print "<TH>Сумма</TH>\n";
            print "<TH TITLE=\"относится к последней покупке\">Дата и время</TH>\n";
            print "<TH>Количество</TH>\n";
            print "</TR>\n";
            //print "<TR><TD COLSPAN=\"6\">Подключён</TD></TR>\n";
            $sql = "select mtp.place_name, sum(transaction_sum) as s_um, max(mt.transaction_date) as ltd, count(*) as cnt from m_transactions mt, m_transaction_types  mtt, m_places mtp where mt.t_type_id=mtt.t_type_id and mtt.Type_sign<0 and mt.place_id=mtp.place_id and transaction_date>='$bd' and  transaction_date<'$ed' ";
            if($bg>0)	{
                    $sql .= " and mt.budget_id=$bg ";
            }
            $sql .= " group by mt.place_id order by 2 desc";
            $res = $conn->query($sql);
            $sm = 0;
            $sd = 0;
            $plus_pict = "picts/plus.gif";
            $minus_pict = "picts/minus.gif";
            if($res)	{		//print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
                while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                    print "<TR class=\"$row_class\">\n";
                    $s = $row['place_name'];
                    print "<TD>$s</TD>\n";
                    $s = $row['s_um'];
                    print "<TD>$s</TD>\n";
                    $s = $row['ltd'];
                    print "<TD>$s</TD>\n";
                    $s = $row['cnt'];
                    print "<TD>$s</TD>\n";
                    print "</TR>\n";
                }
            }
            else	{
                $message  = f_get_error_text($conn, "Invalid query: ");
                print "<TR><TD COLSPAN=\"6\">SQL=\"$sql\"<BR>$message</TD></TR>\n";
            }
            print "</TABLE>\n";
            print "<h2>На что потратили деньги с $bfd по $efd</h2>" . PHP_EOL;
            print "<TABLE class=\"$table_class\">\n";
            print "<TR>\n";
            print "<TH>Статья расходов</TH>\n";
            print "<TH>Сумма</TH>\n";
            print "<TH TITLE=\"относится к последней покупке\">Дата и время</TH>\n";
            print "<TH>Количество</TH>\n";
            print "</TR>\n";
            //print "<TR><TD COLSPAN=\"6\">Подключён</TD></TR>\n";
            $sql = "select mtt.t_type_name, sum(transaction_sum) as s_um, max(mt.transaction_date) as ltd, count(*) as cnt from m_transactions mt, m_transaction_types  mtt where mt.t_type_id=mtt.t_type_id and mtt.Type_sign<0 and transaction_date>='$bd' and  transaction_date<'$ed' ";
            if($bg>0)	{
                    $sql .= " and mt.budget_id=$bg ";
            }
            $sql .= " group by mt.t_type_id order by 2 desc";
            $res =$conn->query($sql);
            $sm = 0;
            $sd = 0;
            $c_class = "dark";
            if($res)	{
                //print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
                while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                    print "<TR class=\"$row_class\">\n";
                    $s = $row['t_type_name'];
                    print "<TD>$s</TD>\n";
                    $s = $row['s_um'];
                    print "<TD>$s</TD>\n";
                    $s = $row['ltd'];
                    print "<TD>$s</TD>\n";
                    $s = $row['cnt'];
                    print "<TD>$s</TD>\n";
                    print "</TR>\n";
                }
            }
            else	{
                $message  = f_get_error_text($conn, "Invalid query: ");
                print "<TR><TD COLSPAN=\"6\">SQL=\"$sql\"<BR>$message</TD></TR>\n";
            }
            print "</TABLE>\n";
            print "<h2>Кто и сколько потратил с $bfd по $efd</h2>" . PHP_EOL;
            print "<TABLE class=\"$table_class\">\n";
            print "<TR>\n";
            print "<TH>Кто</TH>\n";
            print "<TH>Сумма</TH>\n";
            print "<TH TITLE=\"относится к последней покупке\">Дата и время</TH>\n";
            print "<TH>Количество</TH>\n";
            print "</TR>\n";
            //print "<TR><TD COLSPAN=\"6\">Подключён</TD></TR>\n";
            $sql = "select mu.user_name, sum(transaction_sum) as s_um, max(mt.transaction_date) as ltd, count(*) as cnt, mtt.type_sign as type_sign from m_transactions mt, m_users mu, m_transaction_types mtt where mt.user_id=mu.user_id and mt.t_type_id=mtt.t_type_id and transaction_date>='$bd' and transaction_date<'$ed' ";
            if($bg>0)	{
                    $sql .= " and mt.budget_id=$bg ";
            }
            $sql .= " group by mt.user_id, mtt.Type_sign order by mu.user_name";
            $res = $conn->query($sql);
            $sm = 0;
            $sd = 0;
            if($res)	{
                //print "<TR><TD COLSPAN=\"6\">Запрос пошёл</TD></TR>\n";
                while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                    print "<TR class=\"$row_class\">\n";
                    $s = $row['user_name'];
                    print "<TD>$s</TD>\n";
                    $s = $row['s_um'];
                    $t = $row['type_sign'];
                    $col_class = "tl_none";
                    if($t<0)
                            $col_class = "tl_minus";//"<IMG SRC=\"$minus_pict\">";
                    else if($t>0)
                            $col_class = "tl_plus";//"<IMG SRC=\"$plus_pict\">";
                    print "<TD><span class='$col_class'>$s</span></TD>\n";
                    $s = $row['ltd'];
                    print "<TD>$s</TD>\n";
                    $s = $row['cnt'];
                    print "<TD>$s</TD>\n";
                    print "</TR>\n";
                }
            }
            else	{
                $message  = f_get_error_text($conn, "Invalid query: ");
                print "<TR><TD COLSPAN=\"6\">SQL=\"$sql\"<BR>$message</TD></TR>\n";
            }
            print "</TABLE>\n";
            print_buttons(FALSE);
    }

?>
            </form>
        </div>
    </body>

</html>
