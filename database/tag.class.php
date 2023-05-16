<?php
  declare(strict_types = 1);

  class Tag {
    public int $id;
    public string $name;
    
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    static function getTags(PDO $db) {
      $stmt = $db->prepare('
        SELECT * 
        FROM Tag
      ');
      $stmt->execute();
      return $stmt->fetchAll();
    }

    static function getUserTags(PDO $db) {
      $stmt = $db->prepare('
        SELECT DISTINCT TagID, Name
        FROM Tag 
        JOIN Ticket_Tag USING(TagID)
        JOIN Ticket USING(TicketID)
        WHERE ClientID = ?
      ');
      $stmt->execute([$_SESSION['IDUSER']]);
      return $stmt->fetchAll();
    }

    static function addTag(PDO $db, $ticketId, $name) {
      $stmt = $db->prepare('
        INSERT INTO Ticket_Tag (TicketID, TagID)
        VALUES (?, (
          SELECT TagID
          FROM Tag
          WHERE Name = ?
        ))
      ');
      $stmt->execute([$ticketId, $name]);
    }

    static function removeTag(PDO $db, $ticketId, $name) {
      $stmt = $db->prepare('
        DELETE FROM Ticket_Tag
        WHERE TicketID = ? AND TagID = (
          SELECT TagID
          FROM Tag
          WHERE Name = ?
        )
      ');
      $stmt->execute([$ticketId, $name]);
    }

    static function tagExists(PDO $db, $name) {
      $stmt = $db->prepare('
        SELECT *
        FROM Tag
        WHERE Name = ?
      ');
      $stmt->execute([$name]);
      return !empty($stmt->fetch());
    }

    static function createTag(PDO $db, $name) {
      $stmt = $db->prepare('
        INSERT INTO Tag (Name)
        VALUES (?)
      ');
      $stmt->execute([$name]);
    }
  }
?>