<h1>All flights:</h1>
<table class="table">
	<tr>
		<th>Pilot</th>
		<th>Instructor</th>
		<th>Tail No</th>
		<th>Aircraft</th>
		<th>Flight Length</th>
		<th></th>
	</tr>
	<?php foreach ($flights as $flight): ?>
	<tr>
		<td><?php echo $flight['Flight']['studentid']; ?></td>
		<td><?php echo $flight['Flight']['instructorID']; ?></td>
		<td><?php echo $flight['Flight']['tailNo']; ?></td>
		<td><?php echo $flight['Flight']['aircraft']; ?></td>
		<td><?php echo number_format(((int)$flight['Flight']['duration'])/60, 0)." min" ?>
		<td><?php echo $this->Html->link(
			'View Flight',
			array('controller' => 'flights', 'action' => 'view', $flight['Flight']['id']));
			?>
		</td>
	</tr>
	<?php endforeach; ?>
	<?php unset($flight); ?>
</table>