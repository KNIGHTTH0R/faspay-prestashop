	{if $pgexist}
	{foreach from=$midlist item=pg}
		{if $pg.status == 1}
			<p class="payment_module">
				<a href="{$link->getModuleLink('faspaycc', 'payment')}?channel={$pg.name}" title="{$pg.name}">
					<img src="{$this_path}logo/icon_{$pg.name}.png" style="max-height:40px;">
					<span>Pay with {$pg.name}</span>
				</a>
			</p>
		{/if}
	{/foreach}
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
