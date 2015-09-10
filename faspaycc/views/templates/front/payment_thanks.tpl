{if $order.current_state =='12'}
<h2 class='text-center'>Terima Kasih</h2>
<div class='col-sm-6 col-sm-offset-3'>
	<div class='panel panel-default'>
		<div class='panel-heading'>Transaction Detail</div>
		<div class='panel-body'>
			<p class='text-center'> Pembayaran Anda untuk Transaksi ID : {$order.id} <span class='text-success'>SUKSES</span></p>
			<table class='table'>
				<tr>
					<th>Description</th>
					<th class='text-right'>Amount</th>
				</tr>
				<tr>
					<td>Total Product</td>
					<td class='text-right'>{$order.total_products}</td>
				</tr>
				<tr>
					<td>Shipping</td>
					<td class='text-right'>{$order.shipping}</td>
				</tr>
				<tr>
					<td>Grand Total</td>
					<td class='text-right'><b>{$order.total_paid}</b></td>
				</tr>
			</table>
		</div>
	</div>
	<img src="{$modules_dir}faspaycc/image/powered.png">
</div>
{else}
<h2 class='text-center'>Mohon Maaf</h2>
<div class='col-sm-6 col-sm-offset-3'>
	<div class='panel panel-default'>
		<div class='panel-heading'>Transaction Detail</div>
		<div class='panel-body'>
			<p class='text-center'> Pembayaran Anda untuk Transaksi ID : {$order.id} <strong class='text-center .text-fail'>GAGAL</strong></p>
			<table class='table'>
				<tr>
					<th>Description</th>
					<th class='text-right'>Amount</th>
				</tr>
				<tr>
					<td>Total Product</td>
					<td class='text-right'>{$order.total_products}</td>
				</tr>
				<tr>
					<td>Shipping</td>
					<td class='text-right'>{$order.shipping}</td>
				</tr>
				<tr>
					<td>Grand Total</td>
					<td class='text-right'><bold>{$order.total_paid}</bold></td>
				</tr>
			</table>
		</div>
	</div>
	<img src="{$modules_dir}powered.png">
</div>
{/if}

<style>
	.text-success{
		color: #00CC00 !important;
		font-weight: bold;
	}
	.text-fail{
		color: #FF0000 !important;
		font-weight: bold;
	}
</style>