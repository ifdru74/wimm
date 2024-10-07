<?php
final class wimm_config   {
    /**
     * contains wimm parameters
     * @var array 
     */
    const CFG_DSN = "dsn";
    const CFG_USR = "user";
    const CFG_PWD = "password";
    const CFG_INC = "include_path";        // custom include path
    const CFG_AC = "autocomplete";         // autocomplete
    const CFG_SQLN = "CONN_CONFIG";        // alter session commands (after establishing connection)
    const CFG_ROW_LIMIT = "ROW_LIMIT";     // limit query results
    const CFG_AC_ROW_LIMIT = "ACROW_LIMIT";// limit autocomplete query results
    const CFG_CHUNK_SIZE = "CHUNK_SIZE";   // transmitting chunk size in rows
    private  static $properties = array(
            self::CFG_DSN => 'mysql:dbname=wimm;host=localhost',
            //self::CFG_DSN => 'sqlite:D:\\Projects\\wimm\\wimm\\sqlite\\wimm.sqlite',
            self::CFG_USR => 'wimm',
            //self::CFG_USR => '',
            self::CFG_PWD => 'wimm1',
            //self::CFG_PWD => '',
            self::CFG_INC => "cls" . DIRECTORY_SEPARATOR . "table" . PATH_SEPARATOR .
                             "cls" . DIRECTORY_SEPARATOR . "db",
            self::CFG_AC => 'ac_ref.php',
            self::CFG_SQLN => array(
		"names" => 'SET NAMES utf8',
		"collation" => ''   // SET COLLATION_CONNECTION=CP1251_GENERAL_CI
		),
            self::CFG_ROW_LIMIT => 10000, 
            self::CFG_AC_ROW_LIMIT => 100, 
            self::CFG_CHUNK_SIZE => 1000, 
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

