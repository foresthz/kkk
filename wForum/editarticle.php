<?php

require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");

global $boardArr;
global $boardID;
global $boardName;
global $reID;
global $reArticles;

setStat("�༭����");

requireLoginok("�οͲ��ܱ༭���¡�");

preprocess();

show_nav();

showUserMailBoxOrBR();
board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
showPostArticles($boardID,$boardName,$boardArr,$reID,$reArticles);

show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $loginok;
	global $reID;
	global $reArticles;
	if (!isset($_GET['board'])) {
		foundErr("δָ�����档");
	}
	$boardName=$_GET['board'];
	$brdArr=array();
	$boardID= bbs_getboard($boardName,$brdArr);
	$boardArr=$brdArr;
	$boardName=$brdArr['NAME'];
	if ($boardID==0) {
		foundErr("ָ���İ��治���ڡ�");
	}
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ����棡");
	}
	if (bbs_is_readonly_board($boardArr)) {
		foundErr("����Ϊֻ����������");
	}
	if (bbs_checkpostperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�ڱ��淢�����£�");
	}
	if (isset($_GET["reID"])) {
		$reID = $_GET["reID"];
	}else {
		foundErr("δָ���༭������.");
	}
	settype($reID, "integer");
	$articles = array();
	if ($reID > 0)	{
	$num = bbs_get_records_from_id($boardName, $reID,$dir_modes["NORMAL"],$articles);
		if ($num == 0)	{
			foundErr("����������ı��");
		}
	}
	$ret=bbs_caneditfile($boardName,$articles[1]['FILENAME']);
	switch ($ret) {
	case -1:
		foundErr("���������ƴ���");
	case -2:
		foundErr("���治���޸�����");
	case -3:
		foundErr("�����ѱ�����ֻ��");
	case -4:
		foundErr("�޷�ȡ���ļ���¼");
	case -5:
		foundErr("�����޸���������!");
	case -6:
		foundErr("ͬ��ID�����޸���ID������");
	case -7:
		foundErr("����POSTȨ����");
	}
	$reArticles=$articles;
	return true;
}

function showPostArticles($boardID,$boardName,$boardArr,$reID,$reArticles){
	global $currentuser;
?>
<script src="inc/ubbcode.js"></script>
<form action="doeditarticle.php" method=POST onSubmit=submitonce(this) name=frmAnnounce>
<input type="hidden" value="<?php echo $boardName; ?>" name="board">
<input type="hidden" value="<?php echo $reID; ?>" name="reID">
<table cellpadding=3 cellspacing=1 class=TableBorder1 align=center>
    <tr>
      <th width=100% height=25 colspan=2 align=left>&nbsp;&nbsp;�༭����</th>
    </tr>
<!--
        <tr>
          <td width=20% class=TableBody2><b>�û���</b></td>
          <td width=80% class=TableBody2><input name=username value="<?php   echo $currentuser['userid']; ?>" class=FormClass>&nbsp;&nbsp;<font color=#ff0000><b>*</b></font><a href=register.php>��û��ע�᣿</a> 
          </td>
        </tr>
        <tr>
          <td width=20% class=TableBody1><b>��&nbsp;&nbsp;��</b></td>
          <td width=80% class=TableBody1><input name=passwd type=password value=<?php   echo $_COOKIE['PASSWORD']; ?> class=FormClass><font color=#ff0000>&nbsp;&nbsp;<b>*</b></font><a href=lostpass.php>�������룿</a></td>
        </tr>
-->
        <tr>
          <td width=20% class=TableBody2><b>�������</b>
          </td>
          <td width=80% class=TableBody2>&nbsp;<?php echo htmlspecialchars($reArticles[1]["TITLE"],ENT_QUOTES); ?>&nbsp;&nbsp;
	 </td>
        </tr>
        <tr>
          <td width=20% valign=top class=TableBody1><b>��ǰ����</b><br><li>���������ӵ�ǰ��</td>
          <td width=80% class=TableBody1>
<?php
	for ($i=0; $i<=18; $i++) {
?>
	<input type="radio" value="<?php     echo $i ?>" name="Expression" 
<?php    
		if ($i==0) {
			echo "checked";    
		} 
?>><img src="face/face<?php echo  $i; ?>.gif" >&nbsp;&nbsp;
<?php 
		if ($i>0 && (($i+1)% 9==0)) {
			echo "<br>";    
		} 
	} 
?>
 </td>
        </tr>
        <tr>
          <td width=20% valign=top class=TableBody1>
<b>����</b><br>
<li>HTML��ǩ�� <?php   echo $Board_Setting[5]?"����":"������"; ?>
<li>UBB��ǩ�� <?php   echo $Board_Setting[6]?"����":"������"; ?>
<li>��ͼ��ǩ�� <?php   echo $Board_Setting[7]?"����":"������"; ?>
<li>��ý���ǩ��<?php   echo $Board_Setting[9]?"����":"������"; ?>
<li>�����ַ�ת����<?php   echo $Board_Setting[8]?"����":"������"; ?>
<li>�ϴ�ͼƬ��<?php   echo $Forum_Setting[3]?"����":"������"; ?>
<li>���<?php   echo intval($Board_Setting[16]/1024); ?>KB<BR><BR>
<B>��������</B><BR>
<li><?php   echo $Board_Setting[10]?"<a href=\"javascript:money()\" title=\"ʹ���﷨��[money=������ò���������Ҫ��Ǯ��]����[/money]\">��Ǯ��</a>":"��Ǯ����������"; ?>
<li><?php   echo $Board_Setting[11]?"<a href=\"javascript:point()\" title=\"ʹ���﷨��[point=������ò���������Ҫ������]����[/point]\">������</a>":"��������������"; ?>
<li><?php   echo $Board_Setting[12]?"<a href=\"javascript:usercp()\" title=\"ʹ���﷨��[usercp=������ò���������Ҫ������]����[/usercp]\">������</a>":"��������������"; ?>
<li><?php   echo $Board_Setting[13]?"<a href=\"javascript:power()\" title=\"ʹ���﷨��[power=������ò���������Ҫ������]����[/power]\">������</a>":"��������������"; ?>
<li><?php   echo $Board_Setting[14]?"<a href=\"javascript:article()\" title=\"ʹ���﷨��[post=������ò���������Ҫ������]����[/post]\">������</a>":"��������������"; ?>
<li><?php   echo $Board_Setting[15]?"<a href=\"javascript:replyview()\" title=\"ʹ���﷨��[replyview]�ò������ݻظ���ɼ�[/replyview]\">�ظ���</a>":"�ظ�����������"; ?>
<li><?php   echo $Board_Setting[23]?"<a href=\"javascript:usemoney()\" title=\"ʹ���﷨��[usemoney=����ò���������Ҫ���ĵĽ�Ǯ��]����[/usemoney]\">������</a>":"��������������"; ?>
	  </td>
          <td width=80% class=TableBody1>
<?php require_once("inc/ubbmenu.php"); ?>
<textarea class=smallarea cols=95 name=Content rows=12 wrap=VIRTUAL title="����ʹ��Ctrl+Enterֱ���ύ����" class=FormClass onkeydown=ctlent()>
<?php
	bbs_printoriginfile($boardName,$reArticles[1]['FILENAME']); //ToDo: ����ط����� </textarea> ��û�� html ��������
?>
</textarea>
          </td>
        </tr>
		<tr>
                <td class=TableBody1 valign=top colspan=2 style="table-layout:fixed; word-break:break-all"><b>�������ͼ�����������м�����Ӧ�ı���</B><br>
<?php 
	for ($i=1; $i<=69; $i++) {
		if (strlen($i)==1)   {
			$ii="0".$i;
		} else  {
			$ii=$i;
		} 
		if ($i!=1 && (($i-1)%20)==0) {
			echo "<br>\n";
		}
		echo "<img src=\"emot/em".$ii.".gif\" border=0 onclick=\"insertsmilie('[em".$ii."]')\" style=\"CURSOR: hand\">&nbsp;";
	} 
?>
    		</td>
                </tr>
<tr>
	<td valign=middle colspan=2 align=center class=TableBody2>
	<input type=Submit value='�� ��' name=Submit> &nbsp; <input type=button value='Ԥ ��' name=Button onclick=gopreview()>&nbsp;
<input type=reset name=Submit2 value='�� ��'>
                </td></form></tr>
      </table>
</form>
<form name=frmPreview action=preview.php?boardid=<?php echo $Boardid; ?> method=post target=preview_page>
<input type=hidden name=title value=><input type=hidden name=body value=>
</form>
<?php
}
?>
