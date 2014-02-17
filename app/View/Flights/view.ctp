<h1>Flight <?php echo $flight['Flight']['id'] ?></h1>
<table>
<?php foreach ($csv as $line):?>
	<th><?php echo $line ?></th>
<?php endforeach ?>
</table>