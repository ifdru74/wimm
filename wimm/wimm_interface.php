<?php
    include_once 'fun_web.php';
    $uid = auth_check('UID');
    if ($uid === FALSE) {
        die();
    }
    include_once 'fun_dbms.php';
    include_once 'wimm_config.php';

/* currency exchange DML
 * @param PDO $conn
 * @param string $sql - SQL query statement with nnamed parameters (field=:param)
 * @param array $a_params - parameters array (possibly untrusted values)
 * @return array
 */
function executeQueryWithParams($conn, $sql, $a_params=[])
{
    $a_ret = []; // empty array by default
    $a_ret[0] = -1; // something was wrong
    $a_ret[1] = FALSE;
    if($conn && strlen($sql)>10) {
        $params = [];
        foreach ($a_params as $key => $value) {
            $params[$key] = value4db($value);
        }
        $sth = $conn->prepare($sql);
        if($sth)    {
            $sth->execute($params);
            $a_ret[0] = 0;
            $a_ret[1] = sth;
        }
    }
    return $a_ret;
}

/* currency exchange DML
 * @param array $a_params - parameters array (possibly untrusted values)
 * @return bool or array
 */
function nextQueryRow($a_params)
{
    if(!is_array($a_params))    {
        return FALSE; // input is not an array
    }
    if($a_params[0]<0 || !$a_params[1]) {
        return FALSE;   // input is not valid
    }
    if($a_params[0]<
            wimm_config::getConfigParam(wimm_config::CFG_ROW_LIMIT, 10000)) {
        $row = $a_params[1]->fetch(PDO::FETCH_ASSOC);
        if($row)    {
            $a_params[0] ++;
        }
        return $row;
    }
    else    {
        releaseQuery($a_params);
    }
    return FALSE;
}

/* currency exchange DML
 * @param array $a_params - parameters array
 * @return TRUE if query released
 */
function releaseQuery($a_params)
{
    if(!is_array($a_params))    {
        return FALSE; // input is not an array
    }
    if($a_params[1]) {
        $a_params[1]->closeCursor();
        $a_params[1] = FALSE;
    }
    return TRUE;
}