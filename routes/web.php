<?php

use Goutte\Client;
use Illuminate\Support\Facades\Route;
use Symfony\Component\DomCrawler\Crawler;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/te', function () {
    $url = 'https://www.thegioididong.com/dtdd';

    $client = new Client();

    $crawler = $client->request('GET', $url);
    $crawler->filter('ul.homeproduct li.item')->each(
        function (Crawler $node) {
            $name = $node->filter('h3')->text();

            $price = $node->filter('.price strong')->text();

            $wholeStar = $node->filter('.icontgdd-ystar')->count();
            $halfStar = $node->filter('.icontgdd-hstar')->count();
            $rate = $wholeStar + 0.5 * $halfStar;

        }
    );
dd(   $crawler );
});