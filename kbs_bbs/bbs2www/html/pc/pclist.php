<?php
require("pcfuncs.php");
require("pcstat.php");
require("pcmainfuncs.php");

if(pc_update_cache_header(60))
	return;

function pcmain_hot_users($link,$period,$color)
{
	$users = getHotUsersByPeriod($link,$period,50);
	for($i = 0;$i < count($users) ; $i ++)
		echo "<font color=\"".$color."\">".($i+1)."</font>&nbsp;<a href=\"/pc/index.php?id=".$users[$i][username]."\" title=\"".htmlspecialchars($users[$i][corpusname])."\">".html_format(html_format_fix_length($users[$i][corpusname],20))."</a>".
		     "\n<a href=\"/bbsqry.php?userid=".$users[$i][username]."\"><font class=low2>".$users[$i][username]."</font></a><br />";
}

function pcmain_hot_nodes($link,$period,$color)
{
	$nodes = getHotNodesByPeriod($link,$period,50);
	for($i = 0;$i < count($nodes) ; $i ++)
		echo "<font color=\"".$color."\">".($i+1)."</font>&nbsp;<a href=\"/pc/pccon.php?id=".$nodes[$i][uid]."&nid=".$nodes[$i][nid]."&s=all\" title=\"".htmlspecialchars($nodes[$i][subject])."\">".html_format(html_format_fix_length($nodes[$i][subject],32))."</a><br />";
}



$link = pc_db_connect();
pcmain_html_init();

?>
<form action="pcsearch.php" method="get" onsubmit="if(this.keyword.value==''){alert('������ؼ���');return false;}">
<tr>
	<td bgcolor="#F6F6F6">
	<table width="100%"><tr><td align="left">
	BLOG����
	<input name="keyword" type="text" class="textinput" size="20"> 
	<input type="hidden" name="exact" value="0">
	<select name="key">
	<option value="u" selected>�û���</option>
	<option value="c">Blog��</option>
	<option value="d">Blog����</option>
	</select>
	<input type="submit" class="textinput" value="��ʼ��">
	</td><td align="right">
	<a href="/pc/pc.php">ע���û�</a> <?php echo getUsersCnt($link); ?> ��
	<a href="/pc/pcnew.php">��־</a> <?php echo getNodesCnt($link); ?> ��
	<a href="/pc/pcnew.php?t=c">����</a> <?php echo getCommentsCnt($link); ?> ƪ
	</td></tr></table>
	</td>
</tr>
<tr>
	<td>
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<tr>
		<td width="33%">
			<table class="table2" width="100%" cellspacing="0" cellpadding="3" >
				<tr>
					<td class="td3" bgcolor="#f75151" align="center"><b><font color="white">�������Ȳ���</font></b></td>			
				</tr>
				<tr>
					<td align="left" class="td4">
					<?php pcmain_hot_users($link,"day","#f75151"); ?>
					</td>			
				</tr>
			</table>
		</td>
		<td width="33%">
			<table class="table2" width="100%" cellspacing="0" cellpadding="3" >
				<tr>
					<td class="td3" bgcolor="#00b6ef" align="center"><b><font color="white">�������Ȳ���</font></b></td>			
				</tr>
				<tr>
					<td align="left" class="td4">
					<?php pcmain_hot_users($link,"month","#00b6ef"); ?>
					</td>			
				</tr>
			</table>
		</td>
		<td width="33%">
			<table class="table2" width="100%" cellspacing="0" cellpadding="3" >
				<tr>
					<td align="center" class="td3" bgcolor="#4cb81c"><b><font color="white">���Ȳ���</font></b></td>			
				</tr>
				<tr>
					<td align="left" class="td3">
					<?php pcmain_hot_users($link,"all","#4cb81c"); ?>
					</td>			
				</tr>
			</table>
		</td>
		</tr>
		<tr>
		<td colspan="3" bgcolor="#999999" height="3"> </td>
		</tr>
		<tr>
		<td width="33%">
			<table class="table2" width="100%" cellspacing="0" cellpadding="3" >
				<tr>
					<td class="td3" bgcolor="#adbe00" align="center"><b><font color="white">����������־</font></b></td>			
				</tr>
				<tr>
					<td align="left" class="td4">
					<?php pcmain_hot_nodes($link,"day","#adbe00"); ?>
					</td>			
				</tr>
			</table>
		</td>
		<td width="33%">
			<table class="table2" width="100%" cellspacing="0" cellpadding="3" >
				<tr>
					<td class="td3" bgcolor="#dfd581" align="center"><b><font color="white">����������־</font></b></td>			
				</tr>
				<tr>
					<td align="left" class="td4">
					<?php pcmain_hot_nodes($link,"month","#dfd581"); ?>
					</td>			
				</tr>
			</table>
		</td>
		<td width="33%">
			<table class="table2" width="100%" cellspacing="0" cellpadding="3" >
				<tr>
					<td align="center" class="td3" bgcolor="#ff00cc"><b><font color="white">������־</font></b></td>			
				</tr>
				<tr>
					<td align="left" class="td3">
					<?php pcmain_hot_nodes($link,"all","#ff00cc"); ?>
					</td>			
				</tr>
			</table>
		</td>
		</tr>
		<?php /*
		<tr>
		<td colspan="3" bgcolor="#999999" height="3"> </td>
		</tr>
		<tr>
		<td width="33%">
			<table class="table2" width="100%" cellspacing="0" cellpadding="3" >
				<tr>
					<td class="td3" bgcolor="#ffb600" align="center"><b><font color="white">����������Ŀ</font></b></td>			
				</tr>
				<tr>
					<td align="left" class="td4">
					ddd
					</td>			
				</tr>
			</table>
		</td>
		<td width="33%">
			<table class="table2" width="100%" cellspacing="0" cellpadding="3" >
				<tr>
					<td class="td3" bgcolor="#00b6ef" align="center"><b><font color="white">����������Ŀ</font></b></td>			
				</tr>
				<tr>
					<td align="left" class="td4">
					ddd
					</td>			
				</tr>
			</table>
		</td>
		<td width="33%">
			<table class="table2" width="100%" cellspacing="0" cellpadding="3" >
				<tr>
					<td align="center" class="td3" bgcolor="#f75151"><b><font color="white">������Ŀ</font></b></td>			
				</tr>
				<tr>
					<td align="left" class="td3">
					ddd
					</td>			
				</tr>
			</table>
		</td>
		</tr>
		*/ ?>
	</table>
	</td>
</tr>
<tr>
	<td height="5"> </td>
</tr>
</form>
<?php
pc_db_close($link);
pcmain_html_quit()
?>