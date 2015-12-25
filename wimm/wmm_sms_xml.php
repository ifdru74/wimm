<?php
    ob_end_clean();
    ob_start();
    include_once ("fun_web.php");
    $uid = page_pre();
    if($uid===FALSE)
        die();
    include_once 'fun_dbms.php';
    $inc = get_include_path();
    set_include_path($inc . ";trunk\\wimm\\cls\\table");
    include_once 'table.php';
    /**
     * @var $conn PDO 
     */
    $conn = f_get_connection();
    //$xml_file_name = $_FILES['DATA_FILE']['name'];
    include_once 'wimm_sms_xml_core.php';
    $aret = parseSMSXml($_FILES['DATA_FILE']['name']);
?>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="STYLESHEET" href="css/wimm.css" type="text/css"/>
        <link rel="SHORTCUT ICON" href="picts/favicon.ico">
        <title>Импорт информации из СМС</title>
    </head>
    <body>
        <h1>Импорт информации из СМС</h1>
        <div><?php echo $aret['message'];?></div>
    </body>
</html>
<?php
    $size = ob_get_length();
    header("Content-Length: $size");
    ob_end_flush();
    flush();
    processSMSXml($aret['xml'],$conn);