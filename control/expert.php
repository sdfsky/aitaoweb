<?php

!defined('IN_TIPASK') && exit('Access Denied');

class expertcontrol extends base {

    function expertcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load("expert");
        $this->load("message");
    }

    /* 添加举报 */

    function ondefault() {
        $navtitle = "问题专家";
        $categoryid = intval($this->get[2]) ? intval($this->get[2]) : 'all';
        $page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $rownum = $_ENV['expert']->rownum_by_cid($categoryid);
        $expertlist = $_ENV['expert']->get_by_cid($categoryid, $startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "expert/default/$categoryid");
        $questioned_expertlist = $_ENV['expert']->get_questioned_expert($this->user['uid']);
        $questionlist = $_ENV['expert']->get_solves(0, 15);
        include template('expert');
    }

    function onadvisory() {
        $navtitle = "在线咨询";
        $myauth = $_ENV['user']->get_auth($this->user['uid']);
        $messagelist = $_ENV['message']->list_by_fromuid($fromuid, $startindex, $pagesize);
        include template("expert_advisory");
    }

    /**
     * 保存专家信息
     */
    function onajaxsaveinfo() {
        $online_status = intval($this->post['online_status']);
        $support_register = intval($this->post['support_register']);
        $price = intval($this->post['price']);
        $this->db->query("UPDATE " . DB_TABLEPRE . "user SET online_status=$online_status,support_register=$support_register,price=$price WHERE uid=" . $this->user['uid']);
        echo 'ok';
    }

}

?>