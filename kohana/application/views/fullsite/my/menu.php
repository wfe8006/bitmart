<div class="col20 content-left">
	<h4><?php echo I18n::get('my_store') ?></h4>
	<div class="list-group">
		<a href="/my/order" class="list-group-item"><?php echo I18n::get('sales_order') ?></a>
		<a href="#" class="list-group-item"><span class="menu_disabled"><?php echo I18n::get('listings') ?></span></a>
		<a href="/my/listing/new" class="list-group-item">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo I18n::get('new_listing') ?></a>
		<a href="/my/listing" class="list-group-item">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo I18n::get('my_listings') ?></a>
		<a href="#" class="list-group-item"><span class="menu_disabled"><?php echo I18n::get('settings') ?></span></a>
		<a href="/my/general" class="list-group-item">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo I18n::get('general') ?></a>
		<a href="/my/payment" class="list-group-item">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo I18n::get('payment') ?></a>
		<a href="/my/shipping" class="list-group-item">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo I18n::get('shipping') ?></a>
		<a href="/my/tax" class="list-group-item">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo I18n::get('taxes') ?></a>
		<a href="/my/physicalstore" class="list-group-item">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo I18n::get('physical_stores') ?></a>
		<a href="/hub/<?php echo Auth::instance()->get_user()->username ?>" class="list-group-item"><?php echo I18n::get('store_url') ?></a>
	</div>
</div>
