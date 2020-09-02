<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Loader;

Loader::includeModule('firstbit.gitlog');

class GitLogComponent extends CBitrixComponent
{
	/**
	 * @param array $arParams
	 * @return array
	 */
    public function onPrepareComponentParams(array $arParams): array
    {
        $arParams['TASK_ID'] = intval($arParams['TASK_ID']);

        return $arParams;
    }

    protected function prepareRequest()
    {
		$obGitLog = new \FirstBit\GitLog\Main();
		$arGitLog = $obGitLog->getListOnTask(($this->arParams['TASK_ID']));
		$arUsers = array();
		foreach ($arGitLog as &$arCommit){
			$arCommit['DATETIME'] = $arCommit['DATETIME']->toString();

			$arCommit['REPOSITORY_URL'] = self::getGitLabUrl(
				$arCommit['URL'],
				['repository'=>$arCommit['REPOSITORY']],
				'repository'
			);
			$arCommit['BRANCH_URL'] = self::getGitLabUrl(
				$arCommit['URL'],
				[
					'repository'=>$arCommit['REPOSITORY'],
					'branch'=>$arCommit['BRANCH'],
				],
				'branch'
			);
			$arCommit['COMMIT_URL']= self::getGitLabUrl(
				$arCommit['URL'],
				[
					'repository'=>$arCommit['REPOSITORY'],
					'commit'=>$arCommit['COMMIT'],
				],
				'commit'
			);
			if (!empty($arUsers[$arCommit['USER']])){
				$arCommit['USER_FULL_NAME'] = $arUsers[$arCommit['USER']];
			}else{
				$arCommit['USER_FULL_NAME'] = self::getUserFullNameById($arCommit['USER']);
				$arUsers[$arCommit['USER']] = $arCommit['USER_FULL_NAME'];
			}

		}
		unset($arUsers);
		$this->arResult = $arGitLog;
    }

	/**
	 * @param $sBaseUrl
	 * @param $arUrlParams
	 * @param string $sUrlType repository|branch|commit
	 * @return string
	 */
    public static function getGitLabUrl($sBaseUrl, $arUrlParams, $sUrlType)
	{
		switch ($sUrlType){
			case 'repository':
				return $sBaseUrl.'/'.$arUrlParams['repository'];
			case 'branch':
				return $sBaseUrl.'/'.$arUrlParams['repository'].'/tree/'.$arUrlParams['branch'];
			case 'commit':
				return $sBaseUrl.'/'.$arUrlParams['repository'].'/commit/'.$arUrlParams['commit'];
		}
	}

	/**
	 * @param int $iUserId
	 * @return string User Full Name
	 */
	public static function getUserFullNameById($iUserId)
	{
		$by = "ID" ;
		$order = "desc";
		$filter = array("ID" => $iUserId);
		$arParameters = array(
			'FIELDS' =>[
				'NAME',
				'LAST_NAME'
			]
		);
		$rsUsers = CUser::GetList(
			$by,
			$order,
			$filter,
			$arParameters
		);
		return implode(' ', $rsUsers->Fetch());
	}

    public function executeComponent()
    {
        $this->prepareRequest();

        $this->IncludeComponentTemplate();
    }
}