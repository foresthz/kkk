<?php
    require("funcs.php");
login_init();
    if ($loginok != 1)
        html_nologin();
    else
    {
        html_init("gb2312");
        $filename=bbs_sethomefile($currentuser["userid"],"signatures");
        if (isset($_GET['type']))
            if ($_GET["type"]=="1") {
                if(!strcmp($currentuser["userid"],"guest"))
                    html_error_quit("���ܸ�guest����ǩ����!");
                $fp=@fopen($filename,"w+");
                        if ($fp!=false) {
                fwrite($fp,str_replace("\r\n", "\n", $_POST["text"]));
                fclose($fp);
                        }
                bbs_recalc_sig();
            }
?>
<body>
<center><?php echo BBS_FULL_NAME; ?> -- ����ǩ���� [ʹ����: <?php echo $currentuser["userid"]; ?>]</center><hr />
<form name=form1 method="post" action="bbssig.php?type=1">
ǩ����ÿ6��Ϊһ����λ, �����ö��ǩ����.<table width="610" border="1"><tr><td><textarea name="text"  onkeydown='if(event.keyCode==87 && event.ctrlKey) {document.form1.submit(); return false;}'  onkeypress='if(event.keyCode==10) return document.form1.submit()' rows="20" cols="100" wrap="physical">
<?php
if (!isset($_GET['type']) || ($_GET["type"]!="1")) {
    $fp = @fopen ($filename, "r");
    if ($fp!=false) {
        while (!feof ($fp)) {
            $buffer = fgets($fp, 300);
            echo $buffer;
        }
        fclose ($fp);
    }
}
else {
    echo $_POST["text"];
}
?>
</textarea></table>
<input type="submit" value="����" /> <input type="reset" value="��ԭ" />
</form><hr />
</body>
</html>
<?php
    }
?>
