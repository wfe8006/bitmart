<?php include "my_menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo ucfirst($crypto_name) . ' ' . I18n::get('transaction_history') ?></h4><hr>
		<?php
		if ($msg)
		{
			echo "<div class=\"alert alert-$alert_type\">$msg</div>";
		}

		if (count($transaction_obj) > 0)
		{
		?>
		
		<div class="table-responsive">
			<table class="table">
				<thead>
					<tr>
						<td><b><?php echo I18n::get('date') ?></b></td>
						<td><b><?php echo I18n::get('type') ?></b></td>
						<td><b><?php echo I18n::get('address') . '/' . I18n::get('order') ?></b></td>
						<td><b><?php echo I18n::get('amount') ?></b></td>
						<td><b><?php echo I18n::get('status') ?></b></td>
					</tr>
				</thead>
				<tbody>
		<?php
			foreach ($transaction_obj as $record)
			{
				$date = date('M d, Y h:i', $record['time']);
				$order_id = $record['order_id'];
				$category = $record['category'];
				$address = $record['address'];
				$txid = $record['txid'];
				if ($address == 'balance_transfer')
				{
					if ($category == 'receive')
					{
						$type = I18n::get('received_for');
						$address_field = "<a href=\"/my/order/detail?id=$order_id\">" . I18n::get('order') . " #$order_id</a>";
					}
					else
					{
						$type = I18n::get('sent_to');
						$address_field = "<a href=\"/my/order/detail?id=$order_id\">" . I18n::get('order') . " #$order_id</a> - " . I18n::get('sent_via_account_balance');
					}
				}
				else if ($address == 'system_credited')
				{
					$type = I18n::get('received_for');
					$address_field = I18n::get('credited_by_system');
				}
				else
				{
					if ($category == 'receive')
					{
						$type = I18n::get('received_for');
						$address_field = "<a href=\"/my/order/detail?id=$order_id\">" . I18n::get('order') . " #$order_id</a><br>txid: $txid";
					}
					else
					{
						$type = I18n::get('sent_to');
						$address_field = "$address<br>txid: $txid";
					}
				}
				$amount = sprintf('%0.8f', $record['amount'] / 1e8);
				$id = $record['id'];
				
				$status = bindec($record['status']);
				if ($status == 0)
				{
					$confirmation = $record['confirmation'];
					if ($category == 'send')
					{
						
						$txid = $record['txid'];
						//offer a button for user to resend email
						if (strlen(trim($txid)) == 50)
						{
							$confirmation_status = I18n::get('pending_withdrawal') . "<button id=\"tx_$id\" type=\"button\" class=\"btn btn-default btn-small email\">" . I18n::get('resend_email') . "</button>";
						}
						else
						{
							$confirmation_status = $confirmation >= $min_confirmation ? I18n::get('fully_confirmed') : "$confirmation/$min_confirmation " . I18n::get('confirmations');
						}
					}
					else
					{
						$confirmation_status = $confirmation >= $min_confirmation ? I18n::get('fully_confirmed') : "$confirmation/$min_confirmation " . I18n::get('confirmations');
					}
				}
				else if ($status == 2)
				{
					$confirmation_status = I18n::get('pending_release');
				}
				else
				{
					$confirmation_status = I18n::get('completed');
				}
		?>
					<tr>
						<td><?php echo $date ?></td>
						<td><?php echo $type ?></td>
						<td><?php echo $address_field ?><div id="msg_<?php echo $id ?>" class="msg alert alert-success hidden"></div></td>
						<td><?php echo $amount ?></td>
						<td><?php echo $confirmation_status ?></td>
					</tr>
		<?php
			}
		}
		?>
				</tbody>
			</table>
	</div>

</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script>
message = '<?php echo I18n::get('confirmation_email_withdrawal_request_resent') ?>';
$(document).ready(function()
{
	$('.email').each(function(index, element)
	{
		$(element).click(function()
		{
			id = element.id.split('_')[1];
			$.getJSON('/json/resend_tx_confirmation_mail', { id: id }, function(data)
			{
				if (data == 1)
				{
					$('.msg').addClass('hidden');
					$('#msg_' + id).removeClass('hidden');
					$('#msg_' + id).html(message);
				}
			});
		});
	});
});
</script>