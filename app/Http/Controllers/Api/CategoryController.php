<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryController extends Controller
{

    public function index(Request $request)
    {
        $category = QueryBuilder::for(Category::class)
            ->with(["product"])
            ->withCount('product')
            ->allowedFilters(['name'])
            ->defaultSort('created_at')
            ->allowedSorts(['name', 'created_at'])
            // ->paginate($request->limit)
            ->get();
        // ->appends(request()->query());
        return $category;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'string|required|unique:categories,name',
        ]);

        $category = Category::firstOrCreate([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response(['message' => 'Category was created!'], 200);
    }

    // public function show(Category $category)
    // {
    //     return $category->product()->get();
    // }

    // public function update(Request $request, Category $category)
    // {
    //     $request->validate([
    //         'name' => 'string|required|unique:categories,name,' . $request->id,
    //     ]);

    //     $category->update([
    //         'name' => $request->name,
    //         'slug' => Str::slug($request->name),
    //     ]);

    //     return response(['message' => 'Category was updated!'], 200);
    // }

    // public function destroy(Category $category)
    // {
    //     $category->delete();
    //     return response(['message' => 'Category was deleted!'], 200);
    // }
}
