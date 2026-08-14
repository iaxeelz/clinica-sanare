<?php

namespace App\Exports;

use App\Models\Appointment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DoctorReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $doctorId;

    public function __construct($startDate, $endDate, $doctorId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->doctorId = $doctorId;
    }

    public function query()
    {
        $query = Appointment::with(['patient', 'doctor.user', 'service'])
            ->whereBetween('appointment_date', [$this->startDate, $this->endDate]);

        if ($this->doctorId) {
            $query->where('doctor_id', $this->doctorId);
        }

        return $query->orderBy('appointment_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha',
            'Hora',
            'Paciente',
            'DNI Paciente',
            'Médico',
            'Servicio',
            'Estado',
            'Motivo',
            'Registrado por',
        ];
    }

    public function map($appointment): array
    {
        return [
            $appointment->id,
            $appointment->appointment_date->format('d/m/Y'),
            \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A'),
            $appointment->patient->full_name,
            $appointment->patient->dni,
            $appointment->doctor->full_name,
            $appointment->service->name,
            $appointment->status_text,
            $appointment->reason ?? '',
            $appointment->user->name ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']]],
            'A1:J1' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1A5276']]],
        ];
    }
}