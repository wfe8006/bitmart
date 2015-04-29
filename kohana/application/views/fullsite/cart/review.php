
	<div class="col-lg-12 col-md-12">
		<h4><?php echo I18n::get('cart') . ' - ' . I18n::get('review_order') ?></h4><hr>
		<div class="alert alert-info alert-block<?php echo empty($msg) ? " hidden" : "" ?>"><?php echo $msg?></div>
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
			if (count($array_listing) > 0)
			{
				//grand subtotal in original currency
				//$grand_subtotal = 0;
				foreach ($array_listing as $index => $record)
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
					<span class="col-lg-2 col-md-2 col-xs-6 text-right"><?php echo HTML::chars($new_subtotal) . ' ' . strtoupper( $new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_subtotal) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></span>
				</div>
			</div>
			<hr>
				<?php
				}
				?>
			<br>	
					
			
			
			
			<div class="col-lg-4 col-md-4">
				<?php
				if ($has_shipping == 1)
				{
				?>
				<h5><b><?php echo I18n::get('ship_to_this_address') ?></b></h5>
				<address><?php echo $shipping_address ?></address>
				<?php
				}
				?>
				<p><br></p>
			</div>
			<div class="col-lg-4 col-md-4">
				<?php
				if ($has_shipping == 1)
				{
				?>
				<h5><b><?php echo I18n::get('shipping_service') ?></b></h5>
				<?php echo HTML::chars($shipping_service) ?>
				<?php
				}
				?>
				<p><br></p>
			</div>
			<div class="col-lg-4 col-md-4">
				<h5><b><?php echo I18n::get('payment_method') ?></b></h5>
				<?php echo $payment_method ?>
				<p><br></p>
			</div>
			<div class="clearfix"></div>
				
			<br><br>
			<div class="col-lg-offset-6 col-md-offset-6">
				<table class="table" style="font-size: 12px">
					<tr><td><?php echo I18n::get('subtotal') ?>:</td><td align="right"><?php echo HTML::chars($new_grand_subtotal) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_grand_subtotal) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></td></tr>
					<tr><td><?php echo I18n::get('shipping') ?>:</td><td align="right"><?php echo HTML::chars($new_shipping) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_shipping) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></td></tr>
					<tr><td><?php echo I18n::get('tax') ?>:</td><td align="right"><?php echo HTML::chars($new_tax) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_tax) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></td></tr>
					<tr><td><b><?php echo I18n::get('total') ?>:</b></td><td align="right"><b><?php echo HTML::chars($new_total) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_total) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></b></td></tr>

				</table>
			</div>
		
			<form id="fcontent" name="fcontent" action="/cart/order" method="post">
				<div class="text-center"><button id="edit_order" type="button" class="btn btn-default"><?php echo I18n::get('edit_order') ?></button> <button id="submit_order" type="button" class="btn btn-default"><?php echo I18n::get('submit_order') ?></button></div>
				<input type="hidden" name="id" value="<?php echo $confirmation_id ?>">
			</form>
		
			
			<br>
			
		</div>
		<div class="clearfix"></div>	
			<?php
		}
		?>
	</div>

<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script>
$(document).ready(function() {
	$('.btn').each(function(index, element)
	{
		$(element).click(function(){
			if (element.id == 'edit_order')
			{
				window.location.href = "/cart";
			}
			else if (element.id == 'submit_order')
			{
				document.fcontent.submit()
			}
		});
	});
});	
</script>
