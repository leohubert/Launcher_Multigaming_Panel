<?php
/**
 * Created by PhpStorm.
 * User: hubert_i
 * Date: 14/06/16
 * Time: 15:20
 */

header('Content-type: application/json');

$result = array("status" => 500, "message" => "Internal error");

if (isset($_POST['token']) && isset($_POST['support_id']) && isset($_POST['title']) && isset($_POST['status']) && is_numeric($_POST['support_id']) && is_numeric($_POST['status']) )
{
    $token = $_POST['token'];
    $support_id = $_POST['support_id'];
    $status = $_POST['status'];
    $title = $_POST['title'];

    $checkUser = $database->prepare('SELECT user_id FROM sessions WHERE token = :token');
    $checkUser->execute(array('token' => $token));
    $res = $checkUser->fetch();
    if ($checkUser->rowCount() != 0 && $userLevel = $database->prepare('SELECT `level`,`banned` FROM users WHERE id = :id'))
    {
        $userLevel->execute(array('id' => $res['user_id']));
        $myID = $res['user_id'];
        $res = $userLevel->fetch();
        if ($userLevel->rowCount() != 0 && (int)$res['level'] >= 6  && (int)$res['banned'] != 1)
        {
            $getSettings = $database->prepare('SELECT * FROM `support` WHERE id=:id');
            $getSettings->execute(array('id' => $support_id));
            $support = $getSettings->fetch();
            if ($getSettings->rowCount() != 0)
            {
                if ($support['assign_to'] == $myID || (int)$res['level'] >= 9)
                {
                    $updateSupport = $database->prepare('UPDATE `support` SET `status`=:status,`title`=:title,`updated_at`=:date WHERE id=:id');
                    $updateSupport->execute(array('id' => $support_id, 'status' => $status, 'title' => $title, 'date' => date('Y-m-d H:i:s')));
                    $result['status'] = 42;
                    $result['message'] = "Support saved";
                }
                else
                {
                    $result['status'] = 01;
                    $result['message'] = "Support not assigned to your account";
                }
            }
            else
            {
                $result['status'] = 40;
                $result['message'] = "Conversation doesn't exits";
            }
        }
        else
        {
            $result['status'] = 44;
            $result['message'] = "You don't have right to create this request !";
        }
    }
    else
    {
        $result['status'] = 41;
        $result['message'] = "Token invalid";
    }
}
else
{
    $result['status'] = 404;
    $result['message'] = "Arguments missing.";
}

echo json_encode($result);