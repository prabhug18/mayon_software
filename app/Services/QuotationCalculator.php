<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationItem;

class QuotationCalculator
{
    /**
     * Calculate selling rate from base cost and margin
     *
     * @param float $baseCost
     * @param string $marginType (PERCENTAGE or FIXED)
     * @param float $marginValue
     * @return float
     */
    public function calculateSellingRate(float $baseCost, string $marginType, float $marginValue): float
    {
        if ($marginType === 'PERCENTAGE') {
            return $baseCost + ($baseCost * ($marginValue / 100));
        }
        
        // FIXED margin
        return $baseCost + $marginValue;
    }

    /**
     * Calculate line total including GST
     *
     * @param float $sellingRate
     * @param float $quantity
     * @param string $unit
     * @param float $gstPercentage
     * @return float
     */
    public function calculateLineTotal(float $sellingRate, float $quantity, string $unit, float $gstPercentage): float
    {
        // For LS (lump sum), ignore quantity multiplication
        $subtotal = ($unit === 'LS') ? $sellingRate : ($sellingRate * $quantity);
        
        // Add GST
        $gstAmount = $subtotal * ($gstPercentage / 100);
        
        return $subtotal + $gstAmount;
    }

    /**
     * Calculate quotation totals from items
     *
     * @param \Illuminate\Support\Collection $quotationItems
     * @return array ['subtotal' => float, 'gst_total' => float, 'grand_total' => float]
     */
    public function calculateQuotationTotals($quotationItems): array
    {
        $subtotal = 0;
        $gstTotal = 0;

        foreach ($quotationItems as $item) {
            $itemSubtotal = ($item->unit === 'LS') 
                ? $item->selling_rate 
                : ($item->selling_rate * $item->quantity);
            
            $itemGst = $itemSubtotal * ($item->gst_percentage / 100);
            
            $subtotal += $itemSubtotal;
            $gstTotal += $itemGst;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'gst_total' => round($gstTotal, 2),
            'grand_total' => round($subtotal + $gstTotal, 2)
        ];
    }

    /**
     * Recalculate all totals for a quotation and update
     *
     * @param Quotation $quotation
     * @return Quotation
     */
    public function recalculateQuotation(Quotation $quotation): Quotation
    {
        $quotation->load('items');
        
        $totals = $this->calculateQuotationTotals($quotation->items);
        
        $quotation->update([
            'subtotal' => $totals['subtotal'],
            'gst_total' => $totals['gst_total'],
            'grand_total' => $totals['grand_total']
        ]);

        return $quotation->fresh();
    }

    /**
     * Calculate and populate item totals
     *
     * @param array $itemData
     * @return array
     */
    public function calculateItemData(array $itemData): array
    {
        // Calculate selling rate if base cost and margin provided
        if (isset($itemData['base_cost'], $itemData['margin_type'], $itemData['margin_value'])) {
            $itemData['selling_rate'] = $this->calculateSellingRate(
                (float) $itemData['base_cost'],
                $itemData['margin_type'],
                (float) $itemData['margin_value']
            );
        }

        // Calculate line total
        if (isset($itemData['selling_rate'], $itemData['quantity'], $itemData['unit'], $itemData['gst_percentage'])) {
            $itemData['line_total'] = $this->calculateLineTotal(
                (float) $itemData['selling_rate'],
                (float) $itemData['quantity'],
                $itemData['unit'],
                (float) $itemData['gst_percentage']
            );
        }

        return $itemData;
    }
}
