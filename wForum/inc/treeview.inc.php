<?php

/*
 * ���λظ��ṹ��ں��� showTree(boardName, groupID, articles, displayFN)
 *
 * @param boardName    ����Ӣ������
 * @param groupID      ������� groupID
 * @param articles     �� bbs_get_threads_from_gid() �����ƺ������ص� $articles ����
 * @param displayFN    ������ʾÿ�����ӵĻص�����
 *        displayFN(boardName,groupID,article,startNum,level,lastflag)
 *        @param boardName,groupID    ͬ��
 *        @param article              ��ƪ����
 *        @param startNum             ��ƪ��������������е����
 *        @param level                ����ڼ��㡣ԭ�� level = 0
 *        @param lastflag             $lastflag[$l] ��ʾ�� $l ���Ƿ��и���ظ�
 *
 * @author atppp
 */

class TreeNode {
	var $data;
	var $index;
	var $showed;
	var $first_child;
	var $last_child;
	var $next_sibling;
	
	function TreeNode($data, $index) {
		$this->data = &$data;
		$this->index = $index;
		$this->showed = false;
		$this->first_child = $this->last_child = $this->next_sibling = null;
	}
	
	function addChild(&$node) { /* here it's very important to assign by reference */
		if ($this->first_child == null) $this->first_child = &$node;
		if ($this->last_child != null) {
			$this->last_child->next_sibling = &$node;
		}
		$this->last_child = &$node;
	}
}

function showTree($boardName,$groupID,$articles,$displayFN) {
	$threadNum = count($articles);
	$more = ($threadNum > 100);
	if ($more) $threadNum = 100; //�����ʾ100�����������ɡ�
	
	/* �����ظ����ṹ */
	$treenodes = array();
	for($i=0; $i < $threadNum; $i++) {
		$treenodes[$i] = new TreeNode($articles[$i], $i);
	}
	for($i=1; $i < $threadNum; $i++) {
		for ($j=0; $j < $i; $j++) {
			if ($articles[$i]['REID'] == $articles[$j]['ID']) {
				$treenodes[$j]->addChild($treenodes[$i]);
				break;
			}
		}
	}
	
	$lastflag = array();
	showTreeRecursively($boardName, $groupID, $treenodes, 0, 0, $lastflag, $displayFN);
	for($i=0; $i < $threadNum; $i++) { // �����ĺ��ӣ�û�е�������
		if (!$treenodes[$i]->showed)
			$displayFN($boardName, $groupID, $treenodes[$i]->data, $i, 0, $lastflag);
	}	
	if ($more) echo '<tr><td class=TableBody1 width="100%" height=25 style="color:red"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;... ���и��� ...</td></tr>';
}

function showTreeRecursively($boardName, $groupID, &$treenodes, $index, $level, &$lastflag, $displayFN) {
	$displayFN($boardName, $groupID, $treenodes[$index]->data, $index, $level, $lastflag);
	$treenodes[$index]->showed = true;
	$cur = &$treenodes[$index]->first_child;
	while($cur != null) {
		$temp = &$cur->next_sibling;
		$lastflag[$level] = ($temp != null);
		showTreeRecursively($boardName, $groupID, $treenodes, $cur->index, $level+1, $lastflag, $displayFN);
		$cur = &$temp;
	}
}

?>
