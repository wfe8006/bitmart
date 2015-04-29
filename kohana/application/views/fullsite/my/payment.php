</form>
<form class="form-horizontal" id="fcontent" name="fcontent" action="/my/payment" method="post">
	<?php include "menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo I18n::get('payment') ?></h4><hr>
		<?php
		if ($msg)
		{
			echo "<div class=\"alert alert-success\">$msg</div>";
		}
		?>
		<div class="alert alert-info"><?php echo I18n::get('payment_info') ?></div>
		<?php
		foreach ($cfg_crypto as $symbol => $record)
		{
			$active = $record['active'];
			$name = $record['name'];
			if ($active == 1)
			{
				$checked = $$symbol == 1 ? ' checked' : '';
				echo "<div class=\"row\"><div class=\"col-lg-3\"><div class=\"checkbox\"><label><input id=\"$symbol\" name=\"$symbol\" type=\"checkbox\" class=\"cb\" value=\"1\"$checked> <b>" . ucfirst($name) . ' - ' . strtoupper($symbol) . "</b></label></div></div></div><hr>";
			}
		}
		?>
		
	
		
		<div class="row">
			<div class="col-lg-3">
				<div class="checkbox"><label><input id="cash_on_delivery" name="cash_on_delivery" type="checkbox" class="cb" value="1"<?php if ($cash_on_delivery == 1) echo ' checked' ?>> <b><?php echo I18n::get('cash_on_delivery') ?></b></label></div>
			</div>
			<div class="col-lg-6">
				<?php echo I18n::get('payment_instructions') ?><br>
				<textarea class="form-control" name="cash_on_delivery_note" class="form-control" rows="3"><?php echo HTML::chars($cash_on_delivery_note) ?></textarea>
			</div>
		</div>
		<hr>
		
		<div class="row">
			<div class="col-lg-3">
				<div class="checkbox"><label><input id="bank_deposit" name="bank_deposit" type="checkbox" class="cb" value="1"<?php if ($bank_deposit == 1) echo ' checked' ?>> <b><?php echo I18n::get('bank_deposit') ?></b></label></div>
			</div>
			<div class="col-lg-6">
				<?php echo I18n::get('payment_instructions') ?>
			<textarea class="form-control" name="bank_deposit_note" class="form-control" rows="3"><?php echo HTML::chars($bank_deposit_note) ?></textarea>
			</div>
		</div>
		<hr>
	
		<div class="row">
			<div class="col-lg-3">
				<div class="checkbox"><label><input id="money_order" name="money_order" type="checkbox" class="cb" value="1"<?php if ($money_order == 1) echo ' checked' ?>> <b><?php echo I18n::get('money_order') ?></b></label></div>
			</div>
			<div class="col-lg-6">
				<?php echo I18n::get('payment_instructions') ?>
			<textarea class="form-control" name="money_order_note" class="form-control" rows="3"><?php echo HTML::chars($money_order_note) ?></textarea>
			</div>
		</div>
		<hr>
		
		<div class="row">
			<div class="col-lg-3">
				<div class="checkbox"><label><input id="cashier_check" name="cashier_check" type="checkbox" class="cb" value="1"<?php if ($cashier_check == 1) echo ' checked' ?>> <b><?php echo I18n::get('cashier_check') ?></b></label></div>
			</div>
			<div class="col-lg-6">
				<?php echo I18n::get('payment_instructions') ?>
			<textarea class="form-control" name="cashier_check_note" class="form-control" rows="3"><?php echo HTML::chars($cashier_check_note) ?></textarea>
			</div>
		</div>
		<hr>
		
		<div class="row">
			<div class="col-lg-3">
				<div class="checkbox"><label><input name="personal_check" type="checkbox" class="cb" value="1"<?php if ($personal_check == 1) echo ' checked' ?>> <b><?php echo I18n::get('personal_check') ?></b></label></div>
			</div>
			<div class="col-lg-6">
				<?php echo I18n::get('payment_instructions') ?>
			<textarea class="form-control" name="personal_check_note" class="form-control" rows="3"><?php echo HTML::chars($personal_check_note) ?></textarea>
			</div>
		</div>
		<hr>
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('update') ?>" /></p>
	</div>
</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script>
$(document).ready(function() {
	
});	
	
</script>