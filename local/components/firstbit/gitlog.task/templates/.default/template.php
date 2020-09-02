<?php
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

?>
<table id="task-gitlog-table" class="task-log-table">
	<colgroup>
		<col class="task-log-date-column">
		<col class="task-log-author-column">
		<col class="task-log-where-column">
		<col class="task-log-where-column">
		<col class="task-log-what-column">
	</colgroup>
	<tbody>
	<tr>
		<th class="task-log-date-column"><?=Loc::GetMessage('FISTBIT_GITLOG_DATE')?></th>
		<th class="task-log-author-column"><?=Loc::GetMessage('FISTBIT_GITLOG_AUTHOR')?></th>
		<th class="task-log-where-column"><?=Loc::GetMessage('FISTBIT_GITLOG_REPOSITORY')?></th>
		<!--<th class="task-log-where-column"><?/*=Loc::GetMessage('FISTBIT_GITLOG_BRANCH')*/?></th>-->
		<th class="task-log-what-column "><?=Loc::GetMessage('FISTBIT_GITLOG_COMMIT')?></th>
	</tr>
<?foreach ($arResult as $arCommit):?>
	<tr>
		<td class="task-log-date-column">
			<span class="task-log-date"><?=$arCommit['DATETIME']?></span>
		</td>
		<td class="task-log-author-column">
			<a class="task-log-author" target="_top" href="/company/personal/user/<?=$arCommit['USER']?>/">

                <?=$arCommit['USER_FULL_NAME']?>
            </a>
        </td>
		<td class="task-log-where-column">
            <a class="task-log-where" target="_blank" href="<?=$arCommit['REPOSITORY_URL']?>/">
				<?=$arCommit['REPOSITORY']?>
            </a>
		</td>
        <!--<td class="task-log-where-column">
            <a class="task-log-where" target="_blank" href="<?/*=$arCommit['BRANCH_URL']*/?>/">
				<?/*=$arCommit['BRANCH']*/?>
            </a>
        </td>-->
		<td class="task-log-what-column">
            <a class="task-log-where" target="_blank" href="<?=$arCommit['COMMIT_URL']?>/">
				<?=substr($arCommit['COMMIT'], -8)?>
            </a>
             <?=$arCommit['COMMIT_MESSAGE']?>
		</td>
	</tr>
<?endforeach;?>
	</tbody>
</table>
