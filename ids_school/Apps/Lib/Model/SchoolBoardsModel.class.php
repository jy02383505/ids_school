<?php
/**
 * 图片视频滑动框（数据）模型
 * @author Skeam TJ
 *
 */
class SchoolBoardsModel extends Model {
	protected $trueTableName = 'TB_Sch_Boards';
	
	/**
	 * 检测班级板报，确保每班级有唯一的板报相册
	*/
	public function checkBoard($banjiId){
		if (!$banjiId){return false;}
		$count = $this->where("banjiId=".$banjiId)->count();
		if (!$count){
			//新建且只能新建一个班级板报相册
			$data = array();
			
			$data['subject'] = "板报";
			$data['banjiId'] = $banjiId;
			$data['path'] = $banjiId;
			
			$result = $this->data($data)->add();
			if ($result){
				return $result;	//创建成功
			}else{
				return false;	
			}
		}else{
			return true;	
		}
	}
	
	
	
	
	
	
}