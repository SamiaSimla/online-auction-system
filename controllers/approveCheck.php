<?php
    require_once(__DIR__.'/../config/functions.php');
    require_once(__DIR__.'/../models/userModel.php');
    requireAdmin();

    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    if($user_id <= 0){
        json_response(['ok'=>false, 'error'=>'Invalid user']);
    }

    if(approveSeller($user_id)){
        json_response(['ok'=>true]);
    }else{
        json_response(['ok'=>false, 'error'=>'Approve failed']);
    }
?>
