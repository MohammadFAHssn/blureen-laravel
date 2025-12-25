<?php
namespace App\Http\Controllers\HrRequest;
use App\Exceptions\CustomException;
use App\Http\Requests\HrRequest\CreateHrRequest;
use App\Services\HrRequest\HrRequestService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HrRequestController
{
    protected HrRequestService $hrRequestService;

    public function __construct(HrRequestService $hrRequestService)
    {
        $this->hrRequestService = $hrRequestService;
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

    public function update(Request $request)
    {
        return response()->json([
            'data' => $this->hrRequestService->update($request->toArray())
        ]);
    }

    public function getUserRequestsOfCurrentMonth(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->hrRequestService->getUserRequestsOfCurrentMonth($request->toArray())
        ]);
    }

}
