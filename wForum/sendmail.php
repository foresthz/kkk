<?php
require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");
require("inc/board.inc.php");

//ToDo: ת���ż������������ͷ���� telnet ��ʽת���ǲ��������ͷ�ģ��б�Ҫ�ĳ�һ�µ���- atppp
$action=0; //0: �·��ż���1: ָ���ռ����·��ż���2: �ظ��ż���3: �ظ��������£�4: ת���ż�
$mail_receiver="";

setStat("׫д���ż�");

requireLoginok();

show_nav();

preprocess();

showUserMailbox();
head_var($userid."�ķ�����","usermailbox.php?boxname=sendbox",0);
showUserManageMenu();
showmailBoxes();
main();

show_footer();

function preprocess() {
	global $action, $mail_receiver;
    if (!bbs_can_send_mail()) {
		foundErr("��û��д��Ȩ��!");
	}
	if (isset($_GET['boxname'])) {
		setstat("�ظ��ż�");
		if (isset($_GET['forward'])) $action = 4;
		else $action = 2;
		$num=intval($_GET['num']);
		$boxName = $_GET['boxname'];
		if (getMailBoxPathDesc($boxName, $path, $desc)) {
			return getmail($boxName, $path, $num);
		} else {
			foundErr("��ָ���˴�����������ƣ�");
		}
	}
	if (isset($_GET['board'])) {
		setstat("���Ÿ�����");
		$action=3;
		$reID=intval($_GET['reID']);
		return getarticle($_GET['board'],$reID);
	}
	if (isset($_GET['receiver'])) {
		$action = 1;
		$mail_receiver = $_GET['receiver'];
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
	}
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $boardID) == 0) {
		foundErr("����Ȩ�Ķ����棡");
	}
	if (bbs_is_readonly_board($boardArr)) {
		foundErr("����Ϊֻ����������");
	}
	settype($reID, "integer");
	$articles = array();
	if ($reID > 0)	{
	$num = bbs_get_records_from_id($brdArr['NAME'], $reID,$dir_modes["NORMAL"],$articles);
		if ($num == 0)	{
			foundErr("����������ı��");
		}
	}
	$article=$articles[1];
	return true;
}


function getmail($boxName, $boxPath, $num){
	global $article;
	global $currentuser;
	$dir = bbs_setmailfile($currentuser["userid"],$boxPath);

	$articles = array ();
	if( bbs_get_records_from_num($dir, $num, $articles) ) {
		$file = $articles[0]["FILENAME"];
	} else {
		foundErr("����ָ�����ż�������");
	}

	$filename = bbs_setmailfile($currentuser["userid"],$file) ;

	if(! file_exists($filename)){
		foundErr("����ָ�����ż�������");
	}
	$article=$articles[0];
	return true;
}

function main() {
	global $currentuser;
	global $article, $mail_receiver;
	global $action;
?>
<br>
<form action="dosendmail.php" method=post name=messager id="messager" onkeydown="if(event.keyCode==13 && event.ctrlKey){ obj=getRawObject('messager');obj.submit();} ">
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
          <tr> 
            <th colspan=3><?php echo $action<2?"׫д���ż�":($action==4?"ת���ż�":"�ظ��ż�"); ?></td>
          </tr>
          <tr> 
            <td class=TableBody1 valign=middle><b>�ռ���:</b></td>
            <td class=TableBody1 valign=middle>
              <input name="destid" maxlength="12" value="<?php if ($action!=0 && $action!=4) 
			echo ($action==1?$mail_receiver:$article['OWNER']).'" size="12" readonly />'; 
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
		if ($action>1)	{
	        if ($action == 4) {
	        	$nowtitle = $article["TITLE"]."(ת��)";
	        } else {
	        	if (!strncmp($article["TITLE"],"Re: ",4)) {
	        		$nowtitle = $article["TITLE"];
	        	} else {
	            	$nowtitle = "Re: " . $article["TITLE"];
	            }
	        }
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
              <textarea style="width:500px;height:300px" name="content">
<?php
    if($action>1){
		if ($action==3){
    		$filename = "boards/" . $_GET['board']. "/" . $article['FILENAME'];
            echo "\n�� �� " . $article['OWNER'] . " �Ĵ������ᵽ: ��\n";
		}else{
			$filename = bbs_setmailfile($currentuser["userid"],$article['FILENAME']) ;
            if ($action != 4) echo "\n�� �� " . $article['OWNER'] . " ���������ᵽ: ��\n";
		}
		if(file_exists($filename))
		{
		    $fp = fopen($filename, "r");
	        if ($fp) {
				$buf = fgets($fp,5000);
				$prefix = "";
				if ($action != 4) {
					if(strncmp($buf, "������", 6) == 0) {
						for ($i = 0; $i < 3; $i++) {
							if (($buf = fgets($fp,5000)) == FALSE)
								break;
						}
					}
					$prefix = ": ";
				}
				while (1) {
					if ($action != 4) {
						if (($buf = fgets($fp,5000)) == FALSE)
							break;
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
						if (strncmp($buf,"\n",1) == 0)
							continue;
						if (++$lines > 10) {
							echo ": ...................\n";
							break;
						}
					}
					//if (stristr($buf, "</textarea>") == FALSE)  //filter </textarea> tag in the text
						echo $prefix . htmlspecialchars($buf);
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
		for ($i = 1; $i <= $currentuser["signum"]; $i++) {
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
<?php
    $bBackup = (bbs_is_save2sent() != 0);
?>
 <input type="checkbox" name="backup"<?php if ($bBackup) echo " checked=\"checked\""; ?>>���ݵ���������
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
