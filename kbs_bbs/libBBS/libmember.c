#include "bbs.h"

#ifndef LIB_MEMBER
#ifdef ENABLE_BOARD_MEMBER
#include <mysql.h>

int load_board_member_config(const char *name, struct board_member_config *config) {
    const struct boardheader *board;
	char path[PATHLEN];
	struct stat st;
	int fd;
	
	board=getbcache(name);
    if (0==board)
        return -1;
    if (board->flag&BOARD_GROUP)
        return -2;
	
	setbfile(path, board->filename, BOARD_MEMBER_CONFIG);
	if (stat(path, &st)<0) {
        config->approve=0;
		config->max_members=MEMBER_BOARD_MAX_DEFAULT;
		config->logins=0;
		config->posts=0;
		config->score=0;
		config->level=0;
		config->board_posts=0;
		config->board_origins=0;
		config->board_marks=0;
		config->board_digests=0;
		
		return 0;
	} 
	
	bzero(config, sizeof(struct board_member_config));
	if ((fd = open(path, O_RDONLY, 0644)) < 0) 
	    return -3;
	
	read(fd, config, sizeof(struct board_member_config));
    close(fd);
	
	return 1;
}

int save_board_member_config(const char *name, struct board_member_config *config) {
    const struct boardheader *board;
	char path[PATHLEN];
	int fd;
	
	board=getbcache(name);
    if (0==board)
        return -1;
    if (board->flag&BOARD_GROUP)
        return -2;
	if (!HAS_PERM(getSession()->currentuser,PERM_SYSOP)&&!chk_currBM(board->BM,getSession()->currentuser))	
        return -3;
		
	setbfile(path, board->filename, BOARD_MEMBER_CONFIG);
	if ((fd = open(path, O_WRONLY | O_CREAT, 0644)) < 0)
        return -4;
    write(fd, config, sizeof(struct board_member_config));
    close(fd);
    return 0;
}
/**
  * 用户申请成为某版的驻版用户
  * -1: guest不允许驻板
  * -2,-3: 版面错误
  * -4: 无版面权限
  * -6: 已经是该板的驻板用户
  * -7: 驻板申请已提交、等待审批中
  * -10: 登录数未达到要求
  * -11: 发文数未达到要求
  * -12: 积分未达到要求
  * -13: 用户等级未达到要求
  * -14: 在本版的发文数未达到要求
  * -15: 在本版的原创文章数未达到要求
  * -16: 在本版的M文章未达到要求
  * -17: 在本版的G文章未达到要求
  * -19: 本版驻版用户超出限制
  * -21: 所驻版面超过限制
  * 其他负数: 系统错误
  * 1: 提交审批，等待版主通过
  * 2: 成为驻版用户
  *
  * windinsn, 2012.8.12
  */
int join_board_member(const char *name) {
    struct boardheader *board;
	struct board_member member;
	struct board_member_config config;
	int status, user_max, level, count;
	char buf[8];
	MYSQL s;
    char sql[300];
	
	if (0==strcmp(getSession()->currentuser->userid, "guest"))
        return -1;
    	
	board=getbcache(name);
	if (0==board)
        return -2;
    if (board->flag&BOARD_GROUP)
        return -3;
	if (!haspostperm(getSession()->currentuser, board->filename))
	    return -4;
		
	status=get_board_member(board->filename, getSession()->currentuser->userid, &member);
	if (status < 0)
	    return -5;
	if (status == BOARD_MEMBER_STATUS_NORMAL)
	    return -6;
	if (status == BOARD_MEMBER_STATUS_CANDIDATE)
        return -7;
    if (status != BOARD_MEMBER_STATUS_NONE)
	    return -8;
    
	if (load_board_member_config(board->filename, &config)<0)
	    return -9;
    
	if (config.logins>0 && getSession()->currentuser->numlogins<config.logins)
	    return -10;
	if (config.posts>0 && getSession()->currentuser->numposts<config.posts)
	    return -11;
#if defined(NEWSMTH) && !defined(SECONDSITE)
    level=uvaluetochar(buf, getSession()->currentuser);		
	if (config.score>0 && getSession()->currentuser->score_user<config.score)
	    return -12;
	if (config.level>0 && level<config.level) 
        return -13;   
	user_max=(level>MEMBER_USER_MAX_DEFAULT)?level:MEMBER_USER_MAX_DEFAULT;
#else
    user_max=MEMBER_USER_MAX_DEFAULT;		
#endif	
    if (config.max_members>0) {
	    count=get_board_members(board->filename);
	    if (count<0)
	        return -18;
	    if (count>=config.max_members)
	        return -19;
    }
	count=get_member_boards(getSession()->currentuser->userid);
	if (count<0)
	    return -20;
	if (count>=user_max)
	    return -21;
	
	if (config.board_posts>0 && board_regenspecial(board->filename, DIR_MODE_AUTHOR, getSession()->currentuser->userid)<config.board_posts) 
        return -14;    
    if (config.board_origins>0 && board_regenspecial(board->filename, DIR_MODE_ORIGIN_AUTHOR, getSession()->currentuser->userid)<config.board_origins) 
	    return -15;
    if (config.board_marks>0 && board_regenspecial(board->filename, DIR_MODE_MARK_AUTHOR, getSession()->currentuser->userid)<config.board_marks) 
	    return -16;
    if (config.board_digests>0 && board_regenspecial(board->filename, DIR_MODE_DIGEST_AUTHOR, getSession()->currentuser->userid)<config.board_digests) 
	    return -17;
		
	status=(config.approve>0)?BOARD_MEMBER_STATUS_CANDIDATE:BOARD_MEMBER_STATUS_NORMAL;
	
	mysql_init(&s);
	if (!my_connect_mysql(&s)) {
        bbslog("3system", "mysql error: %s", mysql_error(&s));
        return -22;
    }
	
	member.board[0]=0;
	member.user[0]=0;
	
	mysql_escape_string(member.board, board->filename, strlen(board->filename));
	mysql_escape_string(member.user, getSession()->currentuser->userid, strlen(getSession()->currentuser->userid));
	member.time=time(0);
	member.status=status;
	member.manager[0]=0;
	member.score=0;
	member.flag=0;
	
	sprintf(sql,"INSERT INTO `board_user` VALUES (\"%s\", \"%s\", FROM_UNIXTIME(%u), %d, \"\", %u, %u);", member.board, member.user, member.time, member.status, member.score, member.flag);
	if (mysql_real_query(&s, sql, strlen(sql))) {
        bbslog("3system", "mysql error: %s", mysql_error(&s));
        mysql_close(&s);
        return -23;
    }
	
	mysql_close(&s);
	if (BOARD_MEMBER_STATUS_CANDIDATE==status) {
	    // 向版主发信
		// TODO
	}
	return status;
}

int leave_board_member(const char *name) {
    return delete_board_member_record(name, getSession()->currentuser->userid);
}

int approve_board_member(const char *name, const char *user_id) {
    struct boardheader *board;
	struct board_member member;
	int status;
    MYSQL s;
    char sql[200];
	char my_name[STRLEN];
	char my_user_id[STRLEN];
	char my_manager_id[STRLEN];
	
	board=getbcache(name);
	if (0==board)
        return -1;
	if (board->flag&BOARD_GROUP)
        return -2;	
	if (!HAS_PERM(getSession()->currentuser,PERM_SYSOP)&&!chk_currBM(board->BM,getSession()->currentuser))	
        return -3;
		
	status=get_board_member(board->filename, user_id, &member);	
	if (status!=BOARD_MEMBER_STATUS_CANDIDATE)
	    return -4;
	
	my_name[0]=0;
	my_user_id[0]=0;
	my_manager_id[0]=0;
	mysql_escape_string(my_name, board->filename, strlen(board->filename));
	mysql_escape_string(my_user_id, member.user, strlen(member.user));
	mysql_escape_string(my_manager_id, getSession()->currentuser->userid, strlen(getSession()->currentuser->userid));
	
	sprintf(sql,"UPDATE `board_user` SET `time`=FROM_UNIXTIME(%u), `status`=%d, `manager`=\"%s\" WHERE `board`=\"%s\" AND `user`=\"%s\" LIMIT 1;", time(0), BOARD_MEMBER_STATUS_NORMAL, my_manager_id, my_name, my_user_id);

    if (mysql_real_query(&s, sql, strlen(sql))) {
        bbslog("3system", "mysql error: %s", mysql_error(&s));
        mysql_close(&s);
        return -5;
    }

    mysql_close(&s);

    return 0;
}

int remove_board_member(const char *name, const char *user_id) {
    struct boardheader *board;
	int ret;
	
    board=getbcache(name);
	if (0==board)
        return -1;
	if (!HAS_PERM(getSession()->currentuser,PERM_SYSOP)&&!chk_currBM(board->BM,getSession()->currentuser))	
        return -2;
	
	ret=delete_board_member_record(board->filename, user_id);
	if (ret<0)
	    return ret-2;
	
	return 0;
}

int delete_board_member_record(const char *name, const char *user_id) {
    MYSQL s;
    char sql[200];
	char my_name[STRLEN];
	char my_user_id[STRLEN];
	
	if (!name[0])
	    return -1;
	if (!user_id[0])
	    return -2;
	if (0==strcmp(user_id, "guest"))
        return -3;	
	
	my_name[0]=0;
	my_user_id[0]=0;
	mysql_escape_string(my_name, name, strlen(name));
	mysql_escape_string(my_user_id, user_id, strlen(user_id));
	
	sprintf(sql,"DELETE FROM `board_user` WHERE `board`=\"%s\" AND `user`=\"%s\" LIMIT 1;", my_name, my_user_id);

    if (mysql_real_query(&s, sql, strlen(sql))) {
        bbslog("3system", "mysql error: %s", mysql_error(&s));
        mysql_close(&s);
        return -4;
    }

    mysql_close(&s);

    return 0;
}

int get_board_member(const char *name, const char *user_id, struct board_member *member) {
    MYSQL s;
    MYSQL_RES *res;
    MYSQL_ROW row;
    char sql[300];
	char my_name[STRLEN];
	char my_user_id[STRLEN];
	
	if (!user_id[0])
	    return -1;
	if (!name[0])
        return -2;	
	
	mysql_init(&s);
	if (!my_connect_mysql(&s)) {
        bbslog("3system", "mysql error: %s", mysql_error(&s));
        return -3;
    }
	
	my_name[0]=0;
	my_user_id[0]=0;
	mysql_escape_string(my_name, name, strlen(name));
	mysql_escape_string(my_user_id, user_id, strlen(user_id));
	
	sprintf(sql,"SELECT `board`, `user`, UNIX_TIMESTAMP(`time`), `status`, `manager`, `score`, `flag` FROM `board_user` WHERE `board`=\"%s\" AND `user`=\"%s\" LIMIT 1;", my_name, my_user_id);
    
	if (mysql_real_query(&s, sql, strlen(sql))) {
        bbslog("3system", "mysql error: %s", mysql_error(&s));
        mysql_close(&s);
        return -4;
    }
    res = mysql_store_result(&s);
    row = mysql_fetch_row(res);
    member->status=BOARD_MEMBER_STATUS_NONE;
	
	if (row != NULL) {
        strncpy(member->board, row[0], 32);
		strncpy(member->user, row[1], IDLEN+1);
		member->time=atol(row[2]);
		member->status=atol(row[3]);
		strncpy(member->manager, row[4], IDLEN+1);
		member->score=atol(row[5]);
		member->flag=atol(row[6]);
	}
    mysql_free_result(res);

    mysql_close(&s);
    return member->status;
}

int load_board_members(const char *board, struct board_member *member, int start, int num) {
    MYSQL s;
    MYSQL_RES *res;
    MYSQL_ROW row;
    char sql[300];
	char qtmp[100];
	char my_board[STRLEN];
	int i;
	
	if (!board[0])
	    return -1;
	
	mysql_init(&s);
	if (!my_connect_mysql(&s)) {
        bbslog("3system", "mysql error: %s", mysql_error(&s));
        return -2;
    }
	
	my_board[0]=0;
	mysql_escape_string(my_board, board, strlen(board));
	
	sprintf(sql,"SELECT `board`, `user`, UNIX_TIMESTAMP(`time`), `status`, `manager`, `score`, `flag` FROM `board_user` WHERE `board`=\"%s\" ", my_board);
	snprintf(qtmp, 99, " ORDER BY `time` LIMIT %d,%d", start, num);
    strcat(sql, qtmp);
    
	if (mysql_real_query(&s, sql, strlen(sql))) {
        bbslog("3system", "mysql error: %s", mysql_error(&s));
        mysql_close(&s);
        return -3;
    }
    res = mysql_store_result(&s);
    row = mysql_fetch_row(res);

    i=0;
    while (row != NULL) {
        i++;
        if (i>num)
		    break;
			
		strncpy(member[i-1].board, row[0], 32);
		strncpy(member[i-1].user, row[1], IDLEN+1);
		member[i-1].time=atol(row[2]);
		member[i-1].status=atol(row[3]);
		strncpy(member[i-1].manager, row[4], IDLEN+1);
		member[i-1].score=atol(row[5]);
		member[i-1].flag=atol(row[6]);
		
		row = mysql_fetch_row(res);
	}
    mysql_free_result(res);

    mysql_close(&s);
    return i;
}

int load_member_boards(const char *user_id, struct board_member *member, int start, int num) {
    MYSQL s;
    MYSQL_RES *res;
    MYSQL_ROW row;
    char sql[300];
	char qtmp[100];
	char my_user_id[STRLEN];
	int i;
	
	if (!user_id[0])
	    return -1;
	
	mysql_init(&s);
	if (!my_connect_mysql(&s)) {
        bbslog("3system", "mysql error: %s", mysql_error(&s));
        return -2;
    }
	
	my_user_id[0]=0;
	mysql_escape_string(my_user_id, user_id, strlen(user_id));
	
	sprintf(sql,"SELECT `board`, `user`, UNIX_TIMESTAMP(`time`), `status`, `manager`, `score`, `flag` FROM `board_user` WHERE `user`=\"%s\" ", my_user_id);
	snprintf(qtmp, 99, " ORDER BY `time` LIMIT %d,%d", start, num);
    strcat(sql, qtmp);
    
	if (mysql_real_query(&s, sql, strlen(sql))) {
        bbslog("3system", "mysql error: %s", mysql_error(&s));
        mysql_close(&s);
        return -3;
    }
    res = mysql_store_result(&s);
    row = mysql_fetch_row(res);

    i=0;
    while (row != NULL) {
        i++;
        if (i>num)
		    break;
			
		strncpy(member[i-1].board, row[0], 32);
		strncpy(member[i-1].user, row[1], IDLEN+1);
		member[i-1].time=atol(row[2]);
		member[i-1].status=atol(row[3]);
		strncpy(member[i-1].manager, row[4], IDLEN+1);
		member[i-1].score=atol(row[5]);
		member[i-1].flag=atol(row[6]);
		
		row = mysql_fetch_row(res);
	}
    mysql_free_result(res);

    mysql_close(&s);
    return i;
}
int get_board_members(const char *board) {
    MYSQL s;
    MYSQL_RES *res;
    MYSQL_ROW row;
    char sql[300];
    char my_board[STRLEN];
	int i;

	if (!board[0])
	    return -1;
	
	my_board[0]=0;
	mysql_escape_string(my_board, board, strlen(board));
    mysql_init(&s);

    if (! my_connect_mysql(&s)) {
        bbslog("3system", "mysql error: %s", mysql_error(&s));
        return -2;
    }

    sprintf(sql,"SELECT COUNT(*) FROM `board_user` WHERE `board`=\"%s\"", my_board);

    if (mysql_real_query(&s, sql, strlen(sql))) {
        bbslog("3system", "mysql error: %s", mysql_error(&s));
        mysql_close(&s);
        return -3;
    }
    res = mysql_store_result(&s);
    row = mysql_fetch_row(res);

    i=0;
    if (row != NULL) {
        i=atoi(row[0]);
    }
    mysql_free_result(res);

    mysql_close(&s);
    return i;
}
int get_member_boards(const char *user_id) {
    MYSQL s;
    MYSQL_RES *res;
    MYSQL_ROW row;
    char sql[300];
    char my_user_id[STRLEN];
	int i;

	if (!user_id[0])
	    return -1;
		
	my_user_id[0]=0;
	mysql_escape_string(my_user_id, user_id, strlen(user_id));
    mysql_init(&s);

    if (! my_connect_mysql(&s)) {
        bbslog("3system", "mysql error: %s", mysql_error(&s));
        return -2;
    }

    sprintf(sql,"SELECT COUNT(*) FROM `board_user` WHERE `user`=\"%s\"", my_user_id);

    if (mysql_real_query(&s, sql, strlen(sql))) {
        bbslog("3system", "mysql error: %s", mysql_error(&s));
        mysql_close(&s);
        return -3;
    }
    res = mysql_store_result(&s);
    row = mysql_fetch_row(res);

    i=0;
    if (row != NULL) {
        i=atoi(row[0]);
    }
    mysql_free_result(res);

    mysql_close(&s);
    return i;
}
	
#endif
#endif 
