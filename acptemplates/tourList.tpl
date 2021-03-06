{include file='header' pageTitle='wcf.acp.tour.list'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.tour.list{/lang}</h1>

	<script data-relocate="true" src="{@$__wcf->getPath('wcf')}acp/js/WCF.ACP.Tour{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			new WCF.Action.Delete('wcf\\data\\tour\\TourAction', $('.jsTourRow'));
			new WCF.Action.Toggle('wcf\\data\\tour\\TourAction', $('.jsTourRow'));
			new WCF.ACP.Tour.RestartTour();

			var options = { };
			{if $pages > 1}
			options.refreshPage = true;
			{if $pages == $pageNo}options.updatePageNumber = -1;
			{/if}
			{else}
			options.emptyMessage = '{lang}wcf.global.noItems{/lang}';
			{/if}
			new WCF.Table.EmptyTableHandler($('#tourListTableContainer'), 'jsTourRow', options);
		});
		//]]>
	</script>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="TourList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}

	<nav>
		<ul>
			<li>
				<a href="{link controller='TourAdd'}{/link}" class="button">
					<span class="icon icon16 icon-plus"></span>
					<span>{lang}wcf.acp.tour.add{/lang}</span>
				</a>
			</li>
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div id="tourListTableContainer" class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.tour.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>

		<table class="table">
			<thead>
				<tr>
					<th class="columnID{if $sortField == 'tourID'} active {@$sortOrder}{/if}" colspan="2">
						<a href="{link controller='TourList'}pageNo={@$pageNo}&sortField=tourID&sortOrder={if $sortField == 'tourID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a>
					</th>
					<th class="columnText{if $sortField == 'visibleName'} active {@$sortOrder}{/if}">
						<a href="{link controller='TourList'}pageNo={@$pageNo}&sortField=visibleName&sortOrder={if $sortField == 'visibleName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.tour.visibleName{/lang}</a>
					</th>
					<th class="columnText{if $sortField == 'tourTrigger'} active {@$sortOrder}{/if}">
						<a href="{link controller='TourList'}pageNo={@$pageNo}&sortField=tourTrigger&sortOrder={if $sortField == 'tourTrigger' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.tour.tourTrigger{/lang}</a>
					</th>

					{event name='columnHeads'}
				</tr>
			</thead>

			<tbody>
				{foreach from=$objects item=tour}
					<tr class="jsTourRow">
						<td class="columnIcon">
							<span class="icon icon16 icon-play jsTourRestart jsTooltip pointer" title="{lang}wcf.acp.tour.restartTour{/lang}"
							      data-object-id="{$tour->tourID}"></span>
							<span class="icon icon16 icon-check{if $tour->isDisabled}-empty{/if} jsToggleButton jsTooltip pointer"
							      title="{lang}wcf.global.button.{if $tour->isDisabled}enable{else}disable{/if}{/lang}"
							      data-object-id="{$tour->tourID}" data-disable-message="{lang}wcf.global.button.disable{/lang}"
							      data-enable-message="{lang}wcf.global.button.enable{/lang}"></span>
							<a href="{link controller='TourEdit' object=$tour}{/link}" title="{lang}wcf.global.button.edit{/lang}"
							   class="jsTooltip"><span class="icon icon16 icon-pencil"></span></a>
							<a href="{link controller='TourStepList' object=$tour}{/link}" title="{lang}wcf.acp.tour.step.list{/lang}"
							   class="jsTooltip"><span class="icon icon16 icon-list"></span></a>
							<span class="icon icon16 icon-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}"
							      data-object-id="{@$tour->tourID}" data-confirm-message="{lang}wcf.acp.tour.delete.sure{/lang}"></span>

							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$tour->tourID}</td>
						<td class="columnText">
							<a href="{link controller='TourEdit' object=$tour}{/link}">{$tour->visibleName|language|tableWordwrap}</a></td>
						<td class="columnText">{lang}wcf.acp.tour.tourTrigger.{$tour->tourTrigger}{/lang}</td>

						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<div class="contentNavigation">
		{@$pagesLinks}

		<nav>
			<ul>
				<li>
					<a href="{link controller='TourAdd'}{/link}" class="button">
						<span class="icon icon16 icon-plus"></span>
						<span>{lang}wcf.acp.tour.add{/lang}</span>
					</a>
				</li>

				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
