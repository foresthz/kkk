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

		if( $votearr[0]["CONT_NUM"] <= 0 )
			html_error_quit("��ģ���ݲ���ʹ��");
?>
<body>
<center><p><?php echo BBS_FULL_NAME; ?> -- [ģ�巢��] [�û�:<?php echo $currentuser["userid"];?>] 
����<?php echo $board; ?>ʹ�õ�<?php echo $retnum;?>��ģ��<br></p>
<hr class="default"/>
<?php
	if($_POST["submit"]){

		$tmpfile = "tmp/".$currentuser["userid"].".tmpl.tmp" ;
		$title = bbs_make_tmpl_file($board, $num, $_POST["text0"], $_POST["text1"], $_POST["text2"], $_POST["text3"], $_POST["text4"], $_POST["text5"], $_POST["text6"], $_POST["text7"], $_POST["text8"], $_POST["text9"], $_POST["text10"], $_POST["text11"], $_POST["text12"], $_POST["text13"], $_POST["text14"], $_POST["text15"], $_POST["text16"], $_POST["text17"], $_POST["text18"], $_POST["text19"], $_POST["text20"]);

		if( $title == "" )
			html_error_quit("error");

		echo "����:".$title;
?>
<hr class="default"/>
<?php
		echo bbs_printansifile($tmpfile);
?>
<hr class="default"/>
<form name="postform" method="post" action="/cgi-bin/bbs/bbssnd?board=<?php echo $board; ?>">
<input type="hidden" name="tmpl" value=1>
<input type="hidden" name="title" value="<?php echo $title;?>">
<input type="submit" value="����" />
<input type="reset" value="���" />
</form>
<?php
	}else{
?>
<table width="613">
<tr>
<td>ģ��˵��</td><td><?php echo $votearr[0]["TITLE"];?></td>
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
<form action="<?php echo $_SERVER['PHP_SELF'];?>?board=<?php echo $board;?>&num=<?php echo $num?>" method="post">
<table width="613">
<?php
		if( $votearr[0]["TITLE_TMPL"]=="" || strstr($votearr[0]["TITLE_TMPL"], "[$0]") ){
?>
<tr><td>
���±���([$0]):<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=text name=text0 size=80 maxlength=80 value=<?php echo $_POST["text0"]?>>
</td></tr>
<?php	}
?>
<?php
		for($i = 0; $i < $votearr[0]["CONT_NUM"]; $i++ ){
?>
<tr><td>
����<?php echo $i+1;?>:<?php echo $votearr[$i+1]["TEXT"];?>(�<?php echo $votearr[$i+1]["LENGTH"];?>�ֽ�)([$<?php echo $i+1;?>])
</td></tr><tr><td>
<?php
			if( strstr( $votearr[0]["TITLE_TMPL"], "[$".($i+1)."]" ) ){
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=text name=text<?php echo $i+1;?> size=<?php if( $votearr[$i+1]["LENGTH"] > 80 ) echo "80"; else echo $votearr[$i+1]["LENGTH"];?> maxlength=<?php echo $votearr[$i+1]["LENGTH"];?> value=<?php echo $_POST["text".($i+1)];?>>
<?php
			}else{
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<textarea name=text<?php echo $i+1;?> rows=5 cols=80 wrap="physical" maxlength=<?php echo $votearr[$i+1]["LENGTH"];?> value=<?php echo $_POST["text".($i+1)];?>></textarea>
<?php
			}
?>
</td></tr>
<?php
		}
?>
</table>
<input type=submit name=submit value="ȷ��">
</form>
<?php
	}
?>
<hr class="default"/>
<a href="/bbsdoc.php?board=<?php echo $board;?>">���ر�������</a>
<a href="javascript:history.go(-1)">���ٷ���</a>
</center>
<?php
		html_normal_quit();

	}
?>
