<?php
/*
** personal corp. configure start
$pcconfig["LIST"] :Blog��ҳ��ÿҳ��ʾ���û���;
$pcconfig["HOME"] :BBS��Ŀ¼,Ĭ��ΪBBS_HOME;
$pcconfig["BBSNAME"] :վ������,Ĭ��ΪBBS_FULL_NAME;
$pcconfig["ETEMS"] :RSS�������Ŀ��;
$pcconfig["NEWS"] :ͳ��ȫվ��������/����ʱ��ʾ����Ŀ��;
$pcconfig["THEMLIST"] :���������ʱÿ��������ʾ��Blog��;
$pcconfig["SITE"] :վ�������,��blog��ʾ,RSS����о�Ҫ�õ�;
$pcconfig["BOARD"] :Blog��Ӧ�İ�������,�ð������Ĭ��ΪBlog����Ա;
$pcconfig["SEARCHFILTER"] :������������ʱ���˵�������;
$pcconfig["SEARCHNUMBER"] :���������������ʱÿҳ��ʾ����Ŀ��;
$pcconfig["SECTION"] :Blog���෽ʽ;
pc_personal_domainname($userid)���� :�û�Blog������;
*/
$pcconfig["LIST"] = 20;
$pcconfig["HOME"] = BBS_HOME;
$pcconfig["BBSNAME"] = BBS_FULL_NAME;
$pcconfig["ETEMS"] = 20;
$pcconfig["NEWS"] = 20;
$pcconfig["THEMLIST"] = 50;
$pcconfig["SITE"] = "www.smth.edu.cn";
$pcconfig["BOARD"] = "SMTH_blog";
$pcconfig["SEARCHFILTER"] = " ��";
$pcconfig["SEARCHNUMBER"] = 10;
$pcconfig["ADMIN"] = "SYSOP";
$pcconfig["SECTION"] = array(
			"personal" => "���˿ռ�" ,
			"literature" => "ԭ����ѧ" ,
			"computer" => "���Լ���" ,
			"feeling" => "��еش�" ,
			"collage" => "�ഺУ԰" ,
			"learning" => "ѧ����ѧ" ,
			"amusement" => "��������" ,
			"travel" => "�۹�����" ,
			"literae" => "�Ļ�����" ,
			"community" => "�����Ϣ" ,
			"game" => "��Ϸ��԰" ,
			"sports" => "��������" ,
			"publish" => "���ų���" ,
			"others" => "�������"
			);

function pc_personal_domainname($userid)
{
	return "http://".$userid.".mysmth.net";	
}
/* personal corp. configure end */
?>