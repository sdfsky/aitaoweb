<?php

!defined('IN_TIPASK') && exit('Access Denied');

class expertmodel {

    var $db;
    var $base;

    function expertmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_list($showquestion = 0, $start = 0, $limit = 3) {
        $expertlist = array();
        $query = $this->db->query("SELECT *  FROM " . DB_TABLEPRE . "user WHERE expert=1 ORDER BY credit1 DESC,answers DESC LIMIT $start ,$limit");
        while ($expert = $this->db->fetch_array($query)) {
            $expert['avatar'] = get_avatar_dir($expert['uid']);
            $expert['lastlogin'] = tdate($expert['lastlogin']);
            $expert['category'] = $this->get_category($expert['uid']);
            $showquestion && $expert['bestanswer'] = $this->get_solve_answer($expert['uid'], 0, 6);
            $expertlist[] = $expert;
        }
        return $expertlist;
    }

    function get_by_uid($uid) {
        return $this->db->fetch_array($this->db->query("SELECT * FROM " . DB_TABLEPRE . "expert WHERE `uid`=$uid"));
    }

    function get_by_username($username) {
        return $this->db->fetch_array($this->db->query("SELECT * FROM " . DB_TABLEPRE . "expert WHERE `username`='$username'"));
    }

    function get_by_cid($cid, $start = 0, $limit = 10) {
        $expertlist = array();
        $query = ($cid == 'all') ? $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user WHERE expert=1 ORDER BY answers DESC LIMIT $start,$limit") : $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user WHERE expert=1 AND uid IN (SELECT uid FROM " . DB_TABLEPRE . "user_category WHERE cid=$cid) ORDER BY answers DESC  LIMIT $start,$limit");
        while ($expert = $this->db->fetch_array($query)) {
            $expert['avatar'] = get_avatar_dir($expert['uid']);
            $expert['category'] = $this->get_category($expert['uid']);
            $expert['authinfo'] = $this->get_auth($expert['uid']);
            $expertlist[] = $expert;
        }
        return $expertlist;
    }

    function rownum_by_cid($cid) {
        $sql = ($cid == 'all') ? "SELECT COUNT(*) FROM " . DB_TABLEPRE . "user WHERE uid IN (SELECT uid FROM " . DB_TABLEPRE . "expert) " : "SELECT COUNT(*) FROM " . DB_TABLEPRE . "user WHERE uid IN (SELECT uid FROM " . DB_TABLEPRE . "expert WHERE cid=$cid)";
        return $this->db->result_first($sql);
    }

    function add($uid, $cids) {
        $sql = "INSERT INTO " . DB_TABLEPRE . "user_category(`uid`,`cid`) VALUES ";
        foreach ($cids as $cid) {
            $sql .= "($uid,$cid),";
        }
        $this->db->query(substr($sql, 0, -1));
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET `expert`=1 WHERE uid=$uid");
    }

    function remove($uids) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET `expert`=0 WHERE uid IN ($uids)");
        $this->db->query("DELETE FROM " . DB_TABLEPRE . "user_category WHERE uid IN ($uids)");
    }

    function get_category($uid) {
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "user_category WHERE uid=$uid");
        $categorylist = array();
        while ($category = $this->db->fetch_array($query)) {
            $category['categoryname'] = $this->base->category[$category['cid']]['name'];
            $categorylist[] = $category;
        }
        return $categorylist;
    }

    function get_solves($start = 0, $limit = 20) {
        $solvelist = array();
        $query = $this->db->query("SELECT a.qid,a.time,a.title,a.author,a.authorid,f.uid FROM " . DB_TABLEPRE . "answer  as a ,`" . DB_TABLEPRE . "expert` as f WHERE a.authorid=f.uid ORDER BY a.time DESC LIMIT $start ,$limit");
        while ($solve = $this->db->fetch_array($query)) {
            $solve['avatar'] = get_avatar_dir($solve['uid']);
            $solve['format_time'] = tdate($solve['time']);
            $solvelist[] = $solve;
        }
        return $solvelist;
    }

    function get_solve_answer($uid, $start = 0, $limit = 3) {
        $solvelist = array();
        $query = $this->db->query("SELECT * FROM `" . DB_TABLEPRE . "answer` WHERE `authorid`=" . $uid . "  ORDER BY `adopttime` DESC,`supports` DESC LIMIT $start,$limit");
        while ($solve = $this->db->fetch_array($query)) {
            $solvelist[] = $solve;
        }
        return $solvelist;
    }

    function get_auth($uid) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "user_auth WHERE uid=$uid");
    }

    /* 查询我咨询过的医生 */

    function get_questioned_expert($uid) {
        $expertlist = array();
        $query = $this->db->query("SELECT u.username,m.time FROM " . DB_TABLEPRE . "user as u," . DB_TABLEPRE . "message as m WHERE u.uid=m.touid AND m.fromuid=$uid GROUP BY u.username ORDER BY m.time DESC LIMIT 0,5");
        while ($expert = $this->db->fetch_array($query)) {
            $expert['format_time'] = tdate($expert['time'], 2);
            $expertlist[] = $expert;
        }
        return $expertlist;
    }

    /**
     * 添加专家评论
     * @param Int $expert_uid
     * @param String $author
     * @param Int $authorid
     * @param Int $level
     * @param String $content
     */
    function add_comment($expert_uid, $author, $authorid, $level, $content) {
        $level_field = "level" . $level;
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET $level_field=$level_field+1 WHERE uid=$expert_uid");
        return $this->db->query("INSERT INTO " . DB_TABLEPRE . "expert_comment(`id`,expert_uid,author,authorid,level,content,create_time) VALUES (null,$expert_uid,'$author',$authorid,$level,'$content',{$this->base->time})");
    }

    /* 统计专家评分 */

    function get_level_count($expert_uid) {
        $levelcount = array();

        $query = $this->db->query("SELECT COUNT(*) AS num,level FROM " . DB_TABLEPRE . "expert_comment WHERE expert_uid=$expert_uid GROUP BY level");
        $total = 0;
        while ($count = $this->db->fetch_array($query)) {
            $levelcount[$count['level']] = $count['num'];
            $total+=$count['num'];
        }
        if ($total) {
            $levelcount['vgood'] = round($levelcount[1] / $total, 4) * 100;
            $levelcount['good'] = round($levelcount[2] / $total, 4) * 100;
            $levelcount['bad'] = round($levelcount[3] / $total, 4) * 100;
        } else {
            $levelcount['vgood'] = $levelcount['good'] = $levelcount['bad'] = 100;
        }

        return $levelcount;
    }
    
    /**
     * 获取专家所有评论
     * @param type $expert_uid
     * @return type
     */
    function get_comment_list($expert_uid){
        $commentlist = array();
        $query = $this->db->query("SELECT * FROM ".DB_TABLEPRE."expert_comment WHERE expert_uid=$expert_uid LIMIT 0,100");
        while($comment = $this->db->fetch_array($query)){
            $comment['format_time'] = tdate($comment['create_time']);
            $commentlist[] = $comment;
        }
        return $commentlist;
    }
    
    
    

}

