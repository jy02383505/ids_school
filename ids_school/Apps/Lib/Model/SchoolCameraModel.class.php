<?php
/**
 * 
 * 摄像头模型
 * $cameraModel = D("SchoolCamera");
 */
//
class SchoolCameraModel extends Model {
    protected $trueTableName = 'TB_Sch_Camera';
	
	/**
	 * 根据id获取名称
	 * ID为一个值，或逗号分隔的多个值
	 * 当ID是逗号分隔的多个值时，返回值也是逗号分隔的名称
	*/
	public function getCameraName($cameraId=""){
		if (empty($cameraId) || !$cameraId){return false;}
		
		$map = array();
		$datas = array();
		
		if (strpos($cameraId, ',')){
			$map['Id'] = array("IN",$cameraId);
			$datas = $this->where($map)->field("code")->select();
			$tmp = array();
			foreach ($datas as $k=>$v){
				$tmp[] = $v['code'];
			}
			//var_dump($tmp);
			return implode("，",$tmp);
		}else{
			$map['Id'] = array("EQ",$cameraId);
			$datas = $this->where($map)->field("code")->find();
			return $datas['code'];
		}
		
	}	
	
}