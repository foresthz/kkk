<?php
require("inc/funcs.php");

require("inc/usermanage.inc.php");

require("inc/user.inc.php");

setStat("�쿴�ʼ�");

show_nav();

echo "<br><br>";

$boxDesc=getMailBoxName($_GET['boxname']);

if (!isErrFounded()) {
	head_var($userid."��".$boxDesc,"usermailbox.php?boxname=".$_GET['boxname'],0);
}

if ($loginok==1) {
	main();
}else {
	foundErr("��ҳ��Ҫ������ʽ�û���ݵ�½֮����ܷ��ʣ�");
}


if (isErrFounded()) {
		html_error_quit();
} 
show_footer();

function main(){
	global $_GET;
	global $boxDesc;
	$boxName=$_GET['boxname'];
	if (!isset($_GET['num'])) {
		foundErr("����ָ�����ż�������!");
		return false;
	}
	$num=intval($_GET['num']);
	if ($boxName=='') {
		$boxName='inbox';
	}
	if ($boxName=='inbox') {
		showmail('inbox','.DIR','�ռ���', $num);
		return true;
	}
	if ($boxName=='sendbox') {
		showmail('sendbox','.SENT','������',$num );
		return true;
	}
	if ($boxName=='deleted') {
		showmail('deleted','.DELETED','������',$num);
		return true;
	}
	foundErr("��ָ���˴�����������ƣ�");
	return false;
}

function showmail($boxName, $boxPath, $boxDesc, $num){
	global $currentuser;
?>
<table cellpadding=3 cellspacing=1 align=center class=tableborder1>
            <tr>
                <th colspan=3>��ӭʹ���ʼ����ܣ�<?php echo $currentuser['userid'] ; ?></th>
            </tr>

<?php
	$dir = bbs_setmailfile($currentuser["userid"],$boxPath);

	$total = filesize( $dir ) / 256 ;
	if( $total <= 0 ){
?>
<tr><td>����ָ�����ż������ڡ�
</td></tr>
</table>
<?php
		return false;
	}
	$articles = array ();
	if( bbs_get_records_from_num($dir, $num, $articles) ) {
		$file = $articles[0]["FILENAME"];
	}else{
?>
<tr><td>����ָ�����ż������ڡ�
</td></tr>
</table>
<?php
		return false;
	}

	$filename = bbs_setmailfile($currentuser["userid"],$file) ;

	if(! file_exists($filename)){
?>
<tr><td>����ָ�����ż������ڡ�
</td></tr>
</table>
<?php
		return false;
	}
?>
    <tr>
	    <td class=tablebody1 valign=middle align=center colspan=3><a href="deleteusermail.php?file=<?php echo $file; ?>&boxname=<?php echo $boxName; ?>"><img src="pic/m_delete.gif" border=0 alt="ɾ���ʼ�"></a> &nbsp; <a href="sendmail.php"><img src="pic/m_write.gif" border=0 alt="������Ϣ"></a> &nbsp;<a href="sendmail.php?num=<?php echo $num ;?>&boxname=<?php echo $boxName; ?>"><img src="pic/m_reply.gif" border=0 alt="�ظ���Ϣ"></a>&nbsp;<a href="forwardusermail.php?num=<?php echo $num ;?>"><img src=pic/m_fw.gif border=0 alt=ת����Ϣ></a></td>
    </tr>
    <tr><td class=tablebody2 height=25>
		    <b><?php echo $articles[0]['OWNER'] ;?></b> �� <b><?php echo strftime("%Y-%m-%d %H:%M:%S", $articles[0]['POSTTIME']); ?></b> �������͵��ż���<b>[<?php echo htmlspecialchars($articles[0]['TITLE'],ENT_QUOTES) ;?>]</b>
</td>
                </tr>
                <tr>
                    <td  class=tablebody1 valign=top align=left>
					<b>
<?php
					echo bbs_printansifile($filename);
?>
	</b>
	&nbsp;
                    </td>
                </tr>
				<tr align=center><td width="100%" class=tablebody2>
				<a href="<?php   echo 'usermailbox.php?boxname='.$boxName; ?>"> << <?php echo '����'.$boxDesc; ?></a>
				</td></tr>
                </table>
<?php
		bbs_setmailreaded($dir,$num);
}

?>