<?php
/**
 * 测试
 * @author ZJH
 *
 */
class TestAction extends CommonAction {
	
	/**
	 * 测试一
	 */
	public function index() {
		$test_a = array('a'=>'1','help'=>'这是一个数组，在TestAction.class.php的index()方法中定义');
		//var_dump($test_a);
		echo '<hr>';
		$datas = M('Test')->Select();
		//var_dump($datas);
		
		$this->assign('title_1','这是一些文字');
		$this->assign('datas', $datas);
		$this->display('Test/Index');
	}
	
	/**
	 * 测试一
	 */
	public function hello() {
		$this->display('Test/Hello');
	}

	/**
	 * 测试二
	 */
	public function modal() {
		$this->display('Test/modal');
	}


	/**
	 * 测试列表
	 */
	public function list1() {
		
		$datas = M('Test')->Select();
		//var_dump($datas);
		Load('extend'); //扩展函数库的方法不能直接使用，需要加载或者拷贝到项目函数库中才能使用。 加载扩展函数库，使用： Load('extend'); 
		
		$this->assign('myList', $datas);
		$this->display('Test/List');
	}


	/**
	 * 测试Upload 1
	 */

	public function upload1() {	

		$re=array(
			"stat"=>"1",
			"url"=>"/Uploads/logo.jpg",
			"savePath"=>"logo.jpg",
			"pic"=>"logo.jpg",
			"original"=>"7.jpg",
			"size"=>"53052"
		);
		//var_dump($re);
		$json = json_encode($re);
		echo $json;

//{"stat":"1","url":"\/Uploads\/logo.jpg","savePath":"logo.jpg","pic":"logo.jpg","original":"7.jpg","size":"53052"}
		$this->display();
	}	
	
	
	public function json(){
		$this->display();

	}
	
	public function getjson(){
		//$data = '{"stat":"1","url":"logo.jpg","savePath":"logo.jpg","pic":"logo.jpg","original":"7.jpg","size":"53052"}';
		$re=array(
			"stat"=>"1",
			"url"=>"/Uploads/logo.jpg",
			"savePath"=>"logo.jpg",
			"pic"=>"logo.jpg",
			"original"=>"7.jpg",
			"size"=>"53052",
			"rnd"=>rand(),
		);
		//var_dump($re);
		$json = json_encode($re);
		echo $json;	
	}
	
	//获取全部节目树
	public function getJsonProgramList(){
		/*
		$model = D("ProgramsDir");
		
		$where = array();
		//$where['Pid'] = 0;
        $totals = $model->where($where)->count();

        $originTypes = $model->order('sort asc,pid asc, id asc')->select();
        $datas = array();
        $model->sortedTypes($datas, $originTypes);
        var_dump($datas);
		*/
		
		header("Content-type:text/html;charset=utf-8");
		/*
		$model = D("Programs");
		$datas = $model->order('id asc')->select();
		var_dump($datas);
		*/
		
		$model = D("ProgramsDirs");
		$datas = $model->getZTreeData();
		var_dump($datas);
		
	}
	
	//获取全部节目树
	public function getJsonProgram(){	
		$tree_datas = array();
		//获取节目ID
		$program_classid = trim($_REQUEST['program_classid']);
		if (!$program_classid){
			//节目ID==0
		} else {
			//指定了一个节目id
			$model_program = M("programs");
			$map_program = array();
			$map_program['program_classid'] = $program_classid;
			$map_program['bevalid']=1;
			$datas_program = $model_program->where($map_program)->find();
			if ($datas_program !== false){
				header("Content-type:text/html;charset=utf-8");
				//var_dump($datas_program);
				
				//结果
				$tmp = array();
				$tmp = array('type'=>'节目','id'=>$datas_program['program_classid'],'text'=>$datas_program['program_name'],'state'=>'close');
				
				//指定节目下的栏目组，栏目组只有一级，不必考虑子栏目组
				$model_program_group = M("programs_dirs_groups");
				$map_program_group = array();
				$map_program_group['program_id'] = $datas_program['program_classid'];

				$datas_program_group = $model_program_group->where($map_program_group)->select();
				$tmp_2 = array();
				if ($datas_program_group !== false){
					//var_dump($datas_program_group);
					
					//指定栏目组下的栏目，栏目可能有子栏目
					foreach($datas_program_group as $k=>$v){
						//var_dump($k);
						//echo "<br>-".$v['dirgroup_name'];
						$tmp_2[$k]['type'] = 'lmz';//栏目组
						$tmp_2[$k]['id'] = $v['dirgroup_classid'];
						$tmp_2[$k]['name'] = $v['dirgroup_name'];
						
						//第一级栏目
						$model_program_dir = M("programs_dirs");
						$map_program_dir = array();
						$map_program_dir['dirgroup_classid'] = $v['dirgroup_classid'];
						$map_program_dir['dir_level'] = 0;//第一级栏目
						$datas_program_dir = $model_program_dir->where($map_program_dir)->select();
						
						//var_dump($datas_program_dir);
						//第二级、第三级至第N级栏目
						if ($datas_program_dir !== false){
							//var_dump($datas_program_dir);
							foreach($datas_program_dir as $kk=>$vv){
								//echo "<br>".$vv['dir_name'];
								
								$tmp_a = array();
								$tmp_a['type'] = 'LM';//栏目
								$tmp_a['id'] = $vv['classid'];
								$tmp_a['name'] = $vv['dir_name'];					
								
								
								//var_dump($tmp_2[$k]);
								$tmp_k = array();
								$this->getProgramDir($vv['classid'],$tmp_k);
								$tmp_a[]=$tmp_k;
								array_push($tmp_2[$k],$tmp_a);
							}
							
						}
						//$this->getProgramDir($v['dirgroup_classid'],&$v);
					}
					array_push($tmp,$tmp_2);
				}
			}
		}
		echo '<hr>';
		$tree_datas = $tmp;
		//结果
		var_dump($tree_datas);
		
//		$test = array();
//		$this->getProgramDir('43257902-5b4a-6ae6-f051-ffbfb698f58c',$test);
//		var_dump($test);
		
	}
	
	
	public function getProgramDir($parent_classid=''/*父classid*/,&$out/*结果数组*/){
		if ($parent_classid){
			$model = M("programs_dirs");
			$datas = array();
			$map = array();
			$map['parent_classid'] = $parent_classid;
			$datas = $model->where($map)->select();//栏目组下的全部栏目，未排序
			if ($datas !== false){
				//有子栏目
				foreach($datas as $k=>$v){
					$tmp_dir = array();
					$tmp_dir['type'] = 'LM';//栏目
					$tmp_dir['id'] = $v['classid'];
					$tmp_dir['name'] = $v['dir_name'];					
					
					//var_dump($tmp_dir);
					//判断子栏目是否有下级栏目
					$son = $model->where("parent_classid='".$v['classid']."'")->select();
					if ($son !== false){
						foreach($son as $kk=>$vv){
							$this->getProgramDir($vv['classid'],$vv);
						}
					}else{
						return;	
					}
				
					$tmp_dir[] = $son;
					array_push($out,$tmp_dir);//
				}
				
			}
			//array_push($out,array("abc"));
		}
	}
	
}