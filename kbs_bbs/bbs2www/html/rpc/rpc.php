<?php
// $Id$
/**
 * Platform XML-RPC Service
 *
 * windinsn<xyhu@vip.163.com>
 * 
 */
 
define ('_IN_SYSTEM_', 1);

require_once ('XML_RPC.php');
require_once ('Server.php');
require_once ('smth.rpc.php');
require_once ('gettext.php');

if (isset ($_GET['module_id']))
    $mid = intval ($_GET['module_id']);
else if (isset ($_POST['module_id']))
    $mid = intval ($_POST['module_id']);
else {
    echo _("Unknown module");
    exit;    
}    

if (!$GLOBAL_MODULES->isMidExist ($mid)) {
    echo _("Unknown module");
    exit;    
}

$currentModule = &$GLOBAL_MODULES->mList[$mid];

if (strcmp ($currentModule->innerhost, get_remote_host ())) {
    echo _("Unknown Address");
    exit;    
}

if ($currentModule->isDisabled ())  {
    echo _("Disabled Module");
    exit;
}

//  HTTP Basic authorization.
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) 
    || strcmp ($_SERVER['PHP_AUTH_USER'],$currentModule->xmlrpcuser) 
    || strcmp ($_SERVER['PHP_AUTH_PW'],$currentModule->xmlrpcpass)) {
    header('WWW-Authenticate: Basic realm='._("MTech Campus OA System XML-RPC Auth"));
    header('HTTP/1.0 401 Unauthorized');
    echo _("Auth failded,please check your user id and password");
    exit;
}   //end if


/*************************************************************************************************
 ** XML RPC Service 
 ************************************************************************************************/
//#######################################
//## int user.getUser ()               ##
//#######################################
$XMLRPC_SERVER_get_user_SIG = array (array ($XML_RPC_Int, $XML_RPC_String));
$XMLRPC_SERVER_get_user_DOC = '
user.getUser ()
��ȡ��ƽ̨ĳ�û��ĺ�������
����Ϊ���û���ID
�����û�����
';

function XMLRPC_SERVER_get_user_FUNC ($params) {
    global $currentModule, $GLOBAL_CONFIG;
    global $XML_RPC_erruser;
    
    $user_id = $params->getParam (0);
    if (!$user_id) 
        return new XML_RPC_Response(new XML_RPC_Value(-2, 'int'));
    
    $user_id = $user_id->scalarval();
    
    $lookupuser = array ();
    if (!bbs_getuser ($user_id, $lookupuser))
        return new XML_RPC_Response(new XML_RPC_Value(-1, 'int'));
        
    $args = array (
            'uid'=> new XML_RPC_Value ($lookupuser['index']),
            'id'=> new XML_RPC_Value ($lookupuser['userid']),
            'no'=> new XML_RPC_Value ($lookupuser['index']),
            'name'=> new XML_RPC_Value ($lookupuser['username']),
            'type'=> new XML_RPC_Value (0),
            'gender'=> new XML_RPC_Value (0)
            );
    
    return new XML_RPC_Response(new XML_RPC_Value($args, "struct"));
}
 
//#######################################
//## int user.loginFromModule ()       ##
//#######################################
$XMLRPC_SERVER_login_from_module_SIG = array(array($XML_RPC_Int, $XML_RPC_String, $XML_RPC_String, $XML_RPC_String));
$XMLRPC_SERVER_login_from_module_DOC = '
<pre>
int user.loginFromModule (struct $loginParams)
�û���ģ���½����
���� $loginParams �ǹ���������

[user_id] string �û���
[user_password] string �û�����
[user_host] string �û�IP��ַ

����
* 0 ��½�ɹ�
-1 ϵͳ���ڷǵ�½״̬
* -2 �û�������
* -3 �û�û�и�ģ���ʹ��Ȩ��
-4 �ʻ���ͣ��
-5 IP��ַ����
* -6 �������
* -7 ��������
1  �û���Ҫ��ƽ̨��������
2  �û���Ҫȱ��ע����Ϣ
3  �û��д���ĵ�½��¼
* 4  ����Ա��¼
</pre>
';
function XMLRPC_SERVER_login_from_module_FUNC ($params) {
    GLOBAL $currentModule,$GLOBAL_CONFIG;
    GLOBAL $XML_RPC_erruser;

    $userIdVal = $params->getParam (0);
    if (!$userIdVal) 
        return new XML_RPC_Response(new XML_RPC_Value(-7, 'int'));
    else {
        $user_id = $userIdVal->scalarval();
        $user_password = $params->getParam(1)->scalarval();
        $user_host = $params->getParam(2)->scalarval();

        $account = array ();
        if (!bbs_getuser ($user_id, $account))
            return new XML_RPC_Response(new XML_RPC_Value(-2, 'int'));
        
        if (0 != bbs_checkpasswd ($user_id, $user_password)) 
            return new XML_RPC_Response(new XML_RPC_Value(-6, 'int'));
        else {
            if (!($account["userlevel"]&BBS_PERM_LOGINOK))
                return new XML_RPC_Response(new XML_RPC_Value(-3, 'int'));
            else if ($account["userlevel"]&BBS_PERM_SYSOP)
                return new XML_RPC_Response(new XML_RPC_Value(4, 'int'));
            else
                return new XML_RPC_Response(new XML_RPC_Value(0, 'int'));
        }
        
        return new XML_RPC_Response(new XML_RPC_Value(-7, 'int'));
    }
    
}

////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////
//////////
//////////           Start XML_RPC Service
//////////
////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////  

$XMLRPC_SERVICE = new XML_RPC_Server (
                                       array (
                                       'user.loginFromModule'=>array (
                                                    'function'=>'XMLRPC_SERVER_login_from_module_FUNC',
                                                    'signature'=>$XMLRPC_SERVER_login_from_module_SIG,
                                                    'docstring'=>$XMLRPC_SERVER_login_from_module_DOC      
                                                                       ),
                                       'user.getUser'=>array (
                                                    'function'=>'XMLRPC_SERVER_get_user_FUNC',
                                                    'signatrue'=>$XMLRPC_SERVER_get_user_SIG,
                                                    'docstring'=>$XMLRPC_SERVER_get_user_DOC
                                                                        )       
                                                                ));

unset ($currentModule);

?>