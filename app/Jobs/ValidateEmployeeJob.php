<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use App\Models\DummyEmployee;
use App\Models\Employee;
use Illuminate\Bus\Batchable;

class ValidateEmployeeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $offset;
    protected $batchSize;
    protected $requestId;

    /**
     * Create a new job instance.
     */
    public function __construct($offset, $batchSize, $requestId)
    {
        $this->offset = $offset;
        $this->batchSize = $batchSize;
        $this->requestId = $requestId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $offset = $this->offset;
        $batchSize = $this->batchSize;
        $requestId = $this->requestId;

        var_dump($offset);
        
        $getDummyEmployees = DummyEmployee::where('request_id',$this->requestId)
                                            ->offset($offset)
                                            ->limit($batchSize)
                                            ->orderBy('id')
                                            ->get();

        foreach($getDummyEmployees as $dummyEmployee){

            $valid = true;
            $errors = [];

            $nameValidation = $this->nameValidation($dummyEmployee->name);
            if(!$nameValidation){
                $valid = false;
                $errors[] = "The name field is invalid";
            }
            
            $emailValidation = $this->emailValidation($dummyEmployee->email);
            if(!$emailValidation){
                $valid = false;
                $errors[] = "The email field is invalid";
            }
            
            $mobileValidation = $this->mobileValidation($dummyEmployee->mobile);
            if(!$mobileValidation){
                $valid = false;
                $errors[] = "The mobile field is invalid";
            }
            
            $staffIdValidation = $this->staffIdValidation($dummyEmployee->staff_id);
            if(!$staffIdValidation){
                $valid = false;
                $errors[] = "The staff Id field is invalid";
            }
            else{
                $uniqueStaffIdValidation = $this->uniqueStaffIdValidation($dummyEmployee->staff_id);
                if(!$uniqueStaffIdValidation){
                    $valid = false;
                    $errors[] = "The staff Id is not unique";
                }
            }
            

            // $validator = Validator::make($dummyEmployee->toArray(), [
            //     'name' => 'required',
            //     'email' => 'required|email',
            //     'mobile' => 'required|numeric|digits:10',
            //     'staff_id' => 'required',
            //     'place' => 'required',
            //     'dob' => 'required|date',
            //     'designation' => 'required',
            // ]);

            if(!$valid){
                // $errors = $validator->errors()->toArray();
                // $errorsArray = [];
                // foreach($errors as $error){
                //     foreach($error as $e){
                //         $errorsArray[] = $e;
                //     }
                // }

                $errorString = implode(',',$errors);
                $dummyEmployee->errors = $errorString;
                $dummyEmployee->save();
            }
            else{
                
                $employee = new Employee;
                $employee->name = $dummyEmployee->name;
                $employee->email = $dummyEmployee->email;
                $employee->mobile = $dummyEmployee->mobile;
                $employee->staff_id = $dummyEmployee->staff_id;
                $employee->place = $dummyEmployee->place;
                $employee->dob = $dummyEmployee->dob;
                $employee->designation = $dummyEmployee->designation;
                
                $employee->save();
            }

        }
        
    }

    public function nameValidation($name){
        $regex = '/^[a-zA-Z]+[a-zA-Z ]*$/';
        $str = trim($name);
        if(preg_match($regex, $str)){
            return true;
        }
        else{
            return false;
        }
    }
    
    public function emailValidation($email){
        $regex = '/^[_a-zA-Z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        $str = trim($email);
        if(preg_match($regex, $str)){
            return true;
        }
        else{
            return false;
        }
    }

    public function mobileValidation($mobile){
        $regex = '/^\d{10}$/';
        if(preg_match($regex, $mobile)){
            return true;
        }
        else{
            return false;
        }
    }
    
    public function staffIdValidation($staffId){
        $regex = '/^\d{5}$/';
        if(preg_match($regex, $staffId)){
            return true;
        }
        else{
            return false;
        }
    }
    
    public function uniqueStaffIdValidation($staffId){
        
        $checkStaffId = Employee::where('staff_id',$staffId)
                        ->count();
        var_dump($checkStaffId);

        if($checkStaffId>0){
            return false;
        }
        else{
            return true;
        }
    }

}
