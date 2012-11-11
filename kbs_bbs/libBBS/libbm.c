/*
 * bm�Ĳ�������
 */

#include "bbs.h"

/* ��ȡtxt�ļ������ݣ��������ַ�������
 * Ҫȷ��result���鲻����maxcount��������ɺ�caller��Ҫfree(*ptr)
 * return:
 *        0 �ļ������ڻ����
 *        count �ַ�����
 */
int get_textfile_string(const char *file, char **ptr, char *result[], int maxcount)
{
    int fd;
    int count, size;
    struct stat st;
    char *p, *q;
    
    if ((fd=open(file, O_RDONLY))==-1)
        return 0;
    if (fstat(fd, &st)!=0 || st.st_size==0) {
        close(fd);
        return 0;
    }
    *ptr = (char *)malloc(st.st_size);
    read(fd, *ptr, st.st_size);
    count = 0;
    q = p = *ptr;
    if (*p!='#' && *p!='\n') /* ����ע���кͳ���Ϊ0���� */
        result[count++] = p;
    size = st.st_size;
    while (size>0 && (p=(char *)memmem(p, size, "\n", 1))!=NULL) {
        *p = '\0';
        p++;
        if (*p == '\r')
            p++;
        size -= p-q;
        if (size<=0 || count>=maxcount)
            break;
        q = p;
        if (*p!='#' && *p!='\n') /* ����ע���кͳ���Ϊ0���� */
            result[count++] = p;
    }
    /*
    for (i=1;i<st.st_size;i++) {
        if ((*ptr)[i] == '\n') {
            (*ptr)[i] = '\0';
            i++;
            count++;
            if (count>=maxcount)
                break;
            if (i==st.st_size)
                break;
            if ((*ptr)[i] == '\r') {
                i++;
                if (i==st.st_size)
                    break;
                result[count] = &((*ptr)[i]);
            } else {
                result[count] = &((*ptr)[i]);
            }
        }
    }
    */
    close(fd);
    return count;
}

/*
 * ��÷�������б�denyreason�ڵ���ʱ����
 * boardΪ��ʱ�����ϵͳ��������б� 
 */
int get_deny_reason(const char *board, char denyreason[][STRLEN], int max)
{
    char filename[STRLEN];
    char *ptr, *reason[MAXDENYREASON];
    int count, i;

    max = (max>MAXDENYREASON) ? MAXDENYREASON : max;
    if (board)
        setvfile(filename, board, "deny_reason");
    else
        sprintf(filename, "etc/deny_reason");
    count = get_textfile_string(filename, &ptr, reason, max);
    if (count > 0) {
        for (i=0;i<count;i++) {
            remove_blank_ctrlchar(reason[i], denyreason[i], true, true, true);
            denyreason[i][30] = '\0';
            //strcpy(denyreason[i], reason[i]);
        }
        free(ptr);
    }
    return count;
}

int save_deny_reason(const char *board, char denyreason[][STRLEN], int count)
{
    char filename[STRLEN];
    int i;
    FILE *fn;

    setvfile(filename, board, "deny_reason");

#ifdef BOARD_SECURITY_LOG
    char oldfilename[STRLEN];
    gettmpfilename(oldfilename, "old_deny_reason");
    f_cp(filename, oldfilename, 0); 
#endif

    if ((fn=fopen(filename, "w"))!=NULL) {
        for (i=0;i<count;i++)
            fprintf(fn, "%s\n", denyreason[i]);
        fclose(fn);
#ifdef BOARD_SECURITY_LOG
        board_file_report(board, "�޸� <����������>", oldfilename, filename);
        unlink(oldfilename);
#endif
    }
    return 0;
}

/* �ӷ���ļ���ȡ���ַ����л�÷������, ���ļ����ݸ�ʽ��ϵ���� */
int get_denied_reason(const char *buf, char *reason)
{
    char genbuf[STRLEN], *p;

    strcpy(genbuf, buf);
    p = genbuf + IDLEN;
    genbuf[42] = '\0';
    remove_blank_ctrlchar(p, reason, true, true, false);
    return 0;
}

/* �ӷ���ļ���ȡ���ַ����л�÷������ID, ���ļ����ݸ�ʽ��ϵ���� */
int get_denied_operator(const char *buf, char *opt)
{
    char genbuf[STRLEN], *p;

    strcpy(genbuf, buf);
    p = genbuf+43;
    p[IDLEN] = '\0';
    remove_blank_ctrlchar(p, opt, true, true, false);
    return 0;
}

#ifdef MANUAL_DENY
/* �ӷ���ļ���ȡ���ַ����л���Զ����/�ֶ����ѡ����ļ����ݸ�ʽ��ϵ���� */
int get_denied_freetype(const char *buf)
{
    return (!strncmp(buf+64, "��", 2));    
}
#endif

/* �ӷ���ļ���ȡ���ַ����л�ȡ���ʱ�� */
time_t get_denied_time(const char *buf)
{
    time_t denytime;
    char genbuf[STRLEN], *p;

    strcpy(genbuf, buf);
    if (NULL != (p = strrchr(genbuf, '[')))
        sscanf(p + 1, "%lu", &denytime);
    else
        denytime = time(0) + 1;
    return denytime;
}

/* ʹ��ģ�巢���������
 * mode:
 *      0:���
 *      1:�޸�
 */
#ifdef RECORD_DENY_FILE
int deny_announce(char *uident, const struct boardheader *bh, char *reason, int day, struct userec *operator, time_t time, int mode, const struct fileheader *fh, int filtermode)
#else
int deny_announce(char *uident, const struct boardheader *bh, char *reason, int day, struct userec *operator, time_t time, int mode)
#endif
{
    struct userec *lookupuser;
    char tmplfile[STRLEN], postfile[STRLEN], title[STRLEN], title1[STRLEN], timebuf[STRLEN];
    int sysop;
#ifdef MEMBER_MANAGER
	int core_member=0;
#endif	

    gettmpfilename(postfile, "ann_deny.%d", getpid());
    sprintf(tmplfile, "etc/denypost_template");

    if ((HAS_PERM(operator, PERM_SYSOP) || HAS_PERM(operator, PERM_OBOARDS))
            && !chk_BM_instr(bh->BM, operator->userid))
        sysop = 1;
    else {
        sysop = 0;
#ifdef MEMBER_MANAGER
		if (!HAS_PERM(operator, PERM_SYSOP)&&!chk_currBM(bh->BM, operator))
			core_member=1;
#endif		
	}
	
    getuser(uident, &lookupuser);
    if (mode==0) {
        sprintf(title, "%s��ȡ����%s��ķ���Ȩ��", uident, bh->filename);
        if (PERM_BOARDS & lookupuser->userlevel)
            sprintf(title1, "%s ��ĳ��" NAME_BM " %s �� %s", operator->userid, uident, bh->filename);
        else
            sprintf(title1, "%s �� %s �� %s", operator->userid, uident, bh->filename);
    } else {
        sprintf(title, "�޸�%s��%s��ķ���Ȩ�޷��", uident, bh->filename);
        if (PERM_BOARDS & lookupuser->userlevel)
            sprintf(title1, "%s �޸�ĳ��" NAME_BM " %s �� %s �ķ��", operator->userid, uident, bh->filename);
        else
            sprintf(title1, "%s �޸� %s �� %s �ķ��", operator->userid, uident, bh->filename);
    }
    if (dashf(tmplfile)) {
        char daystr[4], opbuf[STRLEN];
        sprintf(daystr, "%d", day);
        if (sysop)
            sprintf(opbuf, "%s" NAME_SYSOP_GROUP DENY_NAME_SYSOP "��\x1b[4m%s\x1b[m",  NAME_BBS_CHINESE, operator->userid);
#ifdef MEMBER_MANAGER
		else if (core_member)
			sprintf(opbuf, NAME_CORE_MEMBER ":\x1b[4m%s\x1b[m", operator->userid);
#endif
		else
            sprintf(opbuf, NAME_BM ":\x1b[4m%s\x1b[m", operator->userid);
        if (write_formatted_file(tmplfile, postfile, "ssssss",
                    uident, bh->filename, reason, daystr, opbuf, ctime_r(&time, timebuf))<0)
            return -1;
    } else {
        FILE *fn;
        fn = fopen(postfile, "w+");
        fprintf(fn, "���� \x1b[4m%s\x1b[m �� \x1b[4m%s\x1b[m ��� \x1b[4m%s\x1b[m ��Ϊ��\n", uident, bh->filename, reason);
        fprintf(fn, DENY_BOARD_AUTOFREE " \x1b[4m%d\x1b[m �졣\n", day);
        /* day������0�죬����Ҫ�����и��� */
        /*
        if (day)
            fprintf(fn, DENY_BOARD_AUTOFREE " \x1b[4m%d\x1b[m �졣\n", day);
        else
            fprintf(fn, DENY_BOARD_NOAUTOFREE "\n");
        */
#ifdef ZIXIA
        GetDenyPic(fn, DENYPIC, ndenypic, dpcount);
#endif 
        if (sysop) {
            fprintf(fn, "                            %s" NAME_SYSOP_GROUP DENY_NAME_SYSOP "��\x1b[4m%s\x1b[m\n", NAME_BBS_CHINESE, operator->userid);
        }
#ifdef MEMBER_MANAGER
		else if (core_member) {
			fprintf(fn, "                              " NAME_CORE_MEMBER ":\x1b[4m%s\x1b[m\n", operator->userid);
		}
#endif		
		else {
            fprintf(fn, "                              " NAME_BM ":\x1b[4m%s\x1b[m\n", operator->userid);
        }
        fprintf(fn, "                              %s\n", ctime_r(&time, timebuf));
        fclose(fn);
    }
#ifdef NEWSMTH
	post_file(operator, "", postfile, bh->filename, title, 0, 1 /*core_member?2:1*/, getSession());
#else
    post_file(operator, "", postfile, bh->filename, title, 0, 2, getSession());
#endif
#ifdef RECORD_DENY_FILE
    if (fh) {
        char bname[STRLEN], filebuf[STRLEN], filestr[256];
        FILE *fn, *fn2;
        int size;
        
        fn = fopen(postfile, "r+");
        fseek(fn, 0, SEEK_END);
        if (filtermode==0)
            sprintf(bname, "%s", bh->filename);
        else if (filtermode==1)
            sprintf(bname, "%s", "Filter");
        else
            sprintf(bname, "%sFilter", bh->filename);
        setbfile(filebuf, bname, fh->filename);
        if (!dashf(filebuf)) {
            int i;
            char prefix[4]="MDJ", *p, ch;
            p = strrchr(filebuf, '/') + 1;
            ch = *p;
            for (i=0;i<3;i++) {
                if (ch == prefix[i])
                    continue;
                *p = prefix[i];
                if (dashf(filebuf))
                    break;
            }
        }
        if ((fn2=fopen(filebuf, "r"))==NULL) {
            fprintf(fn, "\033[1;31;45mϵͳ����, �޷���ʾȫ��, ����ϵ����վ��. \033[K\033[m\n");
        } else {
            fprintf(fn, "\033[1;33;45m�����Ǳ�������ȫ��");
            if (filtermode)
                fprintf(fn, "(��Դ��\033[32m%s\033[33m������)", filtermode==1?"ϵͳ":bh->filename);
            fprintf(fn, ":\033[K\033[m\n");
            while ((size=-attach_fgets(filestr,256,fn2))) {
                if (size<0)
                    fprintf(fn,"%s",filestr);
                else
                    put_attach(fn2,fn,size);
            }
            fclose(fn2);
            fprintf(fn, "\033[1;33;45mȫ�Ľ���.\033[K\033[m\n");
        }
        fclose(fn);
    }
#endif
    post_file(operator, "", postfile, "denypost", title1, 0, -1, getSession());
#ifdef BOARD_SECURITY_LOG
    FILE *fn;
    fn = fopen(postfile, "w");
    fprintf(fn, "\033[33m����û�: \033[4;32m%s\033[m\n", uident);
    fprintf(fn, "\033[33m���ԭ��: \033[4;32m%s\033[m\n", reason);
    fprintf(fn, "\033[33m�������: \033[4;32m %d ��\033[m\n", day);
    fclose(fn);
    sprintf(title, "%s %s �ڱ���ķ���Ȩ��", mode==0?"ȡ��":"�޸�", uident);
#ifdef RECORD_DENY_FILE
    board_security_report(postfile, operator, title, bh->filename, fh);
#else
    board_security_report(postfile, operator, title, bh->filename, NULL);
#endif
#endif
    unlink(postfile);

    return 0;
}

/* ʹ��ģ�巢������ʼ�
 * mode:
 *      0:���
 *      1:�޸�
 */
int deny_mailuser(char *uident, const struct boardheader *bh, char *reason, int day, struct userec *operator, time_t time, int mode, int autofree)
{
    struct userec *lookupuser;
    char tmplfile[STRLEN], mailfile[STRLEN], title[STRLEN], timebuf[STRLEN];
    int sysop;

#ifdef MEMBER_MANAGER
	int core_member=0;
#endif
	
    gettmpfilename(mailfile, "mail_deny.%d", getpid());
    sprintf(tmplfile, "etc/denymail_template");

    if ((HAS_PERM(operator, PERM_SYSOP) || HAS_PERM(operator, PERM_OBOARDS))
            && !chk_BM_instr(bh->BM, operator->userid))
        sysop = 1;
    else {
        sysop = 0;
#ifdef MEMBER_MANAGER
		if (!HAS_PERM(operator, PERM_SYSOP)&&!chk_currBM(bh->BM, operator))
			core_member=1;
#endif	
	}
	
    getuser(uident, &lookupuser);
    if (mode==0) {
        sprintf(title, "%s��ȡ����%s��ķ���Ȩ��", uident, bh->filename);
    } else {
        sprintf(title, "�޸�%s��%s��ķ���Ȩ�޷��", uident, bh->filename);
    }
    if (dashf(tmplfile)) {
        char sender[STRLEN], sitename[STRLEN], opfrom[STRLEN], daystr[16], opbuf[STRLEN], replyhint[STRLEN];
        sprintf(daystr, "\x1b[4m%d\x1b[m ��", day);
        if (!autofree)
            sprintf(replyhint, "�����ں���ظ�\n��������ָ�Ȩ��");
        else
            replyhint[0] = '\0';
        if (sysop) {
            sprintf(sender, "SYSOP (%s) ", NAME_SYSOP);
            sprintf(sitename, "%s (%24.24s)", BBS_FULL_NAME, ctime_r(&time, timebuf));
            sprintf(opfrom, "%s", NAME_BBS_ENGLISH);
            sprintf(opbuf, "%s" NAME_SYSOP_GROUP DENY_NAME_SYSOP "��\x1b[4m%s\x1b[m",NAME_BBS_CHINESE, operator->userid);
        } else {
            sprintf(sender, "%s ", operator->userid);
            sprintf(sitename, "%s (%24.24s)", BBS_FULL_NAME, ctime_r(&time, timebuf));
            sprintf(opfrom, "%s", SHOW_USERIP(operator, getSession()->fromhost));
#ifdef MEMBER_MANAGER
			if (core_member)
			sprintf(opbuf, NAME_CORE_MEMBER ":\x1b[4m%s\x1b[m", operator->userid);
			else
#endif			
            sprintf(opbuf, NAME_BM ":\x1b[4m%s\x1b[m", operator->userid);
        }
        if (write_formatted_file(tmplfile, mailfile, "ssssssssss",
                    sender, title, sitename, opfrom, bh->filename, reason, daystr, replyhint, opbuf, ctime_r(&time, timebuf))<0)
            return -1;
    } else {
        FILE *fn;
        fn = fopen(mailfile, "w+");
        if (sysop) {
            fprintf(fn, "������: SYSOP (%s) \n", NAME_SYSOP);
            fprintf(fn, "��  ��: %s\n", title);
            fprintf(fn, "����վ: %s (%24.24s)\n", BBS_FULL_NAME, ctime_r(&time, timebuf));
            fprintf(fn, "��  Դ: %s\n", NAME_BBS_ENGLISH);
            fprintf(fn, "\n");
            fprintf(fn, "�������� \x1b[4m%s\x1b[m �� \x1b[4m%s\x1b[m���Һ��ź���֪ͨ���� \n", bh->filename, reason);
            fprintf(fn, DENY_DESC_AUTOFREE " \x1b[4m%d\x1b[m ��", day);
            /* day������0�죬����Ҫ�����и��� */
            /* if (day)
                fprintf(fn, DENY_DESC_AUTOFREE " \x1b[4m%d\x1b[m ��", day);
            else
                fprintf(fn, DENY_DESC_NOAUTOFREE);
            */
            if (!autofree)
                fprintf(fn, "�����ں���ظ�\n��������ָ�Ȩ�ޡ�\n");
            fprintf(fn, "\n");
            fprintf(fn, "                            %s" NAME_SYSOP_GROUP DENY_NAME_SYSOP "��\x1b[4m%s\x1b[m\n", NAME_BBS_CHINESE, operator->userid);
            fprintf(fn, "                              %s\n", ctime_r(&time, timebuf));
        } else {
            fprintf(fn, "������: %s \n", operator->userid);
            fprintf(fn, "��  ��: %s\n", title);
            fprintf(fn, "����վ: %s (%24.24s)\n", BBS_FULL_NAME, ctime_r(&time, timebuf));
            fprintf(fn, "��  Դ: %s \n", SHOW_USERIP(operator, getSession()->fromhost));
            fprintf(fn, "\n");
            fprintf(fn, "�������� \x1b[4m%s\x1b[m �� \x1b[4m%s\x1b[m���Һ��ź���֪ͨ���� \n", bh->filename, reason);
            fprintf(fn, DENY_DESC_AUTOFREE " \x1b[4m%d\x1b[m ��", day);
            /* day������0�죬����Ҫ�����и��� */
            /* if (day)
                fprintf(fn, DENY_DESC_AUTOFREE " \x1b[4m%d\x1b[m ��", day);
            else
                fprintf(fn, DENY_DESC_NOAUTOFREE);
            */
            if (!autofree)
                fprintf(fn, "�����ں���ظ�\n��������ָ�Ȩ�ޡ�\n");
#ifdef ZIXIA
            GetDenyPic(fn, DENYPIC, ndenypic, dpcount);
#endif 
            fprintf(fn, "\n");
#ifdef MEMBER_MANAGER
			if (core_member)
			fprintf(fn, "                              " NAME_CORE_MEMBER ":\x1b[4m%s\x1b[m\n", operator->userid);
			else
#endif				
            fprintf(fn, "                              " NAME_BM ":\x1b[4m%s\x1b[m\n", operator->userid);
            fprintf(fn, "                              %s\n", ctime_r(&time, timebuf));
        }
        fclose(fn);
    }
    if (lookupuser)
#ifdef NEWSMTH
        mail_file(DELIVER /*core_member?operator->userid:DELIVER*/, mailfile, uident, title, 0, NULL);
#else
        mail_file(operator->userid, mailfile, uident, title, 0, NULL);
#endif
    unlink(mailfile);

    return 0;
}

#ifdef TITLEKEYWORD
/*
 * ��ñ���ؼ����б�titlekey�ڵ���ʱ����
 * boardΪ��ʱ�����ϵͳ����ؼ���
 */
int get_title_key(const char *board, char titlekey[][8], int max)
{
    char filename[STRLEN];
    char *ptr, *key[MAXTITLEKEY];
    int count, i;

    max = (max>MAXTITLEKEY) ? MAXTITLEKEY : max;
    if (board)
        setvfile(filename, board, "title_keyword");
    else
        sprintf(filename, "etc/title_keyword");
    count = get_textfile_string(filename, &ptr, key, max);
    if (count > 0) {
        for (i=0;i<count;i++) {
            remove_blank_ctrlchar(key[i], titlekey[i], true, true, true);
            titlekey[i][6] = '\0';
        }   
        free(ptr);
    }   
    return count;
}   

int save_title_key(const char *board, char titlekey[][8], int count)
{
    char filename[STRLEN];
    int i;
    FILE *fn;
    
    setvfile(filename, board, "title_keyword");

#ifdef BOARD_SECURITY_LOG
    char oldfilename[STRLEN];
    gettmpfilename(oldfilename, "old_title_keyword");
    f_cp(filename, oldfilename, 0);
#endif

    if ((fn=fopen(filename, "w"))!=NULL) {
        for (i=0;i<count;i++)
            fprintf(fn, "%s\n", titlekey[i]);
        fclose(fn);
        load_title_key(0, getbid(board, NULL), board);
#ifdef BOARD_SECURITY_LOG
        board_file_report(board, "�޸� <����ؼ���>", oldfilename, filename);
        unlink(oldfilename);
#endif
    }
    return 0;
}
#endif

#ifdef BOARD_SECURITY_LOG
/* ���������ļ��޸ļ�¼, ͨ��diff����¾��ļ�����
 */
void board_file_report(const char *board, char *title, char *oldfile, char *newfile)
{       
    FILE *fn, *fn2;
    char filename[STRLEN], buf[256];

    gettmpfilename(filename, "board_report_file", getpid());
    if((fn=fopen(filename, "w"))!=NULL){
        if(strstr(title, "ɾ��")){
            fprintf(fn, "\033[1;33;45mɾ���ļ���Ϣ����\033[K\033[m\n");
    
            if((fn2=fopen(oldfile, "r"))!=NULL){
                while(fgets(buf, 256, fn2)!=NULL)
                    fputs(buf, fn);
                fclose(fn2);
                fprintf(fn, "\n");
            }
            fclose(fn);
        } else {
            char genbuf[STRLEN*2], filediff[STRLEN];
            gettmpfilename(filediff, "filediff", getpid());
            if(!dashf(oldfile)){
                f_touch(oldfile);
            }
            sprintf(genbuf, "diff -u %s %s > %s", oldfile, newfile, filediff);
            system(genbuf);
            fprintf(fn, "\033[1;33;45m�޸��ļ���Ϣ����\033[K\033[m\n");
        
            if((fn2=fopen(filediff, "r"))!=NULL){
                /* ����ǰ2�� */
                fgets(buf, 256, fn2);fgets(buf, 256, fn2);
                while(fgets(buf, 256, fn2)!=NULL){
                    if(buf[0]=='-')
                        fprintf(fn, "\033[1;35m%s\033[m", buf);
                    else if(buf[0]=='+')
                        fprintf(fn, "\033[1;36m%s\033[m", buf);
                    else
                        fputs(buf, fn);
                }
                fclose(fn2);
                fprintf(fn, "\n");
            }
            fclose(fn);
            unlink(filediff);
        }
        board_security_report(filename, getCurrentUser(), title, board, NULL);
        unlink(filename);
    }
}

/* ��ð��氲ȫ��¼������id */
int board_last_log_id(const char *board)
{
    struct fileheader fh;
    char filename[STRLEN * 2];
    int count;

    setbdir(DIR_MODE_BOARD, filename, board);
    memset(&fh, 0, sizeof(struct fileheader));
    count = get_num_records(filename, sizeof(struct fileheader));
    if (count <= 0)
        return 0;
    get_record(filename, &fh, sizeof(struct fileheader), count);

    if (fh.id != 0)
        return fh.id;
    return 0;
}

/* ���氲ȫ��¼����DIR_MODE_BOARD���������, jiangjun, 20100610 */
int board_security_report(const char *filename, struct userec *user, const char *title, const char *bname, const struct fileheader *xfh)
{       
    struct fileheader fh;
    struct boardheader bh;
    FILE *fin, *fout;
    char buf[STRLEN], bufcp[READ_BUFFER_SIZE], timebuf[STRLEN];
    int mode, fd;
    time_t now;
    size_t size;
            
    bzero(&fh, sizeof(struct fileheader));
    setbpath(buf, bname);
    if (!getboardnum(bname, &bh))
        return 1;
    if (GET_POSTFILENAME(fh.filename, buf))
        return 2;
    POSTFILE_BASENAME(fh.filename)[0] = 'B';
    set_posttime(&fh);
    
    mode = 0;
    if (user == NULL)
        mode = 1;  //�Զ�����
    if (mode)
        memcpy(fh.owner, DELIVER, OWNER_LEN);
    else
        memcpy(fh.owner, user -> userid, OWNER_LEN);
    fh.owner[OWNER_LEN - 1] = 0;
    strnzhcpy(fh.title, title, ARTICLE_TITLE_LEN);
    setbfile(buf, bname, fh.filename);
    if (!(fout = fopen(buf, "w")))
        return 3;

    if (mode) {
        now = time(NULL);
        fprintf(fout, "������: "DELIVER" (�Զ�����ϵͳ), ����: %s��ȫ��¼\n", bname);
        fprintf(fout, "��  ��: %s\n", fh.title);
        fprintf(fout, "����վ: %s�Զ�����ϵͳ (%24.24s)\n\n", BBS_FULL_NAME, ctime_r(&now, timebuf));
    } else {
        now = time(NULL);
        fprintf(fout, "������: %s (%s), ����: %s��ȫ��¼\n", user->userid, user->username, bname);
        fprintf(fout, "��  ��: %s\n", fh.title);
        fprintf(fout, "����վ: %s (%24.24s)\n\n", BBS_FULL_NAME, ctime_r(&now, timebuf));
    }
    fprintf(fout, "\033[36m�������ȫ��¼\033[m\n");
    fprintf(fout, "\033[33m��¼ԭ��: \033[32m%s %s\033[m\n", (mode)?"":user->userid, title);
    if (!mode)
        fprintf(fout, "\033[33m�û���Դ: \033[32m%s\033[m\n", getSession()->fromhost);
    if (filename != NULL) {
        if (!(fin = fopen(filename, "r"))) {
            fprintf(fout, "\n\033[45;31mϵͳ�����޷���¼�����ϸ��Ϣ\033[K\033[m\n");
        } else {
            fprintf(fout, "\n\033[36m���β���������Ϣ\033[m\n");
            while (true) {
                size = fread(bufcp, 1, READ_BUFFER_SIZE, fin);
                if (size == 0)
                    break;
                fwrite(bufcp, size, 1, fout);
            }
            fclose(fin);
        }
    }
    if (xfh) {
        time_t t=get_posttime(xfh);
        fprintf(fout, "\n\033[36m���β�����Ӧ������Ϣ\033[m\n");
        fprintf(fout, "\033[33m���±���: \033[4;32m%s\033[m\n", xfh->title);
        fprintf(fout, "\033[33m��������: \033[4;32m%s\033[m\n", xfh->owner);
        fprintf(fout, "\033[33m����ID��: \033[4;32m%d\033[m\n", xfh->id);
        fprintf(fout, "\033[33m����ʱ��: \033[4;32m%s\033[m\n", ctime(&t));
    }
    fclose(fout);
    fh.eff_size = get_effsize_attach(buf, &fh.attachment);
    setbdir(DIR_MODE_BOARD, buf, bname);
    if ((fd = open(buf, O_WRONLY|O_CREAT, S_IRUSR | S_IWUSR | S_IRGRP | S_IWGRP | S_IROTH)) == -1) {
        return 5;
    }
    writew_lock(fd, 0, SEEK_SET, 0);
    fh.id = board_last_log_id(bname) + 1;
    fh.groupid = fh.id;
    fh.reid = fh.id;
    if (xfh)
        fh.o_id = xfh->id;
    else
        fh.o_id = 0;
    lseek(fd, 0, SEEK_END);
    if (safewrite(fd, &fh, sizeof(struct fileheader)) == -1) {
        un_lock(fd, 0, SEEK_SET, 0);
        close(fd);
        setbfile(buf, bname, fh.filename);
        unlink(buf);
        return 6;
    }
    un_lock(fd, 0, SEEK_SET, 0);
    close(fd);
    return 0;
}
#endif
