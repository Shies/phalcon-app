<!-- author by shies 2015-06-08 19:43 -->
<script type="text/javascript" src="/public/js/jquery.min.js"></script>
<style type='text/css'>
.comment_list {width: 500px; height: 100px}
.comment_list h5 {margin: 0; padding: 0; border: 1px solid #000; padding-left: 5px; }
.comment_list .sample {border:1px solid #000; padding-left: 5px; }
.comment_list .sample ul {list-style: none; margin: 0; padding: 0; border-bottom: 1px solid #000; }
.comment_list .sample ul li {line-height: 22px; height: 22px; font-size: 14px; }
.comment_list .page_list a.current {font-weight: bold;}
</style>

<div id="container">

    <div id="article_list">

        <h4><a href="<?=$comment_search?>">进入评论</a></h4>

        <div class="badword">
            <?php if (!empty($badword)): ?>
                <?php foreach ($badword AS $key => $val): ?>
                    <a href="#"><?=$val['badword'];?></a>
                    <?php if ($key < count($badword) - 1): ?>
                        |
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="article">
            <h3>
                <?php if (!empty($article)): ?>
                    <?=$article->title;?>
                <?php endif; ?>
            </h3>
            <p>
                <span>
                    <?php if (!empty($article)): ?>
                         <?=$article->thumb;?>
                    <?php endif; ?>
                </span>
            </p>
        </div>
		
        <div class="comment">
            <form action="#" method="post">
                <textarea name="content" id="content"></textarea>
				<br />
                <input type="button" name="button" value="提交"  onClick="comment(null)" />
            </form>
        </div>
		
		<br />
		
		<div class='comment_list'>
		  
		  <h5>评论展示</h5>


            <!--
		  <div class='sample'>

			  < if (!empty($result)): ?>
			    < foreach ($result AS $key => $val): ?>
				  <ul>
					<li><span>作者：</span><=$val['username']?></li>
					<li><span>时间：</span><=date('Y-m-d', $val['creat_at'])?></li>
					<li><span>内容：</span><=$val['content']?></li>
				  </ul>
				< endforeach; ?>
			  < endif; ?>
		  </div>
		  
		  <div class='page_list'>
			< if (!empty($pager)): ?>
				<=$pager;?>
			< endif; ?>
		  </div>
		    -->
		
		</div>

    </div>

</div>

<script type="text/javascript">
asyncSearch(1);
function comment(content)
{
    var content = $.trim($('#content').val());
    $.ajax({
        type : "POST",
        url : "/index/asyncComment",
        dataType : "JSON",
        data : {
            content:content
        },
        cache : false,
        error : function() {},
        beforeSend : function() {},
        success : comment_reponse
    });
}


function comment_reponse(json)
{
    if (json.err > 1)
    {
        return !!alert(json.msg);
    }
    else
    {
        alert(json.msg);
        return true;
    }
}


function asyncSearch(page)
{
	var page = parseInt(page);
	$.ajax({
        type : "POST",
        url : "/index/asyncSearch",
        dataType : "JSON",
        data : {
            page: page,
        },
        cache : false,
        error : function() {},
        beforeSend : function() {},
        success : search_reponse
    });
}


function search_reponse(json)
{
	if (json.err > 1)
	{
		alert(json.msg);
		return false;
	}
	
	var cont = json.cont;
	if (typeof cont !== 'string')
	{
		return false;
	}
	$('.comment_list').html(cont);
}
</script>