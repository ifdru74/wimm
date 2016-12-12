<?php
final class wimm_config   {
    /**
     * contains wimm parameters
     * @var array 
     */
    const CFG_DSN = "dsn";
    const CFG_USR = "user";
    const CFG_PWD = "password";
    const CFG_INC = "include_path";
    const CFG_AC = "autocomplete";
    const CFG_SQLN = "CONN_CONFIG";
    private  static $properties = array(
            //self::CFG_DSN => 'mysql:dbname=wimm;host=localhost'
            self::CFG_DSN => 'sqlite:D:\\Projects\\wimm\\wimm\\sqlite\\wimm.sqlite',
            //self::CFG_USR => 'wimm',
            self::CFG_USR => '',
            //self::CFG_PWD => 'wimm1',
            self::CFG_PWD => '',
            self::CFG_INC => "trunk" . DIRECTORY_SEPARATOR .
                             "wimm" . DIRECTORY_SEPARATOR .
                             "cls" . DIRECTORY_SEPARATOR .
                             "table",
            self::CFG_AC => 'ac_ref.php',
//            self::CFG_SQLN => array(
//                "names" => 'SET NAMES utf8',
//                "collation" => ''   // SET COLLATION_CONNECTION=CP1251_GENERAL_CI
//                )
            );
    /**
     * provides access to parameters
     * @param string $param_name - name of parameter to retrieve
     * @param mixed $default_value - default value if no parameter deined
     * @return mixed (usually string)
     */
    public final static function getConfigParam($param_name, $default_value)
    {
        if(key_exists($param_name, self::$properties))
        {
            return self::$properties[$param_name];
        }
        return $default_value;
    }
}

