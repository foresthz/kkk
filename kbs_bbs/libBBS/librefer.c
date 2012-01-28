#include "bbs.h"

#ifndef LIB_REFER
#ifdef ENABLE_REFER

#ifndef MAX_REFER
#define MAX_REFER 32
#endif

#ifndef DEF_REFER
#define DEF_REFER 040000000004LL
#endif

#ifndef DEF_REPLY
#define DEF_REPLY 040000000010LL
#endif

int send_refer_msg(const char *boardname, struct fileheader *fh, struct fileheader *re, char *tmpfile) {
    char *ptr,*cur_ptr;
    off_t ptrlen, mmap_ptrlen;    
    char c='\0', last_c;
    int in_at=false;
    char id[IDLEN+1];
    int id_pos=0;
    struct userec *user;
    const struct boardheader *board;
    int users[MAX_REFER];
    int times=0;
    int sent=false;
    int i,uid;

    board=getbcache(boardname);
    if (0==board)
        return -1;
    if (board->flag&BOARD_GROUP)
        return -2;

    if (0==safe_mmapfile(tmpfile, O_RDONLY, PROT_READ, MAP_SHARED, &ptr, &mmap_ptrlen, NULL))
        return -1;
    ptrlen=mmap_ptrlen;
    cur_ptr=ptr;
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
    end_mmapfile(ptr, mmap_ptrlen, -1);

    if (fh->reid!=fh->id&&re&&getuser(re->owner,&user)!=0) {
        send_refer_reply_to(user, board, fh);
    }

    return 0;
}
int send_refer_reply_to(struct userec *user, const struct boardheader *board, struct fileheader *fh) {
    if (0==strncasecmp(user->userid, "guest", 5)||0==strncasecmp(user->userid, "sysop", 5))
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

    strncpy(refer.board, board->filename, IDLEN+6);
    strncpy(refer.user, getSession()->currentuser->userid, IDLEN);
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
    if (0==strncasecmp(user->userid, "guest", 5))
        return -1;
    if (!DEFINE(user, DEF_REFER))
        return -2;
    if (0==strncasecmp(getSession()->currentuser->userid,user->userid, IDLEN))
        return -3;
    if (!check_read_perm(user,board))
        return -4;
    if (0!=check_mail_perm(getSession()->currentuser, user))
        return -5;

    if(0==strncasecmp(user->userid, "sysop", 5)) {
        mail_file(getSession()->currentuser->userid, tmpfile, user->userid, fh->title, 0, fh);
        newbbslog(BBSLOG_USER, "sent refer '%s' to '%s'", fh->title, user->userid);
        return 0;
    }

    char buf[255];  
    struct refer refer;

    memset(&refer, 0, sizeof(refer));

    strncpy(refer.board, board->filename, IDLEN+6);
    strncpy(refer.user, getSession()->currentuser->userid, IDLEN);
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
    if (strncasecmp(r1->board, r2->board, IDLEN+6)==0&&r1->id==r2->id)
        return 1;
    return 0;
}
#endif
#endif
