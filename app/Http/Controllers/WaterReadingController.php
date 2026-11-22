<?php
// app/Http/Controllers/WaterReadingController.php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\WaterReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WaterReadingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get readings based on user role
        if ($user->hasAnyRole(['super_admin', 'admin', 'property_manager'])) {
            $readings = WaterReading::with(['unit.estate'])
                ->orderBy('reading_date', 'desc')
                ->get()
                ->map(function($reading) {
                    $unit = $reading->unit;
                    $billingType = $unit->water_billing_type ?? 'consumption';
                    $rate = $unit->custom_water_rate ?? $unit->estate->water_rate ?? 50;
                    
                    $consumption = $reading->consumption;
                    if ($consumption == 0 && $billingType === 'consumption') {
                        $consumption = $reading->current_reading - $reading->previous_reading;
                    }
                    
                    $charge = $reading->charge;
                    if ($charge == 0) {
                        if ($billingType === 'flat') {
                            $charge = $unit->water_charge ?? 0;
                        } else {
                            $charge = max(0, $consumption) * $rate;
                        }
                    }
                    
                    return [
                        'id' => $reading->id,
                        'unit_id' => $unit->id,
                        'unit_number' => $unit->unit_number ?? 'N/A',
                        'estate_name' => $unit->estate->name ?? 'N/A',
                        'previous_reading' => (float) $reading->previous_reading,
                        'current_reading' => (float) $reading->current_reading,
                        'consumption' => (float) max(0, $consumption),
                        'charge' => (float) max(0, $charge),
                        'reading_date' => $reading->reading_date,
                        'rate' => (float) $rate,
                        'water_billing_type' => $billingType,
                    ];
                });
        } elseif ($user->hasRole('meter_reader')) {
            $readings = WaterReading::with(['unit.estate'])
                ->orderBy('reading_date', 'desc')
                ->get()
                ->map(function($reading) {
                    $unit = $reading->unit;
                    $rate = $unit->custom_water_rate ?? $unit->estate->water_rate ?? 50;
                    
                    return [
                        'id' => $reading->id,
                        'unit_id' => $unit->id,
                        'unit_number' => $unit->unit_number ?? 'N/A',
                        'estate_name' => $unit->estate->name ?? 'N/A',
                        'previous_reading' => (float) $reading->previous_reading,
                        'current_reading' => (float) $reading->current_reading,
                        'consumption' => (float) $reading->consumption,
                        'charge' => (float) $reading->charge,
                        'reading_date' => $reading->reading_date,
                        'rate' => (float) $rate,
                    ];
                });
        } elseif ($user->hasRole('tenant') && $user->tenant && $user->tenant->activeTenancy) {
            $unitId = $user->tenant->activeTenancy->unit_id;
            $readings = WaterReading::where('unit_id', $unitId)
                ->with(['unit.estate'])
                ->orderBy('reading_date', 'desc')
                ->get()
                ->map(function($reading) {
                    $unit = $reading->unit;
                    $rate = $unit->custom_water_rate ?? $unit->estate->water_rate ?? 50;
                    
                    return [
                        'id' => $reading->id,
                        'unit_id' => $unit->id,
                        'unit_number' => $unit->unit_number ?? 'N/A',
                        'estate_name' => $unit->estate->name ?? 'N/A',
                        'previous_reading' => (float) $reading->previous_reading,
                        'current_reading' => (float) $reading->current_reading,
                        'consumption' => (float) $reading->consumption,
                        'charge' => (float) $reading->charge,
                        'reading_date' => $reading->reading_date,
                        'rate' => (float) $rate,
                    ];
                });
        } else {
            $readings = collect();
        }
        
        // Get units list for the modal
        $units = Unit::with('estate')
            ->where('is_active', true)
            ->get()
            ->map(function($unit) {
                return [
                    'id' => $unit->id,
                    'unit_number' => $unit->unit_number,
                    'estate_name' => $unit->estate->name ?? 'N/A',
                    'unit_type' => $unit->unit_type,
                    'water_billing_type' => $unit->water_billing_type ?? 'consumption',
                    'water_charge' => (float) ($unit->water_charge ?? 0),
                    'custom_water_rate' => (float) ($unit->custom_water_rate ?? 0),
                    'current_water_reading' => (float) ($unit->current_water_reading ?? 0),
                    'previous_water_reading' => (float) ($unit->previous_water_reading ?? 0),
                    'last_reading_date' => $unit->last_reading_date,
                ];
            });
        
        return view('water.index', compact('readings', 'units'));
    }

    /**
     * Store a single water reading - existing route
     * POST /water/readings
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'current_reading' => 'required|numeric|min:0',
            'reading_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        
        try {
            $unit = Unit::findOrFail($validated['unit_id']);
            $readingDate = Carbon::parse($validated['reading_date']);
            
            // Check if reading exists for this month
            $existingReading = WaterReading::where('unit_id', $unit->id)
                ->whereYear('reading_date', $readingDate->year)
                ->whereMonth('reading_date', $readingDate->month)
                ->first();
            
            if ($existingReading) {
                return response()->json([
                    'success' => false,
                    'duplicate' => true,
                    'message' => 'A water reading already exists for this month.',
                    'existing_reading' => [
                        'id' => $existingReading->id,
                        'reading_date' => $existingReading->reading_date->format('Y-m-d'),
                        'current_reading' => (float) $existingReading->current_reading,
                        'previous_reading' => (float) $existingReading->previous_reading,
                        'consumption' => (float) $existingReading->consumption,
                        'charge' => (float) $existingReading->charge,
                        'recorded_by' => $existingReading->recorded_by ? optional($existingReading->recordedBy)->name : 'System'
                    ]
                ], 409);
            }
            
            $previousReading = $unit->current_water_reading ?? 0;
            $currentReading = $validated['current_reading'];
            
            if ($currentReading < $previousReading) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current reading cannot be less than previous reading'
                ], 422);
            }
            
            $consumption = $currentReading - $previousReading;
            $rate = $unit->custom_water_rate ?? $unit->estate->water_rate ?? 50;
            
            if ($unit->water_billing_type === 'flat') {
                $charge = $unit->water_charge ?? 0;
                $consumption = 0;
            } else {
                $charge = $consumption * $rate;
            }
            
            $reading = WaterReading::create([
                'unit_id' => $unit->id,
                'previous_reading' => $previousReading,
                'current_reading' => $currentReading,
                'consumption' => $consumption,
                'rate_applied' => $rate,
                'charge' => $charge,
                'billing_type' => $unit->water_billing_type ?? 'consumption',
                'reading_date' => $validated['reading_date'],
                'recorded_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null
            ]);
            
            $unit->update([
                'previous_water_reading' => $previousReading,
                'current_water_reading' => $currentReading,
                'last_reading_date' => $validated['reading_date']
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Water reading recorded successfully',
                'reading' => $reading,
                'unit' => $unit
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving water reading: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record reading: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'exists:units,id',
            'current_reading' => 'required|numeric|min:0',
            'reading_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        
        $results = [
            'success' => [],
            'failed' => [],
            'skipped' => []
        ];
        
        try {
            foreach ($validated['unit_ids'] as $unitId) {
                $unit = Unit::find($unitId);
                if (!$unit) {
                    $results['failed'][] = ['unit_id' => $unitId, 'reason' => 'Unit not found'];
                    continue;
                }
                
                $readingDate = Carbon::parse($validated['reading_date']);
                
                // Check if reading already exists for this month
                $existingReading = WaterReading::where('unit_id', $unit->id)
                    ->whereYear('reading_date', $readingDate->year)
                    ->whereMonth('reading_date', $readingDate->month)
                    ->first();
                
                if ($existingReading) {
                    $results['skipped'][] = [
                        'unit_id' => $unitId,
                        'unit_number' => $unit->unit_number,
                        'reason' => 'Reading already exists for this month'
                    ];
                    continue;
                }
                
                // Get the most recent reading for this unit
                $latestReading = WaterReading::where('unit_id', $unit->id)
                    ->orderBy('reading_date', 'desc')
                    ->first();
                
                $previousReading = $latestReading 
                    ? $latestReading->current_reading 
                    : ($unit->current_water_reading ?? 0);
                
                $currentReading = $validated['current_reading'];
                
                if ($currentReading < $previousReading) {
                    $results['failed'][] = [
                        'unit_id' => $unitId,
                        'unit_number' => $unit->unit_number,
                        'reason' => 'Current reading less than previous'
                    ];
                    continue;
                }
                
                $consumption = $currentReading - $previousReading;
                $rate = $unit->custom_water_rate ?? $unit->estate->water_rate ?? 50;
                
                if ($unit->water_billing_type === 'flat') {
                    $charge = $unit->water_charge ?? 0;
                    $consumption = 0;
                } else {
                    $charge = $consumption * $rate;
                }
                
                // Create reading record
                $reading = WaterReading::create([
                    'unit_id' => $unit->id,
                    'previous_reading' => $previousReading,
                    'current_reading' => $currentReading,
                    'consumption' => $consumption,
                    'rate_applied' => $rate,
                    'charge' => $charge,
                    'billing_type' => $unit->water_billing_type ?? 'consumption',
                    'reading_date' => $validated['reading_date'],
                    'recorded_by' => auth()->id(),
                    'notes' => $validated['notes'] ?? null
                ]);
                
                // CRITICAL: Update the units table with new readings
                $unit->update([
                    'previous_water_reading' => $previousReading,
                    'current_water_reading' => $currentReading,
                    'last_reading_date' => $validated['reading_date']
                ]);
                
                $results['success'][] = [
                    'unit_id' => $unitId,
                    'unit_number' => $unit->unit_number
                ];
            }
            
            DB::commit();
            
            $message = count($results['success']) . ' reading(s) recorded successfully.';
            if (count($results['skipped']) > 0) {
                $message .= ' ' . count($results['skipped']) . ' skipped (already exist).';
            }
            if (count($results['failed']) > 0) {
                $message .= ' ' . count($results['failed']) . ' failed.';
            }
            
            return response()->json([
                'success' => count($results['success']) > 0,
                'message' => $message,
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving bulk readings: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record readings: ' . $e->getMessage()
            ], 500);
        }
    }


    public function storeBulkMatrix(Request $request)
    {
        $validated = $request->validate([
            'readings' => 'required|array|min:1',
            'readings.*.unit_id' => 'required|exists:units,id',
            'readings.*.current_reading' => 'required|numeric|min:0',
            'readings.*.reading_date' => 'required|date',
            'readings.*.existing_reading_id' => 'nullable|exists:water_readings,id',
            'notes' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        
        $results = [
            'success' => [],
            'failed' => [],
            'updated' => [],
            'skipped' => []
        ];
        
        try {
            // Group readings by unit to handle unit updates properly
            $unitReadings = [];
            foreach ($validated['readings'] as $readingData) {
                $unitReadings[$readingData['unit_id']][] = $readingData;
            }
            
            foreach ($unitReadings as $unitId => $readings) {
                $unit = Unit::find($unitId);
                if (!$unit) {
                    foreach ($readings as $reading) {
                        $results['failed'][] = [
                            'unit_id' => $unitId,
                            'reason' => 'Unit not found',
                            'month' => $reading['reading_date']
                        ];
                    }
                    continue;
                }
                
                // Sort readings by date (oldest first)
                usort($readings, function($a, $b) {
                    return strtotime($a['reading_date']) - strtotime($b['reading_date']);
                });
                
                $latestUnitReading = null;
                
                foreach ($readings as $readingData) {
                    $readingDate = Carbon::parse($readingData['reading_date']);
                    
                    // Check if reading already exists for this month
                    $existingReading = WaterReading::where('unit_id', $unit->id)
                        ->whereYear('reading_date', $readingDate->year)
                        ->whereMonth('reading_date', $readingDate->month)
                        ->first();
                    
                    if ($existingReading && !isset($readingData['existing_reading_id'])) {
                        $results['skipped'][] = [
                            'unit_id' => $unit->id,
                            'unit_number' => $unit->unit_number,
                            'month' => $readingDate->format('Y-m'),
                            'reason' => 'Reading already exists for this month'
                        ];
                        continue;
                    }
                    
                    // Get the previous reading (the reading before this one)
                    $previousReadingInHistory = WaterReading::where('unit_id', $unit->id)
                        ->where('reading_date', '<', $readingDate)
                        ->orderBy('reading_date', 'desc')
                        ->first();
                    
                    // Use the previous reading from the current batch if available
                    $previousReading = $previousReadingInHistory 
                        ? $previousReadingInHistory->current_reading 
                        : ($unit->current_water_reading ?? 0);
                    
                    $currentReading = $readingData['current_reading'];
                    
                    if ($currentReading < $previousReading) {
                        $results['failed'][] = [
                            'unit_id' => $unit->id,
                            'unit_number' => $unit->unit_number,
                            'month' => $readingDate->format('Y-m'),
                            'reason' => 'Current reading less than previous'
                        ];
                        continue;
                    }
                    
                    $consumption = $currentReading - $previousReading;
                    $rate = $unit->custom_water_rate ?? $unit->estate->water_rate ?? 50;
                    
                    if ($unit->water_billing_type === 'flat') {
                        $charge = $unit->water_charge ?? 0;
                        $consumption = 0;
                    } else {
                        $charge = $consumption * $rate;
                    }
                    
                    if ($existingReading && isset($readingData['existing_reading_id'])) {
                        // Update existing reading
                        $existingReading->update([
                            'previous_reading' => $previousReading,
                            'current_reading' => $currentReading,
                            'consumption' => $consumption,
                            'rate_applied' => $rate,
                            'charge' => $charge,
                            'notes' => $validated['notes'] ?? $existingReading->notes
                        ]);
                        $results['updated'][] = [
                            'unit_id' => $unit->id,
                            'unit_number' => $unit->unit_number,
                            'month' => $readingDate->format('Y-m')
                        ];
                        $latestUnitReading = $existingReading;
                    } else {
                        // Create new reading
                        $newReading = WaterReading::create([
                            'unit_id' => $unit->id,
                            'previous_reading' => $previousReading,
                            'current_reading' => $currentReading,
                            'consumption' => $consumption,
                            'rate_applied' => $rate,
                            'charge' => $charge,
                            'billing_type' => $unit->water_billing_type ?? 'consumption',
                            'reading_date' => $readingDate,
                            'recorded_by' => auth()->id(),
                            'notes' => $validated['notes'] ?? null
                        ]);
                        $results['success'][] = [
                            'unit_id' => $unit->id,
                            'unit_number' => $unit->unit_number,
                            'month' => $readingDate->format('Y-m')
                        ];
                        $latestUnitReading = $newReading;
                    }
                }
                
                // CRITICAL: Update the units table with the latest reading for this unit
                if ($latestUnitReading) {
                    // Get the most recent reading for this unit (could be from this batch or existing)
                    $mostRecentReading = WaterReading::where('unit_id', $unit->id)
                        ->orderBy('reading_date', 'desc')
                        ->first();
                    
                    if ($mostRecentReading) {
                        // Get the previous reading (the one before the most recent)
                        $previousReadingForUnit = WaterReading::where('unit_id', $unit->id)
                            ->where('reading_date', '<', $mostRecentReading->reading_date)
                            ->orderBy('reading_date', 'desc')
                            ->first();
                        
                        $unit->update([
                            'previous_water_reading' => $previousReadingForUnit ? $previousReadingForUnit->current_reading : 0,
                            'current_water_reading' => $mostRecentReading->current_reading,
                            'last_reading_date' => $mostRecentReading->reading_date
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            $message = count($results['success']) . ' reading(s) created successfully.';
            if (count($results['updated']) > 0) {
                $message .= ' ' . count($results['updated']) . ' reading(s) updated.';
            }
            if (count($results['skipped']) > 0) {
                $message .= ' ' . count($results['skipped']) . ' skipped (already exist).';
            }
            if (count($results['failed']) > 0) {
                $message .= ' ' . count($results['failed']) . ' failed.';
            }
            
            return response()->json([
                'success' => count($results['success']) > 0 || count($results['updated']) > 0,
                'message' => $message,
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving bulk matrix readings: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record readings: ' . $e->getMessage()
            ], 500);
        }
    }


    public function storeMultiMonth(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'start_month' => 'required|date_format:Y-m',
            'end_month' => 'required|date_format:Y-m|after_or_equal:start_month',
            'starting_reading' => 'required|numeric|min:0',
            'monthly_readings' => 'required|array|min:1',
            'monthly_readings.*.reading_date' => 'required|date',
            'monthly_readings.*.current_reading' => 'required|numeric|min:0',
            'distribution_method' => 'nullable|string|in:equal,increasing,decreasing,manual',
            'notes' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        
        try {
            $unit = Unit::findOrFail($validated['unit_id']);
            $results = [];
            
            $startMonth = Carbon::parse($validated['start_month'] . '-01');
            $endMonth = Carbon::parse($validated['end_month'] . '-01');
            
            // Get the current reading before starting
            $currentReading = $unit->current_water_reading ?? 0;
            $previousReading = $currentReading;
            
            // Validate starting reading
            if ($validated['starting_reading'] < $currentReading) {
                return response()->json([
                    'success' => false,
                    'message' => 'Starting reading cannot be less than current reading'
                ], 422);
            }
            
            $monthlyData = [];
            $latestReading = null;
            
            foreach ($validated['monthly_readings'] as $index => $monthReading) {
                $readingDate = Carbon::parse($monthReading['reading_date']);
                $newReading = $monthReading['current_reading'];
                
                if ($newReading < $previousReading) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Reading for {$readingDate->format('M Y')} is less than previous reading"
                    ], 422);
                }
                
                $consumption = $newReading - $previousReading;
                $rate = $unit->custom_water_rate ?? $unit->estate->water_rate ?? 50;
                
                if ($unit->water_billing_type === 'flat') {
                    $charge = $unit->water_charge ?? 0;
                    $consumption = 0;
                } else {
                    $charge = $consumption * $rate;
                }
                
                // Check if reading already exists for this month
                $existingReading = WaterReading::where('unit_id', $unit->id)
                    ->whereYear('reading_date', $readingDate->year)
                    ->whereMonth('reading_date', $readingDate->month)
                    ->first();
                
                if ($existingReading) {
                    // Update existing reading
                    $existingReading->update([
                        'previous_reading' => $previousReading,
                        'current_reading' => $newReading,
                        'consumption' => $consumption,
                        'rate_applied' => $rate,
                        'charge' => $charge,
                        'notes' => $validated['notes'] ?? $existingReading->notes
                    ]);
                    $monthlyData[] = $existingReading;
                    $latestReading = $existingReading;
                } else {
                    // Create new reading
                    $reading = WaterReading::create([
                        'unit_id' => $unit->id,
                        'previous_reading' => $previousReading,
                        'current_reading' => $newReading,
                        'consumption' => $consumption,
                        'rate_applied' => $rate,
                        'charge' => $charge,
                        'billing_type' => $unit->water_billing_type ?? 'consumption',
                        'reading_date' => $readingDate->format('Y-m-d'),
                        'recorded_by' => auth()->id(),
                        'notes' => $validated['notes'] ?? null
                    ]);
                    $monthlyData[] = $reading;
                    $latestReading = $reading;
                }
                
                $previousReading = $newReading;
                $results[] = [
                    'month' => $readingDate->format('Y-m'),
                    'reading' => $newReading,
                    'consumption' => $consumption,
                    'charge' => $charge
                ];
            }
            
            // CRITICAL: Update the unit with the latest reading
            if ($latestReading) {
                // Get the previous reading from the one before the latest
                $previousReadingForUnit = WaterReading::where('unit_id', $unit->id)
                    ->where('reading_date', '<', $latestReading->reading_date)
                    ->orderBy('reading_date', 'desc')
                    ->first();
                
                $unit->update([
                    'previous_water_reading' => $previousReadingForUnit ? $previousReadingForUnit->current_reading : ($unit->previous_water_reading ?? 0),
                    'current_water_reading' => $latestReading->current_reading,
                    'last_reading_date' => $latestReading->reading_date
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => count($monthlyData) . ' month(s) of readings recorded successfully',
                'results' => $results,
                'total_consumption' => collect($results)->sum('consumption'),
                'total_charge' => collect($results)->sum('charge')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving multi-month readings: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record readings: ' . $e->getMessage()
            ], 500);
        }
    }



    public function reconcile(Request $request, WaterReading $reading)
    {
        $validated = $request->validate([
            'current_reading' => 'required|numeric|min:0',
            'reading_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        
        try {
            $unit = Unit::findOrFail($reading->unit_id);
            
            // Store old values for audit
            $oldReading = $reading->current_reading;
            $newReading = $validated['current_reading'];
            
            // Get the previous reading (the reading before this one)
            $previousReadingInHistory = WaterReading::where('unit_id', $unit->id)
                ->where('reading_date', '<', $reading->reading_date)
                ->orderBy('reading_date', 'desc')
                ->first();
            
            $previousReading = $previousReadingInHistory 
                ? $previousReadingInHistory->current_reading 
                : ($unit->previous_water_reading ?? 0);
            
            if ($newReading < $previousReading) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current reading cannot be less than previous reading'
                ], 422);
            }
            
            $consumption = $newReading - $previousReading;
            $rate = $unit->custom_water_rate ?? $unit->estate->water_rate ?? 50;
            
            if ($unit->water_billing_type === 'flat') {
                $charge = $unit->water_charge ?? 0;
                $consumption = 0;
            } else {
                $charge = $consumption * $rate;
            }
            
            $reading->update([
                'previous_reading' => $previousReading,
                'current_reading' => $newReading,
                'consumption' => $consumption,
                'charge' => $charge,
                'reading_date' => $validated['reading_date'],
                'notes' => $validated['notes'] ?? $reading->notes
            ]);
            
            // Get the latest reading for this unit
            $latestReading = WaterReading::where('unit_id', $unit->id)
                ->orderBy('reading_date', 'desc')
                ->first();
            
            $previousReadingForUnit = WaterReading::where('unit_id', $unit->id)
                ->where('reading_date', '<', $latestReading->reading_date)
                ->orderBy('reading_date', 'desc')
                ->first();
            
            $unit->update([
                'previous_water_reading' => $previousReadingForUnit ? $previousReadingForUnit->current_reading : 0,
                'current_water_reading' => $latestReading->current_reading,
                'last_reading_date' => $latestReading->reading_date
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Water reading reconciled successfully',
                'reading' => $reading,
                'unit' => $unit,
                'old_reading' => $oldReading,
                'new_reading' => $newReading
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error reconciling water reading: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reconcile reading: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getLastReading($unitId)
    {
        $unit = Unit::findOrFail($unitId);
        
        // Get the latest reading from water_readings table
        $latestReading = WaterReading::where('unit_id', $unitId)
            ->orderBy('reading_date', 'desc')
            ->first();
        
        return response()->json([
            'success' => true,
            'previous_reading' => (float) ($latestReading ? $latestReading->current_reading : ($unit->current_water_reading ?? 0)),
            'last_reading_date' => $latestReading ? $latestReading->reading_date->format('Y-m-d') : ($unit->last_reading_date ? $unit->last_reading_date->format('Y-m-d') : null),
            'current_rate' => (float) ($unit->custom_water_rate ?? $unit->estate->water_rate ?? 50),
            'billing_type' => $unit->water_billing_type ?? 'consumption',
            'flat_rate' => (float) ($unit->water_charge ?? 0)
        ]);
    }


    public function getUnitWaterHistory($unitId)
    {
        $unit = Unit::with('estate')->findOrFail($unitId);
        
        $allReadings = WaterReading::where('unit_id', $unitId)
            ->orderBy('reading_date', 'asc')
            ->get();
        
        if ($allReadings->isEmpty()) {
            return response()->json([
                'success' => true,
                'unit' => [
                    'id' => $unit->id,
                    'unit_number' => $unit->unit_number,
                    'estate_name' => $unit->estate->name ?? 'N/A',
                    'water_billing_type' => $unit->water_billing_type ?? 'consumption',
                    'water_charge' => (float) ($unit->water_charge ?? 0),
                    'custom_water_rate' => (float) ($unit->custom_water_rate ?? 0)
                ],
                'history' => [],
                'stats' => [
                    'total_readings' => 0,
                    'average_consumption' => 0,
                    'total_consumption' => 0,
                    'total_charges' => 0,
                    'highest_consumption' => 0,
                    'lowest_consumption' => 0,
                    'last_reading_date' => 'No readings',
                    'billing_type' => $unit->water_billing_type ?? 'consumption',
                    'current_rate' => (float) ($unit->custom_water_rate ?? $unit->estate->water_rate ?? 50),
                    'has_initial_reading' => false,
                    'initial_reading' => null
                ],
                'initial_reading' => null
            ]);
        }
        
        $initialReading = $allReadings->first();
        $subsequentReadings = $allReadings->skip(1);
        
        $history = $subsequentReadings->sortByDesc('reading_date')->values()->map(function($reading) {
            $recordedBy = null;
            if ($reading->recorded_by) {
                $user = \App\Models\User::find($reading->recorded_by);
                $recordedBy = $user ? $user->name : 'System';
            }
            
            return [
                'id' => $reading->id,
                'previous_reading' => (float) $reading->previous_reading,
                'current_reading' => (float) $reading->current_reading,
                'consumption' => (float) $reading->consumption,
                'charge' => (float) $reading->charge,
                'rate_applied' => (float) $reading->rate_applied,
                'reading_date' => $reading->reading_date->format('Y-m-d'),
                'reading_date_formatted' => $reading->reading_date->format('M d, Y'),
                'billing_type' => $reading->billing_type,
                'recorded_by' => $reading->recorded_by,
                'recorded_by_name' => $recordedBy,
                'notes' => $reading->notes,
                'is_initial' => false
            ];
        });
        
        $stats = [
            'total_readings' => $subsequentReadings->count(),
            'average_consumption' => $subsequentReadings->count() > 0 ? (float) $subsequentReadings->avg('consumption') : 0,
            'total_consumption' => (float) $subsequentReadings->sum('consumption'),
            'total_charges' => (float) $subsequentReadings->sum('charge'),
            'highest_consumption' => $subsequentReadings->count() > 0 ? (float) $subsequentReadings->max('consumption') : 0,
            'lowest_consumption' => $subsequentReadings->count() > 0 ? (float) $subsequentReadings->min('consumption') : 0,
            'last_reading_date' => $unit->last_reading_date ? $unit->last_reading_date->format('M d, Y') : 'No readings',
            'billing_type' => $unit->water_billing_type ?? 'consumption',
            'current_rate' => (float) ($unit->custom_water_rate ?? $unit->estate->water_rate ?? 50),
            'has_initial_reading' => true,
            'initial_reading' => [
                'id' => $initialReading->id,
                'reading_date' => $initialReading->reading_date->format('M d, Y'),
                'current_reading' => (float) $initialReading->current_reading,
                'previous_reading' => (float) $initialReading->previous_reading,
                'consumption' => 0,
                'charge' => (float) $initialReading->charge,
                'is_initial' => true
            ]
        ];
        
        return response()->json([
            'success' => true,
            'unit' => [
                'id' => $unit->id,
                'unit_number' => $unit->unit_number,
                'estate_name' => $unit->estate->name ?? 'N/A',
                'water_billing_type' => $unit->water_billing_type ?? 'consumption',
                'water_charge' => (float) ($unit->water_charge ?? 0),
                'custom_water_rate' => (float) ($unit->custom_water_rate ?? 0)
            ],
            'history' => $history,
            'stats' => $stats,
            'initial_reading' => $stats['initial_reading']
        ]);
    }


    public function getUnitReadings(Unit $unit)
    {
        $readings = WaterReading::where('unit_id', $unit->id)
            ->orderBy('reading_date', 'desc')
            ->paginate(20);
        
        return response()->json($readings);
    }


    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'estate_id' => 'nullable|exists:estates,id',
            'unit_id' => 'nullable|exists:units,id'
        ]);
        
        $query = WaterReading::with(['unit.estate'])
            ->whereBetween('reading_date', [$validated['start_date'], $validated['end_date']]);
        
        if ($validated['estate_id'] ?? false) {
            $query->whereHas('unit', fn($q) => $q->where('estate_id', $validated['estate_id']));
        }
        
        if ($validated['unit_id'] ?? false) {
            $query->where('unit_id', $validated['unit_id']);
        }
        
        $readings = $query->get();
        
        return response()->json([
            'success' => true,
            'summary' => [
                'total_consumption' => (float) $readings->sum('consumption'),
                'total_charges' => (float) $readings->sum('charge'),
                'average_consumption' => (float) $readings->avg('consumption'),
                'total_readings' => $readings->count(),
                'period' => [
                    'start' => $validated['start_date'],
                    'end' => $validated['end_date']
                ]
            ],
            'readings' => $readings->map(function($reading) {
                return [
                    'id' => $reading->id,
                    'unit_number' => $reading->unit->unit_number,
                    'estate_name' => $reading->unit->estate->name,
                    'previous_reading' => (float) $reading->previous_reading,
                    'current_reading' => (float) $reading->current_reading,
                    'consumption' => (float) $reading->consumption,
                    'charge' => (float) $reading->charge,
                    'reading_date' => $reading->reading_date->format('Y-m-d')
                ];
            })
        ]);
    }


    public function statement(Unit $unit)
    {
        $unit->load('estate');
        
        $allReadings = WaterReading::where('unit_id', $unit->id)
            ->orderBy('reading_date', 'asc')
            ->get();
        
        if ($allReadings->isEmpty()) {
            $readings = collect();
            $stats = [
                'total_readings' => 0,
                'average_consumption' => 0,
                'total_consumption' => 0,
                'total_charges' => 0,
                'has_initial_reading' => false,
                'initial_reading' => [
                    'reading_date' => '',
                    'current_reading' => 0,
                    'consumption' => 0
                ]
            ];
            return view('water.show', compact('unit', 'readings', 'stats'));
        }
        
        $initialReading = $allReadings->first();
        $subsequentReadings = $allReadings->skip(1);
        
        $readings = $subsequentReadings->sortByDesc('reading_date')->values()->map(function($reading) {
            $recordedBy = null;
            if ($reading->recorded_by) {
                $user = \App\Models\User::find($reading->recorded_by);
                $recordedBy = $user ? $user->name : 'System';
            }
            
            return (object) [
                'id' => $reading->id,
                'previous_reading' => (float) $reading->previous_reading,
                'current_reading' => (float) $reading->current_reading,
                'consumption' => (float) $reading->consumption,
                'charge' => (float) $reading->charge,
                'rate_applied' => (float) $reading->rate_applied,
                'reading_date' => $reading->reading_date->format('Y-m-d'),
                'billing_type' => $reading->billing_type,
                'recorded_by_name' => $recordedBy,
                'notes' => $reading->notes
            ];
        });
        
        // Use ARRAY format instead of object for initial_reading
        $stats = [
            'total_readings' => $subsequentReadings->count(),
            'average_consumption' => $subsequentReadings->count() > 0 ? (float) $subsequentReadings->avg('consumption') : 0,
            'total_consumption' => (float) $subsequentReadings->sum('consumption'),
            'total_charges' => (float) $subsequentReadings->sum('charge'),
            'has_initial_reading' => true,
            'initial_reading' => [
                'reading_date' => $initialReading->reading_date->format('M d, Y'),
                'current_reading' => (float) $initialReading->current_reading,
                'consumption' => 0,
                'previous_reading' => (float) $initialReading->previous_reading
            ]
        ];
        
        return view('water.show', compact('unit', 'readings', 'stats'));
    }


 
    public function getUnitsWithWaterReadings(Request $request)
    {
        $estateId = $request->get('estate_id');
        
        $query = Unit::with('estate')
            ->where('is_active', true);
        
        if ($estateId) {
            $query->where('estate_id', $estateId);
        }
        
        $units = $query->get()->map(function($unit) {
            return [
                'id' => $unit->id,
                'unit_number' => $unit->unit_number,
                'estate_name' => $unit->estate->name ?? 'N/A',
                'estate_id' => $unit->estate_id,
                'unit_type' => $unit->unit_type,
                'water_billing_type' => $unit->water_billing_type ?? 'consumption',
                'water_charge' => (float) ($unit->water_charge ?? 0),
                'custom_water_rate' => (float) ($unit->custom_water_rate ?? 0),
                'current_water_reading' => (float) ($unit->current_water_reading ?? 0),
                'previous_water_reading' => (float) ($unit->previous_water_reading ?? 0),
                'last_reading_date' => $unit->last_reading_date,
                'water_rate' => (float) ($unit->custom_water_rate ?? $unit->estate->water_rate ?? 50),
            ];
        });
        
        return response()->json([
            'success' => true,
            'units' => $units,
            'total' => $units->count()
        ]);
    }
}