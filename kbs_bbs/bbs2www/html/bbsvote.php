<?php

	require("funcs.php");
login_init();
	if ($loginok != 1 || ($currentuser["userid"] == "guest") )
		html_nologin();
	else
	{
		html_init("gb2312");

		if(isset($_GET["board"]))
			$board = $_GET["board"];
		else if(isset($_POST["board"]))
			$board = $_POST["board"];
		else
			html_error_quit("����������");

		if(isset($_GET["num"]))
			$num = $_GET["num"];
		else if(isset($_POST["num"]))
			$num = $_POST["num"];
		else
			html_error_quit("��������1");

		$brdarr = array();
		$brdnum = bbs_getboard($board,$brdarr);
		if($brdnum == 0)
			html_error_quit("�����������");

		if(bbs_checkreadperm($currentuser["index"],$brdnum)==0)
			html_error_quit("��û��Ȩ��");

		$oldvote = array();
		$votearr = array();
		$uservotearr = array();

		for( $i=0; $i<32; $i++){
			$oldvote[$i] = 0;
		}

		if( $_POST["submit"] ){

			if(isset($_GET["type"]))
				$votetype = $_GET["type"];
			else if(isset($_POST["type"]))
				$votetype = $_POST["type"];
			else
				html_error_quit("��������2");

			if(isset($_POST["msg"]))
				$msg = $_POST["msg"];
			else
				$msg = "";

			$votevalueint = 0;

			if( $votetype == "��ѡ" || $votetype == "�Ƿ�" ){
				if(isset($_POST["ITEM"]))
					$itemvalue = $_POST["ITEM"];
				else
					html_error_quit("��������3");

				settype($itemvalue,"integer");
				if( $itemvalue < 0 || $itemvalue > 31 )
					html_error_quit("��������4");

				if( $votetype == "�Ƿ�" && ($itemvalue < 0 || $itemvalue > 2) )
					html_error_quit("��������5");

				$votevalueint = ( 1 << $itemvalue );

			}else if( $votetype == "��ѡ" ){

				for($i = 0; $i < 32; $i++){
					$itemstr = "ITEM".($i+1);
					if(isset($_POST[$itemstr]) && $_POST[$itemstr]=="on"){
						$votevalueint += ( 1 << $i );
					}
				}

			}else if( $votetype == "����" ){
				if(isset($_POST["ITEM"]))
					$votevalueint = $_POST["ITEM"];
				else
					html_error_quit("��������3");

				settype($votevalueint,"integer");

				if( $votevalueint < 0 )
					html_error_quit("��������7");
			}else if( $votetype != "�ʴ�" )
				html_error_quit("��������8");

			$retnum = bbs_vote_num($board,$num,$votevalueint,$msg);
			if($retnum <= 0)
				html_error_quit("ͶƱ����".$retnum);
			else{
?>
ͶƱ�ɹ�
<br>
<a href="javascript:history.go(-1)">���ٷ���</a>
<?php
				html_normal_quit();

			}
		}

		$retnum = bbs_get_vote_from_num($board,$votearr,$num,$uservotearr);

		if($retnum <= 0)
			html_error_quit("��ͶƱ������");
?>
<body>
<center><p><?php echo BBS_FULL_NAME; ?> -- [ͶƱ�б�] [�û�:<?php echo $currentuser["userid"];?>] 
<?php echo $board; ?>��ͶƱ<br></p>
<hr class="default"/>
</center>
<?php
		$descdir = "vote/".$board."/desc.".$votearr[0]["DATE"] ;
		echo bbs_printansifile($descdir);
?>
<center>
<hr class="default"/>
<table width="613">
<tr><td>���</td><td><?php echo $num;?></tr>
<tr><td>����</td><td><?php echo $votearr[0]["TITLE"];?></tr>
<tr><td>����</td><td><?php echo $votearr[0]["TYPE"];?></tr>
<tr><td>������</td><td><?php echo $votearr[0]["USERID"];?></tr>
<tr><td>��������</td><td><?php echo date("r",$votearr[0]["DATE"]);?></tr>
<tr><td>ͶƱ����</td><td><?php echo $votearr[0]["MAXDAY"];?></tr>
<tr><td><?php if($uservotearr[0]["USERID"]){?>���Ѿ�ͶƱ�����ڿ��Ը���<?php }else{?>����δͶƱ<?php }?></td><td></td></tr>
</table>
<hr class="default"/>
<form action="/bbsvote.php?board=<?php echo $board;?>&num=<?php echo $num?>" method="post">
<table width="613">
<?php
		if($uservotearr[0]["USERID"]){
			if( $votearr[0]["TYPE"] != "����" ){
				for( $i =0; $i < $votearr[0]["TOTALITEMS"]; $i++){
					if( $uservotearr[0]["VOTED"] & (1 << $i) )
						$oldvote[$i] = 1;
				}
			}
		}
		if( $votearr[0]["TYPE"] == "��ѡ" ){

			for( $i=0; $i < $votearr[0]["TOTALITEMS"]; $i++){
				$itemstr = "ITEM".($i+1);
?>
<tr><td><?php echo $i+1;?></td>
<td><input type="checkbox" name="<?php echo $itemstr;?>" <?php if($oldvote[$i]) echo "checked";?>></td>
<td><?php echo $votearr[0][$itemstr];?></td></tr>
<?php
			}
?>
<input type="hidden" name="type" value="��ѡ">
<?php
		}else if( $votearr[0]["TYPE"] == "��ѡ" || $votearr[0]["TYPE"] == "�Ƿ�" ){

			for( $i=0; $i < $votearr[0]["TOTALITEMS"]; $i++){
				$itemstr = "ITEM".($i+1);
?>
<tr><td><?php echo $i+1;?></td>
<td><input type="radio" name="ITEM" value="<?php echo $i;?>" <?php if($oldvote[$i]) echo "checked";?>></td>
<td><?php echo $votearr[0][$itemstr];?></td></tr>
<?php
			}
?>
<input type="hidden" name="type" value="<?php echo $votearr[0]["TYPE"];?>">
<?php
		}else if( $votearr[0]["TYPE"] == "����" ){
?>
<tr><td></td><td></td><td>��������ֵ�����<?php echo $votearr[0]["MAXTKT"];?>:
<input type="text" name="ITEM" value="<?php echo $uservotearr[0]["VOTED"];?>"></td>
</tr>
<input type="hidden" name="type" value="<?php echo $votearr[0]["TYPE"];?>">
<?php
		}else{
?>
<input type="hidden" name="type" value="<?php echo $votearr[0]["TYPE"];?>">
<?php
		}
?>
<tr><td></td><td></td><td>���������Ľ���(����3��80��)</td></tr>
<tr><td></td><td></td><td><textarea name="msg" rows="3" cols="79" wrap="physical">
<?php echo $uservotearr[0]["MSG1"]; echo $uservotearr[0]["MSG2"]; echo $uservotearr[0]["MSG3"];?>
</textarea></td></tr>
</table>
<input type="submit" name="submit" value="ȷ��">
</form>
<hr class="default"/>
<a href="/bbsshowvote.php?board=<?php echo $board;?>">[�鿴��������ͶƱ]</a>
<a href="/bbsdoc.php?board=<?php echo $board;?>">[���ر�������]</a>
<a href="javascript:history.go(-1)">[���ٷ���]</a>
</center>
<?php
		html_normal_quit();

	}
?>
