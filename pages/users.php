<?php
    declare(strict_types = 1);

    require_once(__DIR__ . '/../utils/session.php');
    session_start();

    require_once(__DIR__ . '/../database/connection.db.php');
    $db = getDatabaseConnection();

    require_once (__DIR__ . '/../templates/common.tpl.php');
    require_once (__DIR__ . '/../templates/ticket.tpl.php');
    require_once (__DIR__ . '/../templates/user_lists.tpl.php');
    require_once (__DIR__ . '/../database/user.class.php');
    require_once (__DIR__ . '/../database/ticket.class.php');
    require_once (__DIR__ . '/../database/tag.class.php');

    if (!Session::isLoggedIn())
        die(header('Location: /pages/login.php'));
    if ($_SESSION['PERMISSIONS'] != 'Admin' && $_SESSION['PERMISSIONS'] != 'Agent') 
        die(header('Location: /pages/my_tickets.php'));
    
    drawHeader(['users_list'], ['general', 'user']);
    drawNavBar($_SESSION['PERMISSIONS']);
    $users = User::getUsers($db);
    drawUsersPage($users);
    drawFooter();
?>
