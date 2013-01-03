<?
// find code for merging in separate file mergecode.php
// (php code must be included before HTML output due to zip file creation!)
include("mergecode.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
    <title>Merge .SRT files</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link type="text/css" rel="stylesheet" href="mergeSRT.css">
    </head>
    <body>
        <div id="CenterBox">
            <p>
            With this tool, you can merge up to four .srt files into one.
            </p>
            <p>
            <form action="<? echo $_PHP['SELF']; ?>" method="post" enctype="multipart/form-data">
                <p>
                    Choose files to merge:
                </p> 
                <p>
                    .srt#1
                    <input name="userfile[]" type="file">
                    <br>
                    .srt#2
                    <input name="userfile[]" type="file">
                    <br>
                    .srt#3
                    <input name="userfile[]" type="file">
                    <br>
                    .srt#4
                    <input name="userfile[]" type="file">
                </p>
                <p>
                New filename:<br>
                (can be left blank)
                </p>    
                <p>
                    <input type="text" name="newfilename">
                </p>    
                <p>
                    Please note that your files must be less than 200KB each.
                </p>    
                <p>
                    <input type="submit" id="submit" name="merge" value="Merge!">
                </p>    
                <p>
                    <input type="reset" id="clear" value="Clear input">
                </p>
                <?
                // include message to the user regarding files to be merged
                include ("outputmsg.php");
                ?>
                </p>
            </form>
        </div>
    </body>
</html>