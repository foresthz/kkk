<?php

require("inc/funcs.php");
require("inc/usermanage.inc.php");
require("inc/user.inc.php");

setStat("�޸�����");

requireLoginok();

show_nav();

showUserMailbox();
head_var($userid."�Ŀ������","usermanagemenu.php",0);
showUserManageMenu();
main();

show_footer();

function main() {
?>
<br>
<form action="dochangepasswd.php" method=POST name="theForm">
<table cellpadding=3 cellspacing=1 align=center class=TableBorder1>
<tr> 
      <th colspan="2" width="100%">�û���������
      </th>
    </tr>
<tr>    
        <td width="40%" class=TableBody1><B>������ȷ��</B>��<BR>��Ҫ�޸���������������ȷ��</td>   
        <td width="60%" class=TableBody1>    
          <input type="password" name="oldpsw" value="" size=30 maxlength=13>   
        </td>   
      </tr>  
    <tr>    
        <td width="40%" class=TableBody1><B>������</B>��<BR>��Ҫ�޸���ֱ������������������</td>   
        <td width="60%" class=TableBody1>    
          <input type="password" name="psw" value="" size=30 maxlength=13>   
        </td>   
      </tr>   
    <tr>    
        <td width="40%" class=TableBody1><B>������ȷ��</B>��<BR>����һ���������ֹ�������</td>   
        <td width="60%" class=TableBody1>    
          <input type="password" name="psw2" value="" size=30 maxlength=13>   
        </td>   
      </tr>   
    <tr align="center"> 
      <td colspan="2" width="100%"  class=TableBody2>
            <input type=Submit value="�� ��" name="Submit"> &nbsp; <input type="reset" name="Submit2" value="�� ��">
      </td>
    </tr>

    </table></form>
<?php
	return false;
}
?>
