<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of thead
 *
 * @author alsh0414
 */
include_once 'tbase.php';
include_once 'tcol.php';
class thead extends tbase {
    public $columns; /** @var array of tbase columns */
    //put your code here
    /**
     * constructs new instance
     * @param string $className - CSS class name for initial display
     */
    public function __construct($className=FALSE) {
        parent::__construct();
        $this->columns = array();
        $this->setValue(self::$PN_TAG,"thead");
        $this->setValue(self::$PN_CLASS, $className);
    }

    public function html($indent=-1)
    {
        $indent_str = $this->getIndentString();
        $sOut = $this->htmlOpen() . PHP_EOL;
        // assume all values in this array 
        // is a tbase descendants
        $sOut .= "<tr>";
        foreach ($this->columns as $value) {
            $sOut .= ($indent_str . $value->html() . PHP_EOL);
        }
        $sOut .= "</tr>" . $this->htmlClose();
        return $sOut;
    }

    /**
     * set CSS class to all columns that do no have it
     * @param string $class class name string
     */
    public function setColClass($class=self::sEmpty)
    {
        if(strlen($class)>0)
        {
            foreach ($this->columns as $value) {
                /** @var $value tbase */
                if (!$value->hasValue(self::$PN_CLASS)) {
                    $value->setValue(self::$PN_CLASS, $class);
                }
            }
        }
    }
    /**
     * add new column
     * @param tbody $col
     * @return integer new array item count
     */
    public function addColumn($col)
    {
        $col->setIndent($this->indent+1);
        $this->columns[] = $col;
        return count($this->columns);
    }
}
