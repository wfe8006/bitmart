<form class="form-horizontal" id="fcontent" name="fcontent" action="/my/cryptowallet/make_payment" method="post">
	<?php include "my_menu.php"; ?>
	<div class="col-lg-10 col-md-10">
		<h4><?php echo I18n::get('make_payment') ?></h4><hr>
		<div class="alert alert-info"><?php echo $payment_msg ?></div>
		
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
				<b><?php echo I18n::get('amount_payable') ?>
			</span>
			<span class="col-lg-5 col-md-6">
				<?php echo $total_decimal . ' ' . strtoupper($currency) . " - <a href=\"/my/purchase/detail?id=$order_id\">" . I18n::get('order') . " #$order_id</a>" ?>
			</span>
		</div>
		
		<br>
		<p class="text-center"><input class="btn btn-default" type="submit" value="<?php echo I18n::get('make_payment') ?>" /></p>
	</div>
	<input type="hidden" name="order_id" value="<?php echo $order_id ?>">
</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>
