<?php
  declare(strict_types = 1);

  require_once(__DIR__ . '/../utils/session.php');
  session_start();

  require_once(__DIR__ . '/../database/connection.db.php');
  $db = getDatabaseConnection();

  require_once(__DIR__ . '/../templates/common.tpl.php');
  require_once(__DIR__ . '/../templates/ticket.tpl.php');
  require_once (__DIR__ . '/../database/user.class.php');
  require_once (__DIR__ . '/../database/ticket.class.php');
  require_once(__DIR__ . '/../database/tag.class.php');

  if (!Session::isLoggedIn())
    die(header('Location: /pages/login.php'));
  if ($_SESSION['PERMISSIONS'] != 'Admin' && $_SESSION['PERMISSIONS'] != 'Agent') 
    die(header('Location: /pages/my_tickets.php'));
  drawHeader(['ticket']);
  $tickets = Ticket::getAgentTickets($db, $_SESSION['IDUSER']);
  if ($tickets == null) {echo 'No tickets found!' . '<br>';}
  else {drawTicketsList($db,$tickets);}
  drawFooter();
?>
