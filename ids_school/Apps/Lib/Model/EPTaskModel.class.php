<?php
/**
 * 班牌任务模型
 * @author Skeam TJ
 * $taskModel = D('EPTask');//任务
 */
class EPTaskModel extends Model {
	
	protected $trueTableName = 'TB_EndPointTask';
	
	/**
	 * 添加任务
	 * @param string $touchMainId
	 * @param string $commandName
	 * @param string $commandParam1
	 * @param string $commandNote
	 * @return number
	 */
	public function addEPTask($touchMainId, $commandName, $commandParam1, $commandNote) {
		
		// 清除已存在的未完成的相同任务
		$this->where(array('touchMainId'=>$touchMainId, 'commandName'=>$commandName, 'isFinished'=>0))->delete();
		
		// 添加新任务
		$data['touchMainId'] = $touchMainId;
		$data['commandName'] = $commandName;
		$data['commandParam1'] = utf82gbk($commandParam1);
		$data['commandNote'] = utf82gbk($commandNote);
		$data['creattime'] = date('Y-m-d H:i:s');
		$result = $this->add($data);

		return $result !== FALSE ? 1 : 0;
	}
	
	/**
	 * 清除历史任务
	*/
	private function clearHistoryTask() {
		$maxCounts = 1000;
		//$taskModel = D('EPTask');
		$taskTotals = $this->count();
		if ($taskTotals > $maxCounts) {
			$delItemIds = $this->order('taskId asc')->limit($taskTotals - $maxCounts)->getField('taskId', true);
			if (count($delItemIds) > 0) {
				$this->where(array('taskId'=>array('in', $delItemIds)))->delete();
			}
		}
	}	
	
	
	
	
	
}