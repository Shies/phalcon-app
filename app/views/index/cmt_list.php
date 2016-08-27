<!-- author by shies 2015-06-15 19:43 -->

  <div class='sample'>
	  <?php if (!empty($result)): ?>
		<?php foreach ($result AS $key => $val): ?>
		  <ul>
			<li><span>作者：</span><?=$val['username']?></li>
			<li><span>时间：</span><?=date('Y-m-d', $val['creat_at'])?></li>
			<li><span>内容：</span><?=$val['content']?></li>
		  </ul>
		<?php endforeach; ?>
	  <?php endif; ?>
  </div>
  
  <div class='page_list'>
	<?=$pager?>
  </div>

