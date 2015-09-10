{*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 6844 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}


	{if $pgexist}
	
	{foreach from=$pglist item=pg}
		{if $pg.active}
			{if strpos($link->getModuleLink('faspay', 'payment'),"index.php") !== false}
			<p class="payment_faspay">
				<a class='faspay-item' href="{$link->getModuleLink('faspay', 'payment')}&pg={$pg.cd}" title="{$pg.desc}">

					<img src="{$this_path}icon_{$pg.id}.png">
					{$pg.desc}
				</a>
			</p>
			{else}
			<p class="payment_faspay">
				<a class='faspay-item'  href="{$link->getModuleLink('faspay', 'payment')}?pg={$pg.cd}" title="{$pg.desc}">

					<img src="{$this_path}icon_{$pg.id}.png">
					{$pg.desc}
				</a>
			</p>
			{/if}
		{/if}
	{/foreach}
	<p style="padding-left:150px;">{l s='Pick the one of above payment channels that suite you' mod='faspay'}</p>
	{else}
	<h2>{l s='Faspay not fully configured' mod='faspay'}</h2>
	{/if}

<style type="text/css">
.payment_faspay {
    border-top: 1px dotted black;
    display: block;
    padding-bottom: 5px;
    padding-top: 15px;
    width: 590px;
}

.payment_faspay img {
    max-height: 25px !important;
}	
</style>
