<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $store = QueryBuilder::for(Store::class)
            ->where('user_id', auth()->user()->id)
            ->with(["media"])
            ->allowedFilters(['name', AllowedFilter::trashed()])
            ->defaultSort('created_at')
            ->allowedSorts(['name', 'created_at'])
            ->paginate($request->limit)
            ->appends(request()->query());

        return $store;
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
            "name" => ["required", Rule::unique('stores', 'name')->where(fn ($query) => $query->where('stores.user_id', auth()->user()->id))],
            'image' => 'sometimes|base64image|base64dimensions:min_width=100,min_height=200|base64max:2048'
        ]);

        $store = Store::create([
            'name' => $request->name,
            'user_id' => auth()->user()->id
        ]);
        if ($request->image !== null) {
            $store->addMediaFromBase64($request->image)
                ->usingFileName(Str::random() . '.png')
                ->toMediaCollection($store->id);
        }

        return response(["message" => "Store was Created!"], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function show(Store $store)
    {
        if (auth()->user()->id != $store->user_id) return abort(403, "You don't have access to this Store! Stay away!!!");
        $store->getFirstMediaUrl();
        return $store;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Store $store)
    {
        $request->validate([
            "name" => ["required", Rule::unique('stores', 'name')->where(fn ($query) => $query->where('stores.user_id', auth()->user()->id))->ignore($store->id)],
            'image' => 'sometimes|base64image|base64dimensions:min_width=100,min_height=200|base64max:2048'
        ]);
        if ($store->user_id != auth()->user()->id) return abort(403, "Unauthorized");
        $store->update([
            "name" => $request->name,
            "user" => $request->user_id,
        ]);

        if ($request->image !== null) {
            if (count($store->media) > 0) {
                $store->media[0]->delete();
            }
            $store->addMediaFromBase64($request->image)
                ->usingFileName(Str::random() . '.png')
                ->toMediaCollection($store->id);
        }
        return response(["message" => "Store was Updated!"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function destroy(Store $store)
    {
        if ($store->user_id != auth()->user()->id) return abort(403, "Unauthorized");
        $store->delete();
        return response(["message" => "Store was Deleted!"], 200);
    }

    public function restore($id)
    {
        $store = Store::onlyTrashed()->findOrFail($id);
        if ($store->user_id != auth()->user()->id) return abort(403, "Unauthorized");
        $store->product()->where('deleted_at', '>=', $store->deleted_at)->restore();
        $store->transaction()->where('deleted_at', '>=', $store->deleted_at)->restore();
        $store->receipt()->where('deleted_at', '>=', $store->deleted_at)->restore();
        $store->restore();
        return response(['message' => 'Store was restored!']);
    }
    public function destroy_permanent($id)
    {
        $store = Store::where('id', $id)->where('user_id', auth()->user()->id)->forceDelete();
        if (!$store) return abort(404, 'Store not found!');
        return response(['message' => 'Store was pemanently deleted!']);
    }
}
