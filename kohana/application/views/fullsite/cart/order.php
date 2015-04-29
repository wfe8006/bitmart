<form id="fcontent" name="fcontent" action="/cart/order" method="post">
	<div class="col-lg-12 col-md-12">
		<h4><?php echo I18n::get('cart') . ' - ' . $title ?></h4><hr>
		<div class="alert alert-info alert-block<?php echo empty($msg) ? " hidden" : "" ?>"><?php echo $msg?></div>
		<?php
		if ($crypto_address != '')
		{
		?>

		<div class="col-lg-offset-2 col-lg-8 col-md-offset-2 col-md-8 panel panel-default">
			<br>
			<div class="media">
				<div class="media-body">
					<span class="col-lg-10 col-md-10 col-xs-6"><b><?php echo I18n::get('item') ?></b></span>
					<!--<span class="col-lg-2 col-md-2 text-right"><b><?php echo I18n::get('unit_price') ?></b></span>//-->
					<!--<span class="col-lg-1 col-md-1"><b><?php echo I18n::get('quantity') ?></b></span>//-->
					<span class="col-lg-2 col-md-2 col-xs-6 text-right"><b><?php echo I18n::get('subtotal') ?></b></span>
				</div>
			</div>
			<hr>
			<?php
			$show_display = $order['show_display'];
			$ori_currency = $order['ori_currency_code'];
			$ori_grand_subtotal = $order['ori_grand_subtotal'];
			$ori_shipping = $order['ori_shipping'];
			$ori_tax = $order['ori_tax'];
			$ori_total = $order['ori_total'];
			$new_currency = $order['new_currency_code'];
			$new_grand_subtotal = $order['new_grand_subtotal'];
			$new_shipping = $order['new_shipping'];
			$new_tax = $order['new_tax'];
			$new_total = $order['new_total'];
			foreach ($order['item'] as $ld_id => $record)
			{
				$quantity = $record['quantity'];
				$new_price = $record['new_price'];
				$new_subtotal = $record['new_subtotal'];
				if ($show_display == 1)
				{
					$ori_price = $record['ori_price'];
					$ori_subtotal = $record['ori_subtotal'];
				}
			?>
			<div class="media">
				<div class="media-body">
					<span class="col-lg-10 col-md-10 col-xs-6"><b><?php echo HTML::chars($record['title']) . " x " . HTML::chars($quantity) ?></b></span>
					<!--<span class="col-lg-2 col-md-2 text-right"><?php echo HTML::chars($new_price) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_price) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></span>//-->
					<!--<span class="col-lg-1 col-md-1 text-center"><?php echo HTML::chars($quantity) ?></span>//-->
					<span class="col-lg-2 col-md-2 col-xs-6 text-right"><?php echo HTML::chars($new_subtotal) . ' ' . strtoupper( $new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_subtotal) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></span>
				</div>
			</div>
			
			<hr>
			<?php
			}
			?>
			
			<br><br>
			<div class="col-lg-offset-6 col-md-offset-6">
				<table class="table" style="font-size: 12px">
					<tr><td><?php echo I18n::get('subtotal') ?>:</td><td align="right"><?php echo HTML::chars($new_grand_subtotal) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_grand_subtotal) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></td></tr>
					<tr><td><?php echo I18n::get('shipping') ?>:</td><td align="right"><?php echo HTML::chars($new_shipping) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_shipping) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></td></tr>
					<tr><td><?php echo I18n::get('tax') ?>:</td><td align="right"><?php echo HTML::chars($new_tax) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_tax) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></td></tr>
					<tr><td><b><?php echo I18n::get('total') ?>:</b></td><td align="right"><b><?php echo HTML::chars($new_total) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_total) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></b></td></tr>

				</table>
			</div>
			
			<hr>
		
			<div class="col-lg-8 col-md-8 text-left">
				<h3><?php echo $crypto_message ?></h3><br>

				<span class="hidden-xs"><h4><b><?php echo $crypto_address ?></b></h4></span>
				<span class="hidden-lg hidden-md hidden-sm"><?php echo $crypto_address ?></span>
				<br>
				<?php
				if ($enough_balance == 1)
				{
					echo "<b><h4>" . I18n::get('pay_by_account_balance_msg') . "</h4><a href=\"/my/cryptowallet/make_payment?order_id=$order_id\" class=\"btn btn-default\">" . I18n::get('pay_with_account_balance') . "</a></b>";
				}
				?>
			</div>
			<div class="col-lg-4 col-md-4 text-center"><img src="/qr?address=<?php echo $crypto_address ?>"></div>

		</div>
		<?php
		}
		?>
	</div>
</form>	
<script src="<?php echo $cfg["jquery.js"] ?>"></script>