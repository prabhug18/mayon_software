<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EnquirySampleExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'Name',
            'Phone',
            'Email',
            'Location',
            'Address',
            'GSTIN',
            'Service',
            'Call Status',
            'Feedback / Remarks',
            'Next Follow Up Date',
            'Priority',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Ramesh Kumar',
                '9876543210',
                'ramesh.k@example.com',
                'Bangalore',
                '12 MG Road, Indiranagar',
                '29AAAAA0000A1Z5',
                'Wooden Flooring',
                'Interested',
                'Customer requested product brochure and quotation',
                '2026-03-25',
                'High',
            ],
            [
                'Mohammed Ali',
                '9845012345',
                'ali.m@example.com',
                'Chennai',
                '45 Anna Salai',
                '',
                'Epoxy Flooring',
                'Call back later',
                'Call back after 2 weeks for warehouse requirement',
                '2026-04-05',
                'Medium',
            ],
            [
                'Priya Shetty',
                '9988776655',
                'priya.s@example.com',
                'Coimbatore',
                'Race Course Road',
                '',
                'Decking',
                'Appointment',
                'Site inspection appointment scheduled',
                '2026-03-30',
                'Urgent',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E40AF'], // Blue header
                ],
            ],
        ];
    }
}
