<?php
require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");

setStat("�û��ղذ���");

requireLoginok();

show_nav();	

showUserMailbox();
head_var($userid."�Ŀ������","usermanagemenu.php",0);
main();	

show_footer();

function main()	{
	global $currentuser;
	
	if (isset($_GET["select"]))
		$select	= $_GET["select"];
	else
		$select	= 0;
	settype($select, "integer");

	if ($select	< 0) {
		foundErr("����Ĳ���");
	}
	if (bbs_load_favboard($select)==-1) {
		foundErr("����Ĳ���");
	}

    if (isset($_GET["delete"])) {
        $delete_s=$_GET["delete"];
        settype($delete_s,"integer");
        bbs_del_favboard($select,$delete_s);
    }
    if (isset($_GET["deldir"])) {
        $delete_s=$_GET["deldir"];
        settype($delete_s,"integer");
        bbs_del_favboarddir($select,$delete_s);
    }
    if (isset($_GET["dname"])) {
        $add_dname=trim($_GET["dname"]);
        if ($add_dname)
            bbs_add_favboarddir($add_dname);
    }
    if (isset($_GET["bname"])) {
        $add_bname=trim($_GET["bname"]);
        if ($add_bname)
            $sssss=bbs_add_favboard($add_bname);
    }
	showSecs($select, 0, true, 1); //������������ isFold����ʱ�趨Ϊ��Զչ�������Ҫ���� showSecs() ����ҲҪ�ġ�- atppp
?>
<center>
<form action=favboard.php>����Ŀ¼: ����Ŀ¼����&nbsp;<input name=dname size=24 maxlength=20 type=text value="">&nbsp;<input type=submit value=ȷ��><input type=hidden name=select value=<?php echo $select;?>></form>
<form action=favboard.php>���Ӱ���: ����Ӣ�İ���&nbsp;<input name=bname size=24 maxlength=20 type=text value="">&nbsp;<input type=submit value=ȷ��><input type=hidden name=select value=<?php echo $select;?>></form>
<!-- ���...wForum�ƺ���Ӣ�İ���ֻ�����ڲ����ݽ������û���ֱ�ӽӴ�Ӣ�İ���������ط���Ҫ���ǿ��� -->
</center>
<?php
}
?>
