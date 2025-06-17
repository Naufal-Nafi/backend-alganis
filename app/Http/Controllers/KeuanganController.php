<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consignment;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KeuanganController extends Controller
{
    // fungsi untuk menampilkan halaman laporan mingguan
    public function mingguanIndex()
    {
        return view('laporan.mingguan');
    }
    // fungsi untuk menampilkan halaman laporan bulanan
    public function bulananIndex()
    {
        return view('laporan.bulanan');
    }
    // fungsi untuk mendapatkan data laporan harian (pemasukan dan pengeluaran) selama satu minggu
    public function getDailyReport()
    {
        Carbon::setLocale('id');
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        // Ambil consignment + relasi produk untuk hitung income
        $incomes = Consignment::with('product')
            ->whereBetween('entry_date', [$startOfWeek, $endOfWeek])
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->entry_date)->format('Y-m-d');
            });

        // Ambil data pengeluaran (expenses)
        $expenses = Expense::whereBetween('date', [$startOfWeek, $endOfWeek])
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        $dataHarian = [];
        for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
            $formattedDate = $date->format('Y-m-d');

            // Hitung income dari sold * price
            $dailyIncome = isset($incomes[$formattedDate])
                ? $incomes[$formattedDate]->sum(function ($item) {
                    return $item->sold * ($item->product->price ?? 0);
                })
                : 0;

            // Hitung pengeluaran
            $dailyExpense = isset($expenses[$formattedDate])
                ? $expenses[$formattedDate]->sum('amount')
                : 0;

            $dataHarian[] = [
                'label' => $date->translatedFormat('l'),
                'masuk' => $dailyIncome,
                'keluar' => $dailyExpense,
            ];
        }

        return response()->json($dataHarian);
    }


    // fungsi untuk mendapatkan data laporan mingguan (pemasukan dan pengeluaran) selama satu bulan
    public function getWeeklyReport()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Ambil semua consignment dalam bulan ini beserta relasi product
        $consignments = Consignment::with('product')
            ->whereBetween('entry_date', [$startOfMonth, $endOfMonth])
            ->get();

        // Hitung pemasukan per minggu secara manual
        $incomes = $consignments->groupBy(function ($item) {
            return floor((Carbon::parse($item->entry_date)->day - 1) / 7) + 1;
        })->map(function ($group) {
            return $group->sum(function ($item) {
                return $item->sold * ($item->product->price ?? 0);
            });
        });

        // Ambil pengeluaran
        $expenses = Expense::selectRaw('FLOOR((DAY(date) - 1) / 7) + 1 as week, SUM(amount) as total_expense')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->groupBy('week')
            ->get();

        $dataMingguan = [];
        for ($week = 1; $week <= 4; $week++) {
            $income = $incomes->get($week, 0);
            $expense = $expenses->firstWhere('week', $week);

            $dataMingguan[] = [
                'label' => "Minggu ke-$week",
                'masuk' => $income,
                'keluar' => $expense ? $expense->total_expense : 0,
            ];
        }

        return response()->json($dataMingguan);
    }

    public function getFortnightlyReport()
    {
        $startDate = Carbon::now()->subDays(13)->startOfDay(); // 14 hari termasuk hari ini
        $endDate = Carbon::now()->endOfDay();

        // Ambil consignment + relasi produk
        $consignments = Consignment::with('product')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->entry_date)->format('Y-m-d');
            });

        // Ambil expenses
        $expenses = Expense::whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        $dataFortnight = [];

        $currentDate = $startDate->copy();
        for ($day = 1; $day <= 14; $day++) {
            $formattedDate = $currentDate->format('Y-m-d');

            $dailyIncome = isset($consignments[$formattedDate])
                ? $consignments[$formattedDate]->sum(function ($item) {
                    return $item->sold * ($item->product->price ?? 0);
                })
                : 0;

            $dailyExpense = isset($expenses[$formattedDate])
                ? $expenses[$formattedDate]->sum('amount')
                : 0;

            $dataFortnight[] = [
                'label' => $day,
                'masuk' => $dailyIncome,
                'keluar' => $dailyExpense,
            ];

            $currentDate->addDay();
        }

        return response()->json($dataFortnight);
    }



    // fungsi untuk mendapatkan data laporan bulanan (pemasukan dan pengeluaran) selama satu tahun
    public function getMonthlyReport()
    {
        $currentYear = Carbon::now()->year;

        $consignments = Consignment::with('product')
            ->whereYear('entry_date', $currentYear)
            ->get();

        // Hitung pemasukan per bulan
        $incomes = $consignments->groupBy(function ($item) {
            return Carbon::parse($item->entry_date)->month;
        })->map(function ($group) {
            return $group->sum(function ($item) {
                return $item->sold * ($item->product->price ?? 0);
            });
        });

        // Ambil pengeluaran per bulan
        $expenses = Expense::selectRaw('MONTH(date) as month, SUM(amount) as total_expense')
            ->whereYear('date', $currentYear)
            ->groupBy('month')
            ->get();

        $dataBulanan = [];
        for ($month = 1; $month <= 12; $month++) {
            $income = $incomes->get($month, 0);
            $expense = $expenses->firstWhere('month', $month);

            $dataBulanan[] = [
                'label' => Carbon::create()->month($month)->format('M'),
                'masuk' => $income,
                'keluar' => $expense ? $expense->total_expense : 0,
            ];
        }

        return response()->json($dataBulanan);
    }


    // fungsi untuk mendapatkan data laporan tahunan (pemasukan dan pengeluaran)
    public function getYearlyReport()
    {
        $firstYearConsignment = Consignment::min('entry_date') ? Carbon::parse(Consignment::min('entry_date'))->year : now()->year;
        $firstYearExpense = Expense::min('date') ? Carbon::parse(Expense::min('date'))->year : now()->year;
        $firstYear = min($firstYearConsignment, $firstYearExpense);

        $lastYearConsignment = Consignment::max('entry_date') ? Carbon::parse(Consignment::max('entry_date'))->year : now()->year;
        $lastYearExpense = Expense::max('date') ? Carbon::parse(Expense::max('date'))->year : now()->year;
        $lastYear = max($lastYearConsignment, $lastYearExpense);

        $incomes = Consignment::selectRaw('YEAR(entry_date) as year, SUM(income) as total_income')
            ->groupBy('year')
            ->get();

        $expenses = Expense::selectRaw('YEAR(date) as year, SUM(amount) as total_expense')
            ->groupBy('year')
            ->get();

        $dataTahunan = [];
        for ($year = $firstYear; $year <= $lastYear; $year++) {
            $incomeData = $incomes->firstWhere('year', $year);
            $expenseData = $expenses->firstWhere('year', $year);

            $dataTahunan[] = [
                'year' => $year,
                'masuk' => $incomeData ? $incomeData->total_income : 0,
                'keluar' => $expenseData ? $expenseData->total_expense : 0,
            ];
        }

        return response()->json($dataTahunan);
    }

    // fungsi untuk mendapatkan data laporan produk yang terjual
    public function getProductsPercentage()
    {
        $dataProduk = Product::withSum('consignments as total_sold', 'sold')
            ->get(['product_name', 'total_sold']);

        $totalSales = $dataProduk->sum('total_sold');

        $salesPercentage = $dataProduk->map(function ($item) use ($totalSales) {
            return [
                'barang' => $item->product_name,
                'jual' => $totalSales > 0 ? round(($item->total_sold / $totalSales) * 100, 2) : 0,
            ];
        });

        return response()->json($salesPercentage);
    }

    // fungsi untuk mendapatkan data omset dan profit
    public function getOmsetAndProfit()
    {
        $total_omset = Consignment::sum('income');

        $totalPengeluaran = Expense::sum('amount');

        $total_profit = $total_omset - $totalPengeluaran;

        return view('laporan.riwayat', compact('total_omset', 'total_profit'));
    }

    public function getIncomePercentageByDays(Carbon $days)
    {
        $consignments = Consignment::with('product')
            ->whereDate('entry_date', '>=', $days)
            ->get();


        // Hitung income per produk
        $incomeData = $consignments->groupBy('product.product_name')->map(function ($group) {
            return $group->sum(function ($item) {
                return $item->sold * ($item->product->price ?? 0);
            });
        });

        $totalIncome = $incomeData->sum();

        // Format hasil ke bentuk untuk chart
        $chartData = $incomeData
            ->sortByDesc(null)
            ->map(function ($income, $productName) use ($totalIncome) {
                $percentage = $totalIncome > 0 ? round(($income / $totalIncome) * 100, 2) : 0;
                return [
                    'label' => $productName,
                    'percentage' => $percentage,
                    'income' => $income,
                ];
            })->values(); // Reset indexing

        return response()->json($chartData);
    }

    public function getIncomePercentageLast7Days()
    {
        return $this->getIncomePercentageByDays(now()->subDays(7));
    }

    public function getIncomePercentageLast14Days()
    {
        return $this->getIncomePercentageByDays(now()->subDays(14));
    }

    public function getIncomePercentageLast30Days()
    {
        return $this->getIncomePercentageByDays(now()->subDays(30));
    }

    public function getIncomePercentageLast12Months()
    {
        return $this->getIncomePercentageByDays(now()->subMonths(12));
    }


    public function storeIncomes()
    {
        // Ambil total income per store (dari sold * product->price)
        $stores = Store::with(['consignments.product'])->get();

        // Hitung income tiap store dan bentuk ulang data
        $stores = $stores->map(function ($store) {
            $totalIncome = $store->consignments->sum(function ($consignment) {
                return $consignment->sold * ($consignment->product->price ?? 0);
            });

            return [
                'store_id' => $store->store_id,
                'store_name' => $store->store_name,
                'total_income' => $totalIncome,
            ];
        });

        // Hitung total income dari semua toko
        $totalIncome = $stores->sum('total_income');

        // Format hasil dengan persentase
        $result = $stores
            ->map(function ($store) use ($totalIncome) {
                $income = $store['total_income'];
                $percentage = $totalIncome > 0 ? ($income / $totalIncome) * 100 : 0;

                return [
                    'store_name' => $store['store_name'],
                    'total_income' => (float) $income,
                    'percentage' => round($percentage, 2),
                ];
            })
            ->sortByDesc('total_income')
            ->values();

        return response()->json($result);
    }


}
