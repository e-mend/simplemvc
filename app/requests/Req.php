<?php 

namespace App\Requests;

class Req
{
   public static function getParams()
   {
        $urlComponents = parse_url($_SERVER['REQUEST_URI']);
        if(isset($urlComponents['query'])){
            parse_str($urlComponents['query'], $queryParams);
        }
        return $queryParams;
   }
}