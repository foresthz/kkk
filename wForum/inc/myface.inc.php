<?php
	/* �Զ���ͷ����غ��� */

	/*
	 * ����ļ�ϵͳ�ϵ��ļ�ȫ·����������� PHP ҳ�涼���� wForum ��Ŀ¼���Ժ�ĳɸ��õİ취�ɡ�
	 */
	$topdir = dirname($_SERVER['SCRIPT_FILENAME']) . "/";
	function get_myface_fs_filename($filename) {
		global $topdir;
		return $topdir.$filename;
	}
	
	function get_myface_dir($userid) {
		return MYFACEDIR . strtoupper(substr($userid,0,1));
	}
	
	/*
	 * �����������Ϊ���������������ˢ���ϴ���ͷ��
	 * ���ﻹ�õ��˵�ǰ�û���Ϊ����ȷ���޸ĵ�ʱ���ܹ����ϴ��������ļ���ɾ����ȱ�������û�ע���ʱ��Ͳ����ϴ�ͷ���� - atppp
	 */
	function get_myface_filename($userid, $ext) {
		mt_srand();
		$d = get_myface_dir($userid);
		$fd = get_myface_fs_filename($d);
		if (is_file($fd)) die("�ⲻ���ܣ��ⲻ���ܣ�");
		if (!is_dir($fd)) @mkdir($fd);
		return $d."/".$userid.".".mt_rand(0,10000).$ext;
	}
	
	function get_myface($user, $extra_tag = false) {
		if ($user['userface_img'] == -1) {
			$user_pic = htmlEncode($user['userface_url']);
			$has_size = true;
		} else {
			$user_pic = 'userface/image'.$user['userface_img'].'.gif';
			$has_size = false;
		}
		$str = "<img src=\"".$user_pic."\"";
		if ($has_size) $str .= " width=\"".$user['userface_width']."\" height=\"".$user['userface_height']."\"";
		if ($extra_tag !== false) $str .= " ".$extra_tag;
		return $str."/>";
	}
?>