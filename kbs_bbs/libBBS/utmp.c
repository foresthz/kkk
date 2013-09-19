/* user cache 处理
   用hask table 保存所有ID的id名,加快查找速度
   2001.5.4 KCN
*/


#include "bbs.h"
#include <sys/ipc.h>
#include <sys/shm.h>
#include <netinet/in.h>
#ifdef FREEBSD
#include <sys/socket.h>
#endif

static int rebuild_list(struct user_info *up, char *arg, int p);
static void clear_utmp2(int uent);
struct UTMPFILE *get_utmpshm_addr() {
    return utmpshm;
}

/*static void longlock(int signo)
{
    bbslog("5system", "utmp lock for so long time!!!.");
    exit(-1);
}*/

#ifndef USE_SEM_LOCK
int utmp_lock()
{
    int utmpfd = 0;

    utmpfd = open(ULIST, O_RDWR | O_CREAT, 0600);
    if (utmpfd < 0) {
        exit(-1);
    }
    //signal(SIGALRM, longlock);
    //alarm(10);
    if (writew_lock(utmpfd, 0, SEEK_SET, 0) == -1) {
        exit(-1);
    }
    //signal(SIGALRM, SIG_IGN);
    return utmpfd;
}

void utmp_unlock(int fd)
{
    un_lock(fd, 0, SEEK_SET, 0);
    close(fd);
}
#else
static int utmp_lock()
{
    //signal(SIGALRM, longlock);
    //alarm(10);
    lock_sem(UTMP_SEMLOCK);
    //signal(SIGALRM, SIG_IGN);
    return 0;
}

static void utmp_unlock(int fd)
{
    unlock_sem_check(UTMP_SEMLOCK);
}

#endif
static void utmp_setreadonly(int readonly)
{
    /* ulock remove this protected
        int iscreate;

        shmdt(utmphead);
        utmphead = (struct UTMPHEAD *) attach_shm1(NULL, 3698, sizeof(struct UTMPHEAD), &iscreate, readonly, utmphead);
    */
}

void detach_utmp()
{
    shmdt((void *)utmphead);
    shmdt((void *)utmpshm);
    utmphead = NULL;
    utmpshm = NULL;
}

void resolve_utmp()
{
    int iscreate;

    if (utmpshm == NULL) {
        utmpshm = (struct UTMPFILE *) attach_shm("UTMP_SHMKEY", 3699, sizeof(*utmpshm), &iscreate);     /*attach user tmp cache */
        if (iscreate) {
            int i, utmpfd;
            utmphead = (struct UTMPHEAD *) attach_shm("UTMPHEAD_SHMKEY", 3698, sizeof(struct UTMPHEAD), &iscreate);     /*attach user tmp cache */
            utmpfd = utmp_lock();
            bzero(utmpshm, sizeof(struct UTMPFILE));
            bzero(utmphead, sizeof(struct UTMPHEAD));
            utmphead->number = 0;
            utmphead->hashhead[0] = 1;
            for (i = 0; i < USHM_SIZE - 1; i++)
                utmphead->next[i] = i + 2;
            utmphead->next[USHM_SIZE - 1] = 0;
            /*
                     utmphead->listhead=0;
            */
            utmp_unlock(utmpfd);
        } else
            utmphead = (struct UTMPHEAD *) attach_shm1("UTMPHEAD_SHMKEY", 3698, sizeof(struct UTMPHEAD), &iscreate, 0, NULL);   /*attach user tmp head */
    }
}

static int utmp_hash(const char *userid)
{
    int hash;

    hash = ucache_hash(userid);
    if (hash == 0)
        return 0;
    hash = (hash / 3) % UTMP_HASHSIZE;
    if (hash == 0)
        return 1;
    return hash;
}

/* disable KCN
struct requesthdr {
    int command;
    union {
        struct user_info utmp;
        int uent;
    } u_info;
} utmpreq;
*/
static struct utmpreqhdr utmpreq;
int sendutmpreq(struct utmpreqhdr *req)
{
    int m_socket;
    struct sockaddr_in sin;
    fd_set rfds;
    int result;
    struct  timeval tv;
    m_socket = socket(PF_INET,SOCK_STREAM,IPPROTO_TCP);
    if (m_socket<0) return -1;
    sin.sin_family=AF_INET;
    sin.sin_port=htons(60002);
    inet_aton("127.0.0.1",&sin.sin_addr);
    if (connect(m_socket,(struct sockaddr*)&sin,sizeof(sin))!=0) {
        close(m_socket);
        return -1;
    }
    write(m_socket,req,sizeof(*req));
    FD_ZERO(&rfds);
    FD_SET(m_socket,&rfds);
    tv.tv_sec=5;
    tv.tv_usec=0;
    result = select(m_socket+1,&rfds,NULL,NULL,&tv);
    if (result > 0)
    {
        int len=read(m_socket,&result,sizeof(result));
        close(m_socket);
        if (len!=sizeof(result)) return -1 ;
        return result ;
    }
    close(m_socket);
    return -1;
}


static void logloop()
{
    int i;
    int ptr;
    int data[USHM_SIZE];
    char buf[255];

    buf[0] = 0;
    ptr = utmphead->listhead;
    for (i = 0; i < USHM_SIZE; i++) {
        int j;
        char buf1[20];

        sprintf(buf1, "%d  ", ptr - 1);
        strcat(buf, buf1);
        data[i] = ptr - 1;
        for (j = 0; j < i; j++) {
            if (data[j] == data[i]) {
                bbslog("3system", "%s", buf);
                bbslog("3system", "find loop!");
                exit(0);
            }
        }
        if ((i - 1) % 10 == 0) {
            bbslog("3system", "%s", buf);
            buf[0] = 0;
        }
        ptr = utmphead->list_next[ptr - 1];
        if (ptr == utmphead->listhead) {
            bbslog("3system", "ok!");
            break;
        }
    }
}

int getnewutmpent(struct user_info *up, int is_www)
{
    utmpreq.command = UTMP_NEW;
    memcpy(&utmpreq.arg._new.utmp, up, sizeof(*up));
    utmpreq.arg._new.is_www = is_www;
    /* connect and send request */
    return sendutmpreq(&utmpreq);
}

// this can only be called in miscd utmpd
int getnewutmpent2(struct user_info *up, int is_www)
{
    struct user_info *uentp;
    time_t now;
    int pos, n, i,ret;
    int utmpfd, hashkey;

    utmpfd = utmp_lock();
    utmp_setreadonly(0);
    up->utmpkey=rand() % 100000000;
    pos = utmphead->hashhead[0] - 1;
    if (pos == -1) {
        ret=-1;
    } else {
        /* add to sorted list  */

        if (!utmphead->listhead) {  /* init the list head  */
            utmphead->list_prev[pos] = pos + 1;
            utmphead->list_next[pos] = pos + 1;
            utmphead->listhead = pos + 1;
        } else {
            int i;

            i = utmphead->listhead;
            if (strcasecmp(utmpshm->uinfo[i - 1].userid, up->userid) >= 0) {
                /* add to head */
                utmphead->list_prev[pos] = utmphead->list_prev[i - 1];
                utmphead->list_next[pos] = i;

                utmphead->list_prev[i - 1] = pos + 1;

                utmphead->list_next[utmphead->list_prev[pos] - 1] = pos + 1;

                utmphead->listhead = pos + 1;
            } else {
                int count;

                count = 0;
                i = utmphead->list_next[i - 1];
                while ((strcasecmp(utmpshm->uinfo[i - 1].userid, up->userid) < 0) && (i != utmphead->listhead)) {
                    i = utmphead->list_next[i - 1];
                    count++;
                    if (count > USHM_SIZE) {
                        utmphead->listhead = 0;
                        bbslog("3system", "UTMP:maybe loop rebuild!");
                        apply_ulist((APPLY_UTMP_FUNC) rebuild_list, NULL);
                        utmp_setreadonly(1);
                        utmp_unlock(utmpfd);
                        exit(-1);
                    }
                }

                utmphead->list_prev[pos] = utmphead->list_prev[i - 1];
                utmphead->list_next[pos] = i;

                utmphead->list_prev[i - 1] = pos + 1;

                utmphead->list_next[utmphead->list_prev[pos] - 1] = pos + 1;
            }
        }
        /*    */

        utmphead->hashhead[0] = utmphead->next[pos];

        if (utmpshm->uinfo[pos].active)
            if (utmpshm->uinfo[pos].pid) {
                bbslog("3system", "utmp: alloc a active utmp! old:%s new:%s", utmpshm->uinfo[pos].userid, up->userid);
                if (utmpshm->uinfo[pos].pid != 1)
                    kill(utmpshm->uinfo[pos].pid, SIGHUP);
            }
        utmpshm->uinfo[pos] = *up;
        hashkey = utmp_hash(up->userid);

        i = utmphead->hashhead[hashkey];
        /* not need sort */
        utmphead->next[pos] = i;
        utmphead->hashhead[hashkey] = pos + 1;

        utmphead->number++;

        setpublicshmreadonly(0);
        if (!is_www) {
            get_publicshm()->logincount ++;
        } else {
            get_publicshm()->wwwlogincount ++;
        }
        setpublicshmreadonly(1);

        if (get_utmp_number() + getwwwguestcount()>get_publicshm()->max_user) {
            setpublicshmreadonly(0);
            save_maxuser();
            setpublicshmreadonly(1);
        }
        now = time(NULL);
        if (!is_www && ((now > utmphead->uptime + 120) || (now < utmphead->uptime - 120))) {
            utmphead->uptime = now;
            newbbslog(BBSLOG_USIES, "UTMP:Clean user utmp cache");
            for (n = 0; n < USHM_SIZE; n++) {
                utmphead->uptime = now;
                uentp = &(utmpshm->uinfo[n]);
                if ((uentp->pid == 1)
                        && ((now - uentp->freshtime) < IDLE_TIMEOUT)) {
                    continue;
                }
                if (uentp->active && uentp->pid && (uentp->pid == 1 || kill(uentp->pid, 0) == -1)) {     /*uentp检查 */
                    char buf[STRLEN];

                    strncpy(buf, uentp->userid, IDLEN + 2);
                    if (uentp->pid == 1) { // web 用户超时离线 fancy Aug 21 2011
                        struct userec *user = getuserbynum(uentp->uid);
                        www_sync_stay(user, uentp);
                    }
                    clear_utmp2(n + 1);     /* 不需要再lock了 */
                    RemoveMsgCountFile(buf);
                }
            }
        }
        ret=pos+1;
    }
    utmp_setreadonly(1);
    utmp_unlock(utmpfd);
    return ret;
}

/* same as getnewutmpent() except no updating of utmpshm
 * only called in www
 */
#if 0
int getnewutmpent2(struct user_info *up, int is_www)
{
    int pos, i,ret;
    int utmpfd, hashkey;

    utmpfd = utmp_lock();
    utmp_setreadonly(0);
    up->utmpkey = rand() % 100000000;
    pos = utmphead->hashhead[0] - 1;
    if (pos == -1) {
        ret=-1;
    } else {
        /* add to sorted list  */

        if (!utmphead->listhead) {  /* init the list head  */
            utmphead->list_prev[pos] = pos + 1;
            utmphead->list_next[pos] = pos + 1;
            utmphead->listhead = pos + 1;
        } else {
            int i;

            i = utmphead->listhead;
            if (strcasecmp(utmpshm->uinfo[i - 1].userid, up->userid) >= 0) {
                /* add to head */
                utmphead->list_prev[pos] = utmphead->list_prev[i - 1];
                utmphead->list_next[pos] = i;

                utmphead->list_prev[i - 1] = pos + 1;

                utmphead->list_next[utmphead->list_prev[pos] - 1] = pos + 1;

                utmphead->listhead = pos + 1;
            } else {
                int count;

                count = 0;
                i = utmphead->list_next[i - 1];
                while ((strcasecmp(utmpshm->uinfo[i - 1].userid, up->userid) < 0) && (i != utmphead->listhead)) {
                    i = utmphead->list_next[i - 1];
                    count++;
                    if (count > USHM_SIZE) {
                        utmphead->listhead = 0;
                        bbslog("3system", "UTMP:maybe loop rebuild..!");
                        apply_ulist((APPLY_UTMP_FUNC) rebuild_list, NULL);
                        utmp_setreadonly(1);
                        utmp_unlock(utmpfd);
                        exit(-1);
                    }
                }

                utmphead->list_prev[pos] = utmphead->list_prev[i - 1];
                utmphead->list_next[pos] = i;

                utmphead->list_prev[i - 1] = pos + 1;

                utmphead->list_next[utmphead->list_prev[pos] - 1] = pos + 1;
            }
        }

        utmphead->hashhead[0] = utmphead->next[pos];

        if (utmpshm->uinfo[pos].active)
            if (utmpshm->uinfo[pos].pid) {
                bbslog("3system", "utmp: alloc a active utmp! old:%s new:%s", utmpshm->uinfo[pos].userid, up->userid);
                kill(utmpshm->uinfo[pos].pid, SIGHUP);
            }
        utmpshm->uinfo[pos] = *up;
        hashkey = utmp_hash(up->userid);

        i = utmphead->hashhead[hashkey];
        /* not need sort */
        utmphead->next[pos] = i;
        utmphead->hashhead[hashkey] = pos + 1;

        utmphead->number++;
        setpublicshmreadonly(0);
        if (!is_www) {
            get_publicshm()->logincount ++;
        } else {
            get_publicshm()->wwwlogincount ++;
        }
        setpublicshmreadonly(1);
        ret=pos+1;
    }
    utmp_setreadonly(1);
    utmp_unlock(utmpfd);
    return ret;
}
#endif
static int rebuild_list(struct user_info *up, char *arg, int p)
{
    int pos = p - 1;

    /* add to sorted list  */
    if ((up->userid[0] == 0) || (!up->active))
        return COUNT;

    if (!utmphead->listhead) {  /* init the list head  */
        utmphead->list_prev[pos] = pos + 1;
        utmphead->list_next[pos] = pos + 1;
        utmphead->listhead = pos + 1;
    } else {
        int i;

        i = utmphead->listhead;
        if (strcasecmp(utmpshm->uinfo[i - 1].userid, up->userid) >= 0) {
            /* add to head */
            utmphead->list_prev[pos] = utmphead->list_prev[i - 1];
            utmphead->list_next[pos] = i;

            utmphead->list_prev[i - 1] = pos + 1;

            utmphead->list_next[utmphead->list_prev[pos] - 1] = pos + 1;

            utmphead->listhead = pos + 1;
        } else {
            int count;

            count = 0;
            i = utmphead->list_next[i - 1];
            while ((strcasecmp(utmpshm->uinfo[i - 1].userid, up->userid) < 0) && (i != utmphead->listhead)) {
                i = utmphead->list_next[i - 1];
                count++;
                if (count > USHM_SIZE) {
                    bbslog("3system", "UTMP:rebuild maybe loop???");
                    f_cat("NOLOGIN", "系统故障，请稍后再来");
                    logloop();
                    exit(-1);
                }
            }

            utmphead->list_prev[pos] = utmphead->list_prev[i - 1];
            utmphead->list_next[pos] = i;

            utmphead->list_prev[i - 1] = pos + 1;

            utmphead->list_next[utmphead->list_prev[pos] - 1] = pos + 1;
        }
    }
    return COUNT;
}

int apply_ulist(APPLY_UTMP_FUNC fptr, void *arg)
{                               /* apply func on user list */
    struct user_info *uentp, utmp;
    int i, max;

    max = USHM_SIZE - 1;
    while (max > 0 && utmpshm->uinfo[max].active == 0)  /*跳过后段 非active的user */
        max--;
    for (i = 0; i <= max; i++) {
        uentp = &(utmpshm->uinfo[i]);
        utmp = *uentp;
        if ((*fptr)(&utmp, arg, i + 1) == QUIT)
            return QUIT;
    }
    return 0;
}

int apply_ulist_addr(APPLY_UTMP_FUNC fptr, void *arg)
{                               /* apply func on user list */
    int i;
    int num;

    i = utmphead->listhead;
    if (!i)
        return 0;
    num = 0;
    if (utmpshm->uinfo[i - 1].active) {
        if (fptr) {
            int ret;

            ret = (*fptr)(&utmpshm->uinfo[i - 1], arg, num);
            if (ret == QUIT)
                return num;
            if (ret == COUNT)
                num++;
        } else
            num++;
    }
    i = utmphead->list_next[i - 1];
    while (i != utmphead->listhead) {
        if (utmpshm->uinfo[i - 1].active) {
            if (fptr) {
                int ret;

                ret = (*fptr)(&utmpshm->uinfo[i - 1], arg, num);
                if (ret == QUIT)
                    return num;
                if (ret == COUNT)
                    num++;
            } else
                num++;
        }
        i = utmphead->list_next[i - 1];
        if (num >= USHM_SIZE) {
            utmphead->listhead = 0;
            bbslog("3system", "UTMP:maybe loop rebuild!!");
            apply_ulist((APPLY_UTMP_FUNC) rebuild_list, NULL);

            exit(0);
        };
    }
    return 0;
}

int apply_utmpuid(APPLY_UTMP_FUNC fptr, int uid, void *arg)
{
    char userid[IDLEN + 1];
    if (uid <= 0)
        return 0;
    strcpy(userid, getuserid2(uid));
    return apply_utmp(fptr, 0, userid, arg);
}

int apply_utmp(APPLY_UTMP_FUNC fptr, int maxcount,const char *userid, void *arg)
{
    int i, num;
    int hashkey;

    num = 0;
    hashkey = utmp_hash(userid);
    i = utmphead->hashhead[hashkey];
    while (i) {
        if ((utmpshm->uinfo[i - 1].active) && (!strcasecmp(utmpshm->uinfo[i - 1].userid, userid))) {
            int ret;

            if (fptr) {
                ret = (*fptr)(&utmpshm->uinfo[i - 1], arg, i);
                if (ret == QUIT)
                    break;
                if (ret == COUNT)
                    num++;
                if (maxcount && (num > maxcount))
                    break;
            } else
                num++;
        }
        i = utmphead->next[i - 1];
    }
    return num;
}

int search_ulist(struct user_info *uentp, int (*fptr)(int, struct user_info *), int farg)
/* ulist 中 search 符合fptr函数的 user */
{
    int i;

    for (i = 0; i < USHM_SIZE; i++) {
        *uentp = utmpshm->uinfo[i];
        if ((*fptr)(farg, uentp))
            return i + 1;
    }
    return 0;
}

void clear_utmp(int uent, int useridx, int pid)
{
    utmpreq.command = UTMP_CLR;
    utmpreq.arg._clr.uent = uent;
    utmpreq.arg._clr.uid = useridx;
    utmpreq.arg._clr.pid = pid;
    /* connect and clear */
    sendutmpreq(&utmpreq);
}

static void clear_utmp2(int uent)
{
    int hashkey, find;
    struct user_info zeroinfo;
    struct userec* user;

    if (!uent) {
        bbslog("3system", "UTMP:clear uent == 0 entry");
#if 0
        if (!CHECK_UENT(uinfo.uid))
            return;
        uent = getSession()->utmpent;
#endif
        return;
    }

    if (!utmpshm->uinfo[uent - 1].active) { //atppp 20051217
        bbslog("3system", "UTMP:clear inactive entry [%d]", uent);
        //    return;
    }
    user=getuserbynum(utmpshm->uinfo[uent-1].uid);
    do_after_logout(user,get_utmpent(uent),uent,0,true);
    do_after_logout(user,get_utmpent(uent),uent,0,false);
    hashkey = utmp_hash(utmpshm->uinfo[uent - 1].userid);
    find = utmphead->hashhead[hashkey];

    if (find == uent)
        utmphead->hashhead[hashkey] = utmphead->next[uent - 1];
    else {
        while (utmphead->next[find - 1] && utmphead->next[find - 1] != uent)
            find = utmphead->next[find - 1];
        if (!utmphead->next[find - 1])
            bbslog("3system", "UTMP:Can't find %s [%d]", utmpshm->uinfo[uent - 1].userid, uent);
        else
            utmphead->next[find - 1] = utmphead->next[uent - 1];
    }

    /* remove from sorted list */
    if (utmphead->listhead == uent) {
        utmphead->listhead = utmphead->list_next[uent - 1];
        if (utmphead->listhead == uent)
            utmphead->listhead = 0;
    }

    utmphead->list_next[utmphead->list_prev[uent - 1] - 1] = utmphead->list_next[uent - 1];
    utmphead->list_prev[utmphead->list_next[uent - 1] - 1] = utmphead->list_prev[uent - 1];
    /* */

    newbbslog(BBSLOG_USIES,"UTMP:clean %s(%d)", utmpshm->uinfo[uent - 1].userid, uent);
    utmphead->next[uent - 1] = utmphead->hashhead[0];
    utmphead->hashhead[0] = uent;
    /* Delete the user's msglist entry from webmsgd,
     * if the user is login from web. */
    /*
       if (utmpshm->uinfo[uent - 1].pid == 1)
           delfrom_msglist(uent, utmpshm->uinfo[uent - 1].userid);
     */
    zeroinfo.active = false;
    zeroinfo.pid = 0;
    zeroinfo.invisible = true;
    zeroinfo.sockactive = false;
    zeroinfo.sockaddr = 0;
    zeroinfo.destuid = 0;

    if (utmpshm->uinfo[uent - 1].active != false) {
        utmphead->number--;

        setpublicshmreadonly(0);
        if (utmpshm->uinfo[uent-1].pid != 1) {
            get_publicshm()->logoutcount ++;
            get_publicshm()->staytime += time(NULL) - utmpshm->uinfo[uent-1].logintime;
        } else {
            get_publicshm()->wwwlogoutcount ++;
            get_publicshm()->wwwstaytime += time(NULL) - utmpshm->uinfo[uent-1].logintime;
        }
        setpublicshmreadonly(1);
    }
    utmpshm->uinfo[uent - 1] = zeroinfo;
}

// this can only be called in miscd utmpd
void clear_utmp3(int uent, int useridx, int pid)
{
    int lockfd;

    /* ulock todo: use user lock
    */
    lockfd = utmp_lock();
    utmp_setreadonly(0);

    if (((useridx == 0) || (utmpshm->uinfo[uent - 1].uid == useridx)) && pid == utmpshm->uinfo[uent - 1].pid)
        clear_utmp2(uent);

    utmp_setreadonly(1);
    utmp_unlock(lockfd);
}

struct kickuser_save {
    int count;
    struct user_info *entp[10];
    int pid[10];
};
static int kickuser_count(struct user_info *uentp, int *arg, int pos)
{
    struct kickuser_save *a = (struct kickuser_save *)arg;
    a->entp[a->count] = uentp;
    a->pid[a->count] = uentp->pid;
    a->count++;
    if (a->count >= 10) return QUIT;
    return COUNT;
}
/* uentp == NULL means to kick all logins */
int kick_user_utmp(int uid, struct user_info *uentp, int signal)
{
    int i, currsave;
    struct kickuser_save s;
    s.count = 0;
    signal = signal ? signal: SIGHUP;
    if (!uentp) {
        struct userec *u = getuserbynum(uid);
        apply_utmp((APPLY_UTMP_FUNC) kickuser_count, 0, u->userid, &s);
    } else {
        s.entp[0] = uentp;
        s.pid[0] = uentp->pid;
        s.count = 1;
    }
    currsave = -1;
    for (i=0;i<s.count;i++) {
        struct user_info *enp = s.entp[i];
        if (enp->active) {
            if (enp -> pid == getpid() && s.count > 1) {
                currsave = i;
                continue;
            } else if (enp->pid != 1)
                kill(enp->pid, signal);
            clear_utmp(get_utmpent_num(enp), uid , s.pid[i]);
        }
    }
    if (currsave != -1) {
        struct user_info *enp = s.entp[currsave];
        if (enp -> active) {
            if (enp -> pid != 1)
                kill(enp -> pid, signal);
            clear_utmp(get_utmpent_num(enp), uid, s.pid[currsave]);
        }
    }
    return s.count;
}

int get_utmp_number()
{
    return utmphead->number;
}

struct user_info *get_utmpent(int utmpnum) {
    if (utmpnum <= 0)
        return NULL;
    return utmpshm->uinfo + (utmpnum - 1);
}

int get_utmpent_num(struct user_info *uent)
{
    if (uent == NULL)
        return -1;
    return uent - utmpshm->uinfo + 1;
}

static int cmpfuid(const void*a,const void*b)
{
    return strncasecmp(((struct friends*)a)->id, ((struct friends*)b)->id, IDLEN);
}


int getfriendstr(struct userec* user,struct user_info* puinfo,session_t * session)
{
    int nf;
    int i;
    struct friends *friendsdata;
    char buf[60];

    puinfo->friendsnum=0;
    if (session->topfriend != NULL) {
        free(session->topfriend);
        session->topfriend = NULL;
    }
    sethomefile(buf, user->userid, "friends");
    nf = get_num_records(buf, sizeof(struct friends));
    if (nf <= 0)
        return 0;
    if (nf>MAXFRIENDS)
        nf=MAXFRIENDS;
    friendsdata = (struct friends *) calloc(nf,sizeof(struct friends));
    get_records(buf, friendsdata, sizeof(struct friends), 1, nf);
    for (i = 0; i < nf; i++) {
        friendsdata[i].id[IDLEN] = 0;
        friendsdata[i].exp[LEN_FRIEND_EXP-1] = 0;
        if (id_invalid(friendsdata[i].id)||(searchuser(friendsdata[i].id)==0)) {
            memcpy(&friendsdata[i], &friendsdata[nf - 1], sizeof(struct friends));
            nf--;
        }
    }
    qsort(friendsdata, nf, sizeof(friendsdata[0]), cmpfuid);      /*For Bi_Search */
    session->topfriend = (struct friends_info *) calloc(nf,sizeof(struct friends_info));
    for (i = 0; i < nf; i++) {
        puinfo->friends_uid[i] = searchuser(friendsdata[i].id);
        strcpy(session->topfriend[i].exp, friendsdata[i].exp);
    }
    free(friendsdata);
    puinfo->friendsnum=nf;
    return 0;
}

int myfriend(int uid, char *fexp,session_t* session)
{
    int i, found = false;
    struct user_info* u;

    u = get_utmpent(session->utmpent);
    /*
     * char buf[IDLEN+3];
     */
    if (u == NULL || u->friendsnum<= 0) {
        return false;
    }
    for (i = 0; i < u->friendsnum; i++) {
        if (u->friends_uid[i] == uid) {
            found = true;
            break;
        }
    }
    if ((found) && fexp)
        strcpy(fexp, session->topfriend[i].exp);
    return found;
}

bool hisfriend(int uid,struct user_info* him)
{
    int i, found = false;
    if (him->friendsnum<= 0) {
        return false;
    }
    for (i = 0; i < him->friendsnum; i++) {
        if (him->friends_uid[i] == uid) {
            found = true;
            break;
        }
    }
    return found;
}

/* etnlegend, 2006.10.29, 更新用户昵称... */
static int update_username_proc(struct user_info *in,void *varg,int pos)
{
    const char *o_name=(((const char**)varg)[0]),*n_name=(((const char**)varg)[1]);
    if (!o_name||!strcmp(in->username,o_name))
        strcpy(in->username,n_name);
    return 0;
}

int update_username(const char *userid,const char *o_name,const char *n_name)
{
    const char *arg[2]={o_name,n_name};
    return apply_utmp(update_username_proc,0,userid,arg);
}

#define SESSIONLEN 9
void get_telnet_sessionid(char* buf,int unum)
{
    static const char encode[] = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    struct user_info* pui=get_utmpent(unum);
    int utmpkey=pui->utmpkey;
    buf[0]=encode[unum%36];
    unum/=36;
    buf[1]=encode[unum%36];
    unum/=36;
    buf[2]=encode[unum%36];

    buf[3]=encode[utmpkey%36];
    utmpkey/=36;
    buf[4]=encode[utmpkey%36];
    utmpkey/=36;
    buf[5]=encode[utmpkey%36];
    utmpkey/=36;
    buf[6]=encode[utmpkey%36];
    utmpkey/=36;
    buf[7]=encode[utmpkey%36];
    utmpkey/=36;
    buf[8]=encode[utmpkey%36];
    utmpkey/=36;

    buf[9]=0;
}

#ifdef NEW_BOARD_ACCESS
extern const unsigned int nba_flag[NBA_NUM];

/* 初始化，生成文件并填充0 */
int init_nba_status(char *filename)
{
    int fd;

    fd = open(filename, O_RDWR|O_CREAT, 0600);
    ftruncate(fd, NBA_SIZE);
    close(fd);
    return 0;
}

/* 根据uinfo及userec的uptime来确定load版面访问数据 */
int load_nba_status(struct user_info *uinfo)
{
    char filename[STRLEN];
    char *ptr;
    off_t size;

    if (getCurrentUser()->nba_uptime > uinfo->nba_uptime) {
        sethomefile(filename, getCurrentUser()->userid, NBA_FILE);
        BBS_TRY {
            if (!safe_mmapfile(filename, O_RDONLY, PROT_READ, MAP_SHARED, &ptr, &size, NULL))
                BBS_RETURN(-1);
            memcpy(uinfo->nba_st, ptr, NBA_SIZE);
        }
        BBS_CATCH {
        }
        BBS_END;
        end_mmapfile(ptr, size, -1);
        uinfo->nba_uptime = time(0);
    }
    return 0;
}

/* 在封禁、黑名单、俱乐部操作后设置版面访问数据 */
int set_nba_status(struct userec *user, int bid, int mode, int set)
{
    char filename[STRLEN];
    unsigned int *nba_st;
    char *ptr;
    off_t size;
    struct stat st;

    sethomefile(filename, user->userid, NBA_FILE);
    if (stat(filename, &st)==-1 || st.st_size<NBA_SIZE)
        init_nba_status(filename);
    BBS_TRY {
        if (!safe_mmapfile(filename, O_RDWR|O_CREAT, PROT_READ|PROT_WRITE, MAP_SHARED, &ptr, &size, NULL))
            BBS_RETURN(-1);
        nba_st = (unsigned int*)ptr;
        if (set)
            nba_st[bid-1] |= nba_flag[mode];
        else
            nba_st[bid-1] &= ~nba_flag[mode];
    }
    BBS_CATCH {
    }
    BBS_END;
    end_mmapfile(ptr, size, -1);

    user->nba_uptime = time(0);
    return 0;
}

/* 检查uinfo中版面访问数据 */
int check_nba_status(struct user_info *uinfo, int bid, int mode)
{
    load_nba_status(uinfo);
    return uinfo->nba_st[bid-1] & nba_flag[mode];
}

/* 检查userec中版面访问数据 */
int check_nba_data(struct userec *user, int bid, int mode)
{
    unsigned nba_data[MAXBOARD];
    char filename[STRLEN];
    char *ptr;
    off_t size;

    sethomefile(filename, user->userid, NBA_FILE);
    BBS_TRY {
        if (!safe_mmapfile(filename, O_RDONLY, PROT_READ, MAP_SHARED, &ptr, &size, NULL))
            BBS_RETURN(-1);
        memcpy(nba_data, ptr, NBA_SIZE);
    }
    BBS_CATCH {
    }
    BBS_END;
    end_mmapfile(ptr, size, -1);

    return nba_data[bid-1] & nba_flag[mode];
}
#endif
