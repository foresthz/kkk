<?php

require("inc/funcs.php");
require("inc/user.inc.php");

setStat("վ�����ò���");

requireLoginok("��ҳ�����Ҫ����Ա��¼����ʹ�á�");

preprocess();

show_nav();

showUserMailBox();
head_var("վ������", 'admin.site_defines.php');
if (isset($_GET["action"])) {
	$action = $_GET["action"];
} else $action = "";
if ($action == "gen") {
	generate_site();
	show_site();
} else if ($action == "mv") {
	apply_change();
} else {
	show_conf();
}
show_footer();

function preprocess() {
	global $currentuser;
	global $curConf;
	global $bakConf;
	global $newConf;
	if (!($currentuser["userlevel"] & BBS_PERM_SYSOP)) {
		foundErr("��ҳ�����Ҫ����Ա��¼����ʹ�á�");
	}
	$topdir = dirname($_SERVER['SCRIPT_FILENAME']);
	$curConf = $topdir . "/inc/site.php";
	if (is_link($curConf)) $curConf = realpath($curConf); //readlink() returns relative path
	$bakConf = $topdir . "/inc/sites/site.bak.php";
	$newConf = $topdir . "/inc/sites/site.new.php";
}

function apply_change() {
	global $curConf;
	global $newConf;
	global $bakConf;
	@copy($curConf, $bakConf) or foundErr("���ݾ������ļ�����");
	@copy($newConf, $curConf) or foundErr("�����������ļ�����");
	setSucMsg("�������ļ���Ч��");
	return html_success_quit('����վ������', "admin.site_defines.php");
}

function write_var_php($var) {
	if (is_string($var)) {
		return "\"" . addcslashes($var, "\\\"") . "\""; //ע������ط������� addslashes()����Ϊ�����Ų����Ա� escape
	} else {
		return $var;
	}
}

function write_array_php($varname) { //������� array ��һ�� array������������ var_export() ���������ʽ��̫�ÿ�
	$var = $GLOBALS[$varname];
	$stat = "\$" . $varname . " = array(";
	$ccc = count($var);
	for ($i = 0; $i < $ccc; $i++) {
		if (is_array($var[$i])) {
			$stat .= "\n\tarray(";
			$cc = count($var[$i]);
			for ($j = 0; $j < $cc; $j++) {
				$stat .= write_var_php($var[$i][$j]);
				if ($j != $cc - 1) $stat .= ", ";
			}
			$stat .= ")";
			if ($i != $ccc - 1) $stat .= ",";
			else $stat .= "\n";
		} else {
			$stat .= write_var_php($var[$i]);
			if ($i != $ccc - 1) $stat .= ", ";
		}
	}
	$stat .= ");\n";
	return $stat;
}

function generate_site() {
	global $site_defines;
	global $newConf;
	
	$stat = "<?php\n/* automatically generated site configuration file */\n";
	$stat .= "\n/* ���������� */\n";
	$stat .= write_array_php("section_nums");
	$stat .= write_array_php("section_names");
	
	$stat .= "\n/* ����ĸ����Զ������ */\n";
	if (isset($GLOBALS["user_define_default"])) $stat .= write_array_php("user_define");
	if (isset($GLOBALS["user_define1_default"])) $stat .= write_array_php("user_define1");
	if (isset($GLOBALS["mailbox_prop_default"])) $stat .= write_array_php("mailbox_prop");
	
	$ccc = count($site_defines);
	for ($i = 0; $i < $ccc; $i++) {
		if ($site_defines[$i] === false) break;
		if (is_string($site_defines[$i])) {
			$stat .= "\n/* " . $site_defines[$i] . " */\n";
			continue;
		}
		$is_boolean = ($site_defines[$i][2] == "b");
		$is_string =  ($site_defines[$i][2] == "s");
		$is_integer =  ($site_defines[$i][2] == "i");
		$is_constant = ($site_defines[$i][0] == 0);
		$varname = $site_defines[$i][1];
		$default = $site_defines[$i][3];
		if (!isset($_POST["c$i"])) foundErr("������ȫ�����������á�");
		$cur = $_POST["c$i"];
		if ($is_boolean) {
			$cur = (($cur==1) ? true : false);
		} else if ($is_integer) {
			if (!is_numeric($cur)) foundErr("���ֲ���ָ���˷����ֵ�ֵ");
			$cur = intval($cur);
		}
		if ($default == $cur) continue;
		if ($is_constant) {
			$stat .= "define(\"" . $varname . "\", ";
		} else {
			$stat .= "\$" . $varname . " = ";
		}
		if ($is_string) {
			$stat .= "\"" . addcslashes($cur, "\\\"") . "\"";
		} else if ($is_boolean) {
			$stat .= ($cur ? "true" : "false");
		} else {			
			$stat .= $cur;
		}
		if ($is_constant) $stat .= ")";
		$stat .= ";\n";
	}
	$stat .= "\n/* Ĭ������ */\nrequire \"default.php\";\n?>\n";
	if (($fp = @fopen($newConf, "w")) !== false) {
		fwrite($fp, $stat);
		fclose($fp);
	}
}

function show_site() {
	global $curConf;
	global $newConf;
	global $bakConf;
?>
<script language="javascript">
<!--
	function con() {
		return confirm("����µ����ô������⣬��ȷ����֪����ô�ָ���ԭ����������");
	}
//-->
</script>
<form action="admin.site_defines.php?action=mv" method="post" onsubmit="return con()" name="site_defines">
<table cellpadding=3 cellspacing=1 border=0 align=center class=TableBorder1>
<tr> 
	<th width="50%">��ǰ����: <?php echo $curConf; ?></th>
	<th width="50%">�µ�����: <?php echo $newConf; ?></th>
</tr>
<tr>
	<td class=TableBody2 style="padding: 10pt;" valign="top">
<?php
	show_source($curConf);
?>
	</td>
	<td class=TableBody2 style="padding: 10pt;" valign="top">
<?php
	if (file_exists($newConf)) show_source($newConf);
	else echo "û�ҵ��ļ������ˡ�";
?>
	</td>
</tr>
<tr> 
	<td colspan="2" class="TableBody1" align="center" style="padding: 5pt;" >
		<b>������Ӧ�ø�ʲô...</b>
	</td>
</tr>
<tr> 
	<td colspan="2" class="TableBody2" align="center" style="padding: 10pt;">
	<table><tr><td align="left">
		<ul>
			<li>��һ�������µ������ļ���ʲô���⡣</li>
			<li>������İ�ť�������µ�������Ч���ɵ����ý��ᱣ����<br><?php echo $bakConf; ?><br>���֮��������⣬���¼ BBS ����ִ��<br>cp -f <?php echo $bakConf ?> <?php echo $curConf ?><br>�Ļؾɵ����á�</li>
			<li>���������������˵ʲô���Ǿ��ȸ��������ٵ�����İ�ť��</li>
		</ul>
	</td></tr></table>
	<input type="submit" value="���µ�������Ч��">
	</td>
</tr>
</table>
<?php
}

function show_conf() {
	global $site_defines;
?>
<form action="admin.site_defines.php?action=gen" method="post" name="site_defines">
<table cellpadding=3 cellspacing=1 border=0 align=center class=TableBorder1>
<tr> 
	<th colspan="3">վ�����ò���</th>
</tr>
<?php
	$ccc = count($site_defines);
	for ($i = 0; $i < $ccc; $i++) {
		if ($site_defines[$i] === false) break;
		if (is_string($site_defines[$i])) {
?>
<tr>
	<td colspan="3" class=TableBody2 align="center"><b><?php echo htmlspecialchars($site_defines[$i]); ?></b></td>
</tr>
<?php
		} else {
			$is_boolean = ($site_defines[$i][2] == "b");
			$is_constant = ($site_defines[$i][0] == 0);
			$varname = $site_defines[$i][1];
			$default = $site_defines[$i][3];
			if ($is_constant) {
				$cur = constant($varname);
			} else {
				$cur = $GLOBALS[$varname];
			}
			$is_default = ($default == $cur);
?>
<tr>
	<td class=TableBody1>
<?php
			if (!$is_default) echo "<b>";
			echo htmlspecialchars($site_defines[$i][4]);
			if (!$is_default) {
				echo "</b>&nbsp;&nbsp;[Ĭ��ֵ��";
				if ($is_boolean) {
					echo ($default?"��":"��");
				} else {
					echo htmlspecialchars($default);
				}
				echo "]";
			}
?>
	</td>
	<td class=TableBody1 align="center">
<?php
			if ($is_boolean) {
				echo "<input type=\"radio\" name=\"c$i\" value=\"1\"" . ($cur? " checked=\"checked\"" : "") . " />��&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<input type=\"radio\" name=\"c$i\" value=\"0\"" . ($cur? "" : " checked=\"checked\"") . " />��";
			} else {
				echo "<input class=\"TableBorder2\" type=\"text\" name=\"c$i\" value=\"" . htmlspecialchars($cur) . "\" size=\"50\" />";
			}
?>	
	</td>
	<td class=TableBody1>
<?php
			echo $is_constant?"����":"<b>����</b>";
			echo " ���ƣ�".htmlspecialchars($varname);
?>	
	</td>
</tr>
<?php
		}
	}
?>
<tr> 
	<td colspan="3" class="TableBody2" align="center"><input type="submit" value="�Զ����� site.php"></td>
</tr>
</table>
</form>
<?php
}
?>
