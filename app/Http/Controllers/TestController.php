<?php

namespace App\Http\Controllers;

use App\Mail\GlobalEmailAll;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        ini_set('max_execution_time', 30000000); //300 seconds = 5 minutes


        $content = 'hi, its amir testing some emails.';

        \Mail::to([
            [ 'email' => 'amir@infcompany.com', 'name' => 'Amir' ],
        ])->send(new GlobalEmailAll("Some Email testing by AMIR.", $content));



        dd('ok nOOOOw');

        ini_set('memory_limit', -1);

    }
}
