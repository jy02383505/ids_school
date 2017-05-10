<?php
/**
 * 模型
 * 值日表
 *
 */
class SchoolDutyModel extends Model {
	protected $trueTableName = 'TB_Sch_Duty';
	
	/**
	 * 初始化一个班的值日表
	*/
	public function resetDutyTable($banjiId=0){
		//检测banjiId的有效性
		if (!$banjiId){return false;}
		
		$recCount = $this->where("banjiId=".$banjiId)->count();
		if ($recCount <> 7){
			//记录条数不为七，则强制初始化
			//删除旧记录
			$this->where("banjiId=".$banjiId)->delete();
			
			//生成七天的值日表原始记录
			for($i=1;$i<=7;$i++){
				$data = array();
				$data['banjiId'] = $banjiId;
				$data['dutyday'] = $i;
				$result = $this->data($data)->add();
			}
		}
		
		return true;
		

		
	}
	
}