<?php

namespace App\Http\Controllers;

use App\Helper\TelegramHelper;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SaleController extends Controller
{

    public function index() {
        $orders = Sale::with('product')
            ->orderBy('sold_at', 'desc')
            ->get();

        return view('sales.index', compact('orders'));
    }


    public function monthlyReport(Request $request) {
        $month = $request->input('month', now()->format('Y-m'));

        //parse the input
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $sales = Sale::whereBetween('sold_at', [$start, $end])->get();

        $totalRevenue = $sales->sum(function($sale) {
            return $sale->price * $sale->quantity;
        });

        $topProduct = Sale::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->whereBetween('sold_at', [$start, $end])
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->with('product')
            ->first();

        $topDay = Sale::select(DB::raw('DATE(sold_at) as day'), DB::raw('SUM(price * quantity) as total'))
            ->whereBetween('sold_at', [$start, $end])
            ->groupBy('day')
            ->orderByDesc('total')
            ->first();

        $dailySales = Sale::select(DB::raw('DATE(sold_at) as date'), DB::raw('SUM(price * quantity) as total'))
            ->whereBetween('sold_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartLabels = $dailySales->pluck('date')->toArray();
        $chartData = $dailySales->pluck('total')->toArray();


        $top5Products = Sale::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereBetween('sold_at', [$start, $end])
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

            //preparing fot pie chart
            $pieLabels = $top5Products->pluck('product.name');
            $pieData = $top5Products->pluck('total_sold');


            $hourlySales = Sale::select(DB::raw('HOUR(sold_at) as hour'), DB::raw('SUM(price * quantity) as total'))
                ->whereBetween('sold_at', [$start, $end])
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $hourLabels = $hourlySales->pluck('hour')->map(function ($h) {
                return str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
            });
            $hourData = $hourlySales->pluck('total');

        return view('sales.report', [
            'month' => $month,
            'totalRevenue' => $totalRevenue,
            'topProduct' => $topProduct,
            'topDay' => $topDay,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'pieLabels' => $pieLabels,
            'pieData' => $pieData,
            'hourLabels' => $hourLabels,
            'hourData' => $hourData
        ]);
    }

    public function exportPDF(Request $request) {
        $month = $request->input('month', now()->format('Y-m'));
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $sales = Sale::whereBetween('sold_at', [$start, $end])->get();

        $totalRevenue = $sales->sum(fn($sale) => $sale->price * $sale->quantity);

        $topProduct = Sale::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->whereBetween('sold_at', [$start, $end])
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->with('product')
            ->first();

        $topDay = Sale::select(DB::raw('DATE(sold_at) as day'), DB::raw('SUM(price * quantity) as total'))
            ->whereBetween('sold_at', [$start, $end])
            ->groupBy('day')
            ->orderByDesc('total')
            ->first();

        $pdf = FacadePdf::loadView('sales.report_pdf', [
            'month' => $month,
            'totalRevenue' => $totalRevenue,
            'topProduct' => $topProduct,
            'topDay' => $topDay
        ]);

        return $pdf->download('Monthly_Sales_Report_' . $month . '.pdf');
    }

    public function sendDailyReport() {
        $today = Carbon::today();
        $sales = Sale::whereDate('sold_at', $today)->get();

        $totalRevenue = $sales->sum(function ($sale) {
            return $sale->price * $sale->quantity;
        });

        $lowStockProducts = Product::whereColumn('quantity', '<=', 'stock_threshold')->get();


        $expiringSoon = Product::whereBetween('expiry_date', [$now, $sixMonthsFromNow])->get();

        $pdf = FacadePdf::loadView('sales.daily_report', [
            'sales' => $sales,
            'totalRevenue' => $totalRevenue,
            'lowStockProducts' => $lowStockProducts,
            'expiringSoon' => $expiringSoon,
            'date' => $today->toFormattedDateString()
        ]);

        $fileName = 'daily_report_'. $today->format('Y_m_d'). '.pdf';
        $filePath = storage_path('app/' . $fileName);

        file_put_contents($filePath, $pdf->output());
        $caption = "📄 Daily Sales Report for " . $today->toFormattedDateString();
        //send to telegram
        TelegramHelper::sendTelegramMessageFile($filePath, $fileName, $caption);
        unlink($filePath);

        return back()->with('success', 'Daily report send to Telegram');

    }



}
