<?php
namespace App\Http\Controllers\HrRequest;
use App\Exceptions\CustomException;
use App\Http\Requests\HrRequest\CreateHrRequest;
use App\Services\HrRequest\HrRequestService;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HrRequestController
{
    protected HrRequestService $hrRequestService;

    public function __construct()
    {
        $this->hrRequestService = new HrRequestService();
    }

    /**
     * @throws CustomException
     */
    public function create(CreateHrRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->hrRequestService->create($request->validated())
        ]);
    }

    /**
     * @throws CustomException
     * @throws ConnectionException
     */
    public function update(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->hrRequestService->update($request->toArray())
        ]);
    }


    /**
     * @throws Exception
     */
    public function delete(Request $request)
    {
        return response()->json([
            'data' => $this->hrRequestService->delete($request->toArray())
        ]);
    }

    public function getUserRequestsOfCurrentMonth(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->hrRequestService->getUserRequestsOfCurrentMonth($request->toArray())
        ]);
    }

    /**
     * @throws Exception
     */
    public function getApprovers(Request $request)
    {
        return response()->json([
            'data' => $this->hrRequestService->getApprovers($request->toArray())
        ]);
    }

    public function referral(Request $request)
    {
        return response()->json([
            'data' => $this->hrRequestService->referral($request->toArray())
        ]);
    }

}
