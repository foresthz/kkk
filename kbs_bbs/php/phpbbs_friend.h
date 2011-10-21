#ifndef PHP_BBS_FRIEND_H
#define PHP_BBS_FRIEND_H

/* ������� */

PHP_FUNCTION(bbs_getfriends);
PHP_FUNCTION(bbs_countfriends);
PHP_FUNCTION(bbs_delete_friend);
PHP_FUNCTION(bbs_add_friend);
PHP_FUNCTION(bbs_getonlinefriends);
#ifdef NEWSMTH
PHP_FUNCTION(bbs_getfans);
PHP_FUNCTION(bbs_countfans);
#endif

#define PHP_BBS_FRIEND_EXPORT_FUNCTIONS_STD \
    PHP_FE(bbs_getfriends, NULL) \
    PHP_FE(bbs_countfriends, NULL) \
    PHP_FE(bbs_delete_friend, NULL) \
    PHP_FE(bbs_add_friend, NULL) \
    PHP_FE(bbs_getonlinefriends,NULL)

#ifdef NEWSMTH
#define PHP_BBS_FRIEND_EXPORT_FUNCTIONS \
    PHP_BBS_FRIEND_EXPORT_FUNCTIONS_STD \
    PHP_FE(bbs_getfans, NULL) \
    PHP_FE(bbs_countfans, NULL)
#else
#define PHP_BBS_FRIEND_EXPORT_FUNCTIONS PHP_BBS_FRIEND_EXPORT_FUNCTIONS_STD
#endif


#endif //PHP_BBS_FRIEND_H

