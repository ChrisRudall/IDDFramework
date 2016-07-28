<?php
namespace InDemandDigital\IDDFramework;
use DateTime;

use InDemandDigital\IDDFramework\Tests\Debug as Debug;

set_include_path("v2/");


class LocationMatrix {
    const googlekey = "AIzaSyBFyN77W9vpMEHvb9jVq-r91RtHxo6KVyQ";
    const expires = 30; //days until data is refreshed
    private static $templocations;
    private static $locations;
    private static $refreshcount = 0;

//PUBLIC FUNCTIONS

//CHECKS IF DATA IS UP TO DATE & UPDATES
    public function checkExpiry(){
        Debug::nicePrint("checkExpiry");
        $lastupdate = new DateTime(file_get_contents("data/location_matrix_updated.txt"));
        $expires = self::expires;
        $expiry = $lastupdate->modify("+$expires days");
        $now = new DateTime;
        if($now > $expiry){
            self::refreshLocationMatrix();
        }else{
            debug::nicePrint("Using saved data from ".$lastupdate->format("c"));

        }
        return;
    }

    public function forceRefreshLocationMatrix(){
        debug::nicePrint("Force Refresh");
        self::refreshLocationMatrix();
        debug::nicePrint("Done");
    }

    public function getJourney($from,$to){
        self::checkExpiry();
        if(!isset(self::$locations)){
            self::readLocationMatrixFromFile();
        }
        return self::$locations->$from->$to;

    }





//PRIVATE
//REFRESH DATA FUNCTIONS
    private function refreshLocationMatrix(){
        debug::nicePrint("refreshing data");
        self::iterateData();
        // debug::nicePrint(self::$locations);
        // var_dump(self::$locations);
        if(self::writeLocationMatrix()){
            self::writeUpdatedDate();
        }
        return;
}

    private function iterateData(){
        self::iterateFrom();
        return;
    }
    private function iterateFrom(){
      Debug::nicePrint("iterateFrom");
      $data = Database::query("SELECT postcode,id FROM locations");
      while($r = $data->fetch_row()){
          if($r[0] != ""){
              self::$templocations->from [] = $r;
          }
          if(count(self::$templocations->from)%9  === 0 && count(self::$templocations->from) !== 0){
              self::iterateTo();
              unset(self::$templocations->from);
          }//call
      }
      //call
      if(count(self::$templocations->from) !== 0){
          self::iterateTo();
      }
      unset(self::$templocations->from);
      return;

    }
    private function iterateTo(){
      Debug::nicePrint("iterateTo");
      $data = Database::query("SELECT postcode,id FROM locations");
      while($r = $data->fetch_row()){
          if($r[0] != ""){
              self::$templocations->to [] = $r;
          }
          if(count(self::$templocations->to)%9  === 0 && count(self::$templocations->to) !== 0){
              self::getGoogleData(self::$templocations);
              unset(self::$templocations->to);
          }//call
      }
      //call
      if(count(self::$templocations->to) !== 0){
          self::getGoogleData(self::$templocations);
      }
      unset(self::$templocations->to);
      return;
    }


    private function getGoogleData($locations){
        debug::nicePrint("getGoogleData");
        $fromlist = "";
        foreach ($locations->from as $value) {
            $fromlist = $fromlist ."|".$value[0];
        }
        $tolist = "";
        foreach ($locations->to as $value) {
            $tolist = $tolist ."|".$value[0];
        }

        $params = ['departure_time' => 'now',
                    'origins' => $fromlist,
                'destinations' => $tolist,
            'key' => self::googlekey];
        $httpparams = http_build_query($params);
        $googleurl = "https://maps.googleapis.com/maps/api/distancematrix/json?".$httpparams;
        // print_r($googleurl);
        $matrix = file_get_contents($googleurl);
        if ($matrix === FALSE){
          return  "ERROR - NO DATA RETURNED\n$googleurl";
        }else{
            self::addJsonToArray($matrix,$locations);
            return;
        }
    }

    private function addJsonToArray($matrix,$locations){
        $matrix = json_decode($matrix);
        if(!isset(self::$locations)){
            self::$locations = [];
        }
        for ($c=0; $c <= count($locations->from); $c++){
            $fromid = $locations->from[$c][1];
            for ($d=0; $d <= count($locations->to); $d++){
                $toid = $locations->to[$d][1];
                $ob = $matrix->rows[$c]->elements[$d];
                self::$locations[$fromid][$toid] = $ob;
            }
        }
        // debug::nicePrint(self::$locations);
        // var_dump(self::$locations);
        return;

    }

//WRITE DATA TO FILE FUNCTIONS
    private function writeLocationMatrix(){
        Debug::nicePrint("writeLocationMatrix");
        $matrixjson = json_encode(self::$locations);
        $dir = 'data';
        if ( !file_exists($dir) ) {
            mkdir ($dir, 0744);
        }
        return file_put_contents ($dir.'/location_matrix.json', $matrixjson);

    }
    private function writeUpdatedDate(){
        Debug::nicePrint("writeLocationUpdatedDate");
        $now = new DateTime;
        $dir = 'data';
        if ( !file_exists($dir) ) {
            mkdir ($dir, 0744);
        }
        file_put_contents ($dir.'/location_matrix_updated.txt', $now->format('c'));
        return;
    }

private function readLocationMatrixFromFile(){
    $raw = @file_get_contents("data/location_matrix.json");
    if(!$raw){
        self::$refreshcount++;
        debug::nicePrint("Could not read data. Attemping refresh ".self::$refreshcount);
        self::refreshLocationMatrix();
        self::readLocationMatrixFromFile();
        return;
    }
    Debug::nicePrint("Readng data from disk");
    self::$locations = json_decode($raw);
    return self::$locations;
}



} ?>
