<?php
/**
 * 
 * @author 
 * $SchoolRoomModel = D('SchoolRooms');
 */
class SchoolRoomsModel extends Model {
	protected $trueTableName = 'TB_Sch_Room';
	
	public function getList($map){
		return $this->where($map)->select();
	}


	
	
}

