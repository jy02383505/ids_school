<?php
/**
 * 作业提交模型
 * $homeworkSumbitModel = D('SchoolHomeworkSubmit');
 */
class SchoolHomeworkSubmitModel extends Model {
	protected $trueTableName = 'TB_Sch_Homeworks_Submit';
	
	/**
	 * 某班作业提交原始记录检测
	 * 
	*/
	public function resetHomeworkSubmit($banjiId){
		if (!$banjiId){return false;}
		
		//获取到班级的所有科目
		$btsModel = D("SchoolBanjiSubjectTeacher");
		$datas = $btsModel->getListFromBanjiId($banjiId);
		
		//每科目对应生成一条记录
		foreach($datas as $k=>$v){
			//先查询，如没有则生成
			$result = $this->where("banjiId=".$banjiId ." and subjectId=".$v['subjectId'])->find();	
			if (!$result){
				$data = array();
				$data['banjiId'] = $banjiId;
				$data['subjectId'] = $v['subjectId'];
				$data['quantity'] = 0;
				$result = $this->data($data)->add();	
			}
		}
		
		
	}
	
	
	
}