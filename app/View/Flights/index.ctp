<h1>All flights:</h1>
<?php if($type != 'student'){
	echo $this->Html->link(
		'Add Flight',
		array('controller' => 'flights', 'action' => 'add'));
	}?>
<table class="table">
	<tr>
		<th>Student</th>
		<th>Instructor</th>
		<th>Tail No</th>
		<th>Aircraft</th>
		<th></th>
	</tr>
	<?php foreach ($flights as $flight): ?>
	<tr>
		<td><?php echo $flight['Flight']['studentid']; ?></td>
		<td><?php echo $flight['Flight']['instructorID']; ?></td>
		<td><?php echo $flight['Flight']['tailNo']; ?></td>
		<td><?php echo $flight['Flight']['aircraft']; ?></td>
		<td><?php echo $this->Html->link(
			'View Flight',
			array('controller' => 'flights', 'action' => 'view', $flight['Flight']['id']));
			?>
		</td>
		<?php if(Authcomponent::user('type') != 'student'):?>
		<td><?php echo $this->Form->postLink(
			'Delete Flight',
			array('action' => 'delete', $flight['Flight']['id'] ),
			array('confirm' => 'Are you sure?')
			);
			?>
		</td>	
		<?php endif ?>
	</tr>
	<?php endforeach; ?>
	<?php unset($flight); ?>
</table>