<h1>Add new Users</h1>

<?php
echo $this->Form->create('User', array('type' => 'file'));
echo $this->Form->input('csvPath', array('type' => 'file'));
echo $this->Form->end('Create Users');
?>