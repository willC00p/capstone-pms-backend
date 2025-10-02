<?php
   
namespace App\Http\Controllers\API;
   
use App\Models\UserDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Validator;
use Illuminate\Support\Str;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
   
class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'middlename' => 'nullable',
            'lastname' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'c_password' => 'required|same:password',
            // vehicle / OR-CR uploads
            'plate_number' => 'nullable|string',
            'vehicle_color' => 'nullable|string',
            'vehicle_type' => 'nullable|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'or_file' => 'sometimes|file|mimes:pdf|max:5120',
            'cr_file' => 'sometimes|file|mimes:pdf|max:5120',
            'or_cr_pdf' => 'sometimes|file|mimes:pdf|max:5120',
            'or_number' => 'nullable|string|unique:vehicles,or_number',
            'cr_number' => 'nullable|string|unique:vehicles,cr_number',
            'referred_by' => [
                'nullable', 
                'email', 
                Rule::exists('users', 'email'), 
                function (string $attribute, mixed $value, \Closure $fail) {
                    $referrer = User::where('email', $value)->first();
                    if ($referrer && $referrer->refer_count >= 10) { $fail("Referrer exceeded count of refers."); }
                }
            ],
            // optional face verification fields (client may supply results computed by Expo app)
            'face_score' => 'nullable|numeric',
            'face_verified' => 'nullable|boolean',
            // second-hand deed upload
            'is_second_hand' => 'nullable|boolean',
            'deed_file' => 'sometimes|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        // If an id_file was uploaded as a real file, validate its mime/size separately.
        if ($request->hasFile('id_file')) {
            $idValidator = Validator::make($request->only(['id_file']), [
                'id_file' => 'file|mimes:jpg,jpeg,png|max:5120'
            ]);
            if ($idValidator->fails()) {
                return $this->sendError('Validation Error.', $idValidator->errors());
            }
        }

        $input = $request->all();
        // debugging helper to track incoming files and storage results
        $debug = [
            'has_or_file' => $request->hasFile('or_file'),
            'has_cr_file' => $request->hasFile('cr_file'),
            'has_or_cr_pdf' => $request->hasFile('or_cr_pdf'),
            'has_id_file' => $request->hasFile('id_file'),
            'files' => [],
            'store_results' => [],
        ];
        $input['password'] = bcrypt($input['password']);
        // build a canonical full name for the users.name field
        $fullNameParts = [$input['firstname'] ?? ''];
        if (!empty($input['middlename'])) $fullNameParts[] = $input['middlename'];
        $fullNameParts[] = $input['lastname'] ?? '';
        $canonicalName = trim(implode(' ', array_filter($fullNameParts)));

        $new_user = [
            'name' => $canonicalName,
            'email' => $input['email'],
            'password' => $input['password']
        ];
        $user = User::create($new_user);
        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['name'] =  $user->name;
        // Build user_details payload but only include id-related columns if they exist in DB
        $udPayload = [
            'user_id' => $user->id,
            'firstname' => $input['firstname'],
            'middlename' => $input['middlename'] ?? null,
            'lastname' => $input['lastname'],
            'department' => $input['department'] ?? null,
            'contact_number' => $input['contact_number'] ?? null,
            'student_no' => $input['student_no'] ?? null,
            'course' => $input['course'] ?? null,
            'yr_section' => $input['yr_section'] ?? null,
            'faculty_id' => $input['faculty_id'] ?? null,
            'employee_id' => $input['employee_id'] ?? null,
            'position' => $input['position'] ?? null,
            'nationality' => 'Filipino',
            'membership_date' => $user->created_at,
            // placeholders for OR/CR paths (may be set below)
            'or_path' => null,
            'cr_path' => null,
            'or_number' => $input['or_number'] ?? null,
            'cr_number' => $input['cr_number'] ?? null,
            'plate_number' => $input['plate_number'] ?? null,
        ];

        // Determine id number from existing role-specific fields if present
        $idNumber = $input['student_no'] ?? $input['faculty_id'] ?? $input['employee_id'] ?? ($input['id_number'] ?? null);

        if (Schema::hasColumn('user_details', 'id_path')) {
            $udPayload['id_path'] = null;
        }
        if (Schema::hasColumn('user_details', 'id_number')) {
            $udPayload['id_number'] = $idNumber;
        }
        // If the migration added face fields, include them in the payload
        if (Schema::hasColumn('user_details', 'selfie_path')) {
            $udPayload['selfie_path'] = null;
        }
        if (Schema::hasColumn('user_details', 'face_score')) {
            $udPayload['face_score'] = isset($input['face_score']) ? $input['face_score'] : null;
        }
        if (Schema::hasColumn('user_details', 'face_verified')) {
            // default to false if not supplied; migration default is false
            $udPayload['face_verified'] = isset($input['face_verified']) ? (bool)$input['face_verified'] : false;
        }

        $user->userDetail()->save(UserDetails::create($udPayload));

        // Ensure basic non-file profile fields are persisted even if no files were uploaded
        try {
            $udAlways = $user->userDetail()->first();
            if ($udAlways) {
                $udAlways->department = $input['department'] ?? $udAlways->department;
                $udAlways->contact_number = $input['contact_number'] ?? $udAlways->contact_number;
                $udAlways->student_no = $input['student_no'] ?? $udAlways->student_no;
                $udAlways->course = $input['course'] ?? $udAlways->course;
                $udAlways->yr_section = $input['yr_section'] ?? $udAlways->yr_section;
                $udAlways->plate_number = $input['plate_number'] ?? $udAlways->plate_number;
                $udAlways->position = $input['position'] ?? $udAlways->position;
                $udAlways->save();
            }
        } catch (\Throwable $e) {
            Log::warning('RegisterController: persisting initial user_details fields failed', ['err' => $e->getMessage()]);
        }

    // Handle vehicle OR/CR upload and vehicle creation if plate_number supplied
    $orPath = null; $crPath = null;
    // Use $request->hasFile to detect uploaded files (multipart/form-data). Relying on
    // isset($input['...']) misses uploaded files because they appear in $request->files.
    if ($request->hasFile('or_cr_pdf') || $request->hasFile('or_file') || $request->hasFile('cr_file') || $request->hasFile('id_file')) {
            // Note: incoming files are in the request, so access via $request
            if ($request->hasFile('or_cr_pdf')) {
                $file = $request->file('or_cr_pdf');
                $debug['files']['or_cr_pdf'] = $file->getClientOriginalName();
                $filename = 'orcr_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $orPath = $file->storeAs('or_cr', $filename, 'public');
                $debug['store_results']['or_cr_pdf'] = $orPath;
            }
            if ($request->hasFile('or_file')) {
                $file = $request->file('or_file');
                $debug['files']['or_file'] = $file->getClientOriginalName();
                $filename = 'or_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $orPath = $file->storeAs('or_cr', $filename, 'public');
                $debug['store_results']['or_file'] = $orPath;
            }
            if ($request->hasFile('cr_file')) {
                $file = $request->file('cr_file');
                $debug['files']['cr_file'] = $file->getClientOriginalName();
                $filename = 'cr_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $crPath = $file->storeAs('or_cr', $filename, 'public');
                $debug['store_results']['cr_file'] = $crPath;
            }

            // store id file if provided
            if ($request->hasFile('id_file')) {
                $file = $request->file('id_file');
                $debug['files']['id_file'] = $file->getClientOriginalName();
                $filename = 'id_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $idPath = $file->storeAs('ids', $filename, 'public');
                $debug['store_results']['id_file'] = $idPath;
            } else {
                $idPath = null;
            }

            // store selfie file if provided
            if ($request->hasFile('selfie_file')) {
                $file = $request->file('selfie_file');
                $debug['files']['selfie_file'] = $file->getClientOriginalName();
                $filename = 'selfie_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $selfiePath = $file->storeAs('selfies', $filename, 'public');
                $debug['store_results']['selfie_file'] = $selfiePath;
            } else {
                $selfiePath = null;
            }

            // update the userDetails with or/cr paths and other fields
            $ud = $user->userDetail()->first();
            if ($ud) {
                // keep existing values if new ones are null
                if (!empty($orPath)) $ud->or_path = $orPath;
                if (!empty($crPath)) $ud->cr_path = $crPath;
                $ud->or_number = $input['or_number'] ?? $ud->or_number;
                $ud->cr_number = $input['cr_number'] ?? $ud->cr_number;
                if (!empty($idPath)) {
                    $ud->id_path = $idPath;
                }
                if (!empty($selfiePath) && Schema::hasColumn('user_details', 'selfie_path')) {
                    $ud->selfie_path = $selfiePath;
                }
                // role-specific id values
                $ud->student_no = $input['student_no'] ?? $ud->student_no;
                $ud->faculty_id = $input['faculty_id'] ?? $ud->faculty_id;
                $ud->employee_id = $input['employee_id'] ?? $ud->employee_id;
                // contact / department
                $ud->contact_number = $input['contact_number'] ?? $ud->contact_number;
                $ud->department = $input['department'] ?? $ud->department;
                $ud->position = $input['position'] ?? $ud->position;
                // persist any incoming face verification results (if client supplied them)
                if (isset($input['face_score']) && Schema::hasColumn('user_details', 'face_score')) {
                    $ud->face_score = $input['face_score'];
                }
                if (isset($input['face_verified']) && Schema::hasColumn('user_details', 'face_verified')) {
                    $ud->face_verified = (bool)$input['face_verified'];
                }

                // handle deed of sale upload for second-hand vehicles
                if ($request->hasFile('deed_file')) {
                    $file = $request->file('deed_file');
                    $debug['files']['deed_file'] = $file->getClientOriginalName();
                    $filename = 'deed_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $deedPath = $file->storeAs('deeds', $filename, 'public');
                    $debug['store_results']['deed_file'] = $deedPath;
                    if (!empty($deedPath) && Schema::hasColumn('user_details', 'deed_path')) {
                        $ud->deed_path = $deedPath;
                    }
                    if (Schema::hasColumn('user_details', 'deed_name')) {
                        $ud->deed_name = $file->getClientOriginalName();
                    }
                }

                if (!empty($input['is_second_hand']) && Schema::hasColumn('user_details', 'is_second_hand')) {
                    $ud->is_second_hand = (bool)$input['is_second_hand'];
                    // second-hand users go straight to admin (mark pending)
                    $ud->from_pending = true;
                }

                // --- PDF TEXT EXTRACTION & VALIDATION ---
                // helper to extract text from stored public path using Smalot\PdfParser if available
                $extractTextFromStored = function ($storedPath) use (&$debug) {
                    if (empty($storedPath)) return null;
                    $fullPath = storage_path('app/public/' . $storedPath);
                    if (!file_exists($fullPath)) {
                        Log::warning('RegisterController: pdf file not found for parsing', ['path' => $fullPath]);
                        return null;
                    }
                        if (!class_exists('Smalot\\PdfParser\\Parser')) {
                            Log::warning('RegisterController: Smalot\\PdfParser\\Parser not installed — skipping pdf parsing');
                            // try OCR fallback below
                        } else {
                            try {
                                $parser = new \Smalot\PdfParser\Parser();
                                $pdf = $parser->parseFile($fullPath);
                                $text = $pdf->getText();
                            } catch (\Throwable $e) {
                                Log::warning('RegisterController: pdf parsing failed', ['err' => $e->getMessage(), 'path' => $fullPath]);
                                $text = null;
                            }
                        }
                        // If Smalot didn't produce text, try OCR fallback (Imagick + tesseract)
                        if (empty($text)) {
                            try {
                                $ocrText = $this->ocrPdf($fullPath);
                                if (!empty($ocrText)) {
                                    $text = $ocrText;
                                    Log::info('RegisterController: OCR fallback produced text for pdf', ['path' => $fullPath]);
                                } else {
                                    Log::info('RegisterController: OCR fallback produced no text', ['path' => $fullPath]);
                                }
                            } catch (\Throwable $e) {
                                Log::warning('RegisterController: OCR fallback failed', ['err' => $e->getMessage(), 'path' => $fullPath]);
                            }
                        }
                        return $text;
                };

                // extract text from the OR and CR PDFs when available
                $orText = $extractTextFromStored($ud->or_path);
                $crText = $extractTextFromStored($ud->cr_path);
                $debug['extracted'] = ['or_text_present' => !empty($orText), 'cr_text_present' => !empty($crText)];

                // helper to find labeled numbers (OR/CR) in text
                $findNumber = function ($text, $labels = ['OR', 'O.R', 'CR', 'C.R']) {
                    if (empty($text)) return null;
                    // search for patterns like 'O.R. No. 02017126838806' or 'CR No. 299826332'
                    if (preg_match('/(?:O\\.?R\\.?\\s*No\\.?|OR\\s*No\\.?|CR\\s*No\\.?|C\\.?R\\.?\\s*No\\.?)[^0-9]*(\\d{6,30})/i', $text, $m)) {
                        return $m[1];
                    }
                    // fallback: any long number sequence
                    if (preg_match('/\\b(\\d{6,30})\\b/', $text, $m2)) {
                        return $m2[1];
                    }
                    return null;
                };

                $extractedOrNumber = $findNumber($orText);
                $extractedCrNumber = $findNumber($crText);
                if ($extractedOrNumber) {
                    $ud->or_number = $extractedOrNumber;
                    $debug['extracted_or_number'] = $extractedOrNumber;
                }
                if ($extractedCrNumber) {
                    $ud->cr_number = $extractedCrNumber;
                    $debug['extracted_cr_number'] = $extractedCrNumber;
                }

                // extract printed name and validate against submitted firstname+middlename+lastname
                $submittedFullName = trim(($input['firstname'] ?? '') . ' ' . ($input['middlename'] ?? '') . ' ' . ($input['lastname'] ?? ''));
                $searchText = (($orText ?? '') . "\n" . ($crText ?? ''));
                // compute character-based similarity (use PHP similar_text which is character-based)
                $nameFound = false;
                $threshold = 0.7; // 70% match required to auto-accept
                $orPct = null; $crPct = null; $combinedPct = null;
                try {
                    if (!empty($orText) && !empty($submittedFullName)) {
                        similar_text(strtolower($submittedFullName), strtolower($orText), $orPct);
                        $orPct = $orPct / 100.0;
                    }
                    if (!empty($crText) && !empty($submittedFullName)) {
                        similar_text(strtolower($submittedFullName), strtolower($crText), $crPct);
                        $crPct = $crPct / 100.0;
                    }
                    if ((empty($orText) && empty($crText)) && !empty($searchText) && !empty($submittedFullName)) {
                        similar_text(strtolower($submittedFullName), strtolower($searchText), $combinedPct);
                        $combinedPct = $combinedPct / 100.0;
                    }
                    // Decision logic:
                    // - If both OR and CR text exist, require both to meet threshold
                    // - If only one exists, require that one to meet threshold
                    // - Otherwise, fall back to combined text similarity
                    if (!empty($orPct) && !empty($crPct)) {
                        $nameFound = ($orPct >= $threshold && $crPct >= $threshold);
                    } elseif (!empty($orPct)) {
                        $nameFound = ($orPct >= $threshold);
                    } elseif (!empty($crPct)) {
                        $nameFound = ($crPct >= $threshold);
                    } elseif (!empty($combinedPct)) {
                        $nameFound = ($combinedPct >= $threshold);
                    } else {
                        $nameFound = false;
                    }
                    $debug['name_validation'] = ['or_pct' => $orPct, 'cr_pct' => $crPct, 'combined_pct' => $combinedPct, 'threshold' => $threshold];
                    // default pending state based on name matching
                    $ud->from_pending = $nameFound ? false : true;
                    // leniency: if client supplied face verification and score is strong, approve regardless
                    if (isset($input['face_verified']) && isset($input['face_score']) && Schema::hasColumn('user_details', 'face_verified') && Schema::hasColumn('user_details', 'face_score')) {
                        try {
                            $fv = (bool)$input['face_verified'];
                            $fs = floatval($input['face_score']);
                            if ($fv && $fs >= 0.75) {
                                $ud->from_pending = false;
                                $debug['name_validation']['face_override'] = ['face_verified' => $fv, 'face_score' => $fs];
                            }
                        } catch (\Throwable $e) {
                            // ignore parsing errors
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('RegisterController: name similarity check failed', ['err' => $e->getMessage()]);
                    $ud->from_pending = true;
                }

                $ud->save();
                // Generate a simple QR code containing basic user info and persist it if the DB column exists.
                try {
                    if (Schema::hasColumn('user_details', 'qr_path')) {
                        // Build a signed compact token payload containing id,email,role,from_pending and timestamp.
                        // This token is compact (base64url(payload).'.'.base64url(sig)) and can be verified later.
                        $roleName = null;
                        try { $roleName = $user->role ? ($user->role->name ?? null) : null; } catch (\Throwable $e) { $roleName = null; }
                        $fromPending = false;
                        try { $fromPending = (bool)($ud->from_pending ?? false); } catch (\Throwable $e) { $fromPending = false; }
                        $tokenPayload = ['id' => $user->id, 'email' => $user->email, 'role' => $roleName, 'from_pending' => $fromPending, 'ts' => time()];
                        $payloadJson = json_encode($tokenPayload);
                        // base64url encode
                        $b64 = rtrim(strtr(base64_encode($payloadJson), '+/', '-_'), '=');
                        $appKey = config('app.key');
                        if (is_string($appKey) && str_starts_with($appKey, 'base64:')) {
                            $appKey = base64_decode(substr($appKey, 7));
                        }
                        $sig = rtrim(strtr(base64_encode(hash_hmac('sha256', $b64, $appKey, true)), '+/', '-_'), '=');
                        $token = $b64 . '.' . $sig;
                        $qrPayload = $token;
                        $encoded = rawurlencode($qrPayload);
                        $chartUrl = "https://chart.googleapis.com/chart?cht=qr&chs=400x400&chl={$encoded}&choe=UTF-8";
                        // Generate an SVG QR locally using chillerlan/php-qrcode and store it under public/qrs.
                        try {
                            // try a sequence of versions: 0 (auto) then harder limits if overflow occurs
                            $versionsToTry = [0, 10, 20, 40];
                            $svg = null;
                            $debug['qr_try_errors'] = [];
                            foreach ($versionsToTry as $tryVersion) {
                                try {
                                    $options = new QROptions([
                                        'outputType' => QRCode::OUTPUT_MARKUP_SVG,
                                        'eccLevel' => QRCode::ECC_L,
                                        'version' => $tryVersion,
                                        'scale' => 4,
                                    ]);
                                    $qrcode = new QRCode($options);
                                    $svg = $qrcode->render($qrPayload);
                                    $debug['qr_version_used'] = $tryVersion;
                                    break;
                                } catch (\Throwable $e2) {
                                    // record per-version error and continue to next version
                                    $debug['qr_try_errors'][] = ['version' => $tryVersion, 'err' => $e2->getMessage()];
                                    continue;
                                }
                            }
                            if (!$svg) {
                                throw new \Exception('QR generation failed for all tried versions: ' . json_encode($debug['qr_try_errors']));
                            }
                            // If the renderer returned a data: URI (base64), decode it to raw SVG
                            if (is_string($svg) && preg_match('{^data:image/svg\+xml;base64,(.*)}s', $svg, $m)) {
                                $svg = base64_decode($m[1]);
                                $debug['qr_svg_decoded'] = true;
                            }
                            // strip UTF-8 BOM if present and trim leading whitespace
                            if (is_string($svg)) {
                                $svg = preg_replace('/^\xEF\xBB\xBF/', '', $svg);
                                $svg = ltrim($svg);
                            }
                            $debug['qr_token_payload'] = $tokenPayload ?? null;
                            $qrFilename = 'qr_' . Str::random(8) . '.svg';
                            $qrStored = 'qrs/' . $qrFilename;
                            Storage::disk('public')->put($qrStored, $svg);
                            $ud->qr_path = $qrStored;
                            $ud->save();
                            $debug['store_results']['qr'] = $qrStored;
                        } catch (\Throwable $e) {
                            // Record full exception details for debugging, then fallback to external URL
                            $ud->qr_path = $chartUrl;
                            $ud->save();
                            $debug['store_results']['qr'] = 'external_url_stored';
                            $debug['qr_error'] = $e->getMessage();
                            $debug['qr_error_trace'] = $e->getTraceAsString();
                            Log::error('RegisterController: SVG QR generation failed, fallback to external URL', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'tries' => $debug['qr_try_errors'] ?? null]);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('RegisterController: QR generation failed', ['err' => $e->getMessage()]);
                }
                // face verification calls removed
                Log::info('RegisterController: saved userDetails', ['user_id' => $user->id, 'ud_id' => $ud->id, 'or_path' => $ud->or_path, 'cr_path' => $ud->cr_path, 'id_path' => $ud->id_path]);
            }
        }

        // Create a Vehicle record if a plate number was provided
        if (!empty($input['plate_number'])) {
            $vehicleData = [
                'user_id' => $user->id,
                'user_details_id' => $user->userDetail()->first()->id ?? null,
                'plate_number' => $input['plate_number'],
                'vehicle_color' => $input['vehicle_color'] ?? null,
                'vehicle_type' => $input['vehicle_type'] ?? null,
                'brand' => $input['brand'] ?? null,
                'model' => $input['model'] ?? null,
                'or_path' => $orPath,
                'cr_path' => $crPath,
                'or_number' => $input['or_number'] ?? null,
                'cr_number' => $input['cr_number'] ?? null,
            ];
            $veh = Vehicle::create($vehicleData);
            $debug['store_results']['vehicle_created'] = $veh ? $veh->id : null;
        }

        // Teams/personal team creation removed for user self-registration flow.
        // The application previously created a personal Team here but the mobile
        // registration flow should not create teams automatically.

        // Prepare a richer response including user details and vehicles so the
        // mobile client can show what files/paths were saved.
        $user->load(['userDetail', 'vehicles']);

        // Ensure a QR exists even if no files were uploaded. Earlier QR
        // generation lived inside the file-upload branch, so registrations
        // without uploaded files could end up with a null qr_path. Generate
        // and persist an SVG QR here if the column exists and the value is
        // empty.
        try {
            if (Schema::hasColumn('user_details', 'qr_path') && $user->userDetail) {
                $udPost = $user->userDetail;
                if (empty($udPost->qr_path)) {
                        // minimal payload (signed token with id,email,role,from_pending)
                    // build token using loaded userDetail
                    $roleName = null;
                    try { $roleName = $user->role ? ($user->role->name ?? null) : null; } catch (\Throwable $e) { $roleName = null; }
                    $fromPending = (bool)($udPost->from_pending ?? false);
                    $tokenPayload = ['id' => $user->id, 'email' => $user->email, 'role' => $roleName, 'from_pending' => $fromPending, 'ts' => time()];
                    $payloadJson = json_encode($tokenPayload);
                    $b64 = rtrim(strtr(base64_encode($payloadJson), '+/', '-_'), '=');
                    $appKey = config('app.key');
                    if (is_string($appKey) && str_starts_with($appKey, 'base64:')) {
                        $appKey = base64_decode(substr($appKey, 7));
                    }
                    $sig = rtrim(strtr(base64_encode(hash_hmac('sha256', $b64, $appKey, true)), '+/', '-_'), '=');
                    $token = $b64 . '.' . $sig;
                    $qrPayload = $token;
                    try {
                        $versionsToTry = [0, 10, 20, 40];
                        $svg = null;
                        $debug['qr_try_errors_post'] = [];
                        foreach ($versionsToTry as $tryVersion) {
                            try {
                                $options = new QROptions([
                                    'outputType' => QRCode::OUTPUT_MARKUP_SVG,
                                    'eccLevel'   => QRCode::ECC_L,
                                    'version'    => $tryVersion,
                                    'scale'      => 4,
                                ]);
                                $qrcode = new QRCode($options);
                                $svg = $qrcode->render($qrPayload);
                                $debug['qr_version_used_post'] = $tryVersion;
                                break;
                            } catch (\Throwable $e2) {
                                $debug['qr_try_errors_post'][] = ['version' => $tryVersion, 'err' => $e2->getMessage()];
                                continue;
                            }
                        }
                        if (!$svg) {
                            throw new \Exception('QR generation failed for all tried versions: ' . json_encode($debug['qr_try_errors_post']));
                        }
                        // If the renderer returned a data: URI (base64), decode it to raw SVG
                        if (is_string($svg) && preg_match('{^data:image/svg\+xml;base64,(.*)}s', $svg, $m)) {
                            $svg = base64_decode($m[1]);
                            $debug['qr_svg_decoded_post'] = true;
                        }
                        if (is_string($svg)) {
                            $svg = preg_replace('/^\xEF\xBB\xBF/', '', $svg);
                            $svg = ltrim($svg);
                        }
                        $qrFilename = 'qr_' . Str::random(8) . '.svg';
                        $qrStored = 'qrs/' . $qrFilename;
                        Storage::disk('public')->put($qrStored, $svg);
                        $udPost->qr_path = $qrStored;
                        $udPost->save();
                        $debug['store_results']['qr_generated_post'] = $qrStored;
                        // refresh the loaded relation so conversion below picks it up
                        $user->load('userDetail');
                    } catch (\Throwable $e) {
                        // log and include trace in debug so temp scripts can show the cause
                        $debug['qr_error_post'] = $e->getMessage();
                        $debug['qr_error_post_trace'] = $e->getTraceAsString();
                        Log::error('RegisterController: post-load SVG QR generation failed', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'tries' => $debug['qr_try_errors_post'] ?? null]);
                        // fallback to external Google Chart if local generation fails
                        $encodedFallback = rawurlencode($qrPayload);
                        $chartUrl = "https://chart.googleapis.com/chart?cht=qr&chs=400x400&chl={$encodedFallback}&choe=UTF-8";
                        $udPost->qr_path = $chartUrl;
                        $udPost->save();
                        $debug['store_results']['qr_generated_post'] = 'external_fallback';
                        $user->load('userDetail');
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('RegisterController: ensuring qr post-load failed', ['err' => $e->getMessage()]);
        }

        // Convert storage paths to public URLs where applicable
    if ($user->userDetail) {
            if (!empty($user->userDetail->or_path)) {
                $user->userDetail->or_path = Storage::url($user->userDetail->or_path);
            }
            if (!empty($user->userDetail->cr_path)) {
                $user->userDetail->cr_path = Storage::url($user->userDetail->cr_path);
            }
            if (!empty($user->userDetail->id_path)) {
                $user->userDetail->id_path = Storage::url($user->userDetail->id_path);
            }
            // convert deed and qr path to public urls as well
            if (!empty($user->userDetail->deed_path)) {
                // only convert to public URL if the stored value is a local storage path
                if (preg_match('/^https?:\/\//i', $user->userDetail->deed_path)) {
                    // external URL already
                } else {
                    $user->userDetail->deed_path = Storage::url($user->userDetail->deed_path);
                }
            }
            if (!empty($user->userDetail->qr_path)) {
                // qr_path may be either a stored path (qrs/...) or an external URL string.
                if (preg_match('/^https?:\/\//i', $user->userDetail->qr_path)) {
                    // leave external URL as-is
                } else {
                    $user->userDetail->qr_path = Storage::url($user->userDetail->qr_path);
                }
            }
        }

        foreach ($user->vehicles as $veh) {
            if (!empty($veh->or_path)) {
                $veh->or_path = Storage::url($veh->or_path);
            }
            if (!empty($veh->cr_path)) {
                $veh->cr_path = Storage::url($veh->cr_path);
            }
        }

        $success['user'] = $user;
    // If qr_path exists on user_details include it at top-level for convenience
    if ($user->userDetail && !empty($user->userDetail->qr_path)) {
        $success['qr'] = $user->userDetail->qr_path;
    }
    // attach debug information for troubleshooting (temporary)
    $success['debug'] = $debug;

        return $this->sendResponse($success, 'User register successfully.');
    }

    /**
     * Parse a single uploaded PDF and return extracted text, name, and OR/CR numbers.
     * This endpoint is intended for client-side pre-validation before final submit.
     */
    public function parseDocument(Request $request)
    {
        if (!$request->hasFile('doc')) {
            return $this->sendError('No file uploaded', ['doc' => 'required']);
        }
        $file = $request->file('doc');
        // temporarily store the file so we can parse it
        $tmpName = 'tmp_parse_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $stored = $file->storeAs('tmp', $tmpName, 'local');
        $fullPath = storage_path('app/' . $stored);

        $result = ['text' => null, 'or_number' => null, 'cr_number' => null];

        if (class_exists('Smalot\\PdfParser\\Parser')) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($fullPath);
                $text = $pdf->getText();
                $result['text'] = $text;
                // extract numbers same heuristics as register()
                if (preg_match('/(?:O\\.?R\\.?\\s*No\\.?|OR\\s*No\\.?)[^0-9]*(\\d{6,30})/i', $text, $m)) {
                    $result['or_number'] = $m[1];
                }
                if (preg_match('/(?:C\\.?R\\.?\\s*No\\.?|CR\\s*No\\.?)[^0-9]*(\\d{6,30})/i', $text, $m2)) {
                    $result['cr_number'] = $m2[1];
                }

                // Try to extract a probable name from common labels found in OR/CR samples
                $possibleName = null;
                if (preg_match('/RECEIVED FROM[\s:\(]*([^\r\n]{3,200})/i', $text, $nm)) {
                    $possibleName = trim($nm[1]);
                } elseif (preg_match('/COMPLETE OWNER(?:' . "'" . 'S)? NAME[\s:\(]*([^\r\n]{3,200})/i', $text, $nm2)) {
                    $possibleName = trim($nm2[1]);
                } elseif (preg_match('/OWNER(?:' . "'" . 'S)? NAME[\s:\(]*([^\r\n]{3,200})/i', $text, $nm3)) {
                    $possibleName = trim($nm3[1]);
                } elseif (preg_match('/NAME[\s:\(]*([^\r\n]{3,200})/i', $text, $nm4)) {
                    $possibleName = trim($nm4[1]);
                }

                if (!empty($possibleName)) {
                    // normalize whitespace
                    $possibleName = preg_replace('/\s+/', ' ', $possibleName);
                    // if in LAST, FIRST format convert to FIRST LAST
                    if (strpos($possibleName, ',') !== false) {
                        $parts = array_map('trim', explode(',', $possibleName, 2));
                        if (count($parts) === 2) {
                            $possibleName = $parts[1] . ' ' . $parts[0];
                        }
                    }
                    $result['name'] = $possibleName;
                }
            } catch (\Throwable $e) {
                Log::warning('parseDocument: pdf parse failed', ['err' => $e->getMessage()]);
            }
            // If Smalot did not return text, try OCR fallback
            if (empty($result['text'])) {
                try {
                    $ocr = $this->ocrPdf($fullPath);
                    if (!empty($ocr)) {
                        $result['text'] = $ocr;
                        Log::info('parseDocument: OCR fallback provided text', ['path' => $fullPath]);
                    } else {
                        Log::info('parseDocument: OCR fallback returned empty for', ['path' => $fullPath]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('parseDocument: OCR fallback failed', ['err' => $e->getMessage()]);
                }
            }
        } else {
            Log::warning('parseDocument: Smalot\\PdfParser not installed — skipping parse');
        }

        // clean up temp file
        try { @unlink($fullPath); } catch (\Throwable $e) {}

        return $this->sendResponse(['parsed' => $result], 'Parsed document');
    }

    /**
     * OCR a PDF using Imagick to render pages and Tesseract CLI to extract text.
     * Returns combined text or null on failure/empty.
     */
    private function ocrPdf(string $pdfPath): ?string
    {
        // require imagick PHP extension
        if (!extension_loaded('imagick')) {
            Log::warning('ocrPdf: imagick extension not available');
            return null;
        }

        // confirm tesseract binary exists (best-effort)
        $tessCheck = null;
        try {
            $tessCheck = trim(@shell_exec('tesseract -v 2>&1'));
        } catch (\Throwable $e) {
            $tessCheck = null;
        }
        if (empty($tessCheck)) {
            Log::warning('ocrPdf: tesseract binary not found in PATH');
            return null;
        }

        $text = '';
        $tmpDir = storage_path('app/tmp/ocr_' . Str::random(6));
        if (!is_dir($tmpDir)) @mkdir($tmpDir, 0755, true);

        try {
            $im = new \Imagick();
            // read PDF (may require ghostscript on some systems)
            $im->readImage($pdfPath);
            $num = $im->getNumberImages();
            $i = 0;
            foreach ($im as $page) {
                $page->setImageFormat('png');
                $page->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                $page->setImageBackgroundColor('white');
                $pngPath = $tmpDir . DIRECTORY_SEPARATOR . 'page_' . $i . '.png';
                $page->writeImage($pngPath);

                // run tesseract on this image, output to stdout
                $cmd = 'tesseract ' . escapeshellarg($pngPath) . ' stdout -l eng 2>&1';
                $out = @shell_exec($cmd);
                if ($out) {
                    $text .= "\n" . $out;
                }
                // remove page image
                @unlink($pngPath);
                $i++;
            }
            // clear imagick
            $im->clear();
            $im->destroy();
        } catch (\Throwable $e) {
            Log::warning('ocrPdf: imagick render failed', ['err' => $e->getMessage()]);
            // cleanup
            @array_map('unlink', glob($tmpDir . DIRECTORY_SEPARATOR . '*.png'));
            @rmdir($tmpDir);
            return null;
        }

        // cleanup dir
        @rmdir($tmpDir);

        $text = trim($text);
        return empty($text) ? null : $text;
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
        'remember' => 'boolean'
    ]);

    $credentials = $request->only('email', 'password');
    $remember = $request->remember ?? false;

    if (!Auth::attempt($credentials, $remember)) {
        return response()->json(['message' => 'Email or Password is incorrect.'], 401);
    }

    $user = Auth::user();
    $success['token'] = $user->createToken('MyApp')->plainTextToken;
    $success['name'] = $user->name;

    return response()->json(['data' => $success, 'message' => 'Login Successfully'], 200);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse([], 'Logged out successfully');
    }
}