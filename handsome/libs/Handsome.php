<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * handsome.php
 * Author     : hewro,hran
 * Date       : 2017/04/23
 * Version    : 1.0.0
 * Description: typecho后台优化的一些方法：包括后台外观设置样式、编辑器样式、检查主题更新
 */
class Handsome{

	public static $version;//主版本号
	public static $versionTag = "20180901301";//版本号后缀，区别同一版本不同修改日期
    public static $times = 0;//向编辑器输出js会莫名其妙的输出两次，所以用一个变量控制
    public static $handsome;

    public static $cdnSetting = null;

    /**
     * 用户初始化信息 = 欢迎信息 + 版本检查
     * @return string
     */
    public static function SettingsWelcome(){
		return self::useIntro() . self::checkupdatejs();
	}

	public static function initCdnSetting(){
        $options = mget();
        if (!defined('PUBLIC_CDN')){
            switch ($options->publicCDNSelcet){
                case 0:
                    @define('PUBLIC_CDN',serialize(Handsome_Config::$BOOT_CDN));
                    break;
                case 1:
                    @define('PUBLIC_CDN',serialize(Handsome_Config::$BAIDU_CDN));
                    break;
                case 2:
                    @define('PUBLIC_CDN',serialize(Handsome_Config::$SINA_CDN));
                    break;
                case 3:
                    @define('PUBLIC_CDN',serialize(Handsome_Config::$QINIU_CDN));
                    break;
                case 4:
                    @define('PUBLIC_CDN',serialize(Handsome_Config::$JSDELIVR_CDN));
                    break;
                case 5:
                    @define('PUBLIC_CDN',serialize(Handsome_Config::$CAT_CDN));
                    break;
                default:
                    @define('PUBLIC_CDN',serialize(Handsome_Config::$SINA_CDN));
                    break;
            }
        }
    }


    /**
     * 随机选取背景颜色
     * @return mixed
     */
    public static function getBackgroundColor(){
		$colors = array(
			array('#673AB7', '#512DA8'),
			array('#20af42', '#1a9c39'),
			array('#336666', '#2d4e4e'),
			array('#2e3344', '#232735')
		);
		$randomKey = array_rand($colors, 1);
		$randomColor = $colors[$randomKey];
		return $randomColor;
	}


    /**
     * 输出用户欢迎信息
     * @return string
     */
    public static function useIntro(){
        self::$version = self::returnHandsomeVersion();
        $version = (string)self::$version;
        $randomColor = self::getBackgroundColor();
        Handsome::initCdnSetting();
        $PUBLIC_CDN_ARRAY = unserialize(PUBLIC_CDN);
        $db = Typecho_Db::get();
        $backupInfo = "";
        if ($db->fetchRow($db->select()->from ('table.options')->where ('name = ?', 'theme:HandsomePro-X-Backup'))){
            $backupInfo = '<div class="mdui-chip" style="color: rgb(26, 188, 156);"><span 
        class="mdui-chip-icon mdui-color-green"><i class="mdui-icon material-icons">&#xe8ba;</i></span><span class="mdui-chip-title">数据库存在主题数据备份</span></div>';
        }else{
            $backupInfo = '<div class="mdui-chip" style="color: rgb(26, 188, 156);"><span 
        class="mdui-chip-icon mdui-color-red"><i class="mdui-icon material-icons">&#xe8ba;</i></span><span 
        class="mdui-chip-title" style="color: rgb(255, 82, 82);">没有主题数据备份</span></div>';
        }
        return <<<EOF
<link href="{$PUBLIC_CDN_ARRAY['css']['mdui']}" rel="stylesheet">
<div class="mdui-card">
  <!-- 卡片的媒体内容，可以包含图片、视频等媒体内容，以及标题、副标题 -->
  <div class="mdui-card-media">    
    <!-- 卡片中可以包含一个或多个菜单按钮 -->
    <div class="mdui-card-menu">
      <button class="mdui-btn mdui-btn-icon mdui-text-color-white"><i class="mdui-icon material-icons">share</i></button>
    </div>
  </div>
  
  <!-- 卡片的标题和副标题 -->
  <div class="mdui-card-primary">
    <div class="mdui-card-primary-title">Handsome {$version} Pro</div>
    <div class="mdui-card-primary-subtitle">
        <div id="update_notification">
            <div class="mdui-progress">
                <div class="mdui-progress-indeterminate"></div>
            </div>
            <div class="checking">检查更新中……</div>
        </div>
     </div>
  </div>
  
  <!-- 卡片的内容 -->
  <!--<div class="mdui-card-content">幸福这种东西啊，要是不放弃什么就得不到啊。</div>-->
  <!--备份情况-->
<div class="mdui-card-primary-subtitle mdui-card-content">
        {$backupInfo}
     </div>
  <!-- 卡片的按钮 -->
  <div class="mdui-card-actions">
    <button class="mdui-btn mdui-ripple"><a href="https://handsome.ihewro.com/" mdui-tooltip="{content: 
    '主题99%的使用问题都可以通过文档解决，文档有搜索功能快试试！'}"}>使用文档</a></button>
    <button class="mdui-btn mdui-ripple"><a href="https://www.ihewro.com/archives/489/" mdui-tooltip="{content:'博客本质是记录，所以希望这款主题能够让你在时间中留下痕迹'}">主题介绍</a></button>
    <button class="mdui-btn mdui-ripple showSettings">展开所有设置</button>
    <button class="mdui-btn mdui-ripple hideSettings">折叠所有设置</button>
    <button class="mdui-btn mdui-ripple recover_back_up" mdui-tooltip="{content: '从主题备份恢复数据'}">从主题备份恢复数据</button>
    <button class="mdui-btn mdui-ripple back_up" 
    mdui-tooltip="{content: '1. 仅仅是备份handsome主题的外观数据</br>2. 切换主题的时候，虽然以前的外观设置的会清空但是备份数据不会被删除。</br>3. 所以当你切换回来之后，可以恢复备份数据。</br>4. 备份数据同样是备份到数据库中。</br>5. 如果已有备份数据，再次备份会覆盖之前备份'}">
    备份主题数据</button>
    <button class="mdui-btn mdui-ripple un_back_up" mdui-tooltip="{content: '删除handsome备份数据'}">删除现有handsome备份</button>
  </div>
  
</div>

<div class="mdui-dialog" id="updateDialog">
    <div class="mdui-dialog-content">
      <div class="mdui-dialog-title">更新说明</div>
      <div class="mdui-dialog-content" id="update-dialog-content">获取更新内容失败，请稍后重试</div>
    </div>
    <div class="mdui-dialog-actions">
      <button class="mdui-btn mdui-ripple" mdui-dialog-close>取消</button>
      <button class="mdui-btn mdui-ripple" mdui-dialog-confirm>前往更新</button>
    </div>
  </div>


EOF;
    }



    /**
     * 检查更新逻辑
     * @return string
     */
    public static function checkupdatejs(){
        //self::$version = self::returnHandsomeVersion();
		$current_version = self::$version;
		//echo $current_version;
        Handsome::initCdnSetting();
        $PUBLIC_CDN_ARRAY = unserialize(PUBLIC_CDN);
        $options = mget();
        $blog_url = $options->rootUrl;
        return <<<EOF
<script src="{$PUBLIC_CDN_ARRAY['js']['mdui']}"></script>
<script>mdui.JQ(function () { $('form:eq(0)').attr('action', $('form:eq(1)').attr('action')); });
    mdui.mutation() </script>
    
<script src="{$PUBLIC_CDN_ARRAY['js']['jquery']}" type="text/javascript"></script>
<script>
var VersionCompare = function (currVer, promoteVer) {
    currVer = currVer || "0.0.0";
    promoteVer = promoteVer || "0.0.0";
    if (currVer == promoteVer) return false;
    var currVerArr = currVer.split(".");
    var promoteVerArr = promoteVer.split(".");
    var len = Math.max(currVerArr.length, promoteVerArr.length);
    for (var i = 0; i < len; i++) {
        var proVal = ~~promoteVerArr[i],
            curVal = ~~currVerArr[i];
        if (proVal < curVal) {
            return false;
        } else if (proVal > curVal) {
            return true;
        }
    }
    return false;
};

(function($){
    $.getJSON("https://api.github.com/repos/ihewro/typecho-theme-handsome/releases/latest",
    function(data){
        $("#update_notification").addClass('mdui-chip');
        if(VersionCompare("$current_version", data.tag_name)){//有更新版本更新
        
            var updateWord = "新版本" +  data.tag_name + "已可用，点击查看";
            var message = "<span class=\"mdui-chip-icon mdui-color-red\"><i class=\"mdui-icon material-icons\">&#xe8d7;</i></span><span class=\"mdui-chip-title\">"+updateWord+"</span>";
            var color = "#ff5252";
            $("#update-dialog-content").html(data.body.replace(/\\n|\\r/g,'<br />'));
            $("#update_notification").css("color",color).html(message);
            $("#update_notification").attr("mdui-dialog","{target: '#updateDialog'}");
            
            mdui.JQ('#updateDialog').on('confirm.mdui.dialog', function (e) {
                mdui.alert('请前往QQ售后群查看群公告以获取正确下载方式');
            })
            
        }else{//当前为最新版本
        
            $("#update_notification").css("color","#1abc9c").html("<span class=\"mdui-chip-icon mdui-color-green\"><i class=\"mdui-icon material-icons\">&#xe2bf;</i></span><span class=\"mdui-chip-title\">当前是最新版本</span>");
            
            $('#update_notification').on('click', function () {
                mdui.snackbar({
                    message: '当前是最新版本',
                    position: 'bottom'
                });
            });
            
        }
    });
    
    $("body").delegate(".appearanceTitle","click",function(){
        $(this).next().slideToggle();
    });
     $(function(){
         $('.showSettings').bind('click',function() {
           $('.mdui-panel-item').addClass('mdui-panel-item-open');
         });
         $('.hideSettings').bind('click',function() {
            $('.mdui-panel-item').removeClass('mdui-panel-item-open');
         });
     });
     
     $('.back_up').click(function() {
        $.ajax({
            url: '$blog_url',
            data: {action:"back_up"},
            success: function(data) {
                if (data !== "-1"){
                    mdui.snackbar({
                    message: '备份成功，操作码:' + data +',正在刷新页面……',
                    position: 'bottom'
                });
                    setTimeout(function (){
                    location.reload();
                },1000);
                }else {
                    mdui.snackbar({
                    message: '备份失败,错误码' + data,
                    position: 'bottom'
                });
                }
            }
        })
     });
     
     
     $('.un_back_up').click(function() {
        $.ajax({
            url: '$blog_url',
            data: {action:"un_back_up"},
            success: function(data) {
                if (data !== "-1"){
                    mdui.snackbar({
                    message: '删除备份成功，操作码:' + data +',正在刷新页面……',
                    position: 'bottom'
                });
                    setTimeout(function (){
                    location.reload();
                },1000);
                }else {
                    mdui.snackbar({
                    message: '删除备份失败,错误码' + data,
                    position: 'bottom'
                });
                }
            }
        })
     });
     
     $('.recover_back_up').click(function() {
        $.ajax({
            url: '$blog_url',
            data: {action:"recover_back_up"},
            success: function(data) {
                if (data !== "-1"){
                    mdui.snackbar({
                    message: '恢复备份成功，操作码:' + data +',正在刷新页面……',
                    position: 'bottom'
                });
                    setTimeout(function (){
                    location.reload();
                },1000);
                }else {
                    mdui.snackbar({
                    message: '恢复备份失败,错误码' + data,
                    position: 'bottom'
                });
                }
            }
        })
     });
   
    
})(jQuery)

</script>
EOF;
    }
    /**
     * 返回handsome主题的信息（版本号和介绍），以便进行检查和显示
     * @return mixed
     */
    public static function returnHandsomeVersion(){
        $version = "";
        $themes = null;
        Typecho_Widget::widget('Widget_Themes_List')->to($themes);
        while($themes->next()){
            if ($themes->name == 'handsome'){
                $version = $themes->version;
                break;
            }
        }
        if (trim($version) == ""){
            $version = "4.5.1";
        }
        return $version;
    }


    /**
     * 输出到后台外观设置的css
     * @return string
     */
    public static function styleoutput(){
        $randomColor = self::getBackgroundColor();
        //$randomColor[0] = "#fff";
        return <<<EOF
<style>
/*后台外观全局控制*/

.mdui-panel-item-sub-header{
    color: #999;
    margin-left: 25px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.typecho-option span{
    display: block;
}
.description {
    margin: .5em 0 0;
    color: #999;
    font-size: .92857em;
}

.description:hover{
    color:#333;
    transition: 0.3s;
}
.checking{
    margin-top: 10px;
}

#update_notification {
    margin-top: 25px;
}
button.btn.primary {
    display: none;
}
.mdui-btn[class*=mdui-color-]:hover, .mdui-fab[class*=mdui-color-]:hover {
    opacity: .87;
    background: #00BCD4;
}
label.settings-subtitle {
    color: #999;
    font-size: 10px;
    font-weight: normal;
}
.settingsbutton{
    margin-bottom:10px;
    display:block
}
.settingsbutton a{
    margin-right: 10px;
}

@media screen and (min-device-width: 1024px) {
    ::-webkit-scrollbar-track {
        background-color: rgba(255,255,255,0);
    }
    ::-webkit-scrollbar {
        width: 6px;
        background-color: rgba(255,255,255,0);
    }
    ::-webkit-scrollbar-thumb {
        border-radius: 3px;
        background-color: rgba(193,193,193,1);
    }
}
.row {
    margin: 0px;
}

code, pre, .mono {
    background: #e8e8e8;
}
#use-intro {
    box-shadow: 0 2px 2px 0 rgba(0,0,0,.14),0 3px 1px -2px rgba(0,0,0,.2),0 1px 5px 0 rgba(0,0,0,.12);
    background-color: #fff;
    margin: 8px;
    padding: 8px;
    padding-left: 20px;
    margin-bottom: 40px;
}
.message{
    background-color:{$randomColor[0]} !important;
    color:#fff;
}
.success{
    background-color:{$randomColor[0]};
    color:#fff;
}

#typecho-nav-list{display:none;}
.typecho-head-nav {
    padding: 0 10px;
    background: {$randomColor[0]};
}
.typecho-head-nav .operate a{
    border: none;
    padding-top: 0px;
    padding-bottom: 0px;
    color: rgba(255,255,255,.6);
}
.typecho-head-nav .operate a:hover {
    background-color: rgba(0, 0, 0, 0.05);
    color: #fff;
}
ul.typecho-option-tabs.fix-tabs.clearfix {
    background: {$randomColor[1]};
}
.col-mb-12 {
    padding: 0px!important;
}
.typecho-page-title {
    margin:0;
    height: 70px;
    background: {$randomColor[0]};
    background-size: cover;
    padding: 30px;
}
.typecho-page-title h2{
    margin: 0px;
    font-size: 2.28571em;
    color: #fff;
}
.typecho-option-tabs{
    padding: 0px;
    background: #fff;
}
.typecho-option-tabs a:hover{
    background-color: rgba(0, 0, 0, 0.05);
    color: rgba(255,255,255,.8);
}
.typecho-option-tabs a{
    border: none;
    height: auto;
    color: rgba(255,255,255,.6);
    padding: 15px;
}
li.current {
    background-color: #FFF;
    height: 4px;
    padding: 0 !important;
    bottom: 0px;
}
.typecho-option-tabs li.current a, .typecho-option-tabs li.active a{
    background:none;
}
.container{
    margin:0;
    padding:0;
}
.body.container {
    min-width: 100% !important;
    padding: 0px;
}
.typecho-option-tabs{
    margin:0;
}
.typecho-option-submit button {
    float: right;
    background: #00BCD4;
    box-shadow: 0 2px 2px 0 rgba(0,0,0,.14),0 3px 1px -2px rgba(0,0,0,.2),0 1px 5px 0 rgba(0,0,0,.12);
    color: #FFF;
}
.typecho-option-tabs li{
    margin-left:20px;
}
.typecho-option{
    border-radius: 3px;
    background: #fff;
    padding: 12px 16px;
}
.col-mb-12{
    padding-left: 0px!important;
}
.typecho-option-submit{
    background:none!important;
}
.typecho-option {
    float: left;
}
.typecho-option span {
    margin-right: 0;
}
.typecho-option label.typecho-label {
    font-weight: 500;
    margin-bottom: 10px;
    margin-top: 10px;
    font-size: 16px;
    padding-bottom: 5px;
    border-bottom: 1px solid rgba(0,0,0,0.2);
}
.typecho-page-main .typecho-option input.text {
    width: 100%;
}
input[type=text], textarea {
    border: none;
    border-bottom: 1px solid rgba(0,0,0,.60);
    outline: none;
    border-radius: 0;
}
.typecho-option-submit {
    position: fixed;
    right: 32px;
    bottom: 32px;
}
.typecho-foot {
    padding: 16px 40px;
    color: rgb(158, 158, 158);
    background-color: rgb(66, 66, 66);
    margin-top: 80px;
}
.typecho-option .description{
    font-weight: normal;
}
@media screen and (max-width: 480px){
.typecho-option {
    width: 94% !important;
    margin-bottom: 20px !important;
}
}
/*大标题样式控制*/
label.typecho-label.settings-title{
	font-size: 30px;
    font-weight: bold;
    border: none;
}
.settings-title:hover {
    text-decoration: underline;
}
.appearanceTitle{
    float: inherit;
    margin-bottom: 0px;
	box-shadow: 0 2px 2px 0 rgba(0,0,0,.14), 0 3px 1px -2px rgba(0,0,0,.2), 0 1px 5px 0 rgba(0,0,0,.12);
    background-color: #fff;
    margin: 8px 1%;
    padding: 8px 2%;
    width: 94%;
	display: table;
	background-color: #f6f8f8;
}


/*组件大小为94%*/
.length-94{
    box-shadow: 0 2px 2px 0 rgba(0,0,0,.14), 0 3px 1px -2px rgba(0,0,0,.2), 0 1px 5px 0 rgba(0,0,0,.12);
    background-color: #fff;
    margin: 8px 1%;
    padding: 8px 2%;
    width: 94%;
	margin-bottom:20px;
}

/*组件大小为60%*/
.length-60{
    box-shadow: 0 2px 2px 0 rgba(0,0,0,.14), 0 3px 1px -2px rgba(0,0,0,.2), 0 1px 5px 0 rgba(0,0,0,.12);
    background-color: #fff;
    margin: 8px 1%;
    padding: 8px 2%;
    width: 60%;
}

/*组件大小为44%*/
.length-44{
    box-shadow: 0 2px 2px 0 rgba(0,0,0,.14), 0 3px 1px -2px rgba(0,0,0,.2), 0 1px 5px 0 rgba(0,0,0,.12);
    background-color: #fff;
    margin: 8px 1%;
    padding: 8px 2%;
    width: 44%;
    margin-bottom: 30px;
}

/*组件大小为27%*/
.length-27{
    box-shadow: 0 2px 2px 0 rgba(0,0,0,.14), 0 3px 1px -2px rgba(0,0,0,.2), 0 1px 5px 0 rgba(0,0,0,.12);
    background-color: #fff;
    margin: 8px 1%;
    padding: 8px 2%;
    width: 27.333%;
    margin-bottom: 40px;
}


/*组件大小为29%*/
.length-29 {
    box-shadow: 0 2px 2px 0 rgba(0,0,0,.14),0 3px 1px -2px rgba(0,0,0,.2),0 1px 5px 0 rgba(0,0,0,.12);
    background-color: #fff;
    margin: 8px 1%;
    padding: 8px 2%;
    width: 29%;
}


/*组件大小为59%*/
.length-59{
    box-shadow: 0 2px 2px 0 rgba(0,0,0,.14),0 3px 1px -2px rgba(0,0,0,.2),0 1px 5px 0 rgba(0,0,0,.12);
    background-color: #fff;
    margin: 8px 1%;
    padding: 8px 2%;
    width: 59%;
	margin-bottom: 30px;
}


#typecho-option-item-BGtype-2 {
    margin-bottom: 0px;
}
#typecho-option-item-bgcolor-4 {
    margin-bottom: 20px;
}
#typecho-option-item-BlogJob-10 {
    margin-bottom: 55px;
}
#typecho-option-item-titleintro-8{
    margin-bottom: 50px;
}
</style>
EOF;
    }


    /**
     * 输出到后台编辑器的js和css
     * @return string
     */
    public static function outputEditorJS(){
        $options = mget();
        if (!defined('THEME_URL')){//主题目录的绝对地址
            define("THEME_URL", rtrim(preg_replace('/^'.preg_quote($options->siteUrl, '/').'/', $options->rootUrl.'/', $options->themeUrl, 1),'/').'/');
        }
        $themeUrl = THEME_URL;
        $url = $themeUrl.'libs/Get.php';
        $versionPrefix = Handsome::$version.Handsome::$versionTag;

        Handsome::$times ++;
        self::initCdnSetting();
        $PUBLIC_CDN_ARRAY = unserialize(PUBLIC_CDN);
        return "
    <link rel=\"stylesheet\" href=\"{$themeUrl}assets/css/owo.css?v={$versionPrefix}\" type=\"text/css\" />
<style>
.insert_button {
    display: inline-block;
    color: #999;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 13px;
    padding: 2px 5px;
    cursor: pointer;
    height: 22px;
    box-sizing: border-box;
    z-index: 2;
    line-height: 16px;
    margin-right: 10px;
    margin-bottom: 10px;
}
.typecho-list-table textarea, .typecho-list-table input[type=\"text\"] {
    width: 100%;
}

@media(max-width:990px ){

.span_insert{
    position: relative;
    user-select: none;
    margin-top: 25px;
    display: inline-block;
}
}

@media(max-width: 325px){
.span_insert{
    position: relative;
    user-select: none;
    margin-top: 45px;
    display: inline-block;
}
}

.OwO .OwO-body{
    top: 21px;
    position: absolute;
}
.OwO-logo .fontello-emo-tongue{
    display: none;
}
.OwO .OwO-logo{
    margin-top: 0px;
    margin-bottom: 3px;
    width: 40px;
}

</style>
<script>
var hplayerUrl='{$url}';
var themeUrl = '{$themeUrl}';
window['LocalConst'] = {
    
    BASE_SCRIPT_URL: themeUrl,
}
</script>

<script src=\"{$PUBLIC_CDN_ARRAY['js']['jquery']}\"></script>
<script src=\"{$themeUrl}assets/js/features/OwO.min.js?v={$versionPrefix}\"></script>
<script src=\"{$themeUrl}assets/js/editor.min.js?v={$versionPrefix}\"></script>


";
    }

}

