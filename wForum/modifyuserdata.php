<?php
require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");
require_once("inc/myface.inc.php");

setStat("���������޸�");

requireLoginok();

show_nav();

showUserMailbox();
head_var($userid."�Ŀ������","usermanagemenu.php",0);
showUserManageMenu();
main();

show_footer();

function main(){
	global $currentuser;
	require("inc/userdatadefine.inc.php");

?>
<br>
<script language="JavaScript">
	function setimg(i) {
		o = document.images['imgmyface'];
		o.src = 'userface/image' + (i + 1) + '.gif';
		o.width = 32;
		o.height = 32;
		getRawObject('myface').value = '';
	}
</script>
<form action="saveuserdata.php" method=POST name="theForm">
<table cellpadding=3 cellspacing=1 border=0 align=center class=TableBorder1>
<tr> 
      <th colspan="2" width="100%">��������ѡ��
      </th>
    </tr> 
<TR> 
<TD width=40% class=TableBody1><B>��&nbsp;&nbsp;&nbsp;&nbsp;��</B>��<BR>��ѡ�������Ա�</TD>
<TD width=60%  class=TableBody1> <INPUT type=radio <?php if (chr($currentuser['gender'])=='M') echo "checked"; ?> value=1 name=gender>
<IMG  src=pic/Male.gif align=absMiddle>�к� &nbsp;&nbsp;&nbsp;&nbsp; 
<INPUT type=radio value=2 <?php if (chr($currentuser['gender'])=='F') echo "checked"; ?> name=gender>
<IMG  src=pic/Female.gif align=absMiddle>Ů��</font></TD>
</tr>
<tr>    
<td width=40%  class=TableBody1><B>����</B><BR>�粻����д����ȫ������</td>   
<td width=60%  class=TableBody1 valign=center>
<input maxlength="4" size="4" name="year" value="<?php echo '19'.$currentuser['birthyear']; ?>" /> �� <input maxlength="2" size="2" name="month" value="<?php echo $currentuser['birthmonth']; ?>"/> �� <input size="2" maxlength="2" name="day" value="<?php echo $currentuser['birthday']; ?>"/> ��
</td>   
</tr>

 <TR> 
<TD width=40%  class=TableBody1><B>ͷ��</B>��<BR>ѡ���ͷ�񽫳������������Ϻͷ���������У���Ҳ����ѡ��������Զ���ͷ��</TD>
<TD width=60%  class=TableBody1> 
<select name=face id=face size=1 onChange="setimg(selectedIndex)" style="BACKGROUND-COLOR: #99CCFF; BORDER-BOTTOM: 1px double; BORDER-LEFT: 1px double; BORDER-RIGHT: 1px double; BORDER-TOP: 1px double; COLOR: #000000">
<?php 
	for ($i=1;$i<=USERFACE_IMG_NUMS;$i++) {
		echo "<option value=\"".$i."\"";
		if ($i==$currentuser['userface_img']) {
			echo " selected ";
		}
		echo ">image".$i.".gif</option>";
	}
?>
</select>
&nbsp;<a href="javascript:openScript('showallfaces.php',500,400)">�鿴����ͷ��</a>
</tr>

<tr> 
<td width="40%" valign="top" class="TableBody1"><b>�Զ���ͷ��</b>��<br/>���ͼ��λ����������ͼƬ�����Զ����Ϊ��</td>
<td width="60%" class="TableBody1">
<?php
	if (USER_FACE) {
?>
<iframe name="ad" frameborder="0" width="100%" height="24" scrolling="no" src="postface.php"></iframe>
<?php
	}
?>
<table width="100%"><tr><td>
ͼ��λ�ã� 
<input type="text" name="myface" id="myface" size="60" maxlength="100" value="<?php echo htmlEncode($currentuser['userface_url']); ?>" />
&nbsp;����Url��ַ<br>
��&nbsp;&nbsp;&nbsp;&nbsp;�ȣ� 
<input type="text" name="width" id="width" size="3" value="<?php echo $currentuser['userface_width'];  ?>" />
0---120������<br>
��&nbsp;&nbsp;&nbsp;&nbsp;�ȣ� 
<input type="text" name="height" id="height" size="3" value="<?php echo $currentuser['userface_height'];  ?>" />
0---120������<br>
</td><td align="right"><?php echo get_myface($currentuser, "id=\"imgmyface\""); ?></td></tr></table>
</td></tr>
<tr>    
<td width="40%" class="TableBody1"><b>������Ƭ</b>��<br/>���������Ƭ�����ϣ���������ҳ��ַ�������ѡ</td>   
<td width="60%" class="TableBody1">    
<input type="text" name="userphoto" value="<?php echo $currentuser['photo_url']; ?>" size="30" maxlength="100"/>   
</td>   
</tr>   
<tr> 
<td width="40%" class="TableBody1"><b>����</b>��<br/>����������ѡ��Ҫ���������</td>
<td width="60%" class="TableBody1"> 
<select name="groupname">
<?php 
	for($i=0;$i<count($groups);$i++) {
		echo "<option value=\"".$i."\"";
		if ($currentuser['groups']==$i){
			echo " selected ";
		}
		echo ">".$groups[$i]."</option>";
	}
?>
</select>
</td>
</tr>
<TR> 
<TD width=40%  class=TableBody1><B>OICQ����</B>��<BR>��д����QQ��ַ�����������˵���ϵ</TD>
<TD width=60%  class=TableBody1> 
<INPUT maxLength=20 size=44 name=OICQ value="<?php echo htmlEncode($currentuser['OICQ']); ?>">
</TD>
</TR>
<TR> 
<TD width=40%  class=TableBody1><B>ICQ����</B>��<BR>��д����ICQ��ַ�����������˵���ϵ</font></TD>
<TD width=60%  class=TableBody1> 
<INPUT maxLength=20 size=44 name=ICQ value="<?php echo htmlEncode($currentuser['ICQ']); ?>">
</TD>
</TR>
<TR > 
<TD width=40%  class=TableBody1><B>MSN</B>��<BR>��д����MSN��ַ�����������˵���ϵ</TD>
<TD width=60%  class=TableBody1> 
<INPUT maxLength=70 size=44 name=MSN value="<?php echo htmlEncode($currentuser['MSN']); ?>">
</TD>
</TR>
<TR > 
<TD width=40%  class=TableBody1><B>��ҳ</B>��<BR>��д���ĸ�����ҳ��ַ��չʾ�������Ϸ��</TD>
<TD width=60%  class=TableBody1> 
<INPUT maxLength=70 size=44 name=homepage value="<?php echo htmlEncode($currentuser['homepage']); ?>">
</TD>
</TR>
<TR> 
<TD width=40% class=TableBody1><B>Email</B>��<BR>������Ч�����ʼ���ַ</TD>
<TD width=60%  class=TableBody1> 
<input name=email size=40 value="<?php echo htmlEncode($currentuser['reg_email']); ?>"></TD>
</TR>
      <tr>    
        <td valign=top width="40%" class=TableBody1><B>ǩ&nbsp;&nbsp;&nbsp;&nbsp;��</B>��<BR>���ܳ��� 250 ���ַ�   
          <br>   
          ���ֽ�����������������µĽ�β����</td>   
        <td width="60%" class=TableBody1>    
          <textarea name="Signature" rows=5 cols=60 wrap=PHYSICAL>
<?php
	$filename=bbs_sethomefile($currentuser["userid"],"signatures");
    $fp = @fopen ($filename, "r"); //ToDo: �����ȫ������һ�� PHP �������������滹��һ��
    if ($fp!=false) {
		while (!feof ($fp)) {
			$buffer = fgets($fp, 300);
			echo htmlspecialchars($buffer);
		}
		fclose ($fp);
    }
?></textarea>   
        </td>   
      </tr>
<tr>
<th height=25 align=left valign=middle colspan=2 align="center">&nbsp;������ʵ��Ϣ���������ݽ�����д��</th>
</tr>
<tr>
<td valign=top width=40% class=TableBody1> ��<b>��ʵ������</b>
<input type=text name=realname size=18 value="<?php echo htmlEncode($currentuser['realname']); ?>">
</td>
<td height=71 align=left valign=top  class=TableBody1 rowspan=14 width=60% >
<table width=100% border=0 cellspacing=0 cellpadding=5>
<tr>
<td class=TableBody1><b>�ԡ��� &nbsp; </b>
<br>
<?php
	for ($i=1;$i<count($character);$i++) {
		echo "<input type=\"checkbox\" name=\"character\" value=\"".$i."\" ";
		if ($i==$currentuser['character']) {
			echo " checked ";
		}
		echo " >".$character[$i];
		if ($i % 5 ==0) {
			echo "<br>";
		}

	}
?>
 </td>
</tr>
<tr>
<td class=TableBody1><b>���˼�飺 &nbsp;</b><br>
<textarea name=personal rows=6 cols=90% >
<?php
	$filename=bbs_sethomefile($currentuser["userid"],"plans");
    $fp = @fopen ($filename, "r");
    if ($fp!=false) {
		while (!feof ($fp)) {
			$buffer = fgets($fp, 300);
			echo htmlspecialchars($buffer);
		}
		fclose ($fp);
    }

?></textarea>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td valign=top width=40%  class=TableBody1>��<b>�������ң�</b>
<b>
<input type=text name=country size=18 value="<?php echo htmlEncode($currentuser['country']); ?>">
</b> </td>
</tr>
<tr>
<td valign=top width=40% class=TableBody1>��<b>ʡ�����ݣ�</b>
<input type=text name=province size=18 value="<?php echo htmlEncode($currentuser['province']); ?>">
</td>
</tr>
<tr>
<td valign=top width=40% class=TableBody1>��<b>�ǡ����У�</b>
<input type=text name=city size=18 value="<?php echo htmlEncode($currentuser['city']); ?>">
</td>
</tr>
<tr>
<td valign=top width=40%  class=TableBody1>��<b>��ϵ�绰��</b>
<b>
<input type=text name=userphone size=18 value="<?php echo htmlEncode($currentuser['telephone']); ?>">
</b> </td>
</tr>
<tr>
<td valign=top width=40% class=TableBody1>��<b>ͨ�ŵ�ַ��</b>
<b>
<input type=text name=address size=18 value="<?php echo htmlEncode($currentuser['address']); ?>">
</b> </td>
</tr>
<tr>
<td valign=top width=40% class=TableBody1>��<b>������Ф��
</b>
<select size=1 name=shengxiao>
<?php
	for ($i=0;$i<count($shengxiao);$i++) {
		echo "<option value=\"".$i."\"";
		if ($i==$currentuser['shengxiao']) {
			echo " selected ";
		}
		echo ">".$shengxiao[$i]."</option>";
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
		echo "<option value=\"".$i."\"";
		if ($i==$currentuser['bloodtype']) {
			echo " selected ";
		}
		echo ">".$bloodtype[$i]."</option>";
	}
?>
</select>
</td>
</td>
</tr>
<tr>
<td valign=top width=40% class=TableBody1>��<b>�š�������</b>
<select size=1 name=belief>
<?php
	for($i=0;$i<count($religion);$i++){
		echo "<option value=\"".$i."\"";
		if ($i==$currentuser['religion']) {
			echo " selected ";
		}
		echo ">".$religion[$i]."</option>";
	}
?>
</select></td>
</tr>
<tr>
<td valign=top width=40% class=TableBody1>��<b>ְ����ҵ�� </b>
<select name=occupation>
<?php
	for($i=0;$i<count($profession);$i++){
		echo "<option value=\"".$i."\"";
		if ($i==$currentuser['profession']) {
			echo " selected ";
		}
		echo ">".$profession[$i]."</option>";
	}
?>
</select></td>
</tr>
<tr>
<td valign=top width=40% class=TableBody1>��<b>����״����</b>
<select size=1 name=marital>
<?php
	for($i=0;$i<count($married);$i++){
		echo "<option value=\"".$i."\"";
		if ($i==$currentuser['married']) {
			echo " selected ";
		}
		echo ">".$married[$i]."</option>";
	}
?>
</select></td>
</tr>
<tr>
<td valign=top width=40% class=TableBody1>��<b>���ѧ����</b>
<select size=1 name=education>
<?php
	for($i=0;$i<count($graduate);$i++){
		echo "<option value=\"".$i."\"";
		if ($i==$currentuser['education']) {
			echo " selected ";
		}
		echo ">".$graduate[$i]."</option>";
	}
?>
</select></td>
</tr>
<tr>
<td valign=top width=40% class=TableBody1>��<b>��ҵԺУ��</b>
<input type=text name=college size=18 value="<?php echo htmlEncode($currentuser['graduateschool']); ?>"></td>
</tr>
<tr align="center"> 
<td colspan="2" width="100%" class=TableBody2>
<input type=Submit value="�� ��" name="Submit" id="oSubmit"> &nbsp; <input type="reset" name="Submit2" value="�� ��" id="oSubmit2">
</td></tr>  
</table></form>
<?php
}
?>
