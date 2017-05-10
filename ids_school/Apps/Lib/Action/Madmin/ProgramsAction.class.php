<?php
/**
 * 节目管理控制器
 * @author Skeam Tj
 *
 */
class ProgramsAction extends CommonAction {
	
	/**
	 * 节目列表
	 */
	public function index() {
		$programsModel = M('Programs');
		$tpls = M('Tpls')->select();
		$where = array('bevalid'=>1);
		$program_name = trim(I('get.progname'));
		if (!empty($program_name)) {
			$where['program_name'] = array('like', '%' . utf82gbk($program_name) . '%');
		}
		
		$checked = trim(I('get.checked'));
		$this->assign("checked",$checked);

		//过滤：已审核/待审核/已驳回
		if (!empty($checked)){
			switch ($checked){
				case "ys"://已审
					$where['checked'] = 1;
					break;
				case "ds"://待审
					$where['checked'] = 0;
					break;
				case "bh"://驳回
					$where['checked'] = -1;
					break;
				default:
					;//全部
			}
		}
		
		// 加载数据分页类
		import('ORG.Util.Page');
		/*
		// 节目权限控制 zjh注释掉本段，因为会导致无权限
		$programNodeClassID = 'a69422aa-6077-6385-4ffd-1676c591a4cc';
		if (!$_SESSION[C('ADMIN_AUTH_KEY')]) {
			$progAccess = M('Access')->where(array('role_id'=>$_SESSION['role'], 'node_id'=>$programNodeClassID))->count();
			if ($progAccess) {
				$accessCon = M('AccessCon')->where(array('role_id'=>$_SESSION['role'], 'con_classid'=>$programNodeClassID, 'con_type'=>'x86'))->getField('con_item_classid', true);
				if (!empty($accessCon)) {
					//$where['program_classid'] = array('in', $accessCon);
				} else {
					//$where['program_classid'] = '00000000-0000-0000-0000-000000000000';
				    $this->error('非法操作！');
				}
		
			} else {
				//$where['program_classid'] = '00000000-0000-0000-0000-000000000000';
			    $this->error('非法操作！');
			}
		}
		*/
		// 数据分页
		$totals = $programsModel->where($where)->count();
		$Page = new Page($totals, 12);
		$show = $Page->show();
		$this->assign('page', $show);
		
		$programs = $programsModel->field(array('id', 'program_name', 'program_note', 'program_classid', 'CONVERT(varchar, modifytime, 120 ) as modifytime', 'bevalid','checked'))->where($where)->limit($Page->firstRow. ',' .$Page->listRows)->select();
		foreach ($programs as &$prog) {
			$prog['program_name'] = gbk2utf8($prog['program_name']);
			$prog['program_note'] = gbk2utf8($prog['program_note']);
			foreach($tpls as $tpl){
				if($tpl[binding_program_classid] == $prog[program_classid]){
					$prog[tpls][] = $tpl[tplname];
				}
			}
		}
		
		$this->assign('programs', $programs);
		$this->display();
	}
	
	/**
	 * 新建节目
	 */
	public function add() {
		if (IS_POST) {
			$this->saveProgramData();
		} else {
			$this->display('edit');
		}
	}
	
	/**
	 * 编辑节目
	 */
	public function edit() {
		if (IS_POST) {
			$this->saveProgramData();
		} else {
			$programClassID = trim(I('get.id'));
			
			if (empty($programClassID)) {
				$this->error('非法操作！');
			}

			$programsModel = M('Programs');
			$program = $programsModel->where(array('program_classid'=>$programClassID))->find();
			if (!$program) {
				$this->error('非法操作！');
			}
			$program['program_name'] = gbk2utf8($program['program_name']);
			$program['program_note'] = gbk2utf8($program['program_note']);

			$this->assign('program', $program);
			$this->display();
		}
	}
	
	/**
	 * 保存节目
	 */
	public function saveProgramData() {
		
		$program_classid = trim(I('post.program_classid'));
		$program_name = str_replace(array(';', '；'), '', trim(I('post.program_name')));
		$program_note = trim(I('post.program_note'));
		
		if (empty($program_name)) {
			//$this->error('节目名称不能为空！');
			die(json_encode(array('stat'=>0, 'msg'=>'节目名称不能为空！')));
		}
		
		$programsModel = M('Programs');
		
		// 检测节目名称不能重复
		$where = array('program_name'=>utf82gbk($program_name));
		if (!empty($program_classid)) {
			$where['program_classid'] = array('neq', $program_classid);
		}
		if ($programsModel->where($where)->count() > 0) {
			//$this->error('已存在名称为"' . $program_name . '"的节目，不可创建同名的节目！');
			die(json_encode(array('stat'=>0, 'msg'=>'已存在名称为"' . $program_name . '"的节目，不可创建同名的节目！')));
		}
		
		$data['program_name'] = utf82gbk($program_name);
		$data['program_note'] = utf82gbk($program_note);
		
		$pcid = '';
		if (empty($program_classid)) {
			
			$data['program_classid'] = $pcid = generateUniqueID();
			$data['bevalid'] = '1';
			$data['creattime'] = $data['modifytime'] = date('Y-m-d H:i:s');
			$re = $programsModel->add($data);
		} else {
			$pcid = $program_classid;
			$data['modifytime'] = date('Y-m-d H:i:s');
			$re = $programsModel->where(array('program_classid'=>$program_classid))->save($data);
		}
		
		if ($re !== false) {
			//$this->success('操作成功！', U('Programs/index'));
			die(json_encode(array('stat'=>1, 'data'=>$programsModel->field(array('id', 'program_name', 'program_classid'))->where(array('program_classid'=>$pcid))->find())));
		} else {
			//$this->error('操作失败！[原因]：' . $programsModel->getError());
			die(json_encode(array('stat'=>0, 'msg'=>$programsModel->getError())));
		}
	}
	
	/**
	 * 将节目放入回收站
	 */
	public function recycle() {
		//$programClassID = trim(I('get.id'));
		$programClassID = trim(I('post.id'));
			
		if (empty($programClassID)) {
			//$this->error('非法操作！');
			die(json_encode(array('stat'=>0, 'msg'=>'非法操作！')));
		}
		
		$programsModel = M('Programs');
		
		$program = $programsModel->where(array('program_classid'=>$programClassID))->find();
		if (!$program) {
			//$this->error('非法操作！');
			die(json_encode(array('stat'=>0, 'msg'=>'非法操作！')));
		}
		
		$re = $programsModel->where(array('program_classid'=>$programClassID))->setField('bevalid', '0');
		if ($re !== false) {
			//$this->success('操作成功！', U('Programs/index'));
			die(json_encode(array('stat'=>1)));
		} else {
			//$this->error('操作失败！[原因]：' . $programsModel->getError());
			die(json_encode(array('stat'=>0, 'msg'=>$programsModel->getError())));
		}
	}
	
	
	
	//批量审核节目
	public function multiCheckPrograms() {
		$artiClassIDs = trim(I('request.aids'), ';');//去掉结尾的;
		$aidsArr = explode(';', $artiClassIDs);//转成数组
		$checkedType = trim(I('request.checkedType'));
		
		$debug ='';
		
		if (empty($artiClassIDs) ) {//|| empty($aidsArr)
			die(json_encode(array('stat'=>0, 'msg'=>'页面数据错误，请刷新页面重试……！','debug'=>$debug)));
		}
	
		$data = array();
		$pdgModel = M('ProgramsDirsGroups');
		$columnModel = M('ProgramsDirs');
		$articleModel = M('ProgramsArticles');
		
		
		
		//过滤：已审核/待审核/已驳回
		if (!empty($checkedType)){
			switch ($checkedType){
				case "ys"://已审
					$checkMessageValue = '审核通过';
					$dotype = 1;
					break;
				case "ds"://待审
					$checkMessageValue = '取消审核';
					$dotype = 0;
					break;
				case "bh"://驳回
					$checkMessageValue = substr(trim(I('request.checkMessage')),0,255);//附加消息
					$dotype = -1;
					break;
				default:
					$dotype = 0;//全部
			}
			//die(json_encode(array('stat'=>1, 'msg'=>'操作成功！','debug'=>$debug)));
		}else{
			die(json_encode(array('stat'=>0, 'msg'=>'ys操作失败，请重试……！','debug'=>$debug)));
		}
		
		$data['checked'] = $dotype;
		
		$programsModel = M('Programs');
		$succCounts = $failCounts = 0;
		foreach ($aidsArr as $aid) {
			$program = $programsModel->where(array('program_classid'=>$aid))->find();
			if (!$program) {
				$failCounts++;
				continue;
			}else{	//节目存在时
				$rid = $program['id'];//写日志用
				
				//设为待审或驳回，不需考虑下级内容的状态，直接改字段值
				if ($checkedType == "ds" || $checkedType=="bh"){ 
					$re = $programsModel->where(array('program_classid'=>$aid))->setField($data);
					if ($re !== false) {
						$succCounts++;
						
						//写审核日志 START
						$checkLogModel = D("CheckLog");
						//$checkMessageValue = "审核通过";
						$checkLogModel->writeNewLog($dotype,$checkMessageValue,"program",$rid,$aid);
						//写审核日志 END
						
					} else {
						$failCounts++;
					}
					continue;				
				}

				
				//下面是审核节目，需要判断下级的内容的状态（检测节目下是否有未通过审核的项目：包括栏目组、栏目、文章）
				
				$isSetField = 1;//是否更新checked
				
				//当前节目的 栏目组
				$map = array();
				$map['_string']  = "program_id = '".$aid."' and checked<>1";
				$not_pass_pdg = $pdgModel->where($map)->count();
				if ($not_pass_pdg){
					//$failCounts++;
					$isSetField = 0;
					$debug = "<br>有栏目组未通过审核：";
					
					$tmp = array();
					$datas_tmp = $pdgModel->where($map)->select();
					foreach($datas_tmp as $k1=>$v1){
						$tmp[] = $v1['dirgroup_name'];
					}
					$tmp_1 = "<br>".implode("，",$tmp);
					$debug .= $tmp_1;
					
				//	continue;//当前节目如判断有未审核通过的项目，直接跳到下一次循环
				}
				
				//当前节目的 栏目组
				$groups = $pdgModel->where(array('program_id'=>$aid))->select();//本次循环到的节目下的栏目组
				foreach($groups as $k=>$v){
					$map = array();
					$map['_string']  = "dirgroup_classid = '".$v['dirgroup_classid']."' and ( checked = 0 or checked = -1 or checked is null )";
					$not_pass_column = $columnModel->where($map)->count();//栏目
					if ($not_pass_column){
						//$failCounts++;
						$isSetField = 0;
						
						//返回的提示 START
						$debug .= " 有栏目未通过审核";
						
						$tmp = array();
						$datas_tmp = $columnModel->where($map)->select();
						
						foreach($datas_tmp as $k2=>$v2){
							$tmp[] = $v2['dir_name'];
						}
						
						$tmp_2 = "：".implode("，",$tmp);
						$debug .= $tmp_2;
						//返回的提示 END				
						
						//break;//有未通过的栏目				
					}else{
						//$debug .= '????????';	
					}
					
					//检查文章
					$columns = $columnModel->where(array('dirgroup_classid'=>$v['dirgroup_classid']))->select();//遍历此栏目组下的所有栏目
					foreach($columns as $kk=>$vv){
						$map_art = array();
						$map_art['_string']  = "program_dir_classid = '".$vv['classid']."' and ( checked = 0 or checked = -1 or checked is null )";
						$not_pass_article = $articleModel->where($map_art)->count();//此栏目下未通过审核的文章数量
						if ($not_pass_article){
							//存在未通过的文章
							//$failCounts++;
							$isSetField = 0;
							
							$debug .= "<br>未通过审核的文章数：".$not_pass_article;
						//	break;				
						}
					}
				}
			}
			
			//设为未审核或驳回直接修改字段，设为审核通过，需要条件判断
			if ($checkedType == 'ys' ){
				if ($isSetField){
					//$data = array('checked'=>'1');
					$re = $programsModel->where(array('program_classid'=>$aid))->setField($data);//设为通过
					if ($re !== false) {
						$succCounts++;
						
						//写审核日志 START
						$checkLogModel = D("CheckLog");
						//$checkMessageValue = "审核通过";
						$checkLogModel->writeNewLog($dotype,$checkMessageValue,"program",$rid,$aid);
						//写审核日志 END
						
					} else {
						$failCounts++;
					}
				}else{
					$failCounts++;	
				}				
			}
		}
		
		if ($failCounts == count($aidsArr)) {
			die(json_encode(array('stat'=>0, 'msg'=>'操作失败！','debug'=>$debug)));
		} else {
			//只要有一个成功的，就提示成功
			die(json_encode(array('stat'=>1, 'msg'=>'操作成功！','debug'=>$debug)));
		}
	}
	
	/**
	 * 根据节目ID管理栏目组
	 */
	public function groups() {
		$programClassID = trim(I('get.id'));
		$this->tpls = M('Tpls')->where(array('binding_program_classid'=>$programClassID))->getField('tplname', true);
			
		if (empty($programClassID) || !$this->checkProgramAccess($programClassID)) {
			$this->error('非法操作！');
		}
		
		$programsModel = M('Programs');
		
		$program = $programsModel->where(array('program_classid'=>$programClassID))->find();
		if (!$program) {
			$this->error('非法操作！');
		}
		$program['program_name'] = gbk2utf8($program['program_name']);
		$this->assign('program', $program);
		
		$pdgModel = M('ProgramsDirsGroups');
		$dirgroup_name = trim(I('get.grpname'));
		$where = array('program_id'=>$programClassID);
		if (!empty($dirgroup_name)) {
			$where['dirgroup_name'] = array('like', '%' . utf82gbk($dirgroup_name) . '%');
		}
		
		$checked = trim(I('get.checked'));
		$this->assign("checked",$checked);

		//过滤：已审核/待审核/已驳回
		if (!empty($checked)){
			switch ($checked){
				case "ys"://已审
					$where['checked'] = 1;
					break;
				case "ds"://待审
					$where['checked'] = 0;
					break;
				case "bh"://驳回
					$where['checked'] = -1;
					break;
				default:
					;//全部
			}
		}
		
		// 加载数据分页类
		import('ORG.Util.Page');
		
		// 数据分页
		$totals = $pdgModel->where($where)->count();
		$Page = new Page($totals, 12);
		$show = $Page->show();
		$this->assign('page', $show);
		
		$groups = $pdgModel->field(array('id', 'dirgroup_name', 'dirgroup_note', 'dirgroup_classid','checked'))->where($where)->limit($Page->firstRow. ',' .$Page->listRows)->select();
		foreach ($groups as &$grp) {
			$grp['dirgroup_name'] = gbk2utf8($grp['dirgroup_name']);
			$grp['dirgroup_note'] = gbk2utf8($grp['dirgroup_note']);
		}
		
		$this->assign('groups', $groups);
		$this->display();
	}
	
	/**
	 * 添加栏目组
	 */
	public function addGroup() {
		if (IS_POST) {
			$this->saveGroupData();
		} else {
			$programClassID = trim(I('get.pid'));
			if (empty($programClassID)) {
				$this->error('非法操作！');
			}
			
			$program = $this->getProgramInfo($programClassID);
			if (!$program) {
				$this->error('非法操作！');
			}
			$this->assign('program', $program);
			$this->display('editGroup');
		}
	}
	
	/**
	 * 编辑栏目组
	 */
	public function editGroup() {
		if (IS_POST) {
			$this->saveGroupData();
		} else {
			$programClassID = trim(I('get.pid'));
			$groupClassID = trim(I('get.id'));
			
			if (empty($programClassID)) {
				$this->error('非法操作！');
			}
				
			$program = $this->getProgramInfo($programClassID);
			if (!$program) {
				$this->error('非法操作！');
			}
			$this->assign('program', $program);
				
			if (empty($groupClassID)) {
				$this->error('非法操作！');
			}
		
			$pdgModel = M('ProgramsDirsGroups');
			$group = $pdgModel->where(array('dirgroup_classid'=>$groupClassID))->find();
			if (!$group) {
				$this->error('非法操作！');
			}
			$group['dirgroup_name'] = gbk2utf8($group['dirgroup_name']);
			$group['dirgroup_note'] = gbk2utf8($group['dirgroup_note']);
		
			$this->assign('group', $group);
			$this->display();
		}
	}
	
	/**
	 * 保存栏目组
	 */
	public function saveGroupData() {
		
		$program_classid = trim(I('post.program_classid'));
		$dirgroup_classid = trim(I('post.dirgroup_classid'));
		$dirgroup_name = str_replace(array(';', '；'), '', trim(I('post.dirgroup_name')));
		$dirgroup_note = trim(I('post.dirgroup_note'));
	
		if (!$program_classid) {
			//$this->error('非法操作！');
			die(json_encode(array('stat'=>0, 'msg'=>'非法操作！')));
		}
		
		$program = $this->getProgramInfo($program_classid);
		if (!$program) {
			//$this->error('非法操作！');
			die(json_encode(array('stat'=>0, 'msg'=>'非法操作！')));
		}
		
		if (empty($dirgroup_name)) {
			//$this->error('栏目组名称不能为空！');
			die(json_encode(array('stat'=>0, 'msg'=>'栏目组名称不能为空！')));
		}
	
		$pdgModel = M('ProgramsDirsGroups');
	
		// 检测节目名称不能重复
		$where = array('dirgroup_name'=>utf82gbk($dirgroup_name), 'program_id'=>$program_classid);
		if (!empty($dirgroup_classid)) {
			$where['dirgroup_classid'] = array('neq', $dirgroup_classid);
		}
		if ($pdgModel->where($where)->count() > 0) {
			//$this->error('已存在名称为"' . $dirgroup_name . '"的栏目组，不可创建同名的栏目组！');
			die(json_encode(array('stat'=>0, 'msg'=>'已存在名称为"' . $dirgroup_name . '"的栏目组，不可创建同名的栏目组！')));
		}
	
		$data['dirgroup_name'] = utf82gbk($dirgroup_name);
		$data['dirgroup_note'] = utf82gbk($dirgroup_note);
		$dgid = '';
		if (empty($dirgroup_classid)) {
			
			$data['program_id'] = $program['program_classid'];
			$data['dirgroup_classid'] = $dgid = generateUniqueID();
			$re = $pdgModel->add($data);
		} else {
			$dgid = $dirgroup_classid;
			$re = $pdgModel->where(array('dirgroup_classid'=>$dirgroup_classid))->save($data);
		}
	
		if ($re !== false) {
			//$this->success('操作成功！', U('Programs/groups', array('id'=>$program['program_classid'])));
			$ajaxData = $pdgModel->where(array('dirgroup_classid'=>$dgid))->find();
			die(json_encode(array('stat'=>1, 'data'=>$ajaxData)));
		} else {
			//$this->error('操作失败！[原因]：' . $pdgModel->getError());
			die(json_encode(array('stat'=>0, 'msg'=>$pdgModel->getError())));
		}
	}
	
	/**
	 * 删除栏目组
	 */
	public function delGroup() {
		//$programClassID = trim(I('get.pid'));
		//$groupClassID = trim(I('get.id'));
		$programClassID = trim(I('post.pid'));
		$groupClassID = trim(I('post.id'));
			
		if (empty($programClassID)) {
			//$this->error('非法操作！');
			die(json_encode(array('stat'=>0, 'msg'=>'非法操作！')));
		}
		
		$program = $this->getProgramInfo($programClassID);
		if (!$program) {
			//$this->error('非法操作！');
			die(json_encode(array('stat'=>0, 'msg'=>'非法操作！')));
		}
		
		if (empty($groupClassID)) {
			//$this->error('非法操作！');
			die(json_encode(array('stat'=>0, 'msg'=>'非法操作！')));
		}
		
		$pdgModel = M('ProgramsDirsGroups');
		$group = $pdgModel->where(array('dirgroup_classid'=>$groupClassID))->find();
		if (!$group) {
			//$this->error('非法操作！');
			die(json_encode(array('stat'=>0, 'msg'=>'非法操作！')));
		}
		
		// 非空的栏目组不允许直接删除
		if (M('ProgramsDirs')->where(array('dirgroup_classid'=>$groupClassID))->count() > 0) {
			//$this->error('操作失败！[原因]：该栏目组存在关联的栏目信息，不允许直接删除！');
			die(json_encode(array('stat'=>0, 'msg'=>'操作失败！[原因]：该栏目组存在关联的栏目信息，不允许直接删除！')));
		}
		
		$re = $pdgModel->where(array('program_id'=>$programClassID, 'dirgroup_classid'=>$groupClassID))->delete();
		if ($re !== false) {
			//$this->success('操作成功！');
			die(json_encode(array('stat'=>1)));
		} else {
			//$this->error('操作失败！[原因]：' . $pdgModel->getError());
			die(json_encode(array('stat'=>0, 'msg'=>$pdgModel->getError())));
		}
	}
	
	//批量审核栏目组
	public function multiCheckGroup() {
		$artiClassIDs = trim(I('post.aids'), ';');//去掉结尾的;
		$aidsArr = explode(';', $artiClassIDs);//转成数组

		if (empty($artiClassIDs) || empty($aidsArr)) {
			die(json_encode(array('stat'=>0, 'msg'=>'页面数据错误，请刷新页面重试……！')));
		}

		$data = array();
		$debug = '';
		$checkedType = trim(I('post.checkedType'));
		//过滤：已审核/待审核/已驳回
		if (!empty($checkedType)){
			switch ($checkedType){
				case "ys"://已审
					$checkMessageValue = '审核通过';
					$dotype = 1;
					//检测下级栏目及文章是否有未审核或驳回
					break;
				case "ds"://待审
					$checkMessageValue = '取消审核';
					$dotype = 0;
					break;
				case "bh"://驳回
					$checkMessageValue = substr(trim(I('post.checkMessage')),0,255);//附加消息
					$dotype = -1;
					break;
				default:
					$dotype = 0;//全部
			}
		}else{
			die(json_encode(array('stat'=>0, 'msg'=>'操作失败，请重试……！')));
		}
		
		$data['checked'] = $dotype;
		
		$pdgModel = M('ProgramsDirsGroups');
		$columnModel = M('ProgramsDirs');
		$articleModel = M('ProgramsArticles');
		
		$succCounts = $failCounts = 0;
		foreach ($aidsArr as $aid) {//遍历指定的栏目组
			$dirGroup = $pdgModel->where(array('dirgroup_classid'=>$aid))->find();
			if (!$dirGroup) {
				$failCounts++;
				continue;
			}else{
				$rid = $dirGroup['id'];//写日志用
				$isSetField = 1;//是否更新checked				
				
				//判断栏目组以下的内容（栏目、文章）是否存在未审核通过的
				$map = array();
				$map['_string']  = "dirgroup_classid = '".$aid."' and ( checked = 0 or checked = -1 or checked is null )";
				$not_pass_column = $columnModel->where($map)->count();//栏目
				if ($not_pass_column){
					$isSetField = 0;
					
					//返回的提示 START
					$debug .= " 有栏目未通过审核";
					
					$tmp = array();
					$datas_tmp = $columnModel->where($map)->select();
					
					foreach($datas_tmp as $k2=>$v2){
						$tmp[] = $v2['dir_name'];
					}
					
					$tmp_2 = "：".implode("，",$tmp);
					$debug .= $tmp_2;
					//返回的提示 END				
					
					//break;//有未通过的栏目				
				}else{
					//$debug .= '????????';	
				}
				
				//检查文章
				$columns = $columnModel->where(array('dirgroup_classid'=>$aid))->select();//遍历此栏目组下的所有栏目
				foreach($columns as $kk=>$vv){
					$map_art = array();
					$map_art['_string']  = "program_dir_classid = '".$vv['classid']."' and ( checked = 0 or checked = -1 or checked is null )";
					$not_pass_article = $articleModel->where($map_art)->count();//此栏目下未通过审核的文章数量
					if ($not_pass_article){
						//存在未通过的文章
						//$failCounts++;
						$isSetField = 0;
						
						$debug .= "<br>未通过审核的文章数：".$not_pass_article;
					//	break;				
					}
				}
				
				
			}
			//设为未审核或驳回直接修改字段，设为审核通过，需要条件判断
			if ($checkedType == 'ys' ){
				if ($isSetField){
					//$data = array('checked'=>'1');
					$re = $pdgModel->where(array('dirgroup_classid'=>$aid))->setField($data);;
					if ($re !== false) {
						$succCounts++;
						
						//写审核日志 START
						$checkLogModel = D("CheckLog");
						//$checkMessageValue = "审核通过";
						$checkLogModel->writeNewLog($dotype,$checkMessageValue,"programDirGroup",$rid,$aid);
						//写审核日志 END
						
					} else {
						$failCounts++;
					}
				}
			}else{
				//把这段往前移，因为不再检测下级内容的审核情况
				//设为待审或驳回，不需考虑下级内容的状态，直接改字段值
			//	if ($checkedType == "ds" || $checkedType=="bh"){ 
					$re = $pdgModel->where(array('dirgroup_classid'=>$aid))->setField($data);
					if ($re !== false) {
						$succCounts++;
						
						//写审核日志 START
						$checkLogModel = D("CheckLog");
						//$checkMessageValue = "审核通过";
						$checkLogModel->writeNewLog($dotype,$checkMessageValue,"programDirGroup",$rid,$aid);
						//写审核日志 END
						
					} else {
						$failCounts++;
					}
					continue;				
			//	}
			}
		}
		
		if ($failCounts == count($aidsArr)) {
			die(json_encode(array('stat'=>0, 'msg'=>'操作失败！','debug'=>$debug)));
		} else {
			die(json_encode(array('stat'=>1,'msg'=>'操作成功！','debug'=>$debug)));
		}
	}	
	
	
	
	/**
	 * 根据节目ID获取节目信息
	 * @param string $programClassID
	 * @return boolean|array
	 */
	private function getProgramInfo($programClassID) {
		if (trim($programClassID) == '') {
			return false;
		}
		
		$programInfo = M('Programs')->where(array('program_classid'=>$programClassID))->find();
		
		if ($programInfo) {
			$programInfo['program_name'] = gbk2utf8($programInfo['program_name']);
			$programInfo['program_note'] = gbk2utf8($programInfo['program_note']);
			
			return $programInfo;
		} else {
			return false;
		}
		
	}
	
	/**
	 * 根据栏目组ID获取栏目组信息
	 * @param string $groupClassID
	 * @return boolean|array
	 */
	private function getGroupInfo($groupClassID) {
		if (trim($groupClassID) == '') {
			return false;
		}
		
		$groupInfo = M('ProgramsDirsGroups')->where(array('dirgroup_classid'=>$groupClassID))->find();
		
		if ($groupInfo) {
			$groupInfo['dirgroup_name'] = gbk2utf8($groupInfo['dirgroup_name']);
			$groupInfo['dirgroup_note'] = gbk2utf8($groupInfo['dirgroup_note']);
			
			return $groupInfo;
		} else {
			return false;
		}
		
	}
	
	/**
	 * 根据栏目ID获取栏目信息
	 * @param string $columnClassID
	 * @return boolean|array
	 */
	private function getColumnInfo($columnClassID) {
		if (trim($columnClassID) == '') {
			return false;
		}
		
		$columnInfo = M('ProgramsDirs')->where(array('classid'=>$columnClassID))->find();
		
		if ($columnInfo) {
			$columnInfo['dir_name'] = gbk2utf8($columnInfo['dir_name']);
			$columnInfo['remark'] = gbk2utf8($columnInfo['remark']);
			$columnInfo['dir_ico'] = trim($columnInfo['dir_ico']);
			$columnInfo['dir_type'] = trim($columnInfo['dir_type']);
			$columnInfo['dir_map'] = trim($columnInfo['dir_map']);
			$columnInfo['dir_ico_path'] = str_replace('\\', '/', D('MediaLib')->where(array('resourceid'=>$columnInfo['dir_ico']))->getField('filepath'));
			$columnInfo['dir_map_path'] = str_replace('\\', '/', D('MediaLib')->where(array('resourceid'=>$columnInfo['dir_map']))->getField('filepath'));
			return $columnInfo;
		} else {
			return false;
		}
		
	}
	
	/**
	 * 栏目列表
	 */
	public function columnLists() {
		
		$programClassID = trim(I('get.pid'));
		$groupClassID = trim(I('get.id'));
		$columnClassID = trim(I('get.cid'));
			
		if (empty($programClassID)) {
			$this->error('非法操作！');
		}
		
		$program = $this->getProgramInfo($programClassID);
		if (!$program) {
			$this->error('非法操作！');
		}
		$this->assign('program', $program);
		
		if (empty($groupClassID)) {
			$this->error('非法操作！');
		}
		
		$pdgModel = M('ProgramsDirsGroups');
		$group = $pdgModel->where(array('dirgroup_classid'=>$groupClassID))->find();
		if (!$group) {
			$this->error('非法操作！');
		}
		$group['dirgroup_name'] = gbk2utf8($group['dirgroup_name']);
		$this->assign('group', $group);
	
		$columnModel = D('ProgramsDirs');
		$where = array('status'=>1, 'dirgroup_classid'=>$groupClassID);
		$column_name = trim(I('get.column_gname'));
		if (!empty($column_name)) {
			$where['dir_name'] = array('like', '%' . utf82gbk($column_name) . '%');
		}

		$columns = $columnModel->field(array('id', 'classid', 'dir_name', 'dir_ico', 'parent_classid', 'dir_level', 'status', 'dirgroup_classid', 'dir_type'))->where($where)->select();
		foreach ($columns as &$col) {
			$col['dir_name'] = gbk2utf8($col['dir_name']);
			$col['dir_type'] = trim($col['dir_type']);
			$col['dir_ico'] = trim($col['dir_ico']);
			$col['dir_ico_path'] = str_replace('\\', '/', D('MediaLib')->where(array('resourceid'=>$col['dir_ico']))->getField('filepath'));
			if (empty($col['parent_classid']) || trim($col['parent_classid']) == '' || !$col['parent_classid']) {
				$col['parent_classid'] = 'root-0000-000-000';
			}
		}
		
		$sortedColumns = array();
		$columnModel->sortNodes($sortedColumns, $columns, empty($columnClassID) ? 'root-0000-000-000' : $columnClassID);
		
		$this->assign('columns', $sortedColumns);
		$this->display();
	}
	
	/**
	 * 栏目列表
	*/
	public function columns() {
		
		$programClassID = trim(I('get.pid'));
		$this->tpls = M('Tpls')->where(array('binding_program_classid'=>$programClassID))->getField('tplname', true);
		$groupClassID = trim(I('get.id'));
		$columnClassID = trim(I('get.cid'));
			
		if (empty($programClassID) || !$this->checkProgramAccess($programClassID)) {
			$this->error('非法操作！');
		}
		
		$program = $this->getProgramInfo($programClassID);
		if (!$program) {
			$this->error('非法操作！');
		}
		$this->assign('program', $program);
		
		if (empty($groupClassID)) {
			$this->error('非法操作！');
		}
		
		$pdgModel = M('ProgramsDirsGroups');
		$group = $pdgModel->where(array('dirgroup_classid'=>$groupClassID))->find();
		if (!$group) {
			$this->error('非法操作！');
		}
		$group['dirgroup_name'] = gbk2utf8($group['dirgroup_name']);
		$this->assign('group', $group);
	
		$columnModel = D('ProgramsDirs');
		$where = array('status'=>1, 'dirgroup_classid'=>$groupClassID);
		
		if (!empty($columnClassID) && $columnModel->where(array('classid'=>$columnClassID))->find()) {
			$where['parent_classid'] = $columnClassID;
		} else {
			$where['dir_level'] = 0;
		}

		$columns = $columnModel->field(array('id', 'classid', 'dir_name', 'dir_ico', 'parent_classid', 'dir_level', 'status', 'dirgroup_classid', 'dir_type','checked'))->where($where)->select();
		foreach ($columns as &$col) {
			$col['dir_type'] = trim($col['dir_type']);
			$col['dir_ico'] = trim($col['dir_ico']);
			$col['dir_ico_path'] = str_replace('\\', '/', D('MediaLib')->where(array('resourceid'=>$col['dir_ico']))->getField('filepath'));
			$col['has_children'] = $columnModel->where(array('parent_classid'=>$col['classid']))->count() > 0 ? 1 : 0;
		}
		
		$this->assign('columns', $columns);
		$this->display();
	}
	
	/**
	 * 新建栏目
	 */
	public function addColumn() {
		if (IS_POST) {
			$this->saveColumnData();
		} else {
			$groupClassID = trim(I('get.gid'));
			if (empty($groupClassID)) {
				$this->error('非法操作！');
			}
				
			$group = $this->getGroupInfo($groupClassID);
			if (!$group) {
				$this->error('非法操作！');
			}
			$this->assign('group', $group);
			$this->assign('program', $this->getProgramInfo($group['program_id']));
			
			$columnModel = D('ProgramsDirs');
			$where = array('status'=>1, 'dirgroup_classid'=>$groupClassID);
			
			$columns = $columnModel->field(array('id', 'classid', 'dir_name', 'dir_ico', 'parent_classid', 'dir_level', 'status', 'dirgroup_classid'))->where($where)->select();
			foreach ($columns as &$col) {
				$col['dir_name'] = gbk2utf8($col['dir_name']);
				if (empty($col['parent_classid']) || trim($col['parent_classid']) == '' || !$col['parent_classid']) {
					$col['parent_classid'] = 'root-0000-000-000';
				}
			}
			
			$sortedColumns = array();
			$columnModel->sortNodes($sortedColumns, $columns);
			foreach ($sortedColumns as &$colu) {
				$colu['has_arti'] = M('ProgramsArticles')->where(array('program_dir_classid'=>$colu['classid']))->count() > 0 ? 1 : 0;
			}
			$this->assign('columns', $sortedColumns);
			$this->assign('tempClassId', generateUniqueID());
			$this->display('editColumn');
		}
	}
	
	/**
	 * 编辑栏目
	 */
	public function editColumn() {
		if (IS_POST) {
			$this->saveColumnData();
		} else {
			$columnClassID = trim(I('get.id'));
			$groupClassID = trim(I('get.gid'));
			
			if (empty($columnClassID)) {
				$this->error('非法操作！');
			}
				
			if (empty($groupClassID)) {
				$this->error('非法操作！');
			}
				
			$group = $this->getGroupInfo($groupClassID);
			if (!$group) {
				$this->error('非法操作！');
			}
			$this->assign('group', $group);
			$this->assign('program', $this->getProgramInfo($group['program_id']));
			
			$column = $this->getColumnInfo($columnClassID);
			if (!$column) {
				$this->error('非法操作！');
			}
			$this->assign('column', $column);
			
			$columnModel = D('ProgramsDirs');
			$where = array('status'=>1, 'dirgroup_classid'=>$groupClassID);
			
			$columns = $columnModel->field(array('id', 'classid', 'dir_name', 'dir_ico', 'parent_classid', 'dir_level', 'status', 'dirgroup_classid', 'dir_type'))->where($where)->select();
			foreach ($columns as &$col) {
				$col['dir_name'] = gbk2utf8($col['dir_name']);
				if (empty($col['parent_classid']) || trim($col['parent_classid']) == '' || !$col['parent_classid']) {
					$col['parent_classid'] = 'root-0000-000-000';
				}
			}
			
			$sortedColumns = array();
			$columnModel->sortNodes($sortedColumns, $columns);
			foreach ($sortedColumns as &$colu) {
				$colu['has_arti'] = M('ProgramsArticles')->where(array('program_dir_classid'=>$colu['classid']))->count() > 0 ? 1 : 0;
			}
			$this->assign('columns', $sortedColumns);
			$this->assign('subColumns', $columnModel->getBelongsColumns($columnClassID));
			$this->assign('tempClassId', $columnClassID);
			$this->display('editColumn');
		}
	}
	
	/**
	 * 保存栏目
	 */
	public function saveColumnData() {
		$column_classid = trim(I('post.column_classid'));
		$dirgroup_classid = trim(I('post.dirgroup_classid'));
		$parent_classid = trim(I('post.parent_classid'));
		$temp_column_classid = trim(I('post.temp_column_classid'));
		$dir_name = str_replace(array(';', '；'), '', trim(I('post.dir_name')));
		$dir_ico = trim(I('post.dir_ico'));
		$dir_type = trim(I('post.dir_type'));
		$dir_map = trim(I('post.dir_map'));
		$remark = trim(I('post.remark'));
	
		if (!$dirgroup_classid) {
			//$this->error('非法操作！');
			die(json_encode(array('stat'=>0, 'msg'=>'非法操作！')));
		}
		
		$group = $this->getGroupInfo($dirgroup_classid);
		if (!$group) {
			//$this->error('非法操作！');
			die(json_encode(array('stat'=>0, 'msg'=>'非法操作！')));
		}
		$program = $this->getProgramInfo($group['program_id']);
	
		if (empty($dir_name)) {
			//$this->error('栏目名称不能为空！');
			die(json_encode(array('stat'=>0, 'msg'=>'栏目名称不能为空！')));
		}
	
		$columnModel = D('ProgramsDirs');
		// 检测节目名称不能重复
		$where = array('dir_name'=>utf82gbk($dir_name), 'dirgroup_classid'=>$dirgroup_classid);
		
		if (!empty($column_classid)) {
			$where['classid'] = array('neq', $column_classid);
			
			if (trim($parent_classid) != '') {
				$belongs = $columnModel->getBelongsColumns($column_classid);
				if (in_array($parent_classid, $belongs)) {
					//$this->error('操作失败！分类级别设置错误！');
					die(json_encode(array('stat'=>0, 'msg'=>'操作失败！分类级别设置错误！')));
				}
			}
			
		}
		
		
		$data['parent_classid'] = $parent_classid;
		if (trim($parent_classid) == '') {
			$data['dir_level'] = 0;
			$where['dir_level'] = 0;
		} else {
			$data['dir_level'] = $columnModel->where(array('classid'=>$parent_classid))->getField('dir_level')*1 + 1;
			$where['parent_classid'] = $parent_classid;
		}
		
		if ($columnModel->where($where)->count() > 0) {
			//$this->error('操作失败！栏目名称冲突，请检查是否已存在同名的栏目！');
			die(json_encode(array('stat'=>0, 'msg'=>'操作失败！栏目名称冲突，请检查是否已存在同名的栏目！')));
		}
	
		$data['dir_name'] = utf82gbk($dir_name);
		$data['dir_ico'] = $dir_ico;
		$data['dir_type'] = $dir_type;
		$data['dir_map'] = in_array($dir_type, array('spotdir', 'routedir')) ? $dir_map : '';
		$data['remark'] = utf82gbk($remark);
		$data['status'] = 1;
		if (empty($column_classid)) {
			
			if (empty($temp_column_classid)) {
				//$this->error('操作失败！[原因]：页面数据错误，请刷新页面重试……');
				die(json_encode(array('stat'=>0, 'msg'=>'操作失败！[原因]：页面数据错误，请刷新页面重试……')));
			}
				
			$data['dirgroup_classid'] = $dirgroup_classid;
			$data['classid'] = $temp_column_classid;
	
			$re = $columnModel->add($data);
		} else {
			$oldColuInfo  = $this->getColumnInfo($column_classid);
			$re = $columnModel->where(array('classid'=>$column_classid))->save($data);
		}
	
		if ($re !== false) {
			if (!empty($column_classid)) {
				$columnModel->upColumnsLevel($column_classid, $data['dir_level']);
			}
			
			$column_classid = !empty($column_classid) ? $column_classid : $temp_column_classid;
			$artiModel = M('ProgramsArticlesTemp');
			$mediaLibModel = D('MediaLib');
			$icoIds = $artiModel->where(array('tmp_article_classid'=>$column_classid, 'act'=>'ico'))->getField('res_id', true);
			if (count($icoIds) > 0) {
				if ($oldColuInfo && (trim($oldColuInfo['dir_ico']) != '')) {
					$oldIcoId = $mediaLibModel->where(array('resourceid'=>$oldColuInfo['dir_ico']))->getField('id');
					if ($oldIcoId > 0) {
						array_push($icoIds, $oldIcoId);
					}
				}
				$mediaLibModel->deleteMediaByParams(array('id'=>array('in', $icoIds), 'resourceid'=>array('neq', $dir_ico)));
			}
			
			$mapIds = $artiModel->where(array('tmp_article_classid'=>$column_classid, 'act'=>'map'))->getField('res_id', true);
			if (count($mapIds) > 0) {
				if ($oldColuInfo && (trim($oldColuInfo['dir_map']) != '')) {
					$oldMapId = $mediaLibModel->where(array('resourceid'=>$oldColuInfo['dir_map']))->getField('id');
					if ($oldMapId > 0) {
						array_push($mapIds, $oldMapId);
					}
				}
				$mediaLibModel->deleteMediaByParams(array('id'=>array('in', $mapIds), 'resourceid'=>array('neq', $dir_map)));
			}
			
			$artiModel->where(array('tmp_article_classid'=>$column_classid))->delete();
			
			/* if ($dir_type == 'newsdir') {
				$this->imageNewsToAticle($group['program_id'], $column_classid);
			} */
			
			//$this->success('操作成功！', U('Programs/columns', array('pid'=>$program['program_classid'], 'id'=>$group['dirgroup_classid'])));
			$ajaxData = $columnModel->where(array('classid'=>$column_classid))->find();
			die(json_encode(array('stat'=>1, 'data'=>$ajaxData)));
		} else {
			//$this->error('操作失败！[原因]：' . $columnModel->getError());
			die(json_encode(array('stat'=>0, 'msg'=> $columnModel->getError())));
		}
	}
	
	/**
	 * 删除栏目
	 */
	public function delColumn() {
		$columnClassID = trim(I('post.id'));
		$groupClassID = trim(I('post.gid'));
			
		if (empty($columnClassID)) {
			//$this->error('非法操作！');
			die(json_encode(array('stat'=>0, 'msg'=>'非法操作！')));
		}
		
		if (empty($groupClassID)) {
			//$this->error('非法操作！');
			die(json_encode(array('stat'=>0, 'msg'=>'非法操作！')));
		}
		
		$group = $this->getGroupInfo($groupClassID);
		if (!$group) {
			//$this->error('非法操作！');
			die(json_encode(array('stat'=>0, 'msg'=>'非法操作！')));
		}
			
		$column = $this->getColumnInfo($columnClassID);
		if (!$column) {
			//$this->error('非法操作！');
			die(json_encode(array('stat'=>0, 'msg'=>'非法操作！')));
		}
			
		$columnModel = M('ProgramsDirs');
		$articleModel = M('ProgramsArticles');
		if ($columnModel->where(array('parent_classid'=>$columnClassID))->count() > 0 || $articleModel->where(array('program_dir_classid'=>$columnClassID))->count() > 0) {
			//$this->error('操作失败！非空的栏目不允许直接删除，如果确定要删除，请先清空该栏目！');
			die(json_encode(array('stat'=>0, 'msg'=>'操作失败！非空的栏目不允许直接删除，如果确定要删除，请先清空该栏目！')));
		}
		
		$re = $columnModel->where(array('classid'=>$columnClassID))->delete();
		if ($re !== false) {
			//$this->success('操作成功！');
			die(json_encode(array('stat'=>1)));
		} else {
			//$this->error('操作失败！[原因]：' . $columnModel->getError());
			die(json_encode(array('stat'=>0, 'msg'=>$columnModel->getError())));
		}
		
	}
	
	//批量审核栏目
	public function multiCheckDir() {
		$artiClassIDs = trim(I('post.aids'), ';');//去掉结尾的;
		$aidsArr = explode(';', $artiClassIDs);//转成数组

		if (empty($artiClassIDs) || empty($aidsArr)) {
			die(json_encode(array('stat'=>0, 'msg'=>'页面数据错误，请刷新页面重试……！')));
		}
		
		$data = array();
		$columnModel = M('ProgramsDirs');
		$articleModel = M('ProgramsArticles');
		
		$checkedType = trim(I('post.checkedType'));
		//过滤：已审核/待审核/已驳回
		if (!empty($checkedType)){
			switch ($checkedType){
				case "ys"://已审
					$checkMessageValue = '审核通过';
					$dotype = 1;
					break;
				case "ds"://待审
					$checkMessageValue = '取消审核';
					$dotype = 0;
					break;
				case "bh"://驳回
					$checkMessageValue = substr(trim(I('post.checkMessage')),0,255);//附加消息
					$dotype = -1;
					break;
				default:
					$dotype = 0;//全部
			}
		}else{
			die(json_encode(array('stat'=>0, 'msg'=>'操作失败，请重试……！')));
		}
		
		$data['checked'] = $dotype;
		$debug = '';
		
		$succCounts = $failCounts = 0;
		foreach ($aidsArr as $aid) {
			
			$column = $columnModel->where(array('classid'=>$aid))->find();
			if (!$column) {
				$failCounts++;
				continue;
			}else{
				$rid = $column['id'];//写日志用
				$isSetField = 1;//是否更新checked
				
				//有这个栏目，判断其下的文章
				$map_art = array();
				$map_art['_string']  = "program_dir_classid = '".$aid."' and ( checked = 0 or checked = -1 or checked is null )";
				$not_pass_article = $articleModel->where($map_art)->count();//此栏目下未通过审核的文章数量
				if ($not_pass_article){
					//存在未通过的文章
					//$failCounts++;
					$isSetField = 0;
					
					$debug .= "<br>未通过审核的文章数：".$not_pass_article;
				//	break;				
				}
			}
			
			//设为未审核或驳回直接修改字段，设为审核通过，需要条件判断
			if ($checkedType == 'ys' ){
				if ($isSetField){			
					//$data = array('checked'=>'1');
					$re = $columnModel->where(array('classid'=>$aid))->setField($data);;
					if ($re !== false) {
						$succCounts++;
						
						//写审核日志 START
						$checkLogModel = D("CheckLog");
						//$checkMessageValue = "审核通过";
						$checkLogModel->writeNewLog($dotype,$checkMessageValue,"programDir",$rid,$aid);
						//写审核日志 END
						
					} else {
						$failCounts++;
					}
				}
			}else{
				$re = $columnModel->where(array('classid'=>$aid))->setField($data);;
				if ($re !== false) {
					$succCounts++;
					
					//写审核日志 START
					$checkLogModel = D("CheckLog");
					//$checkMessageValue = "审核通过";
					$checkLogModel->writeNewLog($dotype,$checkMessageValue,"programDir",$rid,$aid);
					//写审核日志 END
					
				} else {
					$failCounts++;
				}
			}
		}
		
		if ($failCounts == count($aidsArr)) {
			die(json_encode(array('stat'=>0, 'msg'=>'操作失败！','debug'=>$debug)));
		} else {
			die(json_encode(array('stat'=>1,'msg'=>'操作成功','debug'=>$debug)));
		}
	}	
	
	
	/**
	 * 文章列表
	 */
	public function articles() {
		$columnClassID = trim(I('get.id'));
		$groupClassID = trim(I('get.gid'));
			
		if (empty($columnClassID)) {
			$this->error('非法操作！');
		}
		
		if (empty($groupClassID)) {
			$this->error('非法操作！');
		}
		
		$group = $this->getGroupInfo($groupClassID);
		
		if (!$group) {
			$this->error('非法操作！');
		}
		$this->assign('group', $group);//var_dump($group);
		
		if (!$this->checkProgramAccess($group['program_id'])) {
			$this->error('非法操作！');
		}
		
		$this->assign('program', $this->getProgramInfo($group['program_id']));
			
		$column = $this->getColumnInfo($columnClassID);
		if (!$column) {
			$this->error('非法操作！');
		}
		$this->assign('column', $column);
		
		
		if (in_array($column['dir_type'], array('routedir', 'storedir'))) {
		    
    		// 获取分类数据
    		$storeTypeModel = D('SCStoretype');
    		$originTypes = $storeTypeModel->order('Pid asc, ID asc')->select();
    		$sortedTypes = array();
    		$storeTypeModel->sortedTypes($sortedTypes, $originTypes);
    		$this->assign('storeTypes', $sortedTypes);
    		
    		// 获取商场相关配置选项
    		$mallSyscfgModel = D('SCSyscfg');
    		$mallFloors = $mallSyscfgModel->where(array('id'=>1))->getField('floors');
    		$this->assign('mallFloors', $mallFloors);
    		
    		$storeModel = D('SCStore');
    		 
    		// 构建搜索条件
    		$where['program_dir_classid'] = array('like', '%,' . $columnClassID . ',%');
    		 
    		// 加载数据分页类
    		import('ORG.Util.Page');
    		
    		// 数据分页
    		$totals = $storeModel->where($where)->count();
    		$Page = new Page($totals, 12);
    		$show = $Page->show();
    		$this->assign('page', $show);
    		
    		$stores = $storeModel->field(array('Id', 'sname', 'floor', 'adress', 'type'))->where($where)->limit($Page->firstRow. ',' .$Page->listRows)->select();
    		$this->assign("stores", $stores);
    		
		} else {
    		$articleModel = M('ProgramsArticles');
    		$where = array('program_dir_classid'=>$columnClassID);
    		/* if ($column['dir_type'] == 'newsdir' && $articleModel->where($where)->count() <= 0) {
    			$this->imageNewsToAticle($group['program_id'], $columnClassID);
    		} */
    		
    		$artiname = trim(I('get.artiname'));
    		if (!empty($artiname)) {
    			$where['article_name'] = array('like', '%' . utf82gbk($artiname) . '%');
    		}
			
			$checked = trim(I('get.checked'));
			$this->assign("checked",$checked);

			//过滤：已审核/待审核/已驳回
			if (!empty($checked)){
				switch ($checked){
					case "ys"://已审
						$where['checked'] = 1;
						break;
					case "ds"://待审
						$where['checked'] = 0;
						break;
					case "bh"://驳回
						$where['checked'] = -1;
						break;
					default:
						;//全部
				}
				
			}
    	
    		// 加载数据分页类
    		import('ORG.Util.Page');
    	
    		// 数据分页
    		$totals = $articleModel->where($where)->count();
    		$numPerPage = $_GET[numPerPage]?:50;
    		$Page = new Page($totals, $numPerPage);
    		$this->page = $Page->show();
    		$this->totalNum = $totals;
    		$this->lastPage = ceil($totals/$numPerPage);

    		$articles = $articleModel->field(array('id', 'article_name', 'article_classid', 'article_content_type', 'program_dir_classid', 'article_ico', 'create_time','checked' , 'sort'))->where($where)->order('sort, create_time desc')->limit($Page->firstRow. ',' .$Page->listRows)->select();
    		// $articles = $articleModel->field(array('id', 'article_name', 'article_classid', 'article_content_type', 'program_dir_classid', 'article_ico', 'editable', 'create_time','checked' , 'sort'))->where($where)->order('sort, create_time desc')->limit($Page->firstRow. ',' .$Page->listRows)->select();
    		foreach ($articles as &$arti) {
    			$arti['article_name'] = stripslashes(stripslashes($arti['article_name']));
    			$arti['article_content_type_txt'] = $this->getArticeTypes($arti['article_content_type']);
    			$arti['article_ico_path'] = str_replace('\\', '/', D('MediaLib')->where(array('resourceid'=>$arti['article_ico']))->getField('filepath'));
    		}
    		$this->assign('articles', $articles);
		}
		$this->display();
	}

	// 拖动修改文章顺序 By lym
	public function ajax_setArticleOrder(){
	    if(IS_POST){
	        $r = 0;
	        $model = M('ProgramsArticles');
	        $obj_list = $_POST[result];
	        foreach($obj_list as $value){
	            if(!$obj=$model->create($value)){
	                echo $model->getError();
	                return;
	            }else{
	                $model->save($obj);
	                $r++;
	            }
	        }
	        echo $r;
	    }
	}

	// 以自然索引顺序jQuery.each中的i索引为顺序，彻底初始化sort字段 By lym
	public function ajax_initArticleOrder(){
		$init_arr = $_POST[after_repeat_list];
		$model = M('ProgramsArticles');
		foreach ($init_arr as $value) {
		    if(!$obj = $model->create($value)){
		        echo $model->getError();
		        return;
		    }else{
		        $model->save($obj);
		    }
		}
		echo 1;
	}

	// 双击修改文章顺序字段的数值 By lym
	public function ajax_changeArticleOrder(){
	    if(IS_POST){
	        $model = M('ProgramsArticles');
	        $program_dir_classid = $_POST[program_dir_classid];
			$article_id = $_POST[article_id];
	        $max = $model->where("program_dir_classid='".$program_dir_classid."'")->count();
	        $origin_order_val = $_POST[origin_order_val] > $max ? $max-1 : $_POST[origin_order_val];
	        $sort = $_POST[sort] < $max ? $_POST[sort] : $max-1;
	        $rows_all = $model->where("program_dir_classid='".$program_dir_classid."'")->getField('id, sort');
	        $temp_arr = array();
	        if($origin_order_val < $sort){
	            for ($i=$origin_order_val; $i <= $sort; $i++) { 
	                $data = array();
	            	foreach($rows_all as $k => $v){
	            		if($v == $i){
	            			$data[id] = $k;
	            			$data[sort] = --$v;
	            			break;
	            		}
	            	}
	                array_push($temp_arr, $data);
	            }
	            $temp_arr[0][sort] = $sort;
	        }else{
	            for ($i=$sort; $i <= $origin_order_val; $i++) { 
	                $data = array();
	                foreach($rows_all as $k => $v){
	                	if($v == $i){
		                    $data[id] = $k;
		                    $data[sort] = ++$v;
		                    break;
	                	}
	                }
	                array_push($temp_arr, $data);
	            }
	            $temp_arr[count($temp_arr)-1][sort] = $sort;
	        }
	        foreach ($temp_arr as $value) {
	            if(!$obj = $model->create($value)){
	                echo $model->getError();
	                return;
	            }else{
	                $model->save($obj);
	                $r++;
	            }
	        }
	        echo $r-1;
	    }

	}
	
	/**
	 * 添加文章
	 * updateBy: lym at 2016-11-7 16:09:09
	 * 当新增文章的类型为html时，添加预览PDF功能。
	 */
	public function addArticle() {
		if (IS_POST) {
			$this->saveArticleData();
		} else {
			$columnClassID = trim(I('get.cid'));
			$groupClassID = trim(I('get.gid'));
				
			if (empty($columnClassID)) {
				$this->error('非法操作！');
			}
			
			if (empty($groupClassID)) {
				$this->error('非法操作！');
			}
			
			$group = $this->getGroupInfo($groupClassID);
			if (!$group) {
				$this->error('非法操作！');
			}
			$this->assign('group', $group);
			$this->assign('program', $this->getProgramInfo($group['program_id']));
				
			$column = $this->getColumnInfo($columnClassID);
			if (!$column) {
				$this->error('非法操作！');
			}
			$this->assign('column', $column);
			
			// 获取最近3天的天气信息
			if ($column['dir_type'] == 'weatdir') {
				$weatherInfo = M('ReslibWeather')->order('id asc')->limit(0,3)->select();
				$this->assign('weatherInfo', $weatherInfo);
			}
			
			// 根据不同栏目类型加载不同模板
			$tplName = '';
			switch ($column['dir_type']) {
				case 'htaqdir' : $tplName = 'editHtaqArticle'; break;
				case 'moviedir' : $tplName = 'editMovieArticle'; break;
				default : $tplName = 'editArticle';
			}
			
			$rmModel = D('ResmanagerDirs');
			$resTypeTreeData = array();
			$resTypeTreeData['id'] = 0;
			$resTypeTreeData['name'] = '根目录';
			$resTypeTreeData['open'] = true;
			$resTypeTreeData['classid'] = '';
			$resTypeTreeData['children'] = $rmModel->getZTreeData();
			$this->assign('treeData', json_encode($resTypeTreeData));
			
			$this->assign('columns', $this->getGroupColumns($groupClassID));
			$this->assign('tempClassId', generateUniqueID());
			$this->assign('artiConTypes', $this->getArticeTypes());
			$this->assign('spColumns', array('weatdir'));
			$this->display($tplName);
		}
	}

	/**
	 * ajax接收UE传来的html数据，以pdf方式预览
	 */
	public function ajax_pdf_preview(){
		$_SESSION['article_content'] = $_POST['htmlStr'];
		$_SESSION['article_name'] = $_POST['article_name'];
		if($_SESSION['article_content']){
			echo 1;
		}else{
			echo 0;
		}
	}

	// pdf预览
	public function pdf_preview(){
		$article_name = $_SESSION['article_name'];
		$headerHtml = '<!DOCTYPE html><html><head><style>img{max-width:100%;}</style></head><body><div style="position:fixed;width:100%;height:100%;top:0px;right:0px;bottom:0px;left:0px;"></div><div id="jl-content">';
		$footerHtml = '</div></body></html>';
		$article_content = $headerHtml . stripslashes($_SESSION['article_content']) . $footerHtml;
		include(LIBRARY_PATH."mpdf60/mpdf.php");
		$mpdf=new mPDF('','','','fzdbsjt'); 
		$mpdf->autoScriptToLang = true;
		$mpdf->baseScript = 1;
		$mpdf->autoVietnamese = true;
		$mpdf->autoArabic = true;
		$mpdf->autoLangToFont = true;
		$mpdf->WriteHTML($article_content);
		$pdf = $mpdf->Output();
	}
	
	/**
	 * 编辑文章
	 */
	public function editArticle() {
		if (IS_POST) {
			$this->saveArticleData();
		} else {
			$articleClassID = trim(I('get.id'));
			$columnClassID = trim(I('get.cid'));
			$groupClassID = trim(I('get.gid'));
			
			if (empty($articleClassID)) {
				$this->error('非法操作！');
			}
			if (empty($columnClassID)) {
				$this->error('非法操作！');
			}
				
			if (empty($groupClassID)) {
				$this->error('非法操作！');
			}
				
			$group = $this->getGroupInfo($groupClassID);
			if (!$group) {
				$this->error('非法操作！');
			}
			$this->assign('group', $group);
			$this->assign('program', $this->getProgramInfo($group['program_id']));
			
			$column = $this->getColumnInfo($columnClassID);
			if (!$column) {
				$this->error('非法操作！');
			}
			$this->assign('column', $column);
			$this->assign('spColumns', array('weatdir'));
			// 获取最近3天的天气信息
			if ($column['dir_type'] == 'weatdir') {
				$weatherInfo = M('ReslibWeather')->order('id asc')->limit(0,3)->select();
				$this->assign('weatherInfo', $weatherInfo);
			}
			
			$articleModel = M('ProgramsArticles');
			$article = $articleModel->where(array('article_classid'=>$articleClassID, 'program_dir_classid'=>$columnClassID))->find();
			if (!$article) {
				$this->error('非法操作！');
			}
			$artiFiles = array();
			$article['article_name'] = gbk2utf8($article['article_name']);
			$article['article_content_type_txt'] = $this->getArticeTypes($article['article_content_type']);
			$article['article_ico'] = trim($article['article_ico']);
			if (!empty($article['article_ico'])) {
				$article['article_ico_path'] = str_replace('\\', '/', D('MediaLib')->where(array('resourceid'=>$article['article_ico']))->getField('filepath'));
				if (!empty($article['article_ico_path'])) {
					array_push($artiFiles, utf8ToGbk(rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/' . $article['article_ico_path']));
				}
			}
			if ($article['article_content_type'] == 'html') {
				
				// 以"文件"方式获取html类型文章的内容 [ 禁用 ]
				/*$mediaLibModel = D('MediaLib');
				$contentHtmlPath = trim($mediaLibModel->where(array('resourceid'=>$articleClassID, 'filepath'=>array('like', '%.html')))->getField('filepath'));
				
				if (!empty($contentHtmlPath) && is_file(rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/'  . $contentHtmlPath)) {
					$contentHtml = file_get_contents(rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/'  . $contentHtmlPath);
					preg_match('/<div id="jl-content">(.*?)<\/div>/', $contentHtml, $matches);
					$article['article_content'] = gbk2utf8(preg_replace_callback('/<img\ssrc="(.*?)"\stitle=/', array($this, 'replaceImgSrcIn'), $matches[1]));
				} else {
					$article['article_content'] = '';
				}*/
				
				// 以"数据库"方式获取html类型文章的内容 [ 启用 ]
				$article['article_html_content'] = gbk2utf8(htmlspecialchars_decode(stripcslashes(stripcslashes($article['article_content']))));
				
			} else if ($article['article_content_type'] == 'txt') {
				
				$article['article_txt_content'] = replaceTagsBrToP(gbk2utf8($article['article_content']));
				
			} else if ($article['article_content_type'] == 'hyperlink') {
				
				$article['article_hyperlink_content'] = $article['article_content'];
				
			}
			
			if (!in_array($article['article_content_type'], array('hyperlink'))) {
				
				// 获取文章内容关联媒体资源
				$mediaLibModel = D('MediaLib');
				$resList = $mediaLibModel->where(array('resourceid'=>$article['article_classid']))->select();
				
				$movieMedias = array();
				foreach ($resList as &$res) {
					$res['filepath'] = str_replace('\\', '/', gbk2utf8($res['filepath']));
					$res['pdfpath'] = str_replace('\\', '/', gbk2utf8($res['pdfpath']));
					$res['filename'] = getFilename($res['filepath']);

					array_push($artiFiles, utf8ToGbk(rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/' . $res['filepath']));
					array_push($artiFiles, C('UPLOAD_ROOT_PATH') . $res['pdfpath']);
					
					if ($column['dir_type'] == 'moviedir') {
						$mediaType = getFileTypeByEXT($res['type']);
						if ($mediaType == 'image') {
							$movieMedias['image'][] = $res;
						} else if ($mediaType == 'video') {
							$movieMedias['video'][] = $res;
						}
					}
				}
				
				if ($column['dir_type'] == 'moviedir') {
					$this->assign('movieMedias', $movieMedias);
				}
				
				if ($article['article_content_type'] != 'html') {
					$this->assign('resList', $resList);
				}
				
				$artiDir = 'program/' . $group['program_id'] . '/article/' . $articleClassID;
				foreach (glob(rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/' . $artiDir . "/*") as $file) {
					if (is_file($file) && !in_array($file, $artiFiles)) {
						@unlink($file);
					}
				}
				
			}
			
			// 根据不同栏目类型加载不同模板
			$tplName = '';
			switch ($column['dir_type']) {
				case 'htaqdir' : $tplName = 'editHtaqArticle'; break;
				case 'moviedir' : $tplName = 'editMovieArticle'; break;
				default : $tplName = 'editArticle';
			}
			
			$this->assign('article', $article);
			
			$rmModel = D('ResmanagerDirs');
			$resTypeTreeData = array();
			$resTypeTreeData['id'] = 0;
			$resTypeTreeData['name'] = '根目录';
			$resTypeTreeData['open'] = true;
			$resTypeTreeData['classid'] = '';
			$resTypeTreeData['children'] = $rmModel->getZTreeData();
			$this->assign('treeData', json_encode($resTypeTreeData));
			
			$this->assign('tempClassId', $article['article_classid']);
			$this->assign('columns', $this->getGroupColumns($groupClassID));
			$this->assign('artiConTypes', $this->getArticeTypes());
			$this->display($tplName);
		}
	}
	
	/**
	 * 保存文章数据
	 */
	private function saveArticleData() {
		
		$article_classid = trim(I('post.article_classid'));
		$dirgroup_classid = trim(I('post.dirgroup_classid'));
		$column_classid = trim(I('post.column_classid'));
		$temp_article_classid = trim(I('post.temp_article_classid'));
		$program_dir_classid = trim(I('post.program_dir_classid'));
		$article_name = str_replace(array(';', '；'), '', trim(I('post.article_name')));
		$article_content_type = trim(I('post.article_content_type'));
		$article_ico = trim(I('post.article_ico'));
		$pdf_meantime = trim(I('post.pdf_meantime'));
	
		if (!$dirgroup_classid) {
			$this->saveArticleFailed($temp_article_classid);
			$this->error('非法操作！');
		}
		
		$group = $this->getGroupInfo($dirgroup_classid);
		if (!$group) {
			$this->saveArticleFailed($temp_article_classid);
			$this->error('非法操作！');
		}
		
		if (!$column_classid) {
			$this->saveArticleFailed($temp_article_classid);
			$this->error('非法操作！');
		}
		
		$column = $this->getColumnInfo($column_classid);
		if (!$column) {
			$this->saveArticleFailed($temp_article_classid);
			$this->error('非法操作！');
		}
	
		if (empty($article_name)) {
			$this->saveArticleFailed($temp_article_classid);
			$this->error('文章名称不能为空！');
		}
		
		if (empty($program_dir_classid)) {
			$this->saveArticleFailed($temp_article_classid);
			$this->error('非法操作！');
		}
	
		$articleModel = M('ProgramsArticles');
		// 检测名称不能重复
		$where = array('article_name'=>utf82gbk($article_name), 'program_dir_classid'=>$program_dir_classid);
		
		if (!empty($article_classid)) {
			$where['article_classid'] = array('neq', $article_classid);
		}
		if ($articleModel->where($where)->count() > 0) {
			$this->saveArticleFailed($temp_article_classid);
			$this->error('操作失败！文章名称冲突，请检查是否已存在同名的文章！');
		}
		
		$data['program_dir_classid'] = $program_dir_classid;
		$data['article_name'] = utf82gbk($article_name);
		$data['article_ico'] = $article_ico;
		$nowTime = time();
		$data['create_time'] = date('Y-m-d H:i:s', $nowTime);
		$data['ctime_stamps'] = $nowTime;
		
		// 文章内容处理 （原先的网页生成后，背景色是#4F6467，文字颜色是FFFFFF）
		if ($article_content_type == 'html') {
			$_POST['article_html_content'] = preg_replace('/<a.*?>(.*?)<\/a>/', '$1', $_POST['article_html_content']); //  过滤掉<a>标签
			$headerHtml = '<!DOCTYPE html><html><head><style>img{max-width:100%;}</style></head><body><div style="position:fixed;width:100%;height:100%;top:0px;right:0px;bottom:0px;left:0px;"></div><div id="jl-content">';
			$footerHtml = '</div></body></html>';
			$article_content = $headerHtml . stripslashes($_POST['article_html_content']) . $footerHtml;
			$data['article_content'] = utf82gbk(trim(I('post.article_html_content')));
		} else if ($article_content_type == 'txt') {
			if ($column['dir_type'] == 'htaqdir') {
				$data['article_content'] = trim(strip_tags(I('post.article_content')));
			} else {
				$data['article_content'] = utf82gbk(trim(replaceTagsPToBr(strip_tags($_POST['article_txt_content'], '<p>'))));
			}
		} else if ($article_content_type == 'hyperlink') {
			$data['article_content'] = trim(strip_tags(I('post.article_hyperlink_content')));
		} else {
			$data['article_content'] = '';
		}
		
		if (empty($article_classid)) {
			$data['article_content_type'] = $article_content_type;

			if (empty($temp_article_classid)) {
				$this->error('操作失败！[原因]：页面数据错误，请刷新页面重试……');
			}
			
			$data['article_classid'] = $temp_article_classid;
	
			$re = $articleModel->add($data);
		} else {
			$oldArtiInfo  = $articleModel->where(array('article_classid'=>$article_classid))->find();
			$re = $articleModel->where(array('article_classid'=>$article_classid))->save($data);
		}
		
		if ($re !== false) {
			
			// 临时表处理
			$artiModel = M('ProgramsArticlesTemp');
			if (!empty($article_classid)) {
				$delIds = $artiModel->where(array('tmp_article_classid'=>$article_classid, 'act'=>'del'))->getField('res_id', true);
				if (count($delIds) > 0) {
					$mediaLibModel = D('MediaLib');
					$mediaLibModel->where(array('id'=>array('in', $delIds)))->delete();
				}
			}
			
			$article_classid = !empty($article_classid) ? $article_classid : $temp_article_classid;
			$icoIds = $artiModel->where(array('tmp_article_classid'=>$article_classid, 'act'=>'ico'))->getField('res_id', true);
			if (count($icoIds) > 0) {
				$mediaLibModel = D('MediaLib');
				if ($oldArtiInfo && (trim($oldArtiInfo['article_ico']) != '')) {
					$oldIcoId = $mediaLibModel->where(array('resourceid'=>$oldArtiInfo['article_ico']))->getField('id');
					if ($oldIcoId > 0) {
						array_push($icoIds, $oldIcoId);
					}
				}
				$mediaLibModel->where(array('id'=>array('in', $icoIds), 'resourceid'=>array('neq', $article_ico)))->delete();
			}
			
			$artiModel->where(array('tmp_article_classid'=>$article_classid))->delete();

			// 处理html格式文章的内容,保存为html文件
			if ($article_content_type == 'html') {
				$programClassID = M('ProgramsDirsGroups')->where(array('dirgroup_classid'=>$dirgroup_classid))->getField('program_id');
				$filename = generateUniqueID();
				$conSavedir = 'program/' . $programClassID . '/article/' . $article_classid . '/';
				$savedir = rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/' . $conSavedir;
				if (!is_dir($savedir)) {
					@mkdir($savedir, 0777, true);
				}
				$conSavepath = $savedir . $filename . '.html';

				$mediaLibModel = D('MediaLib');
				$where = array('resourceid'=>$article_classid, 'filepath' => array('like', '%.html'));
				
				if ($column['dir_type'] != 'weatdir') {
					
					if (preg_match_all('/<img\ssrc=".*?\?(\d+)"\s[title=|style=]/', $article_content, $matches)) {
						$contentImgIds = array_map('strToNumber', $matches[1]);
						$where['id'] = array('not in', $contentImgIds);
					}
					
				}
				
				$mediaLibModel->where($where)->delete();
				/* if (is_file($conSavepath)) {
					@unlink($conSavepath);
				} */
				
				if ($column['dir_type'] == 'weatdir') {
					
					// 生成天气预报模板（.html格式文件）
					$htmlCode = $this->weatherInfo();
					file_put_contents($conSavepath, utf8ToGbk(str_replace('/Public/images/weather/icon', '../../../scriptlibs/images/icon', $htmlCode)));
					
				} else {
					
					file_put_contents($conSavepath, utf8ToGbk(preg_replace_callback('/<img\ssrc="(.*?)"\stitle=/', array($this, 'replaceImgSrcOut'), $article_content)));
					
				}
				
				$data['filepath'] = str_replace('/', '\\', $conSavedir . $filename . '.html');

				//判断，当复选框勾选之后存储PDF文件
				if($pdf_meantime){
					$data['pdfpath'] = str_replace('/', '\\', $conSavedir . $filename . '.pdf');
					include(LIBRARY_PATH."mpdf60/mpdf.php");
					$mpdf=new mPDF(); 
					$mpdf->autoScriptToLang = true;
					$mpdf->baseScript = 1;
					$mpdf->autoVietnamese = true;
					$mpdf->autoArabic = true;
					$mpdf->autoLangToFont = true;
					$mpdf->WriteHTML($article_content);
					$mpdf->Output(C('UPLOAD_ROOT_PATH').$data['pdfpath'], 'F');
				}
				$data['resourceid'] = $article_classid;
				$mediaLibResult = $mediaLibModel->execute("insert into TB_MediaLib (filepath, type, resourceid, pdfpath) values ('" . $data['filepath'] . "', 'html','" . $data['resourceid'] . "', '" . $data['pdfpath'] . "')");
				
			}

			// txt类型也要丧心病狂地生成txt文件
			if ($article_content_type == 'txt') {
				$programClassID = M('ProgramsDirsGroups')->where(array('dirgroup_classid'=>$dirgroup_classid))->getField('program_id');
				$filename = generateUniqueID();
				$conSavedir = 'program/' . $programClassID . '/article/' . $article_classid . '/';
				$savedir = rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/' . $conSavedir;
				
				// file_put_contents("filepath.txt",PHP_EOL."debug:".$savedir.PHP_EOL,FILE_APPEND);//写调试到TXT
				
				if (!is_dir($savedir)) {
					@mkdir($savedir, 0777, true);
				}
				$conSavepath = $savedir . $filename . '.txt';
				
				// file_put_contents("txtpath.txt",PHP_EOL."debug:".$conSavepath.PHP_EOL,FILE_APPEND);//写调试到TXT

				$mediaLibModel = D('MediaLib');
				$where = array('resourceid'=>$article_classid, 'txtpath' => array('like', '%.txt'));
				$mediaLibModel->where($where)->delete();

				// file_put_contents($conSavepath, str_replace('&nbsp;', ' ', str_replace('&#39;', "'", htmlspecialchars_decode(strip_tags($data['article_content'])))));
				file_put_contents($conSavepath, htmlspecialchars_decode($_POST[txtPlain]));
				$data['txtpath'] = str_replace('/', '\\', $conSavedir . $filename . '.txt');
				$data['resourceid'] = $article_classid;
				$mediaLibResult = $mediaLibModel->execute("insert into TB_MediaLib (filepath, type, resourceid) values ('" . $data['txtpath'] . "', 'txt','" . $data['resourceid'] . "')");
			}
			
			$this->success('操作成功！', U('Programs/articles', array('gid'=>$dirgroup_classid, 'id'=>$column_classid)));
		} else {
			$this->saveArticleFailed($temp_article_classid);
			$this->error('操作失败！[原因]：' . $articleModel->getDBError());
		}
	}

	/**
	 * 返回指定字符串的编码类型，目前支持的有5种：UTF-8, ANSI, GBK, GB2312, GB18030
	 * @param  [string] $string [指定待检测的字符串]
	 * @return [string]         [返回字符编码字符串]
	 */
	public function chkCode($string){
		$code = array('UTF-8', 'ANSI', 'GBK', 'GB2312', 'GB18030');
		foreach($code as $c){
			if($string === iconv('UTF-8', $c, iconv($c, 'UTF-8', $string))){
				return $c;
			}
		}
		return '超出可控字符编码范围！';
	}

	public function ajax_readTxt(){
		$filepath = substr($_POST[filepath], 1);
		$temp_classid = $_POST[temp_classid];
		$model = D('MediaLib');
		$content = file_get_contents($filepath);
		$inCode = $this->chkCode($content);
		echo nl2br(iconv($inCode, 'UTF-8', file_get_contents($filepath)));
		@unlink($filepath);
	}
	
	private function replaceImgSrcOut($matches) {
		return '<img src="' . str_replace('/'. trim(C('UPLOAD_ROOT_PATH'), '/'), '../../../..', $matches[1]) . '" title=';
	}
	
	private function replaceImgSrcIn($matches) {
		return '<img src="' . str_replace('../../../..', '/jolink/docs/medialib', $matches[1]) . '" title=';
	}
	
	private function saveArticleFailed($article_classid) {
		
		$artiModel = M('ProgramsArticlesTemp');
		$delIds = $artiModel->where(array('tmp_article_classid'=>$article_classid, 'act'=>'add'))->getField('res_id', true);
		if (count($delIds) > 0) {
			$mediaLibModel = D('MediaLib');
			$mediaLibModel->where(array('id'=>array('in', $delIds)))->delete();
		}
			
		$artiModel->where(array('tmp_article_classid'=>$article_classid))->delete();
		
		//deldir(rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/program/article/' . $article_classid);
	}
	
	public function delArticle() {
		$progClassID = trim(I('get.pid'));
		$artiClassID = trim(I('get.id'));
		
		if (!$artiClassID) {
			//$this->error('非法操作！');
		}
		
		$articleModel = M('ProgramsArticles');
		$article = $articleModel->where(array('article_classid'=>$artiClassID))->find();
		if (!$article) {
			//$this->error('非法操作！');
		}
		
		$re = $articleModel->where(array('article_classid'=>$artiClassID))->delete();
		if ($re !== false) {
			deldir(rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/program/' . $progClassID . '/article/' . $artiClassID);
			D('MediaLib')->where(array('resourceid'=>$artiClassID))->delete();
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！[原因]：' . $articleModel->getError());
		}
	}
	
	public function multiDelArticles() {
		$progClassID = trim(I('post.pid'));
		$artiClassIDs = trim(I('post.aids'), ';');
		$aidsArr = explode(';', $artiClassIDs);

		if (empty($progClassID) || empty($artiClassIDs) || empty($aidsArr)) {
			die(json_encode(array('stat'=>0, 'msg'=>'页面数据错误，请刷新页面重试……！')));
		}
		
		$articleModel = M('ProgramsArticles');
		$succCounts = $failCounts = 0;
		foreach ($aidsArr as $aid) {
			
			$article = $articleModel->where(array('article_classid'=>$aid))->find();
			if (!$article) {
				$failCounts++;
				continue;
			}
			
			$re = $articleModel->where(array('article_classid'=>$aid))->delete();
			if ($re !== false) {
				deldir(rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/program/' . $progClassID . '/article/' . $aid);
				D('MediaLib')->where(array('resourceid'=>$aid))->delete();
				$succCounts++;
			} else {
				$failCounts++;
			}
		}
		
		if ($failCounts == count($aidsArr)) {
			die(json_encode(array('stat'=>0, 'msg'=>'网络错误导致操作失败，请刷新页面重试……！')));
		} else {
			die(json_encode(array('stat'=>1)));
		}
	}
	
	//批量审核文章
	public function multiCheckArticles() {
		$progClassID = trim(I('post.pid'));
		$artiClassIDs = trim(I('post.aids'), ';');
		$aidsArr = explode(';', $artiClassIDs);

		if (empty($artiClassIDs) || empty($aidsArr)) {//empty($progClassID) || 
			die(json_encode(array('stat'=>0, 'msg'=>'d页面数据错误，请刷新页面重试……！')));
		}
		
		$data = array();
		
		$checkedType = trim(I('post.checkedType'));
		//过滤：已审核/待审核/已驳回
		if (!empty($checkedType)){
			switch ($checkedType){
				case "ys"://已审
					$checkMessageValue = '审核通过';
					$dotype = 1;
					break;
				case "ds"://待审
					$checkMessageValue = '取消审核';
					$dotype = 0;
					break;
				case "bh"://驳回
					$checkMessageValue = substr(trim(I('post.checkMessage')),0,255);//附加消息
					$dotype = -1;
					break;
				default:
					$dotype = 0;//全部
			}
		}else{
			die(json_encode(array('stat'=>0, 'msg'=>'操作失败，请重试……！')));
		}
		
		$data['checked'] = $dotype;		
		
		$articleModel = M('ProgramsArticles');
		$succCounts = $failCounts = 0;
		foreach ($aidsArr as $aid) {
			
			$article = $articleModel->where(array('article_classid'=>$aid))->find();
			if (!$article) {
				$failCounts++;
				continue;
			}else{
				$rid = $article['id'];//写日志用
			}
			
			
			//$data = array('checked'=>'1');
			$re = $articleModel->where(array('article_classid'=>$aid))->setField($data);;
			if ($re !== false) {
				$succCounts++;
				
				//写审核日志 START
				$checkLogModel = D("CheckLog");
				//$checkMessageValue = "审核通过";
				$checkLogModel->writeNewLog($dotype,$checkMessageValue,"programArticle",$rid,$aid);
				//写审核日志 END
				
			} else {
				$failCounts++;
			}
		}
		
		if ($failCounts == count($aidsArr)) {
			die(json_encode(array('stat'=>0, 'msg'=>'网络错误导致操作失败，请刷新页面重试……！')));
		} else {
			die(json_encode(array('stat'=>1)));
		}
	}


	
	private function getGroupColumns($groupClassID) {
		$columnModel = D('ProgramsDirs');
		$where = array('status'=>1, 'dirgroup_classid'=>$groupClassID);
		
		$columns = $columnModel->field(array('id', 'classid', 'dir_name', 'dir_ico', 'parent_classid', 'dir_level', 'status', 'dirgroup_classid', 'dir_type'))->where($where)->select();
		foreach ($columns as &$col) {
			$col['dir_name'] = gbk2utf8($col['dir_name']);
			$col['dir_ico_path'] = str_replace('\\', '/', D('MediaLib')->where(array('resourceid'=>$col['dir_ico']))->getField('filepath'));
			if (empty($col['parent_classid']) || trim($col['parent_classid']) == '' || !$col['parent_classid']) {
				$col['parent_classid'] = 'root-0000-000-000';
			}
		}
		
		$sortedColumns = array();
		$columnModel->sortNodes($sortedColumns, $columns);
		return $sortedColumns;
	}
	
	/**
	 * 获取文章类型
	 * @param string $typeCode
	 * @return mixed < array | string >
	 */
	private function getArticeTypes($typeCode = '') {
		$artiTypes = M('ProgramsArticleTypes')->field(array('id,article_type_enname,article_type_cnname'))->order('id asc')->select();
		if ($artiTypes) {
			
			$types = array();
			
			foreach ($artiTypes as $item) {
				$types[$item['article_type_enname']] = $item['article_type_cnname'];
			}

			return empty($typeCode) ? $types : $types[$typeCode];
			 
		} else {
			return null;
		}
	}
	
	/**
	 * 图片新闻类型的文章预览
	 */
	public function imageNewsArticle() {
		
		$articleClassID = trim(I('get.id'));
		
		if (empty($articleClassID)) {
			$this->error('非法请求！');
		}
		
		$articleModel = M('ProgramsArticles');
		$articleInfo = $articleModel->field(array('id', 'article_name'))->where(array('article_classid'=>$articleClassID))->find();
		if (!$articleInfo) {
			$this->error('非法请求！');
		}
		$this->assign('articleInfo', $articleInfo);
		
		$pictures = D('MediaLib')->where(array('resourceid'=>$articleClassID, 'filepath'=>array('notlike', '%.html')))->select();
		foreach ($pictures as &$pic) {
			$pic['filepath'] = str_replace('\\', '/', $pic['filepath']);
		}
		$this->assign('pictures', $pictures);
		$this->assign('refererURL', $_SERVER["HTTP_REFERER"]);
		$this->display();
	}
	
	private function imageNews($programClassID, $articleClassID) {
		$articleInfo = M('ProgramsArticles')->field(array('id', 'article_name', 'create_time'))->where(array('article_classid'=>$articleClassID))->find();
		$pictures = D('MediaLib')->where(array('resourceid'=>$articleClassID))->select();
		foreach ($pictures as &$pic) {
			$pic['filepath'] = str_replace('\\', '/', $pic['filepath']);
		}
		$this->assign('article', $articleInfo);
		$this->assign('pictures', $pictures);
		$contents = $this->fetch('Programs:imageNews');
		
		$conSavedir = 'program/' . $programClassID . '/article/' . $articleClassID . '/';
		$savedir = rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/' . $conSavedir;
		if (!is_dir($savedir)) {
			@mkdir($savedir, 0777, true);
		}
		$conSavepath = $savedir . $articleClassID . '.html';
		
		$mediaLibModel = D('MediaLib');
		$where = array('resourceid'=>$articleClassID, 'filepath'=>array('like', '%.html'));
		$mediaLibModel->where($where)->delete();
		if (is_file($conSavepath)) {
			unlink($conSavepath);
		}
		
		$contents = str_replace('/Public/images', '../../../scriptlibs/images', $contents);
		$contents = str_replace('/Public/script/jquery', '../../../scriptlibs/js', $contents);
		file_put_contents($conSavepath, preg_replace_callback('/<img\ssrc="(.*?)">/', array($this, 'imageNewsSrcReplace'), $contents));
		
		$data['filepath'] = str_replace('/', '\\', $conSavedir . $articleClassID . '.html');
		$data['resourceid'] = $articleClassID;
		$mediaLibResult = $mediaLibModel->execute("insert into TB_MediaLib (filepath, resourceid) values ('" . $data['filepath'] . "', '" . $data['resourceid'] . "')");
		
		//$this->display();
	}
	
	private function imageNewsSrcReplace($matches) {
		return '<img src="' . getFilename($matches[1]) . '">';
	}
	
	/**
	 * 导入网络采集的图片新闻内容到文章
	 */
	public function imageNewsToAticle() {
		
		$porgClassId = trim(I('post.porgClassId'));
		$coluClassID = trim(I('post.coluClassID'));
		$type = trim(I('post.type'));
		$postData = trim(I('post.postData'), ',');
		
		$newsModel = M('ReslibNews');
		$galleryModel = M('ReslibNewsgallery');
		
		if ($type == 'selected') {
			if (empty($postData) || count(explode(',', $postData)) <= 0) {
				die(json_encode(array('stat'=>0, 'msg'=>'网络数据错误，请刷新页面重试……！')));
			}
			
			$latestedNews = $newsModel->where(array('id'=>array('in', explode(',', $postData))))->order('news_date desc')->select();
		} else {
			// 获取最新10条新闻，每篇新闻生成图片集类型的文章
			$latestedNews = $newsModel->order('news_date desc')->limit(10)->select();
		}
		if ($latestedNews) {
			$articleModel = M('ProgramsArticles');
			$mediaLibModel = D('MediaLib');
			
			if ($type == 'auto') {
				$artiWhere = array('program_dir_classid'=>$coluClassID);
				$artiIds = $articleModel->where($artiWhere)->field('id, article_classid, article_ico')->select();
				
				if (count($artiIds) > 0) {
						
					foreach ($artiIds as $arti) {
						$resClassIds = array($arti['article_classid']);
						if (trim($arti['article_ico']) !== '') {
							array_push($resClassIds, $arti['article_ico']);
						}
						if (D('MediaLib')->where(array('resourceid'=>array('in', $resClassIds)))->delete() && $articleModel->where(array('id'=>$arti['id']))->delete()) {
							deldir(rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/program/' . $porgClassId . '/article/' . $arti['article_classid']);
						}
					}
				}
			}
			
			
			foreach ($latestedNews as &$news) {
			    
			    // 检查是否存在同名的文章
			    if ($articleModel->where(array('program_dir_classid'=>$coluClassID, 'article_name'=>$news['news_title']))->count() > 0) {
			        continue;
			    }
				
				// 创建文章记录
				$articleClassID = generateUniqueID();
				$articleData['article_name'] = $news['news_title'];
				$articleData['article_classid'] = $articleClassID;
				$articleData['article_content_type'] = 'html';
				$articleData['article_content'] = $news['news_content'];
				$articleData['program_dir_classid'] = $coluClassID;
				$articleData['article_ico'] = '';
				$nowTime = time();
				//$articleData['create_time'] = date('Y-m-d H:i:s', $nowTime);
				$articleData['create_time'] = $news['news_date'];
				$articleData['ctime_stamps'] = $nowTime;
				$articleInsertRe = $articleModel->add($articleData);
				if ($articleInsertRe) {
					$gallery = $galleryModel->where(array('news_id'=>$news['id']))->order('id asc')->select();
					
					if ($gallery) {
						
						// 创建文章资源保存目录
						$dbSavePath = 'program/' . $porgClassId . '/article/' . $articleClassID . '/';
						$destPath = rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/' . $dbSavePath;
						if (!is_dir($destPath)) {
							@mkdir($destPath, 0777, true);
						}
						
						foreach ($gallery as $key=>$pic) {
							
							$sourcePath = 'Uploads/reslib/news/' .$pic['image'];
							if (is_file($sourcePath)) {
								$fileExt = substr($pic['image'], strrpos($pic['image'], '.'));
								$basename = generateUniqueID() . $fileExt;
								if (copy($sourcePath, $destPath . utf8ToGbk($basename))) {
									$data['filepath'] = str_replace('/', '\\', $dbSavePath . $basename);
									$data['resourceid'] = $articleClassID;
									$data['news_note'] = $pic['note'];
									
									$mediaLibResult = $mediaLibModel->execute("insert into TB_MediaLib (filepath, resourceid, news_note) values ('" . $data['filepath'] . "', '" . $data['resourceid'] . "', '" . $data['news_note'] . "')");
								}
								
								if ($key+1 == 1) {
									$icoResID = generateUniqueID();
									$basename2 = generateUniqueID() . $fileExt;
									if (copy($sourcePath, $destPath . utf8ToGbk($basename2))) {
										$data['filepath'] = str_replace('/', '\\', $dbSavePath . $basename2);
										$data['resourceid'] = $icoResID;
										$data['news_note'] = $news['news_content'];
											
										$icoInsertRe = $mediaLibResult = $mediaLibModel->execute("insert into TB_MediaLib (filepath, resourceid, news_note) values ('" . $data['filepath'] . "', '" . $data['resourceid'] . "', '" . $data['news_note'] . "')");
										if ($icoInsertRe) {
											$articleModel->where(array('article_classid'=>$articleClassID))->save(array('article_ico'=>$icoResID));
										}
									}
								}
							}
						}
						
						$this->imageNews($porgClassId, $articleClassID);
					}
				}
			}
			
			die(json_encode(array('stat'=>1, 'msg'=>'操作成功！')));
		} else {
			die(json_encode(array('stat'=>0, 'msg'=>'没有可用数据！')));
		}
	}
	
	/**
	 * 获取天气预报模板内容
	 * @return Ambigous <string, NULL>
	 */
	private function weatherInfo() {
		
		// 获取最近3天的天气信息
		$weatherInfo = M('ReslibWeather')->order('id asc')->limit(0,3)->select();
		$this->assign('weatherInfo', $weatherInfo);
		//file_put_contents('wea.html', str_replace('/Public/image', './Public/image', $this->fetch()));
		//$this->display();
		
		return $this->fetch('Programs:weatherInfo');
	}
	
	/**
	 * 节目权限验证
	 */
	private function checkProgramAccess($progClassID) {
		if (empty($progClassID))
			return false;
		
		$programNodeClassID = 'a69422aa-6077-6385-4ffd-1676c591a4cc';
		
		if (!$_SESSION[C('ADMIN_AUTH_KEY')]) {
			
			$progAccess = M('Access')->where(array('role_id'=>$_SESSION['role'], 'node_id'=>$programNodeClassID))->count();
			if ($progAccess) {
				/*$accessCon = M('AccessCon')->where(array('role_id'=>$_SESSION['role'], 'con_item_classid'=>$progClassID, 'con_type'=>'x86'))->count();
				if ($accessCon) {
					
					return true;
				} else {
					
					return false;
				}*/
				return true;
			} else {
				
				return false;
			}
			
		} else {
			
			return true;
		}
	}
	
    /**
     * 审核节目、资源
     */	
	public function ProgramsCheckIndex() {      
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
		//$treeData = '[{"id":124,"dir_name":"\u6d4b\u8bd5\u6570\u636e","classid":"f7b4cfdf-c730-dcbf-c3ab-bb591df057bb","parent_classid":"","ROW_NUMBER":"1","name":"\u6d4b\u8bd5\u6570\u636e"},{"id":125,"dir_name":"\u97f3\u9891","classid":"e26494dd-8f2c-b837-03d9-c0a6638b1e81","parent_classid":"","ROW_NUMBER":"2","name":"\u97f3\u9891"},{"id":126,"dir_name":"\u89c6\u9891","classid":"9587ac8c-8e74-77f8-6d26-6865461d503f","parent_classid":"","ROW_NUMBER":"3","name":"\u89c6\u9891"},{"id":127,"dir_name":"\u56fe\u7247","classid":"581d4c8c-e60f-ad03-c5d3-87b039f39722","parent_classid":"","ROW_NUMBER":"4","name":"\u56fe\u7247"}]';

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
	
		$this->assign("count_ds",$count_ds);//待审统计
		$this->assign("count_ys",$count_ys);//已审统计
		$this->assign("count_bh",$count_bh);//已驳回统计
	
		$this->assign("treeData",$treeData);
		$this->assign("list",$list);
		$this->display("ProgramsCheckIndex");  
		//$this->display("School/schoolList");
        
    }	
	
	/**
	 * 初始化审核，仅供测试时初始化使用
	 * 将相关表的checked设为null，清空审核日志
	*/
	public function resetCheckLog(){
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
	
	/**
	 * 审核日志
	 * 审核节目、栏目组、栏目、文章、各种资源的系统日志
	*/
    public function checkLogList() {
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
		$this->display("checkLogList");     
    }			
	
		
}