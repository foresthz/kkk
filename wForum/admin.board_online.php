<?php

require("inc/funcs.php");
require("inc/user.inc.php");

setStat("�������� ID");

requireLoginok("��ҳ�����Ҫ����Ա��¼����ʹ�á�");

preprocess();

show_nav();

showUserMailBox();
head_var();

if (isset($_GET["board"])) {
    board_online($_GET["board"]);
} else {
    showQueryForm();
}

show_footer();

function preprocess() {
	global $currentuser;
	if (!($currentuser["userlevel"] & BBS_PERM_SYSOP)) {
		foundErr("��ҳ�����Ҫ����Ա��¼����ʹ�á�");
	}
}

function showQueryForm() {
?>
<form method="GET" action="admin.board_online.php">
<table align=center><tr><td>
���������: <input type="text" name="board">&nbsp;<input type="submit" value="��ѯ���������û�">
</td></tr></table>
</form>
<?php
}

function board_online($board) {
    global $sectionCount, $section_nums, $section_names;
?>
<table cellpadding=4 cellspacing=1 align=center class=TableBorder1 width="97%"><tr><th>����</th><th>���� ID</th><th>��Դ</th></tr>
<?php
    if ($board != "") {
        show_onlines($board, true);
    } else {
    	for ($i=0;$i<$sectionCount;$i++){
    		$boards = bbs_getboards($section_nums[$i], 0, $yank | 2 | 4);
    		if ($boards != FALSE) {
    			$brd_desc = $boards["DESC"]; // ��������
    			$brd_name = $boards["NAME"];
    			$brd_flag = $boards["FLAG"];
    			$rows = sizeof($brd_desc);
?>
<tr><td class="TableBody2" colspan="3" align="center"><b><?php echo $section_names[$i][0]; ?></b></td></tr>
<?php
    			for ($t = 0; $t < $rows; $t++)	{
    			    if ($brd_flag[$t] & BBS_BOARD_GROUP) continue;
    			    show_onlines($brd_name[$t]);
    			}
    		}
		}
	}

?>
</table>
<?php
    if ($board != "") {
        showQueryForm();
    }
}

function show_onlines($board, $force_output = false) {
    $users = array();
    $nUsers = bbs_useronboard($board, $users);
    if (($nUsers <= 0) && !$force_output) return;
?>
<tr><td class="TableBody1" rowspan="<?php echo $nUsers==0?1:$nUsers; ?>"><?php echo $board; ?></td>
<?php
    if ($nUsers > 0) {
        $first = true;
        foreach($users as $user) {
            if (!$first) {
                echo "<tr>";
                $first = false;
            }
?>
<td class="TableBody1"><?php echo $user["USERID"]; ?></td><td class="TableBody1"><?php echo $user["HOST"]; ?></td></tr>
<?php
        }
    } else if ($nUsers == 0) {
?>
<td class="TableBody1" colspan="2" align="center">����û���û�����</td></tr>
<?php

    } else {
?>
<td class="TableBody1" colspan="2" align="center">�������������ϵͳ����</td></tr>
<?php
    }
}
?>
