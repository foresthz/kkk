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
$pcconfig["MINREGTIME"] :����ʱҪ������ע��ʱ��;
$pcconfig["ADMIN"] :����ԱID�����ú����й���Ա������ά����Blog
$pcconfig["TMPSAVETIME"] :���������ݴ湦��ʱ�������ʱ������ ��λΪ��
pc_personal_domainname($userid)���� :�û�Blog������;
*/
$pcconfig["LIST"] = 100;
$pcconfig["HOME"] = BBS_HOME;
$pcconfig["BBSNAME"] = BBS_FULL_NAME;
$pcconfig["ETEMS"] = 20;
$pcconfig["NEWS"] = 100;
$pcconfig["THEMLIST"] = 50;
$pcconfig["SITE"] = "www.smth.edu.cn";
$pcconfig["BOARD"] = "SMTH_blog";
$pcconfig["APPBOARD"] = "BlogApply";
$pcconfig["SEARCHFILTER"] = " ��";
$pcconfig["SEARCHNUMBER"] = 10;
$pcconfig["ADMIN"] = "SYSOP";
$pcconfig["MINREGTIME"] = 6;
$pcconfig["TMPSAVETIME"] = 300;
$pcconfig["ALLCHARS"] = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
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
			"publish" => "ý������" ,
			"business" => "��ҵ����",
			"life" => "������Ѷ",
			"picture" => "ͼƬ����",
			"collection" => "�����ղ�",
			"others" => "�������"
			);

//��ҳ��ʾ��һЩ����
define("_PCMAIN_TIME_LONG_" , 259200 ); //��־ͳ��ʱ��
define("_PCMAIN_NODES_NUM_" , 20 );     //��ʾ����־��Ŀ
define("_PCMAIN_USERS_NUM_" , 20 );     //��ʾ���û���Ŀ
define("_PCMAIN_REC_NODES_" , 40 );     //�Ƽ���־��Ŀ
define("_PCMAIN_NEW_NODES_" , 40 );     //����־��Ŀ
define("_PCMAIN_ANNS_NUM_"  , 6  );     //������Ŀ
define("_PCMAIN_RECOMMEND_" , 1   );  //�����Ƽ�
define("_PCMAIN_RECOMMEND_BLOGGER_" , "SYSOP"); //�̶��Ƽ�
define("_PCMAIN_RECOMMEND_QUEUE_" , "smthblogger.php");        //ʹ���Ƽ�����

function pc_personal_domainname($userid)
{
	return "http://".$userid.".mysmth.net";	
}
/* personal corp. configure end */
?>