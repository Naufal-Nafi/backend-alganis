<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Consignment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Validator;

class ConsignmentApiController extends Controller
{
    /**
     * Display a listing of consignments.
     */
    public function index()
    {

        $consignments = Consignment::with(['product', 'store', 'user'])
            ->orderByRaw("(entry_date IS NULL) DESC")
            ->orderBy('exit_date', 'DESC')
            ->paginate(10)
            ->through(function ($consignment) {
                $status = ($consignment->stock - $consignment->sold == 0) ? 'Close' : 'Open';

                $circulationDuration = ($consignment->entry_date && $consignment->exit_date)
                    ? Carbon::parse($consignment->exit_date)->diffInDays(Carbon::parse($consignment->entry_date))
                    : null;

                return [
                    'consignment_id' => $consignment->consignment_id,
                    'product_name' => $consignment->product->product_name,
                    'store_name' => $consignment->store->store_name,
                    'entry_date' => $consignment->entry_date,
                    'exit_date' => $consignment->exit_date,
                    'status' => $status,
                    'circulation_duration' => $circulationDuration,
                    'stock' => $consignment->stock,
                    'sold' => $consignment->sold,
                    'price' => $consignment->product->price,
                    'total_price' => $consignment->stock * $consignment->product->price,
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Consignments retrieved successfully',
            'data' => $consignments
        ], 200);
    }

    /**
     * Display the specified consignment.
     */
    public function show($consignment_id)
    {
        $consignment = Consignment::with(['product', 'store', 'user'])
            ->findOrFail($consignment_id);

        if (!$consignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Consignment not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Consignment retrieved successfully',
            'data' => [
                'consignment_id' => $consignment->consignment_id,
                'product_name' => $consignment->product->product_name,
                'store_name' => $consignment->store->store_name,
                'entry_date' => $consignment->entry_date,
                'exit_date' => $consignment->exit_date,
                'stock' => $consignment->stock,
                'sold' => $consignment->sold,
                'price' => $consignment->product->price,
                'total_price' => $consignment->stock * $consignment->price,
            ]
        ], 200);
    }

    /**
     * Store a newly created consignment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'store_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'exit_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Create product and store if they don't exist
        $product = Product::firstOrCreate(
            ['product_name' => $request['product_name']],
            ['price' => $request['price'], 'stock' => 0]
        );

        $store = Store::firstOrCreate(['store_name' => $request['store_name']]);

        $consignment = Consignment::create([
            'product_id' => $product->product_id,
            'store_id' => $store->store_id,
            'user_id' => Auth::id(),
            'creator_name' => Auth::user()->name,
            'stock' => $request->stock,
            'exit_date' => $request->exit_date,
            'price' => $request->price,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Consignment created successfully',
            'data' => $consignment->load(['product', 'store', 'user'])
        ], Response::HTTP_CREATED);
    }

    /**
     * Update the specified consignment.
     */
    public function update(Request $request, $consignment_id)
    {
        $consignment = Consignment::findOrFail($consignment_id);

        if (!$consignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Consignment not found',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($consignment->user_id !== Auth::id() && Auth::user()->role !== 'owner') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'store_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'entry_date' => 'nullable|date|after_or_equal:exit_date',
            'exit_date' => 'nullable|date',
            'sold' => [
                'required',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value > $request->stock) {
                        $fail("Terjual lebih banyak dari stok yang tersedia.");
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $store = Store::firstOrCreate(['store_name' => $request->store_name]);
        $product = Product::firstOrCreate(
            ['product_name' => $request->product_name],
            ['price' => $request->price, 'stock' => 0]
        );

        $consignment->store->update(['store_name' => $request->store_name]);
        $consignment->product->update([
            'product_name' => $request->product_name,
            'price' => $request->price,
        ]);


        $consignment->update([
            'exit_date' => $request->exit_date,
            'entry_date' => $request->entry_date,
            'stock' => $request->stock,
            'sold' => $request->sold,
            'user_id' => auth()->id(),
        ]);


        return response()->json([
            'status' => 'success',
            'message' => 'Consignment updated successfully',
            'data' => $consignment->load(['product', 'store', 'user'])
        ], 200);
    }

    /**
     * Remove the specified consignment.
     */
    public function destroy($consignment_id)
    {
        $consignment = Consignment::find($consignment_id);

        if (!$consignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Consignment not found',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($consignment->user_id !== Auth::id() && Auth::user()->role !== 'owner') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        $consignment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Consignment deleted successfully'
        ], 200);
    }
}