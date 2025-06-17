<?php

namespace App\Http\Controllers;

use App\Models\Consignment;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsignmentController extends Controller
{
    //fungsi untuk menampilkan daftar data consignment dengan paginasi
    public function laporanIndex(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $consignments = Consignment::with('product', 'store')
            ->orderByRaw("(entry_date IS NULL) DESC")
            ->orderBy('exit_date', 'DESC')
            ->paginate($perPage);

        $consignments->getCollection()->transform(function ($consignment) {
            $status = ($consignment->stock - $consignment->sold == 0) ? 'Close' : 'Open';

            $circulationDuration = $consignment->entry_date && $consignment->exit_date
                ? Carbon::parse($consignment->exit_date)->diffInDays(Carbon::parse($consignment->entry_date))
                : null;

            $totalPrice = $consignment->stock * $consignment->product->price;

            return [
                'consignment_id' => $consignment->consignment_id,
                'product_name' => $consignment->product->product_name,
                'store_name' => $consignment->store->store_name,
                'status' => $status,
                'circulation_duration' => $circulationDuration,
                'entry_date' => $consignment->entry_date,
                'exit_date' => $consignment->exit_date,
                'price' => $consignment->product->price,
                'total_price' => $totalPrice,
                'stock' => $consignment->stock,
                'sold' => $consignment->sold,
            ];
        });

        return response()->json([
            'data' => $consignments->items(),
            'pagination' => [
                'current_page' => $consignments->currentPage(),
                'last_page' => $consignments->lastPage(),
                'per_page' => $consignments->perPage(),
                'total' => $consignments->total(),
            ]
        ]);
    }

    //fungsi untuk menampilkan halaman edit consignment berdasarkan ID
    public function laporanEdit($consignment_id)
    {
        $consignment = Consignment::with(['store', 'product'])->findOrFail($consignment_id);
        return response()->json($consignment);
    }

    //fungsi untuk menampilkan halaman tambah consignment
    public function laporanCreate()
    {
        // return view('transaksi.tambah');
    }

    //fungsi untuk menyimpan data consignment baru ke dalam database
    public function laporanStore(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string',
            'store_name' => 'required|string',
            'price' => 'required|integer',
            'stock' => 'required|integer',
            'exit_date' => 'required|date',
        ]);

        // dd($request->price);
        $consignment = new Consignment();

        $product = Product::create(['product_name' => $request->product_name, 'price' => $request->price]);
        $store = Store::create(['store_name' => $request->store_name]);

        $consignment->product_id = $product->product_id;
        $consignment->store_id = $store->store_id;
        $consignment->stock = $request->stock;
        $consignment->exit_date = $request->exit_date;
        $consignment->user_id = Auth::id();
        $consignment->creator_name = Auth::user()->name;
        $consignment->save();
        return redirect('/transaksi');
    }

    //fungsi untuk mengupdate data consignment berdasarkan ID
    public function laporanUpdate(Request $request, $consignment_id)
    {
        $consignment = Consignment::with(['store', 'product'])->findOrFail($consignment_id);
        // dd($request);
        $request->validate([
            'store_name' => 'required|string',
            'product_name' => 'required|string',
            'entry_date' => 'required|date',
            'exit_date' => 'required|date',
            'price' => 'required|integer',
            'sold' => [
                'required',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) use ($consignment) {
                    if ($value > $consignment->stock) {
                        $fail("Stock Tidak Sebanyak Yang Terjual");
                    }
                },
            ],
            'stock' => 'required|integer',
        ]);

        $consignment = Consignment::with(['store', 'product'])->find($consignment_id);

        // $consignment->store->store_name = $request->store_name;
        // $consignment->store->save();
        $consignment->store->update(['store_name' => $request->store_name]);

        // $consignment->product->product_name = $request->product_name;
        // $consignment->product->save();
        $consignment->product->update(['product_name' => $request->product_name]);
        $consignment->product->update(['price' => $request->price]);

        $consignment->entry_date = $request->entry_date;
        $consignment->exit_date = $request->exit_date;
        $consignment->sold = $request->sold;
        $consignment->stock = $request->stock;
        $consignment->user_id = Auth::id();
        $consignment->save();
        return redirect('/transaksi');
    }

    //fungsi untuk menghapus data consignment berdasarkan ID
    public function laporanDestroy($consignment_id)
    {
        $consignment = Consignment::findOrFail($consignment_id);
        $consignment->user_id = Auth::id();
        $consignment->delete();

        return redirect()->route('laporan.index');
    }

    //fungsi untuk menampilkan daftar data consignment yang masih open
    public function mainpageIndex()
    {
        $consignments = Consignment::with('product', 'store')
            ->paginate(10);

        $transformedConsignments = $consignments->getCollection()->transform(function ($consignment) {
            return [
                'store_name' => $consignment->store->store_name,
                'product_name' => $consignment->product->product_name,
                'income' => $consignment->sold * $consignment->product->price,
                'exit_date' => $consignment->exit_date,
            ];
        });

        $totalIncome = Consignment::selectRaw('SUM(sold * products.price) as total')
            ->join('products', 'consignments.product_id', '=', 'products.product_id')
            ->value('total') ?? 0;
        $totalExpense = Expense::sum('amount');

        return response()->json([
            'consignments' => [
                'data' => $transformedConsignments,
                'current_page' => $consignments->currentPage(),
                'last_page' => $consignments->lastPage(),
                'per_page' => $consignments->perPage(),
                'total' => $consignments->total(),
            ],
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
        ], 200);
    }

    // //fungsi untuk mencari consignment berdasarkan nama produk atau nama toko
    // public function mainpageSearch(Request $request)
    // {
    //     $search = $request->input('search');

    //     $consignments = Consignment::with('product', 'store')
    //         ->when($search, function ($query, $search) {
    //             return $query->whereHas('product', function ($q) use ($search) {
    //                 $q->where('product_name', 'like', '%' . $search . '%');
    //             })->orWhereHas('store', function ($q) use ($search) {
    //                 $q->where('store_name', 'like', '%' . $search . '%');
    //             });
    //         })
    //         ->get()
    //         ->filter(function ($consignment) {
    //             return $consignment->status === 'open';
    //         })
    //         ->map(function ($consignment) {
    //             return [
    //                 'store_name' => $consignment->store->store_name,
    //                 'product_name' => $consignment->product->product_name,
    //                 'stock' => $consignment->stock,
    //             ];
    //         });

    //     return view('home.home', compact('consignments', 'search'));
    // }

    public function printReceipt($consignment_id)
    {
        $consignment = Consignment::with(['store', 'product', 'user'])
            ->findOrFail($consignment_id);

        $nota = [
            'nomor' => 'TRX-' . $consignment_id,
            'nama_pencetak' => Auth::user()->name,
            'toko' => $consignment->store->store_name,
            'tanggal_masuk' => Carbon::parse($consignment->entry_date)->format('d/m/Y'),
            'tanggal_keluar' => Carbon::parse($consignment->exit_date)->format('d/m/Y'),
            'jumlah_awal' => $consignment->stock,
            'harga_satuan' => $consignment->product->price,
            'nama_produk' => $consignment->product->product_name,
            'total_awal' => $consignment->stock * $consignment->product->price,
            'jumlah_kembali' => $consignment->stock - $consignment->sold,
            'total_kembali' => ($consignment->stock - $consignment->sold) * $consignment->product->price,
            'jumlah_bayar' => $consignment->sold,
            'total_bayar' => $consignment->sold * $consignment->product->price,
            'waktu_cetak' => now()->format('d/m/Y H:i:s')
        ];

        // $pdf = PDF::loadView('transaksi.reciept', [
        //     'consignment' => $consignment,
        //     'total_price' => $consignment->sold * $consignment->product->price,
        //     'date_printed' => now()->format('d-m-Y'),
        // ]);
        // dd($nota);
        // return view('transaksi.nota', ['nota' => $nota]);


        $pdf = PDF::loadView('transaksi.nota', ['nota' => $nota]);
        return $pdf->download('nota-' . $consignment_id . '.pdf');
    }
}