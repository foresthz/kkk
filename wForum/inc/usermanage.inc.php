<?php
function showUserManageMenu(){
?>
<table cellpadding=3 cellspacing=1 align=center class=tableborder1>
<tr>
<th width=14% height=25 id=tabletitlelink><a href=usermanager.php>���������ҳ</a></th>
<th width=14%  id=tabletitlelink><a href=mymodify.php>���������޸�</a></th>
<th width=14%  id=tabletitlelink><a href=modifypsw.php>�û������޸�</a></th>
<th width=14%  id=tabletitlelink><a href=modifyadd.php>��ϵ�����޸�</a></th>
<th width=14%  id=tabletitlelink><a href=usersms.php>�û����ŷ���</a></th>
<th width=14%  id=tabletitlelink><a href=friendlist.php>�༭�����б�</a></th>
<th width=14%  id=tabletitlelink><a href=favlist.php>�û��ղع���</a></th>
</tr>
</table>
<?php
}

function showmailBoxes() {
?>
<TABLE cellpadding=6 cellspacing=1 align=center class=tableborder1><TBODY><TR>
<TD align=center class=tablebody1><a href="usermailbox.php?boxname=inbox"><img src=pic/m_inbox.gif border=0 alt=�ռ���></a> &nbsp; <a href="usermailbox.php?boxname=sendbox"><img src=pic/m_outbox.gif border=0 alt=������></a> &nbsp; <a href="usermailbox.php?boxname=deleted"><img src=pic/m_recycle.gif border=0 alt=�ϼ���></a>&nbsp; <a href="friendlist.php"><img src=pic/m_address.gif border=0 alt=��ַ��></a>&nbsp;<a href="sendmail.php"><img src=pic/m_write.gif border=0 alt=������Ϣ></a></td></tr></TBODY></TABLE>
<?php
}

function getMailBoxName($name){
	if ($name=='inbox') {
		return "�ռ���";
	}
	if ($name=='sendbox') {
		return "�ռ���";
	}
	if ($name=='deleted') {
		return "�ռ���";
	}
	return "δ֪����";
}
?>