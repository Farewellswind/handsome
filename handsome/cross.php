<?php
/**
* 时光机
*
* @package custom
*/
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('component/header.php');
?>


	<!-- aside -->
	<?php $this->need('component/aside.php'); ?>
	<!-- / aside -->

  <!-- content -->
<!-- <div id="content" class="app-content"> -->
    <a class="off-screen-toggle hide"></a>
  	<main class="app-content-body <?php echo Content::returnPageAnimateClass($this); ?>">
        <div class="hbox hbox-auto-xs hbox-auto-sm">
            <div class="col center-part">
                <div style="background:url(<?php $this->options->timepic(); ?>) center center; background-size:cover">
                    <div class="wrapper-lg bg-white-opacity">
                        <div class="row m-t">
                            <div class="col-sm-7">
                                <a class="thumb-lg pull-left m-r">
                                    <img src="<?php $this->options->BlogPic() ?>" class="img-circle">
                                </a>
                                <div class="clear m-b">
                                    <div class="m-b m-t-sm">
                                        <span class="h3 text-black"><?php $this->options->BlogName() ?></span>
                                        <small class="m-l"><?php $this->options->BlogJob() ?></small>
                                    </div>
                                    <p class="m-b">
                                        <?php
                                        $socialItemsOutput = '';
                                        $socialSingleItem = '';
                                        $json = '['.Typecho_Widget::widget('Widget_Options')->socialItems.']';
                                        $socialItems = json_decode($json);
                                        foreach ($socialItems as $socialItem){
                                            $itemName = $socialItem->name;
                                            @$itemStatus = $socialItem->status;
                                            @$itemLink = $socialItem->link;
                                            @$itemClass = $socialItem->class;
                                            if ($itemStatus == 'single'){
                                                $socialSingleItem .= '<a target="blank" href="'.$itemLink.'" class="btn btn-sm btn-success btn-rounded">'.$itemName.'</a>';
                                            }else{
                                                $socialItemsOutput .= '<a target="_blank" title="'.$itemName.'" href="'.$itemLink.'" class="btn btn-sm btn-bg btn-rounded btn-default btn-icon"><i class="'.$itemClass.'"></i></a>';
                                            }
                                        }
                                        ?>
                                        <?php echo $socialItemsOutput; ?>
                                    </p>
                                    <?php echo $socialSingleItem; ?>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <?php Typecho_Widget::widget('Widget_Stat')->to($stat); ?>
                                <div class="pull-right pull-none-xs text-center">
                                    <a class="m-b-md inline m">
                                        <span class="h3 block font-bold"><?php $stat->publishedCommentsNum() ?></span>
                                        <small>comments</small>
                                    </a>
                                    <a class="m-b-md inline m">
                                        <span class="h3 block font-bold"><?php $stat->publishedPostsNum() ?></span>
                                        <small>articles</small>
                                    </a>
                                    <a class="m-b-md inline m">
                                        <span class="h3 block font-bold"><?php $this->commentsNum(); ?></span>
                                        <small>weibo</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="wrapper bg-white b-b">
                    <ul class="nav nav-pills nav-sm">
                        <li class="active"><a><?php _me("我的动态") ?></a></li>
                    </ul>
                </div>
                <div class="padder">
                    <?php $this->need('component/say.php') ?>
                </div><!--end of #pedder-->
            </div>
            <div class="col w-lg bg-light lter b-l bg-auto">
                <div class="wrapper">
                    <div class="">
                        <h4 class="m-t-xs m-b-xs"><?php _me("联系方式") ?></h4>
                        <ul class="list-group no-bg no-borders pull-in">
                            <?php
                            $contactItemsOutput = '';
                            $json = '['.Typecho_Widget::widget('Widget_Options')->contactItems.']';
                            $contactItems = json_decode($json);
                            foreach ($contactItems as $contactItem){
                                $itemName = $contactItem->name;
                                $itemImg = $contactItem->img;
                                $itemValue = $contactItem->value;
                                $itemLink = $contactItem->link;

                                $contactItemsOutput .= '<li class="list-group-item"><a target="_blank" href="'.$itemLink.'" class="pull-left thumb-sm avatar m-r"><img 
src="'.$itemImg.'" class="img-circle"><i class="on b-white bottom"></i></a><div class="clear"><div><a target="_blank" href="'.$itemLink.'">'.$itemName.'</a></div><small class="text-muted">'.$itemValue.'</small></div></li>';
                            }
                            ?>
                            <?php echo $contactItemsOutput; ?>
                        </ul>
                    </div>
                    <div class="panel b-a">
                        <h4 class="font-thin padder"><?php _me("关于我") ?></h4>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <p><?php $this->options->about() ?></p>

                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
	</main>

    <!-- footer -->
	<?php $this->need('component/footer.php'); ?>
  	<!-- / footer -->
