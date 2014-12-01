<?php

!defined('IN_TIPASK') && exit('Access Denied');

class expertcontrol extends base {

    function expertcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load("expert");
        $this->load("message");
        $this->load("user");
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

    function oncomment() {
        $userid = intval($this->get[2]);
        $member = $_ENV['user']->get_by_uid($userid, 2);
        $levelcount = $_ENV['expert']->get_level_count($userid);
        $commentlist = $_ENV['expert']->get_comment_list($userid);
        $questionlist = $_ENV['expert']->get_solve_answer($userid, 0, 8);
        include template("expert_comment");
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

    /**
     * 添加评分
     */
    function onajaxaddcomment() {
        if ($this->user['uid'] && $this->post) {
            $expert_uid = $this->post['expert_uid'];
            $level = $this->post['goodlevel'];
            $comment = $this->post['comment'];
            $_ENV['expert']->add_comment($expert_uid, $this->user['username'], $this->user['uid'], $level, $comment);
        }
        echo "ok";
    }

    function oncashin() {
        $this->load("cashin");
        if (isset($this->post['submit'])) {
            $money = intval($this->post['money']);
            if ($money < 100) {
                $this->message("输入提现金额不正确!提现金额最低100元，且单次提现不超过20000元!", 'BACK');
                exit;
            }
            if ($this->user['credit2'] < $money) {
                $this->message("抱歉您的健康币不足，不能完成提现!", 'BACK');
                exit;
            }
            $uid = intval($this->user['uid']);
            $cashid = $_ENV['cashin']->add($uid, $this->user['username'], $money, $this->user['credit2']);
            if ($cashid)
                $this->credit($uid, 0, -$money);

            $this->message("提现申请申请成功，请耐心等待我们的审核，审核期间如有问题请直接联系在线客服！", 'BACK');
        }
        include template("cashin");
    }

}
