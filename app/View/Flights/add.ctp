<h1>Add new flight:</h1>

<?php
echo $this->Form->create('Flight', array('type' => 'file'));
echo $this->Form->input('studentid');
echo $this->Form->input('csvPath', array('type' => 'file'));
echo $this->Form->end(array ('label' => 'Create Flight', 'id' => 'submitBTN', 'onClick' => 'spinMeRightRound();'));
?>

<?php echo $this->Html->script('spin.min'); ?>
<?php echo $this->Html->script('spin.commands'); ?>
<?php echo '<script type="text/javascript">'
   , '</script>'; ?>