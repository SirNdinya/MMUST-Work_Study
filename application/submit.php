<?php
require __DIR__ . '/../config/init.php';


session_start();

require_once __DIR__ . '/../includes/functions.php';

// Validate form submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header('Location: application.php');
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

// Save form data in session for repopulating if error occurs
$_SESSION['form_data'] = $_POST;

/// Process file uploads
$uploads = [];
$requiredFiles = ['fee_statement'];

foreach ($requiredFiles as $fileField) {
    // 1. Check if file field exists in upload
    if (!isset($_FILES[$fileField]) || !is_array($_FILES[$fileField])) {
        throw new Exception("File upload field '$fileField' missing or invalid");
    }

    $file = $_FILES[$fileField];

    // 2. Verify upload error code
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new Exception("Invalid file upload error structure for '$fileField'");
    }

    // 3. Detailed error messages
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds server size limit (php.ini)',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit (MAX_FILE_SIZE)',
        UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was selected for upload',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary upload folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload blocked by PHP extension'
    ];

    // 4. Handle file upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = $errorMessages[$file['error']] ?? 'Unknown upload error';
        throw new Exception("Upload failed for '$fileField': $errorMsg (Code: {$file['error']})");
    }

    // 5. Additional security checks
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new Exception("Invalid file upload attempt for '$fileField'");
    }

    // 6. Verify file exists and is readable
    if (!file_exists($file['tmp_name']) || !is_readable($file['tmp_name'])) {
        throw new Exception("Uploaded file is not accessible for '$fileField'");
    }

    // 7. File size validation
    $max_size = MAX_FILE_SIZE; // Can be defined globally or per file type
    if ($file['size'] > $max_size) {
        throw new Exception("File size for '$fileField' exceeds the maximum allowed size of $max_size bytes.");
    }

    // 8. File extension validation (instead of MIME type validation)
    $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];

    // Get file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Check if the extension is in the allowed list
    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception("Invalid file extension for '$fileField': '$extension'. Allowed types: " . implode(', ', $allowedExtensions));
    }

    // 9. Process the upload
    try {
        $uploadResult = processUpload($file);
        // Store the result (e.g., file path, name) for further processing
        $uploads[$fileField] = $uploadResult;
    } catch (Exception $e) {
        throw new Exception("Failed to process '$fileField': " . $e->getMessage());
    }
}


// Begin transaction
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO applications (
        full_name, reg_number, school, gender, home_address, phone, county, subcounty,
        next_of_kin, kin_address, kin_phone, chief_name, chief_address, chief_phone,
        disability, disability_details, student_status, accommodation, parental_status,
        father_age, father_occupation, father_employer, mother_age, mother_occupation,
        mother_employer, siblings_count, siblings_tertiary, siblings_secondary,
        siblings_out, siblings_out_reason, siblings_working, secondary_payer,
        previous_work_study, external_sponsor, sponsor_details, fee_payment_status,
        fee_balance, deferred_studies, deferral_reason, additional_info, applicant_signature, 
        signature_date
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )");

    // Store all POST values in variables first
    $full_name = $_POST['full_name'];
    $reg_number = $_POST['reg_number'];
    $school = $_POST['school'];
    $gender = $_POST['gender'];
    $home_address = $_POST['home_address'];
    $phone = $_POST['phone'];
    $county = $_POST['county'];
    $subcounty = $_POST['subcounty'];
    $next_of_kin = $_POST['next_of_kin'];
    $kin_address = $_POST['kin_address'];
    $kin_phone = $_POST['kin_phone'];
    $chief_name = $_POST['chief_name'];
    $chief_address = $_POST['chief_address'];
    $chief_phone = $_POST['chief_phone'];
    $disability = $_POST['disability'];
    $disability_details = ($_POST['disability'] === 'Yes') ? $_POST['disability_details'] : null;
    $student_status = $_POST['student_status'];
    $accommodation = $_POST['accommodation'];
    $parental_status = $_POST['parental_status'];

    // Handle conditional parent data
    $father_age = ($_POST['parental_status'] !== 'Orphan') ? $_POST['father_age'] : null;
    $father_occupation = ($_POST['parental_status'] !== 'Orphan') ? $_POST['father_occupation'] : null;
    $father_employer = ($_POST['parental_status'] !== 'Orphan') ? $_POST['father_employer'] : null;
    $mother_age = ($_POST['parental_status'] !== 'Orphan') ? $_POST['mother_age'] : null;
    $mother_occupation = ($_POST['parental_status'] !== 'Orphan') ? $_POST['mother_occupation'] : null;
    $mother_employer = ($_POST['parental_status'] !== 'Orphan') ? $_POST['mother_employer'] : null;

    // Siblings data
    $siblings_count = $_POST['siblings_count'] ?? 0;
    $siblings_tertiary = $_POST['siblings_tertiary'] ?? 0;
    $siblings_secondary = !empty($_POST['siblings_secondary']) ? (int)$_POST['siblings_secondary'] : null;
    $siblings_out = $_POST['siblings_out'] ?? 0;
    $siblings_out_reason = $_POST['siblings_out_reason'] ?? null;
    $siblings_working = $_POST['siblings_working'] ?? null;
    $secondary_payer = $_POST['secondary_payer'] ?? null;
    $previous_work_study = $_POST['previous_work_study'] ?? 'No';

    // Sponsor data
    $external_sponsor = $_POST['external_sponsor'];
    $sponsor_details = ($_POST['external_sponsor'] === 'Yes') ? $_POST['sponsor_details'] : null;

    // Fee data
    $fee_payment_status = $_POST['fee_payment_status'];
    $fee_balance = ($_POST['fee_payment_status'] === 'No') ? $_POST['fee_balance'] : null;

    // Deferral data
    $deferred_studies = $_POST['deferred_studies'];
    $deferral_reason = ($_POST['deferred_studies'] === 'Yes') ? implode(', ', $_POST['deferral_reason'] ?? []) : null;

    // Additional info
    $additional_info = $_POST['additional_info'] ?? null;

    // Signature
    $applicant_signature = $_POST['applicant_signature'];
    $signature_date = $_POST['signature_date'];

    // $status = 'Pending'; // Set default status


    // Now bind the variables
    $stmt->bind_param(
        "ssssssssssssssssssssssssssssssssssssssssss", 
        $full_name,
        $reg_number,
        $school,
        $gender,
        $home_address,
        $phone,
        $county,
        $subcounty,
        $next_of_kin,
        $kin_address,
        $kin_phone,
        $chief_name,
        $chief_address,
        $chief_phone,
        $disability,
        $disability_details,
        $student_status,
        $accommodation,
        $parental_status,
        $father_age,
        $father_occupation,
        $father_employer,
        $mother_age,
        $mother_occupation,
        $mother_employer,
        $siblings_count,
        $siblings_tertiary,
        $siblings_secondary,
        $siblings_out,
        $siblings_out_reason,
        $siblings_working,
        $secondary_payer,
        $previous_work_study,
        $external_sponsor,
        $sponsor_details,
        $fee_payment_status,
        $fee_balance,
        $deferred_studies,
        $deferral_reason,
        $additional_info,  
        $applicant_signature,
        $signature_date,

    );
    
    
    
    $stmt->execute();
    $applicationId = $conn->insert_id;

  // Process file uploads
$fileFields = [
    'death_certificates' => 'Death Certificate',
    'fee_statement' => 'Fee Statement',
    'father_health' => 'Health Evidence',
    'mother_health' => 'Health Evidence',
    'secondary_fee_proof' => 'Other',
    'work_study_proof' => 'Other',
    'other_documents' => 'Other'
];

// Prepare the document statement outside the loop
$docStmt = $conn->prepare("INSERT INTO application_docs (
    application_id, document_type, original_name, 
    saved_name, file_type, file_size, file_content
) VALUES (?, ?, ?, ?, ?, ?, ?)");

foreach ($fileFields as $field => $docType) {
    if (!empty($_FILES[$field]['name'][0] ?? $_FILES[$field]['name'])) {
        $files = is_array($_FILES[$field]['name']) ? 
            reArrayFiles($_FILES[$field]) : [$_FILES[$field]];

            foreach ($files as $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = processUpload($file);
                    if ($uploadResult) {
                        $saved_name = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $uploadResult['original_name']);
            
                        // Bind with "b" for file_content
                        $docStmt->bind_param(
                            "issssib",
                            $applicationId,
                            $docType,
                            $uploadResult['original_name'],
                            $saved_name,
                            $uploadResult['file_type'],
                            $uploadResult['file_size'],
                            $null  // placeholder for file_content
                        );
            
                        // Send binary data safely
                        $docStmt->send_long_data(6, $uploadResult['file_content']);  // index 6 = 7th param
                        $docStmt->execute();
                    }
                }
            }
            
    }
}
    $conn->commit();
    
    
    // Clear form data from session
    unset($_SESSION['form_data']);
    
    $_SESSION['application_status'] = 'Already Applied';

    
    // Redirect to success page
    header('Location: application_success.php');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error submitting application: " . $e->getMessage();
    header('Location: application.php');
    exit;
}