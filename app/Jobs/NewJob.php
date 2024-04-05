<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DummyEmployeesImport;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ValidateEmployeeJob;
use App\Models\DummyEmployee;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Throwable;

class NewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $requestId;

    /**
     * Create a new job instance.
     */
    public function __construct($path, $requestId)
    {
        $this->path = $path;
        $this->requestId = $requestId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Excel::import(new DummyEmployeesImport($this->requestId), storage_path('app/' . $this->path));
        // request_id
        // count DummyEmployees where request_id //
        // init = 0;

        $dummyEmployeesCount = DummyEmployee::where('request_id',$this->requestId)->count();
        
        $batchSize = 200;
        $batchesToMake = [];

        $offset = 0;

        while($offset < $dummyEmployeesCount){
            $batchesToMake[] = $offset;
            $offset += $batchSize;
        }

        $jobBatches = [];
        foreach($batchesToMake as $offset){
            $job = new ValidateEmployeeJob($offset, $batchSize, $this->requestId);
            $jobBatches[] = $job;
        }

        var_dump('Batch Start');

        Bus::batch($jobBatches)
            ->name('Validate Employee')
            ->dispatch();

        // batchSize = 200;
        // batchesToMake = [];
        // while (init < count) {
        //     offset = 0;
        //     batchesToMake[] = offset
        //     offset+= batchSize;
        // }

        // jobBatches= [];
        // foreach (batchesToMake as offset) {
        //     $job = new validateEmployeeJob($offset, $batchSize, $requestId);
        //     jobBatches [] = job; 
        // }

    }
}
