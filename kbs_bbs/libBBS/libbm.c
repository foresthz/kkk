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
    int i, count;
    struct stat st;
    
    if ((fd=open(file, O_RDONLY))==-1)
        return 0;
    if (fstat(fd, &st)!=0) {
        close(fd);
        return 0;
    }
    *ptr = (char *)malloc(st.st_size);
    read(fd, *ptr, st.st_size);
    count = 0;
    result[0] = *ptr;
    for (i=1;i<st.st_size;i++) {
        if ((*ptr)[i] == '\n') {
            (*ptr)[i] = '\0';
            i++;
            count++;
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
            if (count>=maxcount)
                break;
        }
    }
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

    if (board)
        setvfile(filename, board, "deny_reason");
    else
        sprintf(filename, "etc/deny_reason");
    if ((fn=fopen(filename, "w"))!=NULL) {
        for (i=0;i<count;i++)
            fprintf(fn, "%s\n", denyreason[i]);
        fclose(fn);
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
int deny_announce(char *uident, const struct boardheader *bh, char *reason, int day, struct userec *operator, time_t time, int mode)
{
    struct userec *lookupuser;
    char tmplfile[STRLEN], postfile[STRLEN], title[STRLEN], title1[STRLEN], timebuf[STRLEN];
    int sysop;

    gettmpfilename(postfile, "ann_deny.%d", getpid());
    sprintf(tmplfile, "etc/denypost_template");

    if ((HAS_PERM(operator, PERM_SYSOP) || HAS_PERM(operator, PERM_OBOARDS))
            && !chk_BM_instr(bh->BM, operator->userid))
        sysop = 1;
    else
        sysop = 0;
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
            sprintf(opbuf, NAME_BBS_CHINESE NAME_SYSOP_GROUP DENY_NAME_SYSOP "��\x1b[4m%s\x1b[m", operator->userid);
        else
            sprintf(opbuf, NAME_BM ":\x1b[4m%s\x1b[m", operator->userid);
        if (write_formatted_file(tmplfile, postfile, "ssssss",
                    uident, bh->filename, reason, daystr, opbuf, ctime_r(&time, timebuf))<0)
            return -1;
    } else {
        FILE *fn;
        fn = fopen(postfile, "w+");
        fprintf(fn, "���� \x1b[4m%s\x1b[m �� \x1b[4m%s\x1b[m ��� \x1b[4m%s\x1b[m ��Ϊ��\n", uident, bh->filename, reason);
        if (day)
            fprintf(fn, DENY_BOARD_AUTOFREE " \x1b[4m%d\x1b[m �졣\n", day);
        else
            fprintf(fn, DENY_BOARD_NOAUTOFREE "\n");
        if (sysop) {
            fprintf(fn, "                            %s" NAME_SYSOP_GROUP DENY_NAME_SYSOP "��\x1b[4m%s\x1b[m\n", NAME_BBS_CHINESE, operator->userid);
        } else {
            fprintf(fn, "                              " NAME_BM ":\x1b[4m%s\x1b[m\n", operator->userid);
        }
        fprintf(fn, "                              %s\n", ctime_r(&time, timebuf));
        fclose(fn);
    }
#ifdef NEWSMTH
    post_file(operator, "", postfile, bh->filename, title, 0, 1, getSession());
#else
    post_file(operator, "", postfile, bh->filename, title, 0, 2, getSession());
#endif
    post_file(operator, "", postfile, "denypost", title1, 0, -1, getSession());
    unlink(postfile);

    return 0;
}

/* ʹ��ģ�巢������ʼ�
 * mode:
 *      0:���
 *      1:�޸�
 */
int deny_mailuser(char *uident, const struct boardheader *bh, char *reason, int day, struct userec *operator, time_t time, int mode)
{
    struct userec *lookupuser;
    char tmplfile[STRLEN], mailfile[STRLEN], title[STRLEN], timebuf[STRLEN];
    int sysop;

    gettmpfilename(mailfile, "mail_deny.%d", getpid());
    sprintf(tmplfile, "etc/denymail_template");

    if ((HAS_PERM(operator, PERM_SYSOP) || HAS_PERM(operator, PERM_OBOARDS))
            && !chk_BM_instr(bh->BM, operator->userid))
        sysop = 1;
    else
        sysop = 0;
    getuser(uident, &lookupuser);
    if (mode==0) {
        sprintf(title, "%s��ȡ����%s��ķ���Ȩ��", uident, bh->filename);
    } else {
        sprintf(title, "�޸�%s��%s��ķ���Ȩ�޷��", uident, bh->filename);
    }
    if (dashf(tmplfile)) {
        char sender[STRLEN], sitename[STRLEN], opfrom[STRLEN], daystr[4], opbuf[STRLEN];
        sprintf(daystr, "%d", day);
        if (sysop) {
            sprintf(sender, "SYSOP (%s) ", NAME_SYSOP);
            sprintf(sitename, "%s (%24.24s)", BBS_FULL_NAME, ctime_r(&time, timebuf));
            sprintf(opfrom, "%s", NAME_BBS_ENGLISH);
            sprintf(opbuf, NAME_BBS_CHINESE NAME_SYSOP_GROUP DENY_NAME_SYSOP "��\x1b[4m%s\x1b[m", operator->userid);
        } else {
            sprintf(sender, "%s ", operator->userid);
            sprintf(sitename, "%s (%24.24s)", BBS_FULL_NAME, ctime_r(&time, timebuf));
            sprintf(opfrom, "%s", SHOW_USERIP(operator, getSession()->fromhost));
            sprintf(opbuf, NAME_BM ":\x1b[4m%s\x1b[m", operator->userid);
        }
        if (write_formatted_file(tmplfile, mailfile, "sssssssss",
                    sender, title, sitename, opfrom, bh->filename, reason, daystr, opbuf, ctime_r(&time, timebuf))<0)
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
            if (day)
                fprintf(fn, DENY_DESC_AUTOFREE " \x1b[4m%d\x1b[m ��", day);
            else
                fprintf(fn, DENY_DESC_NOAUTOFREE);
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
            if (day)
                fprintf(fn, DENY_DESC_AUTOFREE " \x1b[4m%d\x1b[m ��", day);
            else
                fprintf(fn, DENY_DESC_NOAUTOFREE);
            fprintf(fn, "\n");
            fprintf(fn, "                              " NAME_BM ":\x1b[4m%s\x1b[m\n", operator->userid);
            fprintf(fn, "                              %s\n", ctime_r(&time, timebuf));
        }
        fclose(fn);
    }
    if (lookupuser)
#ifdef NEWSMTH
        mail_file(DELIVER, mailfile, uident, title, 0, NULL);
#else
        mail_file(operator->userid, mailfile, uident, title, 0, NULL);
#endif
    unlink(mailfile);

    return 0;
}
