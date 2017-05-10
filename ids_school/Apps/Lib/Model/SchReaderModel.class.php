<?php
class SchReaderModel extends Model {
    protected $trueTableName = 'TB_Sch_Reader';
	
	public function getChildrenTypes($typeID, $incluedSelf = true) {
	    static $childrenTypes = array();
	    
	    if ($incluedSelf) { 
	        array_push($childrenTypes, $typeID);
	    }
	    
	    $children = $this->where(array('Pid'=>$typeID))->getField('ID', true);
	    if (!empty($children)) {
	        $type = null;
	        foreach ($children as $type) {
        	    if (!$incluedSelf) {
        	        array_push($childrenTypes, $type);
        	    }
	            $this->getChildrenTypes($type, $incluedSelf);
	        }
	    }
	    
	    return $childrenTypes;
	}
	
	/*
	public function getCName($id){
		$data = $this->where(array('ID'=>$id))->find();
		if ($data){
			return $data['tName'];
		}
	}*/
}