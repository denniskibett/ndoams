<?php
// app/Services/MarriageCreationService.php

namespace App\Services;

use App\Models\Marriage;
use App\Models\PdfPage;
use App\Models\Category;
use App\Models\MarriageTypeExtension;
use App\Models\Spouse;
use App\Models\Witness;
use App\Models\County;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MarriageCreationService
{
    /**
     * Track missing fields for validation
     */
    protected $missingFields = [];

    /**
     * Create a marriage record from PDF page (Quick Entry)
     */
    public function createQuickEntry(array $data, PdfPage $page, $user)
    {
        return $this->createMarriage($data, $page, $user, 'quick');
    }

    /**
     * Create a marriage record from PDF page (Full Entry)
     */
    public function createFullEntry(array $data, PdfPage $page, $user)
    {
        return $this->createMarriage($data, $page, $user, 'full');
    }

    protected function createMarriage(array $data, PdfPage $page, $user, $mode = 'quick')
    {
        // Check if validation should be bypassed - THIS IS THE ONLY THING THAT DETERMINES has_errors
        $skipValidation = $data['skip_validation'] ?? false;
        
        // SIMPLE: has_errors = 1 when bypass validation, 0 when normal validation
        $hasErrors = $skipValidation ? 1 : 0;

        // Get category IDs
        $marriageStatusId = Category::where('type', 'marriage_status')
            ->where('name', 'Completed')
            ->value('id');

        $verificationStatusId = Category::where('type', 'verification_status')
            ->where('name', 'Unverified')
            ->value('id');

        if (!$marriageStatusId || !$verificationStatusId) {
            throw new \Exception('Required status categories not found in database.');
        }

        // ===== DATE HANDLING =====
        $marriageDateRaw = $data['marriage_date'] ?? $data['marriageDate'] ?? null;
        $regDateRaw = $data['reg_date'] ?? $data['regDate'] ?? $marriageDateRaw;
        
        $marriageDate = $this->formatDateForDatabase($marriageDateRaw);
        $regDate = $this->formatDateForDatabase($regDateRaw);

        // Extract notes - these go to PDF page, NOT to marriage table
        $notes = $data['notes'] ?? null;

        // Extract license_no
        $licenseNo = $data['license_no'] ?? $data['licenseNo'] ?? '';

        // Prepare default values for empty fields when skipValidation is true
        $defaultEmptyValue = 'N/A';
        
        // Map data to match database schema exactly
        $marriageData = [
            // Core fields
            'certificate_serial' => (!empty($data['certificate_serial'] ?? $data['certificateSerial'] ?? '')) 
                ? strtoupper($data['certificate_serial'] ?? $data['certificateSerial'])
                : ($skipValidation ? $defaultEmptyValue : ''),        
            'license_no' => strtoupper($licenseNo ?: ($skipValidation ? $defaultEmptyValue : '')),
            'marriage_date' => $marriageDate ?: ($skipValidation ? now()->format('Y-m-d') : null),
            'reg_date' => $regDate ?: ($skipValidation ? now()->format('Y-m-d') : null),
            'venue' => !empty($data['venue']) 
                ? strtoupper($data['venue'])
                : ($skipValidation ? $defaultEmptyValue : ''),
            
            // Location fields
            'county' => !empty($data['county']) 
                ? strtoupper($data['county'])
                : ($skipValidation ? $defaultEmptyValue : ''),
            'sub_county' => !empty($data['sub_county'] ?? $data['selectedConstituency'] ?? '')
                ? strtoupper($data['sub_county'] ?? $data['selectedConstituency'])
                : ($skipValidation ? $defaultEmptyValue : ''),
            'ward_id' => $data['ward_id'] ?? null,
            
            // PDF relationships
            'pdf_page_id' => $page->id,
            'pdf_id' => $page->pdfUpload->id,
            'image_id' => null,
            
            // Period info
            'year' => $page->pdfUpload->year,
            'month' => $page->pdfUpload->month,
            
            // Type and status
            'marriage_type_id' => $data['marriage_type_id'] ?? $page->pdfUpload->marriage_type_id,
            'marriage_status_id' => $marriageStatusId,
            'verification_status_id' => $verificationStatusId,
            
            // System status - always Under Review for full entry
            'system_status' => Marriage::SYSTEM_STATUS_UNDER_REVIEW,
            
            // has_errors - DIRECTLY from user's toggle
            'has_errors' => $hasErrors,
            'error_fields' => $skipValidation ? ['Validation bypassed by user'] : [],
            
            // User tracking
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'verified_by' => $user->id,
            
            // Timestamps for review
            'reviewed_at' => now(),
        ];

        Log::info('Creating marriage with data:', array_merge($marriageData, ['skip_validation' => $skipValidation]));
        
        DB::beginTransaction();
        
        try {
            $marriage = Marriage::create($marriageData);

            // Add spouses and witnesses based on mode
            if ($mode === 'full' || $skipValidation) {
                $this->addSpouses($marriage, $data, $user, $skipValidation);
                $this->addWitnesses($marriage, $data, $user, $skipValidation);
                $this->addMarriageExtensions($marriage, $data, $user);
                $pageStatus = PdfPage::STATUS_REVIEW_NEEDED;
            } else {
                $this->addQuickSpouses($marriage, $data, $user);
                $pageStatus = PdfPage::STATUS_IN_PROGRESS;
            }

            // Update PDF page with appropriate status and JSON notes
            $pageUpdateData = [
                'status' => $pageStatus,
                'completed_by' => $user->id,
                'completed_at' => now(),
            ];
            
            $pageNotesData = [];
            
            if (!empty($notes)) {
                $pageNotesData['description'] = $notes;
            }
            
            $pageNotesData['marriage_id'] = $marriage->id;
            $pageNotesData['certificate_serial'] = $marriage->certificate_serial;
            $pageNotesData['license_no'] = $marriage->license_no;
            
            if ($skipValidation) {
                $pageNotesData['type'] = 'validation_bypassed';
                $pageNotesData['message'] = 'Record submitted with validation bypass';
            } else {
                $pageNotesData['type'] = 'complete_submission';
            }
            
            $pageNotesData['submitted_by'] = $user->id;
            $pageNotesData['submitted_by_name'] = $user->name;
            $pageNotesData['submitted_at'] = now()->toISOString();
            
            // Merge with existing notes if any
            $existingNotes = $page->notes;
            if (!empty($existingNotes)) {
                $existingNotesData = is_string($existingNotes) ? json_decode($existingNotes, true) : $existingNotes;
                if (is_array($existingNotesData)) {
                    if (!isset($existingNotesData['history'])) {
                        $existingNotesData['history'] = [];
                    }
                    $existingNotesData['history'][] = $pageNotesData;
                    $pageNotesData = array_merge($existingNotesData, $pageNotesData);
                }
            }
            
            $pageUpdateData['notes'] = json_encode($pageNotesData, JSON_PRETTY_PRINT);
            $page->update($pageUpdateData);

            DB::commit();

            Log::info('Marriage created successfully', [
                'marriage_id' => $marriage->id,
                'has_errors' => $hasErrors,
                'skip_validation' => $skipValidation
            ]);

            return $marriage;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Marriage creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Format date string to Y-m-d for database storage
     */
    protected function formatDateForDatabase($dateString)
    {
        if (empty($dateString)) {
            return null;
        }
        
        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
                return $dateString;
            }
            
            $formats = [
                'd/m/Y', 'd/m/y', 'm/d/Y', 'm/d/y', 'Y-m-d', 
                'd-m-Y', 'm-d-Y', 'Y/m/d', 'd M Y', 'M d, Y',
            ];
            
            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $dateString);
                    if ($date) {
                        return $date->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            $date = Carbon::parse($dateString);
            return $date->format('Y-m-d');
            
        } catch (\Exception $e) {
            Log::warning('Failed to parse date: ' . $dateString, [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Add quick spouses (minimal data)
     */
    protected function addQuickSpouses(Marriage $marriage, array $data, $user)
    {
        if (!empty($data['husband_name'] ?? $data['husband']['name'] ?? '')) {
            $husbandName = $data['husband_name'] ?? $data['husband']['name'] ?? '';
            
            $marriage->spouses()->create([
                'name' => strtoupper($husbandName),
                'spouse_type' => 'husband',
                'age' => 0,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'verified_by' => $user->id,
            ]);
        }

        if (!empty($data['wife_name'] ?? $data['wife']['name'] ?? '')) {
            $wifeName = $data['wife_name'] ?? $data['wife']['name'] ?? '';
            
            $marriage->spouses()->create([
                'name' => strtoupper($wifeName),
                'spouse_type' => 'wife',
                'age' => 0,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'verified_by' => $user->id,
            ]);
        }
    }

    /**
     * Add spouses to marriage with full details
     */
    protected function addSpouses(Marriage $marriage, array $data, $user, $skipValidation = false)
    {
        $defaultEmpty = $skipValidation ? 'N/A' : '';
        
        // Handle husband
        $husbandName = $data['husband_name'] ?? $data['husband']['name'] ?? '';
        if (!empty($husbandName) || $skipValidation) {
            $husbandData = $this->extractSpouseData($data, 'husband', $user, $skipValidation);
            if ($skipValidation && empty($husbandData['name'])) {
                $husbandData['name'] = $defaultEmpty;
            }
            if ($skipValidation && empty($husbandData['age'])) {
                $husbandData['age'] = 0;
            }
            $marriage->spouses()->create($husbandData);
        }

        // Handle wife
        $wifeName = $data['wife_name'] ?? $data['wife']['name'] ?? '';
        if (!empty($wifeName) || $skipValidation) {
            $wifeData = $this->extractSpouseData($data, 'wife', $user, $skipValidation);
            if ($skipValidation && empty($wifeData['name'])) {
                $wifeData['name'] = $defaultEmpty;
            }
            if ($skipValidation && empty($wifeData['age'])) {
                $wifeData['age'] = 0;
            }
            $marriage->spouses()->create($wifeData);
        }
    }

    /**
     * Extract spouse data with deceased handling and marital status
     */
    protected function extractSpouseData(array $data, string $spouseType, $user, $skipValidation = false): array
    {
        $prefix = $spouseType;
        $defaultEmpty = $skipValidation ? 'N/A' : '';
        
        // Basic spouse info
        $name = $data[$prefix . '_name'] ?? $data[$prefix]['name'] ?? '';
        $age = $data[$prefix . '_age'] ?? $data[$prefix]['age'] ?? 0;
        $maritalStatus = $data[$prefix . '_marital_status'] ?? $data[$prefix]['maritalStatus'] ?? null;
        $occupation = $data[$prefix . '_occupation'] ?? $data[$prefix]['occupation'] ?? '';
        $residence = $data[$prefix . '_residence'] ?? $data[$prefix]['residence'] ?? '';
        $county = $data['county'] ?? '';
        
        // Map marital status values
        $maritalStatusMapping = [
            'single' => 'Bachelor',
            'married' => 'Married',
            'divorced' => 'Divorced',
            'widowed' => 'Widowed',
            'Bachelor' => 'Bachelor',
            'Spinster' => 'Spinster',
            'Widowed' => 'Widowed',
            'Divorced' => 'Divorced',
            'Married' => 'Married',
            '' => $spouseType === 'husband' ? 'Bachelor' : 'Spinster',
            null => $spouseType === 'husband' ? 'Bachelor' : 'Spinster',
        ];
        
        $mappedMaritalStatus = $maritalStatusMapping[$maritalStatus] ?? ($spouseType === 'husband' ? 'Bachelor' : 'Spinster');
        
        if ($spouseType === 'wife' && $mappedMaritalStatus === 'Bachelor') {
            $mappedMaritalStatus = 'Spinster';
        }
        
        // Deceased flags
        $fatherDeceased = isset($data[$prefix . '_father_deceased']) && 
                         (filter_var($data[$prefix . '_father_deceased'], FILTER_VALIDATE_BOOLEAN) === true);
        
        $motherDeceased = isset($data[$prefix . '_mother_deceased']) && 
                         (filter_var($data[$prefix . '_mother_deceased'], FILTER_VALIDATE_BOOLEAN) === true);
        
        // Father data
        $rawFatherName = $data[$prefix . '_father_name'] ?? $data[$prefix]['father']['name'] ?? '';
        $rawFatherOccupation = $data[$prefix . '_father_occupation'] ?? $data[$prefix]['father']['occupation'] ?? '';
        $rawFatherResidence = $data[$prefix . '_father_residence'] ?? $data[$prefix]['father']['residence'] ?? '';
        
        $fatherName = strtoupper($rawFatherName);
        $fatherOccupation = $fatherDeceased ? 'DECEASED' : strtoupper($rawFatherOccupation);
        $fatherResidence = $fatherDeceased ? 'XXX' : strtoupper($rawFatherResidence);
        
        // Mother data
        $rawMotherName = $data[$prefix . '_mother_name'] ?? $data[$prefix]['mother']['name'] ?? '';
        $rawMotherOccupation = $data[$prefix . '_mother_occupation'] ?? $data[$prefix]['mother']['occupation'] ?? '';
        $rawMotherResidence = $data[$prefix . '_mother_residence'] ?? $data[$prefix]['mother']['residence'] ?? '';
        
        $motherName = strtoupper($rawMotherName);
        $motherOccupation = $motherDeceased ? 'DECEASED' : strtoupper($rawMotherOccupation);
        $motherResidence = $motherDeceased ? 'XXX' : strtoupper($rawMotherResidence);
        
        return [
            'name' => !empty($name) ? strtoupper($name) : ($skipValidation ? 'N/A' : ''),
            'spouse_type' => $spouseType,
            'age' => $age ?: 0,
            'marital_status' => $mappedMaritalStatus,
            'occupation' => !empty($occupation) ? strtoupper($occupation) : ($skipValidation ? 'N/A' : ''),
            'residence' => !empty($residence) ? strtoupper($residence) : ($skipValidation ? 'N/A' : ''),
            'county' => !empty($county) ? strtoupper($county) : ($skipValidation ? 'N/A' : ''),
            'id_type_id' => null,
            'id_number' => null,
            'father_name' => !empty($fatherName) ? $fatherName : ($skipValidation ? 'N/A' : ''),
            'father_occupation' => !empty($fatherOccupation) ? $fatherOccupation : ($skipValidation ? 'N/A' : ''),
            'father_residence' => !empty($fatherResidence) ? $fatherResidence : ($skipValidation ? 'N/A' : ''),
            'father_id_type_id' => null,
            'father_id_number' => null,
            'mother_name' => !empty($motherName) ? $motherName : ($skipValidation ? 'N/A' : ''),
            'mother_occupation' => !empty($motherOccupation) ? $motherOccupation : ($skipValidation ? 'N/A' : ''),
            'mother_residence' => !empty($motherResidence) ? $motherResidence : ($skipValidation ? 'N/A' : ''),
            'mother_id_type_id' => null,
            'mother_id_number' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'verified_by' => $user->id,
        ];
    }

    /**
     * Add witnesses to marriage
     */
    protected function addWitnesses(Marriage $marriage, array $data, $user, $skipValidation = false)
    {
        $defaultEmpty = $skipValidation ? 'N/A' : '';
        $defaultSides = ['husband', 'wife'];
        
        // Witness 1
        $witness1Name = $data['witness1_name'] ?? $data['witnesses']['witness1']['name'] ?? '';
        if (!empty($witness1Name) || $skipValidation) {
            $witness1Name = !empty($witness1Name) ? $witness1Name : ($skipValidation ? $defaultEmpty : '');
            $witness1Side = $data['witness1_side'] ?? $data['witnesses']['witness1']['side'] ?? ($skipValidation ? $defaultSides[0] : null);
            
            if ($witness1Side === null || $witness1Side === '') {
                $witness1Side = $skipValidation ? $defaultSides[0] : 'both';
            }
            
            $witnessData = [
                'name' => strtoupper($witness1Name),
                'spouse_side' => $witness1Side,
                'id_type_id' => null,
                'id_number' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'verified_by' => $user->id,
                'marriage_id' => $marriage->id,
            ];
            
            $marriage->witnesses()->create($witnessData);
        }

        // Witness 2
        $witness2Name = $data['witness2_name'] ?? $data['witnesses']['witness2']['name'] ?? '';
        if (!empty($witness2Name) || $skipValidation) {
            $witness2Name = !empty($witness2Name) ? $witness2Name : ($skipValidation ? $defaultEmpty : '');
            $witness2Side = $data['witness2_side'] ?? $data['witnesses']['witness2']['side'] ?? ($skipValidation ? $defaultSides[1] : null);
            
            if ($witness2Side === null || $witness2Side === '') {
                $witness2Side = $skipValidation ? $defaultSides[1] : 'both';
            }
            
            $witnessData = [
                'name' => strtoupper($witness2Name),
                'spouse_side' => $witness2Side,
                'id_type_id' => null,
                'id_number' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'verified_by' => $user->id,
                'marriage_id' => $marriage->id,
            ];
            
            $marriage->witnesses()->create($witnessData);
        }
    }

    protected function addMarriageExtensions(Marriage $marriage, array $data, $user)
    {
        $marriageTypeName = $marriage->marriageType->name ?? '';
        
        $extensionData = [
            'marriage_id' => $marriage->id, 
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'verified_by' => $user->id,
        ];
        
        $hasExtensionData = false;
        
        if (!empty($data['entry_no'])) {
            $extensionData['entry_no'] = $data['entry_no'];
            $hasExtensionData = true;
        }
        
        switch ($marriageTypeName) {
            case 'Muslim':
                if (!empty($data['muslim_officer'])) {
                    $extensionData['muslim_officer'] = $data['muslim_officer'];
                    $hasExtensionData = true;
                }
                $extensionFields = ['mahr_agreed', 'mahr_paid', 'mahr_deferred', 'gifts'];
                foreach ($extensionFields as $field) {
                    if (!empty($data[$field])) {
                        $extensionData[$field] = $data[$field];
                        $hasExtensionData = true;
                    }
                }
                break;
                
            case 'Christian':
                if (!empty($data['pastor_name'])) {
                    $extensionData['pastor_name'] = $data['pastor_name'];
                    $hasExtensionData = true;
                }
                if (!empty($data['church_org'])) {
                    $extensionData['church_org'] = $data['church_org'];
                    $hasExtensionData = true;
                }
                break;
                
            case 'Hindu':
                if (!empty($data['registrar_officer'])) {
                    $extensionData['registrar_officer'] = $data['registrar_officer'];
                    $hasExtensionData = true;
                }
                if (!empty($data['temple'])) {
                    $extensionData['temple'] = $data['temple'];
                    $hasExtensionData = true;
                }
                if (!empty($data['dowry'])) {
                    $extensionData['dowry'] = $data['dowry'];
                    $hasExtensionData = true;
                }
                break;
                
            case 'Civil':
            default:
                if (!empty($data['registrar_officer'])) {
                    $extensionData['registrar_officer'] = $data['registrar_officer'];
                    $hasExtensionData = true;
                }
                break;
        }
        
        if ($hasExtensionData) {
            MarriageTypeExtension::updateOrCreate(
                ['marriage_id' => $marriage->id],
                $extensionData
            );
        }
    }
}