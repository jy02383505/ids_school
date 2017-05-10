<?php
/**
 * 
 * @author Skeam TJ
 *
 */
class SchoolGradeModel extends Model {
	protected $trueTableName = 'TB_Sch_Grades';
	
	public function getList($map){
		return $this->where($map)->select();
	}
	/*班级的uuid*/
	public function getOneUUID($id=0){
		$data = $this->where("id=".$id)->field('uuid')->find();
		if ($data){
			return $data['uuid'];
		} else {
			return false;
		}
		
	}
}

