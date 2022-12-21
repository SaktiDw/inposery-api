<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return $category->product()->get();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'string|required|unique:categories,name,' . $request->id,
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response(['message' => 'Category was updated!'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response(['message' => 'Category was deleted!'], 200);
    }
}
