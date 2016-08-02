<?php
namespace InDemandDigital\IDDFramework;

$dir    = 'tests';
$files = scandir($dir);

?>
<!DOCTYPE HTML>
<html>
<head>
    <title>IDD Framework Tests</title>
</head>
<body>
    Tests
    <ul>
        <?php
        if($files){
            foreach ($files as $file){
                echo "<li><a href='tests/$file'>$file</a></li>";
            }
        }
        ?>
    </ul>
</body>
