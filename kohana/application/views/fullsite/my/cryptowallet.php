<?php include "my_menu.php"; ?>
	<div class="col80 content-right">
		<h4><?php echo I18n::get('wallets') ?></h4><hr>
		<?php
		if ($msg)
		{
			echo "<div class=\"alert alert-$alert_type\">$msg</div>";
		}

		//foreach ($array_crypto as $name => $symbol)
		foreach ($cfg_crypto as $symbol => $record)
		{
			$active = $record['active'];
			$name = $record['name'];
			if ($active == 1)
			{
				$balance = $array_balance[$symbol];
				
		?>
		<div class="spacer row">
			<div class="col-lg-3">
				<b><?php echo ucwords($name) ?></b><br><?php echo I18n::get('balance') ?>: <?php echo $balance . ' ' . strtoupper($symbol) ?>
			</div>
			<div class="col-lg-9">
				<div class="btn-group">
				<?php
				if ($balance == 0.00000000)
				{
					echo "<button type=\"button\" class=\"btn btn-default disabled\">" . I18n::get('withdraw') . "</button>";
				}
				else
				{
					echo "<a class=\"btn btn-default\" href=\"/my/cryptowallet/withdraw/$symbol\">" . I18n::get('withdraw') . "</a>";
				}
				?>
				<a class="btn btn-default" href="/my/cryptowallet/transaction/<?php echo $symbol ?>"><?php echo I18n::get('transaction_history') ?></a>
				</div>
			</div>

		</div>

		<?php
			}
		}
		?>

	</div>

</form>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>