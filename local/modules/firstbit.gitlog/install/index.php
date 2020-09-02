<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

/**
 * Class firstbit_gitlog
 */
class firstbit_gitlog extends CModule
{

    var $MODULE_ID = 'firstbit.gitlog';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_PATH;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    /**
     * Construct object
     */
    public function __construct()
    {
        $this->MODULE_NAME = Loc::getMessage('STB_GIT_LOG_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('STB_GIT_LOG_DESCRIPTION');
        $this->PARTNER_NAME = GetMessage('STB_GIT_LOG_PARTNER_NAME');
        $this->PARTNER_URI = GetMessage('STB_GIT_LOG_PARTNER_URI');

        $this->MODULE_PATH = $this->getModulePath();

        include $this->MODULE_PATH.'/install/version.php';

        $this->MODULE_VERSION = $arModuleVersion[ 'VERSION' ];
        $this->MODULE_VERSION_DATE = $arModuleVersion[ 'VERSION_DATE' ];
    }

    /**
     * Return path module
     *
     * @return string
     */
    protected function getModulePath()
    {
        $modulePath = explode('/', __FILE__);
        $modulePath = array_slice(
            $modulePath,
            0,
            array_search($this->MODULE_ID, $modulePath) + 1
        );

        return join('/', $modulePath);
    }

    /**
     * Return components path for install
     *
     * @param bool $absolute
     *
     * @return string
     */
    protected function getComponentsPath($absolute = true)
    {
        $documentRoot = getenv('DOCUMENT_ROOT');
        if (strpos($this->MODULE_PATH, 'local/modules') !== false) {
            $componentsPath = '/local/components';
        } else {
            $componentsPath = '/bitrix/components';
        }

        if ($absolute) {
            $componentsPath = sprintf('%s%s', $documentRoot, $componentsPath);
        }

        return $componentsPath;
    }

    /**
     * Install module
     *
     * @return void
     */
    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->installHLBlock();
        $this->InstallFiles();
    }
	/**
	 * Copy files module
	 *
	 * @return bool
	 */
	public function InstallFiles()
	{
		$path = $this->MODULE_PATH."/install";
		CopyDirFiles($path."/hook", $_SERVER[ "DOCUMENT_ROOT" ], true, true);
		return true;
	}

    /**
     * Remove module
     *
     * @return void
     */
    public function DoUninstall()
    {

        ModuleManager::unregisterModule($this->MODULE_ID);
    }

	/**
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function installHLBlock()
    {
        CModule::IncludeModule('crm');
        $arHl = array(
            array(
                'b_firstbit_gitlog_commits',
                'GitLog Commits',
                array(
                    'FIELDS' => array(
						'UF_TASK_ID'             => array(
							'Y',
							'integer',
							array(
								'EDIT_FORM_LABEL'   => array(
									'ru' => 'Номер задачи',
								),
								'LIST_COLUMN_LABEL' => array(
									'ru' => 'Номер задачи',
								),
							)
						),
                        'UF_DATETIME'             => array(
							'Y',
							'datetime',
							array(
								'EDIT_FORM_LABEL'   => array(
									'ru' => 'Дата',
								),
								'LIST_COLUMN_LABEL' => array(
									'ru' => 'Дата',
								),
							)
						),
						'UF_REPOSITORY'             => array(
							'N',
							'string',
							array(
								'EDIT_FORM_LABEL'   => array(
									'ru' => 'Репозиторий',
								),
								'LIST_COLUMN_LABEL' => array(
									'ru' => 'Репозиторий',
								),
							)
						),
						'UF_BRANCH'             => array(
							'N',
							'string',
							array(
								'EDIT_FORM_LABEL'   => array(
									'ru' => 'Ветка',
								),
								'LIST_COLUMN_LABEL' => array(
									'ru' => 'Ветка',
								),
							)
						),
						'UF_URL'             => array(
							'N',
							'url',
							array(
								'EDIT_FORM_LABEL'   => array(
									'ru' => 'GitLab URL',
								),
								'LIST_COLUMN_LABEL' => array(
									'ru' => 'GitLab URL',
								),
							)
						),
						'UF_USER' => array(
							'N',
							'employee',
							array(
								'EDIT_FORM_LABEL'   => array(
									'ru' => 'Автор',
								),
								'LIST_COLUMN_LABEL' => array(
									'ru' => 'Автор',
								),
							)
						),
                        'UF_COMMIT'           => array(
                            'Y',
                            'string',
                            array(
                                'EDIT_FORM_LABEL'   => array(
                                    'ru' => 'Коммит',
                                ),
                                'LIST_COLUMN_LABEL' => array(
                                    'ru' => 'Коммит',
                                )
                            )
                        ),
						'UF_COMMIT_MESSAGE'           => array(
							'Y',
							'string',
							array(
								'EDIT_FORM_LABEL'   => array(
									'ru' => 'Сообщение коммита',
								),
								'LIST_COLUMN_LABEL' => array(
									'ru' => 'Сообщение коммита',
								)
							)
						),
                    )
                )
            )
        );

        foreach ($arHl as $hl) {
            $idNewHighLoadBlock = self::createHighLoadBlock($hl[ 0 ], $hl[ 1 ], $hl[ 2 ]);
        }

        return true;
    }

	/**
	 * @param $tableName
	 * @param $highBlockName
	 * @param array $hlData
	 * @return array|int
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function createHighLoadBlock($tableName, $highBlockName, array $hlData)
    {
        global $APPLICATION;

        $info = array();
        $addData = false;

        foreach (array( 'highloadblock' ) as $moduleId) {
            if (!\Bitrix\Main\Loader::includeModule($moduleId)) {
                throw new \Bitrix\Main\SystemException(GetMessage('ERROR_INCLUDE_HIGHLOADBLOCK_MODULE', array(
                    '#MODULE#' => $moduleId
                )));
            }
        }

        $connection = \Bitrix\Main\Application::getConnection();

        $sqlHelper = $connection->getSqlHelper();

        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getList(array(
                'filter' => array(
                    'TABLE_NAME' => $tableName,
                )
            )
        )->fetch();

        if (!$hlblock) {

            $highBlockName = preg_replace('/([^A-Za-z0-9]+)/', '', trim($highBlockName));

            if ($highBlockName == '') {
                throw new \Bitrix\Main\SystemException(GetMessage('HIGHLOADBLOCK_NAME_IS_INVALID'));
            }

            $highBlockName = strtoupper(substr($highBlockName, 0, 1)).substr($highBlockName, 1);

            $data = array(
                'NAME'       => $highBlockName,
                'TABLE_NAME' => $tableName,
            );

            $result = Bitrix\Highloadblock\HighloadBlockTable::add($data);

            if ($result->isSuccess()) {
                $highBlockID = $result->getId();
                $addData = true;

                $info[] = GetMessage('HIGHLOADBLOCK_ADDED_INFO', array(
                    '#NAME#' => $highBlockName,
                    '#ID#'   => $highBlockID,
                ));

            } else {
                throw new \Bitrix\Main\SystemException(GetMessage('HIGHLOADBLOCK_ADDED_INFO_ERROR', array(
                    '#NAME#'  => $highBlockName,
                    '#ERROR#' => $result->getErrorMessages(),
                )));
            }

        } else {
            $highBlockID = $hlblock[ 'ID' ];
        }

        $oUserTypeEntity = new CUserTypeEntity();

        $sort = 500;

        foreach ($hlData[ 'FIELDS' ] as $fieldName => $fieldValue) {
            $aUserField = array(
                'ENTITY_ID'     => 'HLBLOCK_'.$highBlockID,
                'FIELD_NAME'    => $fieldName,
                'USER_TYPE_ID'  => $fieldValue[ 1 ],
                'SORT'          => $sort,
                'MULTIPLE'      => 'N',
                'MANDATORY'     => $fieldValue[ 0 ],
                'SHOW_FILTER'   => 'N',
                'SHOW_IN_LIST'  => 'Y',
                'EDIT_IN_LIST'  => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS'      => array(),
            );

            if (isset($fieldValue[ 2 ]) && is_array($fieldValue[ 2 ])) {
                $aUserField = array_merge($aUserField, $fieldValue[ 2 ]);
            }

            $resProperty = CUserTypeEntity::GetList(
                array(),
                array( 'ENTITY_ID' => $aUserField[ 'ENTITY_ID' ], 'FIELD_NAME' => $aUserField[ 'FIELD_NAME' ] )
            );

            if ($aUserHasField = $resProperty->Fetch()) {
                $idUserTypeProp = $aUserHasField[ 'ID' ];
                if ($oUserTypeEntity->Update($idUserTypeProp, $aUserField)) {
                    $info[] = GetMessage('USER_TYPE_UPDATE', array(
                        '#FIELD_NAME#' => $aUserHasField[ 'FIELD_NAME' ],
                        '#ENTITY_ID#'  => $aUserHasField[ 'ENTITY_ID' ],
                    ));
                } else {
                    if (($ex = $APPLICATION->GetException())) {
                        throw new \Bitrix\Main\SystemException(GetMessage('USER_TYPE_UPDATE_ERROR', array(
                            '#FIELD_NAME#' => $aUserHasField[ 'FIELD_NAME' ],
                            '#ENTITY_ID#'  => $aUserHasField[ 'ENTITY_ID' ],
                            '#ERROR#'      => $ex->GetString(),
                        )));
                    }
                }
            } else {
                if ($idUserTypeProp = $oUserTypeEntity->Add($aUserField)) {
                    $info[] = GetMessage('USER_TYPE_ADDED', array(
                        '#FIELD_NAME#' => $aUserField[ 'FIELD_NAME' ],
                        '#ENTITY_ID#'  => $aUserField[ 'ENTITY_ID' ],
                    ));
                } else {
                    if (($ex = $APPLICATION->GetException())) {
                        throw new \Bitrix\Main\SystemException(GetMessage('USER_TYPE_ADDED_ERROR', array(
                            '#FIELD_NAME#' => $aUserField[ 'FIELD_NAME' ],
                            '#ENTITY_ID#'  => $aUserField[ 'ENTITY_ID' ],
                            '#ERROR#'      => $ex->GetString(),
                        )));
                    }
                }
            }

            $sort += 100;
        }

        $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity(
            \Bitrix\Highloadblock\HighloadBlockTable::getRowById($highBlockID)
        );

        if (isset($hlData[ 'ALTER' ]) && is_array($hlData[ 'ALTER' ])) {

            foreach ($hlData[ 'ALTER' ] as $alterData) {

                if ($connection->query(
                    str_replace(
                        '#TABLE_NAME#',
                        $sqlHelper->quote($hlEntity->getDBTableName()),
                        $alterData
                    )
                )
                ) {
                    $info[] = GetMessage('HIGHLOADBLOCK_ALTER_SUCCESS_INFO', array(
                        '#ROW#' => str_replace(
                            '#TABLE_NAME#',
                            $sqlHelper->quote($hlEntity->getDBTableName()),
                            $alterData
                        )
                    ));
                }

            }

        }

        if (isset($hlData[ 'INDEXES' ]) && is_array($hlData[ 'INDEXES' ])) {

            foreach ($hlData[ 'INDEXES' ] as $indexData) {

                $iResult = $connection->createIndex(
                    str_replace('#TABLE_NAME#', $hlEntity->getDBTableName(), $indexData[ 0 ]),
                    str_replace('#TABLE_NAME#', $hlEntity->getDBTableName(), $indexData[ 1 ]),
                    $indexData[ 2 ]
                );

                if ($iResult) {
                    $info[] = GetMessage('HIGHLOADBLOCK_ADDED_INDEX_INFO', array(
                        '#INDEX_NAME#' => str_replace('#TABLE_NAME#', $hlEntity->getDBTableName(), $indexData[ 1 ]),
                        '#TABLE_NAME#' => $hlEntity->getDBTableName(),
                    ));
                } else {
                    throw new \Bitrix\Main\SystemException(GetMessage('HIGHLOADBLOCK_ADDED_INDEX_ERROR', array(
                        '#INDEX_NAME#' => str_replace('#TABLE_NAME#', $hlEntity->getDBTableName(), $indexData[ 1 ]),
                        '#TABLE_NAME#' => $hlEntity->getDBTableName(),
                        '#ERROR#'      => '',
                    )));
                }

            }

        }

        if($addData){
            if (isset($hlData[ 'DATA' ]) && is_array($hlData[ 'DATA' ])) {
                $entity_data_class = $hlEntity->getDataClass();
                foreach ($hlData[ 'DATA' ] as $data){
                    $result = $entity_data_class::add($data);
                }
            }
        }

        return $highBlockID;

    }

}
