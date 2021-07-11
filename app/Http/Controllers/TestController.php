<?php

namespace App\Http\Controllers;

use App\Mail\GlobalEmailAll;
use App\Models\Setting;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        ini_set('max_execution_time', 30000000); //300 seconds = 5 minutes


        $content = 'Hi, Your images has been processed';

        $email = Setting::where('key','adminEmail')->first();

        \Mail::to([[ 'email' => $email ? $email->value : 'amirseersol@gmail.com', 'name' => 'Amir' ],
        ])->bcc('amirseersol@gmail.com')->send(new GlobalEmailAll("Images has been processed.", $content));


        dd('ok nOOOOw');

        ini_set('memory_limit', -1);

    }
}
