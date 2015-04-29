<form class="form-horizontal" id="fcontent" name="fcontent" action="/my/cryptowallet/withdraw/<?php echo $currency ?>" method="post">
	<?php include "my_menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo ucfirst($crypto_name) . ' ' . I18n::get('withdrawal') ?></h4><hr>
		<?php
		if ($msg)
		{
			echo "<div class=\"alert alert-$alert_type\">$msg</div>";
		}
		?>
		<div class="spacer row">
			<span class="col-lg-2 col-md-3">
				<b><?php echo I18n::get('balance') ?>
			</span>
			<span class="col-lg-5 col-md-6">
				<?php echo $balance . ' ' . strtoupper($currency) ?>
			</span>
		</div>
		
		<div class="spacer row">
			<span class="col-lg-2 col-md-3">
				<b><?php echo I18n::get('withdrawal_fee') ?>
			</span>
			<span class="col-lg-5 col-md-6">
				<?php echo $withdrawal_fee . ' ' . strtoupper($currency) ?>
			</span>
		</div>
		
		<div class="spacer row">
			<span class="col-lg-2 col-md-3">
				<b><?php echo ucfirst($crypto_name) . ' ' . I18n::get('address') ?>
			</span>
			<span class="col-lg-5 col-md-6">
				<input class="form-control" id="address" name="address" type="text" value="<?php echo HTML::chars($address) ?>" maxlength="34">
			</span>
		</div>
		
		<div class="spacer row">
			<span class="col-lg-2 col-md-3">
				<b><?php echo I18n::get('amount_to_withdraw') ?>
			</span>
			<span class="col-lg-5 col-md-6">
				<div class="input-group">
					<input class="form-control" id="amount" name="amount" type="text" value="<?php echo HTML::chars($amount) ?>" maxlength="20">
					<span class="input-group-addon"><?php echo strtoupper($currency) ?></span>
				</div>
			</span>
		</div>
		
		<div class="spacer row">
			<span class="col-lg-2 col-md-3">
				<b><?php echo I18n::get('recipient_will_receive') ?>
			</span>
			<span class="col-lg-5 col-md-6">
				<div class="input-group">
					<input class="form-control" id="net_amount" name="net_amount" type="text" value="<?php echo HTML::chars($net_amount) ?>" maxlength="20" disabled>
					<span class="input-group-addon"><?php echo strtoupper($currency) ?></span>
				</div>
			</span>
		</div>
		
		<br>
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>
	</div>
</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script>
balance = <?php echo $balance ?>;
withdrawal_fee = <?php echo $withdrawal_fee ?>;
function isNumber(n)
{
	return !isNaN(parseFloat(n)) && isFinite(n);
}

function isInt(n)
{
   return n % 1 === 0;
}


$(document).ready(function() {
	jQuery.validator.addMethod("check_amount",function(amount_to_withdraw) {
		valid = 0;
		if (isNumber(amount_to_withdraw))
		{
			if (amount_to_withdraw > 0)
			{
				net_withdrawal = (amount_to_withdraw - withdrawal_fee).toFixed(8);
				if (amount_to_withdraw <= balance)
				{
					if (net_withdrawal < 0)
					{
						net_withdrawal = amount_to_withdraw;
					}
					valid = 1;
					$('#net_amount').val(net_withdrawal);
				}
			}
			if (valid == 0)
			{
				$('#net_amount').val();
			}
		}
		else
		{
			valid = 0;
		}
		//return (1+1 < 0);
		return valid;
	}, "Please enter a valid amount.");
	

	var validator = $('form#fcontent').validate({
		rules: {
			address: {
				required: true,
				minlength: 27,
				maxlength: 34
			},
			amount: {
				check_amount: true,
			},
	
		},
		messages: {
			username: {
				remote: jQuery.format("{0} is already in use")
			},
			email: {
				remote: jQuery.format("{0} is already in use")
			}
		}
	});
});
</script>