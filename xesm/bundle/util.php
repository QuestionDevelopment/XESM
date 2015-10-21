<?php
/**
 * General Site Utilities Class
 *
 * @version 1.0
 * @package fileServer
 * @subpackage bundle
 * @category class

 * @author Justin Campo <jcampo@ssww.com>
 */
namespace xesm\bundle;

/**
 * General Site Utilities Class
 *
 * This class stores methods for any general web site
 * utilities.  If any group of methods become plentiful they
 * should be moved to their own standalone class.
 */
class util
{

    /**
     * Redirects current page to another URL
     *
     * @param string $url The URL to redirect visitor to
     */
    public function redirect($url="")
    {
        if ($url==""){ $url = $_SERVER["REQUEST_URI"]; }
        if(!headers_sent()) {
            header('Location: '.$url);
            exit;
        } else {
            echo '<script type="text/javascript">';
            echo 'window.location.href="'.$url.'";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
            echo '</noscript>';
            exit;
        }
    }

    /**
     * Parses a directory and provides file content information
     *
     * @param string $readDir The directory to be read
     * @param string $mode file=only return file info, dir=only return dir info
     * @return array The directory information
     */
    public function dir_read($readDir, $mode = "")
    {
        $dir = dir($readDir);
        $data = array();
        while(($entry = $dir->read()) !== false) {
            if((is_dir($readDir . $entry)) && ($entry != ".") && ($entry != "..")) {
                $data["dirs"][] = $entry;
            }
            else if(is_file($readDir . $entry)) {
                $data["files"][] = $entry;
            } // end if
        } // end while
        $dir->close();

        if ($mode == "file"){ return $data["files"]; }
        else if ($mode == "dir"){ return $data["dirs"]; }
        else { return $data; }
    }

    /**
     * Confirms if the param is an array or not
     *
     * @param array $data
     * @return boolean Is the param a array?
     */
    public function array_check($data)
    {
        if (is_array($data) && count($data) > 0){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Allows for easy uploading of files
     *
     * @param array $file $_FILE information from POST
     * @param string $accountDirectory The location on the server where uploadDir resides
     * @param string $uploadDirectory Where to upload file to
     * @return string The name of the file uploaded or false if error
     */
    public function upload($file, $accountDirectory, $uploadDirectory, $newFileName = '', $allowed_extensions = array())
    {
        $return = array();
        $return["error"] = 0;
        if(!file_exists($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $return["result"] = false;
        }
        if($file['error'] != UPLOAD_ERR_OK) {
            $return["result"] = false;
            $return["error"] = $file['error'];
            switch($file['error']){
                case 0: //no error; possible file attack!
                    //echo "There was a problem with your upload.";
                    break;
                case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
                    //echo "The file you are trying to upload is too big.";
                    break;
                case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
                    //echo "The file you are trying to upload is too big.";
                    break;
                case 3: //uploaded file was only partially uploaded
                    //echo "The file you are trying upload was only partially uploaded.";
                    break;
                case 4: //no file was uploaded
                    //echo "You must select an image for upload.";
                    break;
                default: //a default error, just in case!  :)
                    //echo "There was a problem with your upload.";
                    break;
            }
        } else {
            //make sure extension is good
            $path_parts = pathinfo($file["name"]);
            if (!isset($path_parts['extension']) || $path_parts['extension'] == ""){
                $return["error"] = "998";
            } else {
                $return["extension"] = $path_parts['extension'];
                if (is_array($allowed_extensions) AND count($allowed_extensions) > 0){
                    if (!in_array(strtolower($return["extension"]),$allowed_extensions)){
                        $return["result"] = false;
                        $return["error"] = "999";
                    }
                }
            }
            if ($return["error"] ===0 ){
                if ($newFileName == ''){
                    $newFileName = preg_replace("/[^a-zA-Z0-9-.]/", "", $file["name"]);
                } else {
                    $newFileName = $newFileName;
                }
                $uploadLocation = $accountDirectory . $uploadDirectory . $newFileName;
                $appendText = "";
                while (file_exists($uploadLocation)) {
                    $appendText = time();
                    $uploadLocation = $accountDirectory . $uploadDirectory . $appendText . $newFileName;
                }
                $return["name"] = $appendText.$newFileName;
                if (move_uploaded_file($file["tmp_name"],$uploadLocation)==true) {
                    $return["file"] = str_replace($accountDirectory.$uploadDirectory, "",$uploadLocation);
                    $return["result"] = true;
                } else {
                    $return["result"] = false;
                }
            }
        }
        return $return;
    }

    /**
     * Email message using PHP built in capability
     *
     * @param string $message_to
     * @param string $message_from
     * @param string $message_subject
     * @param string $message_body
     */
    public function email($message_to, $message_from, $message_subject, $message_body)
    {
        $headers = 'From: '.$message_from. "\r\n" .
            'Reply-To: '. $message_to . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        mail($message_to, $message_subject, $message_body, $headers);
    }

    /**
     * Returns the IP address of the current visitor
     *
     * Captures all data from PHP's $_SERVER var.  Includes HTTP_X_FORWARDED_FOR
     * if available in the format of IP_ADDRESS->FORWARD_IP
     *
     * @return string
     */
    public function ip()
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ipAddress = isset($_SERVER["HTTP_CLIENT_IP"]) ? $_SERVER["HTTP_CLIENT_IP"] : $_SERVER["REMOTE_ADDR"];
            $ipAddress .= "->".$_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            $ipAddress = isset($_SERVER["HTTP_CLIENT_IP"]) ? $_SERVER["HTTP_CLIENT_IP"] : $_SERVER["REMOTE_ADDR"];
        }
        return $ipAddress;
    }
}
