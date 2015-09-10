<form class='form-horizontal' class='mid-form' method="POST" action="{$current_url}">
	<div class="panel panel-default">
		<div class="panel-heading" style="margin-left: -20px; margin-right: -20px;">Server Configuration</div>
		<div class='panel-body'>

			<div class='bootstrap col-sm-6 text-center'>
				<div class="form-group">
					<label for="exampleInputEmail1" class='col-sm-4 control-label'><strong>Status</strong></label>
					<div class='col-sm-8'>
						<select class='form-control' name='enabled'>
							<option value='1'> Enabled </option>
							<option value='0' {if $config.status eq 0}selected{/if}> Disabled </option>
						</select>
					</div>
				</div>

				<div class="form-group">
					<label class='col-sm-4 control-label'><strong>Merchant Name</strong></label>
					<div  class="col-sm-8">
						<input type="text" class='form-control' placeholder="Enter Merchant Name" name="merchant_name" value="{$config.merchant_name}">
					</div>
				</div>

				<div class="form-group">
					<label class="text-right col-sm-4">Server</label>
					<label class="col-sm-4 text-left">
						<input type="radio" name="server" value="0" {if $config.server eq 0}checked{/if}> Development
					</label> 
					<label class="col-sm-4 text-left">
						<input type="radio" name="server" value="1" {if $config.server eq 1}checked{/if}> Production
					</label>
				</div>
				<div class="form-group">
					<label class="text-right col-sm-4">Enable Auto Void </label>

					<label class="col-sm-4 text-left">
						<input type="checkbox" class='text-left' name="auto_void" {if $config.auto_void eq 1}checked{/if}>
					</label>
				</div>

			</div>
		</div>
	</div>

	<div class="panel panel-info">
		<div class='panel-heading' style="margin-left: -20px; margin-right: -20px;">Channel Settings</div>
		<div class='panel-body channel-body'>

		{if count($mids) != 0}
		{foreach from=$mids item=channel}
			<table class="table table-collapse channels">
					<tbody>
						<tr>
							<td colspan="2">
								<b>Visa / Master</b>
							</td>
							<td align="left">
								<span class="breadcrumb {if $channel.status eq 0}channel-innactive{else}channel-active{/if}">* Channel Visa / Master</span>
							</td>
						</tr>
						<tr>
							<td>Enabled</td>
							<td width="200">
								<select name="cc_status[]">
									<option value="on">Enabled</option>
									<option value="off" {if $channel.status eq "0"}selected{/if}>Disabled</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>Name</td>
							<td width="200"><input type="text" name="cc_title[]" value="{$channel.name}"></td>
						</tr>
						<tr>
							<td>MID Visa / Master</td>
							<td width="200"><input type="text" name="cc_mid[]" value="{$channel.mid}"></td>
						</tr>
						<tr>
							<td>Password Visa / Master</td>
							<td width="200"><input type="password" name="cc_pass[]" value="{$channel.password}"></td>
						</tr>
						<tr>
							<td>Payment Indicator Visa / Master</td>
							<td width="200"><input type="text" name="cc_pymt_ind[]" value="{$channel.pymt_ind}" ></td>
						</tr>
						<tr>
							<td>Payment Criteria Visa / Master</td>
							<td width="200"><input type="text" name="cc_pymy_crt[]" value="{$channel.pymt_crt}"></td>
							<td><button class='btn btn-danger btn-delete-mid'>Delete this</button></td>
						</tr>
					</tbody>
				</table>

		{/foreach}
		{else}
				<table class="table table-collapse channels">
					<tbody>
						<tr>
							<td colspan="2">
								<b>Visa / Master</b>
							</td>
							<td align="left">
								<span class="breadcrumb">* Channel Visa / Master</span>
							</td>
						</tr>
						<tr>
							<td>Enabled</td>
							<td width="200"><input type="checkbox" checked="" value="on" name="cc_status[]"></td>
						</tr>
						<tr>
							<td>Name</td>
							<td width="200"><input type="text" name="cc_title[]"></td>
						</tr>
						<tr>
							<td>MID Visa / Master</td>
							<td width="200"><input type="text" value="" name="cc_mid[]"></td>
						</tr>
						<tr>
							<td>Password Visa / Master</td>
							<td width="200"><input type="password" value="" name="cc_pass[]"></td>
						</tr>
						<tr>
							<td>Payment Indicator Visa / Master</td>
							<td width="200"><input type="text" value="" name="cc_pymt_ind[]"></td>
						</tr>
						<tr>
							<td>Payment Criteria Visa / Master</td>
							<td width="200"><input type="text" value="" name="cc_pymy_crt[]"></td>
						</tr>
					</tbody>
				</table>
		{/if}
		</div>
			<button id='newchannel' class="btn btn-warning" name="btnSubmit">Add New Channel</button>
	</div>

	<button type="submit" class="btn btn-default" name='btnSubmit'>Update</button>
</form>
<script type="text/javascript">
	var channelinput = $('.channels').first();
	
	$('#newchannel').on('click', function(event){
		event.preventDefault();
		channelinput.clone().appendTo('.channel-body').find("input").val("");
	});

	$(document).on('click','.btn-delete-mid', function(event){
		event.preventDefault();
		$(this).parent().parent().parent().remove();
	});
</script>
<style>
	.breadcrumb.channel-active{
		background-color: #00CC00 !important;
	}
	.breadcrumb.channel-innactive{
		background-color: #FF9900 !important;
	}
	.table.table-collapse.channels{
		margin-bottom: 20px;
	}
</style>