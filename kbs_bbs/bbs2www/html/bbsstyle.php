<?php
	require("www2-funcs.php");
	login_init();
	toolbox_header("�����޸�");

	if (isset($_GET['do'])) {
		if ($currentuser["userid"] == "guest")
			html_error_quit("guest ���ܱ������ã�");
		$new_wwwparams = @intval($_COOKIE["WWWPARAMS"]);
		bbs_setwwwparameters($new_wwwparams); /* TODO: return value ? */
		html_success_quit("�Զ�����汣��ɹ�");
		exit;
	}
?>
<script type="text/javascript">
	var settings = {"sizer": 3, "pager": 4, "hot": 5}; /* faint IE5 */
	function setInd(n, v) {
		getObj(n + 'F').style.fontWeight = v ? 'normal' : 'bold';
		getObj(n + 'T').style.fontWeight = v ? 'bold' : 'normal';
		/* some users might not have bold font... that's why I add underline */
		getObj(n + 'F').style.textDecoration = v ? 'none' : 'underline';
		getObj(n + 'T').style.textDecoration = v ? 'underline' : 'none';
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
	});
	function KCNymsw() {
		alert('��û�����������... ��Ҫ�𣿲���Ҫ����ȥ sysop �溰����');
	}
</script>
<form action="bbsstyle.php?do" method="post" class="small align">
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
		</div>
	</fieldset>
	<div class="oper"><input type="submit" value="�������õ�������"/> &nbsp;<input type="button" onclick="history.go(-1);" value="���ٷ���"/></div>
</form>
<div class="large left"><ul>
	<li>�޸�������Ч��</li>
	<li>�����ϣ����֤ÿ�ε�¼��ʹ��������ã����Ե� �������õ��������������ȵ�¼����</li>
</ul></div>
<?php
	page_footer();
?>
