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
use \DateTime;

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
            else{
                $uniqueEmailValidation = $this->uniqueEmployeeValidation('email', $dummyEmployee->email);
                if(!$uniqueEmailValidation){
                    $valid = false;
                    $errors[] = "The email is not unique";
                }
            }
            
            $mobileValidation = $this->mobileValidation($dummyEmployee->mobile);
            if(!$mobileValidation){
                $valid = false;
                $errors[] = "The mobile field should contain exact 10 digits";
            }
            else{
                $uniqueMobileValidation = $this->uniqueEmployeeValidation('mobile', $dummyEmployee->mobile);
                if(!$uniqueMobileValidation){
                    $valid = false;
                    $errors[] = "The mobile is not unique";
                }
            }
            
            $staffIdValidation = $this->staffIdValidation($dummyEmployee->staff_id);
            if(!$staffIdValidation){
                $valid = false;
                $errors[] = "The staff Id field should contain exact 5 digits";
            }
            else{
                $uniqueStaffIdValidation = $this->uniqueEmployeeValidation('staff_id', $dummyEmployee->staff_id);
                if(!$uniqueStaffIdValidation){
                    $valid = false;
                    $errors[] = "The staff Id is not unique";
                }
            }

            $placeValidation = $this->nameValidation($dummyEmployee->place);
            if(!$placeValidation){
                $valid = false;
                $errors[] = "The place field is invalid";
            }

            $dateValidation = $this->dateValidation($dummyEmployee->dob);
            if(!$dateValidation){
                $valid = false;
                $errors[] = "The dob field is invalid";
            }

            $designationValidation = $this->nameValidation($dummyEmployee->designation);
            if(!$designationValidation){
                $valid = false;
                $errors[] = "The designation field is invalid";
            }

            if(!$valid){
                $errorString = implode(',',$errors);
                $dummyEmployee->errors = $errorString;
                $dummyEmployee->is_processed = 0;
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

                $dummyEmployee->is_processed = 1;
                $dummyEmployee->save();
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
    
    // public function uniqueStaffIdValidation($staffId){
        
    //     $checkStaffId = Employee::where('staff_id',$staffId)
    //                     ->count();

    //     if($checkStaffId>0){
    //         return false;
    //     }
    //     else{
    //         return true;
    //     }
    // }
    
    public function uniqueEmployeeValidation($fieldName, $fieldValue){
        
        $checkEmployee = Employee::where($fieldName, $fieldValue)
                        ->count();

        if($checkEmployee>0){
            return false;
        }
        else{
            return true;
        }
    }

    public function dateValidation($date, $format = 'Y-m-d'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

}
