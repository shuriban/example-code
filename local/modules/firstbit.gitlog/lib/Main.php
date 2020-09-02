<?php

namespace FirstBit\GitLog;


/**
 * Class Main
 * @package FirstBit\GitLog
 */
class Main extends HlBlock
{
	protected $tableName = 'b_firstbit_gitlog_commits';

	public function getHook()
	{
		$arProject = HooksHandler::GetRequest();
		$this->addCommits($arProject);
	}

}