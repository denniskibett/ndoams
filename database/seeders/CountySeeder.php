<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CountySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to your JSON file
        $jsonFile = database_path('seeders/data/counties.json');
        
        // Check if file exists
        if (!File::exists($jsonFile)) {
            $this->command->error("JSON file not found: {$jsonFile}");
            return;
        }
        
        // Read and decode JSON file
        $jsonData = File::get($jsonFile);
        $counties = json_decode($jsonData, true);
        
        // Check if JSON decoding was successful
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Failed to decode JSON: ' . json_last_error_msg());
            return;
        }
        
        // Check if we have data
        if (empty($counties)) {
            $this->command->error('No data found in JSON file');
            return;
        }
        
        $this->command->info('Processing counties data...');
        
        // Transform hierarchical data into flat structure
        $flattenedData = [];
        
        foreach ($counties as $county) {
            $countyCode = $county['county_code'];
            $countyName = $county['county_name'];
            
            // Check if constituencies exist and is an array
            if (!isset($county['constituencies']) || !is_array($county['constituencies'])) {
                $this->command->warning("No constituencies found for county: {$countyName}");
                continue;
            }
            
            foreach ($county['constituencies'] as $constituency) {
                $constituencyName = $constituency['constituency_name'];
                
                // Check if wards exist and is an array
                if (!isset($constituency['wards']) || !is_array($constituency['wards'])) {
                    $this->command->warning("No wards found for constituency: {$constituencyName} in county: {$countyName}");
                    continue;
                }
                
                foreach ($constituency['wards'] as $ward) {
                    $flattenedData[] = [
                        'county_code' => $countyCode,
                        'name' => $countyName,
                        'constituency' => $constituencyName,
                        'wards' => $ward,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        $this->command->info('Found ' . count($flattenedData) . ' ward records to insert...');
        
        if (empty($flattenedData)) {
            $this->command->error('No data to insert after processing');
            return;
        }
        
        // Insert data in chunks to avoid memory issues
        $chunkSize = 100;
        $totalChunks = ceil(count($flattenedData) / $chunkSize);
        
        foreach (array_chunk($flattenedData, $chunkSize) as $index => $chunk) {
            DB::table('counties')->insert($chunk);
            $this->command->info("Inserted chunk " . ($index + 1) . " of {$totalChunks}");
        }
        
        $this->command->info('Counties, constituencies, and wards seeded successfully!');
        $this->command->info('Total records inserted: ' . count($flattenedData));
    }
}