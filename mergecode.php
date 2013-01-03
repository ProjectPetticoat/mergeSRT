<?
$uploaded = 0;

$message = array();
$filename = array();
$filename_full = array();
$filetype = array();
$filesize = array();
$tmpfilepath = array();
$filename_moved = array();
    
$tmpname = '' .time(). '_';
$tmpdirectory = $_SERVER['DOCUMENT_ROOT']. '/mergeSRT/tmpfiles/';

unset($_SESSION['newcontents']);
unset($message);

if ($merge) {    
    
    foreach ($_FILES['userfile']['name'] as $key => $namevalue) {
        
        if ($_FILES['userfile']['error'][$key] === UPLOAD_ERR_OK) {

            // check for correct file extension (.srt)
            $extension = strrchr($namevalue, '.');
            if ($extension != '.srt') {
                $message[] = "Are you sure $namevalue is a .srt file?";
                continue;                
            }

            ++$uploaded;

            // get file-related data            
            $filename_full[] = $namevalue;
            $filename[] = substr_replace($namevalue, '', -4);
            $filetype[] = $_FILES['userfile']['type'][$key];
            $filesize[] = $_FILES['userfile']['size'][$key];
            $tmpfilepath[] = $_FILES['userfile']['tmp_name'][$key];
            
            // check if size is below 200KB                        
            if ($_FILES['userfile']['size'][$key] > 204800) {
               $message[] = "$namevalue exceeds the file size limit.";
            }

            // move files 
            $filename_moved[] = $tmpdirectory.$tmpname.$filename_full[$key];
            move_uploaded_file($tmpfilepath[$key], $tmpdirectory.$tmpname.$filename_full[$key]);
        }
    }
    
    // continue if at least 2 files were uploaded
    if ($uploaded >= 2 && empty($message)) {
    
        // check if file name for merge file was provided
        // otherwise create new name based off .srt#1
        (empty($newfilename)) ? ($newfilename = $filename[0].$filename[1]. "_merged") : '';
        $newfilename_full = "$newfilename.srt";
        $zipname_full = "$newfilename.zip";
    
        function calcnewnum($oldnum) {
            global $lastnum;
            static $newnum = 0;
                // set newnumber from 0 to lastnumber in srt1
                if ($newnum == 0) {
                    $newnum = $lastnum;
                }
            $newnum += 1;
            $oldnum[1] = $newnum;
            return $oldnum[1].$oldnum[2];
        }
        
        for ($y=0; $y<$uploaded-1; ++$y) {
            
            // check if session variable for text to be parsed exists
            // otherwise get contents of first file
            // trim whitespace at beginning + end of files
            (!empty($_SESSION['newcontents'])) ? ($content1 = trim($_SESSION['newcontents'])) : ($content1 = trim(file_get_contents($filename_moved[$y])));
            $content2 = trim(file_get_contents($filename_moved[($y+1)]));
    
            // regex to find subtitle "blocks" numbers
            $pattern = '%(\d+)(\s+\d\d:)%';
            
            // find number of last subtitle "block" in content1
            preg_match_all($pattern, $content1, $matches);
            $position = count($matches[0])-1;
            $lastnum = $matches[0][$position];

            // replace old subtitle "block" numbers in srt2 with newly generated ones
            $content2 = preg_replace_callback($pattern, 'calcnewnum', $content2);
            
            // combine srt1 + srt2
            $content12 = $content1. "\r\n\r\n" .$content2. "\r\n";
            
            // fill session variable for next iteration
            $_SESSION['newcontents'] = $content12;

            // unlink the two temporary files used
            file_exists($filename_moved[$y]) ? (unlink($filename_moved[$y])) : '';
            file_exists($filename_moved[($y+1)]) ? (unlink($filename_moved[($y+1)])) : '';
        }
        
        // create new srt file
        // with contents of session variable for all (new) content        
        $newcontent = $_SESSION['newcontents'];
                
        $thisfile = fopen($newfilename_full, "w");
        fwrite($thisfile, $newcontent);
        fclose($thisfile);  
                   
        $thiszip = new ZipArchive;
        $res = $thiszip->open($zipname_full, ZipArchive::OVERWRITE);
        if ($res === TRUE) {
            $thiszip->addFile($newfilename_full);
            $thiszip->close();
            $thiszipsize = filesize($zipname_full);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' .basename($zipname_full));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' .$thiszipsize);
            ob_clean();
            flush();

            readfile($zipname_full);
            
            // unlink all files
            unlink($newfilename_full);
            unlink($zipname_full);
            unset($_SESSION['newcontents'], $message);
        }         
    } else {
            $message[] = "Not enough files were uploaded.<br>Please choose at least 2 .srt files to merge!";
      }   
}
?>