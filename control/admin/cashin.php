<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
!defined('IN_TIPASK') && exit('Access Denied');

class admin_cashincontrol extends base {

    function admin_cashincontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('cashin');
    }

    function ondefault($msg = '') {
        $username = $status = '';
        if ($this->post['submit']) {
            $username = $this->post['username'];
            $status = $this->post['status'];
        }
        $cashinlist = $_ENV['cashin']->search($username, $status);
        $msg && $message = $msg;
        include template("cashin", "admin");
    }

    function onchange() {
        $status = intval($this->get[2]);
        $cashid = intval($this->get[3]);
        $cashin = $_ENV['cashin']->get($cashid);
        if(!$cashin){
            $this->ondefault("申请记录不存在，请确认！");
            exit;
        }
        if ($status == 2) {//通过
            $_ENV['cashin']->change_status($cashid, $status);
        } else if ($status == 3 && $cashin['status']!=3) {//拒绝
            $this->credit($cashin['uid'], 0, $cashin['money']);
            $_ENV['cashin']->change_status($cashid, $status);
        }
        $this->ondefault("状态操作成功！");
    }

}
