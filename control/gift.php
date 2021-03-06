<?php

!defined('IN_TIPASK') && exit('Access Denied');

class giftcontrol extends base {

    function giftcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('gift');
        $this->load('user');
    }

    function ondefault() {
        $navtitle = "礼品商店";
        @$page = max(1, intval($this->get[2]));
        $pagesize = 12;
        $startindex = ($page - 1) * $pagesize;
        $giftlist = $_ENV['gift']->get_list($startindex, $pagesize);
        $giftnum = $this->db->fetch_total('gift');
        $departstr = page($giftnum, $pagesize, $page, "gift/default");
        $loglist = $_ENV['gift']->getloglist(0, 30);
        include template('giftlist');
    }

    function onsearch() {
        $from = intval($this->get[2]);
        $to = intval($this->get[3]);
        @$page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $giftlist = $_ENV['gift']->get_by_range($from, $to, $startindex, $pagesize);
        $rownum = $this->db->fetch_total('gift', " `credit`>=$from AND `credit`<=$to");
        $departstr = page($rownum, $pagesize, $page, "gift/search/$from/$to");
        $ranglist = unserialize($this->setting['gift_range']);
        include template('giftlist');
    }

    function onajaxgetlist() {
        $page = max(intval($this->get[2]), 3);
        $giftlist = $_ENV['gift']->get_list($page, 3);
        $giftstr = '';
        foreach ($giftlist as $gift) {
            $giftstr .= '<div class="giftinfo clearfix">';
            $giftstr .= '<a class="pic" href="#" target="_blank"><img width="38" height="50" src="' . SITE_URL . '' . $gift['image'] . '"></a>';
            $giftstr .= '<h3>' . $gift['title'] . '</h3>';
            $giftstr .= '<p>价格:' . $gift['credit'] . '健康币</p>';
            $giftstr .= '<div class="handsel"><input type="button" value="赠送" class="button3"/></div>';
            $giftstr .= '<div class="clr"></div>';
            $giftstr .= '</div>';
        }
        exit($giftstr);
    }

    function onadd() {
        if (isset($this->post['realname'])) {
            $realname = $this->post['realname'];
            $email = $this->post['email'];
            $phone = $this->post['phone'];
            $addr = $this->post['addr'];
            $postcode = $this->post['postcode'];
            $qq = $this->post['qq'];
            $notes = $this->post['notes'];
            $gid = $this->post['gid'];
            $param = array();
            if ('' == $realname || '' == $email || '' == $phone || '' == $addr || '' == $postcode) {
                $this->message("为了准确联系到您，真实姓名、邮箱、联系地址（邮编）、电话不能为空！", 'gift/default');
            }

            if (!preg_match("/^[a-z'0-9]+([._-][a-z'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$/", $email)) {
                $this->message("邮件地址不合法!", 'gift/default');
            }

            if (($this->user['email'] != $email) && $this->db->fetch_total('user', " email='$email' ")) {
                $this->message("此邮件地址已经注册!", 'gift/default');
            }

            $gift = $_ENV['gift']->get($gid);
            if ($this->user['credit2'] < $gift['credit']) {
                $this->message("抱歉！您的财富值不足不能兑换该礼品!", 'gift/default');
            }

            $_ENV['user']->update_gift($this->user['uid'], $realname, $email, $phone, $qq);
            $_ENV['gift']->addlog($this->user['uid'], $gid, $this->user['username'], $realname, $this->user['email'], $phone, $addr, $postcode, $gift['title'], $qq, $notes, $gift['credit']);
            $this->credit($this->user['uid'], 0, -$gift['credit']); //扣除财富值
            $this->message("礼品兑换申请已经送出等待管理员审核！", "gift/default");
        }
    }
    
    function onajaxexchange(){
        $giftid = intval($this->post['giftid']);
        $to_uid = intval($this->post['to_uid']);
        $to_user = $_ENV['user']->get_by_uid($to_uid);
        if(!$to_user){
            exit('error');
        }
        $gift = $_ENV['gift']->get($giftid);
        if ($this->user['credit2'] < $gift['credit']) {
            exit('error');
        }
        $this->load("message");
        $description = "恭喜！你刚刚收到 ".$this->user['username']." 赠送的礼物 ".$gift['title'].", 健康币 +".$gift['credit']."< br />";
        $_ENV['message']->add($setting['site_name'].'管理员',0, $to_user['uid'], '您刚刚收到礼物' . $gift['title'], $description);
        $_ENV['gift']->addlog($this->user['uid'],$to_uid,$giftid,$gift['credit']);
        $this->credit($this->user['uid'], 0, -$gift['credit']);
        $this->credit($to_user['uid'], 0, +$gift['credit']);
        $this->db->query("UPDATE ".DB_TABLEPRE."user SET gifts=gifts+1 WHERE uid=".$to_uid);
        exit('ok');
    }

}
