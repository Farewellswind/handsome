<?php
/**
* 友情链接
*
* @package custom
*/
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('component/header.php');
?>
<style type="text/css">

</style>

	<!-- aside -->
	<?php $this->need('component/aside.php'); ?>
	<!-- / aside -->

<!-- <div id="content" class="app-content"> -->
  	<main class="app-content-body links_page <?php echo Content::returnPageAnimateClass($this); ?>">
        <div class="hbox hbox-auto-xs hbox-auto-sm">

            <div class="link-tab-container tab-container">
                <ul class="nav nav-tabs nav-justified">
                    <li class="active">
                        <a href="#my-info" role="tab" data-toggle="tab" aria-expanded="true"><?php _me("本博信息") ?></a>
                    </li>
                    <li>
                        <a href="#links-inside" role="tab" data-toggle="tab" aria-expanded="false"><?php _me("内页链接") ?></a>
                    </li>
                    <li>
                        <a href="#links-allweb" role="tab" data-toggle="tab"><?php _me("全站链接") ?></a>
                    </li>
                    <li>
                        <a href="#links-goodweb" role="tab" data-toggle="tab"><?php _me("推荐链接") ?></a>
                    </li>
                </ul>
                <div class="tab-content">
                    <!-- list -->
                    <div id="my-info" role="tabpane1" class="tab-pane fade in active">
                        <div class="wrapper ng-binding">
                            <?php $this->content(); ?>
                            <!--评论-->
                            <?php $this->need('component/comments.php') ?>
                        </div>
                    </div>
                    <!--内页链接-->
                    <ul id="links-inside" role="tabpane2" class="fade tab-pane list-group list-group-lg no-radius m-b-none m-t-n-xxs link-main">
                        <?php
                        $x=0;
                        $go = _mt("去找TA玩");
                        $mypattern = <<<eof
<div class="item">
   <div class="link-bg" style="background-image: url({image})">
    <div class="bg" style="background-image: url({image})"></div>
    <div class="link-avatar">
     <img src="{image}" class="avatar avatar-80" height="80" width="80" />
    </div>
   </div>
   <div class="meta">
    <button class="btn m-b-xs btn-dark btn-rounded"><a href="{url}" target="_blank">{$go}</a></button>
   </div>
   <div class="info">
    <h3 class="name">{name}</h3>
    <div class="description">
     {title}
    </div>
   </div>
  </div>
eof;
                        Links_Plugin::output($mypattern, 0, "one");
                        ?>
                    </ul>
                    <!--全站链接-->
                    <ul id="links-allweb" role="tabpane3" class="fade tab-pane list-group list-group-lg no-radius m-b-none m-t-n-xxs link-main">
                        <?php Links_Plugin::output($mypattern, 0, "ten");?>
                    </ul>
                    <!--建议网站-->
                    <ul id="links-goodweb" role="tabpane4" class="fade tab-pane list-group list-group-lg no-radius m-b-none m-t-n-xxs link-main">
                        <?php Links_Plugin::output($mypattern, 0, "good");?>
                    </ul>

                    <!-- / list -->
                </div>

            </div>
            <!--文章右侧边栏开始-->
            <?php //$this->need('component/sidebar.php'); ?>
            <!--文章右侧边栏结束-->
        </div>
	</main>
    <!-- footer -->
	<?php $this->need('component/footer.php'); ?>
  	<!-- / footer -->

