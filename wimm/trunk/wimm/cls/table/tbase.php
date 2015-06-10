<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * base class for whole bundle
 *
 * @author alsh0414
 */
class tbase {
    //put your code here
    /** @var string empty string for internal purpose */
    protected static $sEmpty = "";
    /** @var string containing space for internal purpose */
    protected static $sSpace = " ";
    /** @var string containing indent spaces for internal purpose */
    protected static $sIndent = "    ";
    /** @var string CSS class name combination for initial display */
    public static $PN_CLASS = "class";
    /** @var string CSS direct styles */
    public static $PN_STYLE = "style";
    /** 
     * @var string HTML tag name 
     */
    public static $PN_TAG = "html_tag";
    /** @var string HTML opening tag begin */
    public static $HSO_TAG = "<";
    /** @var string HTML closing tag begin */
    public static $HSC_TAG = "</";
    /** @var string HTML tag end */
    public static $HSE_TAG = ">";
    /** @var array internal properties array */
    protected  $properties;
    /**
     *
     * @var type @var integer indent to emphasize element
     */
    protected $indent;

    /**
     * constructor
     */
    public function __construct() {
        $this->properties = array();
        $this->indent = 1;
    }
    
    /**
     * return property value
     * @param type $prop_name - name
     * @param type $def_val - default value [oprional]
     * @return property value or default value if there is no such property
     */
    public function getValue($prop_name, $def_val=FALSE)
    {
        if(key_exists($prop_name, $this->properties))
        {
            return $this->properties[$prop_name];
        }
        return $def_val;
    }
    
    /**
     * set property value
     * @param type $prop_name - name
     * @param type $prop_val - new value
     */
    public function setValue($prop_name, $prop_val=FALSE)
    {
        if($prop_val===FALSE)
            $this->properties[$prop_name] = self::$sEmpty;
        else
            $this->properties[$prop_name] = $prop_val;
    }

    /**
     * check whether it has property with such a name
     * @param type $prop_name - name to check
     * @param type $is_set - also check value
     * @return TRUE or FALSE
     */
    public function hasValue($prop_name, $is_set=FALSE)
    {
        if(key_exists($prop_name, $this->properties))
        {
            if ($is_set)
            {
                return TRUE;
            } else
            {
                return isset($this->properties[$prop_name]);
            }
        }
        return FALSE;
    }

    /**
     * make indent string
     * @return string
     */
    protected function getIndentString($indent=-1)
    {
        if ($indent < 0) {
            $indent = $this->indent;
        }
        if($indent>0)
        {
            $sOut = self::$sIndent;
            for($i=0; $i<$indent-1; $i++)
            {
                $sOut .= self::$sIndent;
            }
        }
        else
        {
            $sOut = self::$sEmpty;
        }
        return $sOut;
    }
    /**
     * construct string with complete html tag
     * @return string
     */
    public function html()
    {
        return $this->htmlOpen($this->indent) . $this->htmlClose(0);
    }
    /**
     * construct string with opening html tag
     * @return string
     */
    public function htmlOpen($indent=-1)
    {
        $sOut = $this->getIndentString($indent);
        $sOut .= self::$HSO_TAG . $this->getValue(self::$PN_TAG, "div") .
                $this->allAttributes2String() . self::$HSE_TAG;
        return $sOut;
    }
    /**
     * construct string with closing html tag
     * @return string
     */
    public function htmlClose($indent=-1)
    {
        return $this->getIndentString($indent) . self::$HSC_TAG . $this->getValue(self::$PN_TAG, "div") . self::$HSE_TAG;
    }
    
    /**
     * creates ke="value" pair for attributes
     * @param type $attr_name name
     * @param type $attr_val value
     * @return string
     */
    public function htmlAttr($attr_name, $attr_val)
    {
        $sRet = self::$sEmpty;
        switch($attr_name)
        {
            case self::$HSC_TAG:
            case self::$HSE_TAG:
            case self::$HSO_TAG:
//            case self::$PN_CLASS:
//            case self::$PN_STYLE:
            case self::$PN_TAG:
                break;
            default :
                $sRet = " $attr_name=\"$attr_val\"";
        }
        return $sRet;
    }
    
    /**
     * creates all atributes string
     * @return string
     */
    public function allAttributes2String()
    {
        $sOut = self::$sEmpty;
        foreach ($this->properties as $key => $value) {
            $sOut .= $this->htmlAttr($key, $value);
        }
        return $sOut;
    }
    
    /**
     * returns current indent
     * @return integer
     */
    public function getIndent()
    {
        return $this->indent;
    }
    
    /**
     * set new indent
     * @param integer $indent
     */
    public function setIndent($indent=1)
    {
        $this->indent = $indent;
    }
}
