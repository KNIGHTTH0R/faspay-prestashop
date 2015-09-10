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
*  @version  Release: $Revision: 7465 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{capture name=path}{l s='Payment Summary' mod='faspay'}{/capture}

<h2>{l s='Order Summary' mod='faspay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.'}</p>
{else}
	
	{if strpos($link->getModuleLink('faspay', 'payment'),"index.php") !== false}
		<form action="{$link->getModuleLink('faspay', 'validation')}&pg={$pg.cd}" method="post">
	{else}
		<form action="{$link->getModuleLink('faspay', 'validation')}?pg={$pg.cd}" method="post">
	{/if}
		
	
		<h3>{l s='Here is a short summary of your order:' mod='faspay'}</h3>
		<ul style="margin-left:50px;">
			<li>{l s='The total amount of your order is' mod='faspay'}
				<b><span id="amount" class="price">{displayPrice price=$total}</span>
				{if $use_taxes == 1}
			    	{l s='(tax incl.)' mod='faspay'}
			    {/if}
			    </b>
			</li>
			<li>
				{if $currencies|@count > 1}
					{l s='We accept several currencies:' mod='faspay'}
					<br /><br />
					{l s='Choose one of the following:' mod='faspay'}
					<select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">
						{foreach from=$currencies item=currency}
							<option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
						{/foreach}
					</select>
				{else}
					{l s='Payment will be in following currency:' mod='faspay'}&nbsp;<b>{$currencies.0.name}</b>
					<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
				{/if}
			</li>
			<li>{l s='Payment will be conduct via' mod='faspay'}: <b>{$pg.nm}</b></li>
		</ul>
		<br />
		{if $nbProducts <= 5 }
			{if $pg.cd == "bca_klikpay" }
				{if $status_mid_3 == 'active' or $status_mid_6 == 'active' or $status_mid_12 == 'active' or $status_mid_24 == 'active'}
				<h3>Pilih Tipe Pembayaran</h3>
				<table class="std">
				<thead>
					<th>Product Name</th>
					<th>Payment Type</th>
				</thead>
				<tbody>
				{foreach from=$cartProd item=prod name=prod}
				<tr>
				<td>{$prod.name}{$prod.price}</td>
					<td>
					<input type="hidden" id="prod_id_{$smarty.foreach.prod.index}" name="prod_id_{$smarty.foreach.prod.index}" value="{$prod.id_product}" />
					<select id="payment_tenor_{$smarty.foreach.prod.index}" name="payment_tenor_{$smarty.foreach.prod.index}">
						<option value="00">Full Payment</option>
						{if $prod.price >= $min_price_3 and $status_mid_3 == 'active'}
							<option value="03">Cicilan Periode 3 Bulan</option>
						{/if}
						{if $prod.price >= $min_price_6 and $status_mid_6 == 'active'}
							<option value="06">Cicilan Periode 6 Bulan</option>
						{/if}
						{if $prod.price >= $min_price_12 and $status_mid_12 == 'active'}
							<option value="12">Cicilan Periode 12 Bulan</option>
						{/if}
						{if $prod.price >= $min_price_24 and $status_mid_24 == 'active'}
							<option value="24">Cicilan Periode 24 Bulan</option>
						{/if}
					</select></td> 
				</tr>
				
				{/foreach}
				</tbody>
				</table>
			{/if}
			{/if}
		{/if}
		<p>	<br /><br /><br /><br />
			<b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='faspay'}.</b>
		</p>
		<p class="cart_navigation" id="cart_navigation">
            {if $smarty.const._PS_VERSION_ >= 1.6}
		<a href="{$link->getPageLink('order', true)}?step=3" class="button-exclusive btn btn-default"><i class="icon-chevron-left"></i>{l s='Other payment methods' mod='faspay'}</a>
                <button type="submit" class="button btn btn-default button-medium" ><span>{l s='I confirm my order' mod='faspay'}</span></button>
            {else}
                <a href="{$link->getPageLink('order', true)}?step=3" class="button_large">{l s='Other payment methods' mod='faspay'}</a>
                <input type="submit" value="{l s='I confirm my order' mod='faspay'}" class="exclusive_large" />
            {/if}
	</p>
	</form>
{/if}
