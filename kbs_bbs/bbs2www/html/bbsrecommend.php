<?php
	/**
	 * This file lists articles to user.
	 * $Id$
	 */
	require("funcs.php");
login_init();

	function display_navigation_bar($brdarr,$brdnum,$start,$total,$order)
	{
		global $section_names;
		$brd_encode = urlencode($brdarr["NAME"]);
	?>		
<table width="100%" border="0" cellspacing="0" cellpadding="0">		
<form name="form1" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get">
<tr>
<td>
</td>
<td align="right" style="font-size:12px;">
 	<?php
		      if($order)
		      {
		   	if ($start <= $total - 20)
			{
		    ?>
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>">��һҳ</a>]
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?start=<?php echo $start + 20; ?>">��һҳ</a>]
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
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?start=<?php if($start>20) echo $start - 20; else echo "1";?>">��һҳ</a>]
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?start=1">���һҳ</a>]
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
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?start=1">��һҳ</a>]
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?start=<?php if($start > 20) echo $start - 20; else echo "1"; ?>">��һҳ</a>]
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
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>?start=<?php echo $start + 20; ?>">��һҳ</a>]
[<a class="b1" href="<?php echo $_SERVER["PHP_SELF"]; ?>">���һҳ</a>]
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
<input style="height:20px; font-size:12px; border:solid 1px; background-color:#f5f5f5" type="submit" value="��ת��"/> �� <input style="height:20px; font-size:12px; border:solid 1px;" onmouseover=this.focus() onfocus=this.select() type="text" name="start" size="3"> ƪ 
</td></tr></form></table>
	<?php
	}
	
	
	function display_g_articles($brdarr,$articles,$start,$order=FALSE){
?>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
<tr><TD width=100% align=left class=grid2 height=2 colspan=5>&nbsp;</td></tr>
<?php
		$brd_encode = urlencode($brdarr["NAME"]);
		$i = 0;
//		foreach ($articles as $article)
for( ; $i < 20; )
		{ $article = $articles[19-$i];
			$title = $article["TITLE"];
			if (strncmp($title, "Re: ", 4) != 0)
				$title = "�� " . $title;
			$flags = $article["FLAGS"];
?>
<tr>
<td width=60% align=left class=grid<?php if($i % 2) echo "1"; else echo "2";?>>
<?php echo $start +19- $i; ?>.&nbsp;[���� :<a href="/bbsrecon.php?id=<?php echo $article["ID"]; ?>"><?php echo htmlspecialchars($title); ?></a>]
</td><td width=15% class=grid<?php if($i % 2) echo "1"; else echo "2";?>>
[�Ƽ��� :<a href="/bbsqry.php?userid=<?php echo $article["OWNER"]; ?>"><?php echo $article["OWNER"]; ?></a>]
</td><td width=15% class=grid<?php if($i % 2) echo "1"; else echo "2";?>>
[���� :<a href="/bbsdoc.php?board=<?php echo $article["O_BOARD"];?>"><?php { $brddarr = array(); if(bbs_getboard($article["O_BOARD"], $brddarr)) echo $brddarr["DESC"]; }?></a>]
</td><td width=10% class=grid<?php if($i % 2) echo "1"; else echo "2";?>>
<a href="/bbscon.php?board=<?php echo $article["O_BOARD"];?>&id=<?php echo $article["O_ID"];?>">�Ķ�ԭ��</a>
</td>
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
$order_articles = TRUE;
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
<style type="text/css"><!--
body {
    font-size:12px;
    }
.b2 {
	FONT-SIZE: 14px; COLOR: #000000;BORDER-bottom: #c0d0ff 1px solid; BACKGROUND-COLOR: #f0f5ff; TEXT-DECORATION: none
}
A:hover {
	 COLOR: #000000; TEXT-DECORATION: underline
}
A:link {
	COLOR: #003399; TEXT-DECORATION: none
}
A:visited {
	COLOR: #993300; TEXT-DECORATION: none
}
td {font-size:12px;}
td.grid1{
BORDER-bottom: #0066cc 1px solid;
background-color:#f0f5ff;}
td.grid2{
BORDER-bottom: #0066cc 1px solid;
background-color:#ffffff;
}
-->
</style>

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

  <tr><td colspan="2" align="right">
  <?php
  	display_navigation_bar($brdarr, $brdnum, $start, $total, $order_articles );
  ?>
  </td></tr>
</table>

    	<?php
		display_g_articles($brdarr,$articles,$start,$order=FALSE);
	?>


<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td colspan="2" align="right">
  <?php
  	display_navigation_bar($brdarr, $brdnum, $start, $total, $order_articles);
  ?>
  </td></tr>

  <tr> 
    <td colspan="2" align="center" class="b1">
    	[<a href="javascript:location.reload()">ˢ��</a>]
    </td>
  </tr>
</table>
<?php
	}
	html_normal_quit();
?>
