<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Topic;
use App\Models\Category;

class CategoriesController extends Controller
{
    //
    public function show(Category $category,Request $request,Topic $topic){
        //读取分类ID 关联的话题，并按每 20 条分页
        $topics = Topic::withOrder($request->order)
        ->with('user','category')  //预加载防止 N+1 问题
        ->where('category_id',$category->id)
        ->paginate(20);

        //传参变量话题和分类话题到模版中
        return view('topics.index',compact('topics','category'));
    }
}
