<?php

    function admin_deny()
    {
        html_error_quit("��û�н���˹���ҳ���Ȩ�ޡ�");
    }

    function admin_header($title_simple, $title_full)
    {
        page_header($title_simple, "ϵͳ����");
        print("<br><div align=\"center\"><strong>{$title_full}</strong></div><hr><br>");
    }

    require("www2-funcs.php");
    login_init();  
    assert_login();
?>
