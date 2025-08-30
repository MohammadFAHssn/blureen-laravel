<?php
namespace App\Http\Controllers\PersonnelRecords;
use App\Exceptions\CustomException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PersonnelRecordsController
{
    /**
     * @throws CustomException
     * @throws ConnectionException
     */
    public function getPersonnelRecords(Request $request){
        $personnelCode = $request->query('personnel_code');
        $personnel_records = [];

        //education data
        /*$response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.legacy_integrated_system.get_completed_onboarding_courses'));
        $response ? $personnel_records[] = $response:throw new CustomException('خطا هنگام دریافت اطلاعات از سامانه جامع', 500);

        //introduction data
        $response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.legacy_integrated_system.get_introductionToUnit_data'));
        $response ? $personnel_records[] = $response:throw new CustomException('خطا هنگام دریافت اطلاعات از سامانه جامع', 500);

        //reassignment data
        $response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.legacy_integrated_system.get_reassignment_data'));
        $response ? $personnel_records[] = $response:throw new CustomException('خطا هنگام دریافت اطلاعات از سامانه جامع', 500);*/

        //RewardFine data
        //$response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.productivity_system.get_reward_and_fines_data'));
        //$response ? $personnel_records[] = $response:throw new CustomException('خطا هنگام دریافت اطلاعات از سامانه بهره وری', 500);

        //assessment data
        $response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.payroll_system.get_assessment_data'));
        $response ? $personnel_records[] = $response:throw new CustomException('خطا هنگام دریافت اطلاعات از سامانه فیش حقوقی', 500);

        //salary data
        $response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.payroll_system.get_payroll_data'));
        $response ? $personnel_records[] = $response:throw new CustomException('خطا هنگام دریافت اطلاعات از سامانه فیش حقوقی', 500);

        //birthday gift data
        $response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.payroll_system.get_birthday_gift_data'));
        $response ? $personnel_records[] = $response:throw new CustomException('خطا هنگام دریافت اطلاعات از سامانه فیش حقوقی', 500);

        //food reservation data
        //$response = $this->fetchPersonnelRecordsFromSystems($personnelCode,config('services.food_reservation_system.get_food_reservation_data'));
        //$response ? $personnel_records[] = $response:throw new CustomException('خطا هنگام دریافت اطلاعات از سامانه رزرو غذا', 500);

        return $personnel_records;

    }


    /**
     * @throws ConnectionException
     */
    public function fetchPersonnelRecordsFromSystems($personnelCode, $url)
    {
        $response = Http::withoutVerifying()->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get(
            $url. $personnelCode
        );
        if ($response->failed()) {
            return false;
        }
        else
            return $response->json()['data'];
    }
}
