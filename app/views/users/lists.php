<div id='container'>

    <h2>
		Hello 
		<?php if ($user->id > 0): ?>
			<?=$user->name;?>, 
		<?php endif; ?>
		Welcome to BeiJing
	</h2>

    <table id='mytable' border='1'>
		<tr>
			<th>用户ID</th>
			<th>用户名</th>
		</tr>
		<?php if (empty($result)) { ?>
			<tr>
				<td colspan="2">暂无用户消息列表 ...</td>
			</tr>
		<?php } else { ?>
			<?php foreach ($result AS $key => $val): ?>
				<tr>
					<td><?=$val['id']?></td>
					<td><?=$val['name']?></td>
				</tr>
			<?php endforeach; ?>
		<?php } ?>
	</table>
	
	<div id='pager'>
        first:   <?=$pager->first;?>
        before:  <?=$pager->before;?>
		total:   <?=$pager->total_pages;?>
        current：<?=$pager->current;?>
        next：   <?=$pager->next;?>
        last：   <?=$pager->last;?>
	</div>
	
</div>