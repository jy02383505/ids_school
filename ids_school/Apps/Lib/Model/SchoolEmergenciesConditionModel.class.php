<?php
/**
 * 模型：紧急事件触发条件设置
 * $emergenciesConditionModel = D("SchoolEmergenciesCondition"); 
 * $model_emc = new Model();
 * $map['TB_Sch_Emergencies.beginTime'] = array("EGT",$starttime);
 * $datas = $model_emc->table("SchoolEmergenciesCondition")->where($map)->select();
 *
*/
class SchoolEmergenciesConditionModel extends Model {
	protected $trueTableName = 'TB_Sch_Emergencies_Condition';
}
