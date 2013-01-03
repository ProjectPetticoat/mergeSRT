<?
// output error message if upload criteria is not met
if (!empty($message)) {
    foreach ($message as $text) {
        echo "<br>";        
        echo $text;
        exit;
    }
}
?>