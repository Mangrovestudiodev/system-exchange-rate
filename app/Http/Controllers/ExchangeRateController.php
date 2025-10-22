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

            $today = now()->toDateString();
            $queryDate = $dateParam ?? $today;

            if ($dateParam) {
                $localDate = Carbon::parse($queryDate, config('app.timezone', 'UTC'));
                $start = $localDate->copy()->startOfDay()->utc();
                $end = $localDate->copy()->endOfDay()->utc();

            } else {
                $start = now()->startOfDay()->utc();
                $end = now()->endOfDay()->utc();
            }

            $rates = ExchangeRate::whereBetween('created_at', [$start, $end])->orderBy('created_at', 'desc')->get();

            if ($rates->isEmpty()) {
                $isToday = $queryDate === $today;

                if (!$isToday && $dateParam) {
                    $closestRate = ExchangeRate::where('created_at', '<=', $end)->orderBy('created_at', 'desc')->first();

                    if (!$closestRate) {
                        return response()->json([
                            'success' => false,
                            'message' => "No exchange rates found for {$queryDate} or any earlier date",
                        ], 404);
                    }

                    $lastDate = $closestRate->created_at->toDateString();
                    $lastRates = ExchangeRate::whereDate('created_at', $lastDate)->orderBy('created_at', 'desc')->get();

                    return response()->json([
                        'success' => true,
                        'note' => "No rates found for {$queryDate}, showing last available date: {$lastDate}",
                        'data' => $this->groupRates($lastRates),
                    ], 200);
                }

                // Get the most recent available rates
                $lastRate = ExchangeRate::latest('created_at')->first();
                if (!$lastRate) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No exchange rates found at all.',
                    ], 404);
                }

                $lastDate = $lastRate->created_at->toDateString();
                $lastRates = ExchangeRate::whereDate('created_at', $lastDate)->orderBy('created_at', 'desc')->get();

                return response()->json([
                    'success' => true,
                    'note' => "No rates found for today, showing last available date: {$lastDate}",
                    'data' => $this->groupRates($lastRates),
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => $this->groupRates($rates),
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch exchange rate',
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

