<?php


$needlogin=1;

require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");
require("inc/ubbcode.php");

global $boardArr;
global $boardID;
global $boardName;
global $reID;
global $reArticles;

preprocess();

setStat("��������");

show_nav();

if (isErrFounded()) {
	html_error_quit() ;
} else {
	showUserMailBoxOrBR();
	board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
	showPostArticles($boardID,$boardName,$boardArr,$reID,$reArticles);
}

//showBoardSampleIcons();
show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	global $loginok;
	global $reID;
	global $reArticles;
	if ($loginok!=1) {
		foundErr("�οͲ��ܷ������¡�");
		return false;
	}
	if (!isset($_GET['board'])) {
		foundErr("δָ�����档");
		return false;
	}
	$boardName=$_GET['board'];
	$brdArr=array();
	$boardID= bbs_getboard($boardName,$brdArr);
	$boardArr=$brdArr;
	$boardName=$brdArr['NAME'];
	if ($boardID==0) {
		foundErr("ָ���İ��治���ڡ�");
		return false;
	}
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ����棡");
		return false;
	}
	if (bbs_is_readonly_board($boardArr)) {
			foundErr("����Ϊֻ����������");
			return false;
	}
	if (bbs_checkpostperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�ڱ��淢�����£�");
		return false;
	}
	if (isset($_GET["reID"])) {
		$reID = $_GET["reID"];
	}else {
		$reID = 0;
	}
	settype($reID, "integer");
	$articles = array();
	if ($reID > 0)	{
	$num = bbs_get_records_from_id($boardName, $reID,$dir_modes["NORMAL"],$articles);
		if ($num == 0)	{
				foundErr("����� Re �ı��");
				return false;
		}
		if ($articles[1]["FLAGS"][2] == 'y') {
			foundErr("���Ĳ��ɻظ�!");
			return false;
		}
		if ($articles[0]["FLAGS"][2] == 'y') {
			foundErr("���Ĳ��ɻظ�!");
			return false;
		}
	}
	$reArticles=$articles;

	return true;
}

function showPostArticles($boardID,$boardName,$boardArr,$reID,$reArticles){
	global $currentuser;
	global $_COOKIE;
?>
<script src="inc/ubbcode.js"></script>
<form action="dopostarticle.php" method=POST onSubmit=submitonce(this) name=frmAnnounce>
<input type="hidden" value="<?php echo $boardName; ?>" name="board">
<input type="hidden" value="<?php echo $reID; ?>" name="reID">
<table cellpadding=3 cellspacing=1 class=TableBorder1 align=center>
    <tr>
      <th width=100% height=25 colspan=2 align=left>&nbsp;&nbsp;����������</th>
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
              <SELECT name=font onchange=DoTitle(this.options[this.selectedIndex].value)>
              <OPTION selected value="">ѡ����</OPTION> <OPTION value=[ԭ��]>[ԭ��]</OPTION> 
              <OPTION value=[ת��]>[ת��]</OPTION> <OPTION value=[��ˮ]>[��ˮ]</OPTION> 
              <OPTION value=[����]>[����]</OPTION> <OPTION value=[����]>[����]</OPTION> 
              <OPTION value=[�Ƽ�]>[�Ƽ�]</OPTION> <OPTION value=[����]>[����]</OPTION> 
              <OPTION value=[ע��]>[ע��]</OPTION> <OPTION value=[��ͼ]>[��ͼ]</OPTION>
              <OPTION value=[����]>[����]</OPTION> <OPTION value=[����]>[����]</OPTION>
              <OPTION value=[����]>[����]</OPTION></SELECT>
          </td>
          <td width=80% class=TableBody2>
<?php
		if ($reID>0)	{
	        if(!strncmp($reArticles[1]["TITLE"],"Re: ",4)) $nowtitle = $reArticles[1]["TITLE"];
	        else
	            $nowtitle = "Re: " . $reArticles[1]["TITLE"];
		} else {
			$nowtitle='';
		}
?>
		  <input name=subject size=70 maxlength=80 value="<?php echo htmlspecialchars($nowtitle,ENT_QUOTES); ?>">&nbsp;&nbsp;<font color=#ff0000><strong>*</strong></font>���ó��� 25 �����ֻ�50��Ӣ���ַ�
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
<?php   
	if (bbs_is_attach_board($boardArr)) {
?>
<tr>
<td width=20% valign=middle class=TableBody2>
<b>�ļ��ϴ�</b>
<!--
<select size=1>
<option value="">��������</option>
<?php 
/*
    $iupload=explode('|', $Board_Setting[19]);
	$len=count($iupload);
    for ($i=0; $i<$len; $i=$i+1)
    {

      print "<option value=".$iupload[$i].">".$iupload[$i]."</option>";

    } 
*/
?>
</select>
-->
</td><td width=80% class=TableBody2>
<iframe name="ad" frameborder=0 width=100% height=24 scrolling=no src="postupload.php?board=<?php echo $boardName; ?>"></iframe>
</td></tr>
<?php   } ?>
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
<textarea class=smallarea cols=95 name=Content id="oArticleContent" rows=12 wrap=VIRTUAL title="����ʹ��Ctrl+Enterֱ���ύ����" class=FormClass onkeydown=ctlent()>
<?php
    if (($reID > 0) && ($_GET['quote']==1)){
		$filename = $reArticles[1]["FILENAME"];
		$filename = "boards/" . $boardName. "/" . $filename;
		if(file_exists($filename))	{
			$fp = fopen($filename, "r");
			if ($fp) {
				$buf = fgets($fp,5000);
				echo "[quote][b]����������[i]".$reArticles[1]['OWNER']."��".strftime("%Y-%m-%d %H:%M:%S",$reArticles[1]['POSTTIME'])."[/i]�ķ��ԣ�[/b]\n";
				$buf2='';
				if(strncmp($buf, "������", 6) == 0) {
					for ($i = 0; $i < 4; $i++) {
						if (($buf = fgets($fp,5000)) == FALSE)
							break;
					}
				}
				while (1) {
					if (strncmp($buf, ": ��", 4) == 0)
						continue;
					if (strncmp($buf, ": : ", 4) == 0)
						continue;
					if (strpos($buf, "�� ��Դ") !== FALSE)
						break;
					if (strpos($buf, "�� �޸�") !== FALSE)
						break;
					if (strncmp($buf, "--\n", 3) == 0)
						break;
					if (strncmp($buf,'\n',1) == 0)
						continue;
					if (++$lines > 10) {
						echo "...................\n";
						break;
					}
					/* */
					if (stristr($buf, "</textarea>") == FALSE)  //filter </textarea> tag in the text
						$buf2.=$buf;
					if (($buf = fgets($fp,5000)) == FALSE)
						break;
				}
				echo reUBBCode($buf2);
				echo "[/quote]\n";
				fclose($fp);
			}
		}
	}
?></textarea>
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
                <td valign=top class=TableBody1><b>ѡ��</b></td>
                <td valign=middle class=TableBody1>&nbsp;<select name="signature">
<?php
		if ($currentuser["signature"] == 0)	{
?>
<option value="0" selected="selected">��ʹ��ǩ����</option>
<?php
		}else{
?>
<option value="0">��ʹ��ǩ����</option>
<?php
		}
		for ($i = 1; $i <= $currentuser["signum"]; $i++) {
			if ($currentuser["signature"] == $i) {
?>
<option value="<?php echo $i; ?>" selected="selected">�� <?php echo $i; ?> ��</option>
<?php
			}else{
?>
<option value="<?php echo $i; ?>">�� <?php echo $i; ?> ��</option>
<?php
			}
		}
?>
</select>
 [<a target="_balnk" href="bbssig.php">�鿴ǩ����</a>]<br>
 <?php
    if (bbs_is_outgo_board($boardArr)) {
        $local_save = 0;
        if ($reID > 0) $local_save = !strncmp($reArticles[1]["INNFLAG"], "LL", 2);
?>
<input type="checkbox" name="outgo" value="1"<?php if (!$local_save) echo " checked=\"checked\""; ?> />ת��<br />
<?php
    }
		if (bbs_is_anony_board($boardArr) ) {
		echo "<input type=\"checkbox\" name=\"annonymous\" value=\"1\" />����<br />";
	}
?>
    <input type=checkbox name=emailflag value=yes disabled>�лظ�ʱʹ���ʼ�֪ͨ����</font>
<BR><BR></td>
	</tr><tr>
	<td valign=middle colspan=2 align=center class=TableBody2>
	<input type=Submit value='�� ��' name=Submit id="oSubmit"> &nbsp; <input type=button value='Ԥ ��' name=Button onclick=gopreview() disabled>&nbsp;
<input type=reset name=Submit2 value='�� ��' id="oSubmit2">
                </td></form></tr>
      </table>
</form>
<form name=preview action=preview.php?boardid=<?php echo $Boardid; ?> method=post target=preview_page>
<input type=hidden name=title value=><input type=hidden name=body value=>
</form>
<?php
}
?>
