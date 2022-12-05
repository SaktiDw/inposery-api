<?php

namespace App\Http\Controllers;

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
        if ($store->user_id != auth()->user()->id) return abort(403);
        return $query->where('store_id', '=', $store_id);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $store = Store::find($request->filter['store_id']) ?? abort(404, "Store that u looking for doesn't exist!!");
        if ($store->user_id != auth()->user()->id) return abort(403, "You don't have access to this Store! Stay away!!!");
        $product = QueryBuilder::for(Product::class)
            ->with(["media", "store"])
            ->allowedFilters(['name', 'store_id', AllowedFilter::scope('qty'),])
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
            'image' => 'sometimes|base64dimensions:min_width=100,min_height=200|base64max:2048'
        ]);

        $product = Product::create([
            "name" => $request->name,
            "sell_price" => $request->sell_price,
            "store_id" => $request->store_id,
        ]);
        if ($request->image !== null) {
            $product->addMediaFromBase64($request->image)
                ->usingFileName(Str::random() . '.png')
                ->toMediaCollection();
        }
        return response()->json(["message" => "Product was created!"]);
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
                ->toMediaCollection();
        }

        return response()->json(["message" => "Product was updated!", "data" => $product]);
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
        $products = Product::whereIn('id', $ids)->with('media')->get();
        $products->each(function ($item) {
            if (count($item->media) > 0) {
                $item->media[0]->delete();
            };
            $item->delete();
        });
        return response()->json(['message' => "Product was deleted!"]);
    }
}
