<?php
    require("www2-admin.php");

    admin_check("editbrd");

    if(isset($_POST["oldfilename"])) {
        $filename = $_POST["oldfilename"];
    }

    if(isset($_GET["board"]))
        $boardname = $_GET["board"];
    else if(isset($_POST["board"]))
        $boardname = $_POST["board"];
    else
        $boardname = "";

    if($boardname != "") {
        $boardparams = array();
        $ret = bbs_admin_getboardparam($boardname, $boardparams);
        switch($ret) {
        case -1:
            html_error_quit("�����ڵİ��档");
            break;
        case -2:
            html_error_quit("�޷���ȡ�������ԡ�");
            break;
        case -3:
            html_error_quit("��ʼ������ʧ�ܣ�����ϵ������Ա��");
            break;
        case 0:
            $bid = $boardparams["BID"];
            $filename = $boardparams["FILENAME"];
            $clubnum = $boardparams["CLUBNUM"];
            $bm = $boardparams["BM"];
            $title = substr($boardparams["TITLE"], 13, 256);
            $secnum = substr($boardparams["TITLE"], 0, 1);
            $btype = substr($boardparams["TITLE"], 2, 4);
            $innflag = substr($boardparams["TITLE"], 7, 6);
            $des = $boardparams["DES"];
            $anony = ($boardparams["FLAG"] & BBS_BOARD_ANNONY) ? " checked" : "";
            $notjunk = ($boardparams["FLAG"] & BBS_BOARD_JUNK) ? "" : " checked";
            $notpoststat = ($boardparams["FLAG"] & BBS_BOARD_POSTSTAT) ? "" : " checked";
            $group = ($boardparams["FLAG"] & BBS_BOARD_GROUP) ? " checked" : "";
            $parentb = $boardparams["GROUP"];
            if($parentb != "") {
                $parentbarr = array();
                $parentbstatus = is_null(bbs_safe_getboard(0, $parentb, $parentbarr)) ? "(�쳣)" : "";
            }
            else {
                $parentb = "";
                $parentbstatus = "";
            }
            $outflag = ($boardparams["FLAG"] & BBS_BOARD_OUTFLAG) ? " checked" : "";
            $attach = ($boardparams["FLAG"] & BBS_BOARD_ATTACH) ? " checked" : "";
			$emailpost = ($boardparams["FLAG"] & BBS_BOARD_EMAILPOST) ? " checked" : "";
			$noreply = ($boardparams["FLAG"] & BBS_BOARD_NOREPLY) ? " checked" : "";
			$clubread = ($boardparams["FLAG"] & BBS_BOARD_CLUB_READ) ? " checked" : "";
			$clubwrite = ($boardparams["FLAG"] & BBS_BOARD_CLUB_WRITE) ? " checked" : "";
			$clubhide = ($boardparams["FLAG"] & BBS_BOARD_CLUB_HIDE) ? " checked" : "";
			if(($clubread == "") && ($clubwrite == ""))
				$clubhide .= " disabled";
			$annpath = $boardparams["ANNPATH"];
            $annpath_section = $boardparams["ANNPATH_SECTION"];
            $annpath_status = $boardparams["ANNPATH_STATUS"];
            $annpath_status_str = array("��Ч", "��Ч", "�쳣");
            $level = $boardparams["LEVEL"];
            $title_level = $boardparams["TITLELEVEL"];
            break;
        default:
        }
    }
    
    for($i=1; $i<256; $i++) {
        $usertitles[$i-1] = bbs_admin_getusertitle($i);
    }
    
    admin_header("�޸İ���", "�޸�������˵�����趨");
?>
<script type="text/javascript">
function setinnflag(ifstr) {
    document.getElementById('innflag').value = ifstr;
}
function clubtypeChange() {
	var cr, cw;
	cr = document.getElementById('clubread').checked;
	cw = document.getElementById('clubwrite').checked;
	if(!(cr || cw))
		document.getElementById('clubhide').disabled = true;
	else
		document.getElementById('clubhide').disabled = false;
}
</script>
<form method="post" action="admeditbrd.php" class="medium">
<fieldset><legend>Ҫ�޸ĵİ���</legend><div class="inputs">
<label>����Ӣ������:</label><input type="text" name="board" size="20" maxlength="30" value="<?php echo $boardname; ?>">
<input type="submit" value="ȷ��"></div></fieldset></form>
<?php if($boardname != "") { ?>
<form method="post" action="admeditbrd.php" class="medium">
<fieldset><legend>�޸İ�������</legend><div class="inputs">
<input type="hidden" name="oldfilename" value="<?php echo $filename; ?>">
<label>���������:</label><?php echo $bid; ?><br>
<label>���ֲ����:</label><?php echo $clubnum; ?><br>
<label>����������:</label><input type="text" name="filename" size="20" maxlength="30" value="<?php echo $filename; ?>"><br>
<label>����������:</label><input type="text" name="bm" size="30" maxlength="59" value="<?php echo $bm; ?>"><br>
<label>������˵��:</label><input type="text" name="title" size="20" maxlength="50" value="<?php echo $title; ?>"><br>
<label>����������:</label><select name="secnum">
<?php
    for($i=0; $i<BBS_SECNUM; $i++) {
        print("<option value=\"{$i}\"" . ((constant("BBS_SECCODE{$i}")==$secnum)?" selected":""). ">&lt;" . constant("BBS_SECCODE{$i}") . "&gt; " . constant("BBS_SECNAME{$i}_0") . " " . constant("BBS_SECNAME{$i}_1") . "</option>");
    }
?>
</select><br>
<label>����������:</label>[<input type="text" name="title" size="4" maxlength="4" value="<?php echo $btype; ?>">]<br>
<label>ת�ű�ǩ:</label><input type="text" id="innflag" name="innflag" size="6" maxlength="6" value="<?php echo $innflag; ?>">
    &lt;<a href="javascript:setinnflag('      ');">��ת��</a>&gt;
    &lt;<a href="javascript:setinnflag(' ��   ');">˫��ת��</a>&gt;
    &lt;<a href="javascript:setinnflag(' ��   ');">����ת��</a>&gt;<br>
<label>����������:</label><input type="text" name="des" size="30" maxlength="194" value="<?php echo $des; ?>"><br>
<label>����������:</label><input type="checkbox" value="o" name="anony"<?php echo $anony; ?>>�û����������ڰ��淢�ġ�<br>
<label>ͳ��������:</label><input type="checkbox" value="o" name="notjunk"<?php echo $notjunk; ?>>�ڰ��淢�����û����������ӡ�<br>
<label>ͳ��ʮ��:</label><input type="checkbox" value="o" name="notpoststat"<?php echo $notpoststat; ?>>�������²μ�ʮ�����Ż���ͳ�ơ�<br>
<label>Ŀ¼������:</label><input type="checkbox" value="o" name="group"<?php echo $group; ?>>������������İ��档<br>
<label>����Ŀ¼:</label><input type="text" name="parentb" value="<?php echo $parentb; ?>"> <?php echo $parentbstatus; ?><br>
<label>����ת��:</label><input type="checkbox" value="o" name="outflag"<?php echo $outflag; ?>>����������վ��ת�š�<br>
<label>�ϴ�����:</label><input type="checkbox" value="o" name="attach"<?php echo $attach; ?>>���º������ճ��������<br>
<label>E-mail����:</label><input type="checkbox" value="o" name="emailpost"<?php echo $emailpost; ?>>���Խ����յ�email���������档<br>
<label>���ɻظ�:</label><input type="checkbox" value="o" name="noreply"<?php echo $noreply; ?>>�����ڰ���ظ����¡�<br>
<label>���ֲ�����:</label><input type="checkbox" id="clubread" value="o" name="clubread" onclick="clubtypeChange();"<?php echo $clubread; ?>>������
	<input type="checkbox" id="clubwrite" value="o" name="clubwrite" onclick="clubtypeChange();"<?php echo $clubwrite; ?>>д����
	<input type="checkbox" id="clubhide" value="o" name="clubhide"<?php echo $clubhide; ?>>����<br>
<label>������·��:</label><select name="annpath">
<?php
    for($i=0; $i<BBS_SECNUM; $i++) {
        print("<option value=\"{$i}\"" . (($i==$annpath_section)?" selected":"") . ">[" . constant("BBS_SECCODE{$i}") . "] " . constant("BBS_SECNAME{$i}_0") . " " . constant("BBS_GROUP{$i}") . "/{$boardname}</option>");
    }
?>
</select> <?php echo $annpath_status_str[$annpath_status]; ?><br>
<label>Ȩ������:</label>
<?php
    print("<table align=\"center\">");
    bbs_admin_permtable("level", $level, 0, 16); 
    bbs_admin_permtable("level", $level, 17, BBS_NUMPERMS);
    print("</table>");
?>
<label>�������:</label>
<select name="title_level">
<option value="0">[û������]</option>
<?php
        for($i=1; $i<256; $i++) {
            if($usertitles[$i-1] != "")
                print("<option value=\"{$i}\"" . (($title_level==$i)?" selected":"") . ">{$usertitles[$i-1]}</option>");
        }
?>
</select><br><br>
<?php // ���ˮľ���еĻ��������Ҳ����ˣ�Ҫ��ecore�����ɡ� ?>
<div align="center"><input type="submit" value="�޸�"> <input type="reset" value="����"></div>
</div></fieldset></form>
<?php } ?><br>
<?php
    page_footer();
?>
