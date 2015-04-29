<?php
$cfg = Kohana::$config->load('general.default');
if (isset($url))
{
?>
<script language="javascript" type="text/javascript">
window.setTimeout('window.location="<?php echo $url?>"; ', 1000);
</script>
<?php
}
?>
<h4><?php echo $header ?></h4><hr>
<div class="alert alert-info alert-block"><?php echo $msg?></div>
<script src="<?php echo $cfg["jquery.js"] ?>"></script>