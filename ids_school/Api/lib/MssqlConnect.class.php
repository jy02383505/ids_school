<?php

class MssqlConnect{

    private $link;
    
	/**
	 * 构造函数，当连接其它数据库时，请在构造对象时传入具体参数（zjh）
	*/
    public function __construct($host = '192.168.1.2', $uid = 'sa', $pwd = 'sxjlinc_2012', $db = 'Jolink_School') {
        
        $connectParams = array("UID"=>$uid, "PWD"=>$pwd, "Database"=>$db);
        
        $this->link = sqlsrv_connect($host, $connectParams);
        
        if ($this->link !== false)
            return $this->link;
        else
            die(json_encode(array('status'=>0, 'msg'=>'数据库连接错误！')));
        
    }

    public function query($sql, $params) {
        
        $return = array();
        
        $result = sqlsrv_query($this->link, $sql, $params);
        
        while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
            array_push($return, $row);
        }
        
        return $return;
    }
    
    public function closeConnect() {
        sqlsrv_close($this->link);
    }
    
}
?>