<?php
/**
 * 自定义图片分类的样式
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
                <h1 class="m-n font-thin h3 text-black l-h"><?php $this->archiveTitle(array(
                        'category'  =>  _mt('%s'),
                    ), '', ''); ?></h1>
                <small class="text-muted letterspacing indexWords"><?php echo $this->getDescription(); ?></small>
            </header>
            <div class="wrapper-md" id="post-panel">
                <!--首页输出相册-->
                <div class="layout">
                <div class="albums">
                    <?php
                    $index = 0;
                    while ($this->next()){
                        $cover =  Content::returnHeaderImgSrc($this,"index",$index);
                        $title = $this->title;
                        $url = $this->permalink;
                        $isLock = "";
                        $imgHtml = Utils::returnImageLazyLoadHtml($cover,300,300);
                        if ($this->hidden == 1){
                            $isLock = " image-lock";
                        }
                        echo <<<EOF
<figure class="album-thumb" itemprop="associatedMedia" itemscope="" itemtype="http://schema.org/ImageObject">

                        <a href="{$url}" class="img-wrap{$isLock}">

                            <img alt="{$title}" {$imgHtml} itemprop="thumbnail">
                        </a>
                        <figcaption itemprop="caption description">{$title}</figcaption>
                    </figure>
EOF;
                        $index++;
                    }
                    ?>
                </div>
                </div>
                <!--分页首页按钮-->
                <nav class="text-center m-t-lg m-b-lg image_nav" role="navigation" style="display: none">
                    <?php $this->pageNav('<i class="fontello fontello-chevron-left"></i>', '<i class="fontello fontello-chevron-right"></i>'); ?>
                </nav>
                <nav class="text-center m-t-lg m-b-lg">
                    <button class="btn m-b-xs btn-sm btn-dark btn-addon view-more-button"><?php _me("加载更多") ?></button>
                </nav>
                <nav class="text-center m-t-lg m-b-lg page-load-status">
                    <!--<button class="btn m-b-xs btn-sm btn-dark btn-addon infinite-scroll-request"><i class="animate-spin fontello fontello-refresh"></i><?php /*_me("正在加载……") */?></button>-->
                    <p class="infinite-scroll-request"><i class="animate-spin fontello fontello-refresh"></i>Loading...</p>

                    <p class="infinite-scroll-last"><?php _me("已经到底了") ?></p>
                    <p class="infinite-scroll-error"><?php _me("加载错误，请稍后重试") ?></p>
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


<script>
    if ($('.next a').length > 0){
        $.getScript("<?php echo STATIC_PATH ?>js/features/infinite-scroll.pkgd.min.js", function() {
            var $container =  $('.albums').infiniteScroll({
                // options
                path: '.next a',
                append: '.album-thumb',
                history: false,
                button: '.view-more-button',
                status: '.page-load-status',
                hideNav: '.image_nav'

            });
            var $viewMoreButton = $('.view-more-button');
            //$viewMoreButton.click();
            $viewMoreButton.on( 'click', function() {
                $container.infiniteScroll('loadNextPage');
                $container.infiniteScroll( 'option', {
                    loadOnScroll: true,
                });
                $viewMoreButton.hide();
            });

            $container.on( 'append.infiniteScroll', function( event, response, path, items ) {
                <?php if (in_array('isPageAnimate',$this->options->featuresetup)): ?>
                $('.app-content-body').animateCss('fadeInUpBig', function() {
                    $("img").lazyload({
                        effect: "fadeIn",
                        threshold: "200"
                    });

                    $(".lazy").lazyload({
                        effect: "fadeIn",
                        threshold: "200"
                    });
                });
                <?php else: ?>
                $("img").lazyload({
                    effect: "fadeIn",
                    threshold: "200"
                });
                $(".lazy").lazyload({
                    effect: "fadeIn",
                    threshold: "200"
                });
                <?php endif; ?>
            });
        });
    }else {
        $('.view-more-button').hide();
    }
</script>

<!-- footer -->
<?php $this->need('component/footer.php'); ?>
<!-- / footer -->
