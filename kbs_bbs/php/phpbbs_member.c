#include "php_kbs_bbs.h"

#ifdef ENABLE_BOARD_MEMBER
void bbs_make_board_member_config_array(zval *array, struct board_member_config *config) {
    add_assoc_long(array, "approve", config->approve);
    add_assoc_long(array, "max_members", config->max_members);
    add_assoc_long(array, "logins", config->logins);
    add_assoc_long(array, "posts", config->posts);
    add_assoc_long(array, "score", config->score);
    add_assoc_long(array, "level", config->level);
    add_assoc_long(array, "board_posts", config->board_posts);
    add_assoc_long(array, "board_origins", config->board_origins);
    add_assoc_long(array, "board_marks", config->board_marks);
    add_assoc_long(array, "board_digests", config->board_digests);
}
void bbs_make_board_member_array(zval *array, struct board_member *member) {
    add_assoc_string(array, "board", member->board, 1);
    add_assoc_string(array, "user", member->user, 1);
    add_assoc_long(array, "time", member->time);
	add_assoc_long(array, "status", member->status);
	add_assoc_string(array, "manager", member->manager, 1);
	add_assoc_long(array, "score", member->score);
    add_assoc_long(array, "flag", member->flag);
}

/**
  * bbs_load_board_member_config(string board_name, array &config);
  */
PHP_FUNCTION(bbs_load_board_member_config)
{
    char *name;
	int name_len;
	zval *array;
	struct board_member_config config;

	if (ZEND_NUM_ARGS()!=2 || zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sz", &name, &name_len, &array)==FAILURE)
        WRONG_PARAM_COUNT;

    if (!PZVAL_IS_REF(array)) {
        zend_error(E_WARNING, "Parameter wasn't passed by reference");
        RETURN_LONG(-1);
    }
	
	if (load_board_member_config(name, &config)<0)
	    RETURN_FALSE;
	
	if (array_init(array) != SUCCESS)
	    RETURN_FALSE;
		
	bbs_make_board_member_config_array(array, &config);
	
	RETURN_TRUE;
}
/**
  * bbs_save_board_member_config(
        string board_name,
		long approve,
		long max_members,
		long logins,
		long posts,
		long score,
		long level,
		long board_posts,
		long board_origins,
		long board_marks,
		long board_digests
    )
  *    int approve;
	int max_members;
	
	int logins;
	int posts;
	int score;
	int level;
	
	int board_posts;
	int board_origins;
	int board_marks;
	int board_digests;
  */
PHP_FUNCTION(bbs_save_board_member_config)
{
    char *name;
	int name_len;
	long approve, max_members, logins, posts, score, level, board_posts, board_origins, board_marks, board_digests;
	struct board_member_config config;
	
	if (ZEND_NUM_ARGS()!=11 || zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sllllllllll", &name, &name_len, &approve, &max_members, &logins, &posts, &score, &level, &board_posts, &board_origins, &board_marks, &board_digests)==FAILURE)
        WRONG_PARAM_COUNT;
		
	if (load_board_member_config(name, &config)<0)
	    RETURN_FALSE;
		
	config.approve=(approve>0)?1:0;
	if (HAS_PERM(getSession()->currentuser,PERM_SYSOP)) 
	    config.max_members=(max_members>0)?max_members:0;
	config.logins=(logins>0)?logins:0;
	config.posts=(posts>0)?posts:0;
	config.score=(score>0)?score:0;
	config.level=(level>0)?level:0;
	config.board_posts=(board_posts>0)?board_posts:0;
	config.board_origins=(board_origins>0)?board_origins:0;
	config.board_marks=(board_marks>0)?board_marks:0;
	config.board_digests=(board_digests>0)?board_digests:0;
	
	if (save_board_member_config(name, &config)<0)
	    RETURN_FALSE;
	RETURN_TRUE;
}

/**
  * bbs_join_board_member(string board_name);
  */
PHP_FUNCTION(bbs_join_board_member)
{
    char *name;
	int name_len;
	
	if (ZEND_NUM_ARGS()!=1 || zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &name, &name_len)==FAILURE)
        WRONG_PARAM_COUNT;
		
	RETURN_LONG(join_board_member(name));	
}

PHP_FUNCTION(bbs_leave_board_member)
{
    RETURN_FALSE;
}

PHP_FUNCTION(bbs_approve_board_member)
{
    RETURN_FALSE;
}

PHP_FUNCTION(bbs_remove_board_member)
{
    RETURN_FALSE;
}

PHP_FUNCTION(bbs_get_board_member)
{
    RETURN_FALSE;
}

PHP_FUNCTION(bbs_load_board_members)
{
	char *name;
	int name_len, sort, start, count, ret, i;
	zval *list, *element;
	const struct boardheader *board;
	struct board_member *members = NULL;
	
	if (ZEND_NUM_ARGS()!=5 || zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "slllz", &name, &name_len, &sort, &start, &count, &list)==FAILURE)
        WRONG_PARAM_COUNT;
	
	if (!PZVAL_IS_REF(list)) {
        zend_error(E_WARNING, "Parameter wasn't passed by reference");
        RETURN_LONG(-1);
    }
	
	if (!getbid(name, &board)||board->flag&BOARD_GROUP)
		RETURN_LONG(-1);
		
	if (!check_read_perm(getCurrentUser(), board))
		RETURN_LONG(-2);

	if (start <= 0)
		start=0;
	if (count<=0)
		RETURN_LONG(-3);
	if (count>20)
		count=20;
		
	members=emalloc(sizeof(struct board_member)*count);
	if (NULL==members)
		RETURN_LONG(-4);
		
	bzero(members, sizeof(struct board_member)*count);
	ret=load_board_members(board->filename, members, sort, start, count);
	
	for (i=0;i<ret;i++) {
		MAKE_STD_ZVAL(element);
		array_init(element);
		bbs_make_board_member_array(element, members+i)
		zend_hash_index_update(Z_ARRVAL_P(list), i, (void *) &element, sizeof(zval*), NULL);
	}
	
	efree(members);
	
	if (ret<0)
		RETURN_LONG(-4);
		
	RETURN_LONG(ret);
}

PHP_FUNCTION(bbs_load_member_boards)
{
	
    RETURN_FALSE;
}

PHP_FUNCTION(bbs_get_board_members)
{
	char *name;
	int name_len, count;
	const struct boardheader *board;
	
	if (ZEND_NUM_ARGS()!=1 || zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &name, &name_len)==FAILURE)
        WRONG_PARAM_COUNT;
		
	if (!getbid(name, &board)||board->flag&BOARD_GROUP)
		RETURN_LONG(-1);
		
	if (!check_read_perm(getCurrentUser(), board))
		RETURN_LONG(-2);
		
	count=get_board_members(board->filename);
	if (count<0)
		RETURN_LONG(-3);
		
    RETURN_LONG(count);
}

PHP_FUNCTION(bbs_get_member_boards)
{
    RETURN_FALSE;
}

#endif // ENABLE_BOARD_MEMBER
