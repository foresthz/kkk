#include "bbs.h"

#ifndef LIB_REFER
#ifdef ENABLE_REFER
#ifndef MAX_REFER
#define MAX_REFER 10
#endif

int send_refer_msg(char *boardname, struct fileheader *fh, char *tmpfile) {
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
            if (id_pos>=IDLEN) {
              in_at=false;
            } else if (isalpha(c)||(isdigit(c)&&id_pos>0)) {
                id[id_pos++]=c;
            } else {
              in_at=false;
              id[id_pos]='\0';
              if (times<MAX_REFER&&id_pos>1&&(uid=getuser(id, &user))!=0&&check_read_perm(user,board)&&DEFINE(user, DEF_REFER)&&0!=strncasecmp(getSession()->currentuser->userid,user->userid, IDLEN)&&0==check_mail_perm(getSession()->currentuser, user)) {
                 sent=false;
                 for (i=0;i<MAX_REFER;i++) if (users[i]==uid) {
                     sent=true;
                     break;
                 }
                 if (!sent) {
                     mail_file(fh->owner, tmpfile, user->userid, fh->title, 0, fh); 
                     newbbslog(BBSLOG_USER, "sent refer '%s' to '%s'", fh->title, user->userid);

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

    return 0;
}
#endif
#endif
