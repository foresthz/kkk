#ifndef BBS_SYSTEM_H_5423523
#define BBS_SYSTEM_H_5423523

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <ctype.h>
#include <string.h>
#include <fcntl.h>
#include <dirent.h>
#include <utime.h>
#include <errno.h>
#include <sys/stat.h>
#include <sys/types.h>

#include "kbs_config.h"

#ifndef HAVE_STRSEP
char* strsep(char **strptr,const char *delim);
#endif

#ifndef HAVE_STRCASESTR
char *strcasestr(const char *haystack, const char *needle);
#endif


#define READ_BUFFER_SIZE 8192

#ifndef HAVE_FLOCK
#define LOCK_SH 1       /* Shared Lock */
#define LOCK_EX 2       /* Exclusive Lock */
#define LOCK_NB 4       /* Non-Blocking */
#define LOCK_UN 8       /* Unlock */
#endif

#ifndef O_LARGEFILE
#define O_LARGEFILE 0
#endif

typedef int (*OUTC_FUNC)(char);

int f_cat(const char *file,const char *str);
int f_catfile(const char *src,const char *dst);
int f_cp(const char *src,const char *dst,int mode);
int f_ln(const char *src,const char *dst);
int lock_reg(int fd,int cmd,int type,off_t offset,int whence,off_t len);
#ifndef HAVE_FLOCK
int flock(int fd,int op);
#endif
int f_mv(const char *src,const char *dst);
int f_rm(const char *path);
int f_touch(const char *file);
void uuencode(const char* inbuf,int size,const char *filename,OUTC_FUNC fn);


char* bm_strcasestr_rp(const char *string,const char *pattern,size_t *shift,int *init);    /* �ַ�����δ�Сд������ƥ�亯��*/
#if 0 //atppp 20060404
char* bm_strstr(const char *string,const char *pattern);                                    /* �ַ���ƥ�亯��*/
char* bm_strstr_rp(const char *string,const char *pattern,size_t *shift,int *init);        /* �ַ������ƥ�亯��*/
char* bm_strcasestr(const char *string,const char *pattern);                                /* �ַ�����Сд�����е�ƥ�亯��*/
void *memfind(const void *in_block,         /* ���ݿ� */
              const size_t block_size,      /* ���ݿ鳤�� */
              const void *in_pattern,       /* ��Ҫ���ҵ����� */
              const size_t pattern_size,    /* �������ݵĳ��� */
              size_t *shift,                /* ��λ��Ӧ����256*size_t������ */
              int *init);                  /* �Ƿ���Ҫ��ʼ����λ�� */
void *txtfind(const void *in_block,         /* ���ݿ� */
              const size_t block_size,      /* ���ݿ鳤�� */
              const void *in_pattern,       /* ��Ҫ���ҵ����� */
              const size_t pattern_size,    /* �������ݵĳ��� */
              size_t *shift,                /* ��λ��Ӧ����256*size_t������ */
              int *init);                  /* �Ƿ���Ҫ��ʼ����λ�� */
#endif


int from64tobits(char *out,const char *in); /* Base64 to Big-Endian, �����ֽڼ��� */
void to64frombits(unsigned char *out,const unsigned char *in,int inlen);



#define read_lock(fd,offset,whence,len)\
	lock_reg(fd,F_SETLK,F_RDLCK,offset,whence,len)
#define readw_lock(fd,offset,whence,len)\
	lock_reg(fd,F_SETLKW,F_RDLCK,offset,whence,len)
#define write_lock(fd,offset,whence,len)\
	lock_reg(fd,F_SETLK,F_WRLCK,offset,whence,len)
#define writew_lock(fd,offset,whence,len)\
	lock_reg(fd,F_SETLKW,F_WRLCK,offset,whence,len)
#define un_lock(fd,offset,whence,len)\
	lock_reg(fd,F_SETLKW,F_UNLCK,offset,whence,len)

#endif
