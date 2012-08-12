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
void bbs_make_board_member_config_array(zval *array, struct board_member *member) {
    add_assoc_string(array, "board", member->board, 1);
    add_assoc_string(array, "user", member->user, 1);
    add_assoc_long(array, "time", meber->time);
	add_assoc_long(array, "status", meber->status);
	add_assoc_string(array, "manager", member->manager, 1);
    add_assoc_long(array, "flag", meber->flag);
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
		
	bbs_make_board_member_config_array(array, config);
	
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
	config.board_digests=(board_degists>0)?board_degists:0;
	
	if (save_board_member_config(name, config)<0)
	    RETURN_FALSE;
	RETURN_TRUE;
}

PHP_FUNCTION(bbs_join_board_member)
{
    RETURN_FALSE;
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
    RETURN_FALSE;
}

PHP_FUNCTION(bbs_load_member_boards)
{
    RETURN_FALSE;
}

PHP_FUNCTION(bbs_get_board_members)
{
    RETURN_FALSE;
}

PHP_FUNCTION(bbs_get_member_boards)
{
    RETURN_FALSE;
}

#endif // ENABLE_BOARD_MEMBER
