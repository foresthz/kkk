<?
  session_start();
  $_SESSION["ReflashTime"]=time();
?>
<?php require("conn.php"); ?>
<?php require("inc/const.php"); ?>
<?php require("inc/chkinput.php"); ?>
<?php 
//=========================================================

// File: smallpaper.php

// Version:5.0

// Date: 2002-9-10

// Script Written by satan

//=========================================================

// Copyright (C) 2001,2002 AspSky.Net. All rights reserved.

// Web: http://www.phpsky.net,http://www.dvbbs.net

// Email: info@aspsky.net,eway@aspsky.net

//=========================================================


$cansmallpaper=false;
$Stats="����С�ֱ�";
if ($Boardid==0)
{

  $Founderr=true;
  $Errmsg.="<br><li>��ѡ����Ҫ����С�ֱ��İ��棡";
} 



if (intval($GroupSetting[17])==0)
{

  $Errmsg.="<br><li>��û�з���С�ֱ���Ȩ�ޣ���<a href=login.php>��½</a>����ͬ����Ա��ϵ��";
  $Founderr=true;
}
  else
{

  if (!$Founduser)
  {

    $membername="����";
  } 

  $cansmallpaper=true;
} 


if ($Founderr)
{

nav();
head_var(2,0,"","");
dvbbs_error();
}
  else
{

nav();
head_var(1,$BoardDepth,0,0);
  if ($_REQUEST['action']=="savepaper")
  {

savepaper();
  }
    else
  {

main();
  } 

activeonline();
  if ($Founderr)
  {
dvbbs_error();  } 

} 

footer();
function main()
{
  extract($GLOBALS);


$conn->query("delete from SmallPaper where s_addtime<subdate(Now(),interval 2 day)");
?>
<form action="smallpaper.php?action=savepaper" method="post"> 
        <table cellpadding=6 cellspacing=1 align=center class=tableborder1>
    <tr>
    <th valign=middle colspan=2>
    ����ϸ��д������Ϣ(<?php   echo $msg; ?>)</th></tr>
    <tr>
    <td class=tablebody1 valign=middle><b>�û���</b></td>
    <td class=tablebody1 valign=middle><INPUT name=username type=text value="<?php   echo $membername; ?>"> &nbsp; <a href="reg.php">û��ע�᣿</a></td></tr>
    <tr>
    <td class=tablebody1 valign=middle><b>�� ��</b></font></td>
    <td class=tablebody1 valign=middle><INPUT name=password type=password value="<?php   echo $memberword; ?>"> &nbsp; <a href="lostpass.php">�������룿</a></td></tr>
    <tr>
    <td class=tablebody1 valign=middle><b>�� ��</b>(���80��)</td>
    <td class=tablebody1 valign=middle><INPUT name="title" type=text size=60></td></tr>
    <tr>
    <td class=tablebody1 valign=top width=30%>
<b>�� ��</b><BR>
�ڱ��淢��С�ֱ���������<font color="<?php   echo $Forum_body[8]; ?>"><b><?php   echo $GroupSetting[46]; ?></b></font>Ԫ����<br>
<font color="<?php   echo $Forum_body[8]; ?>"><b>48</b></font>Сʱ�ڷ����С�ֱ��������ȡ<font color="<?php   echo $Forum_body[8]; ?>"><b>5</b></font>��������ʾ����̳��<br>
<li>HTML��ǩ�� <?php   if ($Forum_Setting[35]==0)
  {
?>������<?php   }
    else
  {
?>����<?php   } ?>
<li>UBB ��ǩ�� <?php   if ($Forum_Setting[34]==0)
  {
?>������<?php   }
    else
  {
?>����<?php   } ?>
<li>���ݲ��ó���500��
</td>
    <td class=tablebody1 valign=middle>
<textarea class="smallarea" cols="60" name="Content" rows="8" wrap="VIRTUAL"></textarea>
<INPUT name="boardid" type=hidden value="<?php   echo $Boardid; ?>">
                </td></tr>
    <tr>
    <td class=tablebody2 valign=middle colspan=2 align=center><input type=submit name="submit" value="�� ��"></td></tr></table>
</form>
<?php   return $function_ret;
} ?>
<?php 
function savepaper()
{
  extract($GLOBALS);
  global $Founderr,$Errmsg,$sucmsg;


  $UserName=checkStr(trim($_POST["username"]));
  $PassWord=checkStr(trim($_POST["password"]));
  $title=checkStr(trim($_POST["title"]));
  $Content=checkStr($_POST["Content"]);
  if ($chkpost==false)
  {

    $Errmsg.="<br><li>���ύ�����ݲ��Ϸ����벻Ҫ���ⲿ�ύ���ԡ�";
    $Founderr=true;
  } 

  if ($UserName=="")
  {

    $Errmsg.="<br><li>����������";
    $Founderr=true;
  } 

  if ($title=="")
  {

    $Founderr=true;
    $Errmsg.="<br><li>���ⲻӦΪ�ա�";
  }
    else
  if (strlen($title)>80)
  {

    $Founderr=true;
    $Errmsg.="<br><li>���ⳤ�Ȳ��ܳ���80";
  } 

  if ($Content=="")
  {

    $Errmsg.="<br><li>û����д���ݡ�";
    $Founderr=true;
  }
    else
  if (strlen($content)>500)
  {

    $Errmsg.="<br><li>�������ݲ��ô���500";
    $Founderr=true;
  } 


//���˲���������֤�û�

  if (!$Founderr && $cansmallpaper)
  {

    if ($PassWord!=$memberword)
    {
      $PassWord=md5($PassWord);
    } 

// $rs is of type "adodb.recordset"

    $sql="Select userWealth From user Where UserName='".$UserName."' and UserPassWord='".$PassWord."'";
	$rs=$conn->getRow($sql);
    if ($rs!=null)
    {

      if (intval($rs[0])<intval($GroupSetting[46]))
      {

        $Errmsg.="<br><li>��û���㹻�Ľ�Ǯ������С�ֱ����쵽��̳����ˮ�ɡ�";
        $Founderr=true;
      }
        else
      {

		$rs[0]=$rs[0]-intval($GroupSetting[46]);
		$conn->query("update user set userWealth=".$rs[0]." where UserName=".$UserName);
                
      } 

    } 

    
    $rs=null;

  } 

  if ($Founderr)
  {

    return $function_ret;

  }
    else
  {

    $sql="insert into SmallPaper (s_boardid,s_username,s_title,s_content,s_addtime) values ".
      "(".
      $Boardid.",'".
      $UserName."','".
      $title."','".
      $Content."','".
	  strftime("%Y-%m-%d %H:%M:%S")."')";
//response.write sql

$conn->query($sql);
    $sucmsg="<li>���ɹ��ķ�����С�ֱ���";
dvbbs_suc();
  } 

  return $function_ret;
} 
?>
