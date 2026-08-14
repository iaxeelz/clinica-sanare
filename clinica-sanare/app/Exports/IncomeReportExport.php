<?php

namespace App\Exports;

use App\Models\Income;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomeReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        return Income::with(['patient', 'service', 'doctor.user'])
            ->whereBetween('payment_date', [$this->startDate, $this->endDate])
            ->orderBy('payment_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha',
            'Paciente',
            'DNI',
            'Servicio',
            'Médico',
            'Precio Costo (S/)',
            'Precio Venta (S/)',
            'Margen (S/)',
            'Monto Pagado (S/)',
            'Vuelto (S/)',
            'Pago Médico (S/)',
            'Método Pago',
            'N° Boleta',
            'N° Factura',
            'Registrado por',
        ];
    }

    public function map($income): array
    {
        $margen = $income->sale_price - $income->cost_price;
        
        return [
            $income->id,
            $income->payment_date->format('d/m/Y'),
            $income->patient->full_name,
            $income->patient->dni,
            $income->service->name,
            $income->doctor->full_name,
            number_format($income->cost_price, 2),
            number_format($income->sale_price, 2),
            number_format($margen, 2),
            number_format($income->amount_paid, 2),
            number_format($income->change_amount, 2),
            number_format($income->doctor_payment, 2),
            $income->payment_method_text,
            $income->receipt_number ?? '',
            $income->invoice_number ?? '',
            $income->user->name ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']]],
            'A1:P1' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1A5276']]],
        ];
    }
}