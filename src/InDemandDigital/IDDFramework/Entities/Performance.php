<?php
namespace InDemandDigital\IDDFramework\Entities;
use InDemandDigital\IDDFramework as IDD;
use InDemandDigital\Tests\Debug as Debug;


class Performance extends Entity{

    public function getArtist(){
        return new Artist($this->artist_id);
    }
    public function getRoom(){
        return new Room($this->room_id);
    }
    public function getEvent(){
        $room = $this->getRoom();
        return new Event($room->event);
    }

}
?>
