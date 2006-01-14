var gTreeArts = new Array();
function treeWriter(board, gid, arts) {
	this.board = escape(board);
	this.gid = gid;
	var i, tI = new Array();
	for (i = 0; i < arts.length; i++) {
		var node = {"id": arts[i][0], "reid": arts[i][1], "owner": arts[i][2], 
			"first_child": -1, "last_child": -1, "next_sibling": -1};
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
treeWriter.prototype.s = function(idx) {
	var id = gTreeArts[idx].id;
	var owner = gTreeArts[idx].owner;
	var url = 'bbscon.php?board=' + this.board + '&id=' + id;
	var ret = '<br/>';
	ret += '<div style="border: 2px solid red; padding: 5px; margin: 5px;">';
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
	//ret += '</div><div class="article" id="art' + id + '"><div align="center">...������...</div></div>';
	ret += '</div>';
	//this.ifs += '<iframe width=0 height=0 frameborder="0" scrolling="no" src="' + url + '"></iframe>';
	document.write(ret);

	var cur = gTreeArts[idx].first_child;
	while(cur != -1) {
		this.s(cur);
		cur = gTreeArts[cur].next_sibling;
	}
	
	document.write("</div>");
};
treeWriter.prototype.o = function() {
	document.write("<pre>");
	this.s(0);
	document.write("</pre>");
	document.write(this.ifs);
};

