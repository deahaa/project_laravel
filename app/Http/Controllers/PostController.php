<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {


         $posts = [
    [
        'title' => 'hello',
        'body' => 'this is my first post'  // تصحيح: إغلاق الاقتباس بنفس النوع (')
    ],
    [
        'title' => 'deaa',
        'body' => 'hello deaa'
    ]
];

$age = 20;
          return view("posts.index", compact('posts'))->with("name",value:'deaa')->with('isAdmin','false')->with('isUser','false')->with('age',20);

//    return view("posts.index")->with('posts', $posts)->with('age', $age);


        //للعرض
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //انشاء
     return view("posts.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //لتخزين
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit( $id)
    {
        //للتعديل
          $posts_info=[
            'id ' => $id,
            'title' => 'de4aaa',
            'content' => 'Post Content'



          ];
        return view('posts_edit' )->with('posts_info' , $posts_info);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //للتحديث
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //للحدف
    }
}
