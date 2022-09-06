<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tbody
 *
 * @author alsh0414
 */
include_once 'thead.php';
include_once 'tcol.php';
class tbody extends thead {
    /** @var string CSS class name combination for initial display for table row */
    public static $PN_ROW_CLASS = "row_class";
    /** @var string CSS direct styles for table row */
    public static $PN_ROW_STYLE = "row_style";
    /** @var string HTML tag name for table row */
    public static $PN_ROW_TAG = "html_row_tag";
    /** @var array of tbase columns */
    public $columns;
    //put your code here
    /**
     * constructs new instance
     * @param string $className - CSS class name for initial display
     */
    public function __construct($className=FALSE, $rowClass=FALSE) {
        parent::__construct($className);
        $this->columns = array();
        $this->setValue(self::$PN_TAG,"tbody");
        $this->setValue(self::$PN_ROW_CLASS, $rowClass);
    }
    /**
     * produce row output
     * @param array $row
     * @return string
     */
    public function htmlRow($row)
    {
        $indent_str = $this->getIndentString();
        $sOut = $indent_str . self::$HSO_TAG . 
                $this->getValue(self::$PN_ROW_TAG, "tr");
        if($this->hasValue(self::$PN_ROW_STYLE))
        {
            $sOut .= ' style="' . $this->getValue(self::$PN_ROW_STYLE) . '"';
        }
        if($this->hasValue(self::$PN_ROW_CLASS))
        {
            $sOut .= ' class="' . $this->getValue(self::$PN_ROW_CLASS) . '"';
        }
        $sOut .= self::$HSE_TAG . PHP_EOL;
        foreach ($this->columns as $col) 
        {
            $sOut .= $indent_str . self::$sIndent . $col->html($row) . PHP_EOL;
        }
        $sOut .= $indent_str . self::$HSC_TAG . 
                $this->getValue(self::$PN_ROW_TAG, "tr") . 
                self::$HSE_TAG . PHP_EOL;
        return $sOut;
    }
    
    /**
     * returns table body column count
     * @return integer
     */
    public function getColCount()
    {
        return count($this->columns);
    }
}
