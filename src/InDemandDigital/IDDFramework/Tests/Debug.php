<?php
namespace InDemandDigital\IDDFramework\Tests;

class Debug{

// @param debug_level
    // 0 -> off;
    // 1 -> strings only;
    // 2 -> full;

public static $debug_level = 0;
private static $indent = 0;

    public function nicePrint($data){
        // is debug off?
        if(self::$debug_level === 0){
            return;
        }

        $data_type = gettype($data);
        if(self::$debug_level > 3){
            self::nicePrintString($data_type);
            // self::$indent++;
        }
        switch ($data_type) {
            case 'string':
                self::nicePrintString($data);
                break;
            case 'array':
                self::nicePrintArray($data);
                break;
            case 'object':
                self::nicePrintObject($data);
                break;
            case 'integer':
                self::nicePrintString($data);
                break;
            case 'boolean':
                self::nicePrintString($data);
                break;
            default:
                self::nicePrintString("Type not recognised: $data_type");
                break;
        }
        // self::$indent--;
    }

    private function nicePrintString($string){
        self::indent();
        echo $string."<br>";
        return;
    }

    private function nicePrintObject($object){
        if(self::$debug_level < 2){
            return;
        }
        self::$indent++;
        echo "<br>";
        foreach ($object as $key => $value) {
            self::nicePrint($key." => ");
            self::nicePrint($value);
        }
        self::$indent--;
        return;
    }

    private function nicePrintArray($array){
        if(self::$debug_level < 2){
            return;
        }
        self::$indent++;
        echo "<br>";
        foreach ($array as $value) {
            self::nicePrint($value);
        }
        self::$indent--;
        return;
    }

    private function indent(){
        $i = "";
        for ($c = 0; $c < self::$indent; $c++){
            $i = $i."&nbsp;&nbsp;&nbsp;";
        }
        echo $i;
    }
}
 ?>
