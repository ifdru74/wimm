<?php
    include_once ("fun_web.php");
    $uid = page_pre();
    if($uid===FALSE)
        die();
    $fm = "refresh";
    if(getRequestParam("btn_refresh",FALSE)===FALSE)
    {
        $fm = getRequestParam("FRM_MODE","refresh");
    }
    if(strcmp($fm,'home')==0)
    {
        unset($_SESSION["cpwd_name"]);
        unset($_SESSION["npwd_name"]);
        $url = "http://" . $_SERVER['HTTP_HOST']
                . dirname($_SERVER['PHP_SELF'])
                . "/index.php";
        header("Location: $url");
        
    }
    else
    {
        include_once 'fun_dbms.php';
        $cpwd_name = getSessionParam('cpwd_name', uniqid());
        $npwd_name = getSessionParam('npwd_name', uniqid());
        /**
         * @var $conn PDO 
         */
        $a_row = array("user_name"=>getRequestParam('user_name',FALSE), 
            "user_login"=>getRequestParam('user_login',FALSE), 
            "user_password"=>getRequestParam($cpwd_name,FALSE),
            "new_password"=>getRequestParam($npwd_name,FALSE));
        $cpwd_name = uniqid();
        $_SESSION['cpwd_name'] = $cpwd_name;
        $npwd_name = uniqid();
        $_SESSION['npwd_name'] = $npwd_name;
        $conn = f_get_connection();
        if($conn)
        {
            $sql = "";
            $sqlf = formatSQL($conn, "select user_id from m_users where user_id=$uid and user_password=#PASSWORD#('{$a_row['user_password']}')");
            echo "<!-- $sqlf -->" . PHP_EOL;
            echo "<!-- $fm -->" . PHP_EOL;
            if( strcmp($fm, "refresh")!=0 &&
                f_get_single_value($conn, $sqlf, FALSE))
            {
                switch($fm)
                {
                    case 'save':
                        $sql = "update m_users set user_name='{$a_row['user_name']}', ".
                                "user_login='{$a_row['user_login']}', ";
                        if(strlen($a_row['new_password'])>0)
                        {
                            $sql .= "user_password=#PASSWORD#('{$a_row['new_password']}') ";
                        }
                        $sql .= "where user_id=$uid";
                        break;
                    case 'refresh':
                    default:
                        break;
                }
            }        
            if(strlen($sql)>0)
            {
                $sqlf = formatSQL($conn, $sql);
                $r = $conn->exec(formatSQL($conn, $sqlf));
                if($r!=0 && $r!==FALSE)
                    $a_row['success'] = TRUE;
                echo "<!-- $sqlf -->" . PHP_EOL;
            }
            $sql = "select user_name, user_login from m_users where user_id=$uid";
            $res = $conn->query(formatSQL($conn, $sql));
            if($res)
            {
                while ($row =  $res->fetch(PDO::FETCH_ASSOC))
                {
                    foreach ($row as $key => $value) {
                        $a_row[$key] = $value;
                    }
                    break;
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="STYLESHEET" href="css/wimm.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/bootstrap.css" type="text/css"/>
        <link rel="STYLESHEET" href="css/jquery_autocomplete_ifd.css" type="text/css"/>
        <link rel="SHORTCUT ICON" href="picts/favicon.ico">
        <title>Пользователь</title>
    </head>
    <body onload="onLoad();">
        <div class="container">
        <script language="JavaScript" type="text/JavaScript" src="js/form_common.js"></script>
<?php    
    if(isMSIE())   {
?>        
        <script language="JavaScript" type="text/JavaScript" src="js/jquery-1.11.1.js"></script>
        <script language="JavaScript" type="text/JavaScript" src="js/json2.js"></script>
<?php    
    }
    else {
?>        
        <script language="JavaScript" type="text/JavaScript" src="js/jquery-2.1.1.js"></script>
<?php
    }
?>        
        <script language="JavaScript" type="text/JavaScript" src="js/jquery-ui.js"></script>
        <script language="JavaScript" type="text/JavaScript" src="js/index_aj.js"></script>
        <script language="JavaScript" type="text/JavaScript" src="js/jquery_autocomplete_ifd.js"></script>
        <script language="JavaScript" type="text/JavaScript" src="js/bootstrap.js"></script>
        <script language="JavaScript" type="text/JavaScript">
            function validate_form()
            {
                var v_ret = true;
                var fields = $('#auth').find(".valid");
                var i;

                if(fields!=null && fields!=undefined)
                {
                    for(i=0; i<fields.length; i++)
                    {
                        var field_id = fields[i].value;
                        if(field_id!=null && field_id!=undefined)
                        {
                            if(field_id.length<1)
                            {
                                v_ret = false;
                                alert('Пустое значение');
                                fields[i].focus();
                                break;
                            }
                        }
                        else
                        {
                            fields[i].focus();
                            alert('Пустое значение!');
                            v_ret = false;
                            break;
                        }
                    }
                    if(v_ret)
                    {
                        fields = $('#auth').find(".y");
                        if(fields!=null && fields!=undefined)
                        {
                            if(fields.length>1)
                            {
                                if(fields[0].value!=fields[1].value)
                                {
                                    v_ret = false;
                                    alert('Пароли не совпадают')
                                    fields[0].focus();
                                }
                            }
                        }
                    }
                }
                return v_ret;
            }
            function submit_form(i_type)
            {
                var v_form = document.getElementById('auth');
                if(i_type=='home')
                {
                    $('#FRM_MODE').val(i_type);
                    v_form.action = 'index.php';
                    v_form.submit();
                }
                else
                {
                    if(validate_form())
                    {
                        $('#FRM_MODE').val(i_type);
                        v_form.submit();
                    }
                }
            }
            function onLoad()
            {
<?php
        if(strcmp($fm,'home')==0)   {
?>
                var auth = document.getElementById('auth');
                auth.action = 'index.php';
                auth.submit();
<?php
        }
?>        
                
            }
        </script>
<?php
        if(strcmp($fm,'home')==0)
        {
?>
        <form id="auth" role="form" method="post" action="index.php">
            <p>Вас должны были перенаправить на другую страницу, но если этого не 
                случилось - проследуйте по <a href='index.php'>ссылке</a> 
                или нажмите на кнопку 
                <button type="submit" class="btn btn-default" name="btn_return">
                    <span class="glyphicon glyphicon-home"></span> Перейти
                </button>
            </p>
<?php
        }
        else
        {
?>        
        <form id="auth" role="form" method="post">
            <div class="form-group">
                <label>Имя:</label>
                <input type="text" class="form-control" name="user_name"
                       value="<?php echo $a_row['user_name'];?>">
            </div>
            <div class="form-group">
                <label>Логин:</label>
                <input type="text" class="form-control valid" name="user_login"
                       value="<?php echo $a_row['user_login'];?>">
            </div>
            <div class="form-group">
                <label>Текущий пароль:</label>
                <input type="password" class="form-control valid" name="<?php echo $cpwd_name;?>"
                       value="">
            </div>
            <div class="form-group">
                <label>Новый пароль:</label>
                <input type="password" class="form-control y" name="<?php echo $cpwd_name;?>1"
                       value="" id="<?php echo $cpwd_name;?>1">
            </div>
            <div class="form-group">
                <label>Новый пароль (подтверждение):</label>
                <input type="password" class="form-control y" value="" id="<?php echo $cpwd_name;?>2">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-default" name="btn_refresh">
                    <span class="glyphicon glyphicon-refresh"></span> Обновить
                </button>
                <button type="button" class="btn" name="btn_save" onclick="submit_form('save');">
                    <span class="glyphicon glyphicon-ok"></span> ОК
                </button>
                <button type="button" class="btn" name="btn_return" onclick="submit_form('home');">
                    <span class="glyphicon glyphicon-home"></span> Отмена
                </button>
            </div>
<?php
        }
        if(key_exists('success', $a_row))
        {
            echo '<p>Данные сохранены</p>' . PHP_EOL;
            $_SESSION["UNAME"] = $a_row['user_name'];
        }
?>        
            <input type="hidden" id='FRM_MODE' name='FRM_MODE' value="refresh">
        </form>
    </body>
</html>
