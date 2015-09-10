{if $state == 'ok'}
<div class='container text-center'>
	<div class='col-sm-6 col-sm-offset-3'>
		<div class="panel panel-default">
			<div class="panel-heading">Please Wait Your Payment is being processed</div>
			<div class='panel-body'>
				<img src="{$this_path}loading.gif">
			</div>
		</div>
	</div>
</div>
	    <form id='paymentform' method="post" action="{$url}">
			<input type="hidden" name="LANG" value="{$data.LANG}" />
			<input type="hidden" name="MERCHANTID" value="{$data.MERCHANTID}" />
			<input type="hidden" name="PAYMENT_METHOD" value="{$data.PAYMENT_METHOD}" />
			<input type="hidden" name="TXN_PASSWORD" value="{$data.TXN_PASSWORD}" />
			<input type="hidden" name="MERCHANT_TRANID" value="{$data.MERCHANT_TRANID}" />
			<input type="hidden" name="CURRENCYCODE" value="IDR" />
			<input type="hidden" name="AMOUNT" value="{$data.AMOUNT}" />
			<input type="hidden" name="CUSTNAME" value="{$data.CUSTNAME}" />
			<input type="hidden" name="CUSTEMAIL" value="{$data.CUSTEMAIL}" />
			<input type="hidden" name="DESCRIPTION" value="{$data.DESCRIPTION}" />
			<input type="hidden" name="RETURN_URL" value="{$data.RETURN_URL}" />
			<input type="hidden" name="SIGNATURE" value="{$data.SIGNATURE}" />
			<input type="hidden" name="BILLING_ADDRESS" value="{$data.BILLING_ADDRESS}" />
			<input type="hidden" name="BILLING_ADDRESS_CITY" value="{$data.BILLING_ADDRESS_CITY}" />
			<input type="hidden" name="BILLING_ADDRESS_REGION" value="{$data.BILLING_ADDRESS_REGION}" />
			<input type="hidden" name="BILLING_ADDRESS_POSCODE" value="{$data.BILLING_ADDRESS_POSCODE}" />
			<input type="hidden" name="BILLING_ADDRESS_COUNTRY_CODE" value="{$data.BILLING_ADDRESS_COUNTRY_CODE}" />
			<input type="hidden" name="RECEIVER_NAME_FOR_SHIPPING" value="{$data.RECEIVER_NAME_FOR_SHIPPING}" />
			<input type="hidden" name="SHIPPING_ADDRESS" value="{$data.SHIPPING_ADDRESS}" />
			<input type="hidden" name="SHIPPING_ADDRESS_CITY" value="{$data.SHIPPING_ADDRESS_CITY}" />
			<input type="hidden" name="SHIPPING_ADDRESS_REGION" value="{$data.SHIPPING_ADDRESS_REGION}" />
			<input type="hidden" name="SHIPPING_ADDRESS_STATE" value="{$data.SHIPPING_ADDRESS_STATE}" />
			<input type="hidden" name="SHIPPING_ADDRESS_POSCODE" value="{$data.SHIPPING_ADDRESS_POSCODE}" />
			<input type="hidden" name="SHIPPING_ADDRESS_COUNTRY_CODE" value="{$data.SHIPPING_ADDRESS_COUNTRY_CODE}" />
			<input type="hidden" name="SHIPPINGCOST" value="{$data.SHIPPINGCOST}" />
			<input type="hidden" name="PHONE_NO" value="{$data.PHONE_NO}" />
			<input type="hidden" name="PYMT_IND" value="{$data.PYMT_IND}" />
			<input type="hidden" name="PYMT_CRITERIA" value="{$data.PYMT_CRITERIA}" />
</form>
<script>$('#paymentform').submit();</script>



	{else}
	<h2>{l s='Your order has been processed.' mod='faspay'}</h2>
{/if}
