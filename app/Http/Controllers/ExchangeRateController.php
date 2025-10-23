<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Http\Requests\ExchangeRateRequest;
use App\Http\Resources\ExchangeRateResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class ExchangeRateController extends Controller
{
    public function store(ExchangeRateRequest $request){
        try {
            // Validation happens automatically here
            $validated = $request->validated();

            $referenceId = $validated['referenceId'] ?? Str::uuid()->toString();
            $createdRates = [];

            foreach ($validated['exchangeRates'] as $serviceType => $currencies) {
                foreach ($currencies as $currencyCode => $rates) {
                    $created = ExchangeRate::create([
                        'referenceId'  => $referenceId,
                        'service_type' => $serviceType,
                        'currency'     => $currencyCode,
                        'buy_rate'     => (float) $rates['buyRate'],
                        'sell_rate'    => (float) $rates['sellRate'],
                    ]);

                    $createdRates[] = $created;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Exchange rates saved successfully.',
                'referenceId' => $referenceId,
                'rows' => count($createdRates),
            ], 201);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to save exchange rates.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $dateParam = is_array($request->query('date')) ? $request->query('date')[0] : $request->query('date');

            $queryDate = $dateParam ? Carbon::parse($dateParam, config('app.timezone', 'UTC')) : now();

            $queryDateString = $queryDate->toDateString();

            $latestRate = ExchangeRate::whereRaw('DATE(created_at) <= ?', [$queryDateString])->orderBy('created_at', 'desc')->first();

            if (!$latestRate) {
                return response()->json([
                    'success' => false,
                    'message' => "No exchange rates found for {$queryDateString} or any earlier date.",
                ], 404);
            }

            $rateDate = Carbon::parse($latestRate->created_at)->toDateString();

            $rates = ExchangeRate::whereRaw('DATE(created_at) = ?', [$rateDate])->whereIn('id', function($query) use ($rateDate) {
                    $query->selectRaw('MAX(id)')->from('exchange_rates')->whereRaw('DATE(created_at) = ?', [$rateDate]) ->groupBy('service_type', 'currency');
            })->orderBy('created_at', 'desc')->get();

            $isRequestedDate = $rateDate === $queryDateString;

            return response()->json([
                'success' => true,
                'note' => $isRequestedDate ? null : "No rates found for {$queryDateString}, showing last available date: {$rateDate}",
                'data' => $this->groupRates($rates),
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch exchange rate',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function groupRates($rates)
    {
        $grouped = [];

        foreach ($rates as $rate) {
            $grouped[$rate->service_type][$rate->currency] = [
                'buyRate' => (float) $rate->buy_rate,
                'sellRate' => (float) $rate->sell_rate,
                'id' => $rate->id,
                'referenceId' => $rate->reference_id ?? null,
                'createdAt' => $rate->created_at ? $rate->created_at->toIso8601String() : null,
            ];
        }
        return $grouped;
    }

}

