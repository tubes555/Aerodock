<h1>
<?php if(Authcomponent::user('type') == 'admin')
				 echo "All users:"; 
			else 
				echo "All students:"; ?></h1>
<?php if(Authcomponent::user('type') == 'admin'){
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
		<?php if(Authcomponent::user('type') == 'admin'):?>
		<th>Delete</th>
		<?php endif ?>
	</tr>
	<?php foreach ($users as $user): ?>
		<tr>
		<td><i class="fa fa-user"></i> <?php echo $user['User']['firstname']; ?></td>
		<td><?php echo $user['User']['lastname']; ?></td>
		<td><?php echo $user['User']['username']; ?></td>
		<?php if(Authcomponent::user('type') != 'teacher'):?>
		<td><?php echo $user['User']['type']; ?></td>
		<?php endif ?>
		<td><i class="customBLUE"><i class="fa fa-pencil-square-o"></i></i><?php echo $this->Html->link(
			' Edit User',
			array('controller' => 'users', 'action' => 'edit', $user['User']['id']));
			?></td>
		<td><i class="customRED"><i class="fa fa-times-circle"></i></i><?php echo $this->Form->postLink(
			' Delete User',
			array('controller' => 'users', 'action' => 'delete', $user['User']['id']),
			array('confirm' => 'Are you sure?'));
			?>
		</td>
	</tr>
	<?php endforeach; ?>
	<?php unset($flight); ?>
</table>