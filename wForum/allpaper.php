<?php

//ToDo: ������������С�- atppp

require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");
require("inc/conn.php");

global $boardArr;
global $boardID;
global $boardName;
global $page;

//preprocess();

setStat("С�ֱ��б�");

show_nav($boardName);

if (isErrFounded()) {
	html_error_quit() ;
} else {
	showUserMailBoxOrBR();
	board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
	if ($_POST['action']=="delpaper") {
		batch();
	} else {
		boardeven();
	} 
}

//showBoardSampleIcons();
show_footer();

CloseDatabase();

function boardeven($boardID,$boardName){
	global $usernum;
	$currentPage=intval($_REQUEST['page']);
	if ($currentpage==0 ) {
		$currentpage=1;
	}
	if (bbs_is_bm($boardID,$usernum)) {
?>
	<div align=center>���������л�������״̬</div>
	<?php   } ?>
	<form action=allpaper.php method=post name=paper>
	<input type="hidden" name="action" value="delpaper" />
	<input type=hidden name=board value="<?php   echo $boardName; ?>" />
	<table class=TableBorder1 cellspacing="1" cellpadding="3" align="center">
	<tr align=center> 
	<th width="15%" height=25>�û�</th>
	<th width="50%">����</th>
	<th width="20%">����ʱ��</th>
	<th width="15%" id=TableTitleLink><?php   if (bbs_is_bm($boardID,$usernum))
	{
	?><a href="allpaper.php?action=batch&board=<?php     echo $boardName; ?>"><?php   } ?>����</a></th>
	</tr> 
	<?php 

	$sql="select * from smallpaper_tb where boardID=".$boardID." order by Addtime desc";

	$sth=$conn->query($sql);
	$totalrec=$sth->numRows();
	if ($totalrec==0) {
		echo  "<tr> <td class=TableBody1 colspan=4 height=25>���滹û��С�ֱ�</td></tr>";
	}else{
		$page_count=0;
		while($rs=$sth->fetchRow(DB_FETCHMODE_ASSOC)) {
			echo '<tr>';
			echo '<td class=TableBody1 align=center height=24>';
			echo '<a href=dispuser.php?id='.$rs['Owner'].' target=_blank>'.$rs['Owner'].'</a>';
			echo '</td>';
			echo '<td class=TableBody1>';
			echo "<a href=javascript:openScript('viewpaper.php?id=".$rs["ID"]."&boardname=".$boardName."',500,400)>".htmlspecialchars($rs['Title'],ENT_QUOTES).'</a>';
			echo '</td>';
			echo '<td class=TableBody1>'.$rs['Addtime'].'</td>';
			echo '<td align=center class=TableBody1>';
			if ($_REQUEST['action']=="batch")	{
				echo "<input type=checkbox name=sid value=".$rs["ID"].">";
			}else{
				echo $rs["Hits"];
			} 

			echo "</td></tr>";
			$page_count++;
	} 
	if ($_REQUEST['action']=="batch")
	{
	  echo "<tr><td class=TableBody2 colspan=4 align=right>��ѡ��Ҫɾ����С�ֱ���<input type=checkbox name=chkall value=on onclick=\"CheckAll(this.form)\">ȫѡ <input type=submit name=Submit value=ִ��  onclick=\"{if(confirm('��ȷ��ִ�еĲ�����?')){this.document.paper.submit();return true;}return false;}\"></td></tr>";
	} 

	echo "</table>";
	$Pcount=1;
	print "<table border=0 cellpadding=0 cellspacing=3 width=\"97%\" align=center>".
	"<tr><td valign=middle nowrap>".
	"ҳ�Σ�<b>".$currentpage."</b>/<b>".$Pcount."</b>ҳ".
	"ÿҳ<b>".$Forum_Setting[11]."</b> ����<b>".$totalrec."</b>��</td>".
	"<td valign=middle nowrap><div align=right><p>��ҳ�� ";

	if ($currentpage>4)
	{

	  print "<a href=\"?page=1&boardid=".$Boardid."\">[1]</a> ...";
	} 

	if ($Pcount>$currentpage+3)
	{

	  $endpage=$currentpage+3;
	}
	  else
	{

	  $endpage=$Pcount;
	} 

	for ($i=$currentpage-3; $i<=$endpage; $i=$i+1)
	{

	  if (!$i<1)
	  {

		if ($i==intval($currentpage))
		{

		  print " <font color=".$Forum_body[8].">[".$i."]</font>";
		}
		  else
		{

		  print " <a href=\"?page=".$i."&boardid=".$Boardid."\">[".$i."]</a>";
		} 

	  } 


	} 

	if ($currentpage+3<$Pcount)
	{

	  print "... <a href=\"?page=".$Pcount."&boardid=".$Boardid."\">[".$Pcount."]</a>";
	} 

	print "</font></td></tr>";

	$rs=null;

	} 


	print "</table></form>";

	return $function_ret;
} 

function batch()
{
  extract($GLOBALS);


  $adminpaper=false;
  if (!$Founduser)
  {

    $Founderr=true;
    $Errmsg=$Errmsg+"<br>"+"<li>���½����в�����";
  } 

  if (($master || $superboardmaster || $boardmaster) && intval($GroupSetting[27])==1)
  {

    $adminpaper=true;
  }
    else
  {

    $adminpaper=false;
  } 

  if ($UserGroupID>3 && intval($GroupSetting[27])==1)
  {

    $adminpaper=true;
  } 

  if ($FoundUserPer && intval($GroupSetting[27])==1)
  {

    $adminpaper=true;
  }
    else
  if ($FoundUserPer && intval($GroupSetting[27])==0)
  {

    $adminpaper=false;
  } 

  if (!$adminpaper)
  {

    $Founderr=true;
    $Errmsg=$Errmsg+"<br>"+"<li>��û��Ȩ��ִ�б�������";
  } 

  if ($_POST["sid"]=="")
  {

    $Founderr=true;
    $Errmsg=$Errmsg+"<br>"+"<li>��ָ�����С�ֱ���";
  }
    else
  {

    $sid=str_replace("'","",$_POST["sid"]);
  } 

  if ($Founderr)
  {
    return $function_ret;
  } 

$conn->query("delete from smallpaper_tb where boardID=".$boardID." and s_id in (".$sid.")");
  $sucmsg="<li>��ѡ���С�ֱ��Ѿ�ɾ����";
dvbbs_suc();
  return $function_ret;
} 
?>
