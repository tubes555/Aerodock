<h1>All flights:</h1>
<?php if($type != 'student'){
	echo $this->Html->link(
		'Add Flight',
		array('controller' => 'flights', 'action' => 'add'));
	}?>
<table class="table">
	<tr>
		<?php if(Authcomponent::user('type') != 'student'): ?>
		<th>Student</th>
		<?php endif ?>
		<th>Instructor</th>
		<th>Tail No</th>
		<th>Aircraft</th>
		<th>Flight Length</th>
		<th></th>
	</tr>
	<?php foreach ($flights as $flight): ?>
	<tr>
		<?php if(Authcomponent::user('type') != 'student'): ?>
		<td><?php echo $flight['Flight']['studentid']; ?></td>
		<?php endif ?>
		<td><?php echo $flight['Flight']['instructorID']; ?></td>
		<td><?php echo $flight['Flight']['tailNo']; ?></td>
		<td><?php echo $flight['Flight']['aircraft']; ?></td>
		<td><?php echo number_format(((int)$flight['Flight']['duration'])/60, 0)." min" ?>
		<td><?php echo $this->Html->link(
			'View Flight',
			array('controller' => 'flights', 'action' => 'view', $flight['Flight']['id']));
			?>
		</td>
		<?php if(Authcomponent::user('type') != 'student'):?>
		<td><?php 
			if(Authcomponent::user('type') == 'admin')
			{
				echo $this->Form->postLink(
				'Delete Flight',
				array('action' => 'delete', $flight['Flight']['id'] ),
				array('confirm' => 'Are you sure?')
				);
			}
			?>
		</td>	
		<?php endif ?>
	</tr>
	<?php endforeach; ?>
	<?php unset($flight); ?>
</table>