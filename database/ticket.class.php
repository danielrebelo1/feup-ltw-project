<?php
  declare(strict_types = 1);

  class Ticket {
    public int $id;
    public string $title;
    public string $description;
    public string $status;
    public $clientId;
    public $agentId;
    public $departmentId;
    public Datetime $date;
    
    public function __construct(int $id, string $title, string $description, string $status, $clientId, $agentId, $departmentId, string $date)
    {
      $this->id = $id;
      $this->title = $title;
      $this->description = $description;
      $this->status = $status;
      $this->clientId = $clientId;
      $this->agentId = $agentId;
      $this->departmentId = $departmentId;
      $this->date = DateTime::createFromFormat('Y-m-d H:i:s', $date);
    }

    static function filterTicketsByStatus(PDO $db, $status) {
      $stmt = $db->prepare('
        SELECT DISTINCT TicketID
        FROM Ticket
        WHERE Status = ?
      ');
      $stmt->execute(array($status));
      return $stmt->fetchAll();
    }

    static function filterTicketsByTags(PDO $db, $tags) {
      $tagsPlaceholders = implode(',', array_fill(0, count($tags), '?'));
      $stmt = $db->prepare('
        SELECT DISTINCT TicketID
        FROM Ticket JOIN Ticket_Tag 
        USING(TicketID) 
        WHERE TagID IN (' . $tagsPlaceholders . ')');
      $stmt->execute($tags);
      return $stmt->fetchAll();
    }

    static function filterTicketsByDeparments(PDO $db, $departments) {
      $departmentsPlaceholders = implode(',', array_fill(0, count($departments), '?'));
      $stmt = $db->prepare('
        SELECT DISTINCT TicketID
        FROM Ticket
        WHERE DepartmentID IN (' . $departmentsPlaceholders . ')');
      $stmt->execute($departments);
      return $stmt->fetchAll();
    }

    static function getTicketsAfter(PDO $db, $dateLowerBound) {
      $stmt = $db->prepare('
        SELECT TicketID
        FROM Ticket
        WHERE Date > ?
      ');
      $stmt->execute(array($dateLowerBound));
      return $stmt->fetchAll();
    }

    static function getTicketsBefore(PDO $db, $dateUpperBound) {
      $stmt = $db->prepare('
        SELECT TicketID
        FROM Ticket
        WHERE Date < ?
      ');
      $stmt->execute(array($dateUpperBound));
      return $stmt->fetchAll();
    }

    static function getAllTickets(PDO $db, $author = null, $sort = null) {
      $query = 'SELECT * FROM Ticket';
      $params = [];
  
      if ($author !== null) {
        array_push($params, $author);
        $query .= ' WHERE ClientID = ?';
      }
  
      if ($sort !== null) {
        $query .= ' ORDER BY Date ' . $sort;
      }
  
      $stmt = $db->prepare($query);
      $stmt->execute($params);
      return $stmt->fetchAll();
    }

    static function getTickets(PDO $db, $status, $tags, $departments, $dateLowerBound, $dateUpperBound, $author = null, $sort = null) {
      $result = array_column(Ticket::getAllTickets($db, $author, $sort), 'TicketID');
      if (!empty($status))
        $result = array_intersect($result, array_column(Ticket::filterTicketsByStatus($db, $status), 'TicketID'));
      if (!empty($tags)) 
        $result = array_intersect($result, array_column(Ticket::filterTicketsByTags($db, $tags), 'TicketID'));
      if (!empty($departments))
        $result = array_intersect($result, array_column(Ticket::filterTicketsByDeparments($db, $departments), 'TicketID'));
      if (!empty($dateLowerBound))
        $result = array_intersect($result, array_column(Ticket::getTicketsAfter($db, $dateLowerBound), 'TicketID'));
      if (!empty($dateUpperBound))
        $result = array_intersect($result, array_column(Ticket::getTicketsBefore($db, $dateUpperBound), 'TicketID'));
      return $result;
    } 

    static function getTicketData(PDO $db, $ticketID) {
      $stmt = $db->prepare('
        SELECT TicketID, Title, Description, Status, ClientID, AgentID, DepartmentID, Date
        FROM Ticket
        WHERE TicketID = ?
      ');
      $stmt->execute([$ticketID]);
      $ticket = $stmt->fetch();
      if ($ticket) {
        return new Ticket(
          $ticket['TicketID'], 
          $ticket['Title'], 
          $ticket['Description'], 
          $ticket['Status'], 
          $ticket['ClientID'], 
          $ticket['AgentID'], 
          $ticket['DepartmentID'], 
          $ticket['Date']
        );
      } else return null;
    }

    static function filterTicketsByAuthor(PDO $db, $authorID) {
      $stmt = $db->prepare('
        SELECT TicketID
        FROM Ticket
        WHERE ClientID = ?
      ');
      $stmt->execute([$authorID]);
      return $stmt->fetch();
    }

    static function getTicketTags(PDO $db, $ticketID) {
      $stmt = $db->prepare('
        SELECT Name, TagID
        FROM Ticket_Tag JOIN Tag
        USING(TagID)
        WHERE TicketID = ?
        ');
      $stmt->execute([$ticketID]);
      return $stmt->fetchAll();
    }

    static function addDocument(PDO $db, int $ticketId, string $path) {
      $stmt = $db->prepare('
        INSERT INTO Ticket_Document (TicketID, Path) VALUES(?,?)
      ');
      $stmt->execute(array($ticketId,$path));
    }
    
    static function getDocuments(PDO $db, $ticketId) {
      $stmt = $db->prepare('
        SELECT Path
        FROM Ticket_Document
        WHERE TicketID = ?
      ');
      $stmt->execute(array($ticketId));
      return $stmt->fetchAll();
    }

    static function getUserTickets(PDO $db, $userID) {
      $stmt = $db->prepare('
        SELECT DISTINCT TicketID
        FROM Ticket JOIN Client ON Client.ClientID = Ticket.ClientID WHERE Client.ClientID = ?');
      $stmt->execute([$userID]);
      return $stmt->fetchAll();
    }

    static function getAgentTickets(PDO $db, $userID) {
      $stmt = $db->prepare('
        SELECT DISTINCT TicketID
        FROM Ticket JOIN Agent ON Ticket.AgentID = ?');
      $stmt->execute([$userID]);
      if ($tickets = $stmt->fetchAll()) {
        $tickets = Ticket::sortTicketsMostRecent($db, $tickets);
        return $tickets;
      } else return null;
    }

    static function sortTicketsMostRecent(PDO $db, $tickets) {
      $ticket_ids = array_column($tickets, 'TicketID');
      $ticketsPlaceholders = implode(',', $ticket_ids);
      $stmt = $db->prepare('
        SELECT TicketID
        FROM Ticket
        WHERE TicketID IN (' . $ticketsPlaceholders . ')
        ORDER BY Date DESC');
      $stmt->execute();
      return $stmt->fetchAll();
    }

    static function sortTicketsLeastRecent(PDO $db, $tickets) {
      if (empty($tickets)) return null;
      $ticket_ids = array_column($tickets, 'TicketID');
      $ticketsPlaceholders = implode(',', $ticket_ids);
      $stmt = $db->prepare('
        SELECT TicketID
        FROM Ticket
        WHERE TicketID IN (' . $ticketsPlaceholders . ')
        ORDER BY Date ASC');
      $stmt->execute();
      return $tickets = $stmt->fetchAll();
    }

    static function addTicket(PDO $db, string $ticket_title, string $ticket_description, string $status, int $ClientID, $departmentID, string $now) : string {
      $ticket_title = htmlspecialchars($ticket_title);
      $ticket_description = htmlspecialchars($ticket_description);
      $stmt = $db->prepare('
        INSERT INTO Ticket (Title, Description, Status, ClientID, DepartmentID, Date)
        VALUES (?, ?, ?, ?, ?, ?)');
      $stmt->execute([$ticket_title, $ticket_description, $status, $ClientID, $departmentID, $now]);
      return $db->lastInsertId();
    }

    static function deleteTicket(PDO $db, int $TicketID) {
      $stmt = $db->prepare('
        DELETE FROM Ticket_Document
        WHERE TicketID = ?');
      $stmt->execute([$TicketID]);
      $stmt = $db->prepare('
        DELETE FROM Ticket
        WHERE TicketID = ?');
      $stmt->execute([$TicketID]);
    }

    static function setStatus(PDO $db, string $status, int $id) {
      $stmt = $db->prepare('
        UPDATE Ticket
        SET Status = ?
        WHERE TicketID = ?');
      $stmt->execute([$status, $id]);
    }

    static function getComments(PDO $db, int $id) {
      $stmt = $db->prepare('
        SELECT CommentID, Comment, Date, Username
        FROM Ticket_Comment JOIN Client
        USING(ClientID)
        WHERE TicketID = ?
        ORDER BY Date ASC
      ');
      $stmt->execute([$id]);
      return $stmt->fetchAll();
    }

    static function getUpdates(PDO $db, int $id) {
      $stmt = $db->prepare('
        SELECT *
        FROM Ticket_Update
        WHERE TicketID = ?
        ORDER BY Date ASC
      ');
      $stmt->execute([$id]);
      return $stmt->fetchAll();
    }

    static function updateTicketTitle(PDO $db, int $id, string $title) {
      $stmt = $db->prepare('
        UPDATE Ticket
        SET Title = ?
        WHERE TicketID = ?');
      $stmt->execute([$title, $id]);
    }

    static function updateTicketDescription(PDO $db, int $id, string $description) {
      $stmt = $db->prepare('
        UPDATE Ticket
        SET Description = ?
        WHERE TicketID = ?');
      $stmt->execute([$description, $id]);
    }

    static function updateTicketStatus(PDO $db, int $id, string $status) {
      $stmt = $db->prepare('
        UPDATE Ticket
        SET Status = ?
        WHERE TicketID = ?');
      $stmt->execute([$status, $id]);
    }

    static function updateTicketAgent(PDO $db, int $id, $agentID) {
      $stmt = $db->prepare('
        UPDATE Ticket
        SET AgentID = ?
        WHERE TicketID = ?');
      $stmt->execute([$agentID, $id]);
    }

    static function updateTicketDepartment(PDO $db, int $id, $departmentID) {
      $stmt = $db->prepare('
        UPDATE Ticket
        SET DepartmentID = ?
        WHERE TicketID = ?');
      $stmt->execute([$departmentID, $id]);
    }

    static function addTicketTag(PDO $db, int $TicketID, int $TagID) {
      $stmt = $db->prepare('
        INSERT INTO Ticket_Tag (TicketID, TagID)
        VALUES (?, ?)');
      $stmt->execute([$TicketID, $TagID]);
    }

    static function changeTicketDepartment(PDO $db, int $TicketID, int $departmentID) {
      $stmt = $db->prepare('
        UPDATE Ticket
        SET DepartmentID = ?
        WHERE TicketID = ?');
      $stmt->execute([$departmentID, $TicketID]);
    }

    static function getTicketsOpenToday(PDO $db) {
      $stmt = $db->prepare("
        SELECT COUNT(*) AS OpenTicketsToday
        FROM Ticket
        WHERE Status = 'Open' AND DATE(Date) = Date('now');
      ");
      $stmt->execute();
      return $stmt->fetch();
    }

    static function getTicketsClosedToday(PDO $db){
      $stmt = $db->prepare("
        SELECT COUNT(*) AS ClosedTicketsToday FROM Ticket_Update
        WHERE Message = 'Ticket marked as Closed.' AND DATE(Date) = Date('now');
      ");
      $stmt->execute();
      return $stmt->fetch();
    }
  }
?>