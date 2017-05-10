<?php
/**
 * 班牌组管理控制器
 * @author Skeam TJ
 * 
 */
class EndpointsGroupsAction extends CommonAction {
	
	/**
	 * 班牌组列表
	 */
	public function index() {
		
		$playPlanModel = D("PlayPlan");//播放计划
		$powerPlanModel = D("PowerPlan");//开关机计划
		
		import('ORG.Util.Page');
	
		// 构建查询条件
		$where = array(array('grouptype'=>$_GET['et']));
		/* $name = I('get.name', '', 'strip_tags');
		if (!empty($name)) {
			$where['groupname'] = array('like', '%' . utf82gbk($name) . '%');
		} */
	
		// 分页获取数据
		$epgModel = D('EndpointsGroups');
		/* $totals = $epgModel->where($where)->count();
		$Page = new Page($totals, 10);
		$show = $Page->show();
	
		$groups = $epgModel->where($where)->limit($Page->firstRow, $Page->listRows)->select(); */
		$groups = $epgModel->where($where)->order('level asc, id asc')->select();
		if ($groups) {
			foreach ($groups as $k=> &$g) {
				$g['groupname'] = gbk2utf8($g['groupname']);
				
				$childrenGrps = array();
				$childrenGrps = $epgModel->where("parent_classid='".$g['groupclassid']."'")->find();
				if ($childrenGrps!=false){
					//有下级组时，显示不允许指定播放计划
					$g['tplname']="--";
				}else{
					if (trim($g['tplclassid']) != '') {
						if ($_GET['et'] == 'x86') {
							$g['tplname'] = D('Tpls')->where(array('tplclassid'=>$g['tplclassid']))->getField('tplname');
						} else if ($_GET['et'] == 'azad') {
							$g['tplname'] = D('TBAPlaylists')->where(array('pl_classid'=>$g['tplclassid']))->getField('pl_name');
						} else {
						
						}
					} else {
						$g['tplname'] = '未指定';
					}				
				}
				
				//注意：只有终极分组才能指定播放计划，有下级分组的不允许指定
				$childrenGrps = array();
				$childrenGrps = $epgModel->where("parent_classid='".$g['groupclassid']."'")->find();
				if ($childrenGrps!=false){
					//有下级组时，显示不允许指定播放计划
					$groups[$k]['playPlanName']="--";
				}else{
					//无下级组时
					if ($g['PlayPlanId']){
						//$playPlanModel = D("PlayPlan");
						$tmpDatas = $playPlanModel->where("Id=".$g['PlayPlanId'])->find();
						if ($tmpDatas){
							$g['playPlanName']=$tmpDatas['Name'];
						}else{
							$g['playPlanName']='计划已删除';
						}
						
					}else{
						$g['playPlanName']='未指定';
					}					
				}
				
				//注意：只有终极分组才能指定播放计划，有下级分组的不允许指定
			//	$childrenGrps = array();
			//	$childrenGrps = $epgModel->where("parent_classid='".$g['groupclassid']."'")->find();
				if ($childrenGrps!=false){
					//有下级组时，显示不允许指定播放计划
					$groups[$k]['powerPlanName']="--";
				}else{
					//无下级组时
					if ($g['PowerPlanId']){
						//$playPlanModel = D("PlayPlan");
						$tmpDatas = $powerPlanModel->where("id=".$g['PowerPlanId'])->find();
						if ($tmpDatas){
							$g['powerPlanName']=$tmpDatas['Name'];
							$g['powerPlanType']=$tmpDatas['Type'];
							$g['powerPlanCanPublish']=1;
						}else{
							$g['powerPlanName']='计划已删除';
						}
						
					}else{
						$g['powerPlanName']='未指定';
					}					
				}
			}
			
			$treeGrps = array();
			$epgModel->sortNodes($treeGrps, $groups);
		}
		/* $this->assign('page', $show); */
		$this->assign('groups', $treeGrps);
		$this->display();
	}

	// 页面上，当鼠标从播放计划变更通知的“小红点”上方离开时触发，修改数据库中的isChanged字段为0.
	public function ajax_setIsChanged(){
		if(IS_POST){
			$id = $_POST[id];
			$model = D('EndpointsGroups');
			$data[isChanged] = 0;
			echo $model->where("id=$id")->save($data);
		}
	}
	
	/**
	 * 添加班牌组
	 */
	public function add() {
		if (IS_POST) {
			$this->saveData();
		} else {
			// 获取模板信息
			$tpls = array();
		    if ($_GET['et'] == 'x86') {
    			
		        $tpls = D('Tpls')->field(array('tplclassid, tplname'))->where(array('bevalid'=>1))->select();
		        $adsList = D('Ads')->field(array('adstitle', 'dir_resourceid'))->select();
    			$this->assign('adsList', $adsList);
		        
		    } else if ($_GET['et'] == 'azad' || $_GET['et'] == 'azt') {
		        
		        $plLists =  D('TBAPlaylists')->field(array('pl_classid, pl_name'))->where(array('status'=>1))->select();
		        foreach ($plLists as $pl) {
		            $tpls[] = array('tplclassid'=>$pl['pl_classid'], 'tplname'=>$pl['pl_name']);
		        }
		        
		    } else {
		        
		    }
			
			$epgModel = D('EndpointsGroups');
			$pGrps = $epgModel->where(array('grouptype'=>$_GET['et']))->order('level asc, id asc')->select();
			if ($pGrps) {
			
				$treeGrps = array();
				$epgModel->sortNodes($treeGrps, $pGrps);
				$this->assign('pGrps', $treeGrps);
			}
			
			// 获取班牌类型
			/* $epSorts = D('EndpointsType')->getField('typecode, typename');
			$this->assign('epSorts', $epSorts); */

			//播放计划
			$playPlanModel = D("PlayPlan");
			$playPlans = $playPlanModel->where($map)->field("Id,Name,PlanType,TplType,Resolution")->order("id DESC")->select(); 
			$this->assign('playPlans', $playPlans);
			
			//开关机计划
			$powerPlanModel = D("PowerPlan");//开关机计划
			$map=array();
			$powerPlans = $powerPlanModel->where($map)->field("Id,Name")->order("Id DESC")->select(); 
			$this->assign('powerPlans', $powerPlans);	
						
			$this->assign('tpls', $tpls);
			$this->display('edit');
		}
	}
	
	/**
	 * 修改班牌组信息
	 */
	public function edit() {
		
		if (IS_POST) {
			
			$this->saveData();
		} else {
			
			$id = I('get.id', 0, 'int');
			
			if (!$id)
				$this->error('非法请求！');
			
			// 获取组信息
			$epgModel = D('EndpointsGroups');
			$groupi = $epgModel->where(array('id'=>$id))->find();
			if ($groupi) {
				$groupi['groupname'] = gbk2utf8($groupi['groupname']);
				$childrenGrps = $epgModel->getChildrenGrps($groupi['groupclassid']);
				$this->assign('childrenGrps', $childrenGrps);
				
				$realChildrenGrps = array();
				foreach ($childrenGrps as $child) {
					if ($child !== $groupi['groupclassid']) {
						array_push($realChildrenGrps, $child);
					}
				}
				$this->assign('realChildrenGrps', $realChildrenGrps);
			}
			
			// 获取模板信息
			$tpls = array();
		    if ($_GET['et'] == 'x86') {
    			
		        $tpls = D('Tpls')->field(array('tplclassid, tplname'))->where(array('bevalid'=>1))->select();
		        $adsList = D('Ads')->field(array('adstitle', 'dir_resourceid'))->select();
		        $this->assign('adsList', $adsList);
		        
		        $groupi['adsclassid'] = D('Ads')->where(array('touchendpoint_groupclassid'=>array('like', '%,' . $groupi['groupclassid'] . ',%')))->getField('dir_resourceid');
    			
		    } else if ($_GET['et'] == 'azad' || $_GET['et'] == 'azt') {
		        
		        $plLists =  D('TBAPlaylists')->field(array('pl_classid, pl_name'))->where(array('status'=>1))->select();
		        foreach ($plLists as $pl) {
		            $tpls[] = array('tplclassid'=>$pl['pl_classid'], 'tplname'=>$pl['pl_name']);
		        }
		        
		    } else {
		        
		    }
			$this->assign('tpls', $tpls);
			
			$pGrps = $epgModel->where(array('grouptype'=>$_GET['et'], 'level'=>array('elt', 3)))->order('level asc, id asc')->select();
			if ($pGrps) {
			
				$treeGrps = array();
				$epgModel->sortNodes($treeGrps, $pGrps);
				$this->assign('pGrps', $treeGrps);
			}
			
			// 获取班牌类型
			/* $epSorts = D('EndpointsType')->getField('typecode, typename');
			$this->assign('epSorts', $epSorts); */
			
			//播放计划
			$playPlanModel = D("PlayPlan");
			$map=array();
			$map['TplType'] = $groupi['grouptype'];//x86,azt,azad
			$playPlans = $playPlanModel->where($map)->field("Id,Name,PlanType,TplType,Resolution")->order("id DESC")->select(); //var_dump($playPlans);
			$this->assign('playPlans', $playPlans);
			
			//开关机计划
			$powerPlanModel = D("PowerPlan");//开关机计划
			$map=array();
			$powerPlans = $powerPlanModel->where($map)->field("id,Name,Type")->order("id DESC")->select(); 
			$this->assign('powerPlans', $powerPlans);			
			
			$this->assign('epg', $groupi);
			$this->display();
		}
	}
	
	private function saveData() {
		
		// 检查请求方式
		if (!IS_POST)
			$this->error('非法操作！');
		
		// 过滤用户提交数据
		$id = I('post.id', 0, 'int');
		$gname = trim(I('post.groupname'));
		$tplID = trim(I('post.tplclassid'));
		$adsClassID = trim(I('post.adsclassid'));
		$endType = trim(I('post.endType'));
		$parentClassid = trim(I('post.parent_classid'));
		$PlayPlanId = intval(I('post.PlayPlanId'));
		$PowerPlanId = intval(I('post.PowerPlanId'));
		
		if (empty($gname)) {
			$this->error('组名称必填！');
		}
		
		
		// 构建提交数据
		$epgModel = D('EndpointsGroups');
		$data = array();
		$data['groupname'] = utf82gbk($gname);
		$data['grouptype'] = $endType;
		$data['tplclassid'] = $tplID;
		$data['parent_classid'] = $parentClassid;
		if ($parentClassid == '') {
			$data['level'] = 1;
		} else {
			$data['level'] = $epgModel->where(array('groupclassid'=>$parentClassid))->getField('level') + 1;
			$epgModel->where(array('groupclassid'=>$parentClassid))->setField(array('PlayPlanId'=>0));//父级终端组的播放计划初始化为0
		}
		$data['PlayPlanId'] = $PlayPlanId;//播放计划
		$data['PowerPlanId'] = $PowerPlanId;//开关机计划
		
		$func = '';
		if ($id) {
			$func = 'save';
			$data['id'] = $id;
			$oldGroupInfo = $epgModel->where(array('id'=>$id))->find();
		} else {
			$func = 'add';
			$data['groupclassid'] = generateUniqueID();
		}
		
		$epgRe = $epgModel->$func($data);
		if ($epgRe !== false) {
			if ($id) {
				if ($oldGroupInfo['level']*1 != $data['level']) {
					$epgModel->upChildrenLevel($epgModel->where(array('id'=>$id))->getField('groupclassid'), $data['level']);
				}				
				/* if (trim($oldGroupInfo['tplclassid']) != $tplID) {
					$childrenGrps = $epgModel->getChildrenGrps($oldGroupInfo['groupclassid']);
					$epgModel->where(array('groupclassid'=>array('in', $childrenGrps)))->setField('tplclassid', $tplID);
				} */
			}
			
			if (!empty($parentClassid)) {
				$epgModel->where(array('groupclassid'=>$parentClassid))->setField('tplclassid', '');
			}
			
			// 广告设置处理
			if (!empty($adsClassID)) {
			    
    			$adsModel = D('Ads');
    			$needOper = true;
    			if ($id) {
    			    
    			    $adsInfo = $adsModel->field(array('id', 'dir_resourceid', 'touchendpoint_groupclassid'))->where(array('touchendpoint_groupclassid'=>array('like', '%,' . $oldGroupInfo['groupclassid'] . ',%')))->find();
    			    if ($adsInfo) {
    			        if ($adsInfo['dir_resourceid'] != $adsClassID) {
    			            $tepGrpClassId = str_replace(',' . $oldGroupInfo['groupclassid'] . ',', ',', $adsInfo['touchendpoint_groupclassid']);
        			        $adsModel->where(array('id'=>$adsInfo['id']))->setField('touchendpoint_groupclassid', $tepGrpClassId);
    			        } else {
    			            $needOper = false;
    			        }
    			    }
    			    
    			} 
    			
    			if ($needOper) {
    			    
    			    $grpClassId = $id ? $oldGroupInfo['groupclassid'] : $data['groupclassid'];
    			    $adsGrpInfo = $adsModel->where(array('dir_resourceid'=>$adsClassID))->getField('touchendpoint_groupclassid');
    			    $adsGrpInfo = trim($adsGrpInfo, ',');
    			    $adsModel->where(array('dir_resourceid'=>$adsClassID))->setField('touchendpoint_groupclassid', $adsGrpInfo != '' ? ',' . $adsGrpInfo . ',' . $grpClassId . ',' : ',' . $grpClassId . ',');
    			    
    			}
    			
			}
			
			$this->success('操作成功', U('EndpointsGroups/index', array('et'=>$endType, 'isUpTree'=>1)));
		} else {
			$this->error('操作失败！[原因]：' . $epgModel->getError());
		}
	}
	
	/**
	 * 删除班牌组
	 */
	public function del() {
		$id = I('get.id', 0, 'int');
		
		if (!$id) {
			$this->error('非法操作！');
		}
		
		$epgModel = D('EndpointsGroups');
		$egpInfo = $epgModel->where(array('id'=>$id))->find();
		if (!$egpInfo) {
			$this->error('非法操作！');
		}
		
		// 检查该班牌组是否为空，非空的班牌组不允许直接删除
		$subEpgs = $epgModel->where(array('parent_classid'=>$egpInfo['groupclassid']))->count();
		
		$tendModel = D('Endpoint');
		$egpEnds = $tendModel->where(array('touchEndPoint_GroupClassId'=>$egpInfo['groupclassid']))->count();
		if ($subEpgs >0 || $egpEnds > 0) {
			$this->error('该班牌组非空，不允许删除！');
		}
		
		// 删除班牌组
		$delRe = $epgModel->where(array('id'=>$id))->delete();
		if ($delRe !== false) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！[原因]：' . $epgModel->getError());
		}
	}
}