<?php
namespace App\Http\Controllers\KasraController;
use App\Exceptions\CustomException;
use App\Http\Requests\Base\UserIdRequest;
use App\Http\Requests\HrRequest\GetEmployeeAttendanceRequest;
use App\Services\Api\KasraService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;

class KasraController
{
    protected KasraService $kasraService;

    public function __construct(KasraService $kasraService)
    {
        $this->kasraService = $kasraService;
    }

    /**
     * @throws ConnectionException|CustomException
     */
    public function getEmployeeAttendanceReport(GetEmployeeAttendanceRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->kasraService->getEmployeeAttendanceReport($request->validated())
        ]);
    }


    /**
     * @throws ConnectionException|CustomException
     */
    public function getRemainingLeave(UserIdRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->kasraService->getRemainingLeave($request['user_id'])
        ]);
    }



}
