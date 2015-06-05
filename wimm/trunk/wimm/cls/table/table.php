<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * implements table output behaviour
 *
 * @author alsh0414
 */
include_once 'tbase.php';
include_once 'thead.php';
include_once 'tbody.php';
include_once 'tcol.php';
class table extends tbase {
    /** @var tbody this object attributes binding */
    public $attributes; 
    /** @var thead  table header */
    public $head;
    /** @var tbody  table body */
    public $body;


    public function __construct() {
        parent::__construct();
        $this->attributes = new tbase();
        $this->head = new thead();
        $this->body = new tbody();
        $this->setValue(self::$PN_TAG,"table");
    }
    public function htmlOpen($indent=-1)
    {
        return parent::htmlOpen($indent+1) . PHP_EOL .
                $this->head->html($indent+1) . PHP_EOL . 
                $this->body->htmlOpen($indent+1) . PHP_EOL;
    }
    
    public function htmlClose($indent=-1)
    {
        return $this->body->htmlClose($indent+1) . PHP_EOL .
                parent::htmlClose($indent) . PHP_EOL;
    }
    /**
     * produce HTML output for table row
     * @param array $row
     * @return string table row
     */
    public function htmlRow($row)
    {
        return $this->body->htmlRow($row);
    }
    
    /**
     * 
     * @param tcol $col
     * @param boolean $is_header
     */
    public function addColumn($col, $is_header=FALSE)
    {
        if ($is_header) {
            $col->setValue(self::$PN_TAG, "th");
            return $this->head->addColumn($col);
        } else {
            $col->setValue(self::$PN_TAG, "td");
            return $this->body->addColumn($col);
        }
    }
}
