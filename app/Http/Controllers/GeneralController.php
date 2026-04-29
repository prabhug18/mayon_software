<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Enquiry;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GeneralController extends Controller
{
    //
    public function dashboard()
    {
        $heading = 'Dashboard';
        // Totals
        $suppliersCount = Supplier::count();
        $productsCount = Product::count();
        $projectsCount = Project::count();
        // Purchase Order filters (affect only Purchase Orders & Recent list)
        $range = request('range', 'this_month');
        $from = request('from');
        $to = request('to');

        $now = Carbon::now();
        if ($range === 'previous_month') {
            $start = $now->copy()->subMonthNoOverflow()->startOfMonth();
            $end = $now->copy()->subMonthNoOverflow()->endOfMonth();
        } elseif ($range === 'last_6_months') {
            $start = $now->copy()->subMonths(5)->startOfMonth();
            $end = $now->copy()->endOfMonth();
        } elseif ($range === 'custom' && $from && $to) {
            try {
                $start = Carbon::parse($from)->startOfDay();
                $end = Carbon::parse($to)->endOfDay();
            } catch (\Exception $e) {
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
            }
        } else {
            // default: this month
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
        }

        $poCurrentMonthCount = PurchaseOrder::whereBetween('po_date', [$start, $end])->count();

        // Paginate recent POs with requested range (5 per page)
        $recentPOs = PurchaseOrder::with(['supplier','project'])
            ->whereBetween('po_date', [$start, $end])
            ->orderBy('po_date', 'desc')
            ->paginate(5)
            ->appends(request()->only(['range','from','to']));

        // Base follow-up query: enquiries with a next_follow_up_at and reminder not sent
        $baseFollowUpsQuery = Enquiry::with('source')
            ->whereNotNull('next_follow_up_at')
            ->whereNull('reminder_sent_at');

        // Follow-up alerts list: only those due now or in the past
        $followUps = (clone $baseFollowUpsQuery)
            ->where('next_follow_up_at', '<=', Carbon::now())
            ->orderBy('next_follow_up_at','asc')
            ->get();

        // Count of follow-ups scheduled for today (not yet reminded)
        $todayFollowUpCount = (clone $baseFollowUpsQuery)->whereDate('next_follow_up_at', Carbon::today())->count();

        // Count of follow-ups scheduled for tomorrow (not yet reminded)
        $tomorrowFollowUpCount = (clone $baseFollowUpsQuery)->whereDate('next_follow_up_at', Carbon::tomorrow())->count();

        // Total pending follow-ups (due or past, not yet reminded)
        $pendingFollowUpCount = (clone $baseFollowUpsQuery)->where('next_follow_up_at', '<=', Carbon::now())->count();

        // Total enquiries
        $enquiryCount = \App\Models\Enquiry::count();

        // Total quotations
        $quotationCount = Quotation::count();

        // Total Purchase Orders
        $poCount = PurchaseOrder::count();

        // --- Chart Data ---
        // 1. Source Breakdown (Doughnut)
        $sourceData = Enquiry::select('source_id', DB::raw('count(*) as total'))
            ->groupBy('source_id')
            ->with('source')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->source ? $item->source->name : 'Unknown',
                    'data' => $item->total
                ];
            });

        // 2. Trend (Last 30 Days) - Enquiries vs Quotations
        $startDate = $now->copy()->subDays(29)->startOfDay();
        
        $enquiriesTrend = Enquiry::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $quotationsTrend = Quotation::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $trendDates = [];
        $trendEnquiries = [];
        $trendQuotations = [];

        for ($i = 0; $i < 30; $i++) {
            $dateString = $startDate->copy()->addDays($i)->format('Y-m-d');
            $trendDates[] = Carbon::parse($dateString)->format('M d');
            $trendEnquiries[] = isset($enquiriesTrend[$dateString]) ? $enquiriesTrend[$dateString]->total : 0;
            $trendQuotations[] = isset($quotationsTrend[$dateString]) ? $quotationsTrend[$dateString]->total : 0;
        }

        $trendData = [
            'labels' => $trendDates,
            'enquiries' => $trendEnquiries,
            'quotations' => $trendQuotations
        ];

        // Recent snippets for bottom tables
        $recentEnquiries = Enquiry::with('enquiryType')->orderBy('created_at', 'desc')->take(5)->get();
        $recentQuotationsList = Quotation::orderBy('created_at', 'desc')->take(5)->get();

        return view('backend.general.dashboard', compact(
            'heading',
            'suppliersCount',
            'productsCount',
            'projectsCount',
            'poCurrentMonthCount',
            'recentPOs',
            'followUps', 'todayFollowUpCount', 'tomorrowFollowUpCount', 'pendingFollowUpCount', 'enquiryCount', 'quotationCount', 'poCount',
            'sourceData', 'trendData', 'recentEnquiries', 'recentQuotationsList'
        ));
    }

    /**
     * Return today's follow-ups as JSON for dashboard modal
     */
    public function todayFollowUps(Request $request)
    {
        $today = Carbon::today();

        $items = Enquiry::with('source')
            ->whereNotNull('next_follow_up_at')
            ->whereNull('reminder_sent_at')
            ->whereDate('next_follow_up_at', $today)
            ->orderBy('next_follow_up_at', 'asc')
            ->get()
            ->map(function($e){
                return [
                    'id' => $e->id,
                    'name' => $e->name,
                    'mobile' => $e->mobile,
                    'enquiry_type' => optional($e->enquiryType)->name,
                    'status' => $e->status,
                    'source' => optional($e->source)->name,
                    'next_follow_up_at' => optional($e->next_follow_up_at)->format('Y-m-d H:i:s'),
                    'next_follow_up_human' => optional($e->next_follow_up_at)->format('M j, H:i'),
                ];
            });

        return response()->json(['data' => $items]);
    }

    public function enquiriesList(Request $request)
    {
        $items = Enquiry::with('enquiryType', 'source')
            ->orderBy('created_at', 'desc')
            ->take(50) // Limit for performance in modal
            ->get()
            ->map(function($e){
                return [
                    'id' => $e->id,
                    'name' => $e->name,
                    'mobile' => $e->mobile,
                    'enquiry_type' => optional($e->enquiryType)->name,
                    'status' => $e->status,
                    'source' => optional($e->source)->name,
                    'created_at' => optional($e->created_at)->format('M j, Y')
                ];
            });

        return response()->json(['data' => $items]);
    }

    public function quotationsList(Request $request)
    {
        $items = Quotation::with('company')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(function($q){
                return [
                    'id' => $q->id,
                    'quotation_no' => $q->quotation_no,
                    'customer_name' => $q->customer_name,
                    'grand_total' => $q->grand_total,
                    'status' => $q->status,
                    'created_at' => optional($q->created_at)->format('M j, Y')
                ];
            });

        return response()->json(['data' => $items]);
    }

    public function purchaseOrdersList(Request $request)
    {
        $items = PurchaseOrder::with('supplier')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(function($po){
                return [
                    'id' => $po->id,
                    'po_number' => $po->po_number,
                    'supplier_name' => optional($po->supplier)->name,
                    'amount' => $po->amount,
                    'status' => $po->status,
                    'po_date' => optional($po->po_date)->format('M j, Y')
                ];
            });

        return response()->json(['data' => $items]);
    }
}
