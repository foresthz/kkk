<?php
require("site.php");
if (!bbs_ext_initialized())
	bbs_init_ext();

# iterate through an array of nodes
# looking for a text node
# return its content
function get_content($parent)
{
    $nodes = $parent->child_nodes();
    while($node = array_shift($nodes))
        if ($node->node_type() == XML_TEXT_NODE)
            return $node->node_value();
    return "";
}

# get the content of a particular node
function find_content($parent,$name)
{
    $nodes = $parent->child_nodes();
    while($node = array_shift($nodes))
        if ($node->node_name() == $name)
            return get_content($node);
    return "";
}

# get an attribute from a particular node
function find_attr($parent,$name,$attr)
{
    $nodes = $parent->child_nodes();
    while($node = array_shift($nodes))
        if ($node->node_name() == $name)
            return $node->get_attribute($attr);
    return "";
}

?>
<?php
function gen_hot_subjects_html()
{
# load xml doc
$hotsubject_file = BBS_HOME . "/xml/day.xml";
$doc = domxml_open_file($hotsubject_file) or die("Can't open hot subject file!");

$root = $doc->document_element();
$boards = $root->child_nodes();


$brdarr = array();
?>
	<table width="600" border="0" cellpadding="0" cellspacing="0" background="images/lan1.gif" class="title">
        <tr> 
		  <td width="23">&nbsp;</td>
          <td>&gt;&gt;�����ȵ㻰������&gt;&gt;</td>
        </tr>
	</table>
	<table border="0" cellpadding="0" cellspacing="0" width="600">
<?php
# shift through the array
while($board = array_shift($boards))
{
    if ($board->node_type() == XML_TEXT_NODE)
        continue;

    $hot_title = find_content($board, "title");
    $hot_author = find_content($board, "author");
    $hot_board = find_content($board, "board");
    $hot_time = find_content($board, "time");
    $hot_number = find_content($board, "number");
    $hot_groupid = find_content($board, "groupid");

	$brdnum = bbs_getboard($hot_board, $brdarr);
	if ($brdnum == 0)
		continue;
	$brd_encode = urlencode($brdarr["NAME"]);
?>
              <tr height="22"> 
<td width="15" class="MainContentText"></td>
                <td class="MainContentText"><li><a href="/cgi-bin/bbs/bbstfind?board=<?php echo $brd_encode; ?>&title=<?php echo urlencode(iconv("UTF-8", "GBK", $hot_title)); ?>"><?php echo htmlspecialchars(iconv("UTF-8", "GBK", $hot_title)); ?></a>&nbsp;&nbsp;[����: <a href="/cgi-bin/bbs/bbsqry?userid=<?php echo $hot_author; ?>"><?php  echo $hot_author; ?></a>]&nbsp;&nbsp;&lt;<a href="/bbsdoc.php?board=<?php echo $brd_encode; ?>"><?php  echo htmlspecialchars($brdarr["DESC"]); ?></a>&gt;</li></td>
              </tr>
<?php
}
?>
		</table>
<?php
}

function gen_sections_html()
{
global $section_nums;
global $section_names;

# load xml doc
$boardrank_file = BBS_HOME . "/xml/board.xml";
$doc = domxml_open_file($boardrank_file) or die("What boards?");


$root = $doc->document_element();
$boards = $root->child_nodes();

$sec_count = count($section_nums);
$sec_boards = array();
$sec_boards_num = array();
for ($i = 0; $i < $sec_count; $i++)
{
	$sec_boards[$i] = array();
	$sec_boards_num[$i] = 0;
}

# shift through the array
while($board = array_shift($boards))
{
    if ($board->node_type() == XML_TEXT_NODE)
        continue;

    $ename = find_content($board, "EnglishName");
    $cname = find_content($board, "ChineseName");
    $visittimes = find_content($board, "VisitTimes");
    $staytime = find_content($board, "StayTime");
    $secid = find_content($board, "SecId");
	$sec_boards[$secid][$sec_boards_num[$secid]]["EnglishName"] = $ename;
	$sec_boards[$secid][$sec_boards_num[$secid]]["ChineseName"] = iconv("UTF-8", "GBK", $cname);
	$sec_boards[$secid][$sec_boards_num[$secid]]["VisitTimes"] = $visittimes;
	$sec_boards[$secid][$sec_boards_num[$secid]]["StayTime"] = $staytime;
	$sec_boards_num[$secid]++;
}
?>
	<table width="600" border="0" cellpadding="0" cellspacing="0" background="images/lan3.gif" class="title">
        <tr> 
		  <td width="23">&nbsp;</td>
          <td>&gt;&gt;���ྫ��������&gt;&gt;</td>
        </tr>
	</table>
		<table border="0" cellpadding="0" cellspacing="0" width="600">
<?php
	for ($i = 0; $i < $sec_count; $i += 3)
	{
?>
        <tr> 
<?php
		for ($j = $i; $j < $i + 3; $j++)
		{
			if ($j < $sec_count)
			{
?>
          <td width="200" height="107" valign="top"  class="MainContentText"> 
<strong><?php echo $section_nums[$j]; ?>. [<a href="bbsboa.php?group=<?php echo $j; ?>"><?php echo htmlspecialchars($section_names[$j][0]); ?></a>]</strong><br><br>
<?php
			$brd_count = $sec_boards_num[$j] > 5 ? 5 : $sec_boards_num[$j];
			for ($k = 0; $k < $brd_count; $k++)
			{
?>
&nbsp;&lt;<a href="bbsdoc.php?board=<?php echo urlencode($sec_boards[$j][$k]["EnglishName"]); ?>"><?php echo $sec_boards[$j][$k]["ChineseName"]; ?></a>&gt;<br>
<?php
			}
?>
<br>
<div align="right"><a href="bbsboa.php?group=<?php echo $j; ?>">�������&gt;&gt;</a>&nbsp;&nbsp;</div>
</td>
<?php
			}
			else
			{
?>
<td width="33%" height="107" valign="top"  class="MainContentText">&nbsp;</td>
<?php
			}
			if ($j != $i + 2)
			{
?>
<td width="1" bgcolor="FFFFFF"></td>
<?php
			}
		}
?>
        </tr>
<?php
		if ($sec_count - $i > 3)
		{
?>
        <tr> 
          <td colspan="5" height="1" bgcolor="FFFFFF"></td>
        </tr>
<?php
		}
	}
?>
      </table>
<?php
}

function gen_recommend_boards_html()
{
	$rcmd_boards = array(
		"PieBridge",
		"Movie",
		"Love",
		"AdvancedEDU",
		"Game"
		);
?>
      <table width="150" height="18" border="0" cellpadding="0" cellspacing="0" class="helpert">
        <tr> 
          <td width="16" background="images/lt.gif">&nbsp;</td>
          <td width="66" bgcolor="#0066CC">�Ƽ�����</td>
          <td width="16" background="images/rt.gif"></td>
          <td>&nbsp;</td>
        </tr>
      </table>
      <table width="150" border="0" cellpadding="0" cellspacing="0" class="helper">
<?php
	$brdarr = array();
	for ($i = 0; $i < count($rcmd_boards); $i++)
	{
			$brdnum = bbs_getboard($rcmd_boards[$i], $brdarr);
			if ($brdnum == 0)
				continue;
			$brd_encode = urlencode($brdarr["NAME"]);
?>
              <tr> 
                <td width="170" height="20" class="MainContentText"><li>&lt;<a href="bbsdoc.php?board=<?php echo $brd_encode; ?>"><?php echo htmlspecialchars($brdarr["DESC"]); ?></a>&gt;</li></td>
              </tr>
<?php
	}
?>
      </table>
      <br>
<?php
}

function gen_board_rank_html()
{
# load xml doc
$boardrank_file = BBS_HOME . "/xml/board.xml";
$doc = domxml_open_file($boardrank_file) or die("What boards?");


$root = $doc->document_element();
$boards = $root->child_nodes();

?>
      <table width="150" height="18" border="0" cellpadding="0" cellspacing="0" class="helpert">
        <tr> 
          <td width="16" background="images/lt.gif">&nbsp;</td>
          <td width="66" bgcolor="#0066CC">��������</td>
          <td width="16" background="images/rt.gif"></td>
          <td>&nbsp;</td>
        </tr>
      </table>
      <table width="150" border="0" cellpadding="0" cellspacing="0" class="helper">
<?php
$i = 0;
# shift through the array
while($board = array_shift($boards))
{
	if ($board->node_type() == XML_TEXT_NODE)
		continue;

	$ename = find_content($board, "EnglishName");
	$cname = find_content($board, "ChineseName");
?>
              <tr> 
                <td height="20" class="MainContentText"><?php echo $i+1; ?>. <a href="bbsdoc.php?board=<?php echo urlencode($ename); ?>"><?php echo htmlspecialchars(iconv("UTF-8", "GBK", $cname)); ?></a></td>
              </tr>
<?php
	$i++;
	if ($i == 5)
		break;
}
?>
      </table>
	  <br>
<?php
}
?>
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<link href="mainpage.css" rel="stylesheet" type="text/css">
</head>
<body leftmargin="5" topmargin="0" marginwidth="0" marginheight="0">
<table border="0" cellpadding="0" cellspacing="0" width="800">
  <tr> 
    <td colspan="2" height="77"><img src="images/logo.gif" width="144" height="71"></td>
    <td colspan="6" ></td>
  </tr>
  <tr> 
    <td height="18" width="84" class="header" align="center">ϵͳ����</td>
    <td width="84" class="header" align="center">�Ƽ�����</td>
    <td width="80" class="header" align="center">����������</td>
    <td width="80" class="header" align="center">�Ƽ�����</td>
    <td width="81" class="header" align="center">��������</td>
    <td width="79" class="header" align="center">����ף��</td>
    <td width="56" class="header"></td>
    <td class="header" align="right" width="315"> <input type="text" name="bsearch" size="15" maxlength="30" value="��������" class="text"> 
      <input type="button" name="search" size="15" value="GO" class="button"> 
    </td>
  </tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" width="800">
  <tr>
    <td colspan="5" height="8"></td>
  </tr>
  <tr>
    <td width="600">
<?php
	gen_hot_subjects_html();
?>
<br>
<?php
	//gen_recommend_boards_html();
	if (0)
	{
?>
<!-- �Ƽ����� ��ʱȥ��
	<table width="626" border="0" cellpadding="0" cellspacing="0" background="images/lan2.gif" class="title">
        <tr> 
		  <td width="23">&nbsp;</td>
          <td>&gt;&gt;�Ƽ�����&gt;&gt;</td>
        </tr>
	</table>
		<table border="0" cellpadding="0" cellspacing="0" width="626">
              <tr>
                <td valign="top" class="MainContentText"><p><img src="images/xia.gif" width="9" height="7"><a href="#">һ���廪���ӵı�������<br>
              </a>��ѧ����ҴҶ��ţ��е���ʧ��ϲ�ǲΰ롣��ѧ����ã����һ��æµ����������ƣ����������壬����������Ŀ��Ҳ���������ġ�ֱ����Ҫ����廪��������һ˿˿����꣬ 
              �ŷ����Լ�����ذ������廪��<br>
              <br>
            </p>
                  
            </td>
              </tr>
              <tr>
                <td valign="top" class="MainContentText"><p><img src="images/xia.gif" width="9" height="7"><a href="#">�ոտ�����ټң��ܸж���<br>
              </a>��������һ����ʵ�Ĺ��£�һ����̫����ʦ�ڻ�ɳ���������̡�ӰƬ��ս���Ĳпᡢ�¾� ��Ұ���Լ���̫�˵ı���������ӳ��һ�����š��ڵ¹���ͳ�εĻ�ɳ�����̫�˹����� 
              ����������ӰƬ�е������ܿ���������ǹɱ����̫�˵�ʬ�壬50����̫�˱�������<br>
              <br>
            </p>
                  
            </td>
              </tr>
              <tr>
                <td valign="top" class="MainContentText"><p><img src="images/xia.gif" width="9" height="7"><a href="#">[Weekend]�ֵ��ǣ�C++������ʱ����Ҫ������!!!</a><br>
              ���ڳ���Ա��˵ borlandҪ�Ƴ�100%���ϱ�׼�ı����� ���⣬ms��vc.2004Ҳ������Ϊ100%֧�ּ��ϣ�gcc�����÷�չ��ͷ��C++����Ҫ���׳�ͷ��!<br>
              <br>
            </p>
                  
            </td>
              </tr>
              <tr>
                <td valign="top" class="MainContentText"><p><img src="images/xia.gif" width="9" height="7"><a href="#">ʡǮ�󷨡��������ٻ�һ�������</a><br>
              ���ȣ���һ�������ϰ��ﳵ���붼�ڰ�Сʱ�ڵķ��ӣ����������ˮƽ���ߵ�������� ��С������Զ�����в��г������ķ��Ӻܶ࣬ƽ������1500 
              ���������г���������������������300�����ܵú�����һ���ڲ��ᶪ��ƽ������ÿ �½�ͨ��25<br>
              <br>
            </p> 
            </td>
              </tr>
              <tr>
                <td valign="top" class="MainContentText"><p><img src="images/xia.gif" width="9" height="7"><a href="#">���к���ߵ�����</a><br>
              �ڹ�����ʱ��������������е�һЩ�ֵ��ϣ��ˡ��������У���ͨ����������Ϊ�˽��������⣬���ǰ����е��Ӹߣ�ʹ���������롣Ȼ�����ڽӽ���·�ڵ� 
              �ط���������һ���͹��·���ʯͷ������ʯ����Ϊָʾ���˹��ֵı�־���˿��Բ��� ��ʯ������·��<br>
              <br>
            </p> 
            </td>
              </tr>
		</table>
�Ƽ����� -->
<?php
	}
	gen_sections_html();
?>
</td>
    <td width="1" bgcolor="0066CC"></td>
    <td width="18">&nbsp;</td>
    <td width="150" align="left" valign="top"> 
<!-- ϵͳ���濪ʼ ��ʱ���ε�
      <table width="150" height="18" border="0" cellpadding="0" cellspacing="0" class="helpert">
        <tr> 
          <td width="16" background="images/lt.gif">&nbsp;</td>
          <td width="66" bgcolor="#0066CC">ϵͳ����</td>
          <td width="16" background="images/rt.gif"></td>
          <td>&nbsp;</td>
        </tr>
      </table>
      <table width="150" border="0" cellpadding="0" cellspacing="0" class="helper">
        <tr> 
                <td height="20" class="MainContentText"><font color="#6E9E54"><img src="images/wen.gif" width="9" height="7"> 
                  ��վ��ͨ���ʷ��ʽӿ�</font></td>
              </tr>
              <tr> 
                <td height="20" class="MainContentText"><font color="#6E9E54"><img src="images/wen.gif" width="9" height="7"> 
                  �°�WEB��������</font></td>
              </tr>
              <tr> 
                <td height="20" class="MainContentText"><img src="images/wen.gif" width="9" height="7"></td>
              </tr>
              <tr> 
                <td height="20" class="MainContentText"><img src="images/wen.gif" width="9" height="7"></td>
              </tr>
              <tr> 
                <td height="20" class="MainContentText"><img src="images/wen.gif" width="9" height="7"></td>
        </tr>
      </table>
      <br>
ϵͳ�������   -->
<?php
	gen_recommend_boards_html();
	gen_board_rank_html();
?>
      <table width="150" height="18" border="0" cellpadding="0" cellspacing="0" class="helpert">
        <tr> 
          <td width="16" background="images/lt.gif">&nbsp;</td>
          <td width="66" bgcolor="#0066CC">����ף��</td>
          <td width="16" background="images/rt.gif"></td>
          <td>&nbsp;</td>
        </tr>
      </table>
      <table width="150" border="0" cellpadding="0" cellspacing="0" class="helper">
              <tr> 
                <td width="170" height="20" class="MainContentText"><font color="#FF6600"><img src="images/xin.gif" width="9" height="7"> 
                  ףΰ��������ٸ�ǿ</font></td>
              </tr>
              <tr> 
                <td height="20" class="MainContentText"><font color="#FF6600"><img src="images/xin.gif" width="9" height="7"> 
                  ףˮľ�廪��������</font></td>
              </tr>
              <tr> 
                <td height="20" class="MainContentText"><img src="images/xin.gif" width="9" height="7"></td>
              </tr>
              <tr> 
                <td height="20" class="MainContentText"><img src="images/xin.gif" width="9" height="7"></td>
              </tr>
              <tr> 
                <td height="20" class="MainContentText"><img src="images/xin.gif" width="9" height="7"></td>
              </tr>
      </table>
	  </td>
    <td width="10">&nbsp;</td>
  </tr>
</table>
<hr class="smth">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center" class="smth">��Ȩ���� &copy; BBS ˮľ�廪վ 1995-2003 <a href="certificate.html">��ICP��02002��</a></td>
  </tr>
</table>
<br>
