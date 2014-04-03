<?php
/**
 *
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<!DOCTYPE html>
<html>
	<head>
		<?php echo $this->Html->charset(); ?>
		<title>
			<?php echo $title_for_layout; ?>
		</title>
		<?php
			echo $this->Html->meta('icon');

			//echo $this->Html->css('bootstrap');
			?>	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
		<?php
			echo $this->fetch('meta');
			echo $this->fetch('css');
			echo $this->fetch('script');
		?>
	</head>
	<body>
	  <div class="navbar navbar-inverse" role="navigation">
		  <div class="container">
		    <div class="navbar-header">
		    	<?php echo $this->Html->link('Aerodock',
		        																	array('controller' => 'staticPages',
		        																	 			 'action' => 'index'),
		        																	array('class' => 'navbar-brand'));?>
		    </div>
		    <div class="navbar-right">
		      <?php if(AuthComponent::user('id')):?>
		      <ul class="nav navbar-nav">
		        <li><?php echo $this->Html->link('Flight List',
		        																	array('controller' => 'flights',
		        																	 'action' => 'index'));?></li>
		        <li><?php echo $this->Html->link(AuthComponent::user('firstname'),
		        																	array('controller' => 'users',
		        																		'action' => 'edit',
		        																		AuthComponent::user('id')));?></li>
		        <?php if(AuthComponent::user('type') == 'admin'):?>
		          <li><?php echo $this->Html->link('Users list',
		  																	array('controller' => 'users',
		  																		'action' => 'index'));?></li>
		  			<?php endif ?>
		  			<?php if(AuthComponent::user('type') == 'teacher'):?>
		          <li><?php echo $this->Html->link('Student list',
		  																	array('controller' => 'users',
		  																		'action' => 'index'));?></li>
		  			<?php endif ?>
		  			<?php if(AuthComponent::user('type') == 'maint' || 
		  								AuthComponent::user('type') == 'admin'):?>
		          <li><?php echo $this->Html->link('Maintenance',
		  																	array('controller' => 'flights',
		  																		'action' => 'maintenance'));?></li>
		  			<?php endif ?>

		        <li><?php echo $this->Html->link('Log out', 
		        																	array('controller' => 'users',
		        																	'action' => 'logout'),
		        																	array('class' => 'navbar-right'));?></li>
		      </ul>
		      <?php else: ?>
			      <?php echo $this->Form->create('User',
			      												array('action' => 'login',
			      															'class' => 'navbar-form navbar-right', 
			      															'role' => 'form')); ?>
				        	<?php echo $this->Form->input('username',
				        																array('placeholder' => 'Pipeline email',
				        																			'class' => 'form-control',
				        																			'div' => 'form-group',
				        																			'label' => false));?>
			       		<?php echo $this->Form->input('password',
			       																	array('placeholder' => 'Password',
				        																			'class' => 'form-control',
				        																			'div' => 'form-group',
				        																			'label' => false));?>
				    	<?php echo $this->Form->button('Log In',
				    																array('type' => 'submit',
				    																			 'class' => 'btn btn-success'));?>
						<?php echo $this->Form->end(); ?>
	      	<?php endif ?>
	      </div>
	  	</div>
		</div>
		<div class="container">
			<div id="content">
				<?php echo $this->Session->flash(); ?>

				<?php echo $this->fetch('content'); ?>
			</div>
			<div id="footer">

			</div>
		</div>
	</body>
</html>
