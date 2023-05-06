
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