<div class="devices index">
	<h2><?php echo __('Devices'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id'); ?></th>
			<th><?php echo $this->Paginator->sort('output_fifo_name'); ?></th>
			<th><?php echo $this->Paginator->sort('input_fifo_name'); ?></th>
			<th><?php echo $this->Paginator->sort('access_token'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($devices as $device): ?>
	<tr>
		<td><?php echo h($device['Device']['id']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($device['User']['name'], array('controller' => 'users', 'action' => 'view', $device['User']['id'])); ?>
		</td>
		<td><?php echo h($device['Device']['output_fifo_name']); ?>&nbsp;</td>
		<td><?php echo h($device['Device']['input_fifo_name']); ?>&nbsp;</td>
		<td><?php echo h($device['Device']['access_token']); ?>&nbsp;</td>
		<td><?php echo h($device['Device']['name']); ?>&nbsp;</td>
		<td><?php echo h($device['Device']['status']); ?>&nbsp;</td>
		<td><?php echo h($device['Device']['created']); ?>&nbsp;</td>
		<td><?php echo h($device['Device']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Send Notification'), array('action' => 'sendNotification', $device['Device']['id'])); ?>
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $device['Device']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $device['Device']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $device['Device']['id']), null, __('Are you sure you want to delete # %s?', $device['Device']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Device'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Devices Notifications'), array('controller' => 'devices_notifications', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Devices Notifications'), array('controller' => 'devices_notifications', 'action' => 'add')); ?> </li>
	</ul>
</div>
