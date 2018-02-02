<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/17
 * Time: 18:15
 */
function echoJSON($withStatus, $andData, $andMessage = ''){
    $data = array('code' => $withStatus, 'data' => $andData, 'msg' => $andMessage);
    $jsonstring = json_encode($data);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo $jsonstring;
}

function returnResult($code, $sql) {
    $result = find($sql);
    if(count($result) === 1) {
        $result = $result[0];
    }
    echoJSON($code, $result);
}

//作者：Linsw
//链接：https://www.jianshu.com/p/423246ba8ebc
//來源：简书
//著作权归作者所有。商业转载请联系作者获得授权，非商业转载请注明出处。

$posts = json_decode(file_get_contents('php://input', true), true);
if(@$posts['menu']) {
    $sql = 'select * from menu';
    returnResult(0, $sql);
}else if(@$posts['novels']) {
    $sql1 = "select * from book WHERE `mId` = " . $posts['novels'];
    $result1 = find($sql1)[0];
    $sql2 = "select * from indexes WHERE `bNo` =" . $result1['bNo'];
    $result2 = find($sql2);
    $result1['indexes'] = $result2;
    echoJSON(0, $result1);
}else if(@$posts['index']) {
    $sql = "select * from content WHERE `iNo` = " . $posts['index'];
    returnResult(0, $sql);
}else if(@$posts['signInData']) {
    $name = $posts['signInData']['userName'] ;
    $pwd = md5($posts['signInData']['passWord']) ;

    $sql1 = "select * from users WHERE  `Loginid` = '$name'";
    $result1 = find($sql1);

    if(count($result1) === 0) {
        echoJSON(-1, '账号不存在');
    }else {
        $pwdSql = strrev($result1[0]['Pwd']);
        if ($pwd === $pwdSql) {
            echoJSON(0, $result1[0]);
        } else {
            echoJSON(1, '错误');
        }
    }
}else if(@$posts['signUpData']) {
    $name = $posts['signUpData']['userName'];
    $pwd = $posts['signUpData']['passWord'];

    $sql1 = "select count(*) from users WHERE  `Loginid` = '$name'";
    $result1 = find($sql1, 2);

    if ($result1[0][0] == 0) {
        $pwd = strrev(md5($pwd));
        $sql2 = "insert into users(Loginid, Pwd) VALUES('$name', '$pwd') ";
        $result2 = find($sql2);
        if ($result2 == 1) {
            echoJSON(0, '成功');
        } else {
            echoJSON(-1, '失败');
        };
    } else {
        echoJSON(1, '已被注册');
    }


}

/**
 * @param $sql
 */
function find($sql, $type = 1)
{
    include_once('./mysql/MysqliHelper.php');
    $mysql = new MysqliHelper();
    $result = $mysql->Execute($sql, $type);
    return $result;
}