#ifndef PHP_BBS_MEMBER_H
#define PHP_BBS_MEMBER_H

#ifdef ENABLE_BOARD_MEMBER
/* פ���û����, windinsn, 2012.8.12 */
	
PHP_FUNCTION(bbs_load_board_member_config);
PHP_FUNCTION(bbs_save_board_member_config);
PHP_FUNCTION(bbs_join_board_member);
PHP_FUNCTION(bbs_leave_board_member);
PHP_FUNCTION(bbs_approve_board_member);
PHP_FUNCTION(bbs_remove_board_member);
PHP_FUNCTION(bbs_get_board_member);
PHP_FUNCTION(bbs_load_board_members);
PHP_FUNCTION(bbs_load_member_boards);
PHP_FUNCTION(bbs_get_board_members);
PHP_FUNCTION(bbs_get_member_boards);


#define PHP_BBS_MEMBER_EXPORT_FUNCTIONS \
    PHP_FE(bbs_load_board_member_config, NULL) \
    PHP_FE(bbs_save_board_member_config, NULL) \
    PHP_FE(bbs_join_board_member, NULL) \
    PHP_FE(bbs_leave_board_member, NULL) \
    PHP_FE(bbs_approve_board_member, NULL) \
    PHP_FE(bbs_remove_board_member, NULL) \
    PHP_FE(bbs_get_board_member, NULL) \
    PHP_FE(bbs_load_board_members, NULL) \
    PHP_FE(bbs_load_member_boards, NULL) \
    PHP_FE(bbs_get_board_members, NULL) \
    PHP_FE(bbs_get_member_boards, NULL) 

#endif // ENABLE_BOARD_MEMBER	
#endif //PHP_BBS_MEMBER_H
