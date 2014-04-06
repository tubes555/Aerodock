<h1>Add new flight:</h1>

<?php
echo $this->Form->create('Flight', array('type' => 'file'));
echo $this->Form->input('studentid');
echo $this->Form->input('csvPath', array('type' => 'file'));
echo $this->Form->end('Create Flight');
?>