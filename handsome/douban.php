<?php
/**
 * 豆瓣清单
 *
 * @package custom
 */
?>
<?php if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
?>
<?php $this->need('component/header.php');?>

<!-- aside -->
<?php $this->need('component/aside.php');?>
<!-- / aside -->

<?php
require 'libs/ParserDom.php';

//获取书籍清单数据

function getBookData($userID){
    $url = "https://api.douban.com/v2/book/user/$userID/collections?count=100"; //最多取100条数据
    $res=json_decode(curl_get_contents($url),true); //读取api得到json
    $res = $res['collections'];
    if ($res == null || $res ==""){
        echo '<script>$(function(){$(".douban_book_tips").text("获取书籍数据失败，可能原因是：1. 豆瓣API发生故障 2. 豆瓣id配置错误")})</script>';
        return [];
    }
    foreach($res as $v){
        //已经读过的书
        if($v['status']=="read"){
            $book_name=$v['book']['title'];
            $book_img = $v['book']['images']['medium'];
            $book_img = str_replace("/view/subject/m/public/","/lpic/",$book_img);
            $book_url = $v['book']['alt'];
            $readlist[] = array("name"=>$book_name,"img"=>$book_img,"url"=>$book_url);
        }
    }

    return $readlist;
}

//获取电影清单数据
function getMovieData($userID){

    $movieList = [];
    $filePath = __DIR__.'/assets/json/movie.json';

    $fp = fopen($filePath, 'r');
    if ($fp) {
        $contents = fread($fp, filesize($filePath));
        fclose($fp);
        $data = json_decode($contents);

        if (time() - $data->time > 60 * 60 * 72) {//缓存文件过期
            $movieList = updateData($userID,$filePath);
        }else{
            $lastUpdateTime = date('Y-m-d', $data->time); //H 24小时制 2017-08-08 23:00:01
            if ($data->user!=null && $data->user !== $userID){//用户名有修改
                $movieList = updateData($userID,$filePath);
            }else {
                if ($data->data == null || $data->data == ""){//缓存文件中的电影数据为空
                    $movieList = updateData($userID,$filePath);
                }else{//读取缓存文件中的数据
                    $movieList = $data->data;
                    echo '<script>$(function(){$(".douban_tips").text("以下数据最后更新于'.$lastUpdateTime.'")})</script>';
                }
            }
        }
    } else {//目录下无movie.json，此时需要创建文件，并且更新信息
        $movieList = updateData($userID,$filePath);
    }

    return $movieList;
}

function curl_get_contents($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}


function updateData($doubanID = 'ihewro',$filePath)
{
    $url = "https://movie.douban.com/people/$doubanID/collect"; //最多取100条数据
    $p1 = getHTML($url);
    $p1 = getMoviesAndNextPage($p1);
    $movieList = array_merge($p1['data']);
    $num = 0;
    while ($p1['next']!=null && $num <= 3) {
        //echo "下一页地址" . $p1['next'];
        $p1          = getHTML($p1['next']);
        $p1          = getMoviesAndNextPage($p1);
        $movieList = array_merge($movieList, $p1['data']);
        $num ++;

    }
    if ($movieList == null || $movieList == ""){
        echo '<script>$(function(){$(".douban_tips").text("获取电影数据失败，可能原因是：1. ip被豆瓣封锁 2. 豆瓣id配置错误")})</script>';
    }else{
        echo '<script>$(function(){$(".douban_tips").text("添加缓存数据成功，请刷新一次页面查看最新数据")})</script>';
    }
    $data = fopen($filePath, "w");
    fwrite($data, json_encode(['time' => time(), 'user' => $doubanID , 'data' => $movieList]));
    fclose($data);
    return [];
}

function getMoviesAndNextPage($html = '')
{
    if ($html != "" && $html != null){
        $doc       = new \HtmlParser\ParserDom($html);
        $itemArray = $doc->find("div.item");
        foreach ($itemArray as $v) {
            $t = $v->find("li.title", 0);
            $movie_name = trimall($t->getPlainText());
            $movie_img  = $v->find("div.pic a img", 0)->getAttr("src");
            $movie_url  = $t->find("a", 0)->getAttr("href");
            //已经读过的电影
            $movieList[] = array("name" => $movie_name, "img" => $movie_img, "url" => $movie_url);
        }

        $t = $doc->find("span.next a", 0);
        if ($t) {
            $t = "https://movie.douban.com" .$t->getAttr("href");
        }else{
            $t = null;
        }
        return ['data' => $movieList, 'next' =>  $t];
    }else{
        return ['data' => [], 'next' => null];
    }


}

function getHTML($url = '')
{
    $ch = curl_init();
    $cookie= 'bid=gnHfdKIXco; ll="108238"; gr_user_id=3a665fce-2a1c-44c7-8e18-531ea651fe6f; _ga=GA1.2.50077311.1505560162; ap=1; ue="ihewro@qq.com"; push_noty_num=0; push_doumail_num=0; _vwo_uuid_v2=0FFDB41687CE3A48C32CB74C8C415F7C|572ba7f63d2101533eb21c21814fd0f5; __utmv=30149280.13002; viewed="24251326"; __utmc=30149280; __utmc=223695111; ps=y; dbcl2="130023498:EFP11hbTQ+w"; ck=v1u1; _pk_ref.100001.4cf6=%5B%22%22%2C%22%22%2C1518071897%2C%22http%3A%2F%2Flocalhost%2Fbuild%2F35.html%22%5D; _pk_ses.100001.4cf6=*; ct=y; __utma=30149280.50077311.1505560162.1518071897.1518074552.44; __utmb=30149280.0.10.1518074552; __utmz=30149280.1518074552.44.24.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided); __utma=223695111.192490176.1505819273.1518071897.1518074552.23; __utmz=223695111.1518074552.23.11.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided); _pk_id.100001.4cf6=f4672bae215755ca.1505819273.22.1518074849.1518066090.; __utmb=223695111.4.10.1518074552';

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_COOKIE, $cookie);

    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36');

    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function trimall($str)
{
    $qian = array(" ", "　", "\t", "\n", "\r");
    $hou  = array("", "", "", "", "");
    return str_replace($qian, $hou, $str);
}

?>
<!-- <div id="content" class="app-content"> -->
<a class="off-screen-toggle hide"></a>
<main class="app-content-body" <?php echo Content::returnPageAnimateClass($this); ?>>
    <div class="hbox hbox-auto-xs hbox-auto-sm">
        <!--文章-->
        <div class="col">
            <!--标题下的一排功能信息图标：作者/时间/浏览次数/评论数/分类-->
            <?php echo Content::exportPostPageHeader($this, $this->user->hasLogin()); ?>
            <div class="wrapper-md" id="post-panel">
                <?php Content::BreadcrumbNavigation($this, $this->options->rootUrl);?>
                <!--博客文章样式 begin with .blog-post-->
                <div id="postpage" class="blog-post">
                    <article class="panel">
                        <!--文章页面的头图-->
                        <?php echo Content::exportHeaderImg($this); ?>
                        <!--文章内容-->
                        <div id="post-content" class="wrapper-lg">
                            <div class="entry-content l-h-2x">
                                <div class="booklist">

                                    <h2>我的书单</h2>
                                    <small class="text-muted letterspacing douban_book_tips">以下数据为实时从豆瓣API读取</small>
                                    <div class="section">
                                        <div class="row">
                                            <?php
                                            $readList = getBookData($this->fields->doubanID);
                                            foreach($readList as $v):?>
                                                <div class="col-sm-3">
                                                    <div class="panel panel-default">
                                                        <div class="panel-body">
                                                            <img src="https://img3.doubanio.com/view/photo/s_ratio_poster/public/p2530572643.jpg" class="img-full">

                                                        </div>
                                                    </div>
                                                </div>
                                                <!--<li>
                                                    <div class="photo"><img src="<?php /*echo $v['img'];*/?>" width="98" height="151" /></div>
                                                    <div class="rsp"></div>
                                                    <div class="text"><a href="<?php /*echo $v['url'];*/?>" target="_blank"><h3><?php /*echo $v['name'];*/?></h3></a></div>
                                                </li>-->
                                            <?php endforeach; ?>
                                        </div>
                                    </div>


                                    <h2>我的观影</h2>
                                    <small class="text-muted letterspacing douban_tips"></small>
                                    <div class="section">
                                        <ul class="clearfix">
                                            <?php
                                            $movieList = getMovieData($this->fields->doubanID);
                                            foreach ($movieList as $v): ?>
                                                <li>
                                                    <div class="photo"><img src="<?php echo $v->img; ?>" width="98" height="151" /></div>
                                                    <div class="rsp"></div>
                                                    <div class="text"><a href="<?php echo $v->url; ?>" target="_blank"><h3><?php echo $v->name; ?></h3></a></div>
                                                </li>
                                            <?php endforeach;?>
                                        </ul>
                                    </div>

                                </div>


                                <?php Content::postContent($this, $this->user->hasLogin());?>
                            </div>
                        </div>
                    </article>
                </div>
                <!--评论-->
                <?php $this->need('component/comments.php')?>
            </div>
        </div>
        <!--文章右侧边栏开始-->
        <?php $this->need('component/sidebar.php');?>
        <!--文章右侧边栏结束-->
    </div>
</main>
<script type="text/javascript">
    $(document).ready(function(){
        $(".booklist .section ul li .rsp").hide();
        $(".booklist .section    ul li").hover(function(){
                $(this).find(".rsp").stop().fadeTo(500,0.5)
                $(this).find(".text").stop().animate({left:'0'}, {duration: 500})
            },
            function(){
                $(this).find(".rsp").stop().fadeTo(500,0)
                $(this).find(".text").stop().animate({left:'30'}, {duration: "fast"})
                $(this).find(".text").animate({left:'-300'}, {duration: 0})
            });
    });
</script>

<!-- footer -->
<?php $this->need('component/footer.php');?>
<!-- / footer -->