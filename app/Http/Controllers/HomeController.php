<?php
/**
 * Created by PhpStorm.
 * User: dev02
 * Date: 7/24/2018
 * Time: 1:12 PM
 */

namespace App\Http\Controllers;

use Sunra\PhpSimple\HtmlDomParser;
//use Sunra\PhpSimple\HtmlDomParser;

class HomeController extends  Controller
{


    public function index(){
//        $url  = "http://www.google.com"  ;
//        $html = HtmlDomParser::file_get_html($url, false, null , 0);
//
//        foreach($html->find('img') as $element) {
//            echo '<img src="'.$url.$element->src.'" /><br>';
//        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://vnexpress.net/');
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);

        curl_close($ch);
        return view('home.index') ;
    }



}