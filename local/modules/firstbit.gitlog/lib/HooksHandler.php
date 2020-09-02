<?php


namespace FirstBit\GitLog;

use \Bitrix\Main\HttpRequest;
use \Bitrix\Main\Application;
use \CModule;
use \CTasks;

/**
 * Class HooksHandler
 * @package FirstBit\GitLog
 */
class HooksHandler
{
	/**
	 * @return array|bool
	 * @throws \Bitrix\Main\SystemException
	 * @throws \TasksException
	 */
	public static function GetRequest()
	{

		$result = array();
		$arProject = array();
		$request = Application::getInstance()->getContext()->getRequest();
		$sToken = $request->getHeader('X-Gitlab-Token');
		$sEvent = $request->getHeader('X-Gitlab-Event');
		if ($sEvent != 'Push Hook'){
			\CHTTP::setStatus("405 Event Not Allowed");
			return false;
		}
		$SECRET_GITLAB_TOKEN = \COption::GetOptionString('firstbit.gitlog', "gitlab_token");
		if (!$SECRET_GITLAB_TOKEN){
			ShowMessage('not install Gitlab-Token in module config');
			return false;
		}
		if ($sToken == $SECRET_GITLAB_TOKEN) {
			$arInput = json_decode(HttpRequest::getInput(), true);

			$arProject['REPOSITORY'] = $arInput['project']['path_with_namespace'];
			$arProject['BRANCH'] = str_replace('refs/heads/', '', $arInput['ref']);
			$arProject['COMMITS'] = array();
			if (is_array($arInput)) {
				$arCommits = $arInput['commits'];
				$arUsers = array();
				$arTasks = array();
				foreach ($arCommits as $arCommit) {
					if (preg_match("!task_(\d+)(.*+)!", $arCommit['message'], $matches)) {
						if (in_array(intval($matches[1]), $arTasks)){
							$result['TASK_ID'] = intval($matches[1]);
						}else{
							$result['TASK_ID'] = self::FindTaskOnId(intval($matches[1]));
							if ($result['TASK_ID']){
								$arTasks[] = $result['TASK_ID'];
							}
						}
						$result['COMMIT_MESSAGE'] = htmlspecialcharsEx($arCommit['message']);
						$result['DATETIME'] = ConvertTimeStamp(strtotime($arCommit['timestamp']), 'FULL');
						$result['COMMIT'] = $arCommit['id'];
						$result['URL'] = parse_url($arCommit['url'],  PHP_URL_SCHEME).'://'.parse_url($arCommit['url'],  PHP_URL_HOST);
						if (in_array($arCommit['author']['email'], $arUsers)){
							$result['USER'] = array_search($arCommit['author']['email'], $arUsers);
						}else{
							$result['USER'] = self::FindUserOnEmail($arCommit['author']['email']);
							$arUsers[$result['USER']] = $result['USER'];
						}
						$arProject['COMMITS'][] = $result;
						unset($result);
					}
				}
				unset($arTasks);
				unset($arUsers);
			}
		}elseif(empty($sToken)){
			define("ERROR_404", "Y");
			\CHTTP::setStatus("404 Not Found");
			require_once(Application::getDocumentRoot().'/404.php');
			return false;
		}else{
			\CHTTP::setStatus("401 Invalid Token");
			return false;
		}
		return $arProject;
	}

	/**
	 * @param $sUserMail
	 * @return int UserID
	 */
	protected static function FindUserOnEmail($sUserMail)
	{
		$by = 'ID';
		$order = 'asc';
		$rsUsers = \CUser::GetList(
			$by,
			$order,
			array(
				'EMAIL' => $sUserMail,
			),
			array(
				'FIELDS' => array(
					'ID', 'LOGIN'
				)
			)
		);
		if ($rsUsers) {
			$arUser = $rsUsers->Fetch();
			return $arUser['ID'];
		} else {
			return false;
		}

	}

	/**
	 * @param $iTaskId
	 * @return int $iTaskId
	 * @throws \TasksException
	 */
	protected static function FindTaskOnId($iTaskId)
	{
		if (CModule::IncludeModule("tasks")) {

			$res = CTasks::GetList(
				array("ID" => "ASC"),
				array("ID" => $iTaskId),
				array("ID"),
				array("USER_ID" => 1)
			);

			if ($arTask = $res->Fetch()) {
				return $arTask['ID'];
			} else {
				return false;
			}

		}
	}
}