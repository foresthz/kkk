<?php
	// this script deals with inter-site post cross.
	require("www2-funcs.php");
	require("www2-board.php");

	if($_SERVER["REMOTE_ADDR"] != "127.0.0.1")
		exit;

	$rpid = $_POST["rpid"];
	if($rpid == "")
		exit;

    // check user exists
	$userid = $_POST["user"];
	$uarr = array();
	if(($userid == "") || (bbs_getuser($userid, $uarr) == 0)) {
		print("�û� {$userid} �����ڡ�");
		exit;
	}
	$uid = $uarr["index"];

	// check board exists
	$bname = $_POST["board"];
	$barr = array();
	$bid = bbs_getboard($bname, $barr);
	if($bid == 0) {
		print("���� {$bname} �����ڡ�");
		exit;
	}
	$bname = $barr["NAME"];

	// check if can post
	if(bbs_checkreadperm($uid, $bid) == 0) {
		print("û���Ķ�Ȩ�ޡ�");
		exit;
	}
	if(bbs_is_readonly_board($barr)) {
		print("����Ϊֻ��״̬��");
		exit;
	}
	if(bbs_checkpostperm($uid, $bid) == 0) {
		print("û�з���Ȩ�ޡ�");
		exit;
	}

	// check from
	$fromsite = $_POST["site"];
	$fromboard = $_POST["fromboard"];

	// check title and content
	$title = $_POST["title"];
	if($title == "") {
		print("û�����±��⡣");
		exit;
	}
	$content = $_POST["content"];
	if($content == "") {
		print("û����������");
		exit;
	}

	// write content into file
	$fname = "tmp/remotepost_{$rpid}";
	$fp = fopen($fname, "w");
	if(!$fp) {
		print("�޷�д���ļ���");
		exit;
	}
	fwrite($fp, "�� ��������ת���� {$fromsite} �� {$fromboard} ������ ��\n");
	fwrite($fp, $content);
	fclose($fp);
	$ret = bbs_post_file_alt($fname, $userid, $title, $bname, NULL, 0x04, 0, 0);
	unlink($fname);
	if($ret == 0) {
		print("ת�سɹ���");
		exit;
	}
	else {
		print("ת��ʧ�ܣ������{$ret}��");
		exit;
	}

?>
