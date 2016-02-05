<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use Goutte\Client;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => 'web'], function () {
    Route::auth();

    Route::get('/home', 'HomeController@index');

    Route::get('/list', function(){
        $client = new Client();
        $list = array();

        for($i = 0; $i <= 1700; $i+=20){
            // Check out the symfony.com subreddit and request the top posts from this month
            $crawler = $client->request('GET', 'https://www.eslpod.com/website/show_all.php?cat_id=-59456&low_rec='.$i);

            // See if the response was ok
            $status_code = $client->getResponse()->getStatus();
            if($status_code==200){
                echo '200 OK<br>';
            }

            // Use the symfony filter method to find all links which are children of paragraph
            // elements which have a class of title then loop through the results using the each method
            $name = '';
            $achou = false;
            $crawler->filter('span > a')->each(function ($node)use($name, $achou, $i) {
                if($node->text() == 'Download Podcast'){
                    $name = substr($node->attr('href'),31);
                    $dir = dir('/var/www/ESL/');

                    while(($arquivo = $dir->read()) !== false){
                        if($name == $arquivo){
                            $achou = true;
                            Log::info('Achou: '.$name);
                        }
                    }

                    if($achou == false){
                        Log::info('Não achou: '.$name);
                        Log::info('Página: '.$i);
                        Log::info('Link: '.$node->attr('href'));
                        Log::info('\n\n');
                        file_put_contents('/var/www/ESL/'.$name, fopen($node->attr('href'), 'r'));
                    }
                }
            });
        }
    });

});
