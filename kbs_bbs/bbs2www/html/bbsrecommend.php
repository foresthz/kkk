<?php
	/**
	 * This file lists articles to user.
	 * $Id$
	 */
	require("funcs.php");

	function display_navigation_bar($brdarr,$brdnum,$start,$total,$order=FALSE)
	{
		global $section_names;
		$brd_encode = urlencode($brdarr["NAME"]);
	?>		
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="b1">		
<form name="form1" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get">
<input type="hidden" name="board" value="<?php echo $brdarr["NAME"]; ?>"/>
<tr>
<td>
</td>
<td align="right">
 	<?php
		      if($order)
		      {
		   	if ($start <= $total - 20)
			{
		    ?>
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>">��һҳ</a>]
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&start=<?php echo $start + 20; ?>">��һҳ</a>]
		    <?php
			}
			else
			{
		    ?>
[��һҳ] 
[��һҳ]
		    <?php
			}
			if ($start > 1)
			{
		?>
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&start=<?php if($start>20) echo $start - 20; else echo "1";?>">��һҳ</a>]
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&start=1">���һҳ</a>]
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
		     	if ($start > 1)
			{
		?>
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&start=1">��һҳ</a>]
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&start=<?php if($start > 20) echo $start - 20; else echo "1"; ?>">��һҳ</a>]
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
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>&start=<?php echo $start + 20; ?>">��һҳ</a>]
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?board=<?php echo $brd_encode; ?>">���һҳ</a>]
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
</td></tr></form></table>
	<?php
	}
	
	
	function display_g_articles($brdarr,$articles,$start,$order=FALSE){
?>
<table width="100%" border="0" cellspacing="0" cellpadding="3" class="t1">
<tr><td class="t2" width="50">���</td><td class="t2" width="120">�Ƽ���</td><td class="t2" width="80">����</td><td class="t2">����</td><td class="t2">ԭ��</td></tr>
<?php
		$brd_encode = urlencode($brdarr["NAME"]);
		$i = 0;
		foreach ($articles as $article)
		{
			$title = $article["TITLE"];
			if (strncmp($title, "Re: ", 4) != 0)
				$title = "�� " . $title;
			$flags = $article["FLAGS"];
?>
<tr>
<td class="t3"><?php echo $start + $i; ?></td>
<td class="t4"><a class="ts1" href="/cgi-bin/bbs/bbsqry?userid=<?php echo $article["OWNER"]; ?>"><?php echo $article["OWNER"]; ?></a></td>
<td class="t3"><?php echo strftime("%b&nbsp;%e", $article["POSTTIME"]); ?></td>
<td class="t5">&nbsp;
<a class="ts2" href="/bbsgcon.php?board=<?php echo $brd_encode; ?>&file=<?php echo $article["FILENAME"]; ?>&num=<?php echo $start + $i; ?>"><?php echo htmlspecialchars($title); ?></a>
</td>
<td class="t4"><a href="/bbscon.php?board=<?php echo $article["O_BOARD"];?>&id=<?php echo $article["O_ID"];?>">�鿴ԭ������ԭ��</a></td>
</tr>
<?php
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
		html_init("gb2312","","",1);
		$board = "Recommend";
		$brdarr = array();
		$brdnum = bbs_getboard($board, $brdarr);
		if ($brdnum == 0)
			html_error_quit("ϵͳ����");
		$total = bbs_countarticles($brdnum, $dir_modes["DIGEST"]);
		if ($total <= 0)
			html_error_quit("Ŀǰû������");

		if (isset($_GET["start"]))
			$start = $_GET["start"];
		elseif (isset($_POST["start"]))
			$start = $_POST["start"];
		else
			$start = 0;
		settype($start, "integer");
		$artcnt = 20;
		/*
		 * �������һ��ʱ�������⣬���ܻᵼ����ű��ҡ�
		 * ԭ���������ε��� bbs_countarticles() �� bbs_getarticles()��
		 */
		if ($start == 0 || $start > ($total - $artcnt + 1))
			$start = ($total - $artcnt + 1);
		if ($start < 0)
			$start = 1;
		$articles = bbs_getarticles($board, $start, $artcnt, $dir_modes["DIGEST"]);
		if ($articles == FALSE)
			html_error_quit("��ȡ�����б�ʧ��");
	
		$brd_encode = urlencode($brdarr["NAME"]);
?>
<body>
<a name="listtop"></a>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
  <tr> 
    <td colspan="2" class="b2">
	    <a href="bbssec.php" class="b2"><?php echo BBS_FULL_NAME; ?></a>
	    -
		�Ƽ�����
    </td>
  </tr>

  <tr> 
    <td colspan="2" height="9" background="images/dashed.gif"> </td>
  </tr>
  <tr><td colspan="2" align="right" class="b1">
  <?php
  	display_navigation_bar($brdarr, $brdnum, $start, $total, $order_articles );
  ?>
  </td></tr>
  <tr> 
    <td colspan="2" align="center">
    	<?php
		display_g_articles($brdarr,$articles,$start,$order=FALSE);
	?>	
    </td>
  </tr>
  <tr><td colspan="2" align="right" class="b1">
  <?php
  	display_navigation_bar($brdarr, $brdnum, $start, $total, $order_articles);
  ?>
  </td></tr>
  <tr> 
    <td colspan="2" height="9" background="images/dashed.gif"> </td>
  </tr>

  <tr> 
    <td colspan="2" align="center" class="b1">
    	[<a href="#listtop">���ض���</a>]
    	[<a href="javascript:location=location">ˢ��</a>]
    </td>
  </tr>
</table>
<?php
	}
	html_normal_quit();
?>
