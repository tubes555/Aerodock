<h1><?php echo $user['firstname']?> <?php echo $user['lastname'];?></h1>
<br>
<p>Update your information here</p>

<?php echo $this->Form->create('User', array(
																'inputDefaluts' => array(
																	'div' => 'form-group',
																	'class' => 'form-control')));
			echo $this->Form->input('firstname',array('value' => $user['firstname']));
			echo $this->Form->input('lastname' ,array('value' => $user['lastname']));
			if($user['type'] == 'admin'){
			echo $this->Form->input('type', array(
															'options' => array(
																'Student', 'Teacher', 'Maintenance', 'Administrator'),
															'selected' => $type));
			}
			echo $this->Form->end('Submit changes');?>
			