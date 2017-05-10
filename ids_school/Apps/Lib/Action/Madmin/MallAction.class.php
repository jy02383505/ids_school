<?php
class MallAction extends CommonAction {
    
	
    /**
     * 商铺列表管理
     */
    public function index() {        

        
    }	
	
	/**
	 * 商场列表
	 *　author:zjh
	*/
	public function mallList(){
		$mallModel = D('SCMall');
		
		$map = array();
		$map['hide'] = 0;
		$malls = $mallModel->where($map)->select(); 
		$this->assign('malls', $malls);
		$this->display("mallList");
	}
	
	/**
	 * 商场铺位规划
	 *　author:zjh
	*/
	public function designLayout(){

		$this->display("designLayout");	
	}
	
    /**
     * 商铺分类管理
     */
    public function storeTypes() {
        
        // 层级关系格式化分类数据
        $storeTypeModel = D('SCStoretype');
        $originTypes = $storeTypeModel->order('Pid asc, ID asc')->select();
        $sortedTypes = array();
        $storeTypeModel->sortedTypes($sortedTypes, $originTypes);
        //dump($sortedTypes);
        $this->assign('storeTypes', $sortedTypes);
        $this->display();
    }
    
    /**
     * 添加商铺分类
     */
    public function addStoreType() {
        if (IS_POST) {
            $this->saveStoreTypeData();
        } else {
            
            // 获取父级分类数据
            $storeTypeModel = D('SCStoretype');
            $originTypes = $storeTypeModel->order('Pid asc, ID asc')->select();
            $sortedTypes = array();
            $storeTypeModel->sortedTypes($sortedTypes, $originTypes);
            $this->assign('storeTypes', $sortedTypes);
            
            $this->display('EditStoreType');
        }
    }
    
    /**
     * 修改商铺分类信息
     */
    public function editStoreType() {
        if (IS_POST) {
            $this->saveStoreTypeData();
        } else {
            
            $storeTypeID = I('get.tid', 0, 'int');
            if (!$storeTypeID) {
                $this->error('非法操作！');
            }
            $storeTypeModel = D('SCStoretype');
            $storeTypeInfo = $storeTypeModel->where(array('ID'=>$storeTypeID))->find();
            if (!$storeTypeInfo) {
                $this->error('非法操作！');
            }
            
            $this->assign('storeType', $storeTypeInfo);
            // 获取父级分类数据
            $storeTypeModel = D('SCStoretype');
            $originTypes = $storeTypeModel->order('Pid asc, ID asc')->select();
            $sortedTypes = array();
            $storeTypeModel->sortedTypes($sortedTypes, $originTypes);
            $this->assign('storeTypes', $sortedTypes);
            
            $this->display('EditStoreType');
        }
    }
    
    private function saveStoreTypeData() {

       // 处理表单提交参数
       $parentTypeID = I('post.ptype_id', 0, 'int');
       $typeName = I('post.tName');
       $typeID = I('post.storetype_id', 0, 'int');
	   $sortnum = I('post.sortnum', 0, 'int');
       
       // 实例化分类模型
       $storeTypeModel = D('SCStoretype');
       $data['tName'] = $typeName;
       $data['Pid'] = $parentTypeID;
	   $data['sortnum'] = $sortnum;
       $funcName = '';
       if ($typeID) {
           $data['ID'] = $typeID;
           $funcName = 'save';
       } else {
           $funcName = 'add';
       }
       
       // 执行操作
       $storeTypeResult = $storeTypeModel->$funcName($data);
       if ($storeTypeResult !== FALSE) {
           $this->success('操作成功！', U('Mall/storeTypes'));
       } else {
           $this->error('操作失败！[原因]：' . $storeTypeModel->getError());
       }
    }
    
    /**
     * 删除商铺分类
     */
    public function delStoreType() {
        $storeTypeID = I('get.tid', 0, 'int');
        if (!$storeTypeID) {
            $this->error('非法操作！');
        }
        
        $storeTypeModel = D('SCStoretype');
        $storeTypeInfo = $storeTypeModel->where(array('ID'=>$storeTypeID))->find();
        if (!$storeTypeInfo) {
            $this->error('非法操作！');
        }
        
        // 包含子分类的父级分类不能删除
        $childrenType = $storeTypeModel->where(array('Pid'=>$storeTypeID))->find();
        $childrenStore = D('SCStore')->where(array('type'=>$storeTypeInfo['tName']))->find();
        if ($childrenType || $childrenStore) {
            $this->error('该分类非空，不允许删除！');
        }
        
        $deleteStoretypeResult = $storeTypeModel->where(array('ID'=>$storeTypeID))->delete();
        if ($deleteStoretypeResult !== false) {
           $this->success('操作成功！', U('Mall/storeTypes'));
        } else {
           $this->error('操作失败！[原因]：' . $storeTypeModel->getError());
        }
    }
    
    /**
     * 商铺列表
     */
    public function stores() {
        
        $storeModel = D('SCStore');
        $where = array();
         
        // 构建搜索条件
        $storeName = I('get.sname');
        $spotType = I('get.sc', 0, 'int');//sc=1商铺列表,sc=0点位列表
        $floor = I('get.floor', 0, 'int');
        $typeID = I('get.type_id', 0, 'int');
        $where['spottype'] = array($spotType ? 'eq' : 'neq', 'shop');
        !empty($storeName) && $where['sname'] = array('like', '%' . $storeName . '%');
        !empty($floor) && $where['floor'] = $floor;
        $storeTypeModel = D('SCStoretype');
        if (!empty($typeID)) {
            $inIds = $storeTypeModel->getChildrenTypes($typeID, true);
            $where['type_id'] = array('in', $inIds);
        }
         
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $storeModel->where($where)->count();
        $Page = new Page($totals, 12);
        $show = $Page->show();
        $this->assign('page', $show);
        
        $stores = $storeModel->where($where)->limit($Page->firstRow. ',' .$Page->listRows)->order('Id desc')->select();
		
		$storeClassModel = D('SCStoreClass');
		$brandModel = D('SCBrand');
		//重新组织信息
		foreach ($stores as $k => $v){
			//classid为整数，只有一值时
			//$one = $brandClassModel->where("ID=".$brands[$k]['brandClassId'])->find();
			//$brands[$k]['brandClassName'] = $one['tName'];
			
			//classid为多选，逗号分隔的字符串
			$some = array();
			$classidstr = $stores[$k]['storeClassId'];
			$some = $storeClassModel->where("Id in ($classidstr)")->field("tName")->select();
			
			$result = "";
			if ( ! empty( $some )){
				foreach ($some as $classname){ 
					$result .= "，".$classname["tName"]; 
				} 

			}
			$stores[$k]['storeClassName'] = ltrim($result,"，");
			
			
			//品牌
			$brandIdStr = $stores[$k]['brandId'];
			$tmpArray = array();
			$tmpArray = $brandModel->where("Id in ($brandIdStr)")->field("brandName")->select();
			
			$result = "";
			if ( !empty($tmpArray)){
				foreach ($tmpArray as $brandName){ 
					$result .= "，".$brandName["brandName"]; 
				} 

			}
			
			$stores[$k]['brandName'] = ltrim($result,"，");
		}
		
		
        $this->assign("stores", $stores);
        
        // 获取分类数据
        $originTypes = $storeTypeModel->order('Pid asc, ID asc')->select();
        $sortedTypes = array();
        $storeTypeModel->sortedTypes($sortedTypes, $originTypes);
        $this->assign('storeTypes', $sortedTypes);
        
        // 获取商场相关配置选项
        $mallSyscfgModel = D('SCMall');
        $mallFloors = $mallSyscfgModel->where(array('id'=>1))->getField('floors');
        $this->assign('mallFloors', $mallFloors);
        
        $this->display();
    }
    
    /**
     * 添加商铺
     */
    public function addStore() {
        
        if (IS_POST) {
            $this->saveStoreData();
        } else {
        
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
            
            $this->assign('unid', generateUniqueID());
            
            $templateFile = '';
            switch ($_GET['spottype']) {
                case 'service':	//公共设施
                    $templateFile = 'editPubService';
                    break;
                case 'tep':		//信息班牌
                    $templateFile = 'editTepPosition';
                    $tends = D('Endpoint')->field('touchMainId, touchEndPointName')->order('touchMainId asc')->select();
                    foreach ($tends as &$tep) {
                        if (empty($tep['touchEndPointName'])) {
                            $tep['touchEndPointName'] = ''; 
                        }
                    }
                    $this->assign('tends', $tends);
                    break;
                default:{		//shop
					// 获取分类数据 zjh add
					$storeClassModel = D('SCStoreClass');
					$originTypes = $storeClassModel->order('sortnum asc,Pid asc, ID asc')->select();
					$storeClass = array();
					$storeClassModel->sortedTypes($storeClass, $originTypes);			
					$this->assign('storeClass', $storeClass);//zjh add
					
					//获取商场配置记录
					$mallModel = D("SCMall");
					$map = array();
					$map['id']  = 1;//$map['id']  = array('eq',1);;//注意：此处直接读取ID=1的商场
					$mallConfig = $mallModel->where($map)->find();//
					$mallFloors = $mallConfig['floors'];
					$this->assign("mallFloors",$mallFloors);
					
					//获取品牌列表
					$brandModel = D("SCBrand");
					$brands = $brandModel->field('id,brandName')->where('hide=0')->select();//->order('sortnum asc')
					$this->assign("brands",$brands);
					
					//指定模板
                    $templateFile = 'editStore';										
				}
            } 
            $this->display($templateFile);
        }
        
    }
    
    /**
     * 修改商铺信息
     */
    public function editStore() {
        
        if (IS_POST) {
            $this->saveStoreData();
        } else {
            
            $storeID = I('get.id', 0, 'int');
            if (!$storeID) {
                $this->error('非法操作！');
            }
            
            $storeModel = D('SCStore');
            $storeInfo = $storeModel->where(array('Id'=>$storeID))->find();
			//var_dump($storeInfo);
            if (!$storeInfo) {
                $this->error('非法操作！');
            }
            $medias = D('MediaLib')->where(array('resourceid'=>array('in', array($storeInfo['logo'], $storeInfo['hotico'], $storeInfo['store_video']))))->getField('resourceid, filepath');
            if (!empty($medias)) {
                $storeInfo['note'] = stripslashes($storeInfo['note']);
                $storeInfo['logoPath'] = str_replace('\\', '/', $medias[$storeInfo['logo']]);
                $storeInfo['hoticoPath'] = str_replace('\\', '/', $medias[$storeInfo['hotico']]);
                $storeInfo['storeVideoPath'] = basename(str_replace('\\', '/', $medias[$storeInfo['store_video']]));
            }
            //dump($storeInfo);
			$this->assign('storeInfo', $storeInfo);
			
            // 获取分类数据
           /* $storeTypeModel = D('SCStoretype');
            $originTypes = $storeTypeModel->order('Pid asc, ID asc')->select();
            $sortedTypes = array();
            $storeTypeModel->sortedTypes($sortedTypes, $originTypes);
            $this->assign('storeTypes', $sortedTypes);
			*/
			
            // 获取分类数据 zjh add
            $storeClassModel = D('SCStoreClass');
            $originTypes = $storeClassModel->order('sortnum asc,Pid asc, ID asc')->select();
            $storeClass = array();
            $storeClassModel->sortedTypes($storeClass, $originTypes);			
			$this->assign('storeClass', $storeClass);//zjh add
			//var_dump($storeClass);
            
            // 获取商场相关配置选项
            $mallSyscfgModel = D('SCSyscfg');
            $mallFloors = $mallSyscfgModel->where(array('id'=>1))->getField('floors');
            $this->assign('mallFloors', $mallFloors);
            
            $this->assign("store", $storeInfo);            
            $this->assign('unid', $storeInfo['classid']);
            $templateFile = '';
            switch ($_GET['spottype']) {
                case 'service':
                    $templateFile = 'editPubService';//公共设施
                    break;
                case 'tep'://信息班牌
                    $templateFile = 'editTepPosition';
                    $tends = D('Endpoint')->field('touchMainId, touchEndPointName')->order('touchMainId asc')->select();
                    $this->assign('tends', $tends);
                    break;
                default:
					{
					// 获取分类数据 zjh add
					$storeClassModel = D('SCStoreClass');
					$originTypes = $storeClassModel->order('sortnum asc,Pid asc, ID asc')->select();
					$storeClass = array();
					$storeClassModel->sortedTypes($storeClass, $originTypes);			
					$this->assign('storeClass', $storeClass);//zjh add
					
				//已提交的已选择品牌，在左侧list中显示
				$arr_checked = array();
				$arr_checked = explode(',',$storeInfo['brandId']);
				$brandModel = D('SCBrand');
				if ( !empty( $arr_checked )){
					foreach ($arr_checked as $v){ 
					 $one = $brandModel->where('id='.$v)->field('id,brandName')->find(); 
					 //var_dump($oneClassName);
					 if ($one){//忽略首尾的0
					 	$brand_list[]=array('id'=>$one['id'],'brandName'=>$one['brandName']);
					 }
					} 
				}
				$this->assign('brand_list', $brand_list);//zjh add
				
				
				//已提交的分类，在左侧list中显示
				$arr_checked_sc = array();
				$arr_checked_sc = explode(',',$storeInfo['storeClassId']);
				if ( !empty( $arr_checked_sc )){
					foreach ($arr_checked_sc as $v){ 
					 $one = $storeClassModel->where('ID='.$v)->field('ID,tName')->find(); 
					 //var_dump($oneClassName);
					 if ($one){//忽略首尾的0
					 	$storeClass_list[]=array('ID'=>$one['ID'],'tName'=>$one['tName']);
					 }
					} 
				}
				$this->assign('storeClass_list', $storeClass_list);//zjh add
					
					//获取商场配置记录
					$mallModel = D("SCMall");
					$map = array();
					$map['id']=1;//注意：此处直接读取ID=1的商场
					$mallConfig = $mallModel->where($map)->find();
					$mallFloors = $mallConfig['floors']?$mallConfig['floors']:1;
					$this->assign("mallFloors",$mallFloors);
					
					//获取品牌列表
					$brandModel = D("SCBrand");
					$brands = $brandModel->field('id,brandName')->where('hide=0')->select();//->order('sortnum asc')
					$this->assign("brands",$brands);
					//var_dump($brands);
					
					//指定模板
                    $templateFile = 'editStore';					
					}
            } 
            $this->display($templateFile);
        }
    }
    
    private function saveStoreData() {
        
        // 处理表单提交参数
        $storeID = I('post.store_id', 0, 'int');
        $typeID = I('post.type_id', 0, 'int');
        $storeName = I('post.sname');
		$shortname = I('post.shortname');
        $pinyinShort = I('post.pyshort');
		$storecode = I('post.storecode');
		$systemcode = I('post.systemcode');
        $logoResId = I('post.logo');
        $floor = I('post.floor');
        $address = I('post.address');
		$manager = I('post.manager');
		$contact = I('post.contact');
        $note = trim($_POST['note']);
        $hoticoResId = I('post.hotico');
        $videoResId = I('post.store_video');
        $classid = I('post.classid');
        $storeNo = I('post.store_no');
        $storeShortname = I('post.short_sname');
        $hasLogoAsCover = I('post.has_logo_as_cover', 0, 'int');
        $spotType = I('post.spottype', 'shop');
       
		$storeClassIdStr=$_POST['storeClassIdStr'];//zjh add
		$storeClassId = implode(',',$storeClassIdStr);//zjh add
		
		$brandIdStr=$_POST['brandIdStr'];//zjh add
		$brandId = implode(',',$brandIdStr);	//zjh add	

		
     //   if(empty($typeID)) {
       //     $this->error('操作失败！[原因]：未指定商铺分类！');
      //  }
        
        if (empty($storeName)) {
            $this->error('操作失败！[原因]：商铺名称不能为空！');
        }
	
        
        $storeModel = D('SCStore');// 实例化商铺模型
		
        $data['sname'] = $storeName;
		$data['shortname'] = $shortname;
		$data['storecode'] = $storecode;
		$data['systemcode'] = $systemcode;
        $data['logo'] = $logoResId;
        $data['address'] = $address;
		$data['manager'] = $manager;
		$data['contact'] = $contact;
        $data['type_id'] = $typeID;//将去掉 zjh
        $data['type'] = D('SCStoretype')->where(array('ID'=>$typeID))->getField('tName');//将去掉 zjh 
		$data['storeClassId']=$storeClassId;//zjh add
		$data['brandId']=$brandId;//zjh add
		$data['storeClassName']="";//zjh add 它将是分类一，分类二，分类三，分类四"
		$data['brandName']="";//zjh add 它将是"品牌一，品牌二，品牌三"
        $data['note'] = $note;
        $data['floor'] = $floor;
        $data['pyshort'] = $pinyinShort;
        $data['hotico'] = $hoticoResId;
        $data['store_no'] = $storeNo;
        $data['short_sname'] = $storeShortname;
        $data['store_cover'] = $hasLogoAsCover ? $logoResId : '';
        $data['store_video'] = $videoResId;
        $data['spottype'] = $spotType;

//var_dump($data);exit;
        // 执行操作
        //$storeResult = null;
        if ($storeID) {
            //$oldStoreResInfo = $storeModel->where(array('Id'=>$storeID))->find();
            $storeResult = $storeModel->where(array('Id'=>$storeID))->save($data);
			if ($storeResult){
				$this->success('操作成功！', U('Mall/stores', array('sc'=>$spotType == 'shop' ? 1 : 0)));
			} else {
				$this->error('操作失败！[原因]：' . $storeModel->getDBError());	
			}
        } else {
            $data['classid'] = $classid;
            $storeResult = $storeModel->add($data);
			if ($storeResult){
				$this->success('操作成功！', U('Mall/stores', array('sc'=>$spotType == 'shop' ? 1 : 0)));
			} else {
				$this->error('操作失败！[原因]：' . $storeModel->getDBError());	
			}
			            
        }
		
		
        
		/*
        if ($storeResult !== false) {
            
            // 处理上传产生的冗余图片
            $tmpModel = M('ProgramsArticlesTemp');
            $mediaLibModel = D('MediaLib');
            $tmpIds = $tmpModel->where(array('tmp_article_classid'=>$classid, 'act'=>array('in', array('logo', 'hotico', 'video'))))->getField('res_id', true);
            if (count($tmpIds) > 0) {
                $oldIcoIds = $mediaLibModel->where(array('resourceid'=>array('in', array($oldStoreResInfo['logo'], $oldStoreResInfo['hotico'], $oldStoreResInfo['store_video']))))->getField('id', true);
                if ($oldIcoIds > 0) {
                    $tmpIds = array_unique(array_merge($tmpIds, $oldIcoIds));
                }
                $mediaLibModel->deleteMediaByParams(array('id'=>array('in', $tmpIds), 'resourceid'=>array('not in', array($logoResId, $hoticoResId, $videoResId))));
            }
            $tmpModel->where(array('tmp_article_classid'=>$classid))->delete();
            
           $this->success('操作成功！', U('Mall/stores', array('sc'=>$spotType == 'shop' ? 1 : 0)));
        } else {
           $this->error('操作失败！[原因]：' . $storeModel->getDBError());
        }
		*/
    }
    
    /**
     * 删除商铺
     */
    public function delStore() {
        $storeID = I('get.id', 0, 'int');
        if (!$storeID) {
            $this->error('非法操作！');
        }
        
        $storeModel = D('SCStore');
        $storeInfo = $storeModel->where(array('Id'=>$storeID))->find();
        if (!$storeInfo) {
            $this->error('非法操作！');
        }
        $this->assign('store', $storeInfo);
        
        // 不允许删除的条件一：关联了活动不能删除
        $hasActivities = D('SCActivities')->where(array('storeid'=>$storeID))->count();
        if ($hasActivities > 0) {
            $this->error('操作失败！[原因]：该商铺已关联了活动内容，不允许删除！');
        }
        
        // 不允许删除的条件一：关联了节目栏目不能删除
        if (!empty($storeInfo['program_dir_classid']) && M('ProgramsDirs')->where(array('classid'=>$storeInfo['program_dir_classid']))->find()) {
            $this->error('操作失败！[原因]：该商铺已关联到节目栏目，不允许删除！');
        }
        
        // 如果删除成功了，需要执行的垃圾处理操作：删除该商铺相关的媒体资源（主要指图片）
        $deleteResult = $storeModel->where(array('Id'=>$storeID))->delete();
        if ($deleteResult !== false) {
            
            $resourceIds = array();
            if (!empty($storeInfo['logo'])) {
                array_push($resourceIds, $storeInfo['logo']);
            }
            
            if (!empty($storeInfo['hotico'])) {
                array_push($resourceIds, $storeInfo['hotico']);
            }
            
            if (!empty($storeInfo['store_video'])) {
                array_push($resourceIds, $storeInfo['store_video']);
            }
            
            if (!empty($storeInfo['wx_erweima'])) {
                array_push($resourceIds, $storeInfo['wx_erweima']);
            }
            
            if(!empty($storeInfo['figurechart'])) {
                $resourceIds = array_merge($resourceIds, explode(',', trim($storeInfo['figurechart'], ',')));
            }
            
            D('MediaLib')->deleteMediaByParams(array('resourceid'=>array('in', $resourceIds)));
            deldir(rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/mall/' . $storeInfo['classid']);
            
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！[原因]：' . $storeModel->getError());
        }
    }
    
    /**
     * 商铺相册管理：可上传商铺形象照片
     */
    public function storeGallery() {
        
        $storeID = I('get.id', 0, 'int');
        if (!$storeID) {
            $this->error('非法操作！');
        }
        
        $storeModel = D('SCStore');
        $storeInfo = $storeModel->where(array('Id'=>$storeID))->find();
        if (!$storeInfo) {
            $this->error('非法操作！');
        }
        $this->assign('store', $storeInfo);
        
        $gallery = array();
        $figurechart = explode(',', trim($storeInfo['figurechart'], ','));
        if (!empty($figurechart)) {
            $gallery = D('MediaLib')->where(array('resourceid'=>array('in', $figurechart)))->field('id, resourceid, filepath')->order('id asc')->select();
        }
        
        $this->assign('gallery', $gallery);
        $this->display();
    }
    
    /**
     * 为商铺绑定一个微信账号
     */
    public function storeWeixin() {
        if (IS_POST) {
            $storeID = I('post.store_id', 0, 'int');
            $wxID = I('post.wx_id');
            $wxErweima = I('post.wx_erweima');
            if (!$storeID) {
                $this->error('非法操作！');
            }

            $storeModel = D('SCStore');
            $data = array();
            // 注解：商铺绑定微信号的操作是开关性功能，输入微信号则是绑定，置空则是解绑，输入新的微信号则是更新绑定。
            if (empty($wxID)) {
                $data['wx_id'] = '';
                $data['wx_erweima'] = '';
                $storeInfo = $storeModel->where(array('Id'=>$storeID))->find();
                if (!empty($storeInfo['wx_erweima'])) {
                    D('MediaLib')->deleteMediaByParams(array('resourceid'=>$storeInfo['wx_erweima']));
                }
            } else {
                $data['wx_id'] = $wxID;
                $data['wx_erweima'] = $wxErweima;
            }
            
            $bindWXResult = $storeModel->where(array('Id'=>$storeID))->save($data);
            if ($bindWXResult !== false) {
                $this->success('操作成功！');
            } else {
                $this->error('操作失败！[原因]：' . $storeModel->getError());
            }
            
        } else {
            $storeID = I('get.id', 0, 'int');
            if (!$storeID) {
                $this->error('非法操作！');
            }
            
            $storeModel = D('SCStore');
            $storeInfo = $storeModel->where(array('Id'=>$storeID))->find();
            if (!$storeInfo) {
                $this->error('非法操作！');
            }
            
            if (!empty($storeInfo['wx_erweima'])) {
                $filepath = D('MediaLib')->where(array('resourceid'=>$storeInfo['wx_erweima']))->getField('filepath');
                $storeInfo['wx_img'] = str_replace('\\', '/', $filepath);
            }
            
            $this->assign('store', $storeInfo);
            $this->display();
        }
    }
    

    

    /**
     * 商场基本信息以及应用配置选项
     */
    public function mallSyscfg() {
        $mallSyscfgModel = D('SCSyscfg');
        $mallSyscfgInfo = $mallSyscfgModel->where(array('id'=>1))->find();
        if (IS_POST) {
            $mallID = I('post.mall_id', 0, 'int');
            $data['sc_name'] = I('post.sc_name');
            $data['sc_short_name'] = I('post.sc_short_name');
            $data['sc_logo'] = I('post.sc_logo');
            $data['floors'] = I('post.floors', 0, 'int');
            $data['address'] = I('post.address');
            $data['sc_manager'] = I('post.sc_manager');
            $data['contact'] = I('post.contact');
            $data['note'] = trim($_POST['note']);
            $data['hotico'] = I('post.hotico');
            
            $result = false;
            if ($mallID) {
                $result = $mallSyscfgModel->where(array('id'=>$mallID))->save($data);
            } else {
                $result = $mallSyscfgModel->add($data);
            }
            
            if ($result !== false) {
                
                $where = array();
                if ($mallSyscfgInfo['sc_logo'] != $data['sc_logo']) array_push($where, $mallSyscfgInfo['sc_logo']);
                if ($mallSyscfgInfo['hotico'] != $data['hotico']) array_push($where, $mallSyscfgInfo['hotico']);
                if (!empty($where)) {
                    D('MediaLib')->deleteMediaByParams(array('resourceid'=>array('in', $where)));
                }
                
                $this->success('操作成功！');
            } else {
                $this->error('操作失败！[原因]：' . $mallSyscfgModel->getError());
            }
            
        } else {
            
            if ($mallSyscfgInfo) {
                $mallSyscfgInfo['note'] = stripslashes($mallSyscfgInfo['note']);
                $medias = D('MediaLib')->where(array('resourceid'=>array('in', array($mallSyscfgInfo['sc_logo'], $mallSyscfgInfo['hotico']))))->getField('resourceid, filepath');
                if (!empty($medias)) {
                    $mallSyscfgInfo['logoPath'] = str_replace('\\', '/', $medias[$mallSyscfgInfo['sc_logo']]);
                    $mallSyscfgInfo['hoticoPath'] = str_replace('\\', '/', $medias[$mallSyscfgInfo['hotico']]);
                }
            }
            
            $this->assign('mall', $mallSyscfgInfo);
            $this->display();
        }
    }
	
	
	/**
	 * 添加商场
	 * author:zjh
	*/
     public function addMall() {
		
        if (IS_POST) {
			$Model = D('SCMall');
            $id = I('post.id', 0, 'int');
			$data['id'] = $id;
            $data['mallName'] = I('post.mallName');
            $data['mallShortName'] = I('post.mallShortName');
            $data['logo'] = I('post.logo');
			$data['mainImage'] = I('post.mainImage');
            $data['floors'] = I('post.floors', 0, 'int');
            $data['address'] = I('post.address');
            $data['positionX'] = I('post.positionX');
            $data['positionY'] = I('post.positionY');
			$data['mapType'] = I('post.mapType');
			$data['contactPerson'] = I('post.contactPerson');
			$data['contactInfo'] = I('post.contactInfo');
			$data['mainVideo'] = I('post.mainVideo');
			$data['mainContent'] = I('post.mainContent');
			$result = $Model->data($data)->add();

		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('Mall/mallList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }

        } else {
            
            // 获取
            $this->display("editMall");
        }
		 
		
	}
	
	/**
	 * 编缉商场	
	 * author:zjh
	*/
    public function editMall() {
        
        if (IS_POST) {
			$mallModel = D('SCMall');
            $id = I('post.id', 0, 'int');
			$data['id'] = $id;
            $data['mallName'] = I('post.mallName');
            $data['mallShortName'] = I('post.mallShortName');
            $data['logo'] = I('post.logo');
			$data['mainImage'] = I('post.mainImage');
            $data['floors'] = I('post.floors', 0, 'int');
            $data['address'] = I('post.address');
            $data['positionX'] = I('post.positionX');
            $data['positionY'] = I('post.positionY');
			$data['mapType'] = I('post.mapType');
			$data['contactPerson'] = I('post.contactPerson');
			$data['contactInfo'] = I('post.contactInfo');
			$data['mainVideo'] = I('post.mainVideo');
			$data['mainContent'] = I('post.mainContent');
            
            $result = $mallModel->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('Mall/mallList'));
			} else {
			   $this->error('操作失败！[原因]：' . $mallModel->getError());
			}            

            
        } else {
            $id = I('get.id',0,'intval');
			$mallModel = D('SCMall');
			$map['id'] = $id;
			$mallInfo = $mallModel->where($map)->find();
			
            if ($mallInfo) {
				//$mallInfo['mainContent'] = stripslashes($mallInfo['mainContent']);
				$this->assign('mallInfo', $mallInfo);
				$this->display();               
            }

        }
    }	
	
	/**
	 * 删除商场	
	 * author:zjh
	*/
    public function delMall() {
            $id = I('request.id',0,'intval');
			$mallModel = D('SCMall');
			$map['id'] = $id;
			$mallInfo = $mallModel->where($map)->delete();
			
	}
	
	/**
	 * 品牌列表
	 * author:zjh
	*/
     public function brandsList() {
		 
		$brandModal = D("SCBrand");
		$map = array();
		//$map['hide'] = 0;
		
		$keyboard = I('request.keyboard');
		$searchtype = I('request.searchtype');
		$brandClassId = I('request.brandClassId');
		//echo 'brandClassId='.$brandClassId;
	
		if (!empty($keyboard)){
			switch ($searchtype)
			{
			case 'brandName':
				$map['brandName']  = array('LIKE','%'.$keyboard.'%');

				break;  
			case 'id':
				$map['id']  = $keyboard;
				break;
			default:
				//$map['brandName']  = array('LIKE','%'.$keyboard.'%');
			}
			
		} 
		
		if ($brandClassId){
			$map['brandClassId']  = array('LIKE','%,'.$brandClassId.',%');//字段值形如 0,1,2,3,0
		}

        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $brandModal->where($map)->count();
        $Page = new Page($totals, 12);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$brands = $brandModal->where($map)->order('id desc')->select(); 


		$brandClassModel = D('SCBrandClass');
				
		
		//重新组织信息
		foreach ($brands as $k => $v){
			//classid为整数，只有一值时
			//$one = $brandClassModel->where("ID=".$brands[$k]['brandClassId'])->find();
			//$brands[$k]['brandClassName'] = $one['tName'];
			
			//classid为多选，逗号分隔的字符串
			$some = array();
			$classidstr = $brands[$k]['brandClassId'];
			//$map['id'] = array('in',$classidstr);
			$some = $brandClassModel->where("Id in ($classidstr)")->field("tName")->select();
			
			$result = "";
			if ( ! empty( $some )){
				foreach ($some as $classname){ 
					$result .= "，".$classname["tName"]; 
					//var_dump($classname["tName"]);
				} 

			}
			$brands[$k]['brandClassName'] = ltrim($result,"，");
		}
		$this->assign('brands', $brands);
		
		
		//品牌分类
		$brandClassModel = D('SCBrandClass');
		$originTypes = $brandClassModel->order('Pid asc, ID asc')->select();
		$brandClassId = array();
		$brandClassModel->sortedTypes($brandClassId, $originTypes);
		$this->assign('brandClassId', $brandClassId);		
		
		
		
		$this->display("brandsList");		
		
	}
	
	/**
	 * 添加品牌
	 * author:zjh
	*/
     public function addBrand() {
        if (IS_POST) {
			$Model = D('SCBrand');
            $id = I('post.id', 0, 'int');
			//$data['id'] = $id;
			
			$brandClassIdStr=$_POST['brandClassIdStr'];
			$brandClassId = implode(',',$brandClassIdStr);
			$brandClassId = "0,".$brandClassId.",0";
			
            $data['brandName'] = I('post.brandName');
            $data['brandFirstLetter'] = I('post.brandFirstLetter');
            $data['brandPYShortName'] = I('post.brandPYShortName');
			$data['brandClassId'] = $brandClassId;//I('post.brandClassId');
            $data['brandLogo'] = I('post.brandLogo');
            $data['brandMainImage'] = I('post.brandMainImage');
            $data['brandDescription'] = I('post.brandDescription');
            $data['brandContent'] = I('post.brandContent');
			$data['hide'] = I('post.hide');
			$result = $Model->data($data)->add();

		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('Mall/brandsList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }

        } else {
            
			//品牌分类
			$brandClassModel = D('SCBrandClass');
			$originTypes = $brandClassModel->order('Pid asc, ID asc')->select();
			$brandClass = array();
			$brandClassModel->sortedTypes($brandClass, $originTypes);
			$this->assign('brandClass', $brandClass);
			
			
            $this->display("editBrand");
        }
	}

	/**
	 * 修改品牌
	 * author:zjh
	*/
     public function editBrand() {
        if (IS_POST) {
			$brandModel = D('SCBrand');
            $id = I('request.id', 0, 'int');
			//$brandClassId = I('post.brandClassId');
			
			
			$brandClassIdStr=$_POST['brandClassIdStr'];
			$brandClassId = implode(',',$brandClassIdStr);
			$brandClassId = "0,".$brandClassId.",0";
			//var_dump($brandClassIdStr);
			//exit;			
			
			
			$data['id'] = $id;
            $data['brandName'] = I('post.brandName');	 
            $data['brandFirstLetter'] = strtoupper(I('post.brandFirstLetter'));
            $data['brandPYShortName'] = I('post.brandPYShortName');
			$data['brandClassId'] = $brandClassId;//I('post.brandClassId');
			$data['brandClassName'] = D('SCBrandClass')->where(array('ID'=>$brandClassId))->getField('tName');
            $data['brandLogo'] = I('post.brandLogo');
            $data['brandMainImage'] = I('post.brandMainImage');
            $data['brandDescription'] = I('post.brandDescription');
            $data['brandContent'] = htmlspecialchars(stripslashes(I('post.brandContent')));
			$data['hide'] = I('post.hide');
			//$data['brandContent'] = str_replace("&lt;","<",I('post.brandContent'));//I('post.brandContent');
			//$data['brandContent'] = str_replace("&gt;",">",I('post.brandContent'));

			
			$result = $brandModel->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('Mall/brandsList'));
			} else {
			   $this->error('操作失败！[原因]：' . $brandModel->getError());
			}            

            
        } else {		 
		 
			$id = I('get.id',0,'intval');
			$brandModel = D('SCBrand');
			$map['id'] = $id;
			$brandInfo = $brandModel->where($map)->find();
			
			
			if ($brandInfo) {
				//$brandInfo['brandContent'] = str_replace("&lt;","<",$brandInfo['brandContent']);
				//$brandInfo['brandContent'] = str_replace("&gt;",">",$brandInfo['brandContent']);
				
				//品牌分类
				$brandClassModel = D('SCBrandClass');
				$originTypes = $brandClassModel->order('Pid asc, ID asc')->select();
				$brandClass = array();
				$brandClassModel->sortedTypes($brandClass, $originTypes);
				$this->assign('brandClass', $brandClass);
				
				//已提交的分类，在左侧list中显示
				$arr_checked = array();
				$arr_checked = explode(',',$brandInfo['brandClassId']);
				if ( !empty( $arr_checked )){
					foreach ($arr_checked as $v){ 
					 $one = $brandClassModel->where('ID='.$v)->field('ID,tName')->find(); 
					 //var_dump($oneClassName);
					 if ($one){//忽略首尾的0
					 	$brandClass_list[]=array('ID'=>$one['ID'],'tName'=>$one['tName']);
					 }
					} 
				}
				
				//var_dump($brandClass_list);
				$this->assign('brandClass_list', $brandClass_list);
				
				//$mallInfo['mainContent'] = stripslashes($mallInfo['mainContent']);
				$this->assign('brandInfo', $brandInfo);
				$this->display("editBrand");           
			}else{
				//错误	
			}
		}
	}	
	
	/**
	 * 删除品牌
	 * author:zjh
	*/
	public function delBrand()	{
        $id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $brandModel = D('SCBrand');
        $brandInfo = $brandModel->where(array('id'=>$id))->find();
        if (!$brandInfo) {
            $this->error('非法操作，不存在该品牌！');
        }
        
        
        $delResult = $brandModel->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('Mall/brandsList'));
        } else {
           $this->error('操作失败！[原因]：' . $brandModel->getError());
        }	
	}
	
	/**
	 * 品牌分类列表
	 * author:zjh
	*/
     public function brandClass() {
        // 层级关系格式化分类数据
        $brandClassModel = D('SCBrandClass');
        $originTypes = $brandClassModel->order('sortnum asc,Pid asc, ID asc')->select();
        $sortedTypes = array();
        $brandClassModel->sortedTypes($sortedTypes, $originTypes);
        //dump($sortedTypes);
        $this->assign('storeTypes', $sortedTypes);

		$this->display("brandClass");
	}	
	
	
	/**
	 * 添加品牌分类
	 * author:zjh
	*/
    public function addBrandClass() {
        if (IS_POST) {
			// 处理表单提交参数
			$tName = I('post.tName');
			$Pid = I('post.Pid', 0, 'int');
			$sortnum = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$brandClassModel = D('SCBrandClass');
			$data['tName'] = $tName;
			$data['Pid'] = $Pid;
			$data['sortnum'] = $sortnum;
			
			// 执行操作
			$result = $brandClassModel->data($data)->add();
			if ($result !== FALSE) {
				$this->success('操作成功！', U('Mall/brandClass'));
			} else {
				$this->error('操作失败！[原因]：' . $brandClassModel->getError());
			}
        } else {
			
			$classInfo = array();
			$classInfo['sortnum'] = 0;
			$this->assign('classInfo', $classInfo);
            
            // 获取父级分类数据
            $brandClassModel = D('SCBrandClass');
            $originTypes = $brandClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $brandClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
			

            
            $this->display('editBrandClass');
        }
    }	
	
	public function editBrandClass(){
        if (IS_POST) {
			// 处理表单提交参数
			$id = I('post.id', 0, 'int');
			$tName = I('post.tName');
			$Pid = I('post.Pid', 0, 'int');
			$sortnum = I('post.sortnum', 0, 'int');
			
			
			
			
			// 实例化分类模型
			$brandClassModel = D('SCBrandClass');
			
			$data['tName'] = $tName;
			$data['Pid'] = $Pid;
			$data['sortnum'] = $sortnum;
			
			
			
			$map=array();
			$map['ID']=$id;
            $result = $brandClassModel->where($map)->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('Mall/brandClass'));
			} else {
			   $this->error('操作失败！[原因]：' . $brandClassModel->getError());
			}            
       

			
			
        } else {
            
            $id = I('get.tid', 0, 'int');
            if (!$id) {
                $this->error('非法操作！');
            }
            $brandClassModel = D('SCBrandClass');
            $classInfo = $brandClassModel->where(array('ID'=>$id))->find();
            if (!$classInfo) {
                $this->error('非法操作！');
            }
            
            $this->assign('classInfo', $classInfo);
			
            // 获取父级分类数据
            $brandClassModel = D('SCBrandClass');
            $originTypes = $brandClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $brandClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editBrandClass');
        }
	}
	
	/**
	 * 删除品牌分类
	 * author:zjh
	*/
    public function delBrandClass() {
        $id = I('get.tid', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $brandClassModel = D('SCBrandClass');
        $brandClassInfo = $brandClassModel->where(array('ID'=>$id))->find();
        if (!$brandClassInfo) {
            $this->error('非法操作，不存在该品牌！');
        }
 
         // 包含子分类的父级分类不能删除
        $childrenClass = $brandClassModel->where(array('Pid'=>$id))->find();//有下级分类
        if ($childrenClass) {
            $this->error('该分类有子分类，不允许删除，请先删除下级分类！');
        }   
        $childrenBrand = D('SCBrand')->where(array('brandClassName'=>$brandClassInfo['tName']))->find();//分类下有品牌
        if ($childrenClass) {
            $this->error('该分类下有品牌，请先将品牌从此分类下移除！');
        }   		
		/*
        if ($childrenClass || $childrenBrand) {
            $this->error('该分类非空，不允许删除！');
        }*/
        
        $delResult = $brandClassModel->where(array('ID'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('Mall/brandClass'));
        } else {
           $this->error('操作失败！[原因]：' . $brandClassModel->getError());
        }	
		
		
		
//////////////
/*
        $storeTypeID = I('get.tid', 0, 'int');
        if (!$storeTypeID) {
            $this->error('非法操作！');
        }
        
        $storeTypeModel = D('SCStoretype');
        $storeTypeInfo = $storeTypeModel->where(array('ID'=>$storeTypeID))->find();
        if (!$storeTypeInfo) {
            $this->error('非法操作！');
        }
        
        // 包含子分类的父级分类不能删除
        $childrenType = $storeTypeModel->where(array('Pid'=>$storeTypeID))->find();
        $childrenStore = D('SCStore')->where(array('type'=>$storeTypeInfo['tName']))->find();
        if ($childrenType || $childrenStore) {
            $this->error('该分类非空，不允许删除！');
        }
        
        $deleteStoretypeResult = $storeTypeModel->where(array('ID'=>$storeTypeID))->delete();
        if ($deleteStoretypeResult !== false) {
           $this->success('操作成功！', U('Mall/storeTypes'));
        } else {
           $this->error('操作失败！[原因]：' . $storeTypeModel->getError());
        }
*/
///////////		
		
		
		
		
	}
	
	
	/**
	 * 商品列表
	 * author:zjh
	*/
     public function goodsList() {
		$this->display("goodsList");
	}
	
	/**
	 * 添加商品
	 * author:zjh
	*/
     public function addGoods() {
		$this->display("editGoods");
	}	
	
	
	/**
	 * 相册列表
	 * author:zjh
	*/
     public function albumList() {
		$albumModal = D("SCAlbum");
		$map = array();
		//$map['hide'] = 0;
		
		$keyboard = I('request.keyboard');
		$searchtype = I('request.searchtype');
		$albumClassId = I('request.albumClassId');
		if (!empty($keyboard)){
			switch ($searchtype)
			{
			case 'aName':
				$map['aName']  = array('LIKE','%'.$keyboard.'%');

				break;  
			case 'id':
				$map['id']  = $keyboard;
				break;
			default:
				$map['aName']  = array('LIKE','%'.$keyboard.'%');
			}
			
		} 	 
		if ($albumClassId){
			$map['classId']  = $albumClassId;
		}
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $albumModal->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$albums = $albumModal->where($map)->limit($Page->firstRow. ',' .$Page->listRows)->order('id desc')->select(); 

		$albumClassModel = D('SCAlbumClass');
		$resModel = D('SCResources');
		
		//重新组织信息
		foreach ($albums as $k => $v){
			$one = $albumClassModel->where("ID=".$albums[$k]['classId'])->find();
			$albums[$k]['albumClassName'] = $one['tName'];
			
			//封面
			$coverImage = $resModel->where("resourceId='".$albums[$k]['coverImage']."'")->find();
			$albums[$k]['coverImageFile'] = C("UPLOAD_COMM_PATH").$coverImage['resFilepath'];
			//var_dump($albums[$k]['coverImageFile']);
		
		}


				//相册分类
				$albumClassModel = D('SCAlbumClass');
				$originTypes = $albumClassModel->order('Pid asc, ID asc')->select();
				$albumClass = array();
				$albumClassModel->sortedTypes($albumClass, $originTypes);
				$this->assign('albumClassId', $albumClass);	
		
		$this->assign('albums', $albums);
		$this->display("albumList");
	}
	
	/**
	 * 添加相册
	 * author:zjh
	*/
     public function addAlbum() {
		if (IS_POST) { 
			$Model = D('SCAlbum');

			//$data['id'] = $id;
            $data['aName'] = I('post.aName');	 
			$data['classId'] = I('post.classId', 0, 'int');
			//$data['classId'] = '';
			$data['aWidth'] = I('post.aWidth', 0, 'int');
			$data['aHeight'] = I('post.aHeight', 0, 'int');
			//$data['aPhotoCounts'] = 0;
			//$data['coverImage'] = '';
			$data['description'] = I('post.description');	
			//$data['aContent'] = I('post.aContent');
			$data['sortnum'] = I('post.sortnum', 0, 'int');
			
			$data['hide'] = I('post.hide', 0, 'int');
			$data['updateTime'] = time();
			

			$result = $Model->data($data)->add();

		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('Mall/albumList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }
			
			
			//获取表单提交
			
			
			//入库
		}
		else {
			
			
			//相册分类
			$albumClassModel = D('SCAlbumClass');
			$originTypes = $albumClassModel->order('Pid asc, ID asc')->select();
			$albumClass = array();
			$albumClassModel->sortedTypes($albumClass, $originTypes);
			$this->assign('albumClass', $albumClass);			
			
			$this->display("editAlbum");
		}
		
	}		
	
	/**
	 * 修改相册
	 * author:zjh
	*/
     public function editAlbum() {
        if (IS_POST) {
			$albumModel = D('SCAlbum');
            $id = I('request.id', 0, 'int'); 
			
			$data['id'] = $id;
            $data['aName'] = I('post.aName');	 
			$data['classId'] = I('post.classId', 0, 'int');
			//$data['classId'] = '';
			$data['aWidth'] = I('post.aWidth', 0, 'int');
			$data['aHeight'] = I('post.aHeight', 0, 'int');
			//$data['aPhotoCounts'] = 0;
			//$data['coverImage'] = '';
			$data['description'] = I('post.description');	
			//$data['aContent'] = I('post.aContent');
			$data['sortnum'] = I('post.sortnum', 0, 'int');
			
			$data['hide'] = I('post.hide', 0, 'int');
			$data['updateTime'] = time();
						
			$result = $albumModel->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('Mall/albumList'));
			} else {
			   $this->error('操作失败！[原因]：' . $albumModel->getError());
			}  			
			
		} else {
			$id = I('get.id',0,'intval');
			$albumModel = D('SCAlbum');
			$map['id'] = $id;
			$albumInfo = $albumModel->where($map)->find();
			
			if ($albumInfo) {
				//相册分类
				$albumClassModel = D('SCAlbumClass');
				$originTypes = $albumClassModel->order('Pid asc, ID asc')->select();
				$albumClass = array();
				$albumClassModel->sortedTypes($albumClass, $originTypes);
				$this->assign('albumClass', $albumClass);
				
				


				
				//$this->assign('albumInfo', $albumInfo);
				$this->assign('album', $albumInfo);
				$this->display("editAlbum");           
			}else{
				//错误	
			}			
			
		}

		
	}	
		
	/**
	 * 删除相册
	 * author:zjh
	*/
     public function delAlbum() {
		$this->display("editAlbum");
	}		
		
	/**
	 * 添加相片
	 * author:zjh
	*/
     public function addPhotos() {
			$id = I('get.id',0,'intval');
			$albumModel = D('SCAlbum');
			$map = array();
			$map['id'] = $id;
			$albumInfo = $albumModel->where($map)->find();
			if ($albumInfo){
				$this->assign('albumInfo', $albumInfo);
			} 
			
			
			
			//相册的图片列表
			$resModel = D('SCResources');
			$condition = array();
			$condition['id'] = $id;
			$condition['resModel'] = 'ALBUM';
			$condition['resType'] = 'IMAGE';
			$condition['resPid'] = $id;
			
			// 加载数据分页类
			import('ORG.Util.Page');
			
			// 数据分页
			$totals = $resModel->where($condition)->count();
			$Page = new Page($totals, 10);
			$show = $Page->show();
			$this->assign('page', $show);	
			
			$photos = $resModel->where($condition)->limit($Page->firstRow. ',' .$Page->listRows)->order("resId desc")->select();
			if ($photos){
				$this->assign('photos', $photos);
			}
			
		 
		$this->display("editPhotos");
	}	
	
	/**
	 * 删除相册中的一张相片
	 * author:zjh
	*/
     public function delPhoto() {

		$resourceId = trim(I('get.resourceId'));
		if (empty($resourceId)){
			echo json_encode(array("stat"=>"0","msg"=>"参数错误"));
			exit;
		}
		
		
				header("Content-type: text/html; charset=utf-8");
				$result = array();
				$result['stat']="1";
				$result['msg']="删除文件成功";		 
				echo json_encode($result);
				exit;	
		
		/*
		$test = "Uploads/mall/1.jpg";
		if (file_exists($test)) {
				$result = array();
				$result['stat']="1";
				$result['msg']="file had ";
				$result['path']=$test;		
				if ($unlink($test)){
					$result['msg']="file del secuss";
				} else{
					$result['msg']="file del faild";
				}
				echo json_encode($result);
				exit;
			
			exit;
		}else{
				$result = array();
				$result['stat']="1";
				$result['msg']="file no ";
				$result['path']=$test;		 
				echo json_encode($result);
				exit;
		}
		*/
		$resModel = D('SCResources'); // 实例化User对象
		$condition = array();
		$condition['resourceId'] = $resourceId;
		
		$resInfor = $resModel->where($condition)->find();
		if ($resInfor['resId']){
			$filePath = "mall/1.jpg";//stripslashes($resInfor['resFilepath']);
			$uploadRoot = C('UPLOAD_COMM_PATH');
			$filename = __ROOT__.'//'.$uploadRoot.$filePath;
			/*if (file_exists($filePath)) {
				if (!unlink(rtrim(C('UPLOAD_ROOT_PATH'), '/') . '/' . str_replace('\\', '/', $res['filepath']))) {
				
					$result = array();
					$result['stat']="1";
					$result['msg']="删除文件成功";		 
					echo json_encode($result);
					exit;
				}
			} else {
				$result = array();
				$result['stat']="1";
				$result['msg']="file no ";
				$result['path']=stripslashes($resInfor['resFilepath']);		 
				echo json_encode($result);				
				
			}*/
		

			
		} else {
			$result = array();
			$result['stat']="0";
			$result['msg']="查询失败";		 
			echo json_encode($result);
			exit;
			
		}
		
	
		
		// 此处目前仅支持删除本地文件，远程其他待
		$imgPath = "";

		

	//	$resModel->where($condition)->delete(); // 删除id为5的用户数据
		
		/*
		$result = array();
		$result['stat']="1";
		$result['msg']="这是服务端消息";		 
		echo json_encode($result);*/
	 }
	

	/**
	 * 相册分类列表
	 * author:zjh
	*/
     public function albumClass() {
        // 层级关系格式化分类数据
        $albumClassModel = D('SCAlbumClass');
        $originTypes = $albumClassModel->order('sortnum asc,Pid asc, ID asc')->select();
        $sortedTypes = array();
        $albumClassModel->sortedTypes($sortedTypes, $originTypes);
        //dump($sortedTypes);
        $this->assign('storeTypes', $sortedTypes);

		$this->display("albumClass");
	}	
	
	
	/**
	 * 添加相册分类
	 * author:zjh
	*/
    public function addAlbumClass() {
        if (IS_POST) {
			// 处理表单提交参数
			$tName = I('post.tName');
			$Pid = I('post.Pid', 0, 'int');
			$sortnum = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$albumClassModel = D('SCAlbumClass');
			$data['tName'] = $tName;
			$data['Pid'] = $Pid;
			$data['sortnum'] = $sortnum;
			
			// 执行操作
			$result = $albumClassModel->data($data)->add();
			if ($result !== FALSE) {
				$this->success('操作成功！', U('Mall/albumClass'));
			} else {
				$this->error('操作失败！[原因]：' . $albumClassModel->getError());
			}
        } else {
			
			$classInfo = array();
			$classInfo['sortnum'] = 0;
			$this->assign('classInfo', $classInfo);
            
            // 获取父级分类数据
            $albumClassModel = D('SCAlbumClass');
            $originTypes = $albumClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $albumClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
			

            
            $this->display('editAlbumClass');
        }
    }	

	/**
	 * 修改相册分类
	 * author:zjh
	*/	
	public function editAlbumClass(){
        if (IS_POST) {
			// 处理表单提交参数
			$id = I('post.id', 0, 'int');
			$tName = I('post.tName');
			$Pid = I('post.Pid', 0, 'int');
			$sortnum = I('post.sortnum', 0, 'int');
			
			
			
			
			// 实例化分类模型
			$albumClassModel = D('SCAlbumClass');
			
			$data['tName'] = $tName;
			$data['Pid'] = $Pid;
			$data['sortnum'] = $sortnum;
			
			
			
			$map=array();
			$map['ID']=$id;
            $result = $albumClassModel->where($map)->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('Mall/albumClass'));
			} else {
			   $this->error('操作失败！[原因]：' . $albumClassModel->getError());
			}            
       

			
			
        } else {
            
            $id = I('get.tid', 0, 'int');
            if (!$id) {
                $this->error('非法操作！');
            }
            $albumClassModel = D('SCAlbumClass');
            $classInfo = $albumClassModel->where(array('ID'=>$id))->find();
            if (!$classInfo) {
                $this->error('非法操作！');
            }
            
            $this->assign('classInfo', $classInfo);
			
            // 获取父级分类数据
            $albumClassModel = D('SCAlbumClass');
            $originTypes = $albumClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $albumClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editAlbumClass');
        }
	}
	
	/**
	 * 删除相册分类
	 * author:zjh
	*/
    public function delAlbumClass() {
        $id = I('get.tid', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $albumClassModel = D('SCAlbumClass');
        $albumClassInfo = $albumClassModel->where(array('ID'=>$id))->find();
        if (!$albumClassInfo) {
            $this->error('非法操作，不存在该分类！');
        }
 
         // 包含子分类的父级分类不能删除
        $childrenClass = $albumClassModel->where(array('Pid'=>$id))->find();//有下级分类
        if ($childrenClass) {
            $this->error('请先删除下级分类！');
        }   
        $childrenBrand = D('SCBrand')->where(array('albumClassName'=>$albumClassInfo['tName']))->find();//分类下有相册
        if ($childrenClass) {
            $this->error('该分类下有相册，请先将相册从此分类下移除！');
        }   		
		/*
        if ($childrenClass || $childrenBrand) {
            $this->error('该分类非空，不允许删除！');
        }*/
        
        $delResult = $albumClassModel->where(array('ID'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('Mall/albumClass'));
        } else {
           $this->error('操作失败！[原因]：' . $albumClassModel->getError());
        }	

		
		
	}
		   
	

	
	
	
	/**
	 * 铺位规划
	 * author:zjh
	*/
     public function mallLayout() {
		$this->display("mallLayout");
	}		
	   

	/**
	 * 商铺分类列表
	 * author:zjh
	*/
     public function storeClass() {
        // 层级关系格式化分类数据
        $storeClassModel = D('SCStoreClass');
	
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
		$where = array();
		//$where['Pid'] = 0;
        $totals = $storeClassModel->where($where)->count();
        $Page = new Page($totals, 2);
		//分页跳转的时候保证查询条件
		foreach($map as $key=>$val) {
			$Page->parameter[$key] = urlencode($val);
		}
        $show = $Page->show();
        $this->assign('page', $show);
		
        $originTypes = $storeClassModel->order('sortnum asc,Pid asc, ID asc')->select();//->limit($Page->firstRow.','.$Page->listRows)
/*		$tmp=array();
		foreach ($originTypes as $v){ 
			//echo $v['ID'].'<br>';
			$tmp[]=$storeClassModel->getChildrenTypes(3,0); 
		} 
		var_dump($tmp);
*/
		
        $sortedTypes = array();
        $storeClassModel->sortedTypes($sortedTypes, $originTypes);
        //dump($sortedTypes);
        $this->assign('storeTypes', $sortedTypes);

		$this->display("storeClass");
	}	
	
	
	/**
	 * 添加商铺分类
	 * author:zjh
	*/
    public function addStoreClass() {
        if (IS_POST) {
			// 处理表单提交参数
			$tName = I('post.tName');
			$Pid = I('post.Pid', 0, 'int');
			$sortnum = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$storeClassModel = D('SCStoreClass');
			$data['tName'] = $tName;
			$data['Pid'] = $Pid;
			$data['sortnum'] = $sortnum;
			
			// 执行操作
			$result = $storeClassModel->data($data)->add();
			if ($result !== FALSE) {
				$this->success('操作成功！', U('Mall/storeClass'));
			} else {
				$this->error('操作失败！[原因]：' . $storeClassModel->getError());
			}
        } else {
			
			$classInfo = array();
			$classInfo['sortnum'] = 0;
			$this->assign('classInfo', $classInfo);
            
            // 获取父级分类数据
            $storeClassModel = D('SCStoreClass');
            $originTypes = $storeClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $storeClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
			

            
            $this->display('editStoreClass');
        }
    }	

	/**
	 * 修改商铺分类
	 * author:zjh
	*/	
	public function editStoreClass(){
        if (IS_POST) {
			// 处理表单提交参数
			$id = I('post.id', 0, 'int');
			$tName = I('post.tName');
			$Pid = I('post.Pid', 0, 'int');
			$sortnum = I('post.sortnum', 0, 'int');
			
			
			
			
			// 实例化分类模型
			$storeClassModel = D('SCStoreClass');
			
			$data['tName'] = $tName;
			$data['Pid'] = $Pid;
			$data['sortnum'] = $sortnum;
			
			
			
			$map=array();
			$map['ID']=$id;
            $result = $storeClassModel->where($map)->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('Mall/storeClass'));
			} else {
			   $this->error('操作失败！[原因]：' . $storeClassModel->getError());
			}            
       

			
			
        } else {
            
            $id = I('get.tid', 0, 'int');
            if (!$id) {
                $this->error('非法操作！');
            }
            $storeClassModel = D('SCStoreClass');
            $classInfo = $storeClassModel->where(array('ID'=>$id))->find();
            if (!$classInfo) {
                $this->error('非法操作！');
            }
            
            $this->assign('classInfo', $classInfo);
			
            // 获取父级分类数据
            $storeClassModel = D('SCStoreClass');
            $originTypes = $storeClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $storeClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editStoreClass');
        }
	}
	
	/**
	 * 删除商铺分类
	 * author:zjh
	*/
    public function delStoreClass() {
        $id = I('get.tid', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $storeClassModel = D('SCStoreClass');
        $storeClassInfo = $storeClassModel->where(array('ID'=>$id))->find();
        if (!$storeClassInfo) {
            $this->error('非法操作，不存在该分类！');
        }
 
         // 包含子分类的父级分类不能删除
        $childrenClass = $storeClassModel->where(array('Pid'=>$id))->find();//有下级分类
        if ($childrenClass) {
            $this->error('请先删除下级分类！');
        }   
        $childrenBrand = D('SCBrand')->where(array('storeClassName'=>$storeClassInfo['tName']))->find();//分类下有商铺
        if ($childrenClass) {
            $this->error('该分类下有商铺，请先将商铺从此分类下移除！');
        }   		
		/*
        if ($childrenClass || $childrenBrand) {
            $this->error('该分类非空，不允许删除！');
        }*/
        
        $delResult = $storeClassModel->where(array('ID'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('Mall/storeClass'));
        } else {
           $this->error('操作失败！[原因]：' . $storeClassModel->getError());
        }	

		
		
	}
		   
	
	
	
	

    /**
     * 活动列表
	 * author:zjh
     */
    public function activities() {
		$modal = D("SCActivities");
		
		$map = array();
		//$map['hide'] = 0;
		
		$keyboard = I('request.keyboard');
		$searchtype = I('request.searchtype');
		$aClassId = I('request.aClassId');

		if (!empty($keyboard)){
			switch ($searchtype)
			{
			case 'aName':
				$map['aName']  = array('LIKE','%'.$keyboard.'%');
				break;  
			case 'ID':
				$map['ID']  = $keyboard;
				break;
			default:
				$map['aName']  = array('LIKE','%'.$keyboard.'%');
			}
			
		} 
		
		if ($aClassId){
			$map['aClassId']  = $aClassId;
		}
		
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $modal->where($map)->count();
        $Page = new Page($totals, 12);
        $show = $Page->show();
        $this->assign('page', $show);			
		$datas = $modal->where($map)->select(); 
	
		$aClassModel = D('SCActiClass');
				
		
		//重新组织信息
		foreach ($datas as $k => $v){
			$one = $aClassModel->where("ID=".$datas[$k]['aClassId'])->find();
			$datas[$k]['actiClassName'] = $one['tName'];
		}
		$this->assign('datas', $datas);
		
		//品牌分类
		$aClassModel = D('SCActiClass');
		$originTypes = $aClassModel->order('Pid asc, ID asc')->select();
		$aClassId = array();
		$aClassModel->sortedTypes($aClassId, $originTypes);
		$this->assign('aClassId', $aClassId);	
		
		
		
		$this->display("activities");
	}
	
	/**
	 * 添加活动
	 * author:zjh
	*/
    public function addActivities() {
       if (IS_POST) {
			$Model = D('SCActivities');

            $data['aName'] = I('post.aName');	 
			$data['aClassId'] = I('post.aClassId');;//////////////////
			$data['className'] = "";
            $data['mainImage'] = I('post.mainImage');//////////////////////////////
            $data['Starttime'] = I('post.starttime');
            $data['Endtime'] = I('post.endtime');
			
			$data['aContent'] = I('post.aContent');

			$result = $Model->data($data)->add();

		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('Mall/activities'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }

        } else {
            
			//活动分类
			$actiClassModel = D('SCActiClass');
			$originTypes = $actiClassModel->order('Pid asc, ID asc')->select();
			$actiClass = array();
			$actiClassModel->sortedTypes($actiClass, $originTypes);
			$this->assign('actiClass', $actiClass);
			
			
            $this->display("editActivities");
        }		

	}	
	
	/**
	 * 修改活动
	 * author:zjh
	*/
     public function editActivities() {
		 
       if (IS_POST) {
			$actiModel = D('SCActivities');
            $id = I('request.id', 0, 'int');
			
			$data['ID'] = $id;
            $data['aName'] = I('post.aName');	 
			$data['aClassId'] = I('post.aClassId');;//////////////////
			$data['className'] = "";
            $data['mainImage'] = I('post.mainImage');//////////////////////////////
            $data['Starttime'] = I('post.starttime');
            $data['Endtime'] = I('post.endtime');
			
			$data['aContent'] = I('post.aContent');
			
			
			$data['hide'] = I('post.hide');

			

			
			$result = $actiModel->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('Mall/activities'));
			} else {
			   $this->error('操作失败！[原因]：' . $actiModel->getError());
			}            

            
        } else {		 
		 
		 
			//活动分类
			$actiClassModel = D('SCActiClass');
			$originTypes = $actiClassModel->order('Pid asc, ID asc')->select();
			$actiClass = array();
			$actiClassModel->sortedTypes($actiClass, $originTypes);
			$this->assign('actiClass', $actiClass);
		 
			$id = I('get.id',0,'intval');
			$actiModel = D('SCActivities');
			$map['ID'] = $id;
			$actiInfo = $actiModel->where($map)->find();
			
			if ($actiInfo) {
				//活动分类


				$this->assign('actiInfo', $actiInfo);
				$this->display("editActivities");           
			}else{
				//错误	
				 $this->error('操作失败！[原因]：' . $actiModel->getError());
			}
		}
		 
		 
		
	}	
		
	/**
	 * 删除活动
	 * author:zjh
	*/
     public function delActivities() {
		//$this->success('（假的）操作成功！', U('Mall/activities'));
		$id = I('request.ID', 0, 'int');
		
		if (!$id) {
			$this->error('非法操作！');
		}
		$actiModel = D('SCActivities');
		$actiInfo = $actiModel->where(array('ID'=>$id))->find();
		if (!$actiInfo) {
			$this->error('无此信息！');
		}
		
        $delResult = $actiModel->where(array('ID'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('Mall/activities'));
        } else {
           $this->error('操作失败！[原因]：' . $actiClassModel->getError());
        }			
		
		
	}			
			
	
	/**
	 * 活动分类列表
	 * author:zjh
	*/
     public function actiClass() {
        // 层级关系格式化分类数据
        $actiClassModel = D('SCActiClass');
        $originTypes = $actiClassModel->order('sortnum asc,Pid asc, ID asc')->select();
        $sortedTypes = array();
        $actiClassModel->sortedTypes($sortedTypes, $originTypes);
        //dump($sortedTypes);
        $this->assign('storeTypes', $sortedTypes);

		$this->display("actiClass");
	}	
	
	
	/**
	 * 添加活动分类
	 * author:zjh
	*/
    public function addActiClass() {
        if (IS_POST) {
			// 处理表单提交参数
			$tName = I('post.tName');
			$Pid = I('post.Pid', 0, 'int');
			$sortnum = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$actiClassModel = D('SCActiClass');
			$data['tName'] = $tName;
			$data['Pid'] = $Pid;
			$data['sortnum'] = $sortnum;
			
			// 执行操作
			$result = $actiClassModel->data($data)->add();
			if ($result !== FALSE) {
				$this->success('操作成功！', U('Mall/actiClass'));
			} else {
				$this->error('操作失败！[原因]：' . $actiClassModel->getError());
			}
        } else {
			
			$classInfo = array();
			$classInfo['sortnum'] = 0;
			$this->assign('classInfo', $classInfo);
            
            // 获取父级分类数据
            $actiClassModel = D('SCActiClass');
            $originTypes = $actiClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $actiClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            $this->display('editActiClass');
        }
    }	
	
	public function editActiClass(){
        if (IS_POST) {
			// 处理表单提交参数
			$id = I('post.id', 0, 'int');
			$tName = I('post.tName');
			$Pid = I('post.Pid', 0, 'int');
			$sortnum = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$actiClassModel = D('SCActiClass');
			
			$data['tName'] = $tName;
			$data['Pid'] = $Pid;
			$data['sortnum'] = $sortnum;
			
			$map=array();
			$map['ID']=$id;
            $result = $actiClassModel->where($map)->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('Mall/ActiClass'));
			} else {
			   $this->error('操作失败！[原因]：' . $actiClassModel->getError());
			}            
        } else {
            $id = I('get.tid', 0, 'int');
            if (!$id) {
                $this->error('非法操作！');
            }
            $actiClassModel = D('SCActiClass');
            $classInfo = $actiClassModel->where(array('ID'=>$id))->find();
            if (!$classInfo) {
                $this->error('非法操作！');
            }
            
            $this->assign('classInfo', $classInfo);
			
            // 获取父级分类数据
            $actiClassModel = D('SCActiClass');
            $originTypes = $actiClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $actiClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editActiClass');
        }
	}
	
	/**
	 * 删除活动分类
	 * author:zjh
	*/
    public function delActiClass() {
        $id = I('get.tid', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $actiClassModel = D('SCActiClass');
        $brandClassInfo = $actiClassModel->where(array('ID'=>$id))->find();
        if (!$brandClassInfo) {
            $this->error('非法操作，不存在该分类！');
        }
 
         // 包含子分类的父级分类不能删除
        $childrenClass = $actiClassModel->where(array('Pid'=>$id))->find();//有下级分类
        if ($childrenClass) {
            $this->error('该分类有子分类，不允许删除，请先删除下级分类！');
        }   
        $childrenBrand = D('SCBrand')->where(array('brandClassName'=>$brandClassInfo['tName']))->find();//分类下有活动
        if ($childrenClass) {
            $this->error('该分类下有活动，请先将活动从此分类下移除！');
        }   		
		/*
        if ($childrenClass || $childrenBrand) {
            $this->error('该分类非空，不允许删除！');
        }*/
        
        $delResult = $actiClassModel->where(array('ID'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('Mall/actiClass'));
        } else {
           $this->error('操作失败！[原因]：' . $actiClassModel->getError());
        }	
	}	
	
	

	
	/**
	 * 公共设施分类列表
	 * author:zjh
	*/
     public function utilityClass() {
        // 层级关系格式化分类数据
        $utilityClassModel = D('SCUtilityClass');
        $originTypes = $utilityClassModel->order('sortnum asc,Pid asc, ID asc')->select();
        $sortedTypes = array();
        $utilityClassModel->sortedTypes($sortedTypes, $originTypes);
        //dump($sortedTypes);
        $this->assign('storeTypes', $sortedTypes);

		$this->display("utilityClass");
	}	
	
	
	/**
	 * 添加公共设施分类
	 * author:zjh
	*/
    public function addUtilityClass() {
        if (IS_POST) {
			// 处理表单提交参数
			$tName = I('post.tName');
			$Pid = I('post.Pid', 0, 'int');
			$sortnum = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$utilityClassModel = D('SCUtilityClass');
			$data['tName'] = $tName;
			$data['Pid'] = $Pid;
			$data['sortnum'] = $sortnum;
			
			// 执行操作
			$result = $utilityClassModel->data($data)->add();
			if ($result !== FALSE) {
				$this->success('操作成功！', U('Mall/utilityClass'));
			} else {
				$this->error('操作失败！[原因]：' . $utilityClassModel->getError());
			}
        } else {
			
			$classInfo = array();
			$classInfo['sortnum'] = 0;
			$this->assign('classInfo', $classInfo);
            
            // 获取父级分类数据
            $utilityClassModel = D('SCUtilityClass');
            $originTypes = $utilityClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $utilityClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            $this->display('editUtilityClass');
        }
    }	
	
	/**
	 * 修改公共设施分类
	*/
	public function editUtilityClass(){
        if (IS_POST) {
			// 处理表单提交参数
			$id = I('post.id', 0, 'int');
			$tName = I('post.tName');
			$Pid = I('post.Pid', 0, 'int');
			$sortnum = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$utilityClassModel = D('SCUtilityClass');
			
			$data['tName'] = $tName;
			$data['Pid'] = $Pid;
			$data['sortnum'] = $sortnum;
			
			$map=array();
			$map['ID']=$id;
            $result = $utilityClassModel->where($map)->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('Mall/UtilityClass'));
			} else {
			   $this->error('操作失败！[原因]：' . $utilityClassModel->getError());
			}            
        } else {
            $id = I('get.tid', 0, 'int');
            if (!$id) {
                $this->error('非法操作！');
            }
            $utilityClassModel = D('SCUtilityClass');
            $classInfo = $utilityClassModel->where(array('ID'=>$id))->find();
            if (!$classInfo) {
                $this->error('非法操作！');
            }
            
            $this->assign('classInfo', $classInfo);
			
            // 获取父级分类数据
            $utilityClassModel = D('SCUtilityClass');
            $originTypes = $utilityClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $utilityClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editUtilityClass');
        }
	}
	
	/**
	 * 删除公共设施分类
	 * author:zjh
	*/
    public function delUtilityClass() {
        $id = I('get.tid', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $utilityClassModel = D('SCUtilityClass');
        $brandClassInfo = $utilityClassModel->where(array('ID'=>$id))->find();
        if (!$brandClassInfo) {
            $this->error('非法操作，不存在该分类！');
        }
 
         // 包含子分类的父级分类不能删除
        $childrenClass = $utilityClassModel->where(array('Pid'=>$id))->find();//有下级分类
        if ($childrenClass) {
            $this->error('该分类有子分类，不允许删除，请先删除下级分类！');
        }   
        $childrenBrand = D('SCBrand')->where(array('brandClassName'=>$brandClassInfo['tName']))->find();//分类下有活动
        if ($childrenClass) {
            $this->error('该分类下有活动，请先将活动从此分类下移除！');
        }   		
		/*
        if ($childrenClass || $childrenBrand) {
            $this->error('该分类非空，不允许删除！');
        }*/
        
        $delResult = $utilityClassModel->where(array('ID'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('Mall/utilityClass'));
        } else {
           $this->error('操作失败！[原因]：' . $utilityClassModel->getError());
        }	
	}	
	 

	

    /**
     * 公共设施列表
	 * author:zjh
     */
    public function utilities() {
		$modal = D("SCUtilities");
		
		$map = array();
		$map['spottype'] = I('request.spottype');
		if ( empty($map['spottype']) ) {
			$map['spottype'] = "service";
		}
		
		$keyboard = I('request.keyboard');
		$searchtype = I('request.searchtype');
		$utilityClassId = I('request.utilityClassId');
		$floor = I('request.floor');

		if (!empty($keyboard)){
			switch ($searchtype)
			{
			case 'aName':
				$map['aName']  = array('LIKE','%'.$keyboard.'%');
				break;  
			case 'id':
				$map['id']  = $keyboard;
				break;
			default:
				$map['aName']  = array('LIKE','%'.$keyboard.'%');
			}
			
		} 
		
		if ($utilityClassId){
			$map['utilityClassId']  = $utilityClassId;
		}
		if ($floor){
			$map['floor']  = $floor;
		}		
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $modal->where($map)->count();
        $Page = new Page($totals, 12);
        $show = $Page->show();
        $this->assign('page', $show);			
		$datas = $modal->where($map)->select(); 
	
		$aClassModel = D('SCUtilityClass');
		
		//重新组织信息
		foreach ($datas as $k => $v){
			$one = $aClassModel->where("ID=".$datas[$k]['utilityClassId'])->find();
			$datas[$k]['utiClassName'] = $one['tName'];
		}
		$this->assign('datas', $datas);
		
		//公共设施分类
		$utilityClassModel = D('SCUtilityClass');
		$originTypes = $utilityClassModel->order('Pid asc, ID asc')->select();
		$utilityClass = array();
		$utilityClassModel->sortedTypes($utilityClass, $originTypes);
		$this->assign('utilityClass', $utilityClass);	
		
		//获取商场配置记录
		$mallModel = D("SCMall");
		$map = array();
		$map['id']  = 1;//$map['id']  = array('eq',1);;//注意：此处直接读取ID=1的商场
		$mallConfig = $mallModel->where($map)->find();//
		$mallFloors = $mallConfig['floors'];
		$this->assign("mallFloors",$mallFloors);		
		
		$this->display("utilities");
	}
	
	/**
	 * 添加公共设施
	 * author:zjh
	*/
    public function addUtilities() {
       if (IS_POST) {
			$Model = D('SCUtilities');

            $data['tName'] = I('post.tName');	 
			$data['utilityClassId'] = I('post.utilityClassId');;//////////////////
            $data['pyShortName'] = strtoupper(I('post.pyShortName'));//////////////////////////////
			if (!empty($data['pyShortName'])){
				$data['pyFirstLetter'] = substr( $data['pyShortName'], 0, 1 );
				$data['pyFirstLetter'] = strtoupper($data['pyFirstLetter']);
			}
            $data['floor'] = I('post.floors');
            $data['address'] = I('post.address');			
			$data['introduction'] = I('post.introduction');
			$data['logo'] = I('post.logo');
			$data['code'] = I('post.code');
			$data['terminal'] = I('post.terminal');
			$data['spottype'] = I('post.spottype');//公共设施？信息班牌?其它？
			$data['hide'] = I('post.hide');

			$result = $Model->data($data)->add();

		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('Mall/utilities',array('spottype'=>I('post.spottype'))));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }

        } else {
            
			//公共设施分类
			$utilityClassModel = D('SCUtilityClass');
			$originTypes = $utilityClassModel->order('Pid asc, ID asc')->select();
			$utilityClass = array();
			$utilityClassModel->sortedTypes($utilityClass, $originTypes);
			$this->assign('utilityClass', $utilityClass);
			
			//公共设施点位班牌
			$tends = D('Endpoint')->field('touchMainId, touchEndPointName')->order('touchMainId asc')->select();
			foreach ($tends as &$tep) {
				if (empty($tep['touchEndPointName'])) {
					$tep['touchEndPointName'] = ''; 
				}
			}
			$this->assign('tends', $tends);
	

			//获取商场配置记录
			$mallModel = D("SCMall");
			$map = array();
			$map['id']  = 1;//$map['id']  = array('eq',1);;//注意：此处直接读取ID=1的商场
			$mallConfig = $mallModel->where($map)->find();//
			$mallFloors = $mallConfig['floors'];
			$this->assign("mallFloors",$mallFloors);			
			
            $this->display("editUtilities");
        }		

	}	
	
	/**
	 * 修改公共设施
	 * author:zjh
	*/
     public function editUtilities() {
		 
       if (IS_POST) {
			$actiModel = D('SCUtilities');
            $id = I('request.id', 0, 'int');
			
			$data['id'] = $id;
            $data['tName'] = I('post.tName');	 
			$data['utilityClassId'] = I('post.utilityClassId');
            $data['pyShortName'] = strtoupper(I('post.pyShortName'));
			if (!empty($data['pyShortName'])){
				$data['pyFirstLetter'] = substr( $data['pyShortName'], 0, 1 );
				$data['pyFirstLetter'] = strtoupper($data['pyFirstLetter']);
			}
            $data['floor'] = I('post.floors');
            $data['address'] = I('post.address');			
			$data['introduction'] = I('post.introduction');
			$data['logo'] = I('post.logo');
			$data['code'] = I('post.code');
			$data['terminal'] = I('post.terminal');
			$data['spottype'] = I('post.spottype');//公共设施？信息班牌?其它？
			$data['hide'] = I('post.hide');

			$result = $actiModel->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('Mall/utilities',array('spottype'=>I('post.spottype'))));
			} else {
			   $this->error('操作失败！[原因]：' . $actiModel->getError());
			}            

            
        } else {		 
		 
			//公共设施分类
			$utilityClassModel = D('SCUtilityClass');
			$originTypes = $utilityClassModel->order('Pid asc, ID asc')->select();
			$utilityClass = array();
			$utilityClassModel->sortedTypes($utilityClass, $originTypes);
			$this->assign('utilityClass', $utilityClass);
		 
			$id = I('get.id',0,'intval');
			$utilityModel = D('SCUtilities');
			$map['id'] = $id;
			$utilityInfo = $utilityModel->where($map)->find();
			$this->assign('utilityInfo', $utilityInfo);
				
			if ($utilityInfo) {
				// 获取商场相关配置选项
				$mallSyscfgModel = D('SCMall');
				$mallFloors = $mallSyscfgModel->where(array('id'=>1))->getField('floors');
				$this->assign('mallFloors', $mallFloors);
				
				//公共设施点位班牌
				$tends = D('Endpoint')->field('touchMainId, touchEndPointName')->order('touchMainId asc')->select();
				foreach ($tends as &$tep) {
					if (empty($tep['touchEndPointName'])) {
						$tep['touchEndPointName'] = ''; 
					}
				}
				$this->assign('tends', $tends);
				
				$this->display("editUtilities");           
			}else{
				 $this->error('操作失败！[原因]：' . $utilityModel->getError());
			}
		}
	}	
		
	/**
	 * 删除公共设施
	 * author:zjh
	*/
     public function delUtilities() {
		$id = I('request.id', 0, 'int');
		
		if (!$id) {
			$this->error('非法操作！');
		}
		$model = D('SCUtilities');
		$info = $model->where(array('id'=>$id))->find();
		if (!$info) {
			$this->error('无此信息！');
		}
		
        $delResult = $model->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('Mall/utilities',array('spottype'=>I('request.spottype'))));
        } else {
           $this->error('操作失败！[原因]：' . $model->getError());
        }			
	}			
	
	
	
	
	
	
	
}