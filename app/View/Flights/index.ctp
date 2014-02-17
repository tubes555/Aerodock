<h1>All flights:</h1>

<?php echo $this->Html->link(
	'Add Flight',
	array('controller' => 'flights', 'action' => 'add'));
	?>
<table>
	<tr>
		<th>Student</th>
		<th>Instructor</th>
		<th>Tail No</th>
		<th>Created</th>
	</tr>
	<?php foreach ($flights as $flight): ?>
	<tr>
		<td><?php echo $flight['Flight']['studentID']; ?></td>
		<td><?php echo $flight['Flight']['instructorID']; ?></td>
		<td><?php echo $flight['Flight']['tailNo']; ?></td>
		<td><?php echo $flight['Flight']['date']; ?></td>
	</tr>
	<?php endforeach; ?>
	<?php unset($flight); ?>
</table>