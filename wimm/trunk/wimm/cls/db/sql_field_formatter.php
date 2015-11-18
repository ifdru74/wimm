<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of field_formatter
 *
 * @author alsh0414
 */
class sql_field_formatter {
    const sDot = ".";
    const sCol = ",";
    const VT_INT = "integer";
    const VT_TXT = "text";
    const VT_DAT = "date";
    const VT_REAL = "real";

    //put your code here
    public static function asText($val)
    {
        return "'$val'";
    }
    public static function asDate($val)
    {
        return "'$val'";
    }
    public static function asReal($val)
    {
        if(strpos($val, self::sCol)!==FALSE)
        {
            return str_replace(self::sCol, self::sDot, $val);
        }
        return $val;
    }
    public static function formatField($val, $type)
    {
        $ret = FALSE;
        switch($type)
        {
            case self::VT_TXT:
                $ret = self::asText($val);
                break;
            case self::VT_DAT:
                $ret = self::asDate($val);
                break;
            case self::VT_REAL:
                $ret = self::asReal($val);
                break;
            case self::VT_INT:
            default :
                $ret = $val;
                break;
        }
        return $ret;
    }
}
