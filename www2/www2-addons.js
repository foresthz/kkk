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
		str += '<a href="bbs0anbm_editdir.php?path=' + itempath + '&title=' + title + '&bm=' + bm + '">�޸�</a>';
	else if (type >= 2)
		str += '<a href="bbs0anbm_editfile.php?path=' + itempath + '&title=' + title + '">�༭</a>';
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





var gTreeArts = new Array();
function treeWriter(board, gid, arts) {
	this.board = escape(board);
	this.gid = gid;
	var i, tI = new Array();
	for (i = 0; i < arts.length; i++) {
		var node = {"id": arts[i][0], "reid": arts[i][1], "owner": arts[i][2], 
			"first_child": -1, "last_child": -1, "next_sibling": -1, "showed": false};
		gTreeArts[i] = node;
		tI[node.id] = i + 1;
		if (i > 0 && tI[node.reid]) {
			var parent = gTreeArts[tI[node.reid] - 1];
			if (parent.first_child == -1) parent.first_child = i;
			if (parent.last_child != -1) gTreeArts[parent.last_child].next_sibling = i;
			parent.last_child = i;
		}
	}
	this.ifs = "";
}
treeWriter.prototype.s = function(idx, flag) { /* flag: -1: root, 1: last */
	if (gTreeArts[idx].showed) return;
	gTreeArts[idx].showed = true;
	var id = gTreeArts[idx].id;
	var owner = gTreeArts[idx].owner;
	var url = 'bbscon.php?board=' + this.board + '&id=' + id;
	var ret = '<br/>';
	var c = "treeFold";
	if (flag == -1) c = "treeFoldRoot";
	else if (flag == 1) c = "treeFoldLast";
	
	ret += '<div class="' + c + '">';
	if (flag == 0) {
		ret += '<div class="treeFoldLeaf"> </div>';
	}
	ret += '<div class="tconPager smaller left">';
	ret += '[<a href="' + url + '">��ƪȫ��</a>] ';
	if (isLogin()) {
		ret += '[<a href="bbspst.php?board=' + this.board + '&reid=' + id + '">�ظ�����</a>] ';
		ret += '[<a href="bbspstmail.php?board=' + this.board + '&id=' + id + '">���Ÿ�����</a>] ';
	}
	ret += '[��ƪ���ߣ�<a href="bbsqry.php?userid=' + owner + '">' + owner + '</a>] ';
	ret += '[<a href="bbsdoc.php?board=' + this.board + '">����������</a>] ';
	ret += '[<a href="#top">���ض���</a>]';
	ret += '<div class="tnum">' + (idx+1) + '</div>';
	ret += '</div><div class="article" id="art' + id + '"><div align="center">...������...</div></div>';
	this.ifs += '<iframe width=0 height=0 frameborder="0" scrolling="no" src="' + url + '"></iframe>';
	document.write(ret);

	var cur = gTreeArts[idx].first_child;
	while(cur != -1) {
		this.s(cur, (cur == gTreeArts[idx].last_child) ? 1 : 0);
		cur = gTreeArts[cur].next_sibling;
	}
	
	document.write("</div>");
};
treeWriter.prototype.o = function() {
	var i;
	this.s(0, -1);
	for(i=1;i<gTreeArts.length;i++) this.s(i, -1); //û���ϸ�����Щ֦��
	document.write(this.ifs);
};
