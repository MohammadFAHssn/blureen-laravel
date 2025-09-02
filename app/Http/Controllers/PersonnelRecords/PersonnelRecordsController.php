<?php
namespace App\Http\Controllers\PersonnelRecords;
use App\Exceptions\CustomException;
use App\Services\PersonnelRecords\PersonnelRecordsService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonnelRecordsController
{
    protected PersonnelRecordsService $personnelRecordsService;

    public function __construct(PersonnelRecordsService $personnelRecordsService)
    {
        $this->personnelRecordsService = $personnelRecordsService;
    }

    /**
     * @throws CustomException
     * @throws ConnectionException
     */
    public function getPersonnelRecords(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->personnelRecordsService->getPersonnelRecords($request->toArray())
        ]);

    }
}
