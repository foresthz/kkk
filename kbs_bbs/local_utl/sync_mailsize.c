/********
atppp: ͬ���ʼ��ֽ���
�·����ʼ���������ˣ����������Ҫ���ڼ�����ǰ���ʼ����ֽ���
�����д��Ӧ.DIR���effsize
*/


#include "bbs.h"

int calc_mailsize(char *userid, char *dirname)
{
    char fn[256];
    int fd, total, i;
    struct stat buf;
    struct flock ldata;
    struct fileheader * ptr1;
    char * ptr;
    int size=sizeof(struct fileheader);

    setmailfile(fn, userid, dirname);

    if ((fd = open(fn, O_RDWR, 0664)) == -1) {
        bbslog("user", "%s", "recopen err");
        return 0;      /* �����ļ���������*/
    }
    fstat(fd, &buf);
    ldata.l_type = F_RDLCK;
    ldata.l_whence = 0;
    ldata.l_len = 0;
    ldata.l_start = 0;
    fcntl(fd, F_SETLKW, &ldata);
    total = buf.st_size / size;

    if ((i = safe_mmapfile_handle(fd, PROT_READ|PROT_WRITE, MAP_SHARED, (void **) &ptr, &buf.st_size)) != 1) {
        if (i == 2)
            end_mmapfile((void *) ptr, buf.st_size, -1);
        ldata.l_type = F_UNLCK;
        fcntl(fd, F_SETLKW, &ldata);
        close(fd);
        return 0;
    }
    ptr1 = (struct fileheader *) ptr;
    for (i = 0; i < total; i++) {
        struct stat st;
        char ffn[256];
        setmailfile(ffn, userid, ptr1->filename);
        if (lstat(ffn, &st) == -1 || !S_ISREG(st.st_mode)) ptr1->eff_size = 0;
        else ptr1->eff_size = st.st_size;

        ptr1++;
    }
    end_mmapfile((void *) ptr, buf.st_size, -1);
    ldata.l_type = F_UNLCK;
    fcntl(fd, F_SETLKW, &ldata);
    close(fd);
    return 0;
}

int sync_mailsize(struct userec *user, void *arg)
{
    char buf[40];
    int i;
    struct _mail_list mail;

    if (!user->userid[0]) return 0;
    calc_mailsize(user->userid, DOT_DIR);
    calc_mailsize(user->userid, ".SENT");
    calc_mailsize(user->userid, ".DELETED");
    load_mail_list(user, &mail);
    for (i = 0; i < mail.mail_list_t; i++) {
        sprintf(buf, ".%s", mail.mail_list[i] + 30);
        calc_mailsize(user->userid, buf);
    }
    printf("%s\n", user->userid);
    return 0;
}

static void 
usage()
{
    fprintf(stderr, "Usage: sync_mailsize <-a|-u userid>\n\n");
    fprintf(stderr, "    If -a parameter is provided, this program will reset all userids' mail sizes,\n");
    fprintf(stderr, "    else only reset the specified userid's mail size.\n");
}

int 
main(int argc, char ** argv)
{
    struct userec *user = NULL;

    chdir(BBSHOME);
    resolve_ucache();
    if (argc == 2 && !strcmp(argv[1], "-a"))
        apply_users(sync_mailsize, NULL);
    else if (argc == 3 && !strcmp(argv[1], "-u"))
    {
        getuser(argv[2], &user);
        if (user == NULL)
        {
            fprintf(stderr, "User %s not found.\n", argv[2]);
            return -1;
        }
        sync_mailsize(user, NULL);
    }
    else
        usage();

    return 0;
}

