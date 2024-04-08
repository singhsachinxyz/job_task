<?php

namespace App\Exports;

use App\Models\DummyEmployee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DummyEmployeesExport implements FromCollection, WithHeadings, WithStrictNullComparison, ShouldAutoSize
{

    protected $id;
    
    public function __construct($id){
        $this->id = $id;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $id = $this->id;
        return DummyEmployee::where('request_id', $id)
        ->select('name as Name','email as Email','mobile as Mobile','staff_id as Staff Id','place as Place',
        'dob as DOB','designation as Designation','errors as Errors','is_processed as Is Processed')
        ->get();
    }

    public function headings(): array
    {
        return array_keys($this->collection()->first()->toArray());
    }
}
