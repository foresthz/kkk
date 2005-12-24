<?php
	require("www2-funcs.php");
	login_init();
	toolbox_header("�����޸�");

	if (isset($_GET['do'])) {
		$new_wwwparams = @intval($_COOKIE["WWWPARAMS"]);
		if (strcmp($currentuser["userid"], "guest")) {
			bbs_setwwwparameters($new_wwwparams); /* TODO: return value ? */
		}
	}
?>
<script type="text/javascript">
	var settings = {"sizer": 3, "pager": 4, "hot": 5}; /* faint IE5 */
	function setInd(n, v) {
		var ff = getObj(n + 'F');
		var tt = getObj(n + 'T');
		/* some users might not have bold font... that's why I add underline */
		if (ff) {
			ff.style.fontWeight = v ? 'normal' : 'bold';
			ff.style.textDecoration = v ? 'none' : 'underline';
		}
		if (tt) {
			tt.style.fontWeight = v ? 'bold' : 'normal';
			tt.style.textDecoration = v ? 'underline' : 'none';
		}
	}
	function adjSet(n, v) {
		var idx = settings[n];
		if (n == "sizer") {
			getObj("fontSizer").style.display = v ? "block" : "none";
		}
		setInd(n, v);
		var mask = 1 << idx;
		saveParaCookie(v ? mask : 0, mask);
	}
	addBootFn(function() {
		var c = readParaCookie();
		for (var n in settings) {
			var i = settings[n];
			var v = c & (1 << i);
			setInd(n, v);
		}
		var stylenum = (c & 0xF80) >>7;
		getObj('style'+stylenum).checked = true;
	});
	function KCNymsw() {
		alert('��û�����������... ��Ҫ�𣿲���Ҫ����ȥ sysop �溰����');
	}
	function pvStyle(cssID) {
		saveParaCookie(cssID << 7, 0xF80);
		var ff = top.window["menu"]; if (ff) ff.resetCss();
		ff = top.window["toogle"]; if (ff) ff.resetCss();
		ff = top.window["f4"]; if (ff) ff.resetCss();
	}
	function chkStyle(cssID) {
		getObj('style' + cssID).checked = true;
		pvStyle(cssID);
	}
</script>
<form action="bbsstyle.php?do" class="small align">
	<fieldset><legend>�Զ������</legend>
		<div class="inputs">
			<label>�����С:</label>
				<span class="clickable" onclick="sizer(1)">�Ŵ�</span>
				<span class="clickable" onclick="sizer(-1)">��С</span>
				<span class="clickable" onclick="sizer(0)">�ָ�</span>
			<br/>
			<label>���������:</label>
				<span class="clickable" onclick="adjSet('sizer', 0)" id="sizerF">����</span>
				<span class="clickable" onclick="adjSet('sizer', 1)" id="sizerT">��ʾ</span>
			<br/>
			<label>���ϽǷ�ҳ����:</label>
				<span class="clickable" onclick="adjSet('pager', 0)" id="pagerF">����</span>
				<span class="clickable" onclick="adjSet('pager', 1)" id="pagerT">��ʾ</span>
			<br/>
<?php if (defined('BBS_NEWPOSTSTAT')) { ?>
			<label>���Ż������:</label>
				<span class="clickable" onclick="adjSet('hot', 0)" id="hotF">�·�</span>
				<span class="clickable" onclick="adjSet('hot', 1)" id="hotT">�Ϸ�</span>
				<span class="clickable" onclick="KCNymsw();">�ر�</span>
			<br/>
<?php } ?>
			<label>���淽��:</label><br>
			<div align="center">
<?php
	// ������ʾÿ�����淽��������ͼ
	$stylecount=count($style_names);
	$ret = "";
	for($i=0;$i<$stylecount;$i++)
	{
		$ret .= "<p><input type=\"radio\" id=\"style{$i}\" onclick=\"pvStyle($i)\" name=\"styleid\" value=\"{$i}\">";
		$ret .= "<img src=\"{$style_names[$i][1]}\" onClick=\"chkStyle($i);\"><br/>{$style_names[$i][0]}</p>";
	}
	print($ret);
?>
			</div>
			<br/>
		</div>
	</fieldset>
	<div class="oper"><input type="submit" value="��������"/> &nbsp;<input type="button" onclick="history.go(-1);" value="���ٷ���"/></div>
</form>
<div class="large left"><ul>
	<li>�޸�������Ч��</li>
	<li>�����ϣ����֤ÿ�ε�¼��ʹ��������ã����Ե� �������ã������ȵ�¼����</li>
</ul></div>
<?php
	page_footer();
?>
