<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class ProductController extends Controller
{
    public function qty($query, $qty)
    {
        return $query->where('qty', '<', $qty);
    }

    public function storeId($query, $store_id)
    {
        $store = Store::findOrFail($store_id);
        if ($store->user_id != auth()->user()->id) return abort(403, "Unauthorized");
        return $query->where('store_id', '=', $store_id);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $store = Store::find($request->filter['store_id']) ?? abort(404, "Store not found!");
        if ($store->user_id != auth()->user()->id) return abort(403, "Unauthorized!");
        $product = QueryBuilder::for(Product::class)
            ->with(["media", "store", "category"])
            ->allowedFilters(['name', 'store_id', AllowedFilter::scope('qty'), AllowedFilter::exact('category.name', null), AllowedFilter::trashed()])
            ->defaultSort('created_at')
            ->allowedSorts(['name', 'created_at'])
            ->paginate($request->limit)
            ->appends(request()->query());
        return $product;
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
            "name" => ["required", Rule::unique('products', 'name')->where(fn ($query) => $query->where('products.store_id', $request->store_id))],
            "sell_price" => "required|numeric|min:0",
            "store_id" => "required",
            "image" => 'sometimes|base64dimensions:min_width=100,min_height=200|base64max:2048',
            "categories.*" => 'array:min:1',
            "categories.*.value" => 'string|required',
        ]);
        $store = Store::where('user_id', auth()->user()->id)->find($request->store_id);
        abort_if(!$store, 403, 'Unauthorized!');
        $product = Product::create([
            "name" => $request->name,
            "sell_price" => $request->sell_price,
            "store_id" => $request->store_id,
        ]);

        if ($request->image !== null) {
            $product->addMediaFromBase64($request->image)
                ->usingFileName(Str::random() . '.png')
                ->toMediaCollection($request->store_id);
        };
        $categories_id = [];
        if ($request->category and count($request->category) > 0) {
            foreach ($request->category as $key => $value) {
                $category = Category::firstOrCreate([
                    'name' => $request->category[$key]['value'],
                    'slug' => Str::slug($request->category[$key]['value'])
                ]);
                array_push($categories_id, $category->id);
            }
        }
        $product->category()->syncWithoutDetaching($categories_id);
        return response(["message" => "Product was created!"], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return $product;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            "name" => ["required", Rule::unique('products', 'name')->where(fn ($query) => $query->where('products.store_id', $request->store_id))->ignore($product->id)],
            "sell_price" => "required|numeric|min:0",
            "store_id" => "required",
            "qty" => "number"
        ]);
        if ($product->store_id != $request->store_id) return abort(403, "Unauthorized");
        $product->update([
            "name" => $request->name,
            "sell_price" => $request->sell_price,
            "store_id" => $request->store_id,
            "qty" => $request->qty,
        ]);
        if ($request->image !== null) {
            if (count($product->media) > 0) {
                $product->media[0]->delete();
            }
            $product->addMediaFromBase64($request->image)
                ->usingFileName(Str::random() . '.png')
                ->toMediaCollection($request->store_id);
        }
        $categories_id = [];
        if ($request->category and count($request->category) > 0) {
            foreach ($request->category as $key => $value) {
                $category = Category::firstOrCreate([
                    'name' => $request->category[$key]['value'],
                    'slug' => Str::slug($request->category[$key]['value'])
                ]);
                array_push($categories_id, $category->id);
            }
        }
        $product->category()->sync($categories_id);

        return response(["message" => "Product was updated!", "data" => $product], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($request)
    {
        $ids = explode(',', $request);
        $products = Product::whereIn('id', $ids)->whereRelation('store', 'user_id', auth()->user()->id)->with(['media',])->get();
        if ($products->count() == 0) abort(404, 'Product not found!');
        $products->each(function ($item) {
            if (count($item->media) > 0) {
                $item->media[0]->delete();
            };
            $item->delete();
        });

        return response(['message' => "Product was deleted!"], 200);
    }
    public function restore($id)
    {
        $product = Product::onlyTrashed()->whereRelation('store', 'user_id', auth()->user()->id)->findOrFail($id);
        if ($product->count() == 0) return abort(404, "Product not found!");
        $product->transaction()->where('deleted_at', '>=', $product->deleted_at)->restore();
        $product->restore();
        return response(['message' => 'Product was restored!']);
    }
    public function destroy_permanent($id)
    {
        $product = Product::withTrashed()->whereRelation('store', 'user_id', auth()->user()->id)->findOrFail($id);
        if ($product->count() == 0) return abort(404, 'Product not found!');
        $product->forceDelete();
        return response(['message' => 'Product was pemanently deleted!']);
    }
}
