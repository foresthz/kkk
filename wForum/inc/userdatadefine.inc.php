<?php
define("USERFACE_IMG_NUMS",60);
define("USERFACE_IMG_CUSTOM",-1);
define("USERFACE_IMG_URL",-2);
$groups=array("��������");
$shengxiao=array("δ֪","��","ţ","��","��","��","��","��","��","��","��","��","��");
$bloodtype=array("δ֪","A","B","O","AB","����");
$religion=array("δ֪","���", "����", "������","������", "�ؽ�" , "��������", "����������", "����");
$profession=array("δ֪" ,"ѧ��", "�ƻ�/����", "����ʦ", "����", "����������ҵ", "��ͥ����", "����/��ѵ",
"�ͻ�����/֧��","������/�ֹ�����", "����","��ְҵ","����/�г�/���","�о��Ϳ���","һ�����/�ල",
"����/����","ִ�й�/�߼�����","����/����/����","רҵ��Ա","�Թ�/ҵ��","����");
$married=array("δ֪","δ��","�ѻ�","����","ɥż");
$graduate=array("δ֪","Сѧ","����","����","��ѧ","�о���","��ʿ");
$character=array("δ֪","�����Ը�","������", "��������","���ɵ�Ƥ","��������","���ÿɰ�","����ͨͨ","������",
"������", "�ĵ�����", "��������", "�ƽ�����", "��Ȥ��Ĭ", "˼�뿪��","������ȡ", "С�Ľ���","�����ѻ�",
"������ֱ","����ʧ��", "�ó�����", "��������" ,"�����ɹ�","���û�ʧ","�����쿪", "����Ƹ�", "��������",
"��������", "հǰ�˺�", "ѭ�浸��", "��������", "���Կ���", "���Թ���","��������","׷��̼�","���Ų��",
"�ƻ����", "̰С����", "����˼Ǩ", "�������", "ˮ���ﻨ", "��ɫ����", "��С����", "��������","�¸�����",
"������ѧ", "ʵ������", "��ʵʵ��", "��ʵ�ͽ�", "Բ������", "Ƣ������", "����˹��", "��ʵ̹��","��������","������");

function get_astro($birthmonth, $birthday)
{
	if (($birthmonth==0) || ($birthday==0) ){
		return "<font color=gray>δ֪</font>";
	}
	$birth=$birthmonth.'��'.$birthday.'��';
	switch ($birthmonth){
	case 1:
		if ($birthday>=21)	{
			$function_ret="<img src=star/z11.gif alt=ˮƿ��".$birth."> ˮƿ��";
		}else{
			$function_ret="<img src=star/z10.gif alt=ħ����".$birth."> ħ����";
		} 
		break;
	case 2:
		if ($birthday>=20)	{
			$function_ret="<img src=star/z12.gif alt=˫����".$birth."> ˫����";
		}else{
			$function_ret="<img src=star/z11.gif alt=ˮƿ��".$birth."> ˮƿ��";
		} 
		break;
	case 3:
		if ($birthday>=21)	{
			$function_ret="<img src=star/z1.gif alt=������".$birth."> ������";
		}else{
			$function_ret="<img src=star/z12.gif alt=˫����".$birth."> ˫����";
		} 
		break;
	case 4:
		if ($birthday>=21)	{
			$function_ret="<img src=star/z2.gif alt=��ţ��".$birth."> ��ţ��";
		}else{
			$function_ret="<img src=star/z1.gif alt=������".$birth."> ������";
		} 
		break;
	case 5:
		if ($birthday>=22)	{
			$function_ret="<img src=star/z3.gif alt=˫����".$birth."> ˫����";
		}else{
			$function_ret="<img src=star/z2.gif alt=��ţ��".$birth."> ��ţ��";
		} 
		break;
	case 6:
		if ($birthday>=22)	{
			$function_ret="<img src=star/z4.gif alt=��з��".$birth."> ��з��";
		}else{
			$function_ret="<img src=star/z3.gif alt=˫����".$birth."> ˫����";
		} 
		break;
	case 7:
		if ($birthday>=23)	{
			$function_ret="<img src=star/z5.gif alt=ʨ����".$birth."> ʨ����";
		}else{
			$function_ret="<img src=star/z4.gif alt=��з��".$birth."> ��з��";
		} 
		break;
	case 8:
		if ($birthday>=24){
			$function_ret="<img src=star/z6.gif alt=��Ů��".$birth."> ��Ů��";
		}else{
			$function_ret="<img src=star/z5.gif alt=ʨ����".$birth."> ʨ����";
		} 
		break;
	case 9:
		if ($birthday>=24)	{
			$function_ret="<img src=star/z7.gif alt=�����".$birth."> �����";
		}else{
			$function_ret="<img src=star/z6.gif alt=��Ů��".$birth."> ��Ů��";
		} 
		break;
	case 10:
		if ($birthday>=24){
			$function_ret="<img src=star/z8.gif alt=��Ы��".$birth."> ��Ы��";
		}else{
			$function_ret="<img src=star/z7.gif alt=�����".$birth."> �����";
		} 
		break;
	case 11:
		if ($birthday>=23)	{
			$function_ret="<img src=star/z9.gif alt=������".$birth."> ������";
		}else{
			$function_ret="<img src=star/z8.gif alt=��Ы��".$birth."> ��Ы��";
		} 
		break;
	case 12:
		if ($birthday>=22)	{
			$function_ret="<img src=star/z10.gif alt=ħ����".$birth."> ħ����";
		}else{
			$function_ret="<img src=star/z9.gif alt=������".$birth."> ������";
		} 
		break;
	default:
		$function_ret="";
		break;
	} 
	return $function_ret;
}
 ?>