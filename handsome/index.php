<?php
/**
 * “我说话的时候不敢看天，看天的时候，不敢说话”

 * @package handsome
 * @author 友人C
 * @version 4.5.1
 * @link https://www.ihewro.com/archives/489/
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;
 $this->need('component/header.php');
 ?>

  <!-- aside -->
  <?php $this->need('component/aside.php'); ?>
  <!-- / aside -->

<!-- <div id="content" class="app-content"> -->
  <a class="off-screen-toggle hide"></a>
  <main class="app-content-body <?php Content::returnPageAnimateClass($this); ?>">
    <div class="hbox hbox-auto-xs hbox-auto-sm">
      <div class="col center-part">
          <?php if($this->options->blogNotice): ?>
              <!--公告位置-->
          <div class="alert alert-warning alert-block" style="
              margin-bottom: 0px;">
              <button type="button" class="close" data-dismiss="alert">×</button><p><i class="fontello fontello-volume-up" aria-hidden="true"></i>&nbsp;
                  <?php $this->options->blogNotice(); ?></p>
          </div>
              <!--/公告位置-->
          <?php endif; ?>
        <header class="bg-light lter b-b wrapper-md">
          <h1 class="m-n font-thin h3 text-black l-h"><?php $this->options->title(); ?></h1>
          <small class="text-muted letterspacing indexWords"><?php
              if (@!in_array('hitokoto',$this->options->featuresetup)) {
                  $this->options->Indexwords();
              }else{
                  echo '加载中……';
                  echo '<script>
                         $.ajax({
                            type: \'Get\',
                            url: \'https://v1.hitokoto.cn/\',
                            success: function(data) {
                               var hitokoto = data.hitokoto;
                              $(\'.indexWords\').text(hitokoto);
                            }
                         });
</script>';
              }
              ?></small>
          </header>
        <div class="wrapper-md" id="post-panel">
            <!--首页输出文章-->
            <?php
            //清空原有文章的列队
            $this->row = [];
            $this->stack = [];
            $this->length = 0;
            $order = '';
            $sticky_cids = [];
            //初始化数据库使用工具
            $restPostSelect = $this->select()->where('table.contents.type = ? and table.contents.status = ? and table.contents.created < ?',
                'post','publish',time())->group('table.contents.cid');
            $db = Typecho_Db::get();
            $sticky = $this->options->sticky; //置顶的文章cid，按照排序输入, 请以半角逗号或空格分隔
            //对置顶文章的处理
            if(trim($sticky) && $this->is('index') || $this->is('front')){
                $sticky_cids = explode(',', strtr($sticky, ' ', ','));//分割文本
                $sticky_html = '<span class="label text-base bg-danger pull-left m-t-xs m-r-xs" style="margin-top:  2px;">'._mt("置顶").'</span>';
                $pageSize = $this->options->pageSize;
                $stickySelect = $this->select()->where('table.contents.type = ?', 'post');

                foreach($sticky_cids as $i => $cid) {
                    if($i == 0) $stickySelect->where('table.contents.cid = ?', $cid);
                    else $stickySelect->orWhere('table.contents.cid = ?', $cid);
                    $order .= " when $cid then $i";
                    $restPostSelect->where('table.contents.cid != ?', $cid); //避免重复
                }
                if ($order) $stickySelect->order(null,"(case cid$order end)"); //置顶文章的顺序 按 $sticky 中 文章ID顺序
                if ($this->_currentPage == 1) foreach($db->fetchAll($stickySelect) as $sticky_post){ //首页第一页才显示
                    $sticky_post['sticky'] = $sticky_html;
                    $this->push($sticky_post); //压入列队
                }
            }

            $restPostSelect->join('table.relationships', 'table.relationships.cid = table.contents.cid',Typecho_Db::LEFT_JOIN)
                ->join('table.metas','table.metas.mid = table.relationships.mid',Typecho_Db::LEFT_JOIN)
                ->where('table.metas.slug != ? or table.metas.slug is NULL', 'image');


            $uid = $this->user->uid; //登录时，显示用户各自的私密文章
            if($uid) {
                $restPostSelect->orWhere('authorId = ? and table.metas.slug!=? or
                 table.metas.slug is NULL', $uid,'image')
                    ->where('table.contents.type = ? and table.contents.status = ? and table.contents.created < ?',
                        'post','private',time());
            }

            $endSelect = $restPostSelect->order('table.contents.created', Typecho_Db::SORT_DESC);
            $rest_posts = $db->fetchAll($restPostSelect->order('table.contents.created', Typecho_Db::SORT_DESC)->page($this->_currentPage, $this->parameter->pageSize));
            //计算相册分类的数目
            $count = IMAGE_POST_NUM;
            foreach($rest_posts as $rest_post) {
                $this->push($rest_post);
            } //压入列队
            Utils::hEcho($this->getTotal());
            $this->setTotal($this->getTotal()-count($sticky_cids)-$count); //置顶文章和相册文章不计算在所有文章内
            ?>
            <?php Content::echoPostList($this) ?>
          <!--分页首页按钮-->
          <nav class="text-center m-t-lg m-b-lg" role="navigation">
        <?php $this->pageNav('<i class="fontello fontello-chevron-left"></i>', '<i class="fontello fontello-chevron-right"></i>'); ?>
          </nav>
            <style>
                .page-navigator>li>a, .page-navigator>li>span{
                    line-height: 1.42857143;
                    padding: 6px 12px;
                }
            </style>
        </div>
      </div>
      <!--首页右侧栏-->
      <?php $this->need('component/sidebar.php') ?>
    </div>
  </main>
    <!-- footer -->
  <?php $this->need('component/footer.php'); ?>
    <!-- / footer -->
