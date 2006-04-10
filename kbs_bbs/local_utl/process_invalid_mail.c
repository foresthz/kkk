/* etnlegend, 2006.04.11, �ż���Ч�Լ�⼰�޸����� */

/*
 * ������:          process_invalid_mail [ -h | -u <userid> ]
 * ѡ���:
 *   -u <userid>    �����û� <userid> ���ż����д���, Ĭ�϶�ȫ���û����ż����д���
 *   -h             ��ʾ�ĵ���Ϣ
 *
*/

#include "bbs.h"
#define PIMP_LEN _POSIX_PATH_MAX
#define PIMF_MOD 0644

static char userid[IDLEN+1];                    /* ����һ�û�����µ��û����� */
static char dirs[MAILBOARDNUM+3][16];           /* ���п�����Ч�� DIR ����*/
static char path[PIMP_LEN];                     /* ��ǰ�û�����·�������� */
static char *p;                                 /* ��ǰ·������������ָ�� */
static struct fileheader *dir;                  /* ��ǰ DIR ӳ��ָ�� */
static int count;                               /* ��ǰ DIR �����ż����� */
static int dir_handle;                          /* ��ǰ DIR ӳ���Ψһ���� */

static int pim_usage(void){                     /* ��ӡ�ĵ���Ϣ */
    fprintf(stdout,"%s",
        "  ������:          process_invalid_mail [ -h | -u <userid> ]\n"
        "  ѡ���:\n"
        "    -u <userid>    �����û� <userid> ���ż����д���, Ĭ�϶�ȫ���û����ż����д���\n"
        "    -h             ��ʾ�ĵ���Ϣ\n");
    return 0;
}

static int gen_dirs(void){                      /* �������п�����Ч DIR ���� */
    int index,suffix;
    index=0;
    sprintf(dirs[index++],"%s",".DIR");
    sprintf(dirs[index++],"%s",".SENT");
    sprintf(dirs[index++],"%s",".DELETED");
    for(suffix=1;!(suffix>MAILBOARDNUM);suffix++)
        sprintf(dirs[index++],".MAILBOX%d",suffix);
    return 0;
}

static int map_dir(int index){                  /* ���� DIR ӳ����� */
    static const struct flock lck_set={F_WRLCK,SEEK_SET,0,0,0};
    static const struct flock lck_remove={F_UNLCK,SEEK_SET,0,0,0};
    static struct stat st;
    static int dir_mapped,fd;
    static void *p_map;
    if(dir_mapped){
        munmap(p_map,st.st_size);
        ftruncate(fd,(count*sizeof(struct fileheader)));
        fcntl(fd,F_SETLKW,&lck_remove);
        close(fd);
        dir_mapped=0;
    }
    if(index<0||index>(MAILBOARDNUM+2))
        return 1;
    sprintf(p,"%s",dirs[index]);
    if(stat(path,&st)==-1||!S_ISREG(st.st_mode))
        return 2;
    if((fd=open(path,O_RDWR
#ifdef __USE_GNU
        |O_NOATIME                              /* ���ܻ��е��Ż����ð� */
#endif /* __USE_GNU */
        ,PIMF_MOD))==-1)
        return 3;
    if(fcntl(fd,F_SETLKW,&lck_set)==-1){
        close(fd);
        return 4;
    }
    if((p_map=mmap(NULL,st.st_size,PROT_READ|PROT_WRITE,MAP_SHARED,fd,0))==MAP_FAILED){
        fcntl(fd,F_SETLKW,&lck_remove);
        close(fd);
        return 5;
    }
    dir=(struct fileheader*)p_map;
    count=(st.st_size/sizeof(struct fileheader));
    dir_handle++;
    dir_mapped=1;
    return 0;
}

static int process_dir(void){                   /* �����ż���Ч�Լ����� */
    static int dir_processed_handle;
    struct stat st;
    int index;
    if(!(dir_handle>dir_processed_handle))
        return 1;
    index=0;
    while(index<count){
        sprintf(p,"%s",dir[index].filename);
        if(stat(path,&st)==-1||!S_ISREG(st.st_mode)){
            unlink(path);
            memmove(&dir[index],&dir[index+1],((count-(index+1))*sizeof(struct fileheader)));
            count--;
            continue;
        }
        index++;
    }
    dir_processed_handle=dir_handle;
    return 0;
}

static int process_user(const char *userid){
    struct stat st;
    int index;
    setmailpath(path,userid);
    if(stat(path,&st)==-1||!S_ISDIR(st.st_mode))
        return 1;
    p=&path[strlen(path)];
    (*p++)='/';
    for(index=0;index<(MAILBOARDNUM+3);index++){
        if(map_dir(index))
            continue;
        process_dir();
    }
    map_dir(-1);
    return 0;
}

static int pim_callback_func(struct userec *user,void *arg){
    return process_user(user->userid);
}

int main(int argc,char **argv){
#define ME_QUIT(s) do{fprintf(stderr,"error: %s\n",s);exit(__LINE__);}while(0)
    struct userec *user;
    int ret;
    opterr=0;
    while((ret=getopt(argc,argv,"u:h"))!=-1){
        switch(ret){
            case 'u':
                if(!*optarg){
                    pim_usage();
                    ME_QUIT("invalid user ...");
                }
                snprintf(userid,IDLEN+1,"%s",optarg);
                break;
            case 'h':
                pim_usage();
                return 0;
            default:
                pim_usage();
                ME_QUIT("get options ...");
        }
    }
    if(optind!=argc){
        pim_usage();
        ME_QUIT("get parameters ...");
    }
    if(chdir(BBSHOME)==-1)
        ME_QUIT("change directory to BBSHOME ...");
    resolve_ucache();
    gen_dirs();
    if(userid[0]){
        if(!getuser(userid,&user))
            ME_QUIT("get specified user ...");
        process_user(user->userid);
    }
    else
        apply_users(pim_callback_func,NULL);
    return 0;
#undef ME_QUIT
}

