<?php

require("inc/funcs.php");
require("inc/user.inc.php");
require("inc/board.inc.php");

global $boardName;
global $boardArr;

setStat("��������");

show_nav();

preprocess();

showUserMailBoxOrBR();

if ($boardName!='') 
	board_head_var($boardArr['DESC'],$boardName,$boardArr['SECNUM']);
else {
	head_var();
}

showSearchMenu();

show_footer();

function preprocess(){
	global $boardID;
	global $boardName;
	global $currentuser;
	global $boardArr;
	if (!isset($_GET['boardName'])) {
		return true;
	}
	$boardName=$_GET['boardName'];
	$brdArr=array();
	$boardID= bbs_getboard($boardName,$brdArr);
	$boardArr=$brdArr;
	$boardName=$brdArr['NAME'];
	if ($boardID==0) {
		$boardName='';
		return true;
	}
	$usernum = $currentuser["index"];
	if (bbs_checkreadperm($usernum, $boardID) == 0) {
		$boardName='';
		return true;
	}
	return true;
}

function showSearchMenu(){
	global $section_names;
	global $sectionCount;
	global $section_nums;
	global $yank;
	global $currentuser;
	$allow_multi_query = isMultiQueryAllowed();
	if (isset($_GET["boardName"])) $s_board = $_GET["boardName"];
	else $s_board = "";
?>
<form action=queryresult.php method=post name=oForm id=oForm onsubmit="return doSubmit();">
	<table cellpadding=5 cellspacing=1 align=center class=TableBorder1>
		<tr>
			<th valign=middle colspan=2 >��̳����</th>
		</tr>
		<tr>
			<td class=TableBody2 valign=middle align=center><b>��ѡ��Ҫ�����İ���</b></td>
			<td class=TableBody2 valign=middle align=center><b>����ѡ�� (����ѡ���ֻ���¥����)</b></td>
		</tr>
		<tr>
			<td class=TableBody1 valign=middle align=center rowspan="6">
				<table cellpadding=5 cellspacing=0 align=center class=TableBorder1><tr>
					<td class=TableBody1 valign=middle align="<?php echo ($allow_multi_query?"right":"center"); ?>">
						<select name=boardName id=boardName size=12<?php if ($allow_multi_query) echo " multiple"; ?>>
<?php
	$selectedIndex = $j = -1;
	for ($i=0;$i<$sectionCount;$i++){
		$boards = bbs_getboards($section_nums[$i], 0, $yank | 2 | 4);
		if ($boards != FALSE) {
			$brd_desc = $boards["DESC"]; // ��������
			$brd_name = $boards["NAME"];
			$brd_flag = $boards["FLAG"];
			$rows = sizeof($brd_desc);
			echo "<OPTGROUP LABEL=\"".$section_names[$i][0]."\">";
			for ($t = 0; $t < $rows; $t++)	{
			    if ($brd_flag[$t] & BBS_BOARD_GROUP) continue;
				$j++;
				$isSelected = ($s_board==$brd_name[$t]);
				echo "<option value=\"".$brd_name[$t]."\"".(($isSelected)?" selected=\"selected\"":"").">".$brd_desc[$t]."</option>";
				if ($isSelected) $selectedIndex = $j;
			}
			echo "</OPTGROUP>";
		}
	}
?>
						</select>
<?php
    if (isSelfMultiQueryAllowed()) {
?>
<br/>
<script language="JavaScript">
<!--
    function toogleQuerySelf(chk) {
        var b = chk.checked;
        var au = getRawObject("userid");
        getRawObject("boardName").disabled = b;
        if (au != null) {
            if (b) au.value = "<?php echo $currentuser["userid"]; ?>";
            au.disabled = b;
        }
    }
//-->
</script>
<input type="checkbox" onclick="toogleQuerySelf(this)" name="querySelf" id="querySelf" value="1"><label style="cursor:hand;" for="querySelf">ȫվ��ѯ�Լ����������</label>
<?php
    }
?>
					</td>
<script language="JavaScript">
<!--
<?php
	if ($selectedIndex >= 0) {
?>
	function kickBrowser() {
		c = getRawObject("boardName").options[<?php echo $selectedIndex; ?>];
		c.selected = false;
		c.selected = true;
	}
	window.onload = kickBrowser; // Force browser to highlight selection. Opera has no problem, but firefox and IE do need this. - atppp
<?php
	}
?>
	function doSelect(mode) {
		c = getRawObject("boardName");
		o = c.options;
		for (i = 0; i < o.length; i++) {
			if (!mode)
				o[i].selected = !o[i].selected;
			else
				o[i].selected = (mode==1);
		}
	}
	function doSubmit() { //���������� <select name=boardName[] ���� PHP �Զ��������飬�������˰ɡ�- atppp
		objForm = getRawObject("oForm");
		objBDs = getRawObject("boardNames");
		objBDs.value = "";
		c = getRawObject("boardName");
		o = c.options;
		objSelf = getRawObject("querySelf");
		if ((objSelf != null) && (objSelf.checked)) { //self-query
		    for (i = 0; i < o.length; i++) {
		        o[i].selected = false;
		    }
		    objForm.method = "GET";
		    return true;
		}
		first = true; second = false;
		for (i = 0; i < o.length; i++) {
			if (o[i].selected) {
				o[i].selected = false;
				if (first) {
					first = false;
				} else {
					objBDs.value += ',';
					second = true;
				}
				objBDs.value += o[i].value;
			}
		}
		if (first) {
			alert("������ѡ��һ����������ѯ��");
			return false;
		} else {
			if (!second) objForm.method = "GET"; //������������ GET
			return true; //objForm.submit()
		}
	}
//-->
</script>
<?php
	if ($allow_multi_query) {
?>
					<td class=TableBody1 valign=middle align=left>
						<ul>
							<li><p>Tip: ��ס CTRL ����ѡ<br>Tip: �϶�����ѡ</p></li>
							<li><p><a href="javascript:doSelect(1)">ȫ��ѡ��</a></p></li>
							<li><p><a href="javascript:doSelect(2)">ȫ����ѡ</a></p></li>
							<li><p><a href="javascript:doSelect(0)">��ѡ</a></p></li>
						</ul>
					</td>
<?php
	}
?>
				</tr></table>
			</td>
			<td class=TableBody1 valign=middle align=center>���⺬��: <input type="text" maxlength="50" size="20" name="title"> AND <input type="text" maxlength="50" size="20" name="title2"></td>
		</tr>
		<tr>
			<td class=TableBody1 valign=middle align=center>���ⲻ��: <input type="text" maxlength="50" size="20" name="title3"></td>
		</tr>
		<tr>
			<td class=TableBody1 valign=middle align=center>�����ʺ�: <input type="text" maxlength="12" size="12" name="userid"></td>
		</tr>
		<tr>
			<td class=TableBody1 valign=middle align=center>���ظ�: <input type="text" maxlength="4"  size="4"  name="dt" value="7"> ������</td>
		</tr>
		<tr>
			<td class=TableBody1 valign=middle align=center>
				<input type="checkbox" name="mg" id="mg"><label style="cursor:hand;" for="mg">��������</label>&nbsp;&nbsp;
				<input type="checkbox" name="ag" id="ag"><label style="cursor:hand;" for="ag">����������</label>&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class=TableBody2 valign=middle align=center>
				<input type="hidden" name="boardNames" id="boardNames">
				<input type=submit value=��ʼ����>
			</td>
		</tr>
	</table>
</form>
<?php
}
?>
