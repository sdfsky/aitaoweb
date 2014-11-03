<?php

!defined('IN_TIPASK') && exit('Access Denied');

class expertcontrol extends base {

    function expertcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load("expert");
    }

    /* 添加举报 */

    function ondefault() {
        $navtitle = "问题专家";
        $categoryid = intval($this->get[2])?intval($this->get[2]):'all';
        $page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $rownum =  $_ENV['expert']->rownum_by_cid($categoryid);
        $expertlist = $_ENV['expert']->get_by_cid($categoryid, $startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "expert/default/$categoryid");
        $questionlist = $_ENV['expert']->get_solves(0, 15);
        include template('expert');
    }
    
    function onadvisory(){
        $navtitle = "在线咨询";
        $myauth = $_ENV['user']->get_auth($this->user['uid']);
        include template("expert_advisory");
    }

}

?>