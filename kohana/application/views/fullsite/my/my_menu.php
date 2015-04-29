<div class="col20 content-left">
	<h4><?php echo I18n::get('my_account') ?></h4>
	<div class="list-group">
		<a href="/account/profile" class="list-group-item"><?php echo I18n::get('profile') ?></a>
		<a href="/my/cryptowallet" class="list-group-item"><?php echo I18n::get('wallets') ?></a>
		<a href="/my/purchase" class="list-group-item"><?php echo I18n::get('purchase_history') ?></a>
			<?php
			$array_url = explode('/', Request::current()->uri());
			if (in_array('message', $array_url))
			{
			?>
			<a href="/my/message" class="list-group-item"><span class="menu_disabled"><?php echo I18n::get('messages') ?></span></a>
			<a href="/my/message/inbox" class="list-group-item">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo I18n::get('inbox') ?></a>
			<a href="/my/message/sent" class="list-group-item">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo I18n::get('sent') ?></a>
			<a href="/my/message/archive" class="list-group-item">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo I18n::get('archive') ?></a>
			<a href="/my/message/trash" class="list-group-item">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo I18n::get('trash') ?></a>
			<?php
			}
			else
			{
			?>
		<a href="/my/message" class="list-group-item"><?php echo I18n::get('messages') ?></a>
			<?php
			}
			?>
	</div>
</div>
