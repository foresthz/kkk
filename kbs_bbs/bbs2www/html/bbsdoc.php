<?php
	/**
	 * This file lists articles to user.
	 * $Id$
	 */
	require("funcs.php");

	function display_navigation_bar($brdarr,$brdnum,$start,$total,$page,$order=FALSE)
	{
		global $section_names;
		$brd_encode = urlencode($brdarr["NAME"]);
	?>		
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="b1">		
<form name="form1" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get">
<input type="hidden" name="board" value="<?php echo $brdarr["NAME"]; ?>"/>
<tr>
<td>
    	<?php
    		if (strcmp($currentuser["userid"], "guest") != 0)
		{
    	?>
<input class="b1" type="button" value="��������" onclick="window.location.href=href='bbspst.php?board=<?php echo $brd_encode; ?>'">
    	<?php
    		}
    	?>
</td>
<td class="b1" align="right">
 	<?php
		      if($order)
		      {
		   	if ($start <= $total - 20)
			{
		    ?>
[<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>">��һҳ</a>]
[<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&page=<?php echo $page + 1; ?>">��һҳ</a>]
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
[<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&page=<?php echo $page - 1; ?>">��һҳ</a>]
[<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&page=1">���һҳ</a>]
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
[<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&page=1">��һҳ</a>]
[<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&page=<?php echo $page - 1; ?>">��һҳ</a>]
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
[<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&page=<?php echo $page + 1; ?>">��һҳ</a>]
[<a href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>">���һҳ</a>]
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
<input class="b1" type="submit" value="��ת��"> �� <input class="b1" type="text" name="start" size="3"  onmouseover=this.focus() onfocus=this.select()> ƪ 
</td></tr></form></table>
	<?php
	}
	
	function display_articles($brdarr,$articles,$start,$order=FALSE)
	{
		global $dir_modes;
		global $default_dir_mode;
		$brd_encode = urlencode($brdarr["NAME"]);
?>
<table width="95%" border="0" cellspacing="0" cellpadding="3" class="t1">
<tr align="center" class="t2"><td width="50" class="t2">���</td><td width="40" class="t2">���</td><td width="120" class="t2">����</td><td width="80" class="t2">����</td><td class="t2">����</td></tr>
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
<tr>
<?php
			if (!strncmp($flags,"D",1)||!strncmp($flags,"d",1)) {
?>
<td colspan="2" align="center" class="t2"><img src="images/istop.gif" alt="��ʾ" align="absmiddle"> <strong>��ʾ</strong></td>
<?php

			} else {
?>
<td align="center" class="t2"><?php echo $start+$i; ?></td>
<td align="center" class="t2">&nbsp;
<?php
			if ($flags[1] == 'y')
			{
				if ($flags[0] == ' ')
				{
?>
<font class="f208">&nbsp;</font>
<?php
				}
				else
				{
?>
<font class="f002"><?php echo $flags[0]; ?></font>
<?php
				}
			}
			elseif ($flags[0] == 'N' || $flags[0] == '*')
			{
?>
<font class="f008"><?php echo $flags[0]; ?></font>
<?php
			}
			else
				echo $flags[0];
			echo $flags[3];
?>
&nbsp;</td>
<?php
	}//�ö�
?>
<td align="center" class="t2"><a href="/cgi-bin/bbs/bbsqry?userid=<?php echo $article["OWNER"]; ?>"><?php echo $article["OWNER"]; ?></a></td>
<td align="center" class="t2"><?php echo strftime("%b&nbsp;%e", $article["POSTTIME"]); ?></td>
<td class="t2">&nbsp;
<?php
	switch ($default_dir_mode)
	{
	case $dir_modes["ORIGIN"]:
		if (!strncmp($flags,"D",1)||!strncmp($flags,"d",1))
		{
?>
<a href="/bbscon.php?board=<?php echo $brd_encode; ?>&id=<?php echo $article["ID"]; ?>&ftype=9"><?php echo htmlspecialchars($title); ?>

</a>
<?php
		}
		else
		{
?>
<a href="/cgi-bin/bbs/bbstcon?board=<?php echo $brd_encode; ?>&file=<?php echo $article["FILENAME"]; ?>"><?php echo htmlspecialchars($title); ?>

</a>
<?php
		}
		break;
	case $dir_modes["NORMAL"]:
	default:
?>
<a href="/bbscon.php?board=<?php echo $brd_encode; ?>&id=<?php echo $article["ID"]; ?><?php if (!strncmp($flags,"D",1)||!strncmp($flags,"d",1)) echo "&ftype=9"; ?>"><?php echo htmlspecialchars($title); ?>

</a>
<?php
	}
?>
</td>
</tr>
<?php
			if ($order)
				$i--;
			else
				$i++;
		}
?>
</table>
<?php
	}

	if ($loginok != 1)
		html_nologin();
	else
	{
		$board_list_error=FALSE;
		html_init("gb2312");
		if (isset($_GET["board"]))
			$board = $_GET["board"];
		else{
			html_error_quit("�����������");
			$board_list_error=TRUE;
			}
		// ����û��ܷ��Ķ��ð�
		$brdarr = array();
		$brdnum = bbs_getboard($board, $brdarr);
		if ($brdnum == 0){
			html_error_quit("�����������");
			$board_list_error=TRUE;
			}
		$usernum = $currentuser["index"];
		if (bbs_checkreadperm($usernum, $brdnum) == 0){
			html_error_quit("�����������");
			$board_list_error=TRUE;
			}
		if ($brdarr["FLAG"]&BBS_BOARD_GROUP) {
			for ($i=0;$i<sizeof($section_nums);$i++)
				if (!strcmp($section_nums[$i],$brdarr["SECNUM"])) {
			         Header("Location: bbsboa.php?group=" . $i . "&group2=" . $brdnum);
			         return;
                                }
			html_error_quit("�����������");
			$board_list_error=TRUE;
		}
		if (!isset($default_dir_mode))
			$default_dir_mode = $dir_modes["NORMAL"];
		$total = bbs_countarticles($brdnum, $default_dir_mode);
		if ($total <= 0)
		    if (strcmp($currentuser["userid"], "guest") != 0){
			html_error_quit("��������Ŀǰû������<br /><a href=\"bbspst.php?board=" . $board . "\">��������</a>");
			$board_list_error=TRUE;
			}
                    else{
			html_error_quit("��������Ŀǰû������");
			$board_list_error=TRUE;
			}
        	bbs_set_onboard($brdnum,1);
		$artcnt = 20;
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
		$articles = bbs_getarticles($brdarr["NAME"], $start, $artcnt, $default_dir_mode);
		if ($articles == FALSE){
			html_error_quit("��ȡ�����б�ʧ��");
			$board_list_error=TRUE;
			}
		$bms = explode(" ", trim($brdarr["BM"]));
		$bm_url = "";
		if (strlen($bms[0]) == 0 || $bms[0][0] <= chr(32))
			$bm_url = "����������";
		else
		{
			if (!ctype_alpha($bms[0][0]))
				$bm_url = $bms[0];
			else
			{
				foreach ($bms as $bm)
				{
					$bm_url .= sprintf("<a href=\"/cgi-bin/bbs/bbsqry?userid=%s\">%s</a> ", $bm, $bm);
				}
				$bm_url = trim($bm_url);
			}
		}
		if (!isset($order_articles))
			$order_articles = FALSE;
			
?>
<body>
<?php
	if($board_list_error==FALSE)
	{
		$order=$order_articles;
		$brd_encode = urlencode($brdarr["NAME"]);
		$ann_path = bbs_getannpath($brdarr["NAME"]);
?>
<a name="listtop"></a>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
  <tr> 
    <td colspan="2" class="b1">
	    <a href="bbssec.php"><?php echo BBS_FULL_NAME; ?></a>
	    -
	    <?php
	    	$sec_index = get_secname_index($brdarr["SECNUM"]);
		if ($sec_index >= 0)
		{
	    ?>
		<a href="/bbsboa.php?group=<?php echo $sec_index; ?>"><?php echo $section_names[$sec_index][0]; ?></a>
	    <?php
		}
	    ?>
	    -
	    <?php echo $brdarr["NAME"]; ?>��(<a href="bbsnot.php?board=<?php echo $brd_encode; ?>">���滭��</a>|<a href="/cgi-bin/bbs/bbsbrdadd?board=<?php echo $brdarr["NAME"]; ?>">Ԥ������</a>)
    </td>
  </tr>
  <tr> 
    <td colspan="2" align="center" height="80"><font size=6><strong><?php echo $brdarr["NAME"]."(".$brdarr["DESC"].")"; ?>��</strong></font></td>
  </tr>
  <tr><td colspan="2" class="b1">
  <img src="images/bm.gif" alt="����" align="absmiddle">���� <?php echo $bm_url; ?>
  </td></tr>
  <tr> 
    <td class="b1">
    <img src="images/online.gif" alt="������������" align="absmiddle">���� <font color=#CC0000><strong><?php echo $brdarr["CURRENTUSERS"]+1; ?></strong></font> ��
    <img src="images/postno.gif" alt="����������" align="absmiddle">���� <font color=#CC0000><strong><?php echo $total; ?></strong></font> ƪ
    </td>
    <td align="right" class="b1">
	    <img src="images/gmode.gif" align="absmiddle" alt="��ժ��"><a href="bbsgdoc.php?board=<?php echo $brd_encode; ?>">��ժ��</a> 
	    <?php
  	    	if ($ann_path != FALSE)
		{
                    if (!strncmp($ann_path,"0Announce/",10))
			$ann_path=substr($ann_path,9);
	    ?>
	    | 
	    <img src="images/soul.gif" align="absmiddle" alt="������"><a href="/cgi-bin/bbs/bbs0an?path=<?php echo urlencode($ann_path); ?>">������</a>
	    <?php
		}
	    ?>
	    | 
	    <img src="images/search.gif" align="absmiddle" alt="���ڲ�ѯ"><a href="/cgi-bin/bbs/bbsbfind?board=<?php echo $brd_encode; ?>">���ڲ�ѯ</a>
	    <?php
    		if (strcmp($currentuser["userid"], "guest") != 0)
		{
    	    ?>
	    | 
	    <img src="images/vote.gif" align="absmiddle" alt="����ͶƱ"><a href="/bbsshowvote.php?board=<?php echo $brd_encode; ?>">����ͶƱ</a>
	    | 
	    <img src="images/model.gif" align="absmiddle" alt="����ģ��"><a href="/bbsshowtmpl.php?board=<?php echo $brd_encode; ?>">��������</a>
    	    <?php
    		}
    	    ?>	
    </td>
  </tr>
  </tr>
  <tr><td colspan="2"><hr class="default"/></td></tr>
  <tr><td colspan="2" align="right">
  <?php
  	display_navigation_bar($brdarr, $brdnum, $start, $total, $page,$order_articles);
  ?>
  </td></tr>
  <tr> 
    <td colspan="2" align="center">
    	<?php
		display_articles($brdarr, $articles, $start, $order_articles);
	?>	
    </td>
  </tr>
  <tr><td colspan="2" align="right">
  <?php
  	display_navigation_bar($brdarr, $brdnum, $start, $total, $page,$order_articles);
  ?>
  </td></tr>
  <tr><td colspan="2"><hr class="default"/></td></tr>
  <tr> 
    <td colspan="2" align="center" class="b1">
    	[<a href="#listtop">���ض���</a>]
    	[<a href="javascript:location=location">ˢ��</a>]
    	[<a href="bbstdoc.php?board=<?php echo $brd_encode; ?>">ͬ����ģʽ</a>]
    	<?php
    		if (strcmp($currentuser["userid"], "guest") != 0)
		{
    	?>
    	[<a href="/cgi-bin/bbs/bbsclear?board=<?php echo $brd_encode; ?>&start=<?php echo $start; ?>">���δ��</a>]
	<?php
		}
		if (bbs_is_bm($brdnum, $usernum))
		{
	?>
	[<a href="bbsmdoc.php?board=<?php echo $brd_encode; ?>">����ģʽ</a>]
	<?php
		}
	?>
    </td>
  </tr>
</table>
<?php
	}
		html_normal_quit();
	}
?>
