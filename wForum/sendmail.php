<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

require("inc/board.inc.php");

$action=0;
$article;

setStat("׫д���ʼ�");

show_nav();

echo "<br><br>";

preprocess();

if (!isErrFounded()) {
	head_var($userid."�ķ�����","usermailbox.php?boxname=sendbox",0);
}

if ($loginok==1) {
	showUserManageMenu();
	showmailBoxes();
	main();
}else {
	foundErr("��ҳ��Ҫ������ʽ�û���ݵ�½֮����ܷ��ʣ�");
}


if (isErrFounded()) {
		html_error_quit();
} 
show_footer();

function preprocess() {
	global $action;
    if (!bbs_can_send_mail()) {
		foundErr("��û��д��Ȩ��!");
		return false;
	}
	if (isset($_GET['boxname'])) {
		setstat("�ظ��ż�");
		$action=1;
		$num=intval($_GET['num']);
		if ($_GET['boxname']=='inbox') {
			return getmail('inbox','.DIR','�ռ���', $num);
		}
		if ($_GET['boxname']=='sendbox') {
			return getmail('sendbox','.SENT','������',$num );
		}
		if ($_GET['boxname']=='deleted') {
			return getmail('deleted','.DELETED','������',$num);
		}
	}
	if (isset($_GET['board'])) {
		setstat("���Ÿ�����");
		$action=2;
		$reID=intval($_GET['reID']);
		return getarticle($_GET['board'],$reID);
	}
}

function getarticle($boardName,$reID){
	global $article;
	global $currentuser;
	$brdArr=array();
	$boardID= bbs_getboard($boardName,$brdArr);
	$boardArr=$brdArr;
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
	settype($reID, "integer");
	$articles = array();
	if ($reID > 0)	{
	$num = bbs_get_records_from_id($brdArr['NAME'], $reID,$dir_modes["NORMAL"],$articles);
		if ($num == 0)	{
			foundErr("����������ı��");
			return false;
		}
	}
	$article=$articles[1];
	return true;
}


function getmail($boxName, $boxPath, $boxDesc, $num){
	global $article;
	global $currentuser;
	$dir = bbs_setmailfile($currentuser["userid"],$boxPath);

	$total = filesize( $dir ) / 256 ;
	if( $total <= 0 ){
		foundErr("����ָ�����ż�������");
		return false;
	}
	$articles = array ();
	if( bbs_get_records_from_num($dir, $num, $articles) ) {
		$file = $articles[0]["FILENAME"];
	}else{
		foundErr("����ָ�����ż�������");
		return false;
	}

	$filename = bbs_setmailfile($currentuser["userid"],$file) ;

	if(! file_exists($filename)){
		foundErr("����ָ�����ż�������");
		return false;
	}
	$article=$articles[0];

	return true;
}

function main() {
	global $currentuser;
	global $_GET;
	global $article;
	global $action;
?>
<br>
<form action="dosendmail.php" method=post name=messager onkeydown="if(event.keyCode==13 && event.ctrlKey)messager.submit()">
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
          <tr> 
            <th colspan=3><?php echo $action==0?"׫д���ʼ�":"�ظ��ʼ�"; ?></td>
          </tr>
          <tr> 
            <td class=TableBody1 valign=middle><b>�ռ���:</b></td>
            <td class=TableBody1 valign=middle>
              <input name="destid" maxlength="12" value="<?php if ($action!=0) 
			echo $article['OWNER'].'" size="12" readonly />'; 
					else { ?>" size="12" />			 
              <SELECT name=font onchange=DoTitle(this.options[this.selectedIndex].value)>
              <OPTION selected value="">ѡ��</OPTION>
			  </SELECT><?php } ?>
            </td>
          </tr>
           <tr> 
            <td class=TableBody1 valign=top width=15%><b>���⣺</b></td>
            <td  class=TableBody1 valign=middle>
<?php
		if ($action!=0)	{
	        if(!strncmp($article["TITLE"],"Re: ",4)) $nowtitle = $article["TITLE"];
	        else
	            $nowtitle = "Re: " . $article["TITLE"];
		} else {
			$nowtitle='';
		}
?>
              <input name="title" maxlength="50" size="50" value="<?php echo htmlspecialchars($nowtitle,ENT_QUOTES); ?>"/>
            </td>
          </tr>
           <tr> 
            <td class=TableBody1 valign=top width=15%><b>���ݣ�</b></td>
            <td  class=TableBody1 valign=middle>
              <textarea style="width:500;height:300" name="content"><?php
    if($action!=0){
		if ($action==2){
    		$filename = "boards/" . $_GET['board']. "/" . $article['FILENAME'];
            echo "\n�� �� " . $article['OWNER'] . " �Ĵ������ᵽ: ��\n";
		}else{
			$filename = bbs_setmailfile($currentuser["userid"],$article['FILENAME']) ;
            echo "\n�� �� " . $article['OWNER'] . " ���������ᵽ: ��\n";
		}
		if(file_exists($filename))
		{
		    $fp = fopen($filename, "r");
	        if ($fp) {
				$buf = fgets($fp,500);
				if(strncmp($buf, "������", 6) == 0) {
					for ($i = 0; $i < 4; $i++) {
						if (($buf = fgets($fp,500)) == FALSE)
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
						echo ": ...................\n";
						break;
					}
					/* */
					if (stristr($buf, "</textarea>") == FALSE)  //filter </textarea> tag in the text
						echo ": ". $buf;
					if (($buf = fgets($fp,500)) == FALSE)
						break;
				}
				fclose($fp);
	        }
	    }
	}
?></textarea>
            </td>
          </tr>
		 <tr>
                <td valign=top class=TableBody1><b>ѡ�</b></td>
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
		for ($i = 1; $i <= bbs_getnumofsig(); $i++) {
			if ($currentuser["signature"] == $i) {
?>
<option value="<?php echo $i; ?>" selected="selected">�� <?php echo $i; ?> ��</option>
<?php
			} else {
?>
<option value="<?php echo $i; ?>">�� <?php echo $i; ?> ��</option>
<?php
			}
		}
?>
</select>
 [<a target="_balnk" href="bbssig.php">�鿴ǩ����</a>]<br>
 <input type=checkbox name=backup>���ݵ���������
			</td>
		  </tr>
          <tr> 
            <td  class=TableBody1 colspan=2>
<b>˵��</b>��<br>
�� ������ʹ��<b>Ctrl+Enter</b>����ݷ��Ͷ���<br>
<!--
�� ������Ӣ��״̬�µĶ��Ž��û�������ʵ��Ⱥ�������<b>5</b>���û�<br>

�� �������<b>50</b>���ַ����������<b>300</b>���ַ�<br>
-->
            </td>
          </tr>

          <tr> 
            <td  class=TableBody2 valign=middle colspan=2 align=center> 
			  
			  &nbsp;
              <input type=Submit value="�����ż�" name=Submit>
            </td>
          </tr>

        </table>
</form>
<?php

}
?>
