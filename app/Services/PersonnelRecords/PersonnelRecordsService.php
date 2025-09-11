<?php

namespace App\Services\PersonnelRecords;


use App\Exceptions\CustomException;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PersonnelRecordsService
{

    /**
     * @throws CustomException
     * @throws ConnectionException
     */
    public function getPersonnelRecords($data): array
    {
        $personnelCode = $data['personnel_code'];
        $user = User::where('personnel_code',$personnelCode)
            ->with([
                'profile','profile.costCenter','profile.jobPosition','profile.educationLevel'
            ])->first();
        if(is_null($user))
            throw new CustomException('پرسنل یافت نشد.');

        $personnel_records = [];
        //profile
        $personnel_records['user_data'] = [
            'personnel_code' => $user->personnel_code,
            'full_name' => $user->first_name.' '.$user->last_name,
            'employment_date' => $user->profile?$user->profile->employment_date:'نامشخص',
            'unit' => ($user->profile && $user->profile->costCenter) ?$user->profile->costCenter->name:'نامشخص',
            'job_position' => ($user->profile && $user->profile->jobPosition) ?$user->profile->jobPosition->name:'نامشخص',
            'education_level' => ($user->profile && $user->profile->educationLevel)?$user->profile->educationLevel->name:'نامشخص',
        ];

        //education data
        $response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.legacy_integrated_system.get_completed_onboarding_courses'));
        $response !== false ? $personnel_records['education_data'] = $response:Log::error('خطا هنگام دریافت اطلاعات از سامانه آموزش - کدپرسنلی :'.$personnelCode);

        //reassignment data
        $response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.legacy_integrated_system.get_reassignment_data'));
        $response !== false ? $personnel_records['reassignment_data'] = $response:Log::error('خطا هنگام دریافت اطلاعات از سامانه جابجایی - کدپرسنلی :'.$personnelCode);

        //RewardFine data
        $response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.productivity_system.get_reward_and_fines_data'));
        $response !== false ? $personnel_records['productivity_data'] = $response:Log::error('خطا هنگام دریافت اطلاعات از سامانه بهروه وری - کدپرسنلی :'.$personnelCode);

        //assessment data
        $response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.payroll_system.get_assessment_data'));
        $response !== false ? $personnel_records['assessment_data'] = $response:Log::error('خطا هنگام دریافت اطلاعات از سامانه ارزیابی - کدپرسنلی :'.$personnelCode);

        //salary data
        $response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.payroll_system.get_payroll_data'));
        $response!==false ? $personnel_records['payroll_data'] = $response:Log::error('خطا هنگام دریافت اطلاعات از سامانه فیش حقوقی - کدپرسنلی :'.$personnelCode);

        //birthday gift data
        $response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.payroll_system.get_birthday_gift_data'));
        $response!==false ? $personnel_records['birthday_gift_data'] = $response:Log::error('خطا هنگام دریافت اطلاعات از سامانه هدیه تولد - کدپرسنلی :'.$personnelCode);

        //food reservation data
        $response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.food_reservation_system.get_food_reservation_data'));
        $response!==false ? $personnel_records['food_reserve_data'] = $response:Log::error('خطا هنگام دریافت اطلاعات از سامانه رزرو غذا - کدپرسنلی :'.$personnelCode);

        return $personnel_records;

    }


    /**
     * @throws ConnectionException
     */
    public function fetchPersonnelRecordsFromSystems($personnelCode, $url)
    {
        //todo: config other system for http requests
        $response = Http::withoutVerifying()->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get(
            $url. $personnelCode
        );
        if ($response->failed()) {
            return false;
        }
        else{
            return $response->json()['data'];
        }

    }
}
