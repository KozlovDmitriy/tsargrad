<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News as News;
use Carbon\Carbon;

class NewsTableSeeder extends Seeder
{
    public function run()
    {
        // clear table
        News::truncate();

        News::create( [
            'title' => 'Entry 1 Title' ,
            'date' => Carbon::now(),
            'published' => 1,
            'text' => 'Entry 1 Text',
            'photo' => '',
        ] );

        News::create( [
            'title' => 'Entry 2 Title' ,
            'date' => Carbon::now(),
            'published' => 1,
            'text' => 'Entry 2 Text',
            'photo' => '',
        ] );

        News::create( [
            'title' => 'Entry 3 Title' ,
            'date' => Carbon::now(),
            'published' => 1,
            'text' => 'Entry 3 Text',
            'photo' => '',
        ] );

        News::create( [
            'title' => 'Entry 4 Title' ,
            'date' => Carbon::now(),
            'published' => 1,
            'text' => 'Entry 4 Text',
            'photo' => '',
        ] );

    }
}