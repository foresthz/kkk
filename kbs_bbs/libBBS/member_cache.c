/** 
  * 默认的驻版信息通过MySQL数据库存储，随着站点对驻版信息使用的增多，
  * 为了减少对数据库的依赖，并提高运行效率（数据库MyISAM表的效率你懂的...）
  * 改用共享内存存储驻版信息。
  * 驻版信息存储于 bbshome/.MEMBERS 文件
  * PS: 此部分代码请勿轻易改动
  *
  * windinsn, 2013-02-03
  */
#include "bbs.h"
#include "sys/socket.h"
#include "netinet/in.h"
#include <signal.h>
#include "member_cache.h"

#ifdef ENABLE_MEMBER_CACHE
#ifndef USE_SEM_LOCK
static int member_cache_lock()
{
	int lockfd;
	char errbuf[STRLEN];
	lockfd = open(MEBERS_TMP_FILE, O_RDWR | O_CREAT, 0600);
	if (lockfd < 0) {
		bbslog("3system", "CACHE:lock member cache:%s", strerror_r(errno, errbuf, STRLEN));
		return -1;
	}
	writew_lock(lockfd, 0, SEEK_SET, 0);
	return lockfd;
}

static void member_cache_unlock(int fd)
{
	un_lock(fd, 0, SEEK_SET, 0);
	close(fd);
}
#else /* USE_SEM_LOCK */
static int member_cache_lock()
{
	lock_sem(MEMBER_CACHE_SEMLOCK);
	return 0;
}

static void member_cache_unlock(int fd)
{
	unlock_sem_check(MEMBER_CACHE_SEMLOCK);
}
#endif /* USE_SEM_LOCK */

void resolve_members () 
{
	int iscreate;

	iscreate = 0;
	if (membershm == NULL) {
		membershm = (struct MEMBER_CACHE *) attach_shm("MEMBER_CACHE_SHMKEY", 3702, sizeof(*membershm), &iscreate);
		if (iscreate) {		 /* shouldn't load .MEMBERS file in this place */
			bbslog("4system", "member cache daemon havn't startup");
			remove_shm("MEMBER_CACHE_SHMKEY",3702,sizeof(*membershm));
			return -1;
		}

	}
	return 0;
}

void detach_members ()
{
	shmdt((void *)membershm);
	membershm=NULL;
}
int update_member_manager_flag(int uid) {
	return 0;
}
int is_null_member(struct MEMBER_CACHE_NODE *node) {
	if (node->bid==0 || node->uid==0)
		return 1;
	return 0;
}
int fill_member(struct board_member *member, struct MEMBER_CACHE_NODE *node) {
	struct userec *user;
	const struct boardheader *board;
	
	if (is_null_member(node))
		return -1;
		
	board=getboard(node->bid);
	if (NULL==board)
		return -2;
		
	user=getuserbynum(node->uid);	
	if (NULL==user)
		return -3;
	
	if (HAS_PERM(user, PERM_NOZAP) && BOARD_MEMBER_STATUS_MANAGER==node->status) {
		node->status=BOARD_MEMBER_STATUS_NORMAL;
		node->flag=0;
	}
	
	if (NULL==member)
		return 0;
	
	strncpy(member->board, board->filename, STRLEN);
	strncpy(member->user, user_id, IDLEN+1);
	member->time=node->time;
	member->score=node->score;
	member->flag=node->flag;
	member->title=node->title;
	member->manager[0]=0;
	member->status=node->status;
	
	return 0;
}
int is_null_member_title(struct MEMBER_CACHE_TITLE *title) {
	if (title->bid==0)
		return 1;
	if (!title->name[0])
		return 1;
	return 0;
}
int fill_member_title(struct board_member_title *title, struct MEMBER_CACHE_TITLE *t) {
	const struct boardheader *board;
	
	if (is_null_member_title(t))
		return -1;
	board=getboard(t->bid);
	if (NULL==board)
		return -2;
		
	if (NULL==title)
		return 0;
		
	title->id=t->id;
	strncpy(title->board, board->filename, STRLEN);
	strncpy(title->name, t->name, STRLEN);
	title->serial=t->serial;
	title->flag=t->flag;
	
	return 0;
}
int find_null_member() {
	int i;
	
	for (i=0; i<MAX_MEMBERS; i++) {
		if (is_null_member(&(membershm->nodes[i])))
			return i+1;
	}
	return 0;
}
int add_member(struct board_member *member) {
	struct MEMBER_CACHE_NODE *node, *p;
	int uid, bid, i, index;
	
	if (membershm->member_count >= MAX_MEMBERS) {
		bbslog("3system", "BBS board members records full.");
		return -1;
	}
	
	if (!members->user[0] || !member->board[0])
		return -2;
	
	uid=getuser(member->user, NULL);
	bid=getbid(member->board, NULL);
	
	if (uid==0 || bid==0)
		return -3;
		
	if (get_member_index(member->board, member->user)>0)
		return -4;
	
	index=find_null_member();
	node=&(membershm->nodes[index-1]);
	node->bid=bid;
	node->uid=uid;
	node->time=member->time;
	node->status=member->status;
	node->score=member->score;
	node->flag=member->flag;
	node->title=member->title;
	node->user_next=0;
	node->board_next=0;
	
	i=membershm->users[uid-1];
	if (i==0) {
		membershm->users[uid-1]=index;
	} else {
		while (i!=0) {
			p=&(membershm->nodes[i-1]);
			i=p->user_next;
			if (i==0) {
				p->user_next=index;
			}
		}
	}
	
	i=membershm->boards[bid-1];
	if (i==0) {
		membershm->boards[bid-1]=index;
	} else {
		while (i!=0) {
			p=&(membershm->nodes[i-1]);
			i=p->board_next;
			if (i==0) {
				p->board_next=index;
			}
		}
	}
	
	membershm->member_count ++;
	return 0;	
}
int remove_member(int index) {
	struct MEMBER_CACHE_NODE *node, *p;
	int bid, uid, i, son;

	node=get_member_by_index(index);
	if (NULL==node)
		return -1;
	
	bid=node->bid;
	uid=node->uid;
	
	i=membershm->users[uid-1];
	son=node->user_next;
	if (i==index || i==0)
		membershm->users[uid-1]=son;
	else {
		while(i!=index && i!=0) {
			p=&(membershm->nodes[i-1]);
			i=p->user_next;
			if (i==index || i==0) {
				p->user_next=son;
			}
		}
	}
	
	i=membershm->boards[bid-1];
	son=node->board_next;
	if (i==index || i==0)
		membershm->boards[bid-1]=son;
	else {
		while (i!=index && i!=0) {
			p=&(membershm->nodes[i-1]);
			i=p->board_next;
			if (i==index || i==0) {
				p->board_next=son;
			}
		}
	}
	
	if (node->status==BOARD_MEMBER_STATUS_MANAGER)
		update_member_manager_flag(node->uid);
		
	bzero(node, sizeof(struct MEMBER_CACHE_NODE));
	membershm->member_count --;
	return 0;
}
int load_members ()
{
	int iscreate;
	int fd;
	int member_file_fd;
	char errbuf[STRLEN];
	struct board_member member;
	int n, size;
	
	fd=member_cache_lock();
	membershm = (struct MEMBER_CACHE *) attach_shm("MEMBER_CACHE_SHMKEY", 3702, sizeof(*membershm), &iscreate);   /*attach to member cache shm */

	if (!iscreate) {
		bbslog("4system", "load a exitist member cache shm!");
	} else {
		load_member_titles();
		
		if ((member_file_fd = open(MEMBERS_FILE, O_RDWR | O_CREAT, 0644)) == -1) {
			bbslog("3system", "Can't open " MEMBERS_FILE " file %s", strerror_r(errno, errbuf, STRLEN));
			member_cache_unlock(fd);
			exit(-1);
		}
		ftruncate(member_file_fd, MAX_MEMBERS * sizeof(struct board_member));
		if (lseek(member_file_fd, 0, SEEK_SET) == -1) {
			member_cache_unlock(fd);
			close(member_file_fd);
			exit(-1);
		}
		bzero(membershm->users,sizeof(membershm->users));
		bzero(membershm->boards,sizeof(membershm->boards));
		bzero(membershm->nodes,sizeof(membershm->nodes));
		membershm->member_count=0;
		membershm->next_member_id=1;
		
		size=sizeof(struct board_member);
		bzero(&member, size);
		while ((n = read(fd, &member, size)) != -1) {
			add_member(&member);
		}
		close(member_file_fd);
		newbbslog(BBSLOG_USIES, "CACHE:reload member cache for %d records", membercache->member_count);
	}
	member_cache_unlock(fd);
	return 0;
}

void load_member_titles() {
	int fd;
	char errbuf[STRLEN];
	struct board_member_title title;
	struct MEMBER_CACHE_TITLE *t;
	int size, n, i;
	
	bzero(membershm->titles,sizeof(membershm->titles));
	if ((fd = open(MEMBER_TITLES_FILE, O_RDONLY, 0)) == -1) {
		bbslog("3system", "Can't open " MEMBER_TITLES_FILE " file %s", strerror_r(errno, errbuf, STRLEN));
		exit(-1);
	}
	
	if (lseek(fd, 0, SEEK_SET) == -1) {
		close(fd);
		exit(-1);
	}
	
	size=sizeof(struct board_member_title);
	bzero(&title, size);
	membershm->next_title_id=1;
	i=0;
	while ((n = read(fd, &title, size)) != -1) {
		t=&(membershm->titles[i]);
		t->id=title.id;
		t->bid=getbid(title.board, NULL);
		strncpy(t->name, title.name, STRLEN);
		t->name[STRLEN-1]=0;
		t->serial=title.serial;
		t->flag=title.flag;
		
		if (t->id >= membershm->next_title_id)
			membershm->next_title_id=t->id+1;
		
		bzero(&title, size);
		i++;
		if (i>MAX_MEMBER_TITLES) {
			break;
		}
	}
	
	membershm->title_count=i;
	newbbslog(BBSLOG_USIES, "CACHE:reload member title cache for %d records", membercache->title_count);
	close(fd);
}
static int flush_member_cache_members() {
	struct board_member *members;
	int i, ret;
	
	members=(struct board_member *)malloc(MAX_MEMBERS * sizeof(struct board_member));
	if (NULL==members)
		return -1;
		
	bzero(members, 	MAX_MEMBERS * sizeof(struct board_member));
	for (i=0; i<MAX_MEMBERS;i++) {
		fill_member(members[i], &(membershm->nodes[i]));
	}
		
	ret=substitute_record(MEMBERS_FILE, members, MAX_MEMBERS * sizeof(struct board_member), 1, NULL, NULL);
	free(members);
	return ret;
}
static int flush_member_cache_titles() {
	struct board_member_title titles;
	int i, ret;
	
	titles=(struct board_member_title *)malloc(MAX_MEMBER_TITLES * sizeof(struct board_member_title));
	if (NULL==titles)
		return -1;
		
	bzero(titles, MAX_MEMBER_TITLES * sizeof(struct board_member_title));
	for (i=0;i<MAX_MEMBER_TITLES;i++) {
		fill_member_title(titles[i], &(membershm->titles[i]));
	}
		
	ret=substitute_record(MEMBER_TITLES_FILE, titles, MAX_MEMBER_TITLES * sizeof(struct board_member_title), 1, NULL, NULL);
	free(titles);
	return ret;
}
int flush_member_cache () {
	flush_member_cache_members();
	flush_member_cache_titles();
	return 0;
}
int get_member_title_index(int id) {
	if (id<=0 || id>MAX_MEMBER_TITLES || membershm->title_count <= 0)
		return 0;
	
	if (is_null_member_title(&(membershm->titles[id-1])))
		return 0;
	return id;
}
struct MEMBER_CACHE_TITLE *get_member_title(int id) {
	int index=get_member_title_index(id);
	if (index<=0)
		return NULL;
		
	return &(membershm->titles[index-1]);
}
int get_member_index(const char *name, const char *user_id) {
	int uid, bid, i;
	struct MEMBER_CACHE_NODE *node;
	
	if (strcasecmp("guest", user_id)==0)
		return 0;
	
	uid=getuser(user_id, NULL);
	if (uid<=0)
		return 0;
		
	bid=getbid(name);
	if (bid<=0)
		return 0;
		
	i=membershm->users[uid-1];
	if (i==0)
		return 0;
		
	while (i>0) {
		node=&(membershm->nodes[i-1]);
		if (is_null_member(node))
			return 0;
		if (node->uid!=uid)
			return 0;
		if (node->bid==bid) 
			return i;
		i=node->user_next;
	}
	
	return 0;
}
struct MEMBER_CACHE_NODE *get_member_by_index(int index) {
	if (index<=0 || index>MAX_MEMBERS)
		return NULL;
	if (is_null_member(&(membershm->nodes[index-1])))
		return NULL;
	return &(membershm->nodes[index-1]);
}
int get_member_cache(const char *name, const char *user_id, struct board_member *member) {
	int index;
	struct MEMBER_CACHE_NODE *node;

	index=get_member_index(name, user_id);
	if (index<=0)
		return -1;
	
	node=get_member_by_index(index);
	fill_member(member, node);
	
	return node->status;
}
int get_board_members_cache(char *name, struct board_member *members, int size)
{
	int bid, i, ret;
	struct MEMBER_CACHE_NODE *node;
	
	bid=getbid(name);
	if (bid<=0)
		return -1;
		
	i=membershm->boards[bid-1];
	if (i==0)
		return 0;
		
	ret=0;
	if (NULL!=members && size>0)
		bzero(members, sizeof(struct board_member)*size);
	
	while (i>0) {
		node=&(membershm->nodes[i-1]);
		if (is_null_member(node))
			break;
		if (node->bid != bid)
			break;
			
		if (NULL!=members && size>0)
			fill_member(members[ret], node);
		
		i=node->board_next;
		ret++;
	}
	return ret;
}
int get_member_boards_cache(char *user_id, struct board_member *members, int size)
{
	int uid, i, ret;
	struct MEMBER_CACHE_NODE *node;
	
	if (strcasecmp("guest", user_id)==0)
		return 0;
		
	uid=getuser(user_id, NULL);
	if (uid<=0)
		return -1;
		
	i=membershm->users[uid-1];
	if (i==0)
		return 0;
		
	ret=0;
	if (NULL!=members && size>0)
		bzero(members, sizeof(struct board_member)*size);
	
	while (i>0) {
		node=&(membershm->nodes[i-1]);
		if (is_null_member(node))
			break;
		if (node->uid != uid)
			break;
			
		if (NULL!=members && size>0)	
			fill_member(members[ret], node);
			
		i=node->user_next;
		ret ++;
	}
	return ret;
}
#endif