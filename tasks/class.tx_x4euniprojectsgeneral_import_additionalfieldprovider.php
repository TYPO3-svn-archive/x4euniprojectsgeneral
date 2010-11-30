<?php
class tx_x4euniprojectsgeneral_Import_AdditionalFieldProvider implements tx_scheduler_AdditionalFieldProvider {
    public function getAdditionalFields(array &$taskInfo,$task, tx_scheduler_Module $parentObject) { 	
		// Initialize extra field value
		if (empty($taskInfo['projpid'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default sleep time
				$taskInfo['projpid'] = '';
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, set to internal value if no data was submitted already
				$taskInfo['projpid'] = $task->projpid;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['projpid'] = '';
			}
		}
		
		if (empty($taskInfo['projoaiuser'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default sleep time
				$taskInfo['projoaiuser'] = '';
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, set to internal value if no data was submitted already
				$taskInfo['projoaiuser'] = $task->oaiuser;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['projoaiuser'] = '';
			}
		}
		
		if (empty($taskInfo['projoaipw'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default sleep time
				$taskInfo['projoaipw'] = '';
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, set to internal value if no data was submitted already
				$taskInfo['projoaipw'] = $task->oaipw;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['projoaipw'] = '';
			}
		}
		
		if (empty($taskInfo['projoaiurl'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default sleep time
				$taskInfo['projoaiurl'] = '';
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, set to internal value if no data was submitted already
				$taskInfo['projoaiurl'] = $task->oaiurl;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['projoaiurl'] = '';
			}
		}
		
		if (empty($taskInfo['projgetall'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default sleep time
				$taskInfo['projgetall'] = '0';
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, set to internal value if no data was submitted already
				$taskInfo['projgetall'] = $task->getall;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['projgetall'] = '0';
			}
		}

			// Write the code for the field
		$pubIdFieldID = 'task_projpid';
		$pubIdFieldCode = '<input type="text" name="tx_scheduler[projpid]" id="' . $pubIdFieldID . '" value="' . $taskInfo['projpid'] . '" size="10" />';
		
		$oaiUserFieldID = 'task_projoaiuser';
		$oaiUserFieldCode = '<input type="text" name="tx_scheduler[projoaiuser]" id="' . $oaiUserFieldID . '" value="' . $taskInfo['projoaiuser'] . '" size="30" />';
		
		$oaiPwFieldID = 'task_projoaipw';
		$oaiPwFieldCode = '<input type="password" name="tx_scheduler[projoaipw]" id="' . $oaiPwFieldID . '" value="' . $taskInfo['projoaipw'] . '" size="30" />';
		
		$oaiUrlFieldID = 'task_projoaiurl';
		$oaiUrlFieldCode = '<input type="text" name="tx_scheduler[projoaiurl]" id="' . $oaiUrlFieldID . '" value="' . $taskInfo['projoaiurl'] . '" size="50" />';
		
		$getAllFieldID = 'task_projgetall';
		$getAllFieldCode = '<input type="text" name="tx_scheduler[projgetall]" id="' . $oaiUrlFieldID . '" value="' . $taskInfo['projgetall'] . '" size="2" />';
		
		
		$additionalFields = array();
		$additionalFields[$pubIdFieldID] = array(
			'code'     => $pubIdFieldCode,
			'label'    => 'Publication PID',
			'cshLabel' => $pubIdFieldID
		);
		
		$additionalFields[$oaiUserFieldID] = array(
			'code'     => $oaiUserFieldCode,
			'label'    => 'OAI Import User',
			'cshLabel' => $oaiUserFieldID
		);
		
		$additionalFields[$oaiPwFieldID] = array(
			'code'     => $oaiPwFieldCode,
			'label'    => 'OAI Import Pw',
			'cshLabel' => $oaiPwFieldID
		);
		
		$additionalFields[$oaiUrlFieldID] = array(
			'code'     => $oaiUrlFieldCode,
			'label'    => 'OAI Import Url',
			'cshLabel' => $oaiUrlFieldID
		);
		
		$additionalFields[$getAllFieldID] = array(
			'code'     => $getAllFieldCode,
			'label'    => 'Ignore tstamp. 0=get only changed, 1=get all',
			'cshLabel' => $getAllFieldID
		);

		return $additionalFields;
	}

	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject) {
		$submittedData['projpid'] = trim($submittedData['projpid']);
		$submittedData['projoaiuser'] = trim($submittedData['projoaiuser']);
		$submittedData['projoaipw'] = trim($submittedData['projoaipw']);
		$submittedData['projoaiurl'] = trim($submittedData['projoaiurl']);
		$submittedData['projgetall'] = trim($submittedData['projgetall']);

		if (empty($submittedData['projpid'])) {
			$parentObject->addMessage('No project pid given', t3lib_FlashMessage::ERROR);
			$result = false;
		} else if (empty($submittedData['projoaiuser'])){
			$parentObject->addMessage('No oai user given', t3lib_FlashMessage::ERROR);
			$result = false;
		} else if (empty($submittedData['projoaipw'])){
			$parentObject->addMessage('No oai pw given', t3lib_FlashMessage::ERROR);
			$result = false;
		} else if (empty($submittedData['projoaiurl'])){
			$parentObject->addMessage('No oai url given', t3lib_FlashMessage::ERROR);
			$result = false;
		}
		else {
			$result = true;
		}
		return $result;
    }

    public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
		$task->projpid = $submittedData['projpid'];
		$task->oaiuser = $submittedData['projoaiuser'];
		$task->oaipw = $submittedData['projoaipw'];
		$task->oaiurl = $submittedData['projoaiurl'];
		$task->getall = $submittedData['projgetall'];
    }
}
?>