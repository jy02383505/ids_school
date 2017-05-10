<?php
/**
 * 作业模型
 *
 */
class SchoolHomeworkModel extends Model {
	protected $trueTableName = 'TB_Sch_Homeworks';
	
	
	/**
	 * 指定班级，某一天的作业ID集合数组
	 *　返回值：retype==arr时返回数组,retype==str时返回逗号分隔的字符串
	*/
	public function getHwkIds($banjiId=0,$date='',$subjectId=0,$retype='arr'/*或str*/){
		if (!$banjiId || $date=='' || !$subjectId){
			return 0;
		}
		
		$datas = array();
		$out = array();//结果数组
		$map = array();
		$map['banjiId'] = $banjiId;
		$map['subjectId'] = $subjectId;
	//	$map['begintime'] = $date;
		
		//$datas = $this->where( "Convert(varchar(10),begintime,120) = Convert(varchar(10),getDate(),120)" )->select();//指定天的记录 getDate()
		$datas = $this->where( "banjiId = ".$banjiId." and subjectId = ".$subjectId." and Convert(varchar(10),begintime,120) = Convert(varchar(10),'$date',120)" )->field("id")->select();//'$date'必须加单引号，而不是$date
		//echo $this->getlastsql();
		foreach($datas as $k=>$v){
			$out[] = $v['id'];
		}
		if ($retype == "arr"){
			return $out;
		} else {
			return implode(",",$out);
		}
	}
	
	
	
	
}