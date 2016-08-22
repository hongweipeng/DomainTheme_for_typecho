<?php
include 'header.php';
include 'menu.php';
?>


<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main manage-metas">
                <div class="col-mb-12">
                    <ul class="typecho-option-tabs clearfix">
                        <li class="current"><?php _e('点击名称进入编辑'); ?></li>
                    </ul>
                </div>
                <div class="col-mb-12 col-tb-8" role="main">                  
                    <?php
						$prefix = $db->getPrefix();
						$domaintheme = $db->fetchAll($db->select()->from($prefix.'domaintheme')->order($prefix.'domaintheme.id', Typecho_Db::SORT_ASC));
                    ?>
                    <form method="post" name="manage_categories" class="operate-form">
                    <div class="typecho-list-operate clearfix">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="<?php _e('你确认要删除这些记录吗?'); ?>" href="<?php $options->index('/action/DomainTheme-edit?'.DomainTheme_Plugin::$FORM_PRE.'do=delete'); ?>"><?php _e('删除'); ?></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <colgroup>
                                <col width="20"/>
								<col width="25%"/>
								<col width=""/>
								<col width="15%"/>
								<col width="10%"/>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th> </th>
									<th><?php _e('名称'); ?></th>
									<th><?php _e('域名'); ?></th>
									<th><?php _e('模板名称'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
								<?php if(!empty($domaintheme)): $alt = 0;?>
								<?php foreach ($domaintheme as $link): ?>
                                <tr id="lid-<?php echo $link['id']; ?>">
                                    <td><input type="checkbox" value="<?php echo $link['id']; ?>" name="id[]"/></td>
									<td><a href="<?php echo $request->makeUriByRequest(array('id'=>$link['id'], 'themename'=>null)); ?>" title="点击编辑"><?php echo $link['name']; ?></a>
									<td><a href="http://<?php echo $link['domain']; ?>" target="_blank"><?php echo $link['domain']; ?></a></td>
									<td><?php echo $link['theme']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5"><h6 class="typecho-list-table-title"><?php _e('没有任何链接'); ?></h6></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    </form>
				</div>
                <div class="col-mb-12 col-tb-4" role="form">
                    <?php DomainTheme_Plugin::form()->render(); ?>
                </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>

<script type="text/javascript">
(function () {
    $(document).ready(function () {
        var table = $('.typecho-list-table').tableDnD({
            onDrop : function () {
                var ids = [];

                $('input[type=checkbox]', table).each(function () {
                    ids.push($(this).val());
                });

                $.post('<?php $options->index('/action/DomainTheme-edit?do=sort'); ?>', 
                    $.param({lid : ids}));

                $('tr', table).each(function (i) {
                    if (i % 2) {
                        $(this).addClass('even');
                    } else {
                        $(this).removeClass('even');
                    }
                });
            }
        });

        table.tableSelectable({
            checkEl     :   'input[type=checkbox]',
            rowEl       :   'tr',
            selectAllEl :   '.typecho-table-select-all',
            actionEl    :   '.dropdown-menu a'
        });

        $('.btn-drop').dropdownMenu({
            btnEl       :   '.dropdown-toggle',
            menuEl      :   '.dropdown-menu'
        });

        $('.dropdown-menu button.merge').click(function () {
            var btn = $(this);
            btn.parents('form').attr('action', btn.attr('rel')).submit();
        });

        <?php if (isset($request->id)): ?>
        $('.typecho-mini-panel').effect('highlight', '#AACB36');
        <?php endif; ?>
    });
})();
function select_theme_change(obj) {
    var url = "<?php echo $request->makeUriByRequest(array('id'=>null,'name'=>null,'domain'=>null,'themename'=>null)); ?>";
    var form_pre = "<?php echo DomainTheme_Plugin::$FORM_PRE; ?>";
    var id = $("[name='"+ form_pre +"id']").eq(0).val();
    var name = $('#domaintheme_name-0-1').val();
    var domain = $('#domaintheme_domain-0-2').val();
    var themename = $('#domaintheme_theme-0-3').val();
    id && (url+='&id='+id);
    name && (url += '&name='+name);
    domain && (url += '&domain='+domain);
    themename && (url += '&themename='+themename);
    window.location.href = url;
}
</script>
<?php include 'footer.php'; ?>
