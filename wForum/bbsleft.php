<?php
	require("inc/funcs.php");
	
	function display_board_list($section_names,$section_nums)
	{
?>
<table width="100%" border="0" cellspacing="0" cellpadding="1">
<col width="16px"/><col align="left"/>
<?php
		$i = 0;
		foreach ($section_names as $secname)
		{
			$i++;
			$group=$i-1;
			$group2 = $yank = 0;
			$level = 0;
?>
<tr>
<td>
<a href="javascript:submenu(0,0,<?php echo $group; ?>,0,0)">
<img id="submenuimg_brd_<?php echo $group; ?>_0" src="pic/plus.gif" border="0">
</a>
</td>
<td>
<a href="section.php?sec=<?php echo $group; ?>" target="main"><?php echo $secname[0]; ?></a>
</td>
</tr>
<tr id="submenu_brd_<?php echo $group; ?>_0" style="display:none">
<td> </td>
<td id="submenu_brd_<?php echo $group; ?>_0_td">
<DIV></DIV>
</td>
</tr>
<?php
		}
?>
</table>
<?php
	}
	
	function display_my_favorite()
	{
?>
<table width="100%" border="0" cellspacing="0" cellpadding="1">
<col width="16px"/><col align="left"/>
<?php
 		$select = 0; 
 		$yank = 0;
 		 		
                if( bbs_load_favboard($select)!=-1 && $boards = bbs_fav_boards($select, 1)) 
                {
                    $brd_name = $boards["NAME"]; // Ӣ����
                    $brd_desc = $boards["DESC"]; // ��������
                    $brd_flag = $boards["FLAG"]; 
                    $brd_bid = $boards["BID"];  //�� ID ���� fav dir ������ֵ 
                    $rows = sizeof($brd_name);
                    
                    for ($j = 0; $j < $rows; $j++)	
                    {
                        if ($brd_flag[$j]==-1)
                        {
?>
<tr>
<td>
<a href="javascript:submenu(1,<?php echo $brd_bid[$j]; ?>,0,0,0)">
<img id="submenuimg_fav_<?php echo $brd_bid[$j]; ?>" src="pic/plus.gif" border="0">
</a>
</td>
<td>
<a href="favboard.php?select=<?php echo $brd_bid[$j]; ?>&up=-1" target="main"><?php echo $brd_desc[$j]; ?></a>
</td>
</tr>
<tr id="submenu_fav_<?php echo $brd_bid[$j]; ?>" style="display:none">
<td background="/images/line3.gif"> </td>
<td id="submenu_fav_<?php echo $brd_bid[$j]; ?>_td">
<DIV></DIV>
</td>
</tr>
<?php
                        }
                        else
                        {
?>
<tr>
<td>��</td>
<td><nobr><a href="<?php echo "board.php?name=" . urlencode($brd_name[$j]); ?>" target="main"><?php echo $brd_desc[$j]; ?></a></nobr></td>
</tr>
<?php
                    }
                }
            }
?>
</table>
<?php
        }

        html_init();
?>
<script src="bbsleft.js" language="JavaScript"></script>
<script language="JavaScript">
<!--
    colsDefine = top.document.getElementById('mainframe').cols;
//-->
</script>
<body  topmargin="0" leftmargin="0" onMouseOver='doMouseOver()' onMouseEnter='doMouseOver()' onMouseOut='doMouseOut()'>
<!--
    վ�����ͼƬ�ȵȡ�������Ҫ��վ�������޸� - atppp
-->
<br/>
<form method="post" target="main" action="logon.php">
<?php
    /* ��¼ע�����֣����ܲ���ͨ�������Ǻ����Եö��࣬����ע�͵�������Ҫ��վ���Լ��򿪣�ע�� CSS ���û�мӣ�- atppp */
    /*
    if ($loginok != 1) {
?>
&nbsp;�ʺţ�<input name="id" type="text" size="12"/><br/>
&nbsp;���룺<input name="passwd" type="password" size="12"/><br/>
&nbsp;&nbsp;&nbsp;<input type="submit" value="��¼"/>&nbsp;&nbsp;<input type="button" value="ע��" onclick="javascript:top.main.location.href='register.php'"/>
<?php
    } else {
?>
&nbsp;&nbsp;��ӭ <?php echo $currentuser["userid"]; ?> <a href="logout.php" target="main">ע��</a>
<?php
    }
    */
    /* ��¼ע�����ֽ��� */
?>
</form>
<table border="0" cellspacing="0" cellpadding="1" width="100%">
		<col width="16px"/><col align="left"/>
		<tr>
			<td>��</td>
			<td><a href="elite.php" target="main"> ������</a></td>
		</tr>
		<tr>
			<td>��</td>
			<td><a href="index.php" target="main"> ȫ��������</a></td>
		</tr>
		<tr>
			<td> </td>
			<td>
				<div id="divboard">
<?php
	display_board_list($section_names,$section_nums);
?>
				</div>
			</td>
		</tr>
		<tr>
			<td>��</td>
			<form action="searchboard.php" method="get" target="main">
			<td>
			    <table width="100px"><tr><td>
			        <input name="board" class="TableBorder2" type="text" value="����������" size="12" onmouseover="this.focus()" onfocus="this.select()" />
			    </td></tr></table>
			</td>
			</form>
		</tr>
<?php
	if($loginok==1){
?>
		<tr>
			<td>
				<div id="divfava">
				<a href='javascript:changemn("fav");'><img id="imgfav" src="pic/plus.gif" border="0"></a>
				</div>
			</td>
			<td><a href="favboard.php" target="main"> �ҵ��ղؼ�</a></td>
		</tr>
		<tr>
			<td> </td>
			<td>
				<div class="s" id="divfav">
<?php
	display_my_favorite();
?>
				</div>
			</td>
		</tr>
		<tr>
			<td>��</td>
			<td><a href="usermailbox.php?boxname=inbox" target="main"> �ҵ�����</a></td>
		</tr>
		<tr>
			<td>
				<div id="divmanagea">
				<a href='javascript:changemn("manage");'><img id="imgmanage" src="pic/plus.gif" border="0"></a>
				</div>
			</td>
			<td>
			<a href='javascript:changemn("manage");'> �û�����</a>
			</td>
		</tr>
		<tr>
			<td> </td>
			<td>
				<div class="s" id="divmanage">
					<table width="100%" border="0" cellspacing="0" cellpadding="1"><col width="16px"/><col align="left"/>
                        <tr><td>��</td><td><a href="usermanagemenu.php" target="main">�û��������</a></td></tr>
                        <tr><td>��</td><td><a href="modifyuserdata.php" target="main">���������޸�</a></td></tr>
    					<tr><td>��</td><td><a href="userparam.php" target="main">�û��Զ������</a></td></tr>
	    				<tr><td>��</td><td><a href="bbssig.php" target="main">�û�ǩ����</a></td></tr>
	    				<tr><td>��</td><td><a href="changepasswd.php" target="main">�޸�����</a></td></tr>
                    </table>
				</div>
			</td>
		</tr>
<?php
	}
?>
		<tr>
			<td>
				<div id="divchata">
				<a href='javascript:changemn("chat");'><img id="imgchat" src="pic/plus.gif" border="0"></a>
				</div>
			</td>
			<td>
			<a href='javascript:changemn("chat");'> ̸��˵��</a>
			</td>
		</tr>
		<tr>
			<td> </td>
			<td>
				<div class="s" id="divchat">
					<table width="100%" border="0" cellspacing="0" cellpadding="1"><col width="16px"/><col align="left"/>
<?php
	if($loginok == 1){
?>
                        <tr><td>��</td><td><a href="showmsgs.php" target="main">�쿴���߶���</a></td></tr>
                        <tr><td>��</td><td><a href="javascript:sendMsg()" target="main">������</a></td></tr>
<?php
    if (SMS_SUPPORT) {
?>
                        <tr><td>��</td><td><a href="javascript:sendSMSMsg()" target="main">�����ֻ�����</a></td></tr>
<?php
    }
?>
                        <tr><td>��</td><td><a href="friendlist.php" target="main">�༭�����б�</a></td></tr>
                        <tr><td>��</td><td><a href="showonlinefriend.php" target="main">���ߺ���</a></td></tr>
<?php
	}
?>
    					<tr><td>��</td><td><a href="showonlineuser.php" target="main">�����û�</a></td></tr>
	    				<tr><td>��</td><td><a href="dispuser.php" target="main">��ѯ�û�</a></td></tr>
                    </table>
				</div>
			</td>
		</tr>
		<tr>
			<td>��</td>
			<td><a href="javascript:switchAutoHide()" id="autoHideDiv"> �����Զ�����</a></td>
		</tr>
</table>
<iframe id="hiddenframe" name="hiddenframe" width="0" height="0"></iframe>
</body>
</html>