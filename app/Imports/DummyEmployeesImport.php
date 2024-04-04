<?php

namespace App\Imports;

use App\Models\DummyEmployee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Importable;

class DummyEmployeesImport implements ToModel, WithHeadingRow, WithChunkReading, ShouldQueue
{
    use Importable;
    protected $requestId;
    
    public function __construct($requestId){
        $this->requestId = $requestId;
    }
    

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $reqId = $this->requestId;

        return new DummyEmployee([
            'name' => $row['name'],
            'email' => $row['email'],
            'mobile' => $row['mobile'],
            'staff_id' => $row['staff_id'],
            'place' => $row['place'],
            'dob' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(intval($row['dob'])),
            'designation' => $row['designation'],
            'request_id' => $reqId,
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
