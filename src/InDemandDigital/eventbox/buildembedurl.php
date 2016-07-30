<?php
 // id number of room or event
 //  type of data - either room or event
 //  display type - either
 // display types
 // artistblock - each atist with pic
 // roomblock - each room with listing, and pic of main artist
 // eventblock - each event(venue) as a block with roomname & headliner
 //  limit number of rooms to display
 //   limit artists to display
 //    show the artoist tags? =true
 //     value in px to offset the text box from the bottom
function buildEmbedURL($id,$datatype,$displaytype,$roomlimit,$artistlimit,$showtag,$textoffset,$columns){
    // if (!$showtag){
    //     $showtag = true;
    // }
    if (!$textoffset){
        $textoffset = '120';
    }
    $url = "/embed/embedevent.php?datatype=$datatype&id=$id&displaytype=$displaytype&roomlimit=$roomlimit&artistlimit=$artistlimit&showtag=$showtag&textoffset=$textoffset&columns=$columns";
    return $url;
}
?>
