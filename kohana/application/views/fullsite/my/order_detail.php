<form id="fcontent" name="fcontent" action="/my/order/detail" method="post">
<?php include "menu.php"; ?>
	<div class="col2 col-lg-10 col-md-10">
		<h4><?php echo I18n::get('order') . ' #' . $order_id ?></h4><hr>
		<div class="alert alert-success alert-block<?php echo empty($msg) ? " hidden" : "" ?>"><?php echo $msg?></div>

		<div class="well col-lg-6 col-md-6">
			<label class="col-lg-3 col-md-3 control-label" for="order_status"><?php echo I18n::get('order_status') ?></label>
			<div class="col-lg-7 col-md-7">
				<select class="form-control" id="order_status" name="order_status">
				<?php
				foreach($order_status_obj as $record)
				{
					$selected = $record['id'] == $order_status ? ' selected' : '';
					echo "<option value=\"{$record['id']}\"$selected>{$record['name']}</option>";
				}
				?>
				</select>
			</div>
			<input class="btn btn-default" name="submit_order_status" type="submit" value="<?php echo I18n::get('submit') ?>">
		</div>
		<div class="clearfix"></div>
		<div class="media">
			<div class="media-body">
				<span class="col-lg-7 col-md-7"></span>
				<span class="col-lg-2 col-md-2 text-right"><b><?php echo I18n::get('price') ?></b></span>
				<span class="col-lg-1 col-md-1"><b><?php echo I18n::get('quantity') ?></b></span>
				<span class="col-lg-2 col-md-2 text-right"><b><?php echo I18n::get('subtotal') ?></b></span>
			</div>
		</div>
		<hr>
		<?php
		$show_display = $array_order['show_display'];
		$new_currency = $array_order['new_currency_code'];
		$new_grand_subtotal = $array_order['new_grand_subtotal'];
		$new_shipping = $array_order['new_shipping'];
		$new_tax = $array_order['new_tax'];
		$new_total = $array_order['new_total'];
		if ($show_display == 1)
		{
			$ori_currency = $array_order['ori_currency_code'];	
			$ori_grand_subtotal = $array_order['ori_grand_subtotal'];
			$ori_shipping = $array_order['ori_shipping'];
			$ori_tax = $array_order['ori_tax'];
			$ori_total = $array_order['ori_total'];
		}
		foreach ($array_order['item'] as $index => $record)
		{
			$title = HTML::chars($record['title']);
			$new_price = $record['new_price'];
			$ori_price = $record['ori_price'];
			$new_subtotal = $record['new_subtotal'];
			$ori_subtotal = $record['ori_subtotal'];
			$quantity = $record['quantity'];
			$uid = $record['uid'];
		?>
		<div class="media">
			<div class="media-body">
				<span class="col-lg-7 col-md-7"><b><a href="/st/<?php echo $uid ?>"><?php echo HTML::chars($title) ?></a></b></span>
				<span class="col-lg-2 col-md-2 text-right"><?php echo HTML::chars($new_price) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_price) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></span>
				<span class="col-lg-1 col-md-1 text-center"><?php echo HTML::chars($quantity) ?></span>
				<span class="col-lg-2 col-md-2 text-right"><?php echo HTML::chars($new_subtotal) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_subtotal) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></span>
			</div>
		</div>
		<hr>
		<?php
		}
		?>
		<br>	
		<div class="col-lg-12 col-md-12 panel panel-default">
			<div class="panel-body">
				<div class="col-lg-8 col-md-8">
					<div class="col-lg-4 col-md-4">
						<?php
						if ($array_order['has_shipping'] == 1)
						{
						?>
						<h5><b><?php echo I18n::get('ship_to') ?></b></h5>
						<?php echo $shipping_address ?>
						<?php
						}
						?>
					</div>
					<div class="col-lg-4 col-md-4">
						<?php
						if ($array_order['has_shipping'] == 1)
						{
						?>
						<h5><b><?php echo I18n::get('shipping_service') ?></b></h5>
						<?php echo $array_order['shipping_service'] ?>
						<?php
						}
						?>
					</div>
					<div class="col-lg-4 col-md-4">
						<h5><b><?php echo I18n::get('payment_method') ?></b></h5>
						<?php echo HTML::chars($array_order['payment_method']) ?>
					</div>
					<div class="clearfix"></div><br>
					<div class="col-lg-4 col-md-4">
						<h5><b><?php echo I18n::get('buyer') ?></b></h5>
						<a href="/hub/<?php echo $buyer ?>"><?php echo $buyer ?></a>
					</div>
					<div class="col-lg-4 col-md-4">
						<h5><b><?php echo I18n::get('seller') ?></b></h5>
						<a href="/hub/<?php echo $seller ?>"><?php echo $seller ?></a>
					</div>
					<div class="col-lg-4 col-md-4">
						<h5><b><?php echo I18n::get('order_date') ?></b></h5>
						<?php echo $order_date ?>
					</div>		
				</div>
				<div class="col-lg-4 col-md-4 text-right">
					<div class="col-lg-4 col-md-4 text-right"><h5><?php echo I18n::get('subtotal') ?>:</h5></div><div class="col-lg-8 col-md-8 text-right"><h5><?php echo HTML::chars($new_grand_subtotal) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_grand_subtotal) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></h5></div>
					<?php
					if ($array_order['has_shipping'] == 1)
					{
					?>
					<div class="col-lg-4 col-md-4 text-right"><h5><?php echo I18n::get('shipping') ?>:</h5></div><div class="col-lg-8 col-md-8 text-right"><h5><?php echo HTML::chars($new_shipping) . ' ' . strtoupper( $new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_shipping) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></h5></div>
					<div class="col-lg-4 col-md-4 text-right"><h5><?php echo I18n::get('tax') ?>:</h5></div><div class="col-lg-8 col-md-8 text-right"><h5><?php echo HTML::chars($new_tax) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_tax) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></h5></div>
					<?php
					}
					?>
					<div class="col-lg-4 col-md-4 text-right"><h5><b><?php echo I18n::get('total') ?>:</b></h5></div><div class="col-lg-8 col-md-8 text-right"><h5><b><?php echo HTML::chars($new_total) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_total) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></b></h5></div>
				</div>
			</div>
			
		</div>
		<?php
		if (isset($user_rating_obj))
		{
		?>
		<br>
		<hr>
		<h5><b><?php echo I18n::get('leave_feedback_for') . " " . I18n::get('order') . ' #' . $order_id ?></b></h5><br>
		<div class="row">
			<span class="col-lg-2"><b><?php echo I18n::get('overall_rating') ?></b></span>
			<span class="col-lg-10">
				<label class="radio-inline">
					<input type="radio" id="rating" name="rating" value="1"<?php echo $rating == 1 ? ' checked' : '' ?>><?php echo I18n::get('very_dissatisfied') ?>
				</label>
				<label class="radio-inline">
					<input type="radio" id="rating" name="rating" value="2"<?php echo $rating == 2 ? ' checked' : '' ?>><?php echo I18n::get('dissatisfied') ?>
				</label>
				<label class="radio-inline">
					<input type="radio" id="rating" name="rating" value="3"<?php echo $rating == 3 ? ' checked' : '' ?>><?php echo I18n::get('neutral') ?>
				</label>
				<label class="radio-inline">
					<input type="radio" id="rating" name="rating" value="4"<?php echo $rating == 4 ? ' checked' : '' ?>><?php echo I18n::get('satisfied') ?>
				</label>
				<label class="radio-inline">
					<input type="radio" id="rating" name="rating" value="5"<?php echo $rating == 5 ? ' checked' : '' ?>><?php echo I18n::get('very_satisfied') ?>
				</label>
			</span>
		</div>
		<br>
		<div class="row">
			<span class="col-lg-2"><b><?php echo I18n::get('your_feedback') ?></b></span>
			<span class="col-lg-8"><input class="form-control" id="feedback" name="feedback" maxlength="100" value="<?php echo HTML::chars($feedback) ?>"></span>
		</div>
		<br>
		<div class="row">
			<p class="text-center"><input name="submit_rating" class="btn btn-default" type="submit" value="<?php echo I18n::get('submit') ?>" /></p>
		</div>
		<?php
		}
		?>
	</div>
	<input type="hidden" name="id" value="<?php echo $order_id ?>">
</form>	
<script src="<?php echo $cfg["jquery.js"] ?>"></script>