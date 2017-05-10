<?php
/**
 * 
 * 
 * 示例：$seatModel = D('SchoolSeatPlan');
 */
class SchoolSeatPlanModel extends Model {
	protected $trueTableName = 'TB_Sch_SeatPlan';
	
	/**
	 * 生成座位表默认记录
	 * 第一个参数是班级或教室ID,第二个参数指明第一个参数是班级ID还是教室ID
	*/	
	public function resetSeatPlan($id,$var_type='banjiId'){
		if (!$id){return false;}
		if (empty($var_type)){$var_type = "banjiId";}
		
		if ($var_type == "banjiId"){
			$banjiModel = D('SchoolBanji');
			$datas_banji = $banjiModel->where("id=".$banjiId)->field("roomId")->find();
			$roomId = $datas_banji['roomId'];	
		}else if ($var_type == "roomId"){
			$roomId = $id;
		}

		if (!$roomId){return false;}
		
		//不管有没有，删除
		$this->where("roomId=".$roomId)->delete();
		
		/*
		$studentModel = D("SchoolStudents");
		$datas_student = $studentModel->getBanjiStudentIdArr($banjiId);
		
		foreach($datas_student as $k=>$v){
			$data = array();
			$data['studentId'] = $v;
			$data['roomId'] = $roomId;
		//	$data['line'] = 0;
		//	$data['col'] = 0;
			$result = $this->data($data)->add();
		}
		*/
		
		$roomModel = D('SchoolRooms');
		$datas_room = $roomModel->where("id=".$roomId)->find();
		if ($datas_room){
			$line = $datas_room['linenumber'];
			$col = $datas_room['columnnumber'];	
			
			if ($line && $col){
				//遍历行，然后遍历列，插入对应的记录，每记录对应一个空座位
				for($i=1;$i<$line+1;$i++){
					for($j=1;$j<$col+1;$j++){
						$data = array();
						$data['studentId'] = 0;
						$data['roomId'] = $roomId;
						$data['line'] = $i;
						$data['col'] = $j;
						$result = $this->data($data)->add();
					}
				}
			}
		}else{
			return false;	
		}
		
		//var_dump($datas_student);
	}
	
	/**
	 * 获取座位表，
	 * 结果中包括学生姓名和学生ID
	*/
	public function seatTableData($banjiId){
		if (!$banjiId){return false;}
		
		$banjiModel = D('SchoolBanji');
		$datas_banji = $banjiModel->where("id=".$banjiId)->field("roomId")->find();
		if ($datas_banji){
			$roomId = $datas_banji['roomId'];
		}
		
		if (!$roomId){return false;}
		
		//学生表
		$studentModel = D("SchoolStudents");
		$datas_student = $studentModel->where("banjiId=".$banjiId)->field("id,name")->select();
		
		//座位表
		$datas_seat_table = array();
		$datas_seat_table = $this->where("roomId=".$roomId)->select();
		
		foreach($datas_seat_table as $k=>$v){
			if ($v['studentId']){
				foreach($datas_student as $kk=>$vv){
					if ($vv['id'] == $v['studentId']){
						$datas_seat_table[$k]['studentName'] = $vv['name'];	
					}
				}
			}
		}
		
		//var_dump($datas_seat_table);
		return $datas_seat_table;
		
	}
	
	/**
	 * 修正座位表
	 * 当已有座位表的行和列被重新设置时，用来新增新的行列和删除多余的行列
	*/
	public function modifySeatTable($roomId=0,$old_x=0,$old_y=0,$new_x=0,$new_y=0){
		$map = array();
		$map['roomId'] = $roomId;
		
		//新增行
		if ($old_x < $new_x){
			if ($old_y < $new_y){//新增列
				//新增行（新的列暂忽略不增加）
				for ($x=$old_x+1; $x<=$new_x; $x++) {
					for ($y=1; $y<=$old_y; $y++) {
						$data = array();
						$data['studentId'] = 0;
						$data['roomId'] = $roomId;
						$data['line'] = $x;
						$data['col'] = $y;
						$result = $this->data($data)->add();
					}
				}
				//新增列（从第一行到最大行），即人为的将待增表格分为两块便于循环插入
				for ($x=1; $x<=$new_x; $x++) {
					for ($y=$old_y+1; $y<=$new_y; $y++) {
						$data = array();
						$data['studentId'] = 0;
						$data['roomId'] = $roomId;
						$data['line'] = $x;
						$data['col'] = $y;
						$result = $this->data($data)->add();
					}
				}
			}else if ($old_y > $new_y){//删除列
				//新增行（新的列暂忽略不增加）
				for ($x=$old_x+1; $x<=$new_x; $x++) {
					for ($y=1; $y<=$old_y; $y++) {
						$data = array();
						$data['studentId'] = 0;
						$data['roomId'] = $roomId;
						$data['line'] = $x;
						$data['col'] = $y;
						$result = $this->data($data)->add();
					}
				}
				//删除列
				$map = array();
				$map['roomId'] = $roomId;
				$map['col'] = array("GT",$new_y);
				$result = $this->where($map)->delete();
			}else{
				//列不变
				for ($x=$old_x+1; $x<=$new_x; $x++) {
					for ($y=1; $y<=$old_y; $y++) {
						$data = array();
						$data['studentId'] = 0;
						$data['roomId'] = $roomId;
						$data['line'] = $x;
						$data['col'] = $y;
						$result = $this->data($data)->add();
					}
				}
			}
		} else if ($old_x > $new_x){//删除行
			if ($old_y < $new_y){//新增列
				for ($x=1; $x<=$new_x; $x++) {
					for ($y=$old_y+1; $y<=$new_y; $y++) {
						$data = array();
						$data['studentId'] = 0;
						$data['roomId'] = $roomId;
						$data['line'] = $x;
						$data['col'] = $y;
						$result = $this->data($data)->add();
					}
				}
				//删除行
				$map = array();
				$map['roomId'] = $roomId;
				$map['line'] = array("GT",$new_x);
				$result = $this->where($map)->delete();
			}else if ($old_y > $new_y){//删除列
				//var_dump($new_x);var_dump($new_y);
				//var_dump($map);exit;
				$map = array();
				$map['roomId'] = $roomId;
				$map['line'] = array("GT",$new_x);
				$result = $this->where($map)->delete();
//var_dump($map);
				$map = array();
				$map['roomId'] = $roomId;
				$map['col'] = array("GT",$new_y);
				$result = $this->where($map)->delete();
//var_dump($map);//exit;				
				//echo "1";exit;
				//var_dump($old_y);
				
			}else{
				$map = array();
				$map['roomId'] = $roomId;
				$map['line'] = array("GT",$new_x);
				$result = $this->where($map)->delete();				
			}
			
		}else if ($old_x = $new_x){
			if ($old_y < $new_y){//新增列
				for ($x=1; $x<=$new_x; $x++) {
					for ($y=$old_y+1; $y<=$new_y; $y++) {
						$data = array();
						$data['studentId'] = 0;
						$data['roomId'] = $roomId;
						$data['line'] = $x;
						$data['col'] = $y;
						$result = $this->data($data)->add();
					}
				}
			}else if ($old_y > $new_y){//删除列

				$map['col'] = array("GT",$new_y);
				$result = $this->where($map)->delete();
			}else{
				
			}		
		
		}

	}

}

