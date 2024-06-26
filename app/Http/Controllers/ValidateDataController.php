<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DummyEmployeesImport;
use App\Exports\EmployeesExport;
use App\Exports\DummyEmployeesExport;
use App\Jobs\NewJob;
use Illuminate\Support\Facades\Storage;

class ValidateDataController extends Controller
{
    public function postExcel(Request $request){
        // dd($request->file);
        $file = $request->file;
        $path = 'excel/file'.$request->request_id.'.xlsx';
        Storage::disk('local')->put($path, file_get_contents($file));
        
        // NewJob::dispatch($path, $request->request_id);

        (new DummyEmployeesImport($request->request_id))->queue(storage_path('app/' . $path))
                                                    ->chain([
                                                        new NewJob($path, $request->request_id)
                                                    ]);

        return 'post';
    }

    public function exportAllEmployees(Request $request){
        $path = 'excel/all-employees.xlsx';
        $store = Excel::store(new EmployeesExport, $path, 'public');
        
        // return Storage::url($path);
        return asset('storage/' . $path);
    }

    public function exportDummyEmployees(Request $request, $id){
        $path = 'excel/dummy-employees'.$id.'.xlsx';
        $store = Excel::store(new DummyEmployeesExport($id), $path, 'public');
        
        // return Storage::url($path);
        return asset('storage/' . $path);
    }
}
