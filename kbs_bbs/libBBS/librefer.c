#include "bbs.h"

#ifndef LIB_REFER
#ifdef ENABLE_REFER

#ifndef MAX_REFER
#define MAX_REFER 32
#endif

#ifdef ENABLE_BOARD_MEMBER
#ifndef MAX_BOARD_REFER
#define MAX_BOARD_REFER 16
#endif
#endif

#ifndef DEF_REFER
#define DEF_REFER 040000000004LL
#endif

#ifndef DEF_REPLY
#define DEF_REPLY 040000000010LL
#endif

#define MAX_REFER_INCEPTS 1024
int refer_incepts[MAX_REFER_INCEPTS];

int set_refer_file_from_mode(char *buf, const int mode)
{
    switch (mode) {
        case REFER_MODE_AT:
            sprintf(buf, "%s", REFER_DIR);
            break;
        case REFER_MODE_REPLY:
            sprintf(buf, "%s", REPLY_DIR);
            break;
        default:
            return -1;
    }

    return 0;
}

#ifdef ENABLE_BOARD_MEMBER
int get_refer_id_fromstr(char *ptr, int ptrlen, int id[], int boards[])
#else
int get_refer_id_fromstr(char *ptr, int ptrlen, int id[])
#endif
{
    char *p, *q, *r;
    int i, len, count, uid;
    char userid[IDLEN+2];
#ifdef ENABLE_BOARD_MEMBER
    int bid, b_count;
    char board[STRLEN+2];

    b_count=0;
#endif
    p = ptr;
    count = 0;
    while (*p!='\0' && ptrlen>0 && count<MAX_REFER) {
        if (*p == '@' && ((p>ptr && !isalnum(p[-1])) && isalpha(p[1]))) { /* 找到@, 并对前后字符进行判断 */
            for (q=p+2,r=p+1,len=1;isalnum(*q);q++)
                len++;
            p += len;
            ptrlen -= len;
            if (len>IDLEN)
                continue;
            strncpy(userid, r, len);
            userid[len] = '\0';
            if ((uid=getuser(userid, NULL))==0)
                continue;
            for (i=0;i<count;i++)
                if (id[i]==uid)
                    break;
            if (i==count) {
                id[count] = uid;
                count++;
            }
        }
#ifdef ENABLE_BOARD_MEMBER
    /* windinsn, 2012.8.14 */
        else if (*p == '@' && ((p>ptr && !isalnum(p[-1])) && p[1]=='#' && (isalpha(p[2])||p[2]=='.'||p[2]=='_'))) { /* 找到 @#, 并对前后进行判断，这个是驻版提醒 windinsn, 2012.8.14 */
            for (q=p+3,r=p+2,len=2;isalnum(*q)||q[0]=='.'||q[0]=='_';q++)
                len++;
            p += len;
            ptrlen -= len;
            if (len>STRLEN)
                continue;
            strncpy(board, r, len-1);
            board[len-1]='\0';

            if ((bid=getbid(board, NULL))==0)
                continue;
            for (i=0;i<b_count;i++)
                if (boards[i]==bid)
                    break;
            if (i==b_count) {
                boards[b_count]=bid;
                b_count++;
            }
        }
#endif
        else if (p==ptr && (strncmp(p, "发信人: ", 8)==0 || strncmp(p, "寄信人: ", 8)==0)) { /* 首行"发信人""寄信人"不匹配，避免昵称 */
            if ((q = strstr(p, "\n"))!=NULL) {
                p = q + 1;
                ptrlen -= (q - p + 1);
                continue;
            } else
                break;
        } else if (p>ptr && strncmp(p-1, "\n【 在 ", 7)==0) { /* “大作”行不匹配，避免昵称 */
            p += 6;
            ptrlen -= 6;
            if ((q = strstr(p, "的大作中提到: 】"))!=NULL) {
                p = q + 17;
                ptrlen -= (q - p + 17);
            }
            continue;
        } else if (p>ptr && strncmp(p-1, "\n: ", 3)==0) { /* 引文整行都不匹配 */
            if ((q = strstr(p, "\n"))!=NULL) {
                p = q + 1;
                ptrlen -= (q - p + 1);
                continue;
            } else
                break;
        } else if (p>ptr && strncmp(p-1, "\n--\n", 4)==0) { /* 跳过签名档及以后 */
            break;
        }
        p++;
        ptrlen--;
    }
    return count;
}

int send_refer_msg(const char *boardname, struct fileheader *fh, struct fileheader *re, char *tmpfile) {
    char *ptr,*cur_ptr;
    off_t ptrlen, mmap_ptrlen;
    //char c='\0', last_c;
    //int in_at=false;
    //char id[IDLEN+1];
    //int id_pos=0;
    struct userec *user;
    const struct boardheader *board;
    int users[MAX_REFER];
#ifdef ENABLE_BOARD_MEMBER
    int boards[MAX_BOARD_REFER];
    struct boardheader *to_board;
#endif
    int times=0;
    //int sent=false;
    int i;//,uid;
#ifdef ENABLE_BOARD_MEMBER
    bzero(boards, MAX_BOARD_REFER * sizeof(int));
#endif
    for (i=0;i<MAX_REFER_INCEPTS;i++)
        refer_incepts[i]=0;

    board=getbcache(boardname);
    if (0==board)
        return -1;
    if (board->flag&BOARD_GROUP)
        return -2;

    if (0==safe_mmapfile(tmpfile, O_RDONLY, PROT_READ, MAP_SHARED, &ptr, &mmap_ptrlen, NULL))
        return -1;
    ptrlen=mmap_ptrlen;
    cur_ptr=ptr;
#ifdef ENABLE_BOARD_MEMBER
    times = get_refer_id_fromstr(ptr, fh->attachment?fh->attachment:mmap_ptrlen, users, boards);
#else
    times = get_refer_id_fromstr(ptr, fh->attachment?fh->attachment:mmap_ptrlen, users);
#endif
    for (i=0;i<times;i++) {
        user = getuserbynum(users[i]);
        send_refer_msg_to(user, board, fh, tmpfile);
    }
#ifdef ENABLE_BOARD_MEMBER
    for (i=0;i<MAX_BOARD_REFER;i++) {
        if (boards[i]<=0)
            break;
        to_board=getboard(boards[i]);
        if (NULL!=to_board)
            send_refer_msg_to_board(to_board, board, fh, tmpfile);
    }
#endif
    /*
    while(ptrlen>0) {
        last_c=c;
        c=*cur_ptr;

        if (in_at) {
            if (id_pos>IDLEN) {
              in_at=false;
            } else if (isalpha(c)||(isdigit(c)&&id_pos>0)) {
                id[id_pos++]=c;
            } else {
              in_at=false;
              id[id_pos]='\0';
              if (times<MAX_REFER&&id_pos>1&&(uid=getuser(id, &user))!=0) {
                 sent=false;
                 for (i=0;i<MAX_REFER;i++) if (users[i]==uid) {
                     sent=true;
                     break;
                 } else if (users[i]==0) {
                     break;
                 }
                 if (!sent) {
                    if (send_refer_msg_to(user, board, fh, tmpfile)>=0)
                        users[times++]=uid;
                 }
              }
            }
        } else if (!isalnum(last_c)&&c=='@') {
            in_at=true;
            id[0]='\0';
            id_pos=0;
        }

        if (c=='\0') break;
        ptrlen--;
        cur_ptr++;
    }
    */
    end_mmapfile(ptr, mmap_ptrlen, -1);

    if (fh->reid!=fh->id&&re&&getuser(re->owner,&user)!=0) {
        send_refer_reply_to(user, board, fh);
    }

    return 0;
}
int send_refer_reply_to(struct userec *user, const struct boardheader *board, struct fileheader *fh) {
    if (0==strcmp(user->userid, "guest")||0==strcmp(user->userid, "SYSOP"))
        return -1;
    if (!DEFINE(user, DEF_REPLY))
        return -2;
    if (0==strncasecmp(getSession()->currentuser->userid,user->userid, IDLEN))
        return -3;
    if (!check_read_perm(user,board))
        return -4;
    if (0!=check_mail_perm(getSession()->currentuser, user))
        return -5;

    char buf[255];
    struct refer refer;

    memset(&refer, 0, sizeof(refer));

    strncpy(refer.board, board->filename, STRLEN);
    strncpy(refer.user, fh->owner, IDLEN);
    strnzhcpy(refer.title, fh->title, ARTICLE_TITLE_LEN);
    refer.id=fh->id;
    refer.groupid=fh->groupid;
    refer.reid=fh->reid;
    refer.flag=0;
    refer.time=time(NULL);

    sethomefile(buf, user->userid, REPLY_DIR);
    if (-1==append_record(buf, &refer, sizeof(refer)))
        return -6;

    setmailcheck(user->userid);
    newbbslog(BBSLOG_USER, "sent reply refer '%s' to '%s'", fh->title, user->userid);

    return 0;
}
int send_refer_msg_to(struct userec *user, const struct boardheader *board, struct fileheader *fh, char *tmpfile) {
    int i, uid;

    if (0==strcmp(user->userid, "guest"))
        return -1;
    if (!DEFINE(user, DEF_REFER))
        return -2;
    if (!getSession()->currentuser || 0==strncasecmp(getSession()->currentuser->userid,user->userid, IDLEN))
        return -3;
    if (!check_read_perm(user,board))
        return -4;
    if (0!=check_mail_perm(getSession()->currentuser, user))
        return -5;

    uid=getuser(user->userid, NULL);
    for (i=0;i<MAX_REFER_INCEPTS;i++) {
        if (refer_incepts[i]<=0)
            break;
        if (refer_incepts[i]==uid)
            return 0;
    }

    if (i>=MAX_REFER_INCEPTS)
        return -7;
    refer_incepts[i]=uid;

    if(0==strncasecmp(user->userid, "sysop", 5)) {
        mail_file(getSession()->currentuser->userid, tmpfile, user->userid, fh->title, 0, fh);
        newbbslog(BBSLOG_USER, "sent refer '%s' to '%s'", fh->title, user->userid);
        return 0;
    }

    char buf[255];
    struct refer refer;

    memset(&refer, 0, sizeof(refer));

    strncpy(refer.board, board->filename, STRLEN);
    strncpy(refer.user, fh->owner, IDLEN);
    strnzhcpy(refer.title, fh->title, ARTICLE_TITLE_LEN);
    refer.id=fh->id;
    refer.groupid=fh->groupid;
    refer.reid=fh->reid;
    refer.flag=0;
    refer.time=time(NULL);

    sethomefile(buf, user->userid, REFER_DIR);
    if (-1==append_record(buf, &refer, sizeof(refer)))
        return -6;

    setmailcheck(user->userid);
    newbbslog(BBSLOG_USER, "sent refer '%s' to '%s'", fh->title, user->userid);

    return 0;
}
#ifdef ENABLE_BOARD_MEMBER
int send_refer_msg_to_board(struct boardheader *to_board, const struct boardheader *board, struct fileheader *fh, char *tmpfile) {
    int total, i, num;
    struct board_member *b_members = NULL;
    struct userec *lookupuser;

    if (!getCurrentUser())
        return 0;
    if (!HAS_PERM(getSession()->currentuser,PERM_SYSOP)&&!chk_currBM(to_board->BM,getSession()->currentuser)&&!is_board_member_manager(to_board->filename, getSession()->currentuser->userid, NULL))
        return 0;

    total=get_board_members(to_board->filename);
    if (total<0)
        return -1;
    if (total==0)
        return 0;

    b_members=(struct board_member *) malloc(sizeof(struct board_member) * total);
    bzero(b_members, sizeof(struct board_member) * total);
    num=load_board_members(to_board->filename, b_members, BOARD_MEMBER_SORT_DEFAULT, 0, total);

    for (i=0;i<num;i++) {
        if (b_members[i].status != BOARD_MEMBER_STATUS_NORMAL && b_members[i].status != BOARD_MEMBER_STATUS_MANAGER)
            continue;
        if(getuser(b_members[i].user, &lookupuser)) {
            send_refer_msg_to(lookupuser, board, fh, tmpfile);
        }
    }

    free(b_members);
    return 0;
}
#endif /* ENABLE_BOARD_MEMBER */
int refer_remove(char *dir, int ent, struct refer *refer) {
    char buf[PATHLEN];
    char *t;

    strcpy(buf, dir);
    if ((t=strrchr(buf, '/'))!=NULL)
        *t='\0';
    if (!delete_record(dir, sizeof(*refer), ent, (RECORD_FUNC_ARG)refer_cmp, refer)) {
        setmailcheck(getCurrentUser()->userid);
        return 0;
    }

    return 1;
}
int refer_cmp(struct refer *r1, struct refer *r2) {
    if (strncasecmp(r1->board, r2->board, STRLEN)==0&&r1->id==r2->id)
        return 1;
    return 0;
}
int refer_get_refer_count(struct userec *user) {
    return refer_get_count(user, REFER_DIR);
}
int refer_get_reply_count(struct userec *user) {
    return refer_get_count(user, REPLY_DIR);
}
int refer_get_count(struct userec *user, char *filename) {
    char buf[STRLEN];
    struct stat st;
    struct refer refer;

    sethomefile(buf, user->userid, filename);
    if (stat(buf, &st)<0)
        return 0;
    return st.st_size/sizeof(refer);
}
int refer_get_refer_new(struct userec *user) {
    return refer_get_new(user, REFER_DIR);
}
int refer_get_reply_new(struct userec *user) {
    return refer_get_new(user, REPLY_DIR);
}
int refer_get_new(struct userec *user, char *filename) {
    char buf[STRLEN];
    struct stat st;
    struct refer refer;
    int pos, fd;
    int i, size;
    unsigned char ch;

    int total_num=0;
    int new_num=0;

    sethomefile(buf, user->userid, filename);
    if (stat(buf, &st)<0)
        return 0;

    size=sizeof(refer);
    total_num=st.st_size/size;
    if (total_num<=0)
        return 0;

    if ((fd=open(buf, O_RDONLY))<0)
        return 0;

    pos=(int)((char *) &(refer.flag)-(char *) &(refer));
    lseek(fd, pos, SEEK_SET);

    i=0;
    while (i<total_num) {
        if ((i++)>0)
            lseek(fd, size, SEEK_CUR);

        read(fd, &ch, 1);
        if (!(ch&FILE_READ)) new_num++;
        lseek(fd, -1, SEEK_CUR);
    }

    close(fd);
    return new_num;
}
int refer_read_all(char *buf) {
    struct stat st;
    struct refer refer;
    int pos, fd;
    int total, i, size;
    unsigned char ch;

    if (stat(buf, &st)<0)
        return 0;

    size=sizeof(refer);
    total=st.st_size/size;
    if (total<=0)
        return 0;

    if ((fd=open(buf, O_RDWR))<0)
        return 0;

    pos=(int)((char *) &(refer.flag)-(char *) &(refer));
    lseek(fd, pos, SEEK_SET);

    i=0;
    while (i<total) {
        if ((i++)>0)
            lseek(fd, size, SEEK_CUR);

        read(fd, &ch, 1);
        if (!(ch&FILE_READ)) {
            ch |= FILE_READ;
            lseek(fd, -1, SEEK_CUR);
            write(fd, &ch, 1);
        }
        lseek(fd, -1, SEEK_CUR);
    }

    close(fd);

    return 0;
}
int refer_truncate(char *buf) {
    struct stat st;

    if (stat(buf, &st)<0)
        return 0;

    if (st.st_size/sizeof(struct refer)<=0)
        return 0;

    unlink(buf);

    return 0;
}
#endif
#endif
