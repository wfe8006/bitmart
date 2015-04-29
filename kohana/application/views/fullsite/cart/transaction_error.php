<div class="col-lg-12 col-md-12">
	<h4><?php echo I18n::get('cart') . ' - ' . I18n::get('transaction_error') ?></h4><hr>
	<div class="alert alert-info alert-block<?php echo empty($msg) ? " hidden" : "" ?>"><?php echo $msg?></div>
</div>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>