<?php
/* �޸ĸ��˲������� wForum ͵���ġ�atppp 20040524 */
require("funcs.php");
login_init();



define('SHOWTELNETPARAM', 0); //�Ƿ���ʾ telnet ѡ��

/* ��ʽ��ÿ�������������һ�� 0 ��ʾ telnet ��ר�ò������ڶ����ǲ������ƣ��������ǲ���������ͣ��������ǲ��� ON �� OFF ������ľ��庬�� */
$user_define=array(array(0,"��ʾ�����", "��telnet��ʽ���Ƿ���ʾ�����","��ʾ","����ʾ"), /* DEF_ACBOARD */
    array(0,"ʹ�ò�ɫ", "��telnet��ʽ���Ƿ�ʹ�ò�ɫ��ʾ", "ʹ��", "��ʹ��"),                /* DEF_COLOR */
    array(0, "�༭ʱ��ʾ״̬��","��telnet��ʽ�±༭����ʱ�Ƿ���ʾ״̬��", "��ʾ","����ʾ"),         /* DEF_EDITMSG */
    array(0,"������������ New ��ʾ", "��telnet��ʽ���Ƿ���δ����ʽ�Ķ�����������", "��", "��"),    /* DEF_NEWPOST */
    array(0,"ѡ����ѶϢ��","��telnet��ʽ���Ƿ���ʾѡ��ѶϢ��","��ʾ","����ʾ"),             /* DEF_ENDLINE */
    array(0,"��վʱ��ʾ��������","��telnet��ʽ����վʱ�Ƿ���ʾ������������","��ʾ","����ʾ"),       /* DEF_LOGFRIEND */
    array(0,"�ú��Ѻ���","���������ر�ʱ�Ƿ�������Ѻ���","��", "��"),               /* DEF_FRIENDCALL */
    array(0, "ʹ���Լ�����վ����", "telnet��ʽ���Ƿ�ʹ���Լ�����վ����","��", "��"),      /* DEF_LOGOUT */
    array(0, "��վʱ��ʾ����¼", "telnet��ʽ�½�վʱ�Ƿ���ʾ����¼", "��", "��"),        /* DEF_INNOTE */
    array(0, "��վʱ��ʾ����¼", "telnet��ʽ����վʱ�Ƿ���ʾ����¼", "��", "��"),        /* DEF_OUTNOTE */
    array(0, "ѶϢ��ģʽ", "telnet��ʽ��ѶϢ������ʾ����",  "������״̬", "��������"), /* DEF_NOTMSGFRIEND */
    array(0, "�˵�ģʽѡ��", "telnet��ʽ�µĲ˵�ģʽ", "ȱʡģʽ", "����ģʽ"), /* DEF_NORMALSCR */
    array(0, "�Ķ������Ƿ�ʹ���ƾ�ѡ��", "telnet��ʽ���Ķ������Ƿ��ƾ�ѡ��", "��","��"),/* DEF_CIRCLE */
    array(0, "�Ķ������α�ͣ춵�һƪδ��","telnet��ʽ�������б�ʱ����Զ���λ��λ��", "��һƪδ������", "����һƪ����"),       /* DEF_FIRSTNEW */
    array(0, "��Ļ����ɫ��", "telnet��ʽ����Ļ����ɫ����ʾģʽ", "��׼", "�Զ��任"), /* DEF_TITLECOLOR */
    array(1, "���������˵�ѶϢ", "�Ƿ����������˸���������Ϣ��","��","��"),         /* DEF_ALLMSG */
    array(1, "���ܺ��ѵ�ѶϢ", "�Ƿ�������Ѹ���������Ϣ��", "��", "��"),          /* DEF_FRIENDMSG */
    array(0, "�յ�ѶϢ��������","�յ����ź��Ƿ���������������","��","��"),         /* DEF_SOUNDMSG */
    array(0, "��վ��Ļ�����ѶϢ","���˳���½���Ƿ�Ѷ���Ϣ��¼�����������䣿", "��", "��"),       /* DEF_MAILMSG */
    array(0, "������ʱʵʱ��ʾѶϢ","telnet��ʽ�±༭����ʱ�Ƿ�ʵʱ��ʾ����Ϣ��","��", "��"),     /*"���к�����վ��֪ͨ",    DEF_LOGININFORM */
    array(0,"�˵�����ʾ������Ϣ","telnet��ʽ���Ƿ��ڲ˵�����ʾ������Ϣ��", "��", "��"),       /* DEF_SHOWSCREEN */
    array(0, "��վʱ��ʾʮ������","telnet��ʽ��վʱ�Ƿ���ʾʮ�����Ż��⣿", "��ʾ", "����ʾ"),       /* DEF_SHOWHOT */
    array(0, "��վʱ�ۿ����԰�","telnet��ʽ�½�վʱ�Ƿ���ʾ���԰�","��ʾ","����ʾ"),         /* DEF_NOTEPAD */
    array(0, "����ѶϢ���ܼ�", "telnet��ʽ�����ĸ������Զ��ţ�", "Enter��","Esc��"),       /* DEF_IGNOREMSG */
    array(0, "ʹ�ø�������","telnet��ʽ���Ƿ�ʹ�ø������棿", "ʹ��", "��ʹ��"),         /* DEF_HIGHCOLOR */
    array(0, "��վʱ�ۿ���վ����ͳ��ͼ", "telnet��ʽ�½�վʱ�Ƿ���ʾ��վ����ͳ��ͼ��", "��ʾ", "����ʾ"), /* DEF_SHOWSTATISTIC Haohmaru 98.09.24 */
    array(0, "δ������ַ�","telnet��ʽ�����ĸ��ַ���Ϊδ�����", "*","N"),           /* DEF_UNREADMARK Luzi 99.01.12 */
    array(0, "ʹ��GB���Ķ�","telnet��ʽ����GB���Ķ���", "��", "��"),             /* DEF_USEGB KCN 99.09.03 */
    array(0, "�Ժ��ֽ������ִ���", "telnet��ʽ���Ƿ�Ժ��ֽ������ִ���","��", "��"),  /* DEF_CHCHAR 2002.9.1 */
    array(0, "��ʾ��ϸ�û���Ϣ(wForum)", "�Ƿ��������˿��������Ա����ա���ϵ��ʽ������", "����", "������"),  /*DEF_SHOWDETAILUSERDATA 2003.7.31 */
    array(0, "��ʾ��ʵ�û���Ϣ(wForum)",  "�Ƿ��������˿���������ʵ��������ַ������", "����", "������") /*DEF_REALDETAILUSERDATA 2003.7.31 */
);

$user_define1=array(array(1,"���� IP", "�Ƿ��ĺͱ���ѯ��ʱ�������Լ��� IP ��Ϣ","����","������") /* DEF_HIDEIP */
);

$mailbox_prop=array(array(1,"����ʱ�����ż���������", "�Ƿ���ʱ�Զ�ѡ�񱣴浽������","����","������"),
array(1,"ɾ���ż�ʱ�����浽������", "�Ƿ�ɾ���ż�ʱ�����浽������","������","����"),
array(0,"��������", "telnet��ʽ�°��水 'v' ʱ����ʲô���棿","����������","�ռ���")
);


function getOptions($var_name, $oldvalue) {
	global $$var_name;
	$userdefine = $$var_name;
	$ccc = count($userdefine);
	$flags = $oldvalue;
	for ($i = 0; $i < $ccc; $i++) {
		if (isset($_POST[$var_name."_".$i])) {
			if ($_POST[$var_name."_".$i] == 1) {
				$flags |= (1<<$i);
			} else {
				$flags &= ~(1<<$i);
			}
		}
	}
	return $flags;
}


function showOptions($var_name, $userparam, $isWWW) {
	global $$var_name;
	$userdefine = $$var_name;
	$ccc = count($userdefine);
	for ($i = 0; $i < $ccc; $i++) {
		if ($userdefine[$i][0]!=$isWWW) 
			continue;
		$flag=1<<$i;
?>
<tr><td align="left" class="t3"><B><?php echo $userdefine[$i][1]; ?></B>��<BR><?php echo $userdefine[$i][2]; ?></td>   
        <td class="t3">    
			<input type="radio" name="<?php echo $var_name."_".$i; ?>" value="1" <?php if ($userparam & $flag) echo "checked"; ?> ><?php echo $userdefine[$i][3]; ?>
			<input type="radio" name="<?php echo $var_name."_".$i; ?>" value="0" <?php if (!($userparam & $flag)) echo "checked"; ?> ><?php echo $userdefine[$i][4]; ?>
        </td>   
</tr>
<?php
	}	
}

function showDefines($isWWW) {
	global $currentuser;
	global $currentuinfo;
	showOptions("user_define", $currentuser['userdefine0'], $isWWW);
	showOptions("user_define1", $currentuser['userdefine1'], $isWWW);
	showOptions("mailbox_prop", $currentuinfo['mailbox_prop'], $isWWW);
}

function showOptionsForm(){
	global $currentuser;
	global $user_define,$user_define_num;
?>
<form action="bbsparm.php?do=1" method=POST name="theForm">
<table class="t1" cellspacing="0" cellpadding="5" border="0">
<tr> 
      <th class="t2" colspan="2" width="100%">�û����˲���</th>
 </tr> 
<?php
	showDefines(1);
	if (SHOWTELNETPARAM == 1) {
?>
<tr> 
      <th class="t2" colspan="2" width="100%">�û����˲�����telnet��ʽר�ã�</th>
 </tr> 
<?php
		showDefines(0);
	}
?>
<tr align="center"> 
<td class="t2" colspan="2" width="100%">
<input type=Submit value="ȷ���޸�" name="Submit">
</td></tr>
</table></form>
<?php
}

if ($loginok != 1) {
    html_nologin();
    exit();
}
html_init("gb2312","","",1);
if(!strcmp($currentuser["userid"],"guest"))
    html_error_quit("���ȵ�¼");

?>
<body topmargin="0">
<table width="100%" border="0" cellspacing="0" cellpadding="3">
  <tr> 
    <td class="b2">
	    <a href="bbssec.php" class="b2"><?php echo BBS_FULL_NAME; ?></a>
	    -
	    <?php echo $currentuser["userid"]; ?>�Ĺ�����
	    -
	    �޸ĸ��˲���
    </td>
   </tr>
   <tr>
   <td height="20"> </td>
   </tr>
   <tr>
        <td align="center">
<?php
    if (isset($_GET['do'])) {
		$userdefine0 = getOptions("user_define", $currentuser['userdefine0']);
		$userdefine1 = getOptions("user_define1", $currentuser['userdefine1']);
		$mailbox_prop = getOptions("mailbox_prop", $currentuinfo['mailbox_prop']);
		bbs_setuserparam($userdefine0, $userdefine1, $mailbox_prop);
?>
�����޸ĳɹ�<br /><br />
[<a href="/<?php echo MAINPAGE_FILE; ?>">������ҳ</a>]
[<a href="javascript:history.go(-2);">���ٷ���</a>]
<?php
    }
    else
        showOptionsForm();
?>        
        </td>
   </tr>
</table>
<?php
html_normal_quit();
?>