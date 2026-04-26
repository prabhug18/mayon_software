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

        return view('backend.general.dashboard', compact(
            'heading',
            'suppliersCount',
            'productsCount',
            'projectsCount',
            'poCurrentMonthCount',
            'recentPOs'
            ,'followUps', 'todayFollowUpCount', 'tomorrowFollowUpCount', 'pendingFollowUpCount', 'enquiryCount', 'quotationCount'
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
}
