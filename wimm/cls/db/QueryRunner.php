<?php
include_once 'wimm_config.php';
/**
 * Description of QueryRunner
 *
 * @author ifdru
 */
class QueryRunner {
    /** @var int row counter  */
    protected $iRowCounter;
    /** @var PDOStatement $conn  */
    protected $stmt;
    /* ctor
     * @param PDO $conn
     * @param string $sql - SQL query statement with nnamed parameters (field=:param)
     * @param bool $withParams - query required parameters to execute
     */
    public function __construct($conn, $sql, $withParams=TRUE) {
        if($conn && strlen($sql)>10) {
            if($withParams) {
                $this->stmt = $conn->prepare(formatSQL($conn, $sql));
            } else {
                $this->stmt = $conn->query(formatSQL($conn, $sql));                
            }
            $this->iRowCounter = 0;
        } else {
            $this->stmt = FALSE;
            $this->iRowCounter = -1;
        }
    }
    
    function __destruct()
    {
        if($this->stmt) {
            $this->stmt->closeCursor();
            $this->stmt = FALSE;
        }
    }
    public function isGood() {
        return ($this->stmt && $this->iRowCounter>=0);
    }

    public function close() {
        if($this->stmt) {
            $this->stmt->closeCursor();
            $this->stmt = FALSE;
        }
    }
    
    public function executeWithParams($a_params) {
        if($this->isGood()) {
            $params = [];
            foreach ($a_params as $key => $value) {
                $params[$key] = value4db($value);
            }
            $this->stmt->execute($params);
            $this->iRowCounter = 0;
        }
    }

    public function execute() {
        if($this->isGood()) {
            $this->iRowCounter = 0;
        }
    }

    public function fetch() {
        if($this->iRowCounter < 
                wimm_config::getConfigParam(wimm_config::CFG_ROW_LIMIT, 10000))
        {
            $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
            if($row)    {
                $this->iRowCounter ++;
            }
            return $row;            
        }  else {
            $this->close();
        }
        return FALSE;
    }
}
