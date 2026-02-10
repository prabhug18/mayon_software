<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

// helper for building absolute URLs
use Illuminate\Support\Facades\URL;

class PurchaseOrderInvoice extends Mailable
{
    use Queueable, SerializesModels;

    public $po;
    public $pdfData;

    /**
     * Create a new message instance.
     */
    public function __construct($po)
    {
        $this->po = $po;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $filename = ($this->po->po_number ?: 'purchase-order-') . '.pdf';

        // Attempt Puppeteer (headless Chrome) render of the show page for pixel-perfect output
        try {
            $showUrl = route('purchaseOrders.show', ['purchaseOrder' => $this->po->id]);
            $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'po_' . $this->po->id . '_' . time() . '.pdf';

            $nodeScriptCjs = base_path('tools/puppeteer-render.cjs');
            $nodeScriptJs = base_path('tools/puppeteer-render.js');
            $nodeScript = file_exists($nodeScriptCjs) ? $nodeScriptCjs : (file_exists($nodeScriptJs) ? $nodeScriptJs : null);

            if ($nodeScript) {
                $nodeBin = 'node';
                $cmd = $nodeBin . ' ' . escapeshellarg($nodeScript) . ' ' . escapeshellarg($showUrl) . ' ' . escapeshellarg($tmp);
                $output = [];
                $returnVar = null;
                exec($cmd . ' 2>&1', $output, $returnVar);
                Log::info('PurchaseOrderInvoice puppeteer attempt', ['cmd' => $cmd, 'return' => $returnVar, 'output' => $output]);
                if ($returnVar === 0 && file_exists($tmp)) {
                    $pdfData = file_get_contents($tmp);
                    // attach and cleanup
                    @unlink($tmp);
                    return $this->subject('Invoice / Purchase Order: ' . ($this->po->po_number ?: ''))
                                ->view('emails.purchaseOrderInvoice')
                                ->attachData($pdfData, $filename, ['mime' => 'application/pdf']);
                }
            } else {
                Log::info('Puppeteer script not found for PurchaseOrderInvoice; looked for: ' . $nodeScriptCjs . ' and ' . $nodeScriptJs);
            }
        } catch (\Exception $e) {
            Log::warning('Puppeteer render failed for PurchaseOrderInvoice: ' . $e->getMessage(), ['exception' => $e]);
            // fall through to dompdf
        }

        // Fallback to DOMPDF render of the PDF blade
        $pdf = Pdf::loadView('module.purchaseOrder.pdf', ['po' => $this->po])->setPaper('a4', 'portrait');

        // Ensure dompdf uses DejaVu and HTML5 parser and allows remote assets (images)
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        return $this->subject('Invoice / Purchase Order: ' . ($this->po->po_number ?: ''))
                    ->view('emails.purchaseOrderInvoice')
                    ->attachData($pdf->output(), $filename, [
                        'mime' => 'application/pdf',
                    ]);
    }
}
