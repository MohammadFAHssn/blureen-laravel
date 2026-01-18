<?php

use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);

    Route::post('/login-supplier', [\App\Http\Controllers\Auth\AuthController::class, 'loginSupplier']);

    Route::post('/verify-supplier-otp', [\App\Http\Controllers\Auth\AuthController::class, 'verifySupplierOtp']);

    Route::post('/get-otp-code', [\App\Http\Controllers\Auth\AuthController::class, 'getOtpCode']);

    Route::post('/verify-user-otp', [\App\Http\Controllers\Auth\AuthController::class, 'verifyUserOtp']);

    Route::controller(\App\Http\Controllers\Commerce\TenderController::class)->prefix('/commerce/tender')->group(function () {
        Route::get('/get-by-token', 'getByToken');
        Route::post('/submit-bid', 'submitBid');
    });

    Route::middleware('JwtMiddleware')->group(function () {

        Route::controller(\App\Http\Controllers\Commerce\TenderController::class)->prefix('/commerce/tender')->group(function () {
            Route::get('/get-actives', 'getActives')->middleware('permission:read Active-Tenders');
        });

        Route::prefix('/base')->group(function () {
            Route::controller(\App\Http\Controllers\Base\UserRoleController::class)->prefix('/user-role')->group(function () {
                Route::post('/update', 'update')->middleware('permission:edit User-Roles');
            });

            Route::controller(\App\Http\Controllers\Base\UserController::class)->prefix('/user')->group(function () {
                Route::get('/approval-flows-as-requester', 'getApprovalFlowsAsRequester')->middleware('permission:read Approval-Flows');
                Route::post('/reset-password', 'resetPassword');
                Route::get('/details', 'getDetails')->middleware('permission:read User-Details');
            });

            Route::controller(\App\Http\Controllers\Base\ApprovalFlowController::class)->prefix('/approval-flow')->group(function () {
                Route::post('/update', 'update')->middleware('permission:edit Approval-Flows');
            });

            Route::controller(\App\Http\Controllers\Base\OrgChartNodeController::class)->prefix('/org-chart-node')->group(function () {
                // TODO: middleware
                Route::get('', 'get')->middleware('permission:read Organization-Chart');
                Route::get('/user-subordinates', 'getUserSubordinates');
                Route::put('/update', 'update')->middleware('permission:edit Organization-Chart');
            });

            Route::controller(\App\Http\Controllers\Base\FileController::class)->prefix('/file')->group(function () {
                Route::post('/upload-bulk-avatars', 'uploadBulkAvatars')->middleware('role:Super Admin');
            });
        });

        Route::prefix('/payroll')->group(function () {
            Route::controller(\App\Http\Controllers\Payroll\PayrollBatchController::class)->prefix('/payroll-batch')->group(function () {
                Route::post('/create', 'create')->middleware('permission:create Payroll-Batch');
                Route::delete('/', 'delete')->middleware('permission:delete Payroll-Batches');
            });

            Route::controller(\App\Http\Controllers\Payroll\PayrollSlipController::class)->prefix('/payroll-slip')->group(function () {
                Route::get('/get-the-last-few-months', 'getTheLastFewMonths')->middleware('role:Super Admin|employee');
                Route::get('/print', 'print')->middleware('role:Super Admin|employee');
                Route::get('reports', 'getReports')->middleware('permission:read Payroll-Batches');
            });
        });

        Route::controller(\App\Http\Controllers\PersonnelRecords\PersonnelRecordsController::class)->prefix('/personnel-records')->group(function () {
            Route::get('/get-by-personnel_code', 'getPersonnelRecords')->middleware('permission:read Personnel-Records');
        });

        Route::controller(\App\Http\Controllers\Survey\SurveyController::class)->prefix('/survey/survey')->group(function () {
            Route::post('/create', 'create')->middleware('permission:read Surveys');
            Route::post('/update', 'update')->middleware('permission:read Surveys');
            Route::delete('/', 'delete')->middleware('permission:read Surveys');
            Route::post('/participate', 'participate')->middleware('role:Super Admin|employee');
        });

        Route::prefix('/evaluation')->group(function () {
            Route::controller(\App\Http\Controllers\Evaluation\EvaluateeController::class)->prefix('/evaluatee')->group(function () {
                Route::get('/by-evaluator', 'getByEvaluator')->middleware('role:Super Admin|employee');
            });

            Route::controller(\App\Http\Controllers\Evaluation\EvaluationQuestionController::class)->prefix('/evaluation-question')->group(function () {
                Route::get('/actives', 'getActives')->middleware('role:Super Admin|employee');
                Route::get('/self-evaluation', 'getSelfEvaluation')->middleware('role:Super Admin|employee');
            });

            Route::controller(\App\Http\Controllers\Evaluation\SelfEvaluationController::class)->prefix('/self-evaluation')->group(function () {
                Route::post('create', 'evaluate')->middleware('role:Super Admin|employee');
            });

            Route::controller(\App\Http\Controllers\Evaluation\EvaluationScoreController::class)->prefix('/evaluation-score')->group(function () {
                Route::post('create', 'evaluate')->middleware('role:Super Admin|employee');
            });
        });

        // Birthday Routes
        Route::prefix('birthday')->group(function () {
            Route::controller(\App\Http\Controllers\Birthday\BirthdayGiftController::class)->prefix('gift')->group(function () {
                Route::post('/', 'store')->middleware('permission:read Birthdays');
                Route::get('/', 'index')->middleware('permission:read Birthdays');
                Route::get('/get-actives', 'getActives')->middleware('permission:use app');
                Route::post('/{id}', 'update')->middleware('permission:read Birthdays');
                Route::delete('/{id}', 'delete')->middleware('permission:read Birthdays');
            });

            Route::controller(\App\Http\Controllers\Birthday\BirthdayFileController::class)->prefix('file')->group(function () {
                Route::post('/', 'store')->middleware('permission:read Birthdays');
                Route::get('/', 'index')->middleware('permission:read Birthdays');
                Route::post('/{id}', 'update')->middleware('permission:read Birthdays');
                Route::delete('/{id}', 'delete')->middleware('permission:read Birthdays');
                Route::get('/statistics', 'statistics')->middleware('permission:read Birthdays');
            });

            Route::controller(\App\Http\Controllers\Birthday\BirthdayFileUserController::class)->prefix('user')->group(function () {
                Route::post('/', 'store')->middleware('permission:read Birthdays');
                Route::get('/', 'index')->middleware('permission:read Birthdays');
                Route::delete('/delete', 'delete')->middleware('permission:read Birthdays');
                Route::post('/status', 'changeStatus')->middleware('permission:read Birthdays');
                Route::post('/choose', 'chooseBirthdayGift')->middleware('permission:use app');
                Route::get('/check', 'checkAccess')->middleware('permission:use app');
            });
        });

        // HSE Routes
        Route::prefix('hse')->group(function () {
            Route::prefix('health-certificate')->group(function () {
                Route::controller(\App\Http\Controllers\HSE\HealthCertificateController::class)->prefix('file')->group(function () {
                    Route::post('/', 'store')->middleware('permission:read Health-Certificate');
                    Route::get('/', 'index')->middleware('permission:read Health-Certificate');
                    Route::post('/image', 'image')->middleware('permission:read Health-Certificate');
                    Route::post('/{id}', 'update')->middleware('permission:read Health-Certificate');
                    Route::delete('/{id}', 'delete')->middleware('permission:read Health-Certificate');
                    Route::get('/{id}', 'show')->middleware('permission:read Health-Certificate');
                });

                Route::controller(\App\Http\Controllers\HSE\HealthCertificateUserController::class)->prefix('user')->group(function () {
                    Route::get('/image', 'getImage')->middleware('permission:use app');
                    Route::get('/image/download', 'downloadImage')->middleware('permission:use app');
                });
            });
        });

        // Food Routes
        Route::prefix('food')->group(function () {
            Route::controller(\App\Http\Controllers\Food\Kitchen\FoodController::class)->prefix('food')->group(function () {
                Route::get('/', 'index')->middleware('permission:read Kitchen|edit Food-Price');
                Route::get('/get-actives', 'getActives')->middleware('permission:read Kitchen');
                Route::post('/', 'store')->middleware('permission:read Kitchen');
                Route::post('/status/{id}', 'changeStatus')->middleware('permission:edit Food-Status');
                Route::post('/{id}', 'update')->middleware('permission:edit Food-Price');
                Route::delete('/{id}', 'delete')->middleware('permission:read Kitchen');
            });
            Route::controller(\App\Http\Controllers\Food\Kitchen\MealController::class)->prefix('meal')->group(function () {
                Route::get('/', 'index')->middleware('permission:read Kitchen');
                Route::get('/get-actives', 'getActives')->middleware('permission:read Reserve-Food|read Kitchen|read Food-Report');
                Route::post('/', 'store')->middleware('permission:read Kitchen');
                Route::post('/status/{id}', 'changeStatus')->middleware('permission:read Kitchen');
                Route::post('/{id}', 'update')->middleware('permission:read Kitchen');
                Route::delete('/{id}', 'delete')->middleware('permission:read Kitchen');
            });
            Route::controller(\App\Http\Controllers\Food\Kitchen\MealPlanController::class)->prefix('meal-plan')->group(function () {
                Route::get('/get-for-date', 'plansForDate')->middleware('permission:read Kitchen');
                Route::post('/', 'store')->middleware('permission:read Kitchen');
                Route::post('/{id}', 'update')->middleware('permission:read Kitchen');
            });
            Route::controller(\App\Http\Controllers\Food\Reservation\MealReservationController::class)->prefix('meal-reservation')->group(function () {
                Route::get('/get-for-personnel-by-user-on-date', 'reservationsForPersonnelByUserOnDate')->middleware('permission:read Reserve-Food');
                Route::get('/get-for-user-by-others-on-date', 'reservationsForUserByOthersOnDate')->middleware('permission:use app');
                Route::get('/get-for-contractor-on-date', 'reservationsForContractorByUserOnDate')->middleware('permission:read Reserve-Food');
                Route::get('/get-for-guest-on-date', 'reservationsForGuestByUserOnDate')->middleware('permission:read Reserve-Food');
                Route::get('/get-for-repairman-on-date', 'reservationsForRepairmanByUserOnDate')->middleware('permission:read Reserve-Food');
                Route::get('/get-in-date-range', 'reservationsInDateRange')->middleware('permission:read Kitchen');
                Route::get('/check-for-delivered', 'checkForDelivered')->middleware('permission:read Kitchen');
                Route::post('/', 'store')->middleware('permission:read Reserve-Food');
                Route::post('/{id}', 'update')->middleware('permission:read Reserve-Food');
                Route::delete('/{id}', 'delete')->middleware('permission:read Reserve-Food');
            });
            Route::controller(\App\Http\Controllers\Food\Reservation\MealReservationDetailController::class)->prefix('meal-reservation-detail')->group(function () {
                Route::delete('/{id}', 'delete')->middleware('permission:read Reserve-Food');
                Route::get('/get-for-specific-contractor-on-date', 'deliveredReservationsForContractorOnDate')->middleware('permission:read Contractor-Invoice');
            });
            Route::controller(\App\Http\Controllers\Food\Kitchen\FoodDeliveryController::class)->prefix('delivery')->group(function () {
                Route::get('/', 'index')->middleware('permission:read Kitchen');
                Route::get('/find', 'find')->middleware('permission:read Kitchen');
                Route::post('/', 'deliver')->middleware('permission:read Kitchen');
            });
            Route::controller(\App\Http\Controllers\Food\Rep\MealReservationExceptionController::class)->prefix('exception')->group(function () {
                Route::get('/', 'index')->middleware('permission:read Food-Report');
                Route::get('/get-actives', 'getActives')->middleware('permission:read Food-Report');
                Route::post('/', 'store')->middleware('permission:read Food-Report');
                Route::post('/status/{id}', 'changeStatus')->middleware('permission:read Food-Report');
                Route::delete('/{id}', 'delete')->middleware('permission:read Food-Report');
            });
            Route::controller(\App\Http\Controllers\Food\Rep\MealReservationEligibilityRuleController::class)->prefix('eligibility')->group(function () {
                Route::get('/', 'index')->middleware('permission:read Food-Report');
                Route::post('/', 'store')->middleware('permission:read Food-Report');
                Route::post('/{id}', 'update')->middleware('permission:read Food-Report');
                Route::delete('/{id}', 'delete')->middleware('permission:read Food-Report');
            });
            Route::controller(\App\Http\Controllers\Food\Rep\MealReservationContradictionController::class)->prefix('report')->group(function () {
                Route::get('/', 'index')->middleware('permission:read Food-Report');
            });
        });

        // Contractor Routes
        Route::prefix('contractor')->group(function () {
            Route::controller(\App\Http\Controllers\Contractor\ContractorController::class)->group(function () {
                Route::get('/', 'index')->middleware('permission:read Contractor');
                Route::post('/', 'store')->middleware('permission:read Contractor');
                Route::get('/get-actives', 'getActives')->middleware('permission:read Reserve-Food|read Contractor|read Contractor-Invoice');
                Route::post('/status/{id}', 'changeStatus')->middleware('permission:read Contractor');
            });
        });

        // TODO: add middleware
        Route::controller(\App\Http\Controllers\HrRequest\HrRequestController::class)->prefix('/hr-request')->group(function (){
           Route::post('/requests/create','create');
           Route::patch('/requests/update','update');
           Route::get('/requests/get-user-requests','getUserRequestsOfCurrentMonth');
        });
        Route::controller(\App\Http\Controllers\HrRequest\HrRequestApprovalController::class)->prefix('/hr-request')->group(function (){
            Route::get('/requests/get-by-approver','getApprovalRequestsByApprover');
            Route::post('/request/approve','approveRequest');
        });

        Route::controller(\App\Http\Controllers\KasraController\KasraController::class)->prefix('/kasra')->group(function (){
            Route::get('/reports/get-attendance-report','getEmployeeAttendanceReport');
            Route::get('/reports/get-remaining-leave','getRemainingLeave');
        });

        Route::controller(\App\Http\Controllers\Base\BaseController::class)->group(function () {
            Route::get('/{module}/{model_name}', 'get')->middleware('CheckPermission');
        });
    });
});
