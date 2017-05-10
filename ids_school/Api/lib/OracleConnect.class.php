<?php

class OracleConnect {
    
    private $link;
    
    public function __construct($dbhost = '192.168.1.73/jlk.cc', $username = 'system', $password = 'SXjlinc_2012') {
		
		$this->link = oci_connect($username, $password, $dbhost, 'utf8');
        
        if ($this->link !== false) {
            return $this->link;
        } else {
			$e = oci_error();
            die(json_encode(array('status'=>0, 'msg'=>'数据库连接错误！' . $e['message'])));
        }
    }
    
    public function query($sql, $params=null) {
		
        $return = null;
		
		$stid = oci_parse($this->link, $sql);
		$r = oci_execute($stid);
		if ($r) {
			oci_fetch_all($stid, $return, null, null, OCI_FETCHSTATEMENT_BY_ROW);
			oci_free_statement ($stid);
		}
        
        return $return;
    }
    
    public function closeConnect() {
		oci_close($this->link);
    }
    
}
?>