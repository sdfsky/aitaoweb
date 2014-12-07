<?php

!defined('IN_TIPASK') && exit('Access Denied');

class cashinmodel {

    var $db;
    var $base;

    function cashinmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get($cashid) {
        return $this->db->fetch_first("SELECT * FROM " . DB_TABLEPRE . "cashin WHERE `id`=$cashid");
    }

    function add($uid, $username, $money, $credit2) {
        $this->db->query("INSERT INTO " . DB_TABLEPRE . "cashin(`id`,uid,username,money,credit2,create_time,update_time,status) VALUES (null,$uid,'$username',$money,$credit2,{$this->base->time},{$this->base->time},1)");
        return $this->db->insert_id();
    }

    function get_by_uid($uid, $start = 0, $limit = 20) {
        $cashinlist = array();
        $query = $this->db->query("SELECT * FROM " . DB_TABLEPRE . "cashin WHERE uid=$uid ORDER BY create_time DESC LIMIT $start,$limit");
        while ($cashin = $this->db->fetch_array($query)) {
            $cashin['fctime'] = tdate($cashin['create_time']);
            $cashin['futime'] = tdate($cashin['update_time']);
            $cashinlist[] = $cashin;
        }
        return $cashinlist;
    }

    function search($username = '', $status = 0, $start = 0, $limit = 50) {
        echo "adsfasf";
        $cashinlist = array();
        $sql = "SELECT * FROM " . DB_TABLEPRE . "cashin WHERE status <> -1";
        $username && $sql.=" AND username LIKE '$username%'";
        $status && $sql.=" AND status=$status";
        $sql.=" ORDER BY create_time DESC LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($cashin = $this->db->fetch_array($query)) {
            $cashin['format_ctime'] = tdate($cashin['create_time']);
            $cashin['format_utime'] = tdate($cashin['update_time']);
            $cashinlist[] = $cashin;
        }
        return $cashinlist;
    }

    function change_status($cashid, $status) {
        $this->db->query("UPDATE " . DB_TABLEPRE . "cashin SET status=$status,`admin`='{$this->base->user['username']}',update_time={$this->base->time} WHERE `id`=$cashid");
    }

}
