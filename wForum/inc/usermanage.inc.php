<?php
function showUserManageMenu(){
?>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr>
<th width=14% height=25 id=TableTitleLink><a href=usermanagemenu.php>���������ҳ</a></th>
<th width=14%  id=TableTitleLink><a href=modifyuserdata.php>���������޸�</a></th>
<th width=14%  id=TableTitleLink><a href=changepasswd.php>�û������޸�</a></th>
<th width=14%  id=TableTitleLink><a href=userparam.php>�û��Զ������</a></th>
<th width=14%  id=TableTitleLink><a href=usermailbox.php>�û��ż�����</a></th>
<th width=14%  id=TableTitleLink><a href=friendlist.php>�༭�����б�</a></th>
<th width=14%  id=TableTitleLink><a href=modifyfavboards.php>�ղذ������</a></th>
</tr>
</table>
<?php
}

function showmailBoxes() {
?>
<TABLE cellpadding=6 cellspacing=1 align=center class=TableBorder1><TBODY><TR>
<TD align=center class=TableBody1><a href="usermailbox.php?boxname=inbox"><img src=pic/m_inbox.gif border=0 alt=�ռ���></a> &nbsp; <a href="usermailbox.php?boxname=sendbox"><img src=pic/m_outbox.gif border=0 alt=������></a> &nbsp; <a href="usermailbox.php?boxname=deleted"><img src=pic/m_recycle.gif border=0 alt=�ϼ���></a>&nbsp; <a href="friendlist.php"><img src=pic/m_address.gif border=0 alt=��ַ��></a>&nbsp;<a href="sendmail.php"><img src=pic/m_write.gif border=0 alt=������Ϣ></a></td></tr></TBODY></TABLE>
<?php
}

function getMailBoxName($name){
	if (getMailBoxPathDesc($name, $path, $desc)) return $desc;
	return "δ֪����";
}

function getMailBoxPath($name) {
	if (getMailBoxPathDesc($name, $path, $desc)) return $path;
	return false;
}

/*
 * return true if $name is a valid mailbox name
 * $name: the mailbox name used in the URL
 * $path: return the mailbox index file name
 * $desc: return the description of the mailbox
 */
function getMailBoxPathDesc($name, &$path, &$desc) { //��ʵ���Ǻܳ�ª��������д����... - atppp
	if ($name=='inbox') {
		$path = ".DIR";
		$desc = "�ռ���";
		return true;
	}
	if ($name=='sendbox') {
		$path = ".SENT";
		$desc = "������";
		return true;
	}
	if ($name=='deleted') {
		$path = ".DELETED";
		$desc = "�ϼ���";
		return true;
	}
	return false;
}
?>
