<h1>All flights:</h1>
<?php if(Authcomponent::user('type') != 'student'){
	echo $this->Html->link(
		'Add Flight',
		array('controller' => 'flights', 'action' => 'add', $currentUserID));
	}?>
<?php echo $this->Form->create(array('action' => 'view_selection', 'method' => 'post'));?>
<?php //echo $this->Form->button('View Selected', array('action' => 'view_selection', 'method' => 'post')); ?>
<table class="table">
	<tr>
		<?php if(Authcomponent::user('type') != 'student'): ?>

		<th><i class="fa fa-male"></i><?php echo $this->Html->link(
		'Student',
		array('controller' => 'flights', 'action' => 'sort', "Student"));?></th>
		<?php endif ?>
		<th><i class="fa fa-user"></i><?php echo $this->Html->link(
		'Instructor',
		array('controller' => 'flights', 'action' => 'sort', "Instructor"));?></th>
		<th><?php echo $this->Html->link(
		'Tail No',
		array('controller' => 'flights', 'action' => 'sort', "Tail No"));?></th>
		<th>Date</th>
		<th><i class="fa fa-clock-o"></i> Flight Length</th>
		<th></th>
		<th></th>
	</tr>
	<?php
		//I put this here because I think postLink is bugged up when it is 1st called
	//Issue with not generating a post link causing undefined method error
	 echo $this->Form->postLink(
			null,
			null,
			null);
			?>
  <?php $counter=0;?>
	<?php foreach ($flights as $flight): ?>
	<tr>
   <?php /* <td>
      <?php echo $this->Form->checkbox('flight_checkbox'.$counter, array('value' => $flight['Flight']['id'])); ?>
    </td>*/?>

    <?php $counter++?>
    
		<?php if(Authcomponent::user('type') != 'student'): ?>
		<td><?php echo $flight['Flight']['studentid']; ?></td>
		<?php endif ?>
		<td><?php echo $flight['Flight']['instructorID']; ?></td>
		<td><?php echo $flight['Flight']['tailNo']; ?></td>
		<td><?php echo $flight['Flight']['date']; ?></td>
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
