<?php include  __DIR__ . "/../my_menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo I18n::get('order') . ' #' . $order_id ?></h4><hr>
		<div class="alert alert-success alert-block<?php echo empty($msg) ? " hidden" : "" ?>"><?php echo $msg?></div>

		
		
		
		<br>
		<h4><b><?php echo I18n::get('order_summary') ?></b></h4><br>
		<div class="row">
			<?php
			if ($order_status == 13)
			{
				$order_status_name = I18n::get('cancelled') . ' (' . $array_order['cancel']['reason'] . ')';
				echo "<div class=\"col-lg-12\"><h5>" . I18n::get('order_status') . ": <b>$order_status_name</b></h5></div>";
			}
			else if ($order_status == 12)
			{
				echo "<div class=\"col-lg-12\"><h5>" . I18n::get('order_status') . ": <b>" . I18n::get('completed') . "</b></h5></div>";
			}
			else
			{
				if ($url == 'purchase')
				{
					foreach($order_status_obj as $record)
					{
						if ($record['id'] == $order_status)
						{
							$selected = ' selected';
							$order_status_name = $record['name'];
						}
						else
						{
							$selected = '';
						}
					}
					echo "<div class=\"col-lg-12\"><h5>" . I18n::get('order_status') . ": <b>$order_status_name</b></h5></div>";
				}
				else
				{
				?>
				<form id="fcontent_status" name="fcontent_status" action="/my/order/detail" method="post">
				<div class="col-lg-2 col-md-2"><?php echo I18n::get('order_status') ?></div>
				<div class="col-lg-4 col-md-4 col-xs-6">
					<select class="form-control" id="order_status" name="order_status">
					<?php
					foreach($order_status_obj as $record)
					{
						$selected = $record['id'] == $order_status ? ' selected' : '';
						if ($record['id'] != 13)
						{
							echo "<option value=\"{$record['id']}\"$selected>{$record['name']}</option>";
						}
					}
					?>
					</select>
				</div>
				<div class="col-lg2 col-md-2 col-xs-6"><input class="btn btn-default" name="submit_order_status" type="submit" value="<?php echo I18n::get('submit') ?>"></div>
				<input type="hidden" name="id" value="<?php echo $order_id ?>">
				</form>
				
				<div class="clearfix"></div>
				<?php
				}
			}
			if ($is_crypto == 1)
			{
				if (count($crypto_transaction_obj) > 0)
				{
					$crypto = $crypto_transaction_obj[0]['crypto'];
					$confirmation = $crypto_transaction_obj[0]['confirmation'];
					$time = date('M d, Y h:i', $crypto_transaction_obj[0]['time']);
					$amount = ($crypto_transaction_obj[0]['amount'] / 1e8) . ' ' . strtoupper($crypto);
					$status = bindec($crypto_transaction_obj[0]['status']);
					if ($status == 0)
					{
						$confirmation_status = $confirmation >= $min_confirmation ? '' : "$confirmation/$min_confirmation " . I18n::get('confirmations');
					}
					$payment_received = "$amount - $time $confirmation_status";
					
				}
				else
				{
					$payment_received = I18n::get('No');
				}
				$payment_released = count($array_order['escrow']['released']) > 0 ? I18n::get('yes') : I18n::get('no');
				echo "<div class=\"col-lg-12 col-md-12\"><h5>" . I18n::get('escrow') . ": <b>" . I18n::get('yes') . "</b></h5></div>";
				echo "<div class=\"col-lg-12 col-md-12\"><h5>" . I18n::get('payment_received') . ": <b>$payment_received</b></h5></div>";
				echo "<div class=\"col-lg-12 col-md-12\"><h5>" . I18n::get('payment_released') . ": <b>$payment_released</b></h5></div>";
			}

			// if order_status != cancelled and there's a request for payment release
			if ($url == 'purchase' AND ! in_array($order_status, array(12, 13)) AND $array_order['escrow']['requested'] > 0 AND $array_order['escrow']['released'] < 1) 
			{
				$request_release_message = sprintf(I18n::get('payment_release_request_message'), date('M d, Y h:i', $array_order['escrow']['requested']));
			?>
			<form id="fcontent_accept" name="fcontent_accept" action="/my/purchase/detail" method="post">
			<div class="alert alert-danger" id="div_accept">
				<h4><?php echo $request_release_message ?></h4>
				<br>
				<p><div class="checkbox"><label><input type="checkbox" id="action_accept" name="action_accept" class="checkbox" value="1"><b><?php echo I18n::get('release_payment_agree') ?></b></label></div><div id="error_accept"></div></p>
				<p>
					<input class="btn btn-default" id="submit_accept" name="submit_accept" type="submit" value="<?php echo I18n::get('accept') ?>" />
					<input class="btn btn-default cancel" id="submit_deny" name="submit_deny" type="submit" value="<?php echo I18n::get('deny') ?>" />
				</p>
			</div>
			<input type="hidden" name="id" value="<?php echo $order_id ?>">
			</form>
			<?php
			}
			?>
			
		</div>
	
		<br>
		<div class="btn-group">
			<?php
			if ( ! in_array($order_status, array(12, 13)))
			{
				//awaiting payment, no escrow button is shown
				if (count($array_order['escrow']['released']) == 0 AND $order_status != 2)
				{
					//show request release only when seller sets the status to shipped
					if ($url == 'purchase')
					{
						echo "<button id=\"btn_release\" name=\"btn_release\" type=\"button\" class=\"btn btn-default action\">" . I18n::get('release_payment') . "</button>";
					}
					else
					{
						if ($order_status == 11)
						{
							echo "<button id=\"btn_request\" name=\"btn_request\ type=\"button\" class=\"btn btn-default action\">" . I18n::get('request_release') . "</button>";
						}
					}
				}
				if (count($array_order['dispute']) < 1)
				{
					echo "<button id=\"btn_dispute\" name=\"btn_dispute\" type=\"button\" class=\"btn btn-default action\">" . I18n::get('open_dispute') . "</button>";
				}
				echo "<button id=\"btn_cancel\" name=\"btn_cancel\" type=\"button\" class=\"btn btn-default action\">" . I18n::get('cancel_order') . "</button>";
			}
			if (isset($user_rating_obj))
			{
				echo "<button id=\"btn_feedback\" name=\"btn_feedback\" type=\"button\" class=\"btn btn-default action\">" . I18n::get('leave_feedback'). "</button>";
			}
			?>

		</div>
		<br>
		<br>
		
		<form id="fcontent_release" name="fcontent_release" action="/my/purchase/detail" method="post">
		<div class="alert alert-danger hidden div_action" id="div_release">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<h4><?php echo I18n::get('release_payment_message') ?></h4>
			<br>
			<p><div class="checkbox"><label><input type="checkbox" id="action_release" name="action_release" class="checkbox" value="1"><b><?php echo I18n::get('release_payment_agree') ?></b></label></div><div id="error_release"></div></p>
			<p>
				<input class="btn btn-default" id="submit_release" name="submit_release" type="submit" value="<?php echo I18n::get('submit') ?>" />
				<button type="button" class="btn btn-default btn_close"><?php echo I18n::get('close') ?></button>
			</p>
		</div>
		<input type="hidden" name="id" value="<?php echo $order_id ?>">
		</form>
		
		
		<form id="fcontent_request" name="fcontent_request" action="/my/order/detail" method="post">
			<div class="alert alert-danger hidden div_action" id="div_request">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				<h4><?php echo I18n::get('request_release_message') ?></h4>
				<br>
				<p><div class="checkbox"><label><input type="checkbox" id="action_request" name="action_request" class="checkbox" value="1"><b><?php echo I18n::get('request_release_agree') ?></b></label></div><div id="error_request"></div></p>
				<p>
					<input class="btn btn-default" id="submit_request" name="submit_request" type="submit" value="<?php echo I18n::get('submit') ?>" />
					<button type="button" class="btn btn-default btn_close"><?php echo I18n::get('close') ?></button>
				</p>
			</div>
			<input type="hidden" name="id" value="<?php echo $order_id ?>">
		</form>
			  

		<form id="fcontent_dispute" name="fcontent_dispute" action="/my/<?php echo $url ?>/detail" method="post">
		<div class="alert alert-danger hidden div_action" id="div_dispute">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<h4><?php echo I18n::get('open_dispute_message') ?></h4>
			<br>
			<p><div class="checkbox"><label><input type="checkbox" id="action_dispute" name="action_dispute" class="checkbox" value="1"><b><?php echo I18n::get('open_dispute_agree') ?></b></label></div><div id="error_dispute"></div></p>
			<p>
				<input class="btn btn-default" id="submit_release" name="submit_dispute" type="submit" value="<?php echo I18n::get('submit') ?>" />
				<button type="button" class="btn btn-default btn_close"><?php echo I18n::get('close') ?></button>
			</p>
		</div>
		<input type="hidden" name="id" value="<?php echo $order_id ?>">
		</form>
		

		<form id="fcontent_cancel" name="fcontent_cancel" action="/my/<?php echo $url ?>/detail" method="post">
		<div class="alert alert-danger hidden div_action" id="div_cancel">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<h4><?php echo I18n::get('cancel_order_message') ?></h4>
			<br>
			<div class="row">
				<label class="col-lg-3 col-md-3 control-label" for="cancellation_reason"><?php echo I18n::get('reason_for_cancellation') ?></label>
				<div class="col-lg-8 col-md-8">
					<input class="form-control" id="cancellation_reason" name="cancellation_reason" type="text">
				</div>
			</div>
			<br>
			<p><div class="checkbox"><label><input type="checkbox" id="action_cancel" name="action_cancel" class="checkbox" value="1"><b><?php echo I18n::get('cancel_order_agree') ?></b></label></div><div id="error_cancel"></div></p>
			<p>
				<input class="btn btn-default" id="submit_cancel" name="submit_cancel" type="submit" value="<?php echo I18n::get('submit') ?>" />
				<button type="button" class="btn btn-default btn_close"><?php echo I18n::get('close') ?></button>
			</p>
		</div>
		<input type="hidden" name="id" value="<?php echo $order_id ?>">
		</form>
		
		
		<?php
		if (isset($user_rating_obj))
		{
		?>
		<form id="fcontent_feedback" name="fcontent_feedback" action="/my/<?php echo $url ?>/detail" method="post">
		<div class="alert alert-info hidden div_action" id="div_feedback">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<div class="clearfix"></div>
			<div class="row" id="error_feedback">
				<span class="col-lg-2 col-xs-12"><b><?php echo I18n::get('overall_rating') ?></b></span>
				<span class="col-lg-10">
					<span class="col-xs-12">
						<label class="radio-inline">
							<input type="radio" name="rating" value="1"<?php echo $rating == 1 ? ' checked' : '' ?>><?php echo I18n::get('very_dissatisfied') ?>
						</label>
					</span>
					<span class="col-xs-12">
						<label class="radio-inline">
							<input type="radio" name="rating" value="2"<?php echo $rating == 2 ? ' checked' : '' ?>><?php echo I18n::get('dissatisfied') ?>
						</label>
					</span>
					<span class="col-xs-12">
						<label class="radio-inline">
							<input type="radio" name="rating" value="3"<?php echo $rating == 3 ? ' checked' : '' ?>><?php echo I18n::get('neutral') ?>
						</label>
					</span>
					<span class="col-xs-12">
						<label class="radio-inline">
							<input type="radio" name="rating" value="4"<?php echo $rating == 4 ? ' checked' : '' ?>><?php echo I18n::get('satisfied') ?>
						</label>
					</span>
					<span class="col-xs-12">
						<label class="radio-inline">
							<input type="radio" name="rating" value="5"<?php echo $rating == 5 ? ' checked' : '' ?>><?php echo I18n::get('very_satisfied') ?>
						</label>
					</span>
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
		</div>
		<input type="hidden" name="id" value="<?php echo $order_id ?>">
		</form>
		<?php
		}
		?>
		
		<div class="clearfix"></div>

		<?php
		$currency = $array_order['new_currency_code'];
		//only show qr code to the buyer
		if (in_array($array_order['order_status'], array(2)) AND $is_crypto == 1 AND $url == 'purchase')
		{
			$total = $array_order['new_total'] . ' ' . strtoupper($currency);
			$payment_method_name = $array_order['payment_method'];
			$crypto_address = $array_order['crypto_address'];
			$crypto_message = sprintf(I18n::get('please_send_amount_to'), $total, $payment_method_name);
		?>
		
		
		<div class="col-lg-12 col-md-12 panel panel-default">
			<div class="panel-body">
				<div class="col-lg-4 col-md-4"><img src="/qr?address=<?php echo $crypto_address ?>"></div>
				<div class="col-lg-8 col-md-8 text-left">
					<h3><?php echo $crypto_message ?></h3><br>
					<b><h4><?php echo $crypto_address ?></h4></b>
					<br>
					<?php
					if ($enough_balance == 1)
					{
						echo "<b><h4>" . I18n::get('pay_by_account_balance_msg') . "</h4><a href=\"/my/cryptowallet/make_payment?order_id=$order_id\" class=\"btn btn-default\">" . I18n::get('pay_with_account_balance') . "</a></b>";
					}
					?>
				</div>
			</div>
		</div>
		<?php
		}
		?>

	
		
		
	
		
		<br>
		<hr>
		<h4><b><?php echo I18n::get('order_details') ?></b></h4><br>
		<div class="col-lg-12 col-md-12 panel panel-default">
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
				<span class="col-lg-10 col-md-10 col-xs-6"><b><?php echo HTML::chars($record['title']) . " x " . HTML::chars($quantity) ?></b></span>
				<span class="col-lg-2 col-md-2 col-xs-6 text-right"><?php echo HTML::chars($new_subtotal) . ' ' . strtoupper( $new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_subtotal) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></span>
			</div>
		</div>
		
		<hr>
		
		<?php
		}
		?>

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
			<p><br></p>
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
			<p><br></p>
		</div>
		<div class="col-lg-4 col-md-4">
			<h5><b><?php echo I18n::get('payment_method') ?></b></h5>
			<?php echo HTML::chars($array_order['payment_method']) ?>
			<p><br></p>
		</div>
		
		
		<div class="clearfix"></div>
		
		<div class="col-lg-4 col-md-4">
			<h5><b><?php echo I18n::get('buyer') ?></b></h5>
			<a href="/hub/<?php echo $buyer ?>"><?php echo $buyer ?></a>
			<p><br></p>
		</div>
		<div class="col-lg-4 col-md-4">
			<h5><b><?php echo I18n::get('seller') ?></b></h5>
			<a href="/hub/<?php echo $seller ?>"><?php echo $seller ?></a>
			<p><br></p>
		</div>
		<div class="col-lg-4 col-md-4">
			<h5><b><?php echo I18n::get('order_date') ?></b></h5>
			<?php echo $order_date ?>
			<p><br></p>
		</div>		
		<div class="clearfix"></div>

		<p><br></p>
		<p><br></p>
		<div class="col-lg-offset-6 col-md-offset-6">
			<table class="table" style="font-size: 12px">
				<tr><td><?php echo I18n::get('subtotal') ?>:</td><td align="right"><?php echo HTML::chars($new_grand_subtotal) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_grand_subtotal) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></td></tr>
				<?php
				if ($array_order['has_shipping'] == 1)
				{
				?>
				<tr><td><?php echo I18n::get('shipping') ?>:</td><td align="right"><?php echo HTML::chars($new_shipping) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_shipping) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></td></tr>
				<tr><td><?php echo I18n::get('tax') ?>:</td><td align="right"><?php echo HTML::chars($new_tax) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_tax) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></td></tr>
				<?php
				}
				?>
				<tr><td><b><?php echo I18n::get('total') ?>:</b></td><td align="right"><b><?php echo HTML::chars($new_total) . ' ' . strtoupper($new_currency) ?><?php echo $show_display == 1 ? "<br><span class=\"text-muted\">" . HTML::chars($ori_total) . ' ' . strtoupper($ori_currency) . "</span>" : '' ?></b></td></tr>

			</table>
		</div>
		
		<p><br></p>
		</div>
		
		<?php
		if (count($digital_content_obj) > 0)
		{
		?>
		<br>
		<hr>
		<h4><b><?php echo I18n::get('digital_content') ?></b></h4><br>
		
		<?php
			if ($url == 'order')
			{
				echo "<div class=\"alert alert-info\">" . I18n::get('digital_content_delivered_msg') . "</div>";
			}
			foreach ($digital_content_obj as $record)
			{
				$listing_data_id = $record['listing_data_id'];
				$content = $record['content'];
				$title = $array_order['item'][$listing_data_id]['title'];
			?>
		<div class="row spacer">
			<span class="col-lg-4"><?php echo $title ?></span>
			<span class="col-lg-4"><?php echo $content ?></span>
		</div>
		<?php
			}
		}
		?>
		
		<br>
		<hr>
		<h4><b><?php echo I18n::get('message_board') ?></b></h4><br>
		<?php
		if (count($order_message_obj) > 0)
		{
			foreach ($order_message_obj as $record)
			{
				$date = date('M d, Y h:i', $record['posted']);
				$username = $record['username'];
			?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><?php echo "$date $username" ?></h3>
			</div>
			<div class="panel-body">
				<div id="comment_<?php echo $record['id'] ?>"><?php echo nl2br(HTML::chars($record['message'])) ?></div>
				<?php
				if ($record['user_id'] == $user_id)
				{
				?>
				<hr>
				<div class="btn-group">
					<button id="edit_<?php echo $record['id'] ?>" name="edit_<?php echo $record['id'] ?>" type="button" class="btn btn-default edit"><?php echo I18n::get('edit') ?></button>
					<!--
					<button id="edit_<?php echo $record['id'] ?>" name="delete_<?php echo $record['id'] ?>" type="button" class="btn btn-default delete"><?php echo I18n::get('delete') ?></button>
					//-->
				</div>
				<?php
				}
				?>
			</div>
		</div>
		<br>
		<?php
			}
		}
		?>
		<form id="fcontent_status" name="fcontent_status" action="/my/<?php echo $url ?>/detail" method="post">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><?php echo I18n::get('new_message') ?></h3>
				</div>
				<div class="panel-body">
					<textarea class="form-control" class="form-control" id="message" name="message" cols="10" rows="6" placeholder="<?php echo I18n::get('message') ?>"><?php echo nl2br(HTML::chars(Arr::get($_POST, "message"))) ?></textarea>
					<div class="error" id="emessage"><?php echo $errors['message'] ?></div>
					<br>
					<p class="text-center">
						<input id="submit_message" name="submit_message" type="submit" class="btn btn-default" value="<?php echo I18n::get('submit') ?>">
						<input type="hidden" name="id" value="<?php echo $order_id ?>">
					</p>
				</div>
			</div>
		</form>
		<br>
	</div>


<script src="<?php echo $cfg["jquery.js"] ?>"></script>
<script src="<?php echo $cfg["jquery_validate.js"] ?>"></script>
<script>
$(document).ready(function() {
	
	jQuery.validator.addMethod("check_rating",function(value) {
		return ($('input[name=rating]:checked').val() > 0);
	}, "This field is required.");
	
	var validator = $('form#fcontent_accept').validate({
		rules: {
			action_accept: {
				required: true,
			}
		},
		errorPlacement: function(error, element) {
			error.insertAfter("#error_accept");
		}
	});
	
	var validator = $('form#fcontent_request').validate({
		rules: {
			action_request: {
				required: true,
			}
		},
		errorPlacement: function(error, element) {
			error.insertAfter("#error_request");
		}
	});

	var validator = $('form#fcontent_release').validate({
		rules: {
			action_release: {
				required: true,
			}
		},
		errorPlacement: function(error, element) {
			error.insertAfter("#error_release");
		}
	});
	
	var validator = $('form#fcontent_dispute').validate({
		rules: {
			action_dispute: {
				required: true,
			}
		},
		errorPlacement: function(error, element) {
			error.insertAfter("#error_dispute");
		}
	});
	
	var validator = $('form#fcontent_cancel').validate({
		rules: {
			action_cancel: {
				required: true,
			},
			cancellation_reason: {
				required: true,
			},
			
		},
		errorPlacement: function(error, element) {
			if (element.attr("name") == 'action_cancel')
			{
				error.insertAfter("#error_cancel");
			}
			else
			{
				error.insertAfter(element);
			}
		}
	});
	
	var validator = $('form#fcontent_feedback').validate({
		rules: {
			rating: {
				check_rating: true,
			},
		},
		errorPlacement: function(error, element) {
			error.insertAfter("#error_feedback");
		}

	});
	
	
	function divClicked() 
	{
		//var divHtml = $(this).html();
		value = $(this).attr("id");
		id = value.split('_')[1];
		var divHtml = $("#comment_" + id).html();
		var editableText = $('<textarea id="comment_'+ id + '"class="form-control" class="form-control" cols="10" rows="6">');
		editableText.val(divHtml);
		$("#comment_" + id).replaceWith(editableText);
		editableText.focus();
		editableText.blur(editableTextBlurred);
	}
	
	function editableTextBlurred() {
		//var html = $(this).val();
		value = $(this).attr("id");
		id = value.split('_')[1];
		var html = $("#comment_" + id).val();
		console.log(html);
		var viewableText = $('<div id="comment_'+ id + '"></div>');
		viewableText.html(html);
		$("#comment_" + id).replaceWith(viewableText);
		$(viewableText).click(divClicked);
		$.post('/json/edit_message', { message: html, id: id}, function(data){
		}, "json");
	}
	
	$('.edit').each(function(index, element)
	{
		$(element).click(divClicked);
	});
	
	$('.close').each(function(index, element)
	{
		$(element).click(function(){
			$('.div_action').addClass('hidden');
	});
	});
	
	$('.btn_close').each(function(index, element)
	{
		$(element).click(function(){
			$('.div_action').addClass('hidden');
		});
	});
	
	$('.action').click(function() {
		value = $(this).attr("id");
		name = value.split('_')[1];
		if ($('#div_' + name).hasClass('hidden'))
		{
			$('.div_action').addClass('hidden');
			$('#div_' + name).removeClass('hidden');
		}
		else
		{
			$('#div_' + name).addClass('hidden');
		}
	});


});

</script>