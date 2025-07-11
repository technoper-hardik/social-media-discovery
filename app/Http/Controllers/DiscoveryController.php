<?php

namespace App\Http\Controllers;

use App\Jobs\ExtractCompanyDetails;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DiscoveryController extends Controller
{
    public function show(Company $company): JsonResponse
    {
        if ($company->status == 'finished') {
            return response()->json([
                'status' => 'finished',
                'data' => $company->handles()->with('socialAccounts')->get(),
            ]);
        }

        return response()->json([
            'status' => $company->status,
        ]);
    }

    public function create(): JsonResponse
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required|string',
            'website' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $company = Company::query()->updateOrCreate([
            'name' => request('name')
        ], [
            'website' => request('website')
        ]);

        ExtractCompanyDetails::dispatch($company, true);

        return response()->json([
            'process_id' => $company->id,
        ]);
    }
}
