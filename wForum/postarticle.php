<?php
require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");
require("inc/ubbcode.php");

global $boardArr;
global $boardID;
global $boardName;
global $reID;
global $reArticles;

setStat("��������");

requireLoginok("�οͲ��ܷ������¡�");

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
		$reID = 0;
	}
	settype($reID, "integer");
	$articles = array();
	if ($reID > 0)	{
	$num = bbs_get_records_from_id($boardName, $reID,$dir_modes["NORMAL"],$articles);
		if ($num == 0)	{
			foundErr("����� Re �ı��");
		}
		if ($articles[1]["FLAGS"][2] == 'y') {
			foundErr("���Ĳ��ɻظ�!");
		}
		if ($articles[0]["FLAGS"][2] == 'y') {
			foundErr("���Ĳ��ɻظ�!");
		}
	}
	$reArticles=$articles;

	return true;
}

function showPostArticles($boardID,$boardName,$boardArr,$reID,$reArticles){
	global $currentuser;
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
	        if(!strncmp($reArticles[1]["TITLE"],"Re: ",4)) $nowtitle = $reArticles[1]["TITLE"]." ";
	        else
	            $nowtitle = "Re: " . $reArticles[1]["TITLE"]." ";
		} else {
			$nowtitle='';
		}
?>
		  <input name=subject size=70 maxlength=80 value="<?php echo htmlspecialchars($nowtitle,ENT_QUOTES); ?>">&nbsp;&nbsp;<font color=#ff0000><strong>*</strong></font>���ó��� 25 �����ֻ�50��Ӣ���ַ�
	 </td>
        </tr>
<?php
		/* disabled by atppp
?>
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
    (������ţ�disabled) */
	if (bbs_is_attach_board($boardArr)) {
?>
<tr>
<td width=20% valign=middle class=TableBody2>
<b>�ļ��ϴ�</b>
</td><td width=80% class=TableBody2>
<iframe name="ad" frameborder=0 width=100% height=24 scrolling=no src="postupload.php?board=<?php echo $boardName; ?>"></iframe>
</td></tr>
<?php   } ?>
        <tr>
          <td width=20% valign=top class=TableBody1><b>����</b></td>
          <td width=80% class=TableBody1>
<?php 
    if (ENABLE_UBB) require_once("inc/ubbmenu.php");
?>
<textarea class=smallarea cols=95 name=Content id="oArticleContent" rows=12 wrap=VIRTUAL title="����ʹ��Ctrl+Enterֱ���ύ����" class=FormClass onkeydown=ctlent()>
<?php
    if (($reID > 0) && ($_GET['quote']==1 || OLD_REPLY_STYLE)){
    	$isquote = ($_GET['quote']==1) && ENABLE_UBB;
		$filename = $reArticles[1]["FILENAME"];
		$filename = "boards/" . $boardName. "/" . $filename;
		if(file_exists($filename))	{
			$fp = fopen($filename, "r");
			if ($fp) {
				if ($isquote) {
					echo "[quote][b]����������[i]".$reArticles[1]['OWNER']."��".strftime("%Y-%m-%d %H:%M:%S",$reArticles[1]['POSTTIME'])."[/i]�ķ��ԣ�[/b]\n";
					$prefix = "";
				} else {
					echo "\n�� �� " . $reArticles[1]['OWNER'] . " �Ĵ������ᵽ: ��\n";
					$prefix = ": ";
				}
				$buf2='';
				$buf = fgets($fp, 5000);
				if(strncmp($buf, "������", 6) == 0) {
					for ($i = 0; $i < 3; $i++) {
						if (($buf = fgets($fp,5000)) == FALSE)
							break;
					}
				}
				$q_prefix = str_repeat(": ", BBS_QUOTE_LEV);
				$q_len = 2 + 2 * BBS_QUOTE_LEV;
				while (1) {
					if (($buf = fgets($fp, 500)) == FALSE)
						break;
					if (strncmp($buf, $q_prefix . "��", $q_len) == 0)
						continue;
					if (strncmp($buf, $q_prefix . ": ", $q_len) == 0)
						continue;
					if (strpos($buf, "�� ��Դ") !== FALSE)
						break;
					if (strpos($buf, "�� �޸�") !== FALSE)
						break;
					if (strncmp($buf, "--\n", 3) == 0)
						break;
					if (strncmp($buf, "\n", 1) == 0)
						continue;
					if (++$lines > BBS_QUOTED_LINES) {
						$buf2 .= ": ...................\n";
						break;
					}
					/* */
					//if (stristr($buf, "</textarea>") == FALSE)  //filter </textarea> tag in the text
						$buf2 .= $prefix . htmlspecialchars($buf);
				}
				echo reUBBCode($buf2);
				if ($isquote) echo "[/quote]\n";
				fclose($fp);
			}
		}
	}
?></textarea>
          </td>
        </tr>
<?php
        if (ENABLE_UBB) {
?>
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
<?php
        }
?>
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
<option value="-1" <?php if ($currentuser["signature"] < 0) echo "selected "; ?>>���ǩ����</option>
<?php
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
		echo "<input type=\"checkbox\" name=\"anonymous\" value=\"1\" />����<br />";
	}
	if ($reID == 0) {
?>
    <input type=checkbox name=emailflag value="1">�лظ�ʱʹ���ʼ�֪ͨ����
<?php
	}
	if (SUPPORT_TEX) {
?>
    <input type=checkbox name=texflag value="1">ʹ�� tex ����<?php if (ENABLE_UBB) echo "��ubb Ч���������ã�"; ?>
<?php
	}
?>
<BR><BR></td>
	</tr><tr>
	<td valign=middle colspan=2 align=center class=TableBody2>
	<input type=Submit value='�� ��' name=Submit id="oSubmit"> &nbsp;&nbsp;&nbsp; <input type=button value='Ԥ ��' name=preview onclick=gopreview()>
                </td></form></tr>
      </table>
</form>
<form name=frmPreview action=preview.php?boardid=<?php echo $Boardid; ?> method=post target=preview_page>
<input type=hidden name=title value=><input type=hidden name=body value=><input type=hidden name=texflag value=>
</form>
<?php
}
?>
