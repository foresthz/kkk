<?php
	$needlogin=0;
	require("inc/funcs.php");
	setStat("���û�ע��");
	show_nav();
	@$action=$_POST['action'];
	if ($action=='apply') {
		setStat("��д����");
		head_var("���û�ע��",'',1);
		do_apply();
	} elseif ($action=='save') {
		setStat("�ύע��");
		head_var("���û�ע��",'',1);
		do_save();
	} else {
		setStat("ע��Э��");
		head_var("���û�ע��",'',1);
		do_show();
	}

show_footer();

function do_show() {
?>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
    <tr><th align=center><form action="<?php echo $_SERVER['PHP_SELF'] ?>" method=post>�������������</td></tr>
	<input type="hidden" name="action" value="apply">
    <tr><td class=TableBody1 align=left>
<?php	require("inc/reg_txt.php") ; ?>
	</td></tr>
    <tr><td align=center class=TableBody2><input type=submit value=��ͬ��></td></form></tr>
</table>
<?php


}

function do_apply(){
	global $SiteName;
	require "inc/userdatadefine.inc.php";

?>

<form method=post action="<?php echo $_SERVER['PHP_SELF'] ?>" name="theForm">
<input type="hidden" name="action" value="save">
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<thead>
<Th colSpan=2 height=24><?php echo $SiteName; ?> -- ���û�ע��</Th>
</thead>
<TBODY> 
<TR> 
<TD width=40% class=TableBody1><B>����</B>��<BR>2-12�ַ�������Ӣ����ĸ�����֣����ַ���������ĸ</TD>
<TD width=60%  class=TableBody1> 
<input name=userid size=12 maxlength=12></TD>
</TR>
<TR> 
<TD width=40% class=TableBody1><B>����</B>��<BR>���������룬5-39�ַ������ִ�Сд��<BR>
�벻Ҫʹ���κ����� '*'��' ' �� HTML �ַ�</TD>
<TD width=60%  class=TableBody1> 
<input type=password name=pass1 size=12 maxlength=12></TD>
</TR>
<TR> 
<TD width=40% class=TableBody1><B>����</B>��<BR>������һ��ȷ��</TD>
<TD width=60%  class=TableBody1> 
<input type=password name=pass2 size=12 maxlength=12></TD>
</TR>
<TR> 
<TD width=40% class=TableBody1><B>��֤��</B>������������ͼƬ���ַ���<br>
<IMG src="img_rand/img_rand.php"></TD>
<TD width=60%  class=TableBody1> 
<input type=text name=validCode size=12 maxlength=12></TD>
</TR>
<TR> 
<TD width=40% class=TableBody1><B>�ǳ�</B>��<BR>����BBS�ϵ��ǳƣ�2-39�ַ�����Ӣ�Ĳ���</TD>
<TD width=60%  class=TableBody1> 
<input name=username size=20 maxlength=32></TD>
</TR>
<TR> 
<TD width=40% class=TableBody1><B>��ʵ����</B>��<BR>��������, ����2������</TD>
<TD width=60%  class=TableBody1> 
<input name=realname size=20></TD>
</TR>
<TR> 
<TD width=40% class=TableBody1><B>�Ա�</B>��<BR>��ѡ�������Ա�</TD>
<TD width=60%  class=TableBody1> <INPUT type=radio CHECKED value=1 name=gender>
<IMG  src=pic/Male.gif align=absMiddle>�к� &nbsp;&nbsp;&nbsp;&nbsp; 
<INPUT type=radio value=2 name=gender>
<IMG  src=pic/Female.gif align=absMiddle>Ů��</font></TD>
</TR>
<TR> 
<TD width=40% class=TableBody1><B>Email</B>��<BR>������Ч�����ʼ���ַ</TD>
<TD width=60%  class=TableBody1> 
<input name=email size=40></TD>
</TR>
<TR> 
<TD width=40% class=TableBody1><B>ѧУϵ��������λ</B>��<BR>�������ģ�����6���ַ�</TD>
<TD width=60%  class=TableBody1> 
<input name=dept size=40></TD>
</TR>
<TR> 
<TD width=40% class=TableBody1><B>��ϸͨѶ��ַ</B>��<BR>�������ģ�����6���ַ�</TD>
<TD width=60%  class=TableBody1> 
<input name=address size=40></TD>
</TR>
<TR> 
<TD width=40% class=TableBody1><B>����绰</B>��<BR>��������绰����д������</TD>
<TD width=60%  class=TableBody1> 
<input name=phone size=40> </TD>
</TR>
<TR> 
<TD width=40% class=TableBody1><B>�ֻ�</B>��<BR>�����ֻ����루���û�п��Բ��</TD>
<TD width=60%  class=TableBody1> 
<input name=mobile size=40>&nbsp; <input type=button value='����ʺ�' name=Button onclick=gopreview() disabled> </TD>
</TR>
</table>
 <table cellpadding=3 cellspacing=1 align=center class=TableBorder1 id=adv style="DISPLAY: none">
<TBODY> 
<TR align=middle> 
<Th colSpan=2 height=24 align=left>��д��ϸ����</TD>
</TR>
<TR> 
<TD width=40%  class=TableBody1><B>ͷ��</B>��<BR>ѡ���ͷ�񽫳������������Ϻͷ���������У�<br>��ע��֮���������ڻ��������޸����ϴ��Զ���ͷ��</TD>
<TD width=60%  class=TableBody1> 
<select name=face id=face size=1 onChange="document.images['imgmyface'].src='userface/image'+options[selectedIndex].value+'.gif';" style="BACKGROUND-COLOR: #99CCFF; BORDER-BOTTOM: 1px double; BORDER-LEFT: 1px double; BORDER-RIGHT: 1px double; BORDER-TOP: 1px double; COLOR: #000000">
<?php 
	for ($i=1;$i<=USERFACE_IMG_NUMS;$i++) {
		echo "<option value=\"".$i."\">image".$i.".gif</option>";
}
?>
</select>
<img id=imgmyface src=userface/image1.gif>&nbsp;<a href="javascript:openScript('showallfaces.php',500,400)">�鿴����ͷ��</a>
</TR>
<tr>    
<td width=40%  class=TableBody1><B>����</B><BR>�粻����д����ȫ������</td>   
<td width=60%  class=TableBody1 valign=center>
<input maxlength="4" size="4" name="year" /> �� <input maxlength="2" size="2" name="month" /> �� <input size="2" maxlength="2" name="day" /> ��
</td>   
</tr>
<tr> 
<td width=40%  class=TableBody1><B>�ظ���ʾ</B>��<BR>����������������˻ظ�ʱ��ʹ����̳��Ϣ֪ͨ����</td>
<td width=60%  class=TableBody1>
<input type=radio name=showRe value=1 checked>
��ʾ��
<input type=radio name=showRe value=0>
����ʾ
</tr>

<TR> 
<TD width=40% class=TableBody1><B>����</B>��<BR>����������ѡ��Ҫ���������</TD>
<TD width=60% class=TableBody1> 
<select name=groupname>
<?php 
	for($i=0;$i<count($groups);$i++) {
		echo "<option value=\"".$i."\">".$groups[$i]."</option>";
	}
?>
</select>
</TD>
</TR>

<TR> 
<TD width=40%  class=TableBody1><B>OICQ����</B>��<BR>��д����QQ��ַ�����������˵���ϵ</TD>
<TD width=60%  class=TableBody1> 
<INPUT maxLength=20 size=44 name=OICQ>
</TD>
</TR>
<TR> 
<TD width=40%  class=TableBody1><B>ICQ����</B>��<BR>��д����ICQ��ַ�����������˵���ϵ</font></TD>
<TD width=60%  class=TableBody1> 
<INPUT maxLength=20 size=44 name=ICQ>
</TD>
</TR>
<TR > 
<TD width=40%  class=TableBody1><B>MSN</B>��<BR>��д����MSN��ַ�����������˵���ϵ</TD>
<TD width=60%  class=TableBody1> 
<INPUT maxLength=70 size=44 name=MSN>
</TD>
</TR>
<TR > 
<TD width=40%  class=TableBody1><B>��ҳ</B>��<BR>��д���ĸ�����ҳ��ַ��չʾ�������Ϸ��</TD>
<TD width=60%  class=TableBody1> 
<INPUT maxLength=70 size=44 name=homepage>
</TD>
</TR>
<TR> 
<TD width=40%  class=TableBody1><B>ǩ����</B>��<BR>���300�ֽ�<BR>
���ֽ�����������������µĽ�β�����������ĸ��ԡ� </TD>
<TD width=60%  class=TableBody1> 
<TEXTAREA name=Signature rows=5 wrap=PHYSICAL cols=60></TEXTAREA>
</TD>
</TR>
<tr>
<th height=25 align=left valign=middle colspan=2><b>&nbsp;������ʵ��Ϣ</b>���������ݽ�����д��</th>
</tr>
<tr>
<td valign=top  class=TableBody1 width=40% >��<b>�������ң�</b>
<b>
<input type=text name=country size=18>
</b> </td>
<td height=71 align=left valign=top  class=TableBody1 rowspan=14 width=60% >
<table width=100% border=0 cellspacing=0 cellpadding=5>
<tr>
<td class=TableBody1><b>�ԡ��� &nbsp; </b>
<br>
<?php
	for ($i=1;$i<count($character);$i++) {
		echo "<input type=\"checkbox\" name=\"character\" value=\"".$i."\" >".$character[$i];
		if ($i % 5 ==0) {
			echo "<br>";
		}

	}
?>
 </td>
</tr>
<tr>
<td class=TableBody1><b>���˼�飺 &nbsp;</b><br>
<textarea name=personal rows=6 cols=90% ></textarea>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td valign=top  class=TableBody1 width=40% >��<b>ʡ�����ݣ�</b>
<input type=text name=province size=18>
</td>
</tr>
<tr>
<td valign=top  class=TableBody1 width=40% >��<b>�ǡ����У�
</b>
<input type=text name=city size=18>
</td>
</tr>
<tr>
<td valign=top  class=TableBody1 width=40% >��<b>������Ф��
</b>
<select size=1 name=shengxiao>
<?php
	for ($i=0;$i<count($shengxiao);$i++) {
		echo "<option value=\"".$i."\">".$shengxiao[$i]."</option>";
	}
?>
</select>
</td>
</tr>
<tr>
<td valign=top  class=TableBody1 width=40% >��<b>Ѫ�����ͣ�</b>
<select size=1 name=blood>
<?php
	for($i=0;$i<count($bloodtype);$i++){
		echo "<option value=\"".$i."\">".$bloodtype[$i]."</option>";
	}
?>
</select>
</td>
</tr>
<tr>
<td valign=top  class=TableBody1 width=40% >��<b>�š�������</b>
<select size=1 name=belief>
<?php
	for($i=0;$i<count($religion);$i++){
		echo "<option value=\"".$i."\">".$religion[$i]."</option>";
	}
?>
</select></td>
</tr>
<tr>
<td valign=top class=TableBody1 width=40% >��<b>ְ����ҵ�� </b>
<select name=occupation>
<?php
	for($i=0;$i<count($profession);$i++){
		echo "<option value=\"".$i."\">".$profession[$i]."</option>";
	}
?>
</select></td>
</tr>
<tr>
<td valign=top class=TableBody1 width=40% >��<b>����״����</b>
<select size=1 name=marital>
<?php
	for($i=0;$i<count($married);$i++){
		echo "<option value=\"".$i."\">".$married[$i]."</option>";
	}
?>
</select></td>
</tr>
<tr>
<td valign=top class=TableBody1 width=40% >��<b>���ѧ����</b>
<select size=1 name=education>
<?php
	for($i=0;$i<count($graduate);$i++){
		echo "<option value=\"".$i."\">".$graduate[$i]."</option>";
	}
?>
</select></td>
</tr>
<tr>
<td valign=top class=TableBody1 width=40% >��<b>��ҵԺУ��</b>
<input type=text name=college size=18></td>
</tr>
</TBODY> 
</TABLE>
</td></tr></table>
<table cellpadding=0 cellspacing=0 border=0 width=97% align=center>
<tr>
<td width=50% height=24>
	<INPUT id=advshow name=advshow type=checkbox value=1 onclick=showadv()><span name=advance id=advance>��ʾ�߼��û�����ѡ��</span>
</td>
<td width=50% ><input type=submit value=�ύ���>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type=reset value=������д></td>
</tr></table>
</form>
<form name=preview action=chkreg.php method=post target=preview_page>
<input type=hidden name=userid value=>
</form>
<script language="javascript">
function gopreview()
{
document.preview.userid.value=document.theForm.userid.value;
var popupWin = window.open('', 'preview_page', 'scrollbars=yes,width=500,height=300');
document.preview.submit();
}
function showadv(){
if (document.theForm.advshow.checked == true) {
	getRawObject("adv").style.display = "";
	//getRawObject("advance").innerHTML="�رո߼��û�����ѡ��"; //�����仰��ʵ�����е��Ǻ���ûʲô�÷���������⣬��ȥ���ѡ�- atppp
}else{
	getRawObject("adv").style.display = "none";
	//getRawObject("advance").innerHTML="��ʾ�߼��û�����ѡ��";
}
}
</script>


<?php
}

function do_save(){
	global $SiteName;
	@$userid=$_POST["userid"];
	@$pass1=$_POST["pass1"];
	@$pass2=$_POST["pass2"];
	@$nickname=$_POST["username"];

	@$realname=$_POST["realname"];
	@$dept=$_POST["dept"];
    @$address=$_POST["address"];
	@$year=$_POST["year"];
	@$month=$_POST["month"];
	@$day=$_POST["day"];
	@$email=$_POST["email"];
	@$phone=$_POST["phone"];
	@$mobile_phone=$_POST["mobile"];
	@$gender=$_POST["gender"];


    if(!isset($_SESSION['num_auth']))
		foundErr("��ȴ���֤��ͼƬ��ʾ��ϣ�");
    if(strcasecmp($_SESSION['num_auth'],$_POST['validCode']))
        foundErr("���������֤�����");
	if(strcmp($pass1,$pass2))
		foundErr("�����������벻һ��");
	else if(strlen($pass1) < 5 || !strcmp($pass1,$userid))
       	foundErr("���볤��̫�̻��ߺ��û�����ͬ!");
	$ret=bbs_createnewid($userid,$pass1,$nickname);
	switch($ret)
	{
	case 0:
			break;
	case 1:
			foundErr("�û����з�������ĸ�ַ��������ַ�������ĸ!");
			break;
	case 2:
			foundErr("�û�������Ϊ������ĸ!");
			break;
	case 3:
			foundErr("ϵͳ���ֻ�������!");
			break;
	case 4:
			foundErr("���û����Ѿ���ʹ��!");
			break;
	case 5:
			foundErr("�û���̫��,�12���ַ�!");
			break;
	case 6:
			foundErr("����̫��,�39���ַ�!");
			break;
	case 10:
			foundErr("ϵͳ����,����ϵͳ����ԱSYSOP��ϵ.");
			break;
	default:
			foundErr("ע��IDʱ����δ֪�Ĵ���!");
			break;
	}
	if($gender!='1')$gender=2;
    settype($year,"integer");
	settype($month,"integer");
	settype($day,"integer");

$ret=bbs_createregform($userid,$realname,$dept,$address,$gender,$year,$month,$day,$email,$phone,$mobile_phone, $_POST['OICQ'], $_POST['ICQ'], $_POST['MSN'],  $_POST['homepage'], intval($_POST['face']), '', 0, 0, intval($_POST['groupname']), $_POST['country'],  $_POST['province'], $_POST['city'], intval($_POST['shengxiao']), intval($_POST['blood']), intval($_POST['belief']), intval($_POST['occupation']), intval($_POST['marital']), intval($_POST['education']), $_POST['college'], intval($_POST['character']), FALSE);//�Զ�����ע�ᵥ

	switch($ret)
	{
	case 0:
		break;
	case -1:
		foundErr("�û��Զ���ͼ���ȴ���");
		break;
	case -2:
		foundErr("�û��Զ���ͼ��߶ȴ���");
		break;
	case 2:
		foundErr("���û�������!");
		break;
	case 3:
		foundErr("��������");
		break;
	default:
		foundErr("δ֪�Ĵ���!");
		break;
	}
	$signature=trim($_POST["Signature"]);
	if ($signature!='') {
		$filename=bbs_sethomefile($userid,"signatures");
		$fp=@fopen($filename,"w+");
		if ($fp!=false) {
			fwrite($fp,str_replace("\r\n", "\n", $signature));
			fclose($fp);
			bbs_recalc_sig();
		}
	}
	$personal=trim($_POST["personal"]);
	if ($signature!='') {
		$filename=bbs_sethomefile($userid,"plans");
		$fp=@fopen($filename,"w+");
		if ($fp!=false) {
			fwrite($fp,str_replace("\r\n", "\n", $personal));
			fclose($fp);
		}
	}
?>
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr>
<th height=24>ע�ᵥ�ѳɹ���<?php echo $SiteName; ?>��ӭ���ĵ���</th>
</tr>
<tr><td class=TableBody1><br>
<ul>
<li>�����ڻ�û��ͨ�������֤��,ֻ���������Ȩ�ޣ����ܷ��ġ����š�����ȡ�</li>
<li>ϵͳ���Զ�����ע�ᵥ����վ�����ͨ����,�㽫��úϷ��û�Ȩ�ޣ�</li>
<li><a href="index.php">����������</a></li></ul>
</td></tr>
</table>
<?php
}

?>
