<?php


$needlogin=1;

require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/userdatadefine.inc.php");

preprocess();

setStat("�쿴�û���Ϣ");

show_nav();

if (isErrFounded()) {
	html_error_quit() ;
} else {
	?>
	<br>
	<TABLE cellSpacing=0 cellPadding=0 width=97% border=0 align=center>
	<?php

	if ($loginok==1) {
		showUserMailbox();
?>
</table>
<?php
	}
	head_var();
	showUserData($user,$user_num);
}

//showBoardSampleIcons();
show_footer();

function preprocess() {
	global $user,$user_num;
	$userarray=array();
	if (($user_num=bbs_getuser($_GET['id'],$userarray))==0) {
		foundErr("�����û�����ʧ�ܣ�");
		return false;
	}
	$user=$userarray;
	return true;

}

function showUserData($user, $user_num) {
	print_r($user);
?>
<table width=97% border=0 cellspacing=0 cellpadding=3 align=center>
  <tr> 
    <td><img src="<?php
	if ($user['userface_img']==-2) {
		echo $user['userface_url'];
	} else {
		echo 'userface/image'.$user['userface_img'].'.gif';
	}
?>" width=20 height=21 align=absmiddle> 
<b><?php echo $user['userid']; ?></b> 
</td>
    <td align=right>
��ǰλ�ã�[���������б�]<img src=pic/zhuangtai.gif width=16 height=16 align=absmiddle> 
      ״̬��
����  [���ߣ�0Mins]
  </td>
  </tr>
</table>

<table cellspacing=1 cellpadding=3 align=center  style="table-layout:fixed;word-break:break-all" class=tableborder1>
  <col width=20% ><col width=*><col width=40% > 
  <tr> 
    <th colspan=2 align=left>��������</th>
    <td rowspan=9 align=center class=tablebody1 width=40% valign=top>
<font color=gray>��</font>
    </td>
  </tr>   
  <tr> 
    <td class=tablebody1 width=20% align=right>�� ��</td>
    <td class=tablebody1><?php echo $user['gender']?'��':'Ů'; ?> </td>
  </tr>
  <tr> 
    <td class=tablebody2 width=20% align=right>�� ����</td>
    <td class=tablebody2>
<?php
	if ( ($user['birthyear']!=0) && ($user['birthmonth']!=0) && ($user['birthday']!=0)) {
		echo $user['birthyear'].'��'.$user['birthmonth'].'��'.$user['birthday'].'��';
	} else {
		echo "<font color=gray>δ֪</font>";
	}?>
 </td>
  </tr>
  <tr> 
    <td class=tablebody1 width=20% align=right>�� ����</td>
    <td class=tablebody1>
<?php
	if ( ($user['birthmonth']!=0) && ($user['birthday']!=0)) {
		echo $user['birthyear'].'��'.$user['birthmonth'].'��'.$user['birthday'].'��';
	} else {
		echo "<font color=gray>δ֪</font>";
	}?>
?></td>
  </tr>
  <tr> 
    <td class=tablebody2 width=20% align=right>�ţ���죺</td>
    <td class=tablebody2>
<a href=mailto:eway@aspsky.net>eway@aspsky.net</a>
</td>
  </tr>
  <tr> 
    <td class=tablebody1 width=20% align=right>�� �ѣ�</td>
    <td class=tablebody1>
<font color=gray>δ��</font>
</td>
  </tr>
  <tr> 
    <td class=tablebody2 width=20% align=right>�ɣãѣ�</td>
    <td class=tablebody2>
<font color=gray>δ��</font>
</td>
  </tr>
  <tr> 
    <td class=tablebody1 width=20% align=right>�ͣӣΣ�</td>
    <td class=tablebody1>
<font color=gray>δ��</font>
 </td>
  </tr>
  <tr> 
    <td class=tablebody2 width=20% align=right>�� ҳ��</td>
    <td class=tablebody2>
<font color=gray>δ��</font>
</td>
  </tr>
  <tr> 
    <td class=tablebody1 width=20% align=right valign=top>&nbsp;</td>
    <td class=tablebody1>&nbsp;</td>
    <td class=tablebody1 align=center width=40% >
      <b><a href="javascript:openScript('messanger.asp?action=new&touser=admin',500,400)">��������</a> | <a href="friendlist.asp?action=addF&myFriend=admin" target=_blank>��Ϊ����</a></b></td>
  </tr>
</table>
<br>


<table cellspacing=1 cellpadding=3 align=center class=tableborder1>
  <tr>
    <th align=left colspan=6> ��̳����</th>
  </tr>
  <tr>
    <td class=tablebody1 width=15% align=right>����ֵ��</td>

    <td  width=35%  class=tablebody1><b>94023 </b></td>
    <td width=15% align=right class=tablebody1>�������ӣ�</td>
    <td width=35%  class=tablebody1> <b>0</b>ƪ</td>
  </tr>
  <tr> 
    <td class=tablebody1 width=15% align=right>����ֵ��</td>
    <td  width=35%  class=tablebody1><b>94023 </b></td>
    <td width=15% align=right class=tablebody1>����������</td>
    <td width=35%  class=tablebody1><b>47</b> ƪ</td>
  </tr>
  <tr> 
    <td class=tablebody1 width=15% align=right>��̳�ȼ���</td>
    <td  width=35%  class=tablebody1><b>����Ա </b></td>
    <td width=15% align=right class=tablebody1>��ɾ���⣺</td>
    <td width=35%  class=tablebody1><b><font color=#FF0000>-2</font></b> 
      ƪ</td>
  </tr>
  <tr> 
    <td class=tablebody1 width=15% align=right>����ֵ��</td>
    <td  width=35%  class=tablebody1><b><font color=#FF0000>1000</font> </b></td>
    <td width=15% align=right class=tablebody1>��ɾ���ʣ�</td>
<td width=35%  class=tablebody1><b></b> <font color=#FF0000><b>
4.26%
</b></font> 
    </td>
  </tr>
  <tr> 
    <td class=tablebody1 width=15% align=right>��  �ɣ�</td>
    <td  width=35%  class=tablebody1><b>
��������
 </b></td>
    <td class=tablebody1 width=15% align=right>��½������</td>
    <td width=35%  class=tablebody1><b>18</b> 
    </td>
  </tr>
  <tr> 
    <td class=tablebody1 width=15% align=right>ע�����ڣ�</td>
    <td  width=35%  class=tablebody1><b>2002-5-19 1:19:38</b></td>
    <td width=15% align=right class=tablebody1>�ϴε�¼��</td>
    <td width=35%  class=tablebody1><b>2003-7-31 11:14:03</b></td>
  </tr>
</table>
<br>
<table cellspacing=1 cellpadding=3 align=center class=tableborder1>
  <tr> 
    <th align=left colspan=4>
      �ʲ����</th>
  </tr>
  <tr> 
    <td class=tablebody1 width=15% align=right>�ֽ���ң�</td>
    <td width=35%  class=tablebody1><b>15340</b></td>
    <td colspan=2 valign=top rowspan=4 class=tablebody1>��ְ̳��
      <hr size=1 width=100 align=left>
����Ա<br>
      </td>
  </tr>
  <tr> 
    <td class=tablebody1 width=15% align=right>��Ʊ��ֵ��</td>
    <td  width=35%  class=tablebody1><b>0</b></td>
  </tr>

  <tr> 
    <td class=tablebody1 width=15% align=right>���д�</td>
    <td width=35%  class=tablebody1><b>0</b></td>
  </tr>
  <tr> 
    <td class=tablebody1 width=15% align=right>�� �� ����</td>
    <td width=35%  class=tablebody1><b>15340</b></td>
  </tr>
</table>
<br>
<table cellspacing=1 cellpadding=3 align=center class=tableborder1>
  <tr> 
    <th align=left id=tabletitlelink>
      ��������<a href="queryResult.asp?stype=1&nSearch=3&keyword=admin&SearchDate=ALL&boardid=0">�������û���������</a></th>
  </tr>
<tr> 
    <td class=tablebody1 align=left>
&nbsp;<img src=face/face1.gif width=14 height=14>&nbsp;<a href=dispbbs.asp?boardid=1&replyid=15&id=7&skin=1>jkhkh...</a>&nbsp;--&nbsp;2003-7-25 17:19:56<br>&nbsp;<img src=face/face1.gif width=14 height=14>&nbsp;<a href=dispbbs.asp?boardid=1&replyid=14&id=7&skin=1>khjklj...</a>&nbsp;--&nbsp;2003-7-25 17:19:12<br>&nbsp;<img src=face/face1.gif width=14 height=14>&nbsp;<a href=dispbbs.asp?boardid=1&replyid=13&id=7&skin=1>jkhj,...</a>&nbsp;--&nbsp;2003-7-25 17:18:44<br>&nbsp;<img src=face/face1.gif width=14 height=14>&nbsp;<a href=dispbbs.asp?boardid=1&replyid=12&id=7&skin=1>kjkj...</a>&nbsp;--&nbsp;2003-7-25 17:18:35<br>&nbsp;<img src=face/face1.gif width=14 height=14>&nbsp;<a href=dispbbs.asp?boardid=1&replyid=11&id=7&skin=1>nmhjhjk...</a>&nbsp;--&nbsp;2003-7-25 17:18:25<br>
</td>
  </tr>
</table>
<BR>

<table class=tableborder1 cellspacing=1 cellpadding=3 align=center>
<tr><th height="25" align=left colspan=2>��ݹ���ѡ��</th></tr>

<tr><td class=tablebody1 height=25 colspan=2>
<B>�û�����ѡ��</B>��   �� <a href=admin_lockuser.asp?action=lock_1&name=admin title=�������û��������½�ͷ���>����</a> | <a href=admin_lockuser.asp?action=lock_2&name=admin title=���θ��û�����̳�ķ���>����</a> | <a href=admin_lockuser.asp?action=lock_3&name=admin title=������û�����̳������������>���</a> | <a href="admin_lockuser.asp?action=power&name=admin" title=���û����з�ֵ����>����</a> | <a href="admin_lockuser.asp?action=getpermission&name=admin&userid=1">�༭���û���̳Ȩ��</a> ��
</td></tr>

<tr>
<FORM METHOD=POST ACTION="admin_lockuser.asp?action=DelTopic">
<td class=tablebody1 valign=middle width="50%">
<B>���ӹ���ѡ��</B>��   ɾ�����û�&nbsp;
<input type="hidden" value="1" name="SetUserID">
<input type="hidden" value="admin" name="name">
<select name="delTopicDate" size=1>

<option value="1">1</option>

<option value="2">2</option>

<option value="3">3</option>

<option value="4">4</option>

<option value="5">5</option>

<option value="6">6</option>

<option value="7">7</option>

<option value="8">8</option>

<option value="9">9</option>

<option value="10">10</option>

</select>&nbsp;���ڵ�����&nbsp;<input type="submit" name="submit" value="ִ��">
</td>
</form>
<FORM METHOD=POST ACTION="admin_lockuser.asp?action=DelUserReply">
<td class=tablebody1 width="50%">
   ɾ�����û�&nbsp;
<input type="hidden" value="1" name="SetUserID">
<input type="hidden" value="admin" name="name">
<select name="delTopicDate" size=1>

<option value="1">1</option>

<option value="2">2</option>

<option value="3">3</option>

<option value="4">4</option>

<option value="5">5</option>

<option value="6">6</option>

<option value="7">7</option>

<option value="8">8</option>

<option value="9">9</option>

<option value="10">10</option>

</select>&nbsp;���ڵĻظ��� 
<select size=1 name="delbbs">
<option value="bbs1" selected >���ݱ�1</option>
</select>
 ��&nbsp;<input type="submit" name="submit" value="ִ��">
</td>
</FORM></tr>

<tr><td class=tablebody1 valign=middle height=25 colspan=2>
<B>�û��������IP</B>��   <a href="look_ip.asp?action=lookip&ip=127.0.0.1">127.0.0.1</a>&nbsp;&nbsp;���IP�鿴�û���Դ������
</td></tr>

</table>

<?php
}

?>