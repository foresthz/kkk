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

		if(isset($_GET["num"]))
			$num = $_GET["num"];
		else
			html_error_quit("��������2");

		if($num <= 0)
			html_error_quit("��������3");

		$brdarr = array();
		$brdnum = bbs_getboard($board,$brdarr);
		if($brdnum == 0)
			html_error_quit("�����������");

		if(bbs_checkreadperm($currentuser["index"],$brdnum)==0)
			html_error_quit("��û��Ȩ��");

		$votearr = array();
		$retnum = bbs_get_tmpl_from_num($board,$num,$votearr);

		if( $retnum <= 0 )
			html_error_quit("����");
?>
<body>
<center><p><?php echo BBS_FULL_NAME; ?> -- [������ϸ��ʾ] [�û�:<?php echo $currentuser["userid"];?>] 
����<?php echo $board; ?>��<?php echo $retnum;?>������<br></p>
<hr class="default"/>
<table width="613">
<tr>
<td>����˵��</td><td><?php echo $votearr[0]["TITLE"];?></td>
</tr>
<tr>
<td>�������</td><td><?php echo $votearr[0]["CONT_NUM"];?></td>
</tr>
<tr>
<td>�����ʽ</td><td><?php echo $votearr[0]["TITLE_TMPL"];?></td>
</tr>
</table>
<hr class="default"/>
<?php
		if( $votearr[0]["FILENAME"] != "" ){
?>
���ĸ�ʽ����:<br>
</center>
<?php
			echo bbs_printansifile($votearr[0]["FILENAME"]);
?>
<center>
<hr class="default"/>
<?php
		}
?>
<table width="613">
<tr><td>�������</td><td>����</td><td>�ش𳤶�</td></tr>
<?php
		for($i = 0; $i < $votearr[0]["CONT_NUM"]; $i++ ){
?>
<tr><td>
<?php echo $i+1;?>
</td><td>
<?php echo $votearr[$i+1]["TEXT"];?>
</td><td>
<?php echo $votearr[$i+1]["LENGTH"];?>
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
