<?php
/**
 * 模型：考试时间
 * $examinationTimeModel = D("SchoolExaminationTime"); 
 
 * $examinationTimeModel = new Model();
 * $map['beginTime'] = array("EGT",$starttime);
 * $datas = $examinationTimeModel->table("TB_Sch_Examination_Time")->where($map)->select();
 *
*/
class SchoolExaminationTimeModel extends Model {
	protected $trueTableName = 'TB_Sch_Examination_Time';
}
