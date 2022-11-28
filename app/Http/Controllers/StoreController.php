<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\Mime\MimeTypes;

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
            ->allowedFilters(['name'])
            ->defaultSort('created_at')
            ->allowedSorts(['name', 'created_at'])
            ->paginate($request->limit)
            ->appends(request()->query());
        // $store = auth()->user()->stores;
        // $store = Store::where('user_id', auth()->user()->id)
        //     ->with("media")

        // ->where('name', 'LIKE', '%' . $request->search . '%')
        //     ->paginate(16);

        // return json_encode($store);
        // $sortField = request('sort_field', 'created_at');
        // if (!in_array($sortField, ['name'])) {
        //     $sortField = 'created_at';
        // }
        // $sortDirection = request('sort_direction', 'desc');
        // if (!in_array($sortDirection, ['asc', 'desc'])) {
        //     $sortDirection = 'desc';
        // }
        // $store = Store::when(request('search', '', function ($query) {
        //     $query->where(function ($q) {
        //         $q
        //             ->where('user_id', auth()->user()->id)
        //             ->where('name', 'LIKE', '%' . request('search') . '%')
        //             ->paginate(15);
        //     });
        // }))->orderBy($sortField, $sortDirection)->paginate(16);

        return json_encode($store);
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
                ->toMediaCollection();
        }

        return response()->json(["message" => "Store was Created!"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function show(Store $store)
    {
        $store->transaction;
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
                ->toMediaCollection();
        }
        return response()->json(["message" => "Store was Updated!"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Store  $store
     * @return \Illuminate\Http\Response
     */
    public function destroy(Store $store)
    {
        $store->delete();
        return response()->json(["message" => "Store was Deleted!"]);
    }
}
