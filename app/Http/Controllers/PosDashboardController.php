<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pos; // Adjust based on your actual Pos model namespace
use App\Models\Purchase; // Adjust based on your actual Purchase model namespace
use Carbon\Carbon;

class PosDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (\Auth::user()->can('show pos dashboard')) {
            if ($user->type == 'admin') {
                return view('admin.dashboard');
            } else {
                // Get POS data
                $pos_data = [];
                
                // Monthly POS amount (current month)
                $monthlyPosAmount = Pos::where('created_by', \Auth::user()->creatorId())
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total_amount');
                
                // Total POS amount
                $totalPosAmount = Pos::where('created_by', \Auth::user()->creatorId())
                    ->sum('total_amount');
                
                // Monthly purchase amount (current month)
                $monthlyPurchaseAmount = Purchase::where('created_by', \Auth::user()->creatorId())
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total_amount');
                
                // Total purchase amount
                $totalPurchaseAmount = Purchase::where('created_by', \Auth::user()->creatorId())
                    ->sum('total_amount');
                
                $pos_data = [
                    'monthlyPosAmount' => format_price($monthlyPosAmount),
                    'totalPosAmount' => format_price($totalPosAmount),
                    'monthlyPurchaseAmount' => format_price($monthlyPurchaseAmount),
                    'totalPurchaseAmount' => format_price($totalPurchaseAmount),
                ];
                
                // Get chart data for last 10 days
                $days = 10;
                $purchasesArray = ['label' => [], 'value' => []];
                $posesArray = ['label' => [], 'value' => []];
                
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $dateLabel = $date->format('M d');
                    
                    // Get purchase amount for the day
                    $purchaseAmount = Purchase::where('created_by', \Auth::user()->creatorId())
                        ->whereDate('created_at', $date)
                        ->sum('total_amount');
                    
                    // Get POS amount for the day
                    $posAmount = Pos::where('created_by', \Auth::user()->creatorId())
                        ->whereDate('created_at', $date)
                        ->sum('total_amount');
                    
                    $purchasesArray['label'][] = $dateLabel;
                    $purchasesArray['value'][] = $purchaseAmount;
                    
                    $posesArray['label'][] = $dateLabel;
                    $posesArray['value'][] = $posAmount;
                }
                
                return view('dashboard.pos-dashboard', compact('pos_data', 'purchasesArray', 'posesArray'));
            }
        } else {
            return redirect()->route('dashboard'); // Adjusted redirect, see note below
        }
    }
}