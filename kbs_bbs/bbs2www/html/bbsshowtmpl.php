<?php

	require("funcs.php");
	if ($loginok != 1 || ($currentuser["userid"] == "guest") ){
		html_init("gb2312");
		html_error_quit("�����û�û�з���Ȩ��");
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
		$retnum = bbs_get_tmpls($board,$votearr);

		if( $retnum < 0 )
			$retnum = 0;
?>
<body>
<center><p><?php echo BBS_FULL_NAME; ?> -- [�����б�] [�û�:<?php echo $currentuser["userid"];?>] 
����<?php echo $board; ?>����<?php echo $retnum;?>������<br></p>
<hr class="default"/>
<table width="613">
<tr><td>���</td><td>����</td><td>����</td><td>�������</td><td></td></tr>
<?php
		for($i = 0; $i < $retnum; $i++ ){
?>
<tr><td>
<?php echo $i+1;?>
</td><td>
<a href="/bbsatmpl.php?board=<?php echo $board;?>&num=<?php echo $i+1;?>"><?php echo $votearr[$i]["TITLE"];?></a>
</td><td>
<?php echo $votearr[$i]["TITLE"];?>
</td><td>
<?php echo $votearr[$i]["CONT_NUM"];?>
</td><td>
<a href="/bbspsttmpl.php?board=<?php echo $board;?>&num=<?php echo $i+1;?>">ʹ�ñ����巢��</a>
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
