<h1>Add new flight:</h1>

<?php
echo $this->Form->create('Flight', array('type' => 'file'));
echo $this->Form->input('studentID');
echo $this->Form->input('instructorID');
echo $this->Form->input('aircraft');
echo $this->Form->input('tailNo');
echo $this->Form->input('csvPath', array('type' => 'file'));
echo $this->Form->end('Create Flight');
?>