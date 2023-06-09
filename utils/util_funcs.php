<?php 

    function getUserType(PDO $db, $id) {
        $stmt = $db->prepare("
            SELECT 
            CASE 
                WHEN EXISTS (SELECT 1 FROM Admin WHERE ClientID = ?) THEN 'Admin'
                WHEN EXISTS (SELECT 1 FROM Agent WHERE ClientID = ?) THEN 'Agent'
                ELSE 'Client'
            END AS UserType
            FROM Client
            WHERE ClientID = ?;
        ");
        $stmt->execute(array($id, $id, $id));
        $userType = $stmt->fetch();
        return $userType['UserType'];
    }

    function removeOverflow($string, $maxSize) {
        if (strlen($string) > $maxSize) 
            return substr($string, 0, $maxSize - 3) . '...';
        return $string;
    }

    function timeAgo(DateTime $date) {
        $now = new DateTime('now',new DateTimeZone('Europe/Lisbon'));
        $diff = $now->diff($date);
        if ($diff->y > 0) return $diff->y . ' year' .  ($diff->y > 1 ? 's' : '') . ' ago';
        if ($diff->m > 0) return $diff->m . ' month' .  ($diff->m > 1 ? 's' : '') . ' ago';
        if ($diff->d > 0) return $diff->d . ' day' .  ($diff->d > 1 ? 's' : '') . ' ago';
        if ($diff->h > 0) return $diff->h . ' hour' .  ($diff->h > 1 ? 's' : '') . ' ago';
        if ($diff->i > 0) return $diff->i . ' minute' .  ($diff->i > 1 ? 's' : '') . ' ago';
        if ($diff->s > 0) return $diff->s . ' second' .  ($diff->s > 1 ? 's' : '') . ' ago';
        return 'just now';
    }

    function displayDate(DateTime $date) {
        return $date->format('l, j F Y H:i');
    }
?>