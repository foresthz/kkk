<?php
/*
**  Ϊˮľ�廪blog���붨�Ƶ������
**  ������������������ύ��BBSBLOGBOARD�Ⱥ���
**  @windinsn Mar 12 , 2004
*/
require("pcfuncs.php");
define(BBSBLOGBOARD , "SMTH_blog"); //blog����
define(BBSBLOGMINREGTIME , 6); //���ע��ʱ��

if ($loginok != 1)
	html_nologin();
elseif(!strcmp($currentuser["userid"],"guest"))
{
	html_init("gb2312");
	html_error_quit("���¼���ٽ���Blog����!");
	exit();
}
else
{
	$link = pc_db_connect();
	if( pc_load_infor($link,$currentuser["userid"]) )
	{
		pc_db_close($link);
		html_init("gb2312");
		html_error_quit("�Բ������Ѿ�ӵ��Blog��");
		exit();	
	}
	pc_db_close($link);
	
	if( time() - $currentuser["firstlogin"] < intval( BBSBLOGMINREGTIME * 2592000 ) )
	{
		html_init("gb2312");
		html_error_quit("�Բ�������ע��ʱ���в���".BBSBLOGMINREGTIME."����");
		exit();	
	}
	if( !$_POST["appname"] || !$_POST["appself"] || !$_POST["appdirect"] )
	{
		html_init("gb2312");
		html_error_quit("�Բ�������ϸ��дBlog�����");
		exit();	
	}
	
	$apptitle = "[����] ".$currentuser["userid"]." ���뽨��ˮľBLOG";
	$appbody  = "(1) BLOG���ƣ�".$_POST["appname"]."\n\n\n".
		    "(2) ������ ID ����Ҫ���ҽ���\n".
		    "    ID��".$currentuser["userid"]."\n".
		    "    ע��ʱ�䣺".date("Y��m��d��",$currentuser["firstlogin"])."\n\n".
		    "        ".$_POST["appself"]."\n\n\n".
		    "(3) ��Ӫ����(����������Blog�ĳ����滮)\n        ".$_POST["appdirect"]."\n\n";
	
	$ret = bbs_postarticle(BBSBLOGBOARD, preg_replace("/\\\(['|\"|\\\])/","$1",$apptitle), preg_replace("/\\\(['|\"|\\\])/","$1",$appbody), 0 , 0 , 0 , 0);
	switch ($ret) {
			case -1:
				html_error_quit("���������������!");
				break;
			case -2: 
				html_error_quit("����Ϊ����Ŀ¼��!");
				break;
			case -3: 
				html_error_quit("����Ϊ��!");
				break;
			case -4: 
				html_error_quit("����������Ψ����, ����������Ȩ���ڴ˷�������!");
				break;		
			case -5:	
				html_error_quit("�ܱ�Ǹ, �㱻������Աֹͣ�˱����postȨ��!");
				break;	
			case -6:
				html_error_quit("���η��ļ������,����Ϣ��������!");	
				break;
			case -7: 
				html_error_quit("�޷���ȡ�����ļ�! ��֪ͨվ����Ա, лл! ");
				break;
			case -8:
				html_error_quit("���Ĳ��ɻظ�!");
				break;
			case -9:
				html_error_quit("ϵͳ�ڲ�����, ��Ѹ��֪ͨվ����Ա, лл!");
				break;
		}
	pc_html_init("gb2312","Blog����");
?>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr align=center><th width="100%">�����ύ�ɹ���</td>
</tr><tr><td width="100%" class=TableBody1>
����BLOG�����Ѿ��ύ�ɹ�������Ա���������ڴ����������롣<br/><br/>
��ҳ�潫��3����Զ��л���Blog��̳<meta HTTP-EQUIV=REFRESH CONTENT='3; URL=/bbsdoc.php?board=<?php echo BBSBLOGBOARD; ?>' >��<b>������ѡ�����²�����</b><br><ul>
<li><a href="/mainpage.php">������ҳ</a></li>
<li><a href="/pc/pcmain.php">����Blog��ҳ</a></li>
<li><a href="/bbsdoc.php?board=<?php echo BBSBLOGBOARD; ?>">����Blog��̳</a></li>
</ul></td></tr></table>
<?php	
	html_normal_quit();
}
	
?>