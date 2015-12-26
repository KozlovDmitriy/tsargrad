<? require_once($_SERVER['DOCUMENT_ROOT'] . '/../resources/views/layout/master_header.php'); ?>
<link rel="stylesheet" href="/css/blog-comment.css">
<div class="panel panel-default">
    <div class="panel-heading">
        <form method="post" action="/comments/add">
            <input name="comment_block_id" class="hidden" value="<?=$block->id?>"/>
            <input name="parent_comment_id" class="hidden parent-comment-id"/>
            <div class="row">
                <div class="col-lg-1 col-md-2">
                    <p>Комментарии</p>
                </div>

                <div class="col-lg-9 col-md-6">
                    <textarea name="text" class="form-control"></textarea>
                </div>

                <div class="col-lg-2 col-md-4" >
                    <div class="center-block" >
                        <input type="submit" class="btn btn-success btn-sm center-block" value="Добавить"/>
                    </div>
                    <div  class="comment-answer-block center-block hidden">
                        <p class="text-center">
                            <i>в ответ для</i>
                            &nbsp;
                            <a href="#" class="comment-to">janv</a>
                            &nbsp;
                            <a href="#" class="delete-comment-to"><i class="glyphicon glyphicon-remove"></i></a>
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="panel-body">
        <div class="blog-comment">
            <ul class="comments">
            <? foreach( $comments as $comment ) { ?>
                <? echo view('comments/comment', ['comment' => $comment]); ?>
            <? } ?>
            </ul>
        </div>
    </div>
    <? if ($page_count > 1):?>
    <div class="panel-footer clearfix" style="background-color: #fff">
        <nav class="pull-right">
            <ul class="pagination">
                <? if ($page > 1) : ?>
                    <li>
                        <a href="/comments/<?=$block->id.'/'.($page-1)?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <? endif; ?>

                <? for($i=1; $i <= $page_count; ++$i): ?>
                    <li class="<?=$i == $page ? 'active' : ''?>">
                        <a href="/comments/<?=$block->id.'/'.$i?>">
                            <?=$i?>
                        </a>
                    </li>
                <? endfor; ?>

                <? if ($page < $page_count) : ?>
                    <li>
                        <a href="/comments/<?=$block->id.'/'.($page+1)?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <? endif; ?>
            </ul>
        </nav>
    </div>
    <? endif; ?>
</div>
<? require_once($_SERVER['DOCUMENT_ROOT'] . '/../resources/views/layout/master_footer.php'); ?>
