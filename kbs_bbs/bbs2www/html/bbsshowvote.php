<?php

	require("funcs.php");
login_init();
	if ($loginok != 1 || ($currentuser["userid"] == "guest") ){
		html_init("gb2312");
		html_error_quit("�����û�û��ͶƱȨ��");
	}else
	{
		html_init("gb2312");

		if(isset($_GET["board"]))
			$board = $_GET["board"];
		else
			html_error_quit("����������");

		$brdarr = array();
		$brdnum = bbs_getboard($board,$brdarr);
		if($brdnum == 0)
			html_error_quit("�����������");

		if(bbs_checkreadperm($currentuser["index"],$brdnum)==0)
			html_error_quit("��û��Ȩ��");

		$votearr = array();
		$retnum = bbs_get_votes($board,$votearr);

		if( $retnum < 0 )
			$retnum = 0;
?>
<body>
<center><p><?php echo BBS_FULL_NAME; ?> -- [ͶƱ�б�] [�û�:<?php echo $currentuser["userid"];?>] 
����<?php echo $board; ?>����<?php echo $retnum;?>��ͶƱ<br></p>
<hr class="default"/>
<table width="613">
<tr><td>���</td><td>����</td><td>����</td><td>������</td><td>��������</td><td>ͶƱ����</td></tr>
<?php
		for($i = 0; $i < $retnum; $i++ ){
?>
<tr><td>
<?php echo $i+1;?>
</td><td>
<a href="/bbsvote.php?board=<?php echo $board;?>&num=<?php echo $i+1;?>"><?php echo $votearr[$i]["TITLE"];?></a>
</td><td>
<?php echo $votearr[$i]["TYPE"];?>
</td><td>
<?php echo $votearr[$i]["USERID"];?>
</td><td>
<?php echo date("r",$votearr[$i]["DATE"]);?>
</td><td>
<?php echo $votearr[$i]["MAXDAY"];?>
</td></tr>
<?php
		}
?>
</table>
<hr class="default"/>
<a href="/bbsdoc.php?board=<?php echo $board;?>">���ر�������</a>
<a href="javascript:history.go(-1)">���ٷ���</a>
</center>
<?php
		html_normal_quit();

	}
?>
