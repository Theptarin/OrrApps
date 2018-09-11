<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php 
foreach($css_files as $file): ?>
	<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
<?php endforeach; ?>
</head>
<body>
	<div style="padding: 20px">
		Menu: <a href='<?php echo site_url('examples/customers_management')?>'>Customers</a>
	</div>
    <div style="padding: 20px">
		<?php echo $output; ?>
    </div>
    <?php foreach($js_files as $file): ?>
		<script src="<?php echo $file; ?>"></script>
	<?php endforeach; ?>
</body>
</html>
