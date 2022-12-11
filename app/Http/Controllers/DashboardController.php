<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DashboardController extends Controller
{
    public function getAllStoresTransaction(Request $request)
    {
        $store = Store::whereIn('id', explode(',', $request->id))->where('user_id', auth()->user()->id)->get();

        $from = isset($request->from) ? Carbon::parse($request->from) : Carbon::parse("2000-01-01T00:00:00.000000Z");
        $to = isset($request->to) ? Carbon::parse($request->to) : Carbon::now();
        $transaction = Transaction::with(['store' => function ($q) {
            $q->where('user_id', auth()->user()->id);
        }])
            ->whereIn('store_id', explode(',', $request->id))
            ->where('type', $request->type)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->select(
                DB::raw('SUM(total) as total'),
                DB::raw("EXTRACT(YEAR FROM `created_at`) as year"),
                DB::raw("EXTRACT(MONTH FROM `created_at`) as month"),
                // DB::raw("EXTRACT(DAY FROM `created_at`) as day"),
                // 'created_at',
                'type',
                'store_id'
            )
            ->groupBy(
                'year',
                'month',
                // 'day',
                // 'created_at',
                "type",
                'store_id'
            )
            ->get();
        if (!$transaction->count() == 0) return abort(403, "Opps");
        // $group = $transaction->groupBy(['day']);
        // if (!$transaction->count() > 0) return response(["message" => "Transactions not found!"], 404);
        // return $group->all();
        return $transaction;
    }
    public function scopeFrom(Builder $query, $date): Builder
    {
        return $query->where('created_at', '<=', Carbon::parse($date));
    }



    public function getTopProduct(Request $request)
    {
        $from = isset($request->from) ? Carbon::parse($request->from) : Carbon::parse("2000-01-01T00:00:00.000000Z");
        $to = isset($request->to) ? Carbon::parse($request->to) : Carbon::now();
        $transaction = Transaction::with('product')
            ->whereIn('store_id', explode(',', $request->id))
            ->where('type', $request->type)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->select(DB::raw('SUM(total) as total'), 'product_id')
            ->groupBy(
                'product_id'
            )->orderBy('total', 'DESC')
            ->limit($request->limit ?? 5)
            ->get();
        return $transaction;
    }
    public function getModalSales(Request $request)
    {
        $from = isset($request->from) ? Carbon::parse($request->from) : Carbon::parse("2000-01-01T00:00:00.000000Z");
        $to = isset($request->to) ? Carbon::parse($request->to) : Carbon::now();
        $transaction =
            Transaction::whereIn('store_id', explode(',', $request->id))
            // ->where('type', $request->type)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->select(DB::raw('SUM(total) as total'), 'type')
            ->groupBy(
                'type'
            )->orderBy('total', 'DESC')
            // ->paginate($request->limit ?? 5);
            ->get($request->limit);
        return $transaction;
    }

    public function Dashboard(Request $request)
    {
        $from = isset($request->from) ? Carbon::parse($request->from) : Carbon::parse("2000-01-01T00:00:00.000000Z");
        $to = isset($request->to) ? Carbon::parse($request->to) : Carbon::now();
        $id = $request->id;
        $type = $request->type;

        $transaction = Store::whereIn('id', explode(',', $id))
            // ->where('user_id', auth()->user()->id)
            ->with(['transaction' => function ($q) {
                $q->select(
                    DB::raw('SUM(total) as total'),
                    DB::raw("EXTRACT(YEAR FROM `created_at`) as year"),
                    DB::raw("EXTRACT(MONTH FROM `created_at`) as month"),
                    // DB::raw("EXTRACT(DAY FROM `created_at`) as day"),
                    'type',
                    'store_id'
                )
                    ->groupBy(
                        'year',
                        'month',
                        "type",
                        'store_id'
                    );
            }])

            ->withSum(
                [
                    'transaction as total_in' => function ($query) use ($type) {
                        $query->where('type', 'IN');
                    }
                ],
                'total',
            )
            ->withSum(
                [
                    'transaction as total_in_yesterday' => function ($query) use ($type) {
                        $query->where('type', 'IN');
                        $query->where('created_at', '>=', Carbon::yesterday());
                        $query->where('created_at', '<', Carbon::today());
                    }
                ],
                'total'
            )
            ->withSum(
                [
                    'transaction as total_in_today' => function ($query) use ($type) {
                        $query->where('type', 'IN');
                        $query->where('created_at', '>=', Carbon::today());
                    }
                ],
                'total'
            )
            ->withSum(
                [
                    'transaction as total_out' => function ($query) use ($type) {
                        $query->where('type', 'OUT');
                    }
                ],
                'total'
            )
            ->withSum(
                [
                    'transaction as total_out_yesterday' => function ($query) use ($type) {
                        $query->where('type', 'OUT');
                        $query->where('created_at', '>=', Carbon::yesterday());
                        $query->where('created_at', '<', Carbon::today());
                    }
                ],
                'total'
            )
            ->withSum(
                [
                    'transaction as total_out_today' => function ($query) use ($type) {
                        $query->where('type', 'OUT');
                        $query->where('created_at', '>=', Carbon::today());
                    }
                ],
                'total'
            )
            ->whereRelation('transaction', 'created_at', '>=', $from)
            ->whereRelation('transaction', 'created_at', '<=', $to)
            ->get();

        return $transaction;
    }
}


// public function getAllStoreTransaction($id, Request $request)
// {
//     // $store->with("transaction")->get();

//     $from = "2022-12-01T00:00:00.000000Z";
//     $to = "2022-12-03T00:00:00.000000Z";
//     $transaction = Transaction::select(
//         DB::raw('SUM(total) as total'),
//         DB::raw("EXTRACT(YEAR FROM `created_at`) as year"),
//         DB::raw("EXTRACT(MONTH FROM `created_at`) as month"),
//         DB::raw("EXTRACT(DAY FROM `created_at`) as day"),
//         'created_at'
//     )
//         ->where('store_id', $id)
//         ->whereBetween('created_at', [$request->from, $request->to])
//         ->groupBy('year', 'month', 'day', 'created_at', 'type')
//         ->get();

//     return $transaction;
// }

// ->where('type', $request->type)
// ->where('created_at', '>=', $from)
// ->where('created_at', '<=', $to)
// ->select(
//     DB::raw('SUM(total) as total'),
//     // DB::raw("EXTRACT(YEAR FROM `created_at`) as year"),
//     // DB::raw("EXTRACT(MONTH FROM `created_at`) as month"),
//     // DB::raw("EXTRACT(DAY FROM `created_at`) as day"),
//     'created_at',
//     'type',
//     'store_id'
// )
// ->groupBy(
//     // 'year', 'month', 'day', 
//     'created_at',
//     "type",
//     'store_id'
// )
// ->get();