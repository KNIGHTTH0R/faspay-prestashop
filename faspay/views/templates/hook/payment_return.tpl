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
*  @version  Release: $Revision: 7471 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $status == 'ok'}
	<h2>{l s='Your order on is complete.' mod='faspay'}</h2>
	{if $pg eq 405}
		<form method="post" action={$uri}>
			<input type="hidden" name="klikPayCode" value="{$dat.klikPayCode}" />
			<input type="hidden" name="transactionNo" value="{$dat.transactionNo}" />
			<input type="hidden" name="totalAmount" value="{$dat.totalAmount}" />
			<input type="hidden" name="currency" value="IDR" />
			<input type="hidden" name="payType" value="{$dat.payType}" />
			<input type="hidden" name="callback" value="{$dat.callback}" />
			<input type="hidden" name="transactionDate" value="{$dat.transactionDate}" />
			<input type="hidden" name="descp" value="{$dat.descp}" />
			<input type="hidden" name="miscFee" value="{$dat.miscFee}" />
			<input type="hidden" name="signature" value="{$dat.signature}" />
			<input type="submit" class="button" value="{l s='Click to do online payment' mod='faspay'}" id="submitBCA" name="submitBCA">
		</form>
	{elseif $pg eq 500 or $pg eq 506 or $pg eq 512 or $pg eq 518}
	    <form method="post" action={$uri}>
			<input type="hidden" name="LANG" value="{$dat.LANG}" />
			<input type="hidden" name="MERCHANTID" value="{$dat.MERCHANTID}" />
			<input type="hidden" name="PAYMENT_METHOD" value="{$dat.PAYMENT_METHOD}" />
			<input type="hidden" name="TXN_PASSWORD" value="{$dat.TXN_PASSWORD}" />
			<input type="hidden" name="MERCHANT_TRANID" value="{$dat.MERCHANT_TRANID}" />
			<input type="hidden" name="CURRENCYCODE" value="{$dat.CURRENCYCODE}" />
			<input type="hidden" name="AMOUNT" value="{$dat.AMOUNT}" />
			<input type="hidden" name="CUSTNAME" value="{$dat.CUSTNAME}" />
			<input type="hidden" name="CUSTEMAIL" value="{$dat.CUSTEMAIL}" />
			<input type="hidden" name="DESCRIPTION" value="{$dat.DESCRIPTION}" />
			<input type="hidden" name="RETURN_URL" value="{$dat.RETURN_URL}" />
			<input type="hidden" name="SIGNATURE" value="{$dat.SIGNATURE}" />
			<input type="hidden" name="BILLING_ADDRESS" value="{$dat.BILLING_ADDRESS}" />
			<input type="hidden" name="BILLING_ADDRESS_CITY" value="{$dat.BILLING_ADDRESS_CITY}" />
			<input type="hidden" name="BILLING_ADDRESS_REGION" value="{$dat.BILLING_ADDRESS_REGION}" />
			<input type="hidden" name="BILLING_ADDRESS_POSCODE" value="{$dat.BILLING_ADDRESS_POSCODE}" />
			<input type="hidden" name="BILLING_ADDRESS_COUNTRY_CODE" value="{$dat.BILLING_ADDRESS_COUNTRY_CODE}" />
			<input type="hidden" name="RECEIVER_NAME_FOR_SHIPPING" value="{$dat.RECEIVER_NAME_FOR_SHIPPING}" />
			<input type="hidden" name="SHIPPING_ADDRESS" value="{$dat.SHIPPING_ADDRESS}" />
			<input type="hidden" name="SHIPPING_ADDRESS_CITY" value="{$dat.SHIPPING_ADDRESS_CITY}" />
			<input type="hidden" name="SHIPPING_ADDRESS_REGION" value="{$dat.SHIPPING_ADDRESS_REGION}" />
			<input type="hidden" name="SHIPPING_ADDRESS_STATE" value="{$dat.SHIPPING_ADDRESS_STATE}" />
			<input type="hidden" name="SHIPPING_ADDRESS_POSCODE" value="{$dat.SHIPPING_ADDRESS_POSCODE}" />
			<input type="hidden" name="SHIPPING_ADDRESS_COUNTRY_CODE" value="{$dat.SHIPPING_ADDRESS_COUNTRY_CODE}" />
			<input type="hidden" name="SHIPPINGCOST" value="{$dat.SHIPPINGCOST}" />
			<input type="hidden" name="PHONE_NO" value="{$dat.PHONE_NO}" />
			<input type="hidden" name="PYMT_IND" value="{$dat.PYMT_IND}" />
			<input type="hidden" name="PYMT_CRITERIA" value="{$dat.PYMT_CRITERIA}" />						
			<input type="submit" class="button" value="{l s='Click to do online payment' mod='faspay'}" id="submitVISA" name="submitVISA">
	{elseif $pg eq 301 or $pg eq 400 or $pg eq 402 or $pg eq 407}
		<a href="{$uri}" class="button_large">{l s='Click to find out payment instructions' mod='faspay'}</a>
	{else}
		<a href="{$uri}" class="button btn btn-default button-medium">{l s='Click to do online payment' mod='faspay'}</a>
	{/if}
	<br /><br />
{/if}
