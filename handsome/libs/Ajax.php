<?php
/**
 * User: hewro
 * Date: 2018/7/17
 * Time: 20:43
 * 一些ajax请求，能够有效的提升用户体验
 */
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if(@$_POST['action'] == 'send_talk'){//从微信公众号发送说说说
        //获取必要的参数
        if (!empty($_POST['content']) && !empty($_POST['time_code']) && !empty($_POST['cid']) && !empty($_POST['token'])){
            $cid = $_POST['cid'];
            $content=$_POST['content']; //发送的内容
            $time_code= $_POST['time_code'];//用来检验是否是博客主人
            $token= $_POST['token'];//用来检验是否是博客主人
            $msg_type = $_POST['msg_type'];
            $options = mget();

            //身份验证
            if ($time_code == md5($options->time_code)){//验证成功

                if ($msg_type == "image"){//上传图片
                    $mediaId = $_POST['mediaId'];
                    //这里的content是url地址
                    $url = uploadPic($options->rootUrl,$mediaId,$content);
                    //$url = "";
                    $content = '<img src="'.$url.'"/>';
                }

                //向数据库添加说说记录
                $db = Typecho_Db::get();
                //先找到作者信息
                $getAdminSql = $db->select()->from('table.users')
                    ->limit(1);
                $user = $db->fetchRow($getAdminSql);

                $insert = $db->insert('table.comments')
                    ->rows(array("cid" => $cid,"created" => time(),"author" => $user['screenName'],"authorId" =>
                        $user['uid'],"ownerId" => $user['uid'],"text"=> $content,"url" => $user['url'],"mail" =>
                        $user['mail'],"agent"=>"weChat"));
                //将构建好的sql执行, 如果你的主键id是自增型的还会返回insert id
                $insertId = $db->query($insert);
                echo "1";
            }else{
                echo $time_code . "|" . md5($options->time_code);
                echo "-3";//身份验证失败
            }

        }else{
            echo "-2";//信息缺失
        }
        die();
    }
}else if ($_SERVER["REQUEST_METHOD"] == "GET"){
    if(@$_GET['action'] == 'ajax_avatar_get') {
        $email = strtolower( $_GET['email']);
        echo Utils::getAvator($email,65);
        die();
    }elseif(@$_GET['action'] == 'send_talk'){
        echo "非法get请求";
        die();
    }else if (@$_GET['action'] == 'star_talk'){
        if (!empty($_GET['coid'])){
            $coid = $_GET['coid'];
            $db = Typecho_Db::get();

            $stars = Typecho_Cookie::get('extend_say_stars');
            if(empty($stars)){
                $stars = array();
            }else{
                $stars = explode(',', $stars);
            }
            $row = $db->fetchRow($db->select('stars')->from('table.comments')->where('coid = ?',$coid));

            if(!in_array($coid,$stars)){//如果cookie不存在才会加1
                $db->query($db->update('table.comments')->rows(array('stars' => (int) $row['stars'] + 1))->where('coid = ?', $coid));
                array_push($stars, $coid);
                $stars = implode(',', $stars);
                Typecho_Cookie::set('extend_say_stars', $stars); //记录查看cookie
                echo 1;//点赞成功
            }else{
                echo 2;//已经点赞过了
            }
        }else{
            echo -1;//信息缺失
        }

        die();
    }
    else if(@$_GET['action'] == 'open_world'){
        if (!empty($_GET['password'])){
            $password = $_GET['password'];
            $options = mget();
            if ($password == $options->open_new_world){
                echo 1;//密码正确
                Typecho_Cookie::set('open_new_world', $password); //保存密码的cookie，以便后面可以直接访问
            }else{
                echo -1;//密码错误
            }
        }else{
            echo -2;//信息不完成
        }

        die();

    }
    else if (@$_GET['action'] == 'back_up' || @$_GET['action'] == 'un_back_up' || @$_GET['action'] == 'recover_back_up'){//备份管理

        $action = $_GET['action'];
        $db = Typecho_Db::get();

        $themeName = $db->fetchRow($db->select()->from ('table.options')->where ('name = ?', 'theme'));
        $handsomeThemeName = "theme:".$themeName['value'];
        $handsomeThemeBackupName = "theme:HandsomePro-X-Backup";


        if ($action == "back_up"){//备份数据
            $handsomeInfo=$db->fetchRow($db->select()->from ('table.options')->where ('name = ?', $handsomeThemeName));
            $handsomeValue = $handsomeInfo['value'];

            if($db->fetchRow($db->select()->from ('table.options')->where ('name = ?', $handsomeThemeBackupName))) {
                $update = $db->update('table.options')->rows(array('value' => $handsomeValue))->where('name = ?', 'theme:HandSomeBackUp');
                $updateRows = $db->query($update);
                echo 1;
            }else{
                $insert = $db->insert('table.options')
                    ->rows(array('name' => $handsomeThemeBackupName,'user' => '0','value' => $handsomeValue));
                $db->query($insert);
                echo 2;
            }
        }else if ($action == "un_back_up"){//删除备份
            $db = Typecho_Db::get();
            if($db->fetchRow($db->select()->from ('table.options')->where ('name = ?', $handsomeThemeBackupName))){
                $delete = $db->delete('table.options')->where ('name = ?', $handsomeThemeBackupName);
                $deletedRows = $db->query($delete);
                echo 1;
            }else{
                echo -1;//备份不存在
            }
        }else if ($action == "recover_back_up"){//恢复备份
            $db = Typecho_Db::get();
            if($db->fetchRow($db->select()->from ('table.options')->where ('name = ?', $handsomeThemeBackupName))){
                $themeInfo = $db->fetchRow($db->select()->from ('table.options')->where ('name = ?',
                    $handsomeThemeBackupName));
                $themeValue = $themeInfo['value'];
                $update = $db->update('table.options')->rows(array('value'=>$themeValue))->where('name = ?', $handsomeThemeName);
                $updateRows= $db->query($update);
                echo 1;
            }else{
                echo -1;//没有备份数据
            }
        }

        die();
    }
    else {
        $options = mget();
        //如果路径包含后台管理路径，则不加密
        $password = Typecho_Cookie::get('open_new_world');
        $cookie = false;//true为可以直接进入
        if (!empty($password) && $password == trim($options->open_new_world)){
            $cookie = true;
        }
        if (!$cookie && trim($options->open_new_world) != "" && !strpos($_SERVER["SCRIPT_NAME"],
                __TYPECHO_ADMIN_DIR__)){
            require_once ('lock.php');
            die();
        }else{
            return;
        }
    }
}

/**
 * @param $blogUrl
 * @param $name
 * @param $picUrl
 * @return string
 */
function uploadPic($blogUrl,$name,$picUrl){
    $childDir = DIRECTORY_SEPARATOR.'usr'.DIRECTORY_SEPARATOR.'uploads' . DIRECTORY_SEPARATOR .'time' .DIRECTORY_SEPARATOR;
    $dir = __TYPECHO_ROOT_DIR__ . $childDir;
    if (!file_exists($dir)){
        mkdir($dir, 0777, true);
    }
    $fileName = $name. ".jpg";
    $file = $dir .$fileName;

    //开始捕捉
    ob_start();
    readfile($picUrl);
    $img = ob_get_contents();
    ob_end_clean();
    $fp2 = fopen($file , "a");
    fwrite($fp2, $img);
    fclose($fp2);

    return $blogUrl.$childDir.$fileName;
}
