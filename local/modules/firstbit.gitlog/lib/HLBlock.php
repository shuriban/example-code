<?php

namespace FirstBit\GitLog;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;

Loader::includeModule('highloadblock');

/**
 * Class HlBlock
 * @package FirstBit\GitLog
 */
abstract class HlBlock
{

	protected $tableName;

	/**
	 * @return string|null
	 */
	public function getTableName(): ?string
	{

		return $this->tableName;

	}

	/**
	 * @param array $filter
	 * @param array $select
	 * @param array $order
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getList($filter = [], $select = [], $order = []): array
	{
		self::prepareFilter($filter);

		if (empty($select)) {
			$select = ['*'];
		}
		else {
			self::prepareSelect($select);
		}
		if (!empty($order)){
			self::prepareFilter($order);
		}
		$entity_data_class = self::GetEntityDataClass();
		$items = $entity_data_class::getList(['filter' => $filter, 'select' => $select, 'order' => $order])->fetchAll();

		foreach ($items as &$item) {
			self::prepareResult($item);
		}

		return $items;
	}

	/**
	 * @param $array
	 */
	public static function prepareFilter(&$array): void
	{
		if (is_array($array) && !empty($array)) {
			foreach ($array as $key => $value) {
				if (stripos($key, 'UF_') === false) {
					$array['UF_' . $key] = $value;
					unset($array[ $key ]);
				}
			}
		}
	}

	/**
	 * @param $array
	 */
	public static function prepareSelect(&$array): void
	{
		array_walk($array, static function (&$value) {
			$value = 'UF_' . $value;
		});
	}

	/**
	 * @param $array
	 */
	public static function prepareResult(&$array): void
	{
		if (is_array($array) && !empty($array)) {
			foreach ($array as $key => $value) {
				if (stripos($key, 'UF_') !== false) {
					$array[ str_replace('UF_', '', $key) ] = $value;
					unset($array[ $key ]);
				}
			}
		}
	}

	/**
	 * @param $arCommit
	 * @return array|int
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function addCommit($arCommit)
	{

		self::prepareFilter($arCommit);

		$entity_data_class = $this->GetEntityDataClass();
		$result = $entity_data_class::add(
			$arCommit
		);
		$errors = $result->getErrorMessages();
		if ($errors){
			return $errors;
		}else{
			return $result->getId();
		}

	}

	/**
	 * @param $arProject
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function addCommits($arProject){
		$arCommits = array();
		foreach ($arProject['COMMITS'] as $key => $arCommit){
			$arCommits[] = $arCommit['COMMIT'];
		}
		$arExcludes = self::getList(
			array('COMMIT' => $arCommits),
			array('COMMIT')
		);
		$arExcludeDeployed = array();
		foreach ($arExcludes as $arExclude){
			$arExcludeDeployed[] = $arExclude['COMMIT'];
		}

		foreach ($arProject['COMMITS'] as $arCommit){
			$arCommit['REPOSITORY'] = $arProject['REPOSITORY'];
			$arCommit['BRANCH'] = $arProject['BRANCH'];
			if (!in_array($arCommit['COMMIT'] , $arExcludeDeployed)){
				self::addCommit($arCommit);
			}
		}
	}

	/**
	 * @param $iTaskId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getListOnTask($iTaskId)
	{
		return $this->getList(
			array('TASK_ID'=>$iTaskId),
			[],
			['DATETIME'=>'DESC']
		);
	}

	/**
	 * @param $iTaskId
	 * @return integer Count Commit on sask
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getCountCommitOnTask($iTaskId)
	{
		$entity_data_class =  $this->GetEntityDataClass();
		$rsData = $entity_data_class::getList([
			'select'  => ['CNT', 'UF_TASK_ID'],
			'runtime' => [
				new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			],
			'filter'  => ['UF_TASK_ID'=>$iTaskId],
		]);
		return $rsData->Fetch()['CNT'];
	}
	/**
	 * @return \Bitrix\Main\ORM\Data\DataManager
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function GetEntityDataClass()
	{
		$hlblock = HLBT::getList(['filter' => ['TABLE_NAME' => $this->getTableName()]])->fetch();
		$entity = HLBT::compileEntity($hlblock);
		$entity_data_class = $entity->getDataClass();
		return $entity_data_class;
	}

}