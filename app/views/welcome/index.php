<div id='container'>

    <h2>
		Hello 
		<?php if ($user->id > 0): ?>
			<?=$user->name;?>, 
		<?php endif; ?>
		Welcome to BeiJing
	</h2>

    <fieldset>
        <div class="control-group"><a href="/users/search">用户列表</a></div>
        <div class="control-group"><a href="#">角色列表</a></div>
        <div class="control-group"><a href="#">权限列表</a></div>
    </fieldset>

</div>