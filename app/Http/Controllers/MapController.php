<?php

// app/Http/Controllers/MapController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MapController extends Controller
{
    public function googlemap()
    {
        return view('layout.googlemap');
    }

}

