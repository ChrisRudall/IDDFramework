<?php
namespace InDemandDigital\IDDFramework\Entities;
use InDemandDigital\IDDFramework as IDD;

// require 'includes/config.php';

class Reservation extends Entity{

    public static function exportReservationsAsCSVForEventID($eventid){
        $sql = "SELECT
        locations.name AS locationname,
        reservation_number,
        room_type,
        booking_name,

        artists.name AS artistname,
        artists.firstname AS firstname,
        artists.lastname AS lastname,
        check_in,
        check_out,
        hotel_reservations.notes

        FROM hotel_reservations,locations,performances,artists,events,rooms
        WHERE
        locations.id=hotel_reservations.location AND
        performances.id=hotel_reservations.performance AND
        artists.id=performances.artist_id AND
        rooms.id=performances.room_id AND
        events.id=rooms.event AND
        events.id = $eventid
        ORDER BY check_in
        ";
        $rs=IDD\Database::query($sql);
        while($reservation = $rs->fetch_assoc())
        {
            $reservations[] = $reservation;
        }



        $reservations = \v1\decodeArray($reservations);

        foreach ($reservations as &$reservation) {
            // print_r($reservation);
            if ($reservation['booking_name'] == ""){
                if ($reservation['firstname'] != ""){
                    $reservation['booking_name'] = $reservation['firstname'] . " " . $reservation['lastname']. " (".$reservation['artistname'].")";
                }else{
                    $reservation['booking_name'] = $reservation['artistname'];
                }
            }
            unset($reservation['lastname']);
            unset ($reservation['firstname']);
            unset($reservation['artistname']);
        }
        // sort array
        foreach ($reservations as $key => $row) {
            $checkin[$key]  = $row['check_in'];
            $name[$key] = $row['artistname'];
        }
        array_multisort($checkin, SORT_ASC, $name, SORT_ASC,$reservations);

        // print_r($reservations);
        $filename = self::exportArrayToCSV($reservations);
        return $filename;
    }

}
?>
