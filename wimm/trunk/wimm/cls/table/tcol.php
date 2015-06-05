<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tcol
 *
 * @author alsh0414
 */
include_once 'tbase.php';
class tcol extends tbase{
    //put your code here
    /** @var string format string for this column or none */
    protected $format_str;
    public function __construct($fmtStr=FALSE) {
        parent::__construct();
        $this->setValue(self::$PN_TAG,"td");
        if($fmtStr===FALSE)
        {
            $this->format_str = self::$sEmpty;
        }
        else
        {
            $this->format_str = $fmtStr;
        }
    }
    /**
     * returns format string
     * @return string
     */
    public function getFormatStr()
    {
        return $this->format_str;
    }
    
    /**
     * sets format string value
     * @param type $fmt_str
     */
    public function setFormatStr($fmt_str)
    {
        $this->format_str = $fmt_str;
    }
    
    public function html($row=FALSE)
    {
        if(strlen($this->format_str)<1)
        {
            return $this->htmlOpen() . "&nbsp;" . $this->htmlClose(0) . PHP_EOL;
        }
        else
        {
            $sOut = $this->format_str;
            if(is_array($row))
            {
                foreach ($row as $key => $value) {
                    $sOut = str_replace("=$key", $value, $sOut);
                }
            }
            return  $this->htmlOpen() . $sOut . $this->htmlClose(0) . PHP_EOL;
        }
        return self::$sEmpty;
    }
}
