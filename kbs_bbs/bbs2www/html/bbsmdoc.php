<?php
/**
 * articles list for boardmanagers
 * windinsn May 19,2004
 */
    function display_navigation_bar($brdarr,$brdnum,$start,$total,$page,$order=FALSE)
	{
		global $section_names,$currentuser;
		$brd_encode = urlencode($brdarr["NAME"]);
	?>		
<table width="100%" border="0" cellspacing="0" cellpadding="3" class="b1">		
<form name="form1" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get">
<input type="hidden" name="board" value="<?php echo $brdarr["NAME"]; ?>"/>
<tbody><tr>
<td>
    	<?php
    		if (strcmp($currentuser["userid"], "guest") != 0)
		{
    	?>
<a href="bbspst.php?board=<?php echo $brd_encode; ?>"><img src="images/postnew.gif" border="0" alt="������"></a>
    	<?php
    		}
    	?>
</td>
<td align="right">
 	<?php
		      if($order)
		      {
		   	if ($start <= $total - 20)
			{
		    ?>
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>"><font class="b1"><u>��һҳ</u></font></a>]
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&page=<?php echo $page + 1; ?>"><font class="b1"><u>��һҳ</u></font></a>]
		    <?php
			}
			else
			{
		    ?>
[��һҳ] 
[��һҳ]
		    <?php
			}
			if ($page > 1)
			{
		?>
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&page=<?php echo $page - 1; ?>"><font class="b1"><u>��һҳ</u></font></a>]
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&page=1"><font class="b1"><u>���һҳ</u></font></a>]
		    <?php
			}
			else
			{
		    ?>
[��һҳ] 
[���һҳ]
		    <?php
			}
		     }
		     else
		     {	
		     	if ($page > 1)
			{
		?>
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&page=1"><font class="b1"><u>��һҳ</u></font></a>]
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&page=<?php echo $page - 1; ?>"><font class="b1"><u>��һҳ</u></font></a>]
		    <?php
			}
			else
			{
		    ?>
[��һҳ] 
[��һҳ]
		    <?php
			}
			if ($start <= $total - 20)
			{
		    ?>
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&page=<?php echo $page + 1; ?>"><font class="b1"><u>��һҳ</u></font></a>]
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>"><font class="b1"><u>���һҳ</u></font></a>]
		    <?php
			}
			else
			{
		    ?>
[��һҳ] 
[���һҳ]
		    <?php
			}
		    }
	?>
<input type="submit" class="b5" value="��ת��"/> �� <input type="text" name="start" size="3"  onmouseover=this.focus() onfocus=this.select() class="b5"> ƪ 
</td></tr></tbody></form></table>
	<?php
    }

    function display_articles($brdarr,$articles,$start,$order=FALSE)
	{
		global $dir_modes;
		global $default_dir_mode;
		$brd_encode = urlencode($brdarr["NAME"]);
?>
<center>
<table width="98%" border="0" cellspacing="0" cellpadding="3" class="t1">
<tbody><tr><td class="t2" width="50">���</td><td class="t2" width="30">����</td><td class="t2" width="30">���</td><td class="t2" width="85">����</td><td class="t2" width="50">����</td><td class="t2">����</td></tr>
</tbody>
<?php
		$ding_cnt = 0;
		foreach ($articles as $article)
		{
			$flags = $article["FLAGS"];
			if (!strncmp($flags,"D",1)||!strncmp($flags,"d",1))
				$ding_cnt++;
		}
		$i = 0;
		if ($order) {
			$articles = array_reverse($articles);
			$i = count($articles) - $ding_cnt - 1;
		}
		foreach ($articles as $article)
		{
			$title = $article["TITLE"];
			if (strncmp($title, "Re: ", 4) != 0)
				$title = "�� " . $title;

			$flags = $article["FLAGS"];
?>
<tbody>
<tr>
<?php
			if (!strncmp($flags,"D",1)||!strncmp($flags,"d",1)) {
?>
<td class="t6">��ʾ</td>
<td class="t3">
<input type="checkbox" name="ding<?php echo $i; ?>" value="<?php echo $article["ID"]; ?>" />
</td>
<td class="t3" width="30"><img src="images/istop.gif" alt="��ʾ" align="absmiddle"></td>
<?php

			} else {
?>
<td class="t4"><?php echo $start+$i; ?></td>
<td class="t3">
<input type="checkbox" name="art<?php echo $i; ?>" value="<?php echo $article["ID"]; ?>" />
</td>
<td class="t4">
<?php
			if ($flags[1] == 'y')
			{
				if ($flags[0] == ' ')
					echo "&nbsp;";
				else
					echo $flags[0];
			}
                         elseif ($flags[0] == 'N' || $flags[0] == '*'){
                                 if ($flags[0] == ' ') 
                                         echo "&nbsp;"; 
                                 else
                                         echo $flags[0];  
                         }
                         else{
                                 if ($flags[0] == ' ')
                                         echo "&nbsp;"; 
                                 else
                                         echo $flags[0];
                         }   
                         echo $flags[3]; 
 ?> 
</td>
<?php
	}//�ö�
?>
<td class="t3" width="85"><a class="ts1" href="/bbsqry.php?userid=<?php echo $article["OWNER"]; ?>"><?php echo $article["OWNER"]; ?></a></td>
<td class="t4" width="50"><?php echo strftime("%b&nbsp;%e", $article["POSTTIME"]); ?></td>
<td class="t5"><strong>
<?php
	switch ($default_dir_mode)
	{
	case $dir_modes["ORIGIN"]:
		if (!strncmp($flags,"D",1)||!strncmp($flags,"d",1))
		{
?>
<a href="/bbscon.php?board=<?php echo $brd_encode; ?>&id=<?php echo $article["ID"]; ?>&ftype=<?php echo $dir_modes["ZHIDING"]; ?>"><?php echo htmlspecialchars($title); ?>

</a></strong>
<?php
		}
		else
		{
?>
<a href="/bbstcon.php?board=<?php echo $brd_encode; ?>&gid=<?php echo $article["GROUPID"]; ?>"><?php echo htmlspecialchars($title); ?>

</a></strong>
<?php
		}
		break;
	case $dir_modes["NORMAL"]:
	default:
?>
<a href="/bbscon.php?board=<?php echo $brd_encode; ?>&id=<?php echo $article["ID"]; ?><?php if (!strncmp($flags,"D",1)||!strncmp($flags,"d",1)) echo "&ftype=" . $dir_modes["ZHIDING"]; ?>"><?php echo htmlspecialchars($title); ?> </a></strong><font class="<?php if($article["EFFSIZE"] >= 1000) echo "mb2"; else echo "b1";?>">(<?php if($article["EFFSIZE"] < 1000) echo $article["EFFSIZE"]; else { printf("%.1f",$article["EFFSIZE"]/1000.0); echo "k";} ?>)</font>

<?php
	}
?>
</td>
</tr>
</tbody>
<?php
			if ($order)
				$i--;
			else
				$i++;
		}
?>
</table></center>
<?php
	}
require('funcs.php');
require('board.inc.php');
login_init();
html_init('gb2312','','',1);
if ($loginok != 1)
    html_nologin();

		if (isset($_GET["board"]))
			$board = $_GET["board"];
	    elseif (isset($_POST["board"]))
	        $board = $_POST["board"];
		else
			html_error_quit("�����������");

		$brdarr = array();
		$brdnum = bbs_getboard($board, $brdarr);
		$board = $brdarr['NAME'];
		if ($brdnum == 0)
			html_error_quit("�����������");
		$usernum = $currentuser["index"];
		if (!bbs_is_bm($brdnum, $usernum))
			html_error_quit("�㲻�ǰ���");

        $artcnt = 50;
        
if (isset($_POST['act'])) {
    $mode = intval($_POST['act']);
    if ($mode > 0 && $mode <= sizeof($bbsman_modes)) {
        for ($i = 0 ; $i < $artcnt ; $i ++) {
            if (isset($_POST['art'.$i])) {
                if (intval($_POST['art'.$i])) {
                    $id = intval($_POST['art'.$i]);
                    $zhiding = 0;
                }
            }
            elseif (isset($_POST['ding'.$i])) {
                if (intval($_POST['ding'.$i])) {
                    $id = intval($_POST['ding'.$i]);
                    $zhiding = 1;
                }
            }
            else
                continue;
            
            if ($zhiding) {
                 if ($mode !=  $bbsman_modes['DEL'] && $mode != $bbsman_modes['ZHIDING'])
                    continue;   
                 $mode = $bbsman_modes['DEL'];
            }
            
            if (!$id)   continue;
            
            $ret = bbs_bmmanage($board,$id,$mode,$zhiding);
            switch ($ret) {
                case -1:
                case -2:
                case -3:
                case -9:
                    html_error_quit('ϵͳ����'.$ret);
                    break;
                case -4:
                    html_error_quit('����ID����');
                    break;
                default:    
            }
        }
    }
}


		$total = bbs_countarticles($brdnum, $dir_modes["NORMAL"]);
		if ($total <= 0)
			html_error_quit("��������Ŀǰû������");
		bbs_set_onboard($brdnum,1);

		
		if (isset($_GET["page"]))
			$page = $_GET["page"];
		elseif (isset($_POST["page"]))
			$page = $_POST["page"];
		else
		{
			if (isset($_GET["start"]))
			{
				$start = $_GET["start"];
				settype($start, "integer");
				$page = ($start + $artcnt - 1) / $artcnt;
			}
			else
				$page = 0;
		}
		settype($page, "integer");
		if ($page > 0)
			$start = ($page - 1) * $artcnt + 1;
		else
			$start = 0;
		/*
		 * �������һ��ʱ�������⣬���ܻᵼ����ű��ҡ�
		 * ԭ���������ε��� bbs_countarticles() �� bbs_getarticles()��
		 */
		if ($start == 0 || $start > ($total - $artcnt + 1))
		{
			if ($total <= $artcnt)
			{
				$start = 1;
				$page = 1;
			}
			else
			{
				$start = ($total - $artcnt + 1);
				$page = ($start + $artcnt - 1) / $artcnt + 1;
			}
		}
		else
			$page = ($start + $artcnt - 1) / $artcnt;
		settype($page, "integer");
		$articles = bbs_getarticles($brdarr["NAME"], $start, $artcnt, $dir_modes["NORMAL"]);
		if ($articles == FALSE)
			html_error_quit("��ȡ�����б�ʧ��");

        bbs_board_header($brdarr,$total);
        display_navigation_bar($brdarr,$brdnum,$start,$total,$page);
?>

<form name="manage" id="manage" method="post" action="/bbsmdoc.php?board=<?php echo $brdarr["NAME"]; ?>&page=<?php echo $page; ?>">
<?php
    display_articles($brdarr,$articles,$start,FALSE);
?>
<p align="center">
<input type="hidden" name="act" value="">
<input type="button" class="a" value="ɾ��" onclick="document.manage.act.value='<?php echo $bbsman_modes['DEL']; ?>'; document.manage.submit();">
<input type="button" class="a" value="�л�M" onclick="document.manage.act.value='<?php echo $bbsman_modes['MARK']; ?>'; document.manage.submit();">
<input type="button" class="a" value="�л�G" onclick="document.manage.act.value='<?php echo $bbsman_modes['DIGEST']; ?>'; document.manage.submit();">
<input type="button" class="a" value="�л�����Re" onclick="document.manage.act.value='<?php echo $bbsman_modes['NOREPLY']; ?>'; document.manage.submit();">
<input type="button" class="a" value="�л��ö�" onclick="document.manage.act.value='<?php echo $bbsman_modes['ZHIDING']; ?>'; document.manage.submit();">
</p></form>
<?php
display_navigation_bar($brdarr,$brdnum,$start,$total,$page);
bbs_board_foot($brdarr,'MANAGE');
html_normal_quit();
?>
