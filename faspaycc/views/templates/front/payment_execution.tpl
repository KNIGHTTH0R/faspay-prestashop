{capture name=path}{l s='Payment Summary' mod='faspaycc'}{/capture}

<h2>{l s='Order Summary' mod='faspaycc'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.'}</p>
{else}
	
	{if strpos($link->getModuleLink('faspaycc', 'payment'),"index.php") !== false}
		<form action="{$link->getModuleLink('faspaycc', 'validation')}&channel={$pg}" method="post">
	{else}
		<form action="{$link->getModuleLink('faspaycc', 'validation')}?channel={$pg}" method="post">
	{/if}
		
	
		<h3>{l s='Here is a short summary of your order:' mod='faspaycc'}</h3>
		<ul style="margin-left:50px;">
			<li>{l s='The total amount of your order is' mod='faspaycc'}
				<b><span id="amount" class="price">{displayPrice price=$total}</span>
				{if $use_taxes == 1}
			    	{l s='(tax incl.)' mod='faspaycc'}
			    {/if}
			    </b>
			</li>
			<li>
				{if $currencies|@count > 1}
					{l s='We accept several currencies:' mod='faspaycc'}
					<br /><br />
					{l s='Choose one of the following:' mod='faspaycc'}
					<select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">
						{foreach from=$currencies item=currency}
							<option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
						{/foreach}
					</select>
				{else}
					{l s='Payment will be in following currency:' mod='faspaycc'}&nbsp;<b>{$currencies.0.name}</b>
					<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
				{/if}
			</li>
			<li>{l s='Payment will be conduct via' mod='faspaycc'}: <b>{$pg}</b></li>
		</ul>
		<br />
		<p>	<br /><br /><br /><br />
			<b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='faspaycc'}.</b>
		</p>
		<p class="cart_navigation" id="cart_navigation">
            {if $smarty.const._PS_VERSION_ >= 1.6}
		<a href="{$link->getPageLink('order', true)}?step=3" class="button-exclusive btn btn-default"><i class="icon-chevron-left"></i>{l s='Other payment methods' mod='faspaycc'}</a>
                <button type="submit" class="button btn btn-default button-medium" ><span>{l s='I confirm my order' mod='faspaycc'}</span></button>
            {else}
                <a href="{$link->getPageLink('order', true)}?step=3" class="button_large">{l s='Other payment methods' mod='faspaycc'}</a>
                <input type="submit" value="{l s='I confirm my order' mod='faspaycc'}" class="exclusive_large" />
            {/if}
	</p>
	</form>
{/if}
