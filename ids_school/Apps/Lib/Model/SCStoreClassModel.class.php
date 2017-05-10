<?php
class SCStoreClassModel extends Model {
    protected $trueTableName = 'TB_sc_storeClass';
    
	public function sortedTypes(&$newList, $dataList, $parentID = 0, $level = 1) {
		foreach ($dataList as &$item) {
			if ($item['Pid'] == $parentID) {
				if ($parentID) {
					if ($newList[$parentID]) {
						$newList[$parentID]['has_children'] = 1;
						$item['path'] = $newList[$parentID]['path'] . '_' . $item['ID'];
					} else {
						$item['path'] = $parentID . '_' . $item['ID'];
					}
				} else {
					$item['path'] = $item['ID'];
				}
				$item['level'] = $level;
				$item['space'] = str_repeat('&nbsp;&nbsp;', ($level-1)*4);
				$newList[$item['ID']] = $item;
				$this->sortedTypes($newList, $dataList, $item['ID'], $level+1);
			}
		}
	}
	
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