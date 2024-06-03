<?php 

namespace App\Requests;

class Req
{
     const MAX_IMAGES = 3;
     const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];
     const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jfif'];
     const MAX_FILE_SIZE = 32 * 1024 * 1024;
     const UPLOAD_DIR = '../resources/uploads/';
     private static $images = [];

     public static function getParams()
     {
          $urlComponents = parse_url($_SERVER['REQUEST_URI']);
          if(isset($urlComponents['query'])){
               parse_str($urlComponents['query'], $queryParams);
          }
          return $queryParams;
     }

     public static function getPostParams()
     {
          return $_POST;
     }

     public static function getFiles()
     {
          return $_FILES;
     }

     public static function validateFile(array $file)
     {
          $fileTmpPath = $file['tmp_name'];
          $fileName = $file['name'];
          $fileSize = $file['size'];
          $fileType = $file['type'];
          $fileNameCmps = explode(".", $fileName);
          $fileExtension = strtolower(end($fileNameCmps));

          $finfo = finfo_open(FILEINFO_MIME_TYPE);
          $mimeType = finfo_file($finfo, $fileTmpPath);
          finfo_close($finfo);

          if($file['error'] != UPLOAD_ERR_OK 
          || $fileSize > self::MAX_FILE_SIZE 
          || !in_array($fileExtension, self::ALLOWED_IMAGE_EXTENSIONS)
          || !in_array($mimeType, self::ALLOWED_MIME_TYPES)){
               return false;
          }

          self::$images[] = [
               'type' => $fileType,
               'tmp_name' => $fileTmpPath,
               'error' => $file['error'],
               'size' => $fileSize,
               'extension' => $fileExtension,
               'mimeType' => $mimeType,
               'base64' => base64_encode(file_get_contents($fileTmpPath)),
          ];

          return true;
     }

     public static function getImages()
     {
          return self::$images;
     }

     public static function saveImages()
     {

          if (!is_dir(self::UPLOAD_DIR)) {
               mkdir(self::UPLOAD_DIR, 0777, true);
          }

          foreach(self::$images as $image){
               if(!move_uploaded_file($image['tmp_name'], self::UPLOAD_DIR . $image['name']. '.' . $image['extension'])){
                    return false;
               }

               $image['tmp_name'] = null;
          }

          return true;
     }
}
