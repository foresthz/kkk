#ifndef _KBSBBS_MEMBER_CACHE_H_
#define _KBSBBS_MEMBER_CACHE_H_

#ifdef ENABLE_MEMBER_CACHE

#ifndef MAX_MEMBERS
#define MAX_MEMBERS 400000
#endif

#ifndef MAX_MEMBER_TITLES
#define MAX_MEMBER_TITLES 10000
#endif

#ifndef MEBERS_TMP_FILE
#define MEBERS_TMP_FILE ".MEMBERS.TMP"
#endif

#ifndef MAX_BOARD_MEMBERS
#define MAX_BOARD_MEMBERS 10000
#endif

#ifndef MAX_MEMBER_BOARDS
#define MAX_MEMBER_BOARDS 100
#endif

/*
 * struct.h 中 board_member 的瘦身版，便于存储和比较
 * 40byte
 */
struct MEMBER_CACHE_NODE {
	int bid;
	int uid;
	time_t time;
	int status;
	unsigned int score;
	unsigned int flag;
	int title;
	
	int user_next;
	int board_next;
};
/*
 * strcut.h 中 board_member_title 的瘦身版，便于存储和比较
 */
struct MEMBER_CACHE_TITLE {
	int id;
	int bid;
	char name[STRLEN];
	int serial;
	unsigned int flag;
};
struct MEMBER_CACHE {
	int users[MAXUSERS];
	int boards[MAXBOARD];
	struct MEMBER_CACHE_NODE nodes[MAX_MEMBERS];
	struct MEMBER_CACHE_TITLE titles[MAX_MEMBER_TITLES];
	
	int member_count;
	int title_count;
	int next_member_id;
	int next_title_id;
};


#endif
