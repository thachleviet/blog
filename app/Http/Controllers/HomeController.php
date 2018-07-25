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
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
class HomeController extends  Controller
{


    public function index(){

        $client = new Client();
        $crawler = $client->request('GET', 'https://vi.aliexpress.com/item/FLOVEME-Magnetic-Car-Phone-Holder-2-pack-Universal-Wall-Desk-Metal-Magnet-Sticker-Mobile-Stand-Phone/32837999990.html');

        $crawler->filter('div#j-product-tabbed-pane')->each(function ($node) {
            echo '<pre>' ;
            print $node->text()."\n";
            echo '</pre>' ;
        });
        return view('home.index') ;
    }



}