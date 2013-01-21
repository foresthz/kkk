#include "bbs.h"


/* new msg system, windinsn, Jan 21,2013 */
#ifdef ENABLE_NEW_MSG
#ifndef NEW_MSG_LIB
#define NEW_MSG_LIB
#include "sqlite3.h"

#define NEW_MSG_SQL_SELECT_MESSAGE_FULL "[id],[user],strftime('%%s',[time], 'localtime'),[host],[from],[msg],[msg_size],[attachment_type],[attachment_size],[attachment_name],[flag]"
#define NEW_MSG_SQL_SELECT_USER_FULL "[id], [msg_id], [user], strftime('%%s',[time], 'localtime'), [host], [from], [msg], [msg_size], [count], [flag]"


unsigned long new_msg_ip2long(const char *ip)
{
	unsigned long int addr;
	int len=sizeof(ip);
	if (len==0||(addr=inet_addr(ip))==INADDR_NONE) {
		if (len==sizeof("255.255.255.255")-1 && !memcmp(ip, "255.255.255.255", sizeof("255.255.255.255")-1))
			return 0xFFFFFFFF;
		return 0;
	}	
	return ntohl(addr);
}

/**
  * 获取数据库中的某些数值
  * @param int type:   1 -> COUNT
  *                    2 -> SUM
  * @param void arg:   type==2 时为求和项 (char *)
  */
int new_msg_query_number(struct new_msg_handle *handle, int type, void *arg, char *table, char *where) {
	char *sql;
	int size, count;
	sqlite3_stmt  *stmt=NULL;
	char *fields=NULL;
	
	size=100+strlen(table);
	if (NULL!=where)
		size += strlen(where);
	if (NULL!=arg) {
		fields=(char *)arg;
		size += strlen(fields);
	}
	
	sql=(char *)malloc(size);
	if (!sql)
		return -1;
	bzero(sql, size);
	
	switch(type) {
		case 1:
			sprintf(sql, "SELECT COUNT(*) FROM [%s] ", table);
			break;
		case 2:
			if (!fields || !fields[0]) {
				free(sql);
				return -3;
			}
			sprintf(sql, "SELECT SUM(%s) FROM [%s] ", fields, table);
			break;
		default:
			free(sql);
			return -2;
	}
	
	if (NULL!=where) {
		strcat(sql, " WHERE ");
		strcat(sql, where);
	}
		
	if (SQLITE_OK != sqlite3_prepare(handle->db, sql, strlen(sql), &stmt, NULL)) {
		if (stmt)
			sqlite3_finalize(stmt);
		free(sql);
		return -4;
	}
	
	if (SQLITE_ROW == sqlite3_step(stmt)) 
		count=sqlite3_column_int(stmt, 0);
	else
		count=0;
	
	sqlite3_finalize(stmt);
	free(sql);
	
	return count;
}

int new_msg_count(struct new_msg_handle *handle, char *table, char *where) {
	return new_msg_query_number(handle, 1, NULL, table, where);
}

int new_msg_sum(struct new_msg_handle *handle, char *fields, char *table, char *where) {
	return new_msg_query_number(handle, 2, fields, table, where);
}

int new_msg_delete(struct new_msg_handle *handle, char *table, char *where) {
	char *sql;
	int size, ret;
	char *errmsg=NULL; 
	
	size=50+strlen(table);
	if (NULL!=where)
		size += strlen(where);
	
	sql=(char *)malloc(size);
	if (!sql)
		return -1;
	bzero(sql, size);
	if (NULL==where)
		sprintf(sql, "DELETE FROM [%s];", table);
	else
		sprintf(sql, "DELETE FROM [%s] WHERE %s;", table, where);
		
	if (SQLITE_OK==sqlite3_exec(handle->db, sql, NULL, NULL, &errmsg)) 
		ret=sqlite3_changes(handle->db);
	else
		ret=-2;
	
	free(sql);
	return ret;
}

int new_msg_fill_msg(struct new_msg_info *msg, sqlite3_stmt  *stmt, int offset) {
	msg->time=(time_t) sqlite3_column_int64(stmt, offset);
	msg->host=(unsigned long) sqlite3_column_int64(stmt, offset+1);
	strncpy(msg->from, (char *)sqlite3_column_text(stmt, offset+2), NEW_MSG_FROM_LEN+1);
	msg->size=(unsigned int)sqlite3_column_int(stmt, offset+4);
	
	if (msg->size<=0) 
		return -1;
	if (NULL==msg->msg) {
		msg->msg=(char *)malloc(msg->size+2);
		if (!msg->msg)
			return -2;
	}
	
	bzero(msg->msg, msg->size+2);
	strncpy(msg->msg, (char *)sqlite3_column_text(stmt, offset+3), msg->size+1);
	msg->msg[msg->size]=0;
	return 0;
}

int new_msg_fill_attachment(struct new_msg_attachment *attachment, sqlite3_stmt  *stmt, int offset) {
	strncpy(attachment->type, (char *)sqlite3_column_text(stmt, offset), NEW_MSG_ATTACHMENT_TYPE_LEN+1);
	attachment->size=(unsigned int)sqlite3_column_int(stmt, offset+1);
	strncpy(attachment->name, (char *)sqlite3_column_text(stmt, offset+2), NEW_MSG_ATTACHMENT_NAME_LEN+1);
	
	return 0;
}

int new_msg_fill_user(struct new_msg_user *info, sqlite3_stmt  *stmt) {
	info->id=(long)sqlite3_column_int64(stmt, 0);
	info->msg_id=(long)sqlite3_column_int64(stmt, 1);
	strncpy(info->user, (char *)sqlite3_column_text(stmt, 2), IDLEN+1);
	
	if (new_msg_fill_msg(&info->msg, stmt, 3)<0)
		return -1;
		
	info->count=(unsigned int)sqlite3_column_int64(stmt, 8);
	info->flag=(unsigned long) sqlite3_column_int64(stmt, 9);
	
	return 0;
}

int new_msg_fill_message(struct new_msg_message *message, sqlite3_stmt  *stmt) {
	message->id=(long) sqlite3_column_int64(stmt, 0);
	strncpy(message->user, (char *)sqlite3_column_text(stmt, 1), IDLEN+1);
	
	if (new_msg_fill_msg(&message->msg, stmt, 2)<0)
		return -1;
	
	new_msg_fill_attachment(&message->attachment, stmt, 7);
	message->flag=(unsigned long) sqlite3_column_int64(stmt, 10);
	
	return 0;
}

int new_msg_open(struct new_msg_handle *handle) {
	struct userec *user;
	struct stat st;
	char path[PATHLEN];
	
	if (handle->flag&NEW_MSG_HANDLE_OK)
		return 0;
	
	if (!handle->user[0])
		return -1;
	if (!getuser(handle->user, &user))
		return -1;
	
	strncpy(handle->user, user->userid, IDLEN+1);
	sethomefile(path, handle->user, NEW_MSG_DB);
	
	if (stat(path, &st)<0 && f_cp(NEW_MSG_INIT_DB, path, 0)!=0)
		return -2;
		
	if (SQLITE_OK!=sqlite3_open(path, &handle->db))
		return -3;
		
	handle->flag |= NEW_MSG_HANDLE_OK;	
		
	return 0;
}

int new_msg_close(struct new_msg_handle *handle) {
	if (NULL != handle->db && SQLITE_OK!=sqlite3_close(handle->db))
		return -1;
	
	handle->flag &= ~NEW_MSG_HANDLE_OK;
	return 0;
}

int new_msg_get_capacity(struct userec *user) {
	if (HAS_PERM(user, PERM_SYSOP))
		return 0;
		
	return NEW_MSG_CAPACITY;
}
/**
  * 检查发送权限
  * @return 0: 没有问题
  *        -1: 找不到发件人
  *        -2: 找不到收件人
  *        -3: 发件人、收件人为同一用户
  *        -4: 发件人封禁mail权限
  *        -5: 发件人没有通过注册
  *        -6: 收件人自杀或者封禁mail权限
  *        -7: 收件人拒收   
  *        -8: 发件人超容， 短信息容量为 new_msg_get_capacity(from)
  *        -9: 收件人超容， 短信息容量为 new_msg_get_capacity(to)
  *        
  */
int new_msg_check(struct new_msg_handle *sender, struct new_msg_handle *incept) {
	struct userec *from;
	struct userec *to;
	int size, used;
	
	if (!getuser(sender->user, &from))
		return -1;
	if (!getuser(incept->user, &to))
		return -2;
		
	if (!strcasecmp(from->userid, to->userid))
		return -3;
		
	if (HAS_PERM(from, PERM_DENYMAIL))
		return -4;	
	if (!HAS_PERM(from, PERM_LOGINOK))
		return -5;
		
	if (HAS_PERM(from, PERM_SYSOP))
		return 0;
		
	if (to->userlevel & PERM_SUICIDE)
		return -6;
	if (!(to->userlevel & PERM_BASIC))
		return -6;
	if (!canIsend2(from, to->userid))
		return -7;
	
	size=new_msg_get_capacity(from);
	if (size!=0) {
		used=new_msg_get_size(sender);
		if (used >= size)
			return -8;
	}
	
	size=new_msg_get_capacity(to);
	if (size!=0) {
		used=new_msg_get_size(incept);
		if (used >= size)
			return -9;
	}
	
	return 0;
}
int new_msg_free_info(struct new_msg_info *info) {
	if (NULL!=info->msg)
		free(info->msg);
	return 0;
}
int new_msg_free_attachment(struct new_msg_attachment *attachment) {
	if (NULL!=attachment->body)
		free(attachment->body);
	return 0;
}
int new_msg_free(struct new_msg_message *message) {
	if (message->flag&NEW_MSG_MESSAGE_ATTACHMENT) 
		new_msg_free_attachment(&(message->attachment));
	new_msg_free_info(&(message->msg));
	return 0;
}

int new_msg_user_free(struct new_msg_user *info) {
	new_msg_free_info(&(info->msg));
	return 0;
}

long new_msg_user_info(struct new_msg_handle *handle, char *user_id, struct new_msg_user *info) {
	char sql[300];
	sqlite3_stmt  *stmt=NULL;
	long id;
	
	if (NULL==info) {
		sprintf(sql, "SELECT [id] FROM [%s] WHERE [user]='%s' LIMIT 1;",
			NEW_MSG_TABLE_USER,
			user_id
		);
	} else {
		sprintf(sql, "SELECT %s FROM [%s] WHERE [user]='%s' LIMIT 1",
			NEW_MSG_SQL_SELECT_USER_FULL,
			NEW_MSG_TABLE_USER,
			user_id
		);
	}
	
	if (SQLITE_OK != sqlite3_prepare(handle->db, sql, strlen(sql), &stmt, NULL)) {
		if (stmt)
			sqlite3_finalize(stmt);
		
		return -1;
	}
	
	if (SQLITE_ROW == sqlite3_step(stmt)) {
		id=(long) sqlite3_column_int64(stmt, 0);
		if (NULL!=info && new_msg_fill_user(info, stmt)<0) {
			sqlite3_finalize(stmt);
			return -2;
		}
	} else {
		id=0;
	}
	sqlite3_finalize(stmt);
	
	return id;
}

int new_msg_get_user_messages(struct new_msg_handle *handle, char *user_id) {
	char buf[STRLEN];
	
	sprintf(buf, " [user]='%s' ", user_id);
	return new_msg_count(handle, NEW_MSG_TABLE_MESSAGE, buf);
}

int new_msg_update_user(struct new_msg_handle *handle, char *user_id, struct new_msg_message *record) {
	struct new_msg_message *message;
	struct new_msg_message query;
	int size, count, load;
	long id;
	char *sql;
	sqlite3_stmt  *stmt=NULL;
	
	if (NULL==record || record->id<=0 || strcmp(user_id, record->user)!=0) {
		id=new_msg_last_user_message(handle, user_id, &query);
		load=1;
		if (id<0) {
			new_msg_free(&query);
			return -1;
		}
		if (id==0) {
			new_msg_free(&query);
			message=NULL;
		} else {
			message=&query;
		}
	} else {
		message=record;
		load=0;
	}

	id=new_msg_user_info(handle, user_id, NULL);
	if (id<0) {
		if (load) new_msg_free(&query);
		return -2;
	}
	
	count=new_msg_get_user_messages(handle, user_id);
	if (count<0) {
		if (load) new_msg_free(&query);
		return -3;
	}
	
	size=1024;
	if (NULL!=message)
		size+=(&(message->msg))->size*2;
		
	sql=(char *)malloc(size);
	
	if (!sql) {
		if (load) new_msg_free(&query);
		return -4;
	}
	
	bzero(sql, size);
	
	if (count>0 && NULL!=message && id==0) {
		// 有message记录，无user记录
		sprintf(sql, "INSERT INTO [%s] ([msg_id], [user], [time], [host], [from], [msg], [msg_size], [count], [flag]) VALUES (%ld, '%s', datetime(%lu, 'unixepoch', 'localtime'), %lu, ?, ?, %u, %u, %lu);",
			NEW_MSG_TABLE_USER,
			message->id, 
			message->user,
			(&(message->msg))->time,
			(&(message->msg))->host,
			(&(message->msg))->size,
			count,
			message->flag
		);
	} else if (count>0 && NULL!=message && id>0) {
		// 有message记录和user记录
		sprintf(sql, "UPDATE [%s] SET [msg_id]=%ld, [user]='%s', [time]=datetime(%lu, 'unixepoch', 'localtime'), [host]=%lu, [from]=?, [msg]=?, [msg_size]=%u, [count]=%u, [flag]=%lu WHERE [id]=%ld LIMIT 1;",
			NEW_MSG_TABLE_USER,
			message->id, 
			message->user,
			(&(message->msg))->time,
			(&(message->msg))->host,
			(&(message->msg))->size,
			count,
			message->flag,
			id
		);
	} else if ((count==0 || NULL==message) && id>0) {
		// 无message记录，有user记录
		sprintf(sql, "DELETE FROM [%s] WHERE [id]=%ld LIMIT 1;", 
			NEW_MSG_TABLE_USER,
			id
		);
	} else {
		// 会出现么?
		free(sql);
		if (load) new_msg_free(&query);
		return -6;
	}
	
	if (SQLITE_OK != sqlite3_prepare(handle->db, sql, size, &stmt, NULL)) {
		if (stmt)
			sqlite3_finalize(stmt);
		free(sql);
		if (load) new_msg_free(&query);
		return -7;
	}
	
	if (count>0 && NULL!=message) {
		// 有message记录
		sqlite3_bind_text(stmt, 1, (&(message->msg))->from, strlen((&(message->msg))->from), NULL);
		sqlite3_bind_text(stmt, 2, (&(message->msg))->msg, strlen((&(message->msg))->msg), NULL);
	}
	
	if (SQLITE_DONE != sqlite3_step(stmt)) {
		sqlite3_finalize(stmt);
		free(sql);
		if (load) new_msg_free(&query);
		return -8;
	}
	
	sqlite3_finalize(stmt);
	free(sql);
	if (load) new_msg_free(&query);
	
	return 0;
}

long new_msg_last_user_message(struct new_msg_handle *handle, char *user_id, struct new_msg_message *message) {
	char sql[300];
	sqlite3_stmt  *stmt=NULL;
	long id;
	
	if (NULL==message) {
		sprintf(sql, "SELECT [id] FROM [%s] WHERE [user]='%s' ORDER BY [time] DESC LIMIT 1;",
			NEW_MSG_TABLE_MESSAGE,
			user_id
		);
	} else {
		sprintf(sql, "SELECT %s FROM [%s] WHERE [user]='%s' ORDER BY [time] DESC LIMIT 1",
			NEW_MSG_SQL_SELECT_MESSAGE_FULL,
			NEW_MSG_TABLE_MESSAGE,
			user_id
		);
	}
	
	if (SQLITE_OK != sqlite3_prepare(handle->db, sql, strlen(sql), &stmt, NULL)) {
		if (stmt)
			sqlite3_finalize(stmt);
		
		return -1;
	}
	
	if (SQLITE_ROW == sqlite3_step(stmt)) {
		id=(long) sqlite3_column_int64(stmt, 0);
		if (NULL!=message && new_msg_fill_message(message, stmt)<0) {
			sqlite3_finalize(stmt);
			return -2;
		}
	} else {
		id=0;
	}
	sqlite3_finalize(stmt);
	
	return id;
}

int new_msg_create(struct new_msg_handle *handle, struct new_msg_message *message) {
	char *sql;
	int size;
	sqlite3_stmt  *stmt=NULL;
	
	message->id=0;
	size=1024+(&(message->msg))->size*2+(&(message->attachment))->size*2;
	sql=(char *)malloc(size);
	if (!sql)
		return -1;
	bzero(sql, size);
	
	sprintf(sql, "INSERT INTO [%s] ([user], [time], [host], [from], [msg], [msg_size], [attachment_type], [attachment_size], [attachment_name], [attachment_body], [flag]) VALUES ('%s', datetime(%lu, 'unixepoch', 'localtime'), '%lu', ?, ?, %u, ?, %u, ?, ?, %lu);", 
		NEW_MSG_TABLE_MESSAGE,
		message->user, 
		(&(message->msg))->time,
		(&(message->msg))->host,
		(&(message->msg))->size,
		(&(message->attachment))->size,
		message->flag
	);
	
	if (SQLITE_OK != sqlite3_prepare(handle->db, sql, size, &stmt, NULL)) {
		if (stmt)
			sqlite3_finalize(stmt);
		free(sql);
		
		return -2;
	}
	
	sqlite3_bind_text(stmt, 1, (&(message->msg))->from, strlen((&(message->msg))->from), NULL);
	sqlite3_bind_text(stmt, 2, (&(message->msg))->msg, strlen((&(message->msg))->msg), NULL);
	if (message->flag&NEW_MSG_MESSAGE_ATTACHMENT) {
		sqlite3_bind_text(stmt, 3, (&(message->attachment))->type, strlen((&(message->attachment))->type), NULL);
		sqlite3_bind_text(stmt, 4, (&(message->attachment))->name, strlen((&(message->attachment))->name), NULL);
		sqlite3_bind_blob(stmt, 5, (&(message->attachment))->body, (&(message->attachment))->size, NULL);
	} else {
		sqlite3_bind_null(stmt, 3);
		sqlite3_bind_null(stmt, 4);
		sqlite3_bind_null(stmt, 5);
	}
	
	if (SQLITE_DONE != sqlite3_step(stmt)) {
		sqlite3_finalize(stmt);
		free(sql);
		
		return -3;
	}
	message->id=(long)sqlite3_last_insert_rowid(handle->db);
	sqlite3_finalize(stmt);
	free(sql);
	
	if (new_msg_update_user(handle, message->user, message)<0)
		return -4;
	
	return 0;
}

/**
  * 发送一条短消息，此函数仅进行发送操作，未检查权限和变量初始化
  * @return  0: 成功
  *         -1: sender未初始化
  *         -2: incept未初始化
  *         -3: msg->from 未设定，该参数反应发送的方式，如Telnet/nForum/Android等
  *         -4: msg->msg 为空，即空消息
  *         -5: msg->size 过长，即短消息过长， 最大长度为 NEW_MSG_MAX_SIZE
  *         -6: attachment->type 为空，即缺少附件的MIME类型
  *         -7: attachment->type 过长，即附件的MIME过长，最大长度 NEW_MSG_ATTACHMENT_TYPE_LEN
  *         -8: attachment->name 为空，即缺少附件的名称
  *         -9: attachment->name 过长，即附件文件名过长, 最大长度 NEW_MSG_ATTACHMENT_NAME_LEN
  *         -10: attachment->body 为空，即附件内容为空
  *         -11: attachment->size 过大，即附件过大，最大文件大小为 NEW_MSG_ATTACHMENT_MAX_SIZE
  *         -12: 发送失败，接收方存储信息时失败
  *         -13: 发送失败，发送方存储信息时失败，此时接收方能接受信息
  *         -14: 系统错误，内存不足?
  */
int new_msg_send(struct new_msg_handle *sender, struct new_msg_handle *incept, struct new_msg_info *msg, struct new_msg_attachment *attachment) {
	struct new_msg_message message;
	int flag;
	
	if (!(sender->flag&NEW_MSG_HANDLE_OK))
		return -1;
	if (!(incept->flag&NEW_MSG_HANDLE_OK))
		return -2;
	
	if (!msg->time)
		msg->time=time(NULL);
	if (!msg->host)
		msg->host=new_msg_ip2long(getSession()->fromhost);
	if (!msg->from)
		return -3;
	if (!msg->msg)
		return -4;
	msg->size=strlen(msg->msg);
	if (msg->size > NEW_MSG_MAX_SIZE || msg->size <= 0)
		return -5;
		
	flag=0;
	if (NULL!=attachment) {
		flag |= NEW_MSG_MESSAGE_ATTACHMENT;
		
		if (!attachment->type)
			return -6;
		if (strlen(attachment->type)>NEW_MSG_ATTACHMENT_TYPE_LEN)
			return -7;
		if (!attachment->name)
			return -8;
		if (strlen(attachment->name)>NEW_MSG_ATTACHMENT_NAME_LEN)
			return -9;
		if (!attachment->body || attachment->size <= 0)
			return -10;
		if (attachment->size > NEW_MSG_ATTACHMENT_MAX_SIZE)
			return -11;
			
		if (strncmp(attachment->type, "image/", strlen("image/"))==0)
			flag |= NEW_MSG_MESSAGE_IMAGE;
	}
	
	/* 初始化收件人信息 */
	strncpy(message.user, sender->user, IDLEN+1);
	message.msg.time=msg->time;
	message.msg.host=msg->host;
	strncpy(message.msg.from, msg->from, NEW_MSG_FROM_LEN+1);
	message.msg.size=msg->size;
	message.msg.msg=(char *)malloc(message.msg.size+2);
	if (!message.msg.msg)
		return -14;
	bzero(message.msg.msg, message.msg.size+2);
	strncpy(message.msg.msg, msg->msg, message.msg.size+1);
	message.flag=flag;
	
	if (message.flag&NEW_MSG_MESSAGE_ATTACHMENT) {
		strncpy(message.attachment.type, attachment->type, NEW_MSG_ATTACHMENT_TYPE_LEN+1);
		message.attachment.size=attachment->size;
		strncpy(message.attachment.name, attachment->name, NEW_MSG_ATTACHMENT_NAME_LEN+1);
		message.attachment.body=(char *)malloc(attachment->size+1);
		if (!message.attachment.body) {
			new_msg_free(&message);
			return -14;
		}
		bzero(message.attachment.body, attachment->size+1);
		memcpy(message.attachment.body, attachment->body, attachment->size);
	} else {
		message.attachment.size=0;
		message.attachment.name[0]=0;
	}
	
	if (new_msg_create(incept, &message)<0) {
		new_msg_free(&message);
		return -12;
	}
	
	message.flag|=NEW_MSG_MESSAGE_SEND;
	strncpy(message.user, incept->user, IDLEN+1);
	if (new_msg_create(sender, &message)<0) {
		new_msg_free(&message);
		return -13;
	}
		
	setmailcheck(incept->user);
	newbbslog(BBSLOG_USER, "sent new message to '%s' (size: %d, attachment: [%d]%s)", incept->user, message.msg.size, message.attachment.size, message.attachment.name);	
	
	new_msg_free(&message);
	return 0;
}

int new_msg_get_users(struct new_msg_handle *handle) {
	return new_msg_count(handle, NEW_MSG_TABLE_USER, NULL);
}

int new_msg_load_users(struct new_msg_handle *handle, int start, int count, struct new_msg_user *users) {
	char sql[300];
	sqlite3_stmt  *stmt=NULL;
	int i;
	
	if (NULL==users)
		return -1;
	
	sprintf(sql, "SELECT %s FROM [%s] ORDER BY [time] DESC LIMIT %d, %d;",
		NEW_MSG_SQL_SELECT_USER_FULL,
		NEW_MSG_TABLE_USER,
		start,
		count
	);
	
	if (SQLITE_OK != sqlite3_prepare(handle->db, sql, strlen(sql), &stmt, NULL)) {
		if (stmt)
			sqlite3_finalize(stmt);
		
		return -2;
	}
	
	i=0;
	while (SQLITE_ROW == sqlite3_step(stmt)) {
		if (new_msg_fill_user(&(users[i]), stmt)<0) {
			sqlite3_finalize(stmt);
			return -3;
		}
		i++;
	}
	sqlite3_finalize(stmt);
	
	return i;
}

int new_msg_load_user_messages(struct new_msg_handle *handle, char *user_id, int start, int count, struct new_msg_message *messages) {
	char sql[300];
	sqlite3_stmt  *stmt=NULL;
	int i;
	
	if (NULL==messages)
		return -1;
	
	sprintf(sql, "SELECT %s FROM [%s] WHERE [user]='%s' ORDER BY [time] DESC LIMIT %d, %d;",
		NEW_MSG_SQL_SELECT_MESSAGE_FULL,
		NEW_MSG_TABLE_MESSAGE,
		user_id,
		start,
		count
	);
	
	if (SQLITE_OK != sqlite3_prepare(handle->db, sql, strlen(sql), &stmt, NULL)) {
		if (stmt)
			sqlite3_finalize(stmt);
		
		return -2;
	}
	
	i=0;
	while (SQLITE_ROW == sqlite3_step(stmt)) {
		if (new_msg_fill_message(&(messages[i]), stmt)<0) {
			sqlite3_finalize(stmt);
			return -3;
		}
		i++;
	}
	sqlite3_finalize(stmt);
	
	return i;
}

long new_msg_get_message(struct new_msg_handle *handle, long id, struct new_msg_message *message) {
	char sql[300];
	sqlite3_stmt  *stmt=NULL;
	long ret;
	
	if (NULL==message)
		return -1;
	if (id<=0)
		return -2;
	
	sprintf(sql, "SELECT %s FROM [%s] WHERE [id]=%ld LIMIT 1;",
		NEW_MSG_SQL_SELECT_MESSAGE_FULL,
		NEW_MSG_TABLE_MESSAGE,
		id
	);
	
	if (SQLITE_OK != sqlite3_prepare(handle->db, sql, strlen(sql), &stmt, NULL)) {
		if (stmt)
			sqlite3_finalize(stmt);
		
		return -3;
	}
	
	if (SQLITE_ROW == sqlite3_step(stmt)) {
		if (new_msg_fill_message(message, stmt)<0) {
			sqlite3_finalize(stmt);
			return -4;
		}
		ret=message->id;
	} else {
		ret=0;
	}
	sqlite3_finalize(stmt);
	
	return ret;
}

int new_msg_delete_message(struct new_msg_handle *handle, struct new_msg_message *message) {
	char buf[100];
	int ret;
	
	if (NULL==message || message->id<=0 || !message->user[0])
		return -1;
	
	sprintf(buf, " [id]=%ld ", message->id);
	ret=new_msg_delete(handle, NEW_MSG_TABLE_MESSAGE, buf);
	if (ret<0)
		return -2;
	if (ret>0 && new_msg_update_user(handle, message->user, NULL)<0)
		return -3;
	return ret;
}

int new_msg_remove_user_messages(struct new_msg_handle *handle, char *user_id) {
	char buf[100];
	
	if (NULL==user_id || !user_id[0])
		return -1;
	sprintf(buf, " [user]='%s' ", user_id);
	if (new_msg_delete(handle, NEW_MSG_TABLE_MESSAGE, buf)<0)
		return -2;
	if (new_msg_delete(handle, NEW_MSG_TABLE_USER, buf)<0)
		return -3;
	return 0;
}

int new_msg_get_size(struct new_msg_handle *handle) {
	return new_msg_sum(handle, "[msg_size]+[attachment_size]", NEW_MSG_TABLE_MESSAGE, NULL);
}

int new_msg_read(struct new_msg_handle *handle, struct new_msg_user *info) {
	char sql[300];
	char *errmsg=NULL; 
	
	if (info->flag&NEW_MSG_MESSAGE_READ)
		return 0;
	
	sprintf(sql, "UPDATE [%s] SET [flag]=[flag]|%d WHERE [user]='%s' LIMIT 1;",
		NEW_MSG_TABLE_USER,
		NEW_MSG_MESSAGE_READ,
		info->user
	);	
	sqlite3_exec(handle->db, sql, NULL, NULL, &errmsg);
	
	sprintf(sql, "UPDATE [%s] SET [flag]=[flag]|%d WHERE [user]='%s' AND [flag]&%d=0;",
		NEW_MSG_TABLE_MESSAGE,
		NEW_MSG_MESSAGE_READ,
		info->user,
		NEW_MSG_MESSAGE_READ
	);
	sqlite3_exec(handle->db, sql, NULL, NULL, &errmsg);
	
	return 0;
}
#undef NEW_MSG_SQL_SELECT_MESSAGE_FULL
#undef NEW_MSG_SQL_SELECT_USER_FULL

#endif /* NEW_MSG_LIB */
#endif /* ENABLE_NEW_MSG */
