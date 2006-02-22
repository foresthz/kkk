/**
 * Part of the KBS BBS Code
 * Copyright (c) 2005-2006 KBS Development Team. (http://dev.kcn.cn/)
 * Source file is subject to the pending KBS License.
 *
 * You may use and/or modify the source code only for pure personal study
 * purpose (e.g. using it in a public website is not acceptable), unless
 * you get explicit permission by the KBS Development Team.
 */

function annWriter(path, perm_bm) {
	this.path = path;
	this.perm_bm = perm_bm;
	this.num = 1;
	var str;
	str = '<form id="frmAnnounce" action="bbs0anbm.php?path=' + path + '" method="post">';
	str += '<div class="smaller" style="float:right">����������ģʽ�����ڵ�ǰĿ¼<span style="color:#FF0000">';
	str += perm_bm ? '��' : 'û��';
	str += '</span>����Ȩ�ޡ�</div>';
	str += '<table class="main wide"><col width="5%" /><col width="8%" /><col width="4%" /><col width="38%" />';
	str += '<col width="10%" /><col width="10%" /><col width="10%" /><tr><th>#</th><th>����</th><th></th>';
	str += '<th>����</th><th>����</th><th>' + (perm_bm?'�ļ���':'����') + '</th><th>����</th></tr><tbody>';
	document.write(str);
}
annWriter.prototype.i = function(type, title, bm, filename, date) {
	var str, itempath;
	str = '<tr><td class="center">' + this.num + '</td><td class="center">';
	switch(type) {
		case 0:
			str += '����';
			break;
		case 1:
			str += 'Ŀ¼';
			break;
		case 2:
		case 3:
		default:
			str += '�ļ�';
			break;
	}
	str += '</td><td class="center"><input type="checkbox" name="ann' + this.num + '" value="' + filename + '"></td><td>';
	itempath = this.path + '%2F' + filename;
	if (type == 1)
		str += '<a href="bbs0anbm.php?path=' + itempath + '">';
	else if (type >= 2)
		str += '<a href="bbsanc.php?path=' + itempath + '">';
	str += title + '</a></td><td>' + bm + '</td><td>';
	str += this.perm_bm ? (filename + ((type == 1) ? '/' : '')) : date;
	str += '</td><td>';
	if (type == 1)
		str += '<a href="bbs0anbm_editdir.php?path=' + itempath + '">�޸�</a>';
	else if (type >= 2)
		str += '<a href="bbs0anbm_editfile.php?path=' + itempath + '">�༭</a>';
	str += ' <a href="#">����</a>';
	str += '</td></tr>';
	document.write(str);
	this.num++;
};
annWriter.prototype.f = function() {
	var str;
	str = '</tbody></table>';
	if (this.perm_bm)
	{
		str += '<br><div class="center smaller">';
		str += '[<a href="bbs0anbm_mkdir.php?path=' + this.path + '">����Ŀ¼</a>] ';
		str += '[<a href="bbs0anbm_mkfile.php?path=' + this.path + '">�����ļ�</a>] ';
		str += '[<a href="javascript:ann_cutcopy(0);">����</a>] ';
		str += '[<a href="javascript:ann_cutcopy(1);">����</a>] ';
		str += '[<a href="javascript:ann_paste();">ճ��</a>] ';
		str += '[<a href="javascript:ann_delete();">ɾ��</a>]';
	}
	str += '</form>';
	document.write(str);
};