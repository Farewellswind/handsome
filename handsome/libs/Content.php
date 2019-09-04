<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Content.php
 * Author     : hewro
 * Date       : 2017/05/21
 * Version    : 1.0.0
 * Description: 使用PHP方法输出内容
 */
class Content{

    /**
     * 输出文章摘要
     * @param $content
     * @param string $thumbStyle
     * @param $limit 字数限制
     * @return string
     */
    public static function excerpt($content,$limit){

        if ($limit == 0){
            return "";
        }else{
            $content = self::returnExceptShortCodeContent($content);
            return Typecho_Common::subStr(strip_tags($content), 0, $limit, "...");
        }


    }

    public static function returnExceptShortCodeContent($content){
        //排除摘要的collapse 公式
        if (strpos( $content, '[collapse')!== false){
            $pattern = self::get_shortcode_regex(array('collapse'));
            $content = preg_replace("/$pattern/",'',$content);
        }

        //排除摘要中的块级公式
        $content = preg_replace('/\$\$[\s\S]*\$\$/sm','',$content);
        //排除摘要的vplayer
        if (strpos( $content, '[vplayer')!== false){
            $pattern = self::get_shortcode_regex(array('vplayer'));
            $content = preg_replace("/$pattern/",'',$content);
        }
        //排除摘要中的短代码
        if (strpos( $content, '[hplayer')!== false){
            $pattern = self::get_shortcode_regex(array('hplayer'));
            $content = preg_replace("/$pattern/",'',$content);
        }
        if (strpos( $content, '[post')!== false){
            $pattern = self::get_shortcode_regex(array('post'));
            $content = preg_replace("/$pattern/",'',$content);
        }
        if (strpos( $content, '[scode')!== false){
            $pattern = self::get_shortcode_regex(array('scode'));
            $content = preg_replace("/$pattern/",'',$content);
        }
        if (strpos( $content, '[button')!== false){
            $pattern = self::get_shortcode_regex(array('button'));
            $content = preg_replace("/$pattern/",'',$content);
        }
        //排除回复可见的短代码
        if (strpos( $content, '[hide')!== false){
            $pattern = self::get_shortcode_regex(array('hide'));
            $content = preg_replace("/$pattern/",'',$content);
        }

        //排除文档助手
        if (strpos( $content, '>')!== false){
            $content = preg_replace("/(@|√|!|x|i)&gt;/",'',$content);
        }

        //排除login
        if (strpos( $content, '[login')!== false){
            $pattern = self::get_shortcode_regex(array('login'));
            $content = preg_replace("/$pattern/",'',$content);
        }

        return $content;
    }

    /**
     * 文章以及搜索页面的导航条
     * @param $archive
     * @param $WebUrl
     */
    public static function BreadcrumbNavigation($archive, $WebUrl){
        $WebUrl = $WebUrl . '/';
        $options = mget();
        if (@in_array("sreenshot",$options->featuresetup) && $archive->is("post")){
            $screenshotStyle='.breadcrumb i.fontello.fontello-weibo:after {
    padding: 0 5px 0 5px;
    color: #ccc;
    content: "/\00a0";
    }';
            $screenshot = '   <a id="generateShareImg" itemprop="breadcrumb" title="" data-toggle="tooltip" data-original-title="'._mt("生成分享图") .'"><i style ="font-size:13px;" class="fontello fontello-camera" aria-hidden="true"></i></a>';
        }else{
            $screenshot = "";
            $screenshotStyle="";
        }
        echo '<ol class="breadcrumb bg-white b-a" itemscope="">';
        echo '<li>
                 <a href="'.$WebUrl.'" itemprop="breadcrumb" title="'._mt("返回首页").'" data-toggle="tooltip"><i class="fontello fontello-home" aria-hidden="true"></i>&nbsp;'._mt("首页").'</a>
             </li>';
        if ($archive->is('archive')){
            echo '<li class="active">';
            $archive->archiveTitle(array(
                'category'  =>  _t('%s'),
                'search'    =>  _t('%s'),
                'tag'       =>  _t('%s'),
                'author'    =>  _t('%s')
            ), '', '');
            echo '</li></ol>';
        }else{
            if ($archive->is('page')) {
                echo '<li class="active">'.$archive->title.'&nbsp;&nbsp;</li>';
            }else{
                echo '<li class="active">'._mt("正文").'&nbsp;&nbsp;</li>';
            }
            echo  '
              <div style="float:right;">
   '._mt("分享到").'：
   <style>
   .breadcrumb i.iconfont.icon-qzone:after {
    padding: 0 0 0 5px;
    color: #ccc;
    content: "/\00a0";
    }
    '.$screenshotStyle.'
   </style>
   <a href="http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='.$archive->permalink.'&title='.$archive->title.'&site='.$WebUrl.'" itemprop="breadcrumb" target="_blank" title="" data-toggle="tooltip" data-original-title="'._mt("分享到QQ空间").'" onclick="window.open(this.href, \'qzone-share\', \'width=550,height=335\');return false;"><i style ="font-size:15px;" class="iconfont icon-qzone" aria-hidden="true"></i></a>
   <a href="http://service.weibo.com/share/share.php?url='.$archive->permalink.'&title='.$archive->title.'" target="_blank" itemprop="breadcrumb" title="" data-toggle="tooltip" data-original-title="'._mt("分享到微博").'" onclick="window.open(this.href, \'weibo-share\', \'width=550,height=335\');return false;"><i style ="font-size:15px;" class="fontello fontello-weibo" aria-hidden="true"></i></a>'.$screenshot.'</div>';
            echo '</ol>';
        }
    }


    /**
     * 文章页面标题
     * @param $archive
     * @param $isLogin
     * @return string
     */
    public static function exportPostPageHeader($archive, $isLogin){
        $html = "";
        $html .=  '
        <header id="small_widgets" class="bg-light lter b-b wrapper-md">
             <h1 class="entry-title m-n font-thin h3 text-black l-h">'.$archive->title;
            $html .= '<a class="plus-font-size" data-toggle="tooltip" data-original-title="点击改变文章字体大小"><i class="glyphicon glyphicon-text-size
" aria-hidden="true"></i></a>';
             if ($isLogin) {
                 if ($archive->is("page")) {
                     $html .= '
                     <a class="superscript" href="'.Helper::options()->adminUrl.'write-page.php?cid='.$archive->cid.'" target="_blank"><i class="fontello fontello-edit" aria-hidden="true"></i></a>
                     ';
                 }else {
                     $html .= '
                     <a class="superscript" href="'.Helper::options()->adminUrl.'write-post.php?cid='.$archive->cid.'" target="_blank"><i class="fontello fontello-edit" aria-hidden="true"></i></a>
                     ';
                 }
             }
             if ($archive ->is("page")) {
                 $html .= '</h1></header>';
             }else {
                 $html .= '</h1>';//此时不用封闭header标签
             }
             return $html;
    }


    /**
     * 输出DNS预加载信息
     * @return string
     */
    public static function exportDNSPrefetch() {
        $defaultDomain = array();
        $customDomain = mget()->dnsPrefetch;
        if (!empty($customDomain)) {
            $customDomain = mb_split("\n", $customDomain);
            $defaultDomain = array_merge($defaultDomain, $customDomain);
            $defaultDomain = array_unique($defaultDomain);
        }
        $html = "<meta http-equiv=\"x-dns-prefetch-control\" content=\"on\">\n";
        foreach ($defaultDomain as $domain) {
            $domain = trim($domain, " \t\n\r\0\x0B/");
            if (!empty($domain)) {
                $html .= "<link rel=\"dns-prefetch\" href=\"//{$domain}\" />\n";
            }
        }
        return $html;
    }


    /**
     * 选择左侧边栏配色
     * @return string
     */
    public static function selectAsideStyle(){
        $html = "";
        $options = mget();
        switch($options->themetype){
            case 0: $html .= '<aside id="aside" class="app-aside hidden-xs bg-black">';break;
            case 1: $html .= '<aside id="aside" class="app-aside hidden-xs bg-dark">';break;
            case 2: $html .= '<aside id="aside" class="app-aside hidden-xs bg-black">';break;
            case 3: $html .= '<aside id="aside" class="app-aside hidden-xs bg-dark">';break;
            case 4: $html .= '<aside id="aside" class="app-aside hidden-xs bg-black">';break;
            case 5: $html .= '<aside id="aside" class="app-aside hidden-xs bg-dark">';break;
            case 6: $html .= '<aside id="aside" class="app-aside hidden-xs bg-dark">';break;
            case 7: $html .= '<aside id="aside" class="app-aside hidden-xs bg-white b-r">';break;
            case 8: $html .= '<aside id="aside" class="app-aside hidden-xs bg-light">';break;
            case 9: $html .= '<aside id="aside" class="app-aside hidden-xs bg-light dker b-r">';break;
            case 10: $html .= '<aside id="aside" class="app-aside hidden-xs bg-dark">';break;
            case 11: $html .= '<aside id="aside" class="app-aside hidden-xs bg-black">';break;
            case 12: $html .= '<aside id="aside" class="app-aside hidden-xs bg-dark">';break;
            case 13: $html .= '<aside id="aside" class="app-aside hidden-xs bg-dark">';break;

        }
        return $html;
    }


    /**
     * 选择背景样式css： 纯色背景 + 图片背景 + 渐变背景
     * @return string
     */
    public static function exportBackground(){
        $html = "";
        $options = mget();
        if ($options->BGtype == 0) {
            $html .= 'background: '.$options->bgcolor.'';
        }elseif ($options->BGtype == 1) {
            $html .= 'background: url('.$options->bgcolor.') #6A6B6F fixed;background-size: cover';
        }elseif ($options->BGtype == 2) {
            switch ($options->GradientType) {
                case 0: $html .= <<<EOF
            background-image:
                           -moz-radial-gradient(0% 100%, ellipse cover, #96DEDA 10%,rgba(255,255,227,0) 40%),
                           -moz-linear-gradient(-45deg,  #1fddff 0%,#FFEDBC 100%)
                           ;
            background-image:
                           -o-radial-gradient(0% 100%, ellipse cover, #96DEDA 10%,rgba(255,255,227,0) 40%),
                           -o-linear-gradient(-45deg,  #1fddff 0%,#FFEDBC 100%)
                           ;
            background-image:
                           -ms-radial-gradient(0% 100%, ellipse cover, #96DEDA 10%,rgba(255,255,227,0) 40%),
                           -ms-linear-gradient(-45deg,  #1fddff 0%,#FFEDBC 100%)
                           ;
            background-image:
                           -webkit-radial-gradient(0% 100%, ellipse cover, #96DEDA    10%,rgba(255,255,227,0) 40%),
                           -webkit-linear-gradient(-45deg,  #1fddff 0%,#FFEDBC 100%)
                           ;
EOF;
break;

                case 1: $html .= <<<EOF
           background-image:
               -moz-radial-gradient(-20% 140%, ellipse ,  rgba(255,144,187,.6) 30%,rgba(255,255,227,0) 50%),
               -moz-linear-gradient(top,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%),
               -moz-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -moz-linear-gradient(-45deg,  rgba(18,101,101,.8) -10%,#d9e3e5 80% )
               ;
           background-image:
               -o-radial-gradient(-20% 140%, ellipse ,  rgba(255,144,187,.6) 30%,rgba(255,255,227,0) 50%),
               -o-linear-gradient(top,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%),
               -o-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -o-linear-gradient(-45deg,  rgba(18,101,101,.8) -10%,#d9e3e5 80% )
               ;
           background-image:
               -ms-radial-gradient(-20% 140%, ellipse ,  rgba(255,144,187,.6) 30%,rgba(255,255,227,0) 50%),
               -ms-linear-gradient(top,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%),
               -ms-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -ms-linear-gradient(-45deg,  rgba(18,101,101,.8) -10%,#d9e3e5 80% )
               ;
           background-image:
               -webkit-radial-gradient(-20% 140%, ellipse ,  rgba(255,144,187,.6) 30%,rgba(255,255,227,0) 50%),
               -webkit-linear-gradient(top,  rgba(57,173,219,.25) 0%,rgba(42,60,87,.4) 100%),
               -webkit-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -webkit-linear-gradient(-45deg,  rgba(18,101,101,.8) -10%,#d9e3e5 80% )
               ;
EOF;
break;
                case 2: $html .= <<<EOF
           background-image:
               -moz-radial-gradient(-20% 140%, ellipse ,  rgba(235,167,171,.6) 30%,rgba(255,255,227,0) 50%),
               -moz-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -moz-linear-gradient(-45deg,  rgba(62,70,92,.8) -10%,rgba(220,230,200,.8) 80% )
               ;
           background-image:
               -o-radial-gradient(-20% 140%, ellipse ,  rgba(235,167,171,.6) 30%,rgba(255,255,227,0) 50%),
               -o-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -o-linear-gradient(-45deg,  rgba(62,70,92,.8) -10%,rgba(220,230,200,.8) 80% )
               ;
           background-image:
               -ms-radial-gradient(-20% 140%, ellipse ,  rgba(235,167,171,.6) 30%,rgba(255,255,227,0) 50%),
               -ms-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -ms-linear-gradient(-45deg,  rgba(62,70,92,.8) -10%,rgba(220,230,200,.8) 80% )
               ;
           background-image:
               -webkit-radial-gradient(-20% 140%, ellipse ,  rgba(235,167,171,.6) 30%,rgba(255,255,227,0) 50%),
               -webkit-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -webkit-linear-gradient(-45deg,  rgba(62,70,92,.8) -10%,rgba(220,230,200,.8) 80% )
               ;
EOF;
break;
                case 3: $html .= <<<EOF
           background-image:
               -moz-radial-gradient(-20% 140%, ellipse ,  rgba(143,192,193,.6) 30%,rgba(255,255,227,0) 50%),
               -moz-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -moz-linear-gradient(-45deg,  rgba(143,181,158,.8) -10%,rgba(213,232,211,.8) 80% )
           ;
           background-image:
               -o-radial-gradient(-20% 140%, ellipse ,  rgba(143,192,193,.6) 30%,rgba(255,255,227,0) 50%),
               -o-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -o-linear-gradient(-45deg,  rgba(143,181,158,.8) -10%,rgba(213,232,211,.8) 80% )
           ;
           background-image:
               -ms-radial-gradient(-20% 140%, ellipse ,  rgba(143,192,193,.6) 30%,rgba(255,255,227,0) 50%),
               -ms-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -ms-linear-gradient(-45deg,  rgba(143,181,158,.8) -10%,rgba(213,232,211,.8) 80% )
           ;
           background-image:
               -webkit-radial-gradient(-20% 140%, ellipse ,  rgba(143,192,193,.6) 30%,rgba(255,255,227,0) 50%),
               -webkit-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -webkit-linear-gradient(-45deg,  rgba(143,181,158,.8) -10%,rgba(213,232,211,.8) 80% )
               ;
EOF;
break;
                case 4: $html .= <<<EOF
           background-image:
               -moz-radial-gradient(-20% 140%, ellipse ,  rgba(214,195,224,.6) 30%,rgba(255,255,227,0) 50%),
               -moz-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -moz-linear-gradient(-45deg, rgba(97,102,158,.8) -10%,rgba(237,187,204,.8) 80% )
               ;
           background-image:
               -o-radial-gradient(-20% 140%, ellipse ,  rgba(214,195,224,.6) 30%,rgba(255,255,227,0) 50%),
               -o-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -o-linear-gradient(-45deg, rgba(97,102,158,.8) -10%,rgba(237,187,204,.8) 80% )
               ;
           background-image:
               -ms-radial-gradient(-20% 140%, ellipse ,  rgba(214,195,224,.6) 30%,rgba(255,255,227,0) 50%),
               -ms-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -ms-linear-gradient(-45deg, rgba(97,102,158,.8) -10%,rgba(237,187,204,.8) 80% )
               ;
           background-image:
               -webkit-radial-gradient(-20% 140%, ellipse ,  rgba(214,195,224,.6) 30%,rgba(255,255,227,0) 50%),
               -webkit-radial-gradient(60% 40%,ellipse,   #d9e3e5 10%,rgba(44,70,76,.0) 60%),
               -webkit-linear-gradient(-45deg, rgba(97,102,158,.8) -10%,rgba(237,187,204,.8) 80% )
               ;
EOF;
break;
                case 5: $html .= <<<EOF
           background-image: #DAD299; /* fallback for old browsers */
           background-image: -webkit-linear-gradient(to left, #DAD299 , #B0DAB9); /* Chrome 10-25, Safari 5.1-6 */
           background-image: linear-gradient(to left, #DAD299 , #B0DAB9); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
EOF;
break;
                case 6: $html .= <<<EOF
           background-image: linear-gradient(-20deg, #d0b782 20%, #a0cecf 80%);
EOF;
break;
                case 7: $html .= <<<EOF
           background: #F1F2B5; /* fallback for old browsers */
           background: -webkit-linear-gradient(to left, #F1F2B5 , #135058); /* Chrome 10-25, Safari 5.1-6 */
           background: linear-gradient(to left, #F1F2B5 , #135058); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ *
EOF;
break;
                case 8: $html .= <<<EOF
           background: #02AAB0; /* fallback for old browsers */
           background: -webkit-linear-gradient(to left, #02AAB0 , #00CDAC); /* Chrome 10-25, Safari 5.1-6 */
           background: linear-gradient(to left, #02AAB0 , #00CDAC); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
EOF;
break;
                case 9: $html .= <<<EOF
           background: #C9FFBF; /* fallback for old browsers */
           background: -webkit-linear-gradient(to left, #C9FFBF , #FFAFBD); /* Chrome 10-25, Safari 5.1-6 */
           background: linear-gradient(to left, #C9FFBF , #FFAFBD); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
EOF;
break;
            }
        }
        return $html;
    }


    /**
     * 选择全站布局设置
     * @param $options
     * @return string
     */
    public static function selectLayout($options){
        $layout = '<div id="alllayout" class="app ';
        if (@in_array('aside-fix', $options)){
            $layout .= 'app-aside-fixed ';
        }
        if (@in_array('aside-folded', $options)){
            $layout .= 'app-aside-folded ';
        }
        if (@in_array('aside-dock', $options)){
            $layout .= 'app-aside-dock ';
        }
        if (@in_array('container-box', $options)){
            $layout .= 'container ';
        }
        if (@in_array('header-fix', $options)){
            $layout .= 'app-header-fixed ';
        }
        $layout .= '">';
        return $layout;
    }


    /**
     * 根据是否为盒子布局，输出相应的HTML标签
     * @param $options
     * @return string
     */
    public static function exportHtmlTag($options){
        $html = '<html class="no-js';
        if (@in_array('container-box', $options)){
            $html .= ' bg';
        }
        if (@in_array("opacityMode",$options)){
            $html .= ' cool-transparent';
        }
        $html .= '"';
        return $html;
    }


    /**
     * 输出自定义css + 如果为盒子布局输出背景css
     * @param $archive
     * @return string
     */
    public static function exportCss($archive){
        $css = "";
        $css .= '
        html.bg {
        '.Content::exportBackground().'
        }
        .cool-transparent .off-screen+* .app-content-body {
        '.Content::exportBackground().'
        }';
        $options = mget();

        $css .= $options->customCss;
        return $css;
    }



    /**
     * 输出打赏信息
     * @return string
     */
    public static function exportPayForAuthors(){
        $options = mget();
        return '
             <div class="support-author">
                 <button data-toggle="modal" data-target="#myModal" class="btn btn-pay btn-danger btn-rounded"><i class="fontello fontello-wallet" aria-hidden="true"></i>&nbsp;'._mt("赞赏").'</button>
                 <div class="mt20 text-center article__reward-info">
                     <span class="mr10">'._mt("如果觉得我的文章对你有用，请随意赞赏").'</span>
                 </div>
             </div>
             <div id="myModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
                 <div class="modal-dialog modal-sm" role="document">
                     <div class="modal-content">
                         <div class="modal-header">
                             <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                             <h4 class="modal-title">'._mt("赞赏作者").'</h4>
                         </div>
                         <div class="modal-body">
                             <p class="text-center article__reward"> <strong class="article__reward-text">'._mt("扫一扫支付").'</strong> </p>
                             <div class="tab-content">
                                 <img noGallery aria-labelledby="alipay-tab" class="pay-img tab-pane fade in active" id="alipay_author" role="tabpanel" src="'.$options->AlipayPic.'" />
                                 <img noGallery aria-labelledby="wechatpay-tab" class="pay-img tab-pane fade" id="wechatpay_author" role="tabpanel" src="'.$options->WechatPic.'" />
                             </div>
                             <div class="article__reward-border mb20 mt10"></div>

                             <div class="text-center" role="tablist">
                                 <div class="pay-button" role="presentation" class="active"><button  href="#alipay_author" id="alipay-tab" aria-controls="alipay_author" role="tab" data-toggle="tab" class="btn m-b-xs btn-info"><i class="iconfont icon-alipay" aria-hidden="true"></i><span>&nbsp;'._mt("支付宝支付").'</span></button>
                                 </div>
                                 <div class="pay-button" role="presentation"><button href="#wechatpay_author" id="wechatpay-tab" aria-controls="wechatpay_author" role="tab" data-toggle="tab" class="btn m-b-xs btn-success"><i class="iconfont icon-wechatpay" aria-hidden="true"></i><span>&nbsp;'._mt("微信支付").'</span></button>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
        ';
    }


    /**
     * 输出文章底部信息
     * @param $time
     * @param $obj
     * @return string
     */
    public static function exportPostFooter($time,$obj){
        return '
             <div class="show-foot">
                 <div class="notebook">
                     <i class="fontello fontello-clock-o"></i>
                     <span>'._mt("最后修改").'：'.date(_mt("Y 年 m 月 d 日 h : i  A") , $time + $obj).'</span>
                 </div>
                 <div class="copyright" data-toggle="tooltip" data-html="true" data-original-title="'._mt("转载请联系作者获得授权，并注明转载地址").'"><span>© '._mt("著作权归作者所有").'</span>
                 </div>
             </div>
        ';

    }

    /**
     * 处理具体的头图显示逻辑：当有头图时候，显示随机图片还是第一个附件还是一张图片还是thumb字段
     * @param int $index
     * @param $howToThumb 显示缩略图的方式，0，1，2，3
     * @param $attach 文章的第一个附件
     * @param $content 文章内容
     * @param $thumbField thumb字段
     * @return string
     */
    public static function whenSwitchHeaderImgSrc($index =0,$howToThumb,$attach,$content,$thumbField){
        $options = mget();
        $randomNum = unserialize(INDEX_IMAGE_ARRAY);

        // 随机缩略图路径
        $random = THEME_URL . 'usr/img/sj/' . @$randomNum[$index] . '.jpg';//如果有文章置顶，这里可能会导致index not undefined
        $pattern = '/\<img.*?src\=\"(.*?)\"[^>]*>/i';
        $patternMD = '/\!\[.*?\]\((http(s)?:\/\/.*?(jpg|png))/i';
        $patternMDfoot = '/\[.*?\]:\s*(http(s)?:\/\/.*?(jpg|png))/i';

        if ($howToThumb == '0'){
            return $random;
        }elseif ($howToThumb == '1' || $howToThumb == '2'){
            if (!empty($thumbField)){
                return $thumbField;
            }elseif ($attach!=null && isset($attach->isImage) && $attach->isImage == 1){
                return $attach->url;
            }else{
                if (preg_match_all($pattern, $content, $thumbUrl)){
                    $thumb = $thumbUrl[1][0];
                }elseif (preg_match_all($patternMD, $content, $thumbUrl)){
                    $thumb = $thumbUrl[1][0];
                }elseif (preg_match_all($patternMDfoot, $content, $thumbUrl)){
                    $thumb = $thumbUrl[1][0];
                }else{//文章中没有图片
                    if ($howToThumb == '1'){
                        return '';
                    }else{
                        return $random;
                    }
                }
                return $thumb;
            }
        }elseif ($howToThumb == '3'){
            if (!empty($thumbField)){
                return $thumbField;
            }else{
                return $random;
            }
        }
    }


    /**
     * 处理显示是否有图片地址的逻辑：根据thumb字段和后台外观设置的是否开启头图开关
     * @param $widget
     * @param $select
     * @param int $index
     * @return string
     */
    public static function returnHeaderImgSrc($widget,$select,$index=0){
        $options = mget();
        $thumbField = $widget->fields->thumb;
        if (strtoupper($thumbField) == "NO"){//thumb 为no 直接不显示头图
            $imgSrc = "";
        }else{//thumb不为no
            if (in_array('NoRandomPic-post',$options->indexsetup) && in_array('NoRandomPic-index',
                    $options->indexsetup)){//全部关闭
                if ($thumbField != ""){
                    $imgSrc = Content::whenSwitchHeaderImgSrc($index,$options->RandomPicChoice,$widget->attachments(1)->attachment, $widget->content, $widget->fields->thumb);
                }else{
                    $imgSrc = "";
                }
            }else if (!in_array('NoRandomPic-post',$options->indexsetup) && !in_array('NoRandomPic-index',
                    $options->indexsetup)){//全部开启
                $imgSrc = Content::whenSwitchHeaderImgSrc($index,$options->RandomPicChoice,$widget->attachments(1)->attachment, $widget->content, $widget->fields->thumb);
            }else{//一开一闭
                if (in_array('NoRandomPic-post',$options->indexsetup)){//不显示文章头图，显示首页头图
                    if ($select == "post"){
                        $imgSrc = "";
                    }else{
                        $imgSrc = Content::whenSwitchHeaderImgSrc($index,$options->RandomPicChoice,$widget->attachments(1)->attachment, $widget->content, $widget->fields->thumb);
                    }
                }else{//不显示首页头图，显示文章页面头图
                    if ($select == "post"){
                        $imgSrc = Content::whenSwitchHeaderImgSrc($index,$options->RandomPicChoice,$widget->attachments(1)->attachment, $widget->content, $widget->fields->thumb);
                    }else{
                        $imgSrc = "";
                    }
                }
            }
        }
        return $imgSrc;
    }

    public static function returnSharePostDiv($obj){
        $headImg = Content::returnHeaderImgSrc($obj,"post",0);
        if (trim($headImg) == ""){//头图为空
            $headImg = STATIC_PATH.'img/video.jpg';
        }
        $author = $obj->author->screenName;
        $title = $obj->title;
        $url = $obj->permalink;
        if ($obj->fields->album!=""){
            $content = $obj->fields->album;
        }else{
            $content = $obj->excerpt;
        }
        $expert = Content::excerpt($content,60);
        if (trim($expert) == ""){
            $expert = _mt("暂无文字描述");
        }
        $year = date('Y/m',$obj->date->timeStamp);
        $day = date('d',$obj->date->timeStamp);
        $notice = _mt("扫描右侧二维码阅读全文");
        $image = THEME_URL.'libs/GetCode.php?type=url&content='.$url;
        return <<<EOF
        <style>
        
        .mdx-si-head .cover{
            object-fit: cover;
            width: 100%;
            height: 100%
        }
        
</style>
<div class="mdx-share-img" id="mdx-share-img"><div class="mdx-si-head" style="background-image:url({$headImg})"><p>{$author}</p><span>{$title}</span></div><div 
class="mdx-si-sum">{$expert}</div><div class="mdx-si-box"><span>{$notice}</span><div class="mdx-si-qr" id="mdx-si-qr"><img 
src="{$image}"></div></div><div class="mdx-si-time">{$day}<br><span 
class="mdx-si-time-2">{$year}</span></div></div>
EOF;
    }


    /**
     * 判断是否是相册分类
     * @param $data
     * @return bool
     */
    public static function isImageCategory($data){
        //print_r($data);
        if (is_array($data)){
            for ($i=0; $i< count($data); $i++) {
                if ($data[$i]["slug"] == "image"){
                    return true;
                }
            }
        }
        return false;

    }
    /**
     * 单个页面输出头图函数
     * @param $obj
     * @param int $index
     * @return string
     */
    public static function exportHeaderImg($obj,$index=0){

        $parameterArray = array();
        $options = mget();
        $parameterArray['imgSrc'] = Content::returnHeaderImgSrc($obj,"post",$index);
        $parameterArray['isIndex'] = false;
        //是否固定图片大小（默认8：3）
        //echo $options->featuresetup.'test';
        if (in_array('FixedImageSize', $options->featuresetup)){
            $parameterArray['isFixedImg'] = true;
        }else{
            $parameterArray['isFixedImg'] = false;
        }
        return Content::returnPostItem($parameterArray);
    }

    /**
     * 输出文章列表:首页和archive页面
     * @param $obj
     */
    public static function echoPostList($obj){

        $options = mget();

        $index = 0;

        echo '<div class="blog-post">';

        while ($obj->next()){
            /*if (self::isImageCategory($obj->categories)){
                //echo "good";
            }else{*/
                $parameterArray = array();
                $parameterArray['title'] = $obj->sticky.$obj->title;

                //是否是大版式头图
                $styleThumb = strtoupper($obj->fields->thumbStyle);
                if ($styleThumb == 'SMALL'){//文章页面选择小版式
                    $parameterArray['isBig'] = false;
                }elseif ($styleThumb == 'LARGE'){
                    $parameterArray['isBig'] = true;
                }else{//跟随外观设置
                    if ($options->thumbStyle == "0"){//小头图
                        $parameterArray['isBig'] = false;
                    }else if ($options->thumbStyle == "2"){//交错显示
                        if ($index %2 == 0){
                            $parameterArray['isBig'] = true;
                        }else{
                            $parameterArray['isBig'] = false;
                        }
                    }else if ($options->thumbStyle == "1"){//大头图
                        $parameterArray['isBig'] = true;
                    }else{//默认是大图
                        $parameterArray['isBig'] = true;
                    }
                }

                if (in_array('NoSummary-index', $options->indexsetup)){
                    $expertNum = 0;
                }else{
                    if (!$parameterArray['isBig']){//小头图
                        if ($options->numberOfSmallPic == ""){//自定义摘要字数
                            $expertNum = 80;
                        }else{
                            $expertNum = $options->numberOfSmallPic;
                        }
                    }else{
                        if ($options->numberOfBigPic == ""){//自定义摘要字数
                            $expertNum = 200;
                        }else{
                            $expertNum = $options->numberOfBigPic;
                        }
                    }
                }
                $parameterArray['summary'] = Content::excerpt($obj->excerpt,$expertNum);
                $parameterArray['imgSrc'] = Content::returnHeaderImgSrc($obj,"index",$index);
                $parameterArray['linkUrl'] = $obj->permalink;
                /*if (index == 0){
                    print_r($obj->author);
                }*/
                $parameterArray['author'] = $obj->author->screenName;
                $parameterArray['authorUrl'] = $obj->author->permalink;
                $parameterArray['date'] = $obj->date->timeStamp;
                $parameterArray['commentNum'] =  $obj->commentsNum;
                $parameterArray['viewNum'] = get_post_view($obj);
                //是否是首页
                $parameterArray['isIndex'] = true;

                //是否固定图片大小（默认8：3）
                if (in_array('FixedImageSize', $options->featuresetup)){
                    $parameterArray['isFixedImg'] = true;
                }else{
                    $parameterArray['isFixedImg'] = false;
                }
                echo Content::returnPostItem($parameterArray);
                $index ++;
            //}
        }
        echo '</div>';
    }


    /**
     * @param $parameterArray
     * @return string : 返回单篇文章头部的HTML代码
     * @internal param $title : 标题
     * @internal param $summary : 摘要
     * @internal param $imgSrc : 头图地址
     * @internal param $linkUrl : 文章地址
     * @internal param $author : 作者
     * @internal param $authorUrl : 作者链接
     * @internal param $date : 日期
     * @internal param int $commentNum : 评论数
     * @internal param int $viewNum : 浏览数
     * @internal param bool $isBig : 是否是大版式头图
     * @internal param bool $isIndex : 是否是首页
     * @internal param bool $isFixedImg : 是否固定图片大小（默认8：3）
     */
    public static function returnPostItem($parameterArray){
        $options = mget();

        if ($parameterArray['isIndex']){
            //格式化时间
            if ($parameterArray['date']!=0){
                $dateString = date(I18n::dateFormat(),$parameterArray['date']);
            }
            //格式化评论数
            if ($parameterArray['commentNum'] == 0){
                $commentNumString = _mt("暂无评论");
            }else{
                $commentNumString = $parameterArray['commentNum']." "._mt("条评论");
            }
            //格式化浏览次数
            $viewNumString = $parameterArray['viewNum']." "._mt("次浏览");
        }

        $html = "";
        //首页文章
        if ($parameterArray['isIndex']){//首页界面的文章item结构
            //头图部分
            /*if (in_array('multiStyleThumb',$options->indexsetup)){
                $html .='<div class="col-sm-6 multi-post">';
            }*/
            if ($parameterArray['imgSrc'] == ""){//图片地址为空即不显示头图
                $html .= '<div class="panel">';
            }else{
                if ($parameterArray['isBig']){//大版式头图
                    $backgroundImageHtml = Utils::returnDivLazyLoadHtml($parameterArray['imgSrc'],1200,0);
                    if ($parameterArray['isFixedImg']){//裁剪头图
                        $html .= <<<EOF
<div class="panel">
    <div class="index-post-img">
        <a href="{$parameterArray['linkUrl']}">
            <div class="item-thumb lazy" {$backgroundImageHtml}>
</div>
           
        </a>
    </div>
EOF;
                    }else{//头图没有裁剪
                        $imageHtml = Utils::returnImageLazyLoadHtml($parameterArray['imgSrc'],1200,0);
                        $html .= <<<EOF
<div class="panel"><div class="index-post-img"><a href="{$parameterArray['linkUrl']}"><img {$imageHtml} 
class="img-full lazy" /></a></div>
EOF;
                    }
                }else{//小版式头图
                    $backgroundImageHtml = Utils::returnDivLazyLoadHtml($parameterArray['imgSrc'],500,0);
                    $html .= <<<EOF
<div class="panel-small">
    <div class="index-post-img-small post-feature index-img-small">
        <a href="{$parameterArray['linkUrl']}">
            <div class="item-thumb-small lazy" {$backgroundImageHtml}></div>
        </a>
    </div>
EOF;
                }
            }
            //标题部分
            $html .= <<<EOF
<div class="post-meta wrapper-lg">
    <h2 class="m-t-none index-post-title"><a href="{$parameterArray['linkUrl']}">{$parameterArray['title']}</a></h2>
EOF;
            //if (!in_array('multiStyleThumb',$options->indexsetup)){
                //摘要部分
                $html .= <<<EOF
<p class="summary l-h-2x text-muted">{$parameterArray['summary']}</p>
EOF;
            //}
            //页脚部分，显示评论数、作者等信息
            $html .= <<<EOF
<div class="line line-lg b-b b-light"></div>
<div class="text-muted post-item-foot-icon">
<i class="fontello fontello-user text-muted"></i><span class="m-r-sm">&nbsp;<a href="{$parameterArray['authorUrl']}">{$parameterArray['author']}&nbsp;</a></span>

<i class="fontello fontello-clock-o text-muted"></i><span class="m-r-sm">&nbsp;{$dateString}</span>
EOF;
            if ($options->commentChoice == '0'){
                $html .= <<<EOF
<a href="{$parameterArray['linkUrl']}#comments" class="m-l-sm"><i class="iconfont icon-comments-o text-muted"></i>&nbsp;{$commentNumString}</a>
EOF;
            }else{
                $html .= <<<EOF
<a href="{$parameterArray['linkUrl']}#comments" class="m-l-sm"><i class="fontello fontello-eye text-muted"></i>&nbsp;{$viewNumString}</a>
EOF;

            }
            $html .= <<<EOF
</div><!--text-muted-->
</div><!--post-meta wrapper-lg-->
</div><!--panel/panel-small-->

EOF;
            /*if (in_array('multiStyleThumb',$options->indexsetup)){
                $html .='</div>';//<!--分栏 col-sm-6-->
            }*/

        }else{//文章页面的item结构，只有头图，没有其他的了
            if ($parameterArray['imgSrc'] != ""){
                if ($parameterArray['isFixedImg']){//固定头图大小
                    $html .= '<div class="entry-thumbnail" aria-hidden="true"><div class="item-thumb lazy" '.Utils::returnDivLazyLoadHtml($parameterArray['imgSrc'],1200,0) .'></div></div>';
                }else{
                    $html .= '<div class="entry-thumbnail" aria-hidden="true"><img width="100%" height="auto" '
                        .Utils::returnImageLazyLoadHtml($parameterArray['imgSrc'],1200,0).' 
 class="img-responsive lazy" /></div>';
                }
            }
        }
        return $html;
    }




    /**
     * 浏览器顶部标题
     * @param $obj
     * @param $title
     * @param $currentPage
     */
    public static function echoTitle($obj,$title,$currentPage){
        $options = mget();
        if($currentPage>1){
            echo '第'.$currentPage.'页 - ';
        }
        $obj->archiveTitle(array(
            'category'  =>  _mt('分类 %s 下的文章'),
            'search'    =>  _mt('包含关键字 %s 的文章'),
            'tag'       =>  _mt('标签 %s 下的文章'),
            'author'    =>  _mt('%s 发布的文章')
        ), '', ' - ');
        echo $title;

        $titleIntro = $options->titleintro;
        if ($obj->is('index') && $titleIntro != "") {
            echo ' - '.$options->titleintro.'';
        }
    }


    /**
     * 短代码解析正则替换回调函数
     * @param $matches
     * @return bool|string
     */
    public static function scodeParseCallback ($matches){
        // 不解析类似 [[player]] 双重括号的代码
        if ( $matches[1] == '[' && $matches[6] == ']' ) {
            return substr($matches[0], 1, -1);
        }
        //[scode type="share"]这是灰色的短代码框，常用来引用资料什么的[/scode]
        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = self::shortcode_parse_atts($attr);//获取短代码的参数
        $type = "info";
        switch ($attrs['type']){
            case 'yellow':
                $type = "warning";break;
            case 'red':
                $type = "error";break;
            case 'lblue':
                $type = "info";break;
            case 'green':
                $type = "success";break;
            case 'share':
                $type = "share";break;
        }
        return '<div class="tip inlineBlock '.$type.'">'.$matches[5].'</div>';
    }

    /**
     * 文档助手markdown正则替换回调函数
     * @param $matches
     * @return string
     */
    public static function sCodeMarkdownParseCallback($matches){
        $type = "info";
        switch ($matches[1]){
            case '!':
                $type = "warning";break;
            case 'x':
                $type = "error";break;
            case 'i':
                $type = "info";break;
            case '√':
                $type = "success";break;
            case '@':
                $type = "share";break;
        }
        return '<div class="tip inlineBlock '.$type.'">'.$matches[2].'</div>';
        //return $matches[2];
    }

    /**
     * 私密内容正则替换回调函数
     * @param $matches
     * @return bool|string
     */
    public static function secretContentParseCallback($matches){
        // 不解析类似 [[player]] 双重括号的代码
        if ( $matches[1] == '[' && $matches[6] == ']' ) {
            return substr($matches[0], 1, -1);
        }

        return '<div class="hideContent">'.$matches[5].'</div>';
    }


    /**
     * 折叠框解析
     * @param $matches
     * @return bool|string
     */
    public static function collapseParseCallback($matches){
        // 不解析类似 [[player]] 双重括号的代码
        if ( $matches[1] == '[' && $matches[6] == ']' ) {
            return substr($matches[0], 1, -1);
        }

        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = self::shortcode_parse_atts($attr);//获取短代码的参数

        $title = $attrs['title'];
        $default = @$attrs['status'];
        if ($default == null || $default == ""){
            $default = "true";
        }
        if ($default == "false"){//默认关闭
            $class = "collapse";
        }else{
            $class = "collapse in";
        }
        $content = $matches[5];
        $notice = _mt("开合");
        $id="collapse-" . md5(uniqid());

        return <<<EOF
<div class="panel b-a">
        <div class="panel-heading b-b b-light">{$title}
          <button class="btn btn-default btn-xs pull-right" data-toggle="collapse" data-target="#{$id}" 
          aria-expanded="true">{$notice}</button>
        </div>
        <div id="{$id}" class="panel-body {$class}">
          {$content}
        </div>
 </div>
EOF;



    }

    /**
     * 音乐解析的正则替换回调函数
     * @param $matches
     * @return bool|string
     */
    public static function musicParseCallback ($matches){
        /*
        $mathes array
        * 1 - An extra [ to allow for escaping shortcodes with double [[]]
        * 2 - 短代码名称
        * 3 - 短代码参数列表
        * 4 - 闭合标志
        * 5 - 内部包含的内容
        * 6 - An extra ] to allow for escaping shortcodes with double [[]]
     */

        // 不解析类似 [[player]] 双重括号的代码
        if ( $matches[1] == '[' && $matches[6] == ']' ) {
            return substr($matches[0], 1, -1);
        }

        //[hplayer media=&quot; netease&quot; type=&quot; song&quot;  id=&quot; 23324242&quot; /]
        //对$matches[3]的url如果被解析器解析成链接，这些需要反解析回来
        $matches[3] = preg_replace("/<a href=.*?>(.*?)<\/a>/",'$1',$matches[3]);
        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = self::shortcode_parse_atts($attr);//获取短代码的参数
        if (@$attrs['size'] =="small"){
            $size = "audio_wrp";
        }else{
            $size = "audio_wrp2";
        }

        if (@$attrs['id'] !=null){//云解析
            $getUrl = THEME_URL.'libs/Get.php?id='.@$attrs["id"].'&type='.@$attrs["type"].'&media='.@$attrs["media"];
        }else{
            $getUrl = "";
        }

        if (@$attrs['auto']){//自动播放
            $autoplay = 'auto="true"';
        }else{
            $autoplay = 'auto="false"';
        }
        $playCode = '<div class="weixinAudio">
<input type="hidden" name="url" value="'.$getUrl.'">
<input type="hidden" name="mp3" value="'.@$attrs['url'].'" >
<input type="hidden" name="title" value="'.@$attrs['title'].'" >
<input type="hidden" name="tips" value="'.@$attrs['author'].'" >
<audio title="" tips="" class="WeChatAudio" preload="none"><source src="" type="audio/mpeg"></audio><div class="db audio_area"><div 
class="'.$size.'"><div class="audio_play_area"><i class="icon_audio_default"></i><i 
class="icon_audio_playing"></i></div><div class="audio_length tips_global">00:00</div><div 
class="db audio_info_area"><strong class="db audio_title">加载中……</strong><span class="audio_source tips_global">请稍等……
</span><div class="progress_bar_bg"><div class="progress_bar" style="width: 0%;"></div></div></div></div></div></div>';
        return $playCode;

        //为自定义Mp3播放地址解析[player][/player]内部的[mp3]标签,这个地方以后可能才会用到，添加多首歌曲播放
    }


    /**
     * 视频解析的回调函数
     * @param $matches
     * @return bool|string
     */
    public static function videoParseCallback($matches){
        // 不解析类似 [[player]] 双重括号的代码
        if ( $matches[1] == '[' && $matches[6] == ']' ) {
            return substr($matches[0], 1, -1);
        }
        //对$matches[3]的url如果被解析器解析成链接，这些需要反解析回来
        $matches[3] = preg_replace("/<a href=.*?>(.*?)<\/a>/",'$1',$matches[3]);
        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = self::shortcode_parse_atts($attr);//获取短代码的参数
        if ($attrs['url'] != null || $attrs['url'] != ""){
            $url = $attrs['url'];
        }else{
            return "";
        }

        if (array_key_exists('pic',$attrs) && ($attrs['pic'] != null || $attrs['pic'] != "")){
            $pic = $attrs['pic'];
        }else{
            $pic = STATIC_PATH.'img/video.jpg';
        }
        $playCode = '<video src="'.$url.'"style="background-image:url('.$pic.');background-size: cover;"></video>';

        return $playCode;

    }




    /**
     * 一篇文章中引用另一篇文章正则替换回调函数
     * @param $matches
     * @return bool|string
     */
    public static function quoteOtherPostCallback($matches){
        $options = mget();
        // 不解析类似 [[post]] 双重括号的代码
        if ( $matches[1] == '[' && $matches[6] == ']' ) {
            return substr($matches[0], 1, -1);
        }

        //对$matches[3]的url如果被解析器解析成链接，这些需要反解析回来
        $matches[3] = preg_replace("/<a href=.*?>(.*?)<\/a>/",'$1',$matches[3]);
        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = self::shortcode_parse_atts($attr);//获取短代码的参数

        //这里需要对id做一个判断，避免空值出现错误
        $cid = @$attrs["cid"];
        $url = @$attrs['url'];
        $cover = @$attrs['cover'];//封面
        $targetTitle = "";//标题
        $targetUrl = "";//链接
        $targetSummary ="";//简介文字
        $targetImgSrc = "";//封面图片地址
        if ($cid !== "" && $cid != null){
            $db = Typecho_Db::get();
            $prefix = $db->getPrefix();
            $sticky_posts = $db->fetchAll($db
                ->select()->from($prefix.'contents')
                ->orWhere('cid = ?',$cid)
                ->where('type = ? AND status = ? AND password IS NULL', 'post', 'publish'));
            //这里需要对id正确性进行一个判断，避免查找文章失败
            if (count($sticky_posts) == 0)
                return "";
            $result = Typecho_Widget::widget('Widget_Abstract_Contents')->push($sticky_posts[0]);
            if ($cover == ""){
                $thumbArray =  $db->fetchAll($db
                    ->select()->from($prefix.'fields')
                    ->orWhere('cid = ?',$cid)
                    ->where('name = ? ', 'thumb'));
                $targetImgSrc = Content:: whenSwitchHeaderImgSrc(0,2,null,$result['text'],@$thumbArray[0]['str_value']);
            }else{
                $targetImgSrc = $cover;
            }
            $targetSummary = Content::excerpt(Markdown::convert($result['text']),60);
            $targetTitle = $result['title'];
            $targetUrl = $result['permalink'];
        }else if ($url !=""){
            $targetUrl = $url;
            $targetSummary = @$attrs['intro'];
            $targetTitle = @$attrs['title'];
            $targetImgSrc = $cover;
        }else{
            return "";
        }

        return <<<EOF
<div class="preview">
   <div class="post-inser post">
    <a href="{$targetUrl}" class="post_inser_a ">
    <div class="inner-image bg" style="background-image: url({$targetImgSrc});background-size: cover;"></div>
    <div class="inner-content">
     <p class="inser-title">{$targetTitle}</p>
     <div class="inster-summary">
      {$targetSummary}
     </div>
     <a href="{$targetUrl}" target="_blank">{$targetUrl}</a>
    </div>
    </a>
    <!-- .inner-content #####-->
   </div>
   <!-- .post-inser ####-->
  </div>
EOF;

    }

    /**
     * 解析显示按钮的短代码的正则替换回调函数
     * @param $matches
     * @return bool|string
     */
    public static function parseButtonCallback($matches){
        // 不解析类似 [[post]] 双重括号的代码
        if ( $matches[1] == '[' && $matches[6] == ']' ) {
            return substr($matches[0], 1, -1);
        }
        //对$matches[3]的url如果被解析器解析成链接，这些需要反解析回来
        /*$matches[3] = preg_replace("/<a href=.*?>(.*?)<\/a>/",'$1',$matches[3]);*/
        $attr = htmlspecialchars_decode($matches[3]);//还原转义前的参数列表
        $attrs = self::shortcode_parse_atts($attr);//获取短代码的参数
        $type = "";
        $color = "primary";
        $icon = "";
        $addOn = " ";
        $linkUrl = "";
        if (@$attrs['type'] == "round"){
            $type = "btn-rounded";
        }
        if (@$attrs['url'] != ""){
            $linkUrl = 'window.open("'.$attrs['url'].'","blank")';
        }
        if (@$attrs['color'] != ""){
            $color = $attrs['color'];
        }
        if (@$attrs['icon'] != ""){
            $icon = '<i class="'.$attrs['icon'].'"></i>';
            $addOn = 'btn-addon';
        }

        return <<<EOF
<button class="btn m-b-xs btn-{$color} {$type}{$addOn}" onclick='{$linkUrl}'>{$icon}{$matches[5]}</button>
EOF;
    }



    /**
     * 解析时光机页面的评论内容
     * @param $content
     * @return mixed
     */
    public static function timeMachineCommentContent($content){
        //时光机中播放器功能

        if ( strpos( $content, '[hplayer')!== false) {//提高效率，避免每篇文章都要解析
            $pattern = self::get_shortcode_regex(array('hplayer'));
            $content = preg_replace_callback("/$pattern/",array('Content','musicParseCallback'), $content);
        }

        //时光机中视频播放器功能
        if ( strpos( $content, '[vplayer')!== false) {//提高效率，避免每篇文章都要解析
            $pattern = self::get_shortcode_regex(array('vplayer'));
            $content = preg_replace_callback("/$pattern/",array('Content','videoParseCallback'), $content);
        }
        return $content;
    }


    /**
     * 解析文章页面的评论内容
     * @param $content
     * @param boolean $isLogin 是否登录
     * @param $rememberEmail
     * @param $currentEmail
     * @param $parentEmail
     * @return mixed
     */
    public static function postCommentContent($content, $isLogin, $rememberEmail, $currentEmail, $parentEmail){
        //解析表情
        $emotionPathPrefix = THEME_URL.'usr/img/emotion';
        $content = Utils::handle_preg_replace('/::([^:\s]*?):([^:\s]*?)::/sm','<img src="'.$emotionPathPrefix.'/$1/$2.png" class="emotion-$1">',$content);
        //评论中的链接，以新链接方式打开
        $content = preg_replace("/<a href=\"([^\"]*)\">/i", "<a href=\"\\1\" target=\"_blank\">", $content);
        //解析私密评论
        $flag = true;
        if ( strpos( $content, '[secret]')!== false) {//提高效率，避免每篇文章都要解析
            $pattern = self::get_shortcode_regex(array('secret'));
            $content = preg_replace_callback("/$pattern/",array('Content','secretContentParseCallback'), $content);
            if ($isLogin || ($currentEmail == $rememberEmail && $currentEmail != "") || ($parentEmail == $rememberEmail && $rememberEmail != "")){
                $flag = true;
//                echo "父评论邮箱".$parentEmail."|"."当前评论邮箱".$currentEmail."|"."记录的邮箱".$rememberEmail;
            }else{
//                echo "父评论邮箱".$parentEmail."|"."当前评论邮箱".$currentEmail."|"."记录的邮箱".$rememberEmail;
                $flag = false;
            }
        }
        if ($flag){
            return $content;
        }else{
            return '<div class="hideContent">该评论仅登录用户及评论双方可见</div>';
        }
    }


    /**
     * 输入内容之前做一些有趣的替换+输出文章内容
     *
     * @param $obj
     * @param $status
     */
    public static function postContent($obj,$status){
        $options = mget();
        $content = $obj->content;
        $isImagePost = self::isImageCategory($obj->categories);


        //镜像处理文章中的图片
        if($options->cdn_add!="") {
            $cdnArray = explode("|",$options->cdn_add);
            $localUrl = str_replace("/","\/",$options->rootUrl);//本地加速域名
            $cdnUrl = trim($cdnArray[0]," \t\n\r\0\x0B\2F");//cdn自定义域名
            $width=0;
            if ($isImagePost){
                $width = 300;//图片的缩略图大小
            }
            $suffix = Utils::getImageAddOn($options,true,trim($cdnArray[1]),$width,0);//图片云处理后缀
            $content = preg_replace('/(<img\s.*src=")' . $localUrl . '(.*?)"(.*?>)/', '$1' . $cdnUrl
                . '$2' .$suffix. '"$3', $content);
        }

        //延迟加载
        if (in_array('lazyload',$options->featuresetup)){//对图片进行处理
            $placeholder=Utils::choosePlaceholder($options);//图片占位符
            $style="";
            if ($isImagePost){
                $style = ' style="width:100%;height=100%"';
            }
            $content = preg_replace('/<img (.*?)src(.*?)(\/)?>/','<img $1src="'.$placeholder.'"'.$style.' data-original$2 />',$content);
        }

        if ($isImagePost){//照片文章
            self::postImagePost($content,$obj);
        }else{//普通文章
            if ($obj->hidden == true && trim($obj->fields->lock)!=""){//加密文章且没有访问权限
                echo '<p class="text-muted protected"><i class="glyphicon glyphicon-eye-open"></i>&nbsp;&nbsp;'._mt("密码提示").'：'.$obj->fields->lock.'</p>';
            }

            $db = Typecho_Db::get();
            $sql = $db->select()->from('table.comments')
                ->where('cid = ?',$obj->cid)
                ->where('status = ?','approved')
                ->where('mail = ?', $obj->remember('mail',true))
                ->limit(1);
            $result = $db->fetchAll($sql);//查看评论中是否有该游客的信息

            //文章中部分内容隐藏功能
            if($status || $result) {
                $content = preg_replace("/\[hide\](.*?)\[\/hide\]/sm",'<div class="hideContent">$1</div>',$content);
            }else{
                $content = preg_replace("/\[hide\](.*?)\[\/hide\]/sm",'<div class="hideContent">'._mt("此处内容需要评论回复后（审核通过）方可阅读。").'</div>',$content);
            }

            //文章中折叠框功能
            if (strpos($content, '[collapse') != false){
                $pattern = self::get_shortcode_regex(array('collapse'));
                $content = preg_replace_callback("/$pattern/",array('Content','collapseParseCallback'),
                    $content);
            }

            //文章中播放器功能
            if ( strpos( $content, '[hplayer')!== false) {//提高效率，避免每篇文章都要解析
                $pattern = self::get_shortcode_regex(array('hplayer'));
                $content = Utils::handle_preg_replace_callback("/$pattern/",array('Content','musicParseCallback'), $content);
            }

            //文章中视频播放器功能
            if ( strpos( $content, '[vplayer')!== false) {//提高效率，避免每篇文章都要解析
                $pattern = self::get_shortcode_regex(array('vplayer'));
                $content = Utils::handle_preg_replace_callback("/$pattern/",array('Content','videoParseCallback'), $content);
            }

            //解析文章中的表情短代码
            $emotionPathPrefix = THEME_URL.'usr/img/emotion';
            $content = Utils::handle_preg_replace('/::([^:\s]*?):([^:\s]*?)::/sm','<img src="'.$emotionPathPrefix.'/$1/$2.png" class="emotion-$1">',$content);

            //调用其他文章页面的摘要
            if ( strpos( $content, '[post')!== false) {//提高效率，避免每篇文章都要解析
                $pattern = self::get_shortcode_regex(array('post'));
                $content = preg_replace_callback("/$pattern/",array('Content','quoteOtherPostCallback'), $content);
            }

            //仅登录用户可查看的内容
            if ( strpos( $content, '[login')!== false) {//提高效率，避免每篇文章都要解析
                $pattern = self::get_shortcode_regex(array('login'));
                $isLogin = $status;
                $content = preg_replace_callback("/$pattern/",function ($matches) use ($isLogin) {
                    // 不解析类似 [[player]] 双重括号的代码
                    if ( $matches[1] == '[' && $matches[6] == ']' ) {
                        return substr($matches[0], 1, -1);
                    }
                    if($isLogin){
                        return '<div class="hideContent">'.$matches[5].'</div>';
                    }else{
                        return '<div class="hideContent">'._mt("该部分仅登录用户可见").'</div>';
                    }
                }, $content);
            }

            //markdown转HTML
            //$content = Markdown::convert($content);

            //解析短代码功能
            if ( strpos( $content, '[scode')!== false) {//提高效率，避免每篇文章都要解析
                $pattern = self::get_shortcode_regex(array('scode'));
                $content = Utils::handle_preg_replace_callback("/$pattern/",array('Content','scodeParseCallback'),
                    $content);
            }

            //解析markdown扩展语法

            if ($options->markdownExtend != "" && in_array('scode',$options->markdownExtend)){
                $content = Utils::handle_preg_replace_callback("/(@|√|!|x|i)&gt;\s(((?!<\/p>).)*)(<br \/>|<\/p>)/is",array('Content','sCodeMarkdownParseCallback'), $content);
            }

            //解析拼音注解写法
            if ($options->markdownExtend != "" && in_array('pinyin',$options->markdownExtend)){
                $content = Utils::handle_preg_replace('/\{\{\s*([^\:]+?)\s*\:\s*([^}]+?)\s*\}\}/is',
                    "<ruby>$1<rp> (</rp><rt>$2</rt><rp>) </rp></ruby>", $content);
            }


            //解析显示按钮短代码
            if ( strpos( $content, '[button')!== false) {//提高效率，避免每篇文章都要解析
                $pattern = self::get_shortcode_regex(array('button'));
                $content = Utils::handle_preg_replace_callback("/$pattern/",array('Content','parseButtonCallback'), $content);
            }



            //文章中的链接，以新链接方式打开
            $content = preg_replace("/<a href=\"([^\"]*)\">/i", "<a href=\"\\1\" target=\"_blank\">", $content);

            echo trim($content);

        }

    }

    public static function parsePlayer($content){

        $pattern = self::get_shortcode_regex(array('hplayer'));
        return preg_replace_callback("/$pattern/",array('Content','musicParseCallback'), $content);
    }


    /**
     * @param $content
     * @param $obj
     */
    public static function postImagePost($content,$obj){
        if ($obj->hidden === true){//输入密码访问
            echo $content;
        }else{
            preg_match_all('/<img.*?src="(.*?)"(.*?)alt="(.*?)"(.*?)\/?>/',$content,$matches);
            echo "<div class='photos'>";
            if (is_array($matches)){
                if (count($matches[0]) == 0){
                    echo '<small class="text-muted letterspacing indexWords">相册无图片</small>';
                }else{
                    for ($i = 0;$i<count($matches[0]);$i++){
                        echo <<<EOF
<figure class="image-thumb" itemprop="associatedMedia" itemscope="" itemtype="http://schema.org/ImageObject">
          {$matches[0][$i]}
          <figcaption itemprop="caption description">{$matches[3][$i]}</figcaption>
      </figure>
EOF;
                    }
                }
            }else{//
            }
            echo "</div>";
        }
    }

    /**
     * 获取匹配短代码的正则表达式
     * @param null $tagnames
     * @return string
     * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/shortcodes.php#L254

     */
    public static function get_shortcode_regex( $tagnames = null ) {
        global $shortcode_tags;
        if ( empty( $tagnames ) ) {
            $tagnames = array_keys( $shortcode_tags );
        }
        $tagregexp = join( '|', array_map( 'preg_quote', $tagnames ) );
        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
        return
            '\\['                                // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '(?![\\w-])'                       // Not followed by word character or hyphen
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            .     '(?:'
            .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
            .     ')*?'
            . ')'
            . '(?:'
            .     '(\\/)'                        // 4: Self closing tag ...
            .     '\\]'                          // ... and closing bracket
            . '|'
            .     '\\]'                          // Closing bracket
            .     '(?:'
            .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            .             '[^\\[]*+'             // Not an opening bracket
            .             '(?:'
            .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            .                 '[^\\[]*+'         // Not an opening bracket
            .             ')*+'
            .         ')'
            .         '\\[\\/\\2\\]'             // Closing shortcode tag
            .     ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
        // phpcs:enable
    }

    /**
     * 获取短代码属性数组
     * @param $text
     * @return array|string
     * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/shortcodes.php#L508
     */
    public static function shortcode_parse_atts($text) {
        $atts    = array();
        $pattern = self::get_shortcode_atts_regex();
        $text    = preg_replace( "/[\x{00a0}\x{200b}]+/u", ' ', $text );
        if ( preg_match_all( $pattern, $text, $match, PREG_SET_ORDER ) ) {
            foreach ( $match as $m ) {
                if ( ! empty( $m[1] ) ) {
                    $atts[ strtolower( $m[1] ) ] = stripcslashes( $m[2] );
                } elseif ( ! empty( $m[3] ) ) {
                    $atts[ strtolower( $m[3] ) ] = stripcslashes( $m[4] );
                } elseif ( ! empty( $m[5] ) ) {
                    $atts[ strtolower( $m[5] ) ] = stripcslashes( $m[6] );
                } elseif ( isset( $m[7] ) && strlen( $m[7] ) ) {
                    $atts[] = stripcslashes( $m[7] );
                } elseif ( isset( $m[8] ) && strlen( $m[8] ) ) {
                    $atts[] = stripcslashes( $m[8] );
                } elseif ( isset( $m[9] ) ) {
                    $atts[] = stripcslashes( $m[9] );
                }
            }
            // Reject any unclosed HTML elements
            foreach ( $atts as &$value ) {
                if ( false !== strpos( $value, '<' ) ) {
                    if ( 1 !== preg_match( '/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value ) ) {
                        $value = '';
                    }
                }
            }
        } else {
            $atts = ltrim( $text );
        }
        return $atts;
    }

    /**
     * Retrieve the shortcode attributes regex.
     *
     * @since 4.4.0
     *
     * @return string The shortcode attribute regular expression
     */
    public static function get_shortcode_atts_regex() {
        return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
    }

    public static function get_markdown_regex($tagName = '?'){
        return '\\'.$tagName.'&gt; (.*)(\n\n)?';

    }

    /**
     * 输出系统原生评论必须的js，主要是用来绑定按钮的
     * @param Widget_Archive $archive
     * @param $security
     */
    public static function outputCommentJS(Widget_Archive $archive, $security) {

        $options = mget();

        $header = "";
        if ($options->commentsThreaded && $archive->is('single')) {
            $header .= "<script type=\"text/javascript\">
(function () {
    window.TypechoComment = {
        dom : function (id) {
            return document.getElementById(id);
        },
    
        create : function (tag, attr) {
            var el = document.createElement(tag);
        
            for (var key in attr) {
                el.setAttribute(key, attr[key]);
            }
        
            return el;
        },

        reply : function (cid, coid) {
            var comment = this.dom(cid), parent = comment.parentNode,
                response = this.dom('" . $archive->respondId . "'), input = this.dom('comment-parent'),
                form = 'form' == response.tagName ? response : response.getElementsByTagName('form')[0],
                textarea = response.getElementsByTagName('textarea')[0];

            if (null == input) {
                input = this.create('input', {
                    'type' : 'hidden',
                    'name' : 'parent',
                    'id'   : 'comment-parent'
                });

                form.appendChild(input);
            }

            input.setAttribute('value', coid);

            if (null == this.dom('comment-form-place-holder')) {
                var holder = this.create('div', {
                    'id' : 'comment-form-place-holder'
                });

                response.parentNode.insertBefore(holder, response);
            }

            comment.appendChild(response);
            this.dom('cancel-comment-reply-link').style.display = '';

            if (null != textarea && 'text' == textarea.name) {
                textarea.focus();
            }

            return false;
        },

        cancelReply : function () {
            var response = this.dom('{$archive->respondId}'),
            holder = this.dom('comment-form-place-holder'), input = this.dom('comment-parent');

            if (null != input) {
                input.parentNode.removeChild(input);
            }

            if (null == holder) {
                return true;
            }

            this.dom('cancel-comment-reply-link').style.display = 'none';
            holder.parentNode.insertBefore(response, holder);
            return false;
        }
    };
})();
</script>
";
        }
        if ($archive->is('single')) {
            $requestURL = $archive->request->getRequestUrl();

            $requestURL = str_replace('&_pjax=%23content', '', $requestURL);
            $requestURL = str_replace('?_pjax=%23content', '', $requestURL);
            $requestURL = str_replace('_pjax=%23content', '', $requestURL);

            $header .= "<script type=\"text/javascript\">
var registCommentEvent = function() {
    var event = document.addEventListener ? {
        add: 'addEventListener',
        focus: 'focus',
        load: 'DOMContentLoaded'
    } : {
        add: 'attachEvent',
        focus: 'onfocus',
        load: 'onload'
    };
    var r = document.getElementById('{$archive->respondId}');
        
    if (null != r) {
        var forms = r.getElementsByTagName('form');
        if (forms.length > 0) {
            var f = forms[0], textarea = f.getElementsByTagName('textarea')[0], added = false;

            if (null != textarea && 'text' == textarea.name) {
                textarea[event.add](event.focus, function () {
                    if (!added) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = '_';
                            input.value = " . Typecho_Common::shuffleScriptVar(
                    $security->getToken($requestURL)) . "
                    
                        f.appendChild(input);
                        ";

                $header .= "
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'checkReferer';
                        input.value = 'false';
                        
                        f.appendChild(input);
                        ";

            $header .= "

                        added = true;
                    }
                });
            }
        }
    }
};
</script>";
        }
        echo $header;
    }


    /**
     * 生成typecho过滤头部输出信息的标志
     * @param Widget_Archive $archive
     * @return string
     */
    public static function exportGeneratorRules(Widget_Archive $archive){
        $rules = array(
            "commentReply",
        );

        //$options = mget();
        //if (@in_array('isPjax', $options->featuresetup)){
            //$rules[] = "antiSpam";
        //}
        $rules[] = "antiSpam";
        return join("&", $rules);
    }

    /**
     * 选择 最左上边的交集的地方 的颜色
     * @return string
     */
    public static function slectNavbarHeader(){
        $options = mget();
        $html = "";
        switch ($options->themetype){
            case 0:
                $html .= '<div class="navbar-header bg-black">';break;
            case 1:
                $html .= '<div class="navbar-header bg-dark">';break;
            case 2:
                $html .= '<div class="navbar-header bg-white-only">';break;
            case 3:
                $html .= '<div class="navbar-header bg-primary">';break;
            case 4:
                $html .= '<div class="navbar-header bg-info">';break;
            case 5:
                $html .= '<div class="navbar-header bg-success">';break;
            case 6:
                $html .= '<div class="navbar-header bg-danger">';break;
            case 7:
                $html .= '<div class="navbar-header bg-black">';break;
            case 8:
                $html .= '<div class="navbar-header bg-dark">';break;
            case 9:
                $html .= '<div class="navbar-header bg-info dker">';break;
            case 10:
                $html .= '<div class="navbar-header bg-primary">';break;
            case 11:
                $html .= '<div class="navbar-header bg-info dker">';break;
            case 12:
                $html .= '<div class="navbar-header bg-success">';break;
            case 13:
                $html .= '<div class="navbar-header bg-danger">';break;
            default:
                $html .= '<div class="navbar-header bg-danger">';break;
        }
        return $html;
    }

    /**
     * 选择顶部的导航栏的颜色
     * @return string
     */
    public static function selectNavbarCollapse(){
        $options = mget();
        $html = "";
        switch ($options->themetype){
            case 0:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-white-only">';break;
            case 1:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-white-only">';break;
            case 2:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-white-only">';break;
            case 3:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-white-only">';break;
            case 4:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-white-only">';break;
            case 5:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-white-only">';break;
            case 6:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-white-only">';break;
            case 7:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-black">';break;
            case 8:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-dark">';break;
            case 9:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-info dker">';break;
            case 10:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-primary">';break;
            case 11:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-info dk">';break;
            case 12:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-success">';break;
            case 13:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-danger">';break;
            default:
                $html .= '<div class="collapse pos-rlt navbar-collapse box-shadow bg-danger">';break;
        }

        return $html;
    }

    /**
     * 返回时光机插入按钮的model模态框HTML代码
     * @param $modelId
     * @param $okButtonId
     * @param $title
     * @param $label
     * @return string
     */
    public static function returnCrossInsertModelHtml($modelId,$okButtonId,$title,$label){
        return '
 <div class="modal fade" id="'.$modelId.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                <div class="modal-dialog modal-sm" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title" id="myModalLabel">'._mt("$title").'</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-lg-12 b-r">
                                                    <div class="form-group">
                                                        <label class="insert_tips">'._mt("$label").'</label>
                                                        <input name="'.$modelId.'" type="text" class="form-control" >
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                    data-dismiss="modal"> '._mt("取消").' </button>
                                            <button type="button" id="'.$okButtonId.'" class="btn btn-primary">'._mt("确定").'</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
 ';
    }

    /**
     * 返回pjax自定义动画所需的css
     * @return string
     */
    public static function returnPjaxAnimateCss(){
        $options = mget();
        $css = "";
        $color = $options->progressColor;

        switch (trim($options->pjaxAnimate)){
            case "minimal":
                $css = <<<EOF
.pace .pace-progress{
    background: {$color};
}
EOF;
                break;

            case "flash":
                $css = <<<EOF
.pace .pace-progress{
    background: {$color};
}

.pace .pace-progress-inner{
      box-shadow: 0 0 10px {$color}, 0 0 5px {$color};
}
.pace .pace-activity{
      border-top-color: {$color};
      border-left-color: {$color};
}
EOF;
                break;

            case "big-counter":
                $textColor = Utils::hex2rgb($color);
                $css = <<<EOF
.pace .pace-progress:after{
      color: rgba({$textColor}, 0.19999999999999996);
}
EOF;
                break;

            case "corner-indicator":
                $css = <<<EOF
.pace .pace-activity{
    background: {$color};
}
EOF;
                break;

            case "center-simple":
                $css = <<<EOF
.pace{
    border: 1px solid {$color};
    
}
.pace .pace-progress{
    background: {$color};
}
EOF;
                break;

            case "loading-bar":
                $css = <<<EOF
.pace .pace-progress{
      background: {$color};
      color: {$color};
}

.pace .pace-activity{
    box-shadow: inset 0 0 0 2px {$color}, inset 0 0 0 7px #FFF;
}
EOF;
                break;

            case "whiteRound":
                $css = <<<EOF
.loading {
    display: block;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.9);
    z-index: 123456789 !important;
    opacity: 1;
    -webkit-transition: opacity 0.3s ease;
    -moz-transition: opacity 0.3s ease;
    -ms-transition: opacity 0.3s ease;
    -o-transition: opacity 0.3s ease;
    transition: opacity 0.3s ease
}

.loading .preloader-inner {
    position: absolute;
    top: 50%;
    left: 50%;
    -webkit-transform: translate(-50%, -50%);
    -moz-transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    -o-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%)
}

.loading .loader-inner.ball-scale-multiple div {
    background-color: #000
}

.loading.loading-loaded {
    opacity: 0;
    display: none;
}

@-webkit-keyframes ball-scale-multiple {
    0% {
        -webkit-transform: scale(0);
        transform: scale(0);
        opacity: 0
    }

    5% {
        opacity: 1
    }

    100% {
        -webkit-transform: scale(1);
        transform: scale(1);
        opacity: 0
    }

}

@keyframes ball-scale-multiple {
    0% {
        -webkit-transform: scale(0);
        transform: scale(0);
        opacity: 0
    }

    5% {
        opacity: 1
    }

    100% {
        -webkit-transform: scale(1);
        transform: scale(1);
        opacity: 0
    }

}

.ball-scale-multiple {
    position: relative;
    -webkit-transform: translateY(-30px);
    transform: translateY(-30px)
}

.ball-scale-multiple>div:nth-child(2) {
    -webkit-animation-delay: -.4s;
    animation-delay: -.4s
}

.ball-scale-multiple>div:nth-child(3) {
    -webkit-animation-delay: -.2s;
    animation-delay: -.2s
}

.ball-scale-multiple>div {
    position: absolute;
    left: -30px;
    top: 0;
    opacity: 0;
    margin: 0;
    width: 60px;
    height: 60px;
    -webkit-animation: ball-scale-multiple 1s 0s linear infinite;
    animation: ball-scale-multiple 1s 0s linear infinite
}

.ball-scale-multiple>div {
    background-color: #fff;
    border-radius: 100%
}

EOF;

                break;
            case "customise":
                $css = $options->pjaxCusomterAnimateCSS;
                break;
        }
        return $css;
    }


    /**
     * 热门文章，按照评论数目排序
     * @param $hot
     */
    public static function returnHotPosts($hot){
        $options = mget();
        $days = 99999999999999;
        $time = time() - (24 * 60 * 60 * $days);
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        //echo date('Y-n-j H:i:s',time());
        if (!array_key_exists('views', $db->fetchRow($db->select()->from('table.contents')))){
            $db->query('ALTER TABLE '. $prefix .'contents ADD views INTEGER(10) DEFAULT 0;');
        }
        $orderType = $options->hotPostOrderType;
        if (empty($orderType)){
            $orderType = 'commentsNum';
        }else{
            $orderType = $options->hotPostOrderType;
        }
        $sql = $db->select()->from('table.contents')
            ->where('created >= ?', $time)
            ->where('table.contents.created <= ?', time())
            ->where('type = ?', 'post')
            ->where('status =?', 'publish')
            ->limit(5)
            ->order($orderType,Typecho_Db::SORT_DESC);
        //echo $sql->__toString();
        $result = $db->fetchAll($sql);
        $index = 0;
        $isShowImage = true;
        if (count($options->indexsetup)>0 && in_array('notShowRightSideThumb',$options->indexsetup)){
            $isShowImage = false;
        }
        foreach($result as $val){
            $val = Typecho_Widget::widget('Widget_Abstract_Contents')->filter($val);
            echo '<li class="list-group-item">
                <a href="' . $val['permalink'] . '" class="pull-left thumb-sm m-r">'.self::returnRightSideImageHtml($isShowImage,$hot,$index).'</a>
                <div class="clear">
                    <h4 class="h5 l-h"> <a href="' . $val['permalink'] . '" title="' . $val['title'] . '"> ' . $val['title'] . ' </a></h4>
                    <small class="text-muted post-head-icon">
                    <span class="meta-views"> <i class="iconfont icon-comments-o" aria-hidden="true"></i> <span class="sr-only">评论数：</span> <span class="meta-value">'.$val['commentsNum'].'</span>
                    </span>
                    <span class="meta-date m-l-sm"> <i class="fontello fontello-eye" aria-hidden="true"></i> <span class="sr-only">浏览次数:</span> <span class="meta-value">'.$val['views'].'</span>
                    </span>
                    </small>
                    </div>
            </li>';
            $index ++;
        }
    }

    /**
     * 随机文章，显示5篇
     * @param $random
     */
    public static function returnRandomPosts($random){
        $options = mget();
        $modified = $random->modified;
        $db = Typecho_Db::get();
        $adapterName = $db->getAdapterName();//兼容非MySQL数据库
        if($adapterName == 'pgsql' || $adapterName == 'Pdo_Pgsql' || $adapterName == 'Pdo_SQLite' || $adapterName == 'SQLite'){
            $order_by = 'RANDOM()';
        }else{
            $order_by = 'RAND()';
        }
        $sql = $db->select()->from('table.contents')
            ->where('status = ?','publish')
            ->where('table.contents.created <= ?', time())
            ->where('type = ?', 'post')
            ->limit(5)
            ->order($order_by);

        $result = $db->fetchAll($sql);
        $index = 0;
        $isShowImage = true;
        if (count($options->indexsetup)>0 && in_array('notShowRightSideThumb',$options->indexsetup)){
            $isShowImage = false;
        }
        foreach($result as $val){
            $val = Typecho_Widget::widget('Widget_Abstract_Contents')->filter($val);
            echo '<li class="list-group-item">
                <a href="' . $val['permalink'] . '" class="pull-left thumb-sm m-r">'.self::returnRightSideImageHtml($isShowImage,$random,$index).'</a>
                <div class="clear">
                    <h4 class="h5 l-h"> <a href="' . $val['permalink'] . '" title="' . $val['title'] . '"> ' . $val['title'] . ' </a></h4>
                    <small class="text-muted post-head-icon">
                    <span class="meta-views"> <i class="iconfont icon-comments-o" aria-hidden="true"></i> <span class="sr-only">评论数：</span> <span class="meta-value">'.$val['commentsNum'].'</span>
                    </span>
                    <span class="meta-date m-l-sm"> <i class="fontello fontello-eye" aria-hidden="true"></i> <span class="sr-only">浏览次数:</span> <span class="meta-value">'.$val['views'].'</span>
                    </span>
                    </small>
                    </div>
            </li>';
            $index ++;
        }
    }

    /**
     * @param $isShowImage
     * @param $obj
     * @param $index
     * @return string
     */
    public static function returnRightSideImageHtml($isShowImage,$obj,$index){
        if ($isShowImage){
            return '<img style="height: 40px!important;width: 40px!important;" src="'.Utils::returnImageSrcWithSuffix
                (showSidebarThumbnail($obj,
                    $index),null,40,40).'" class="img-circle">';
        }else{
            return "";
        }
    }


    /**
     * 返回主题的文章显示动画class
     * @param $obj
     */
    public static function returnPageAnimateClass($obj){
        $options = mget();
        if (in_array('isPageAnimate',$options->featuresetup) && ($obj->is('post') || $obj->is('page'))){
            echo "animated ng-enter";
        }else if (in_array('isOtherAnimate',$options->featuresetup) && !$obj->is('post') && !$obj->is('page')){
            echo "animated ng-enter";
        }else{
            echo "";
        }
    }

    public static function returnCommentList($obj,$comments){
        if ($comments->have()){
            echo '<h4 class="comments-title m-t-lg m-b">';
            $obj->commentsNum(_mt('暂无评论'), _mt('1 条评论'), _mt('%d 条评论'));
            echo '</h4>';
            $comments->listComments();//列举评论
            echo '<nav class="text-center m-t-lg m-b-lg" role="navigation">';
            $comments->pageNav('<i class="fontello fontello-chevron-left"></i>', '<i class="fontello fontello-chevron-right"></i>');
            echo '</nav>';
        }
    }


    /**
     * @param $categories
     * @return string
     */
    public static function returnCategories($categories){
        $html = "";
        $options = mget();
        while($categories->next()){
            if ($categories->levels === 0){//父亲分类

                $children = $categories->getAllChildren($categories->mid);//获取当前父分类所有子分类
                //print_r($children);
                //var_dump(empty($children));
                if (!empty($children)){//子分类不为空
                    $html .= '<li><a class="auto"><span class="pull-right text-muted">
                    <i class="fontello icon-fw fontello-angle-right text"></i>
                    <i class="fontello icon-fw fontello-angle-down text-active"></i>
                  </span><span>'.$categories->name.'</span></a>';
                    //循环输出子分类
                    $childCategoryHtml = '<ul class="nav nav-sub dk child-nav">';
                    //有子分类判断是否输出父分类
                    if (!in_array('noShowParentCategory',$options->featuresetup)){
                        $childCategoryHtml .= '<li><a href="'.$categories->permalink.'"><b class="badge pull-right">'.$categories->count.'</b><span>'.$categories->name.'</span></a></li>';
                    }
                    foreach ($children as $mid){
                        $child = $categories->getCategory($mid);
                        $childCategoryHtml .= '<li><a href="'.$child['permalink'].'"><b class="badge pull-right">'.$child['count'].'</b><span>'.$child['name'].'</span></a></li>';
                    }
                    $childCategoryHtml .= '</ul>';

                    $html .= $childCategoryHtml;
                    $html .= "</li>";
                }else{//没有子分类
                    $html .= '<li><a href="'.$categories->permalink.'"><b class="badge pull-right">'.$categories->count.'</b><span>'.$categories->name.'</span></a></li>';
                }
            }
        }

        return $html;
    }

    public static function returnPjaxAnimateHtml(){
        $options = mget();
        $html = "";
        if ($options->pjaxAnimate == "default"){
            $html .= '<div id="loading" class="butterbar active hide">
            <span class="bar"></span>
        </div>';
        }else if ($options->pjaxAnimate == "whiteRound"){
            $html .='<section id="loading" class="loading hide">
    <div class="preloader-inner">
        <div class="loader-inner ball-scale-multiple"><div></div><div></div><div></div></div>
    </div>
</section>';
        }else if ($options->pjaxAnimate == "customise"){
            $html .= $options->pjaxCusomterAnimateHtml;
        }
        return $html;
    }

}


