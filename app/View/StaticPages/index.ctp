<?php
/**
 *
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Pages
 * @since         CakePHP(tm) v 0.10.0.1076
 */
?>


      <div class="jumbotron">
        <h1>Aerodock<br>
        <small>MTSU Aerospace Student Flight Tracking</small></h1>
        <p><?php echo $this->Html->link('Sign in using your Pipeline ID and password',
        																array(
        																		'controller' => 'users',
        																		'action' => 'login'),
        																		array('class' => 'btn btn-lg btn-success',
        																				  'role' => 'button'));?>
      </div>


      <div class="row marketing">
        <div class="col-md-6">
          <h4>Logging in</h4>
          <p>Aerodock requires the user to be enrolled in an MTSU Aerospace class to gain access. If you are a student, use your Pipeline ID and password to log in. If you are still unable to log in, contact your teacher to resolve the issue.</p>
        </div>
      </div>