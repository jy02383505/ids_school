<?php

class SchoolPinbiClassModel extends Model {
    protected $trueTableName = 'TB_Sch_Pinbi_Class';
    
	public function sortedTypes(&$newList, $dataList, $parentID = 0, $level = 1) {
		foreach ($dataList as &$item) {
			if ($item['pid'] == $parentID) {
				if ($parentID) {
					if ($newList[$parentID]) {
						$newList[$parentID]['has_children'] = 1;
						$item['path'] = $newList[$parentID]['path'] . '_' . $item['id'];
					} else {
						$item['path'] = $parentID . '_' . $item['id'];
					}
				} else {
					$item['path'] = $item['id'];
				}
				$item['level'] = $level;
				$item['space'] = str_repeat('&nbsp;&nbsp;', ($level-1)*4);
				$newList[$item['id']] = $item;
				$this->sortedTypes($newList, $dataList, $item['id'], $level+1);
			}
		}
	}
	
	public function getChildrenTypes($typeID, $incluedSelf = true) {
	    static $childrenTypes = array();
	    
	    if ($incluedSelf) { 
	        array_push($childrenTypes, $typeID);
	    }
	    
	    $children = $this->where(array('pid'=>$typeID))->getField('id', true);
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
		$data = $this->where(array('id'=>$id))->find();
		if ($data){
			return $data['tName'];
		}
	}*/
}