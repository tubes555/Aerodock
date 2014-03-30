<h1>
<?php if(Authcomponent::user('type') == 'admin')
				 echo "All users:"; 
			else 
				echo "All students:"; ?></h1>
<?php if(Authcomponent::user('type') != 'teacher'){
	echo $this->Html->link(
		'Add user',
		array('controller' => 'users', 'action' => 'add'));
	}?>
<table class="table">
	<tr>
		<th>First Name</th>
		<th>Last Name</th>
		<th>Pipeline ID</th>
		<?php if(Authcomponent::user('type') != 'teacher'):?>
		<th>Type</th>
		<?php endif ?>
		<th>Edit</th>
	</tr>
	<?php foreach ($users as $user): ?>
	<tr>
		<td><?php echo $user['User']['firstname']; ?></td>
		<td><?php echo $user['User']['lastname']; ?></td>
		<td><?php echo $user['User']['username']; ?></td>
		<?php if(Authcomponent::user('type') != 'teacher'):?>
		<td><?php echo $user['User']['type']; ?></td>
		<?php endif ?>
		<td><?php echo $this->Html->link(
			'Edit User',
			array('controller' => 'users', 'action' => 'edit', $user['User']['id']));
			?></td>
	</tr>
	<?php endforeach; ?>
	<?php unset($flight); ?>
</table>