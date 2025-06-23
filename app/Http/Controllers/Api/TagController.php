<?php

// app/Http/Controllers/Api/TagController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tag;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;

class TagController extends Controller
{




    public function index()
    {
        $tags = Tag::pluck('name');
        return response()->json($tags);
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name' => 'required|string|unique:tags,name|max:50',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $tag = Tag::create(['name' => $request->name]);
        return response()->json(['message' => 'Tag created', 'tag' => $tag], 201);
    }





}

