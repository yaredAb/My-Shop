<?php

namespace App\Helper;

use App\Models\Product;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportHelper
{

    public static function sendDailyReport() {
        $today = Carbon::today();
        $sales = Sale::whereDate('sold_at', $today)->get();

        $totalRevenue = $sales->sum(function ($sale) {
            return $sale->price * $sale->quantity;
        });

        $lowStockProducts = Product::whereColumn('quantity', '<=', 'stock_threshold')->get();

        $now = Carbon::now();
        $sixMonthsFromNow = Carbon::now()->addMonth(6);
        $expiringSoon = Product::whereBetween('expiry_date', [$now, $sixMonthsFromNow])->orderBy('expiry_date', 'asc')->get();

        $pdf = Pdf::loadView('sales.daily_report', [
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
