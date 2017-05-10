<?php
/**
 * 内容审核日志
*/

class CheckAction extends CommonAction {
    
	
    /**
     * 
     */
    public function index() {      
	
		//待审核、已审核、已驳回
		$checked = I("request.checked","","trim");
		if (empty($checked)){
			$checked = "ds";	
		}
		$this->assign("checked",$checked);
		
		$treeid = I("request.treeid","","intval");
		if (!$treeid){
			$treeid = 2;
		}
		$this->assign("treeid",$treeid);
		
		//节目、栏目组、栏目、文章、资源库：世界要闻、历史上的今天、幽默笑话、名人名言
		$type = I("request.type","","trim");
		if (empty($type)){
			$type = "programs";	
		}
		$this->assign("type",$type);		
	
		//树形菜单
		$treeData = '[{"id":124,"dir_name":"\u6d4b\u8bd5\u6570\u636e","classid":"f7b4cfdf-c730-dcbf-c3ab-bb591df057bb","parent_classid":"","ROW_NUMBER":"1","name":"\u6d4b\u8bd5\u6570\u636e"},{"id":125,"dir_name":"\u97f3\u9891","classid":"e26494dd-8f2c-b837-03d9-c0a6638b1e81","parent_classid":"","ROW_NUMBER":"2","name":"\u97f3\u9891"},{"id":126,"dir_name":"\u89c6\u9891","classid":"9587ac8c-8e74-77f8-6d26-6865461d503f","parent_classid":"","ROW_NUMBER":"3","name":"\u89c6\u9891"},{"id":127,"dir_name":"\u56fe\u7247","classid":"581d4c8c-e60f-ad03-c5d3-87b039f39722","parent_classid":"","ROW_NUMBER":"4","name":"\u56fe\u7247"}]';

		$programsModel = M('Programs');
		$pdgModel = M('ProgramsDirsGroups');
		$columnModel = D('ProgramsDirs');
		$articleModel = M('ProgramsArticles');
	
		// 加载数据分页类
		import('ORG.Util.Page');
		
		$where = array();
		switch ($checked){
			case "ds":
				$where['_string']  = " checked = 0 or checked is null ";
				break;
			case "ys":
				$where['checked'] = 1;
				break;				
			case "bh":
				$where['checked'] = "-1";
				break;				
				
		}

		switch ($type){
			case "programs":
				$where['bevalid'] = 1;

				$count_ds = $programsModel->where("(checked = 0 or checked is null ) and bevalid = 1")->count();
				$count_ys = $programsModel->where("checked = 1 and bevalid = 1")->count();
				$count_bh = $programsModel->where("checked = -1 and bevalid = 1")->count();
				
				$totals = $programsModel->where($where)->count();
				$Page = new Page($totals, 12);
				$show = $Page->show();
				$this->assign('page', $show);

				$list = $programsModel->field(array('program_classid as tid', 'program_name as name', 'program_classid as classid','program_classid','checked'))->where($where)->limit($Page->firstRow. ',' .$Page->listRows)->select();	
				break;
			case "group":
				$count_ds = $pdgModel->where("checked = 0 or checked is null")->count();
				$count_ys = $pdgModel->where("checked = 1")->count();
				$count_bh = $pdgModel->where("checked = -1")->count();
			
				$totals = $pdgModel->where($where)->count();
				$Page = new Page($totals, 12);
				$show = $Page->show();
				$this->assign('page', $show);
				
				$list = $pdgModel->field(array('dirgroup_classid as tid','dirgroup_classid', 'dirgroup_name as name', 'program_id as classid','program_id as program_classid', 'checked'))->where($where)->limit($Page->firstRow. ',' .$Page->listRows)->select();	
				break;
			case "column":
				$count_ds = $columnModel->where("checked = 0 or checked is null")->count();
				$count_ys = $columnModel->where("checked = 1")->count();
				$count_bh = $columnModel->where("checked = -1")->count();
			
				$totals = $columnModel->where($where)->count();
				$Page = new Page($totals, 12);
				$show = $Page->show();
				$this->assign('page', $show);
				
				
				$list = $columnModel->field(array('classid as tid', 'parent_classid','dir_name as name', 'dirgroup_classid','classid as classid', 'checked'))->where($where)->limit($Page->firstRow. ',' .$Page->listRows)->select();	
				break;
			case "article":
				$count_ds = $articleModel->where("checked = 0 or checked is null")->count();
				$count_ys = $articleModel->where("checked = 1")->count();
				$count_bh = $articleModel->where("checked = -1")->count();
			
				$totals = $articleModel->where($where)->count();
				$Page = new Page($totals, 12);
				$show = $Page->show();
				$this->assign('page', $show);
		//$pdgModel = M('ProgramsDirsGroups');
		//$columnModel = D('ProgramsDirs');				
				$list = $articleModel->field(array('article_classid as tid', 'article_name as name', 'article_classid','program_dir_classid', 'checked'))->where($where)->limit($Page->firstRow. ',' .$Page->listRows)->select();	
				foreach ($list as $k=>$v){
					$column = $columnModel->where("classid = '".$v['program_dir_classid']."'")->field('dirgroup_classid')->find();
					$list[$k]['groupId'] = $column['dirgroup_classid'];
					
					//根据栏目组Id获取节目ID
					if (!empty($column['dirgroup_classid'])){
						$programs = $pdgModel->where("dirgroup_classid='".$column['dirgroup_classid']."'")->field("program_id")->find();
						$list[$k]['program_id'] = $programs['program_id'];
					}
				}
				break;
				
			case "ResLibWorldNews"://资源库：全球要闻
				$model = M('ReslibNews');
				$count_ds = $model->where("checked = 0 or checked is null")->count();
				$count_ys = $model->where("checked = 1")->count();
				$count_bh = $model->where("checked = -1")->count();
			
				$totals = $model->where($where)->count();
				$Page = new Page($totals, 12);
				$show = $Page->show();
				$this->assign('page', $show);
				
				$list = $model->field(array('id','id as tid', 'news_title as name', 'checked'))->where($where)->limit($Page->firstRow. ',' .$Page->listRows)->select();	
				break;
	
			case "ResLibHistoric"://资源库：历史上的今天
				$model = M('ReslibHistoric');
				$count_ds = $model->where("checked = 0 or checked is null")->count();
				$count_ys = $model->where("checked = 1")->count();
				$count_bh = $model->where("checked = -1")->count();
			
				$totals = $model->where($where)->count();
				$Page = new Page($totals, 12);
				$show = $Page->show();
				$this->assign('page', $show);
				
				$list = $model->field(array('id','id as tid', 'event_title as name','event_content', 'checked'))->where($where)->limit($Page->firstRow. ',' .$Page->listRows)->select();	
				break;
				
			case "ResLibFamousQuotes":
				$model = D('FamousQuotes');
				$count_ds = $model->where("checked = 0 or checked is null")->count();
				$count_ys = $model->where("checked = 1")->count();
				$count_bh = $model->where("checked = -1")->count();
			
				$totals = $model->where($where)->count();
				$Page = new Page($totals, 12);
				$show = $Page->show();
				$this->assign('page', $show);
				
				$list = $model->field(array('id','id as tid', 'contents as name','author', 'checked'))->where($where)->limit($Page->firstRow. ',' .$Page->listRows)->select();	
				break;
				
			case "ResLibBaike":
				$model = M('ReslibBaike');
				$count_ds = $model->where("checked = 0 or checked is null")->count();
				$count_ys = $model->where("checked = 1")->count();
				$count_bh = $model->where("checked = -1")->count();
			
				$totals = $model->where($where)->count();
				$Page = new Page($totals, 12);
				$show = $Page->show();
				$this->assign('page', $show);
				
				$list = $model->field(array('id','id as tid', 'title as name','contents', 'checked'))->where($where)->limit($Page->firstRow. ',' .$Page->listRows)->select();	
				break;

			case "ResLibHumorJokes":
				$model = D('HumorJokes');
				$count_ds = $model->where("checked = 0 or checked is null")->count();
				$count_ys = $model->where("checked = 1")->count();
				$count_bh = $model->where("checked = -1")->count();
			
				$totals = $model->where($where)->count();
				$Page = new Page($totals, 12);
				$show = $Page->show();
				$this->assign('page', $show);
				
				$list = $model->field(array('id','id as tid', 'title as name','contents', 'checked'))->where($where)->limit($Page->firstRow. ',' .$Page->listRows)->select();	
				break;
				
			default:
				;
			
		}	
	
		
		


			

//	var_dump($where);
		$this->assign("count_ds",$count_ds);//待审统计
		$this->assign("count_ys",$count_ys);//已审统计
		$this->assign("count_bh",$count_bh);//已驳回统计
	
		$this->assign("treeData",$treeData);
		$this->assign("list",$list);
		$this->display();  
		//$this->display("School/schoolList");
        
    }	
	
    public function logList() {
		$type = I("request.type","","trim");
		$checked = I("request.checked","","trim");
		
		$map = array();
		if (!empty($type)){
			$map['type'] = $type;	
		}
		if (!empty($checked)){
			switch ($checked){
				case "ys":
					$map['dotype'] = 1;
					break;
				case "ds":
					$map['dotype'] = 0;
					break;
				case "bh":
					$map['dotype'] = -1;
					break;	
				default:
					;								
			}	
		}
		//var_dump($map);
		
		$programsModel = M('Programs');			//节目
		$pdgModel = M('ProgramsDirsGroups');	//栏目组
		$columnModel = M('ProgramsDirs');		//栏目
		$articleModel = M('ProgramsArticles');	//文章
		
		$checkLogModel = D("CheckLog");
/*		
		//初始化数组
		$countCheckedProgram = array("ds"=>0,"ys"=>0,"bh"=>0);		//节目：存待审，已审，未审
		$countCheckedDirGroup = array("ds"=>0,"ys"=>0,"bh"=>0);		//栏目组：存待审，已审，未审
		$countCheckedDir = array("ds"=>0,"ys"=>0,"bh"=>0);			//栏目：存待审，已审，未审
		$countCheckedArticle = array("ds"=>0,"ys"=>0,"bh"=>0);		//文章：存待审，已审，未审
		
		//节目统计
		$where['_string']  = 'checked is null or checked = 0';
		$countCheckedProgram['ds'] = $programsModel->where($where)->count();//待审
		$countCheckedProgram['ys'] = $programsModel->where("checked = 1 and bevalid = 1")->count();//已审
		$countCheckedProgram['bh'] = $programsModel->where("checked = -1 and bevalid = 1")->count();//驳回
		
		//栏目组统计
		$where['_string']  = 'checked is null or checked = 0';
		$countCheckedDirGroup['ds'] = $pdgModel->where($where)->count();//待审
		$countCheckedDirGroup['ys'] = $pdgModel->where("checked = 1")->count();//已审
		$countCheckedDirGroup['bh'] = $pdgModel->where("checked = -1")->count();//驳回		

		//栏目统计
		$where['_string']  = 'checked is null or checked = 0';
		$countCheckedDir['ds'] = $columnModel->where($where)->count();//待审
		$countCheckedDir['ys'] = $columnModel->where("checked = 1")->count();//已审
		$countCheckedDir['bh'] = $columnModel->where("checked = -1")->count();//驳回			

		//文章统计
		$where['_string']  = 'checked is null or checked = 0';
		$countCheckedArticle['ds'] = $articleModel->where($where)->count();//待审
		$countCheckedArticle['ys'] = $articleModel->where("checked = 1")->count();//已审
		$countCheckedArticle['bh'] = $articleModel->where("checked = -1")->count();//驳回			

		//var_dump($countCheckedProgram);
		//var_dump($countCheckedDirGroup);
		//var_dump($countCheckedDir);
		//var_dump($countCheckedArticle);
		
		//数量
		$this->assign("countCheckedProgram",$countCheckedProgram);//节目
		$this->assign("countCheckedDirGroup",$countCheckedDirGroup);//栏目组
		$this->assign("countCheckedDir",$countCheckedDir);//栏目
		$this->assign("countCheckedArticle",$countCheckedArticle);//文章
		
		
		//待审核列表
		$datas_no_check_program = $programsModel->where($where)->select();//待审节目
		$datas_no_check_dir_group =  $pdgModel->where($where)->select();//待审栏目组
		$datas_no_check_dir = $columnModel->where($where)->select();//待审栏目
		$datas_no_check_article = $articleModel->where($where)->select();//待审栏目
		
		$this->assign("datas_no_check_program",$datas_no_check_program);//节目
		$this->assign("datas_no_check_dir_group",$datas_no_check_dir_group);//栏目组
		$this->assign("datas_no_check_dir",$datas_no_check_dir);//栏目
		$this->assign("datas_no_check_article",$datas_no_check_article);//文章		
		//var_dump($datas_no_check_program);
		//var_dump($datas_no_check_dir_group);
		//var_dump($datas_no_check_dir);
		//var_dump($datas_no_check_article);   
*/		
		
		//$map = array();
		
        // 加载数据分页类
        import('ORG.Util.Page');		
		
        // 数据分页
        $totals = $checkLogModel->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		//var_dump($map);
		$datas = $checkLogModel->where($map)->field("id,dotype,word,userid,username,type,rid,classid,convert(VARCHAR(24),checktime,120) as checktime")->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
		foreach($datas as $k=>$v){
			switch ($v['type']){
				case "program"://节目
					$datas[$k]['thename'] = $programsModel->where("id=".$v['rid'])->getField('program_name');
					break;
				case "programDirGroup"://栏目组
					$datas[$k]['thename'] = $pdgModel->where("id=".$v['rid'])->getField('dirgroup_name');
					break;
				case "programDir"://栏目
					$datas[$k]['thename'] = $columnModel->where("id=".$v['rid'])->getField('dir_name');
					break;					
				case "programArticle"://节目文章
					$datas[$k]['thename'] = $articleModel->where("id=".$v['rid'])->getField('article_name');
					break;					
				case "resourceWorldNews"://资源：全球要闻
					$newsModel = M('ReslibNews');
					$datas[$k]['thename'] = $newsModel->where("id=".$v['rid'])->getField('news_title');
					break;					
				case "resourceHistoric"://资源：历史上的今天
					$hisModel = M('ReslibHistoric');
					$datas[$k]['thename'] = $hisModel->where("id=".$v['rid'])->getField('event_title');
					break;					
				case "resourceFamousQuotes"://资源：名人名言
					$resModel = D('FamousQuotes');
					$datas[$k]['thename'] = $resModel->where("id=".$v['rid'])->getField('contents');				
					break;	
				case "resourceBaike"://资源：百科
					$baikeModel = M('ReslibBaike');
					$datas[$k]['thename'] = $baikeModel->where("id=".$v['rid'])->getField('title');	
					break;						
				case "resourceHumorJoke"://资源：笑话
					$hjModel = D('HumorJokes');
					$datas[$k]['thename'] = $hjModel->where("id=".$v['rid'])->getField('title');	
					break;						
				default:					
					;							
			}
			
		}
		
		//下拉菜单
		$checkLogModel = D("CheckLog");
		$typeArr = $checkLogModel->getTypeArr();
		$this->assign("types",$typeArr);
		
		$this->assign("datas",$datas);
		$this->assign("type",$type);
		$this->assign("checked",$checked);
		$this->display("logList");     
    }		
	
	/**
	 * 批量驳回
	*/
	public function multiCheck(){
		$type = I("request.type","",trim);
		$artiClassIDs = trim(I('post.aids'), ';');//去掉结尾的;
		$aidsArr = explode(';', $artiClassIDs);//转成数组

		if (empty($artiClassIDs) || empty($aidsArr)) {
			die(json_encode(array('stat'=>0, 'msg'=>'页面数据错误，请刷新页面重试……！')));
		}
	}
	
	/**
	 * 初始化审核
	 * 将相关表的checked设为null，清空审核日志
	*/
	public function reset(){
		$model = M();
		$result = $model->query("update tb_programs set checked=null where id>0");
		$result = $model->query("update tb_programs_dirs_groups set checked=null where id>0");
		$result = $model->query("update tb_programs_dirs set checked=null where id>0");
		$result = $model->query("update tb_programs_articles set checked=null where id>0");
		
		$result = $model->query("update tb_reslib_news set checked=null where id>0");
		$result = $model->query("update tb_reslib_historic set checked=null where id>0");
		$result = $model->query("update tb_reslib_famousQuotes set checked=null where id>0");
		$result = $model->query("update tb_reslib_baike set checked=null where id>0");
		$result = $model->query("update tb_reslib_humorJokes set checked=null where id>0");
		
		$result = $model->query("delete from  TB_CheckLog ");
		
		echo "reset success!";
		
		
		
		
	}
	
}