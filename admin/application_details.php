<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = (int)$_GET['id'];

// Get application data
$stmt = $conn->prepare("SELECT * FROM applications WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$application = $stmt->get_result()->fetch_assoc();

if (!$application) {
    header('Location: dashboard.php');
    exit;
}

// Get all documents for this application
$docStmt = $conn->prepare("SELECT * FROM application_docs WHERE application_id = ?");
$docStmt->bind_param("i", $id);
$docStmt->execute();
$documents = $docStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Group documents by type for easier access
$documentsByType = [];
foreach ($documents as $doc) {
    $documentsByType[$doc['document_type']][] = $doc;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Details - <?= htmlspecialchars($application['full_name']) ?></title>
    <link rel="stylesheet" href="../assets/css/application_details.css">
    <style>
        .document-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-left: 10px;
        }
        .document-link .view-button {
            padding: 2px 8px;
            font-size: 0.8em;
        }
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .radio-group {
            display: flex;
            gap: 15px;
            margin-top: 5px;
        }
        .radio-group.vertical {
            flex-direction: column;
            gap: 5px;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Application Details</h1>
            <a href="applicants.php" class="back-button">← Back to Applications</a>
        </header>

        <main>
            <div class="application-header">
                <h2><?= htmlspecialchars($application['full_name']) ?></h2>
                <p>Registration Number: <?= htmlspecialchars($application['reg_number']) ?></p>
                <p>Status: <span class="status-badge <?= strtolower($application['status']) ?>">
                    <?= htmlspecialchars($application['status']) ?>
                </span></p>
            </div>

            <div class="application-details">
                <!-- SECTION 1: PERSONAL DETAILS -->
                <div class="form-section">
                    <h2>1. PERSONAL DETAILS OF THE APPLICANT</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name</label>
                            <p><?= htmlspecialchars($application['full_name']) ?></p>
                        </div>
                        <div class="form-group">
                            <label>Registration Number</label>
                            <p><?= htmlspecialchars($application['reg_number']) ?></p>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>School</label>
                            <p><?= htmlspecialchars($application['school']) ?></p>
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <p><?= $application['gender'] === 'M' ? 'Male' : 'Female' ?></p>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Home Address</label>
                            <p><?= htmlspecialchars($application['home_address']) ?></p>
                        </div>
                        <div class="form-group">
                            <label>Telephone Number</label>
                            <p><?= htmlspecialchars($application['phone']) ?></p>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Home County</label>
                            <p><?= htmlspecialchars($application['county']) ?></p>
                        </div>
                        <div class="form-group">
                            <label>Sub-county</label>
                            <p><?= htmlspecialchars($application['subcounty']) ?></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Name of Next of Kin</label>
                        <p><?= htmlspecialchars($application['next_of_kin']) ?></p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Address of Next of Kin</label>
                            <p><?= htmlspecialchars($application['kin_address']) ?></p>
                        </div>
                        <div class="form-group">
                            <label>Telephone Number</label>
                            <p><?= htmlspecialchars($application['kin_phone']) ?></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Name of Chief</label>
                        <p><?= htmlspecialchars($application['chief_name']) ?></p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Chief's Address</label>
                            <p><?= htmlspecialchars($application['chief_address']) ?></p>
                        </div>
                        <div class="form-group">
                            <label>Telephone Number</label>
                            <p><?= htmlspecialchars($application['chief_phone']) ?></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Disability</label>
                        <p><?= htmlspecialchars($application['disability']) ?>
                        <?php if ($application['disability'] === 'Yes'): ?>
                            : <?= htmlspecialchars($application['disability_details']) ?>
                        <?php endif; ?>
                        </p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Student's Status</label>
                            <p><?= htmlspecialchars($application['student_status']) ?></p>
                        </div>
                        <div class="form-group">
                            <label>Accommodation Status</label>
                            <p><?= htmlspecialchars($application['accommodation']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: FAMILY BACKGROUND -->
                <div class="form-section">
                    <h2>2. FAMILY BACKGROUND</h2>
                    
                    <div class="form-group">
                        <label>Parental Status</label>
                        <p><?= htmlspecialchars($application['parental_status']) ?></p>
                    </div>

                    <?php if ($application['parental_status'] !== 'Orphan'): ?>
                    <div id="parent_details">
                        <div class="form-row">
                            <div class="form-group">
                                <h3>Father's Information</h3>
                                <label>Age</label>
                                <p><?= htmlspecialchars($application['father_age']) ?></p>
                                
                                <label>Occupation</label>
                                <p><?= htmlspecialchars($application['father_occupation']) ?></p>
                                
                                <label>Current Employer</label>
                                <p><?= htmlspecialchars($application['father_employer']) ?></p>
                                
                                <?php if (isset($documentsByType['Health Evidence'])): ?>
                                    <label>Health Evidence</label>
                                    <?php foreach ($documentsByType['Health Evidence'] as $doc): ?>
                                        <?php if (strpos($doc['original_name'], 'father') !== false): ?>
                                            <p>
                                                <?= htmlspecialchars($doc['original_name']) ?>
                                                <span class="document-link">
                                                    <a href="view_document.php?id=<?= $doc['id'] ?>" class="view-button">View</a>
                                                </span>
                                            </p>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <h3>Mother's Information</h3>
                                <label>Age</label>
                                <p><?= htmlspecialchars($application['mother_age']) ?></p>
                                
                                <label>Occupation</label>
                                <p><?= htmlspecialchars($application['mother_occupation']) ?></p>
                                
                                <label>Current Employer</label>
                                <p><?= htmlspecialchars($application['mother_employer']) ?></p>
                                
                                <?php if (isset($documentsByType['Health Evidence'])): ?>
                                    <label>Health Evidence</label>
                                    <?php foreach ($documentsByType['Health Evidence'] as $doc): ?>
                                        <?php if (strpos($doc['original_name'], 'mother') !== false): ?>
                                            <p>
                                                <?= htmlspecialchars($doc['original_name']) ?>
                                                <span class="document-link">
                                                    <a href="view_document.php?id=<?= $doc['id'] ?>" class="view-button">View</a>
                                                </span>
                                            </p>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($application['parental_status'] === 'Orphan' && isset($documentsByType['Death Certificate'])): ?>
                    <div class="form-group">
                        <h3>Death Certificate(s)</h3>
                        <?php foreach ($documentsByType['Death Certificate'] as $doc): ?>
                            <p>
                                <?= htmlspecialchars($doc['original_name']) ?>
                                <span class="document-link">
                                    <a href="view_document.php?id=<?= $doc['id'] ?>" class="view-button">View</a>
                                </span>
                            </p>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Siblings Information</label>
                        <label>Total number of siblings (excluding yourself)</label>
                        <p><?= htmlspecialchars($application['siblings_count']) ?></p>
                        
                        <label>No. of brothers/sisters in:</label>
                        <div class="form-row">
                            <div class="form-group">
                                <label>University/College/Tertiary Institution</label>
                                <p><?= htmlspecialchars($application['siblings_tertiary']) ?></p>
                            </div>
                            <div class="form-group">
                                <label>Secondary</label>
                                <p><?= htmlspecialchars($application['siblings_secondary']) ?></p>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>How many are out of School?</label>
                                <p><?= htmlspecialchars($application['siblings_out']) ?></p>
                            </div>
                            <div class="form-group">
                                <label>Why?</label>
                                <p><?= htmlspecialchars($application['siblings_out_reason']) ?></p>
                            </div>
                        </div>
                        
                        <label>Any who are working and their occupations</label>
                        <p><?= htmlspecialchars($application['siblings_working']) ?></p>
                    </div>
                </div>

                <!-- SECTION 3: OTHER INFORMATION -->
                <div class="form-section">
                    <h2>3. OTHER INFORMATION</h2>
                    
                    <div class="form-group">
                        <label>Who paid your secondary school fee?</label>
                        <p><?= htmlspecialchars($application['secondary_payer']) ?></p>
                        
                        <?php if (isset($documentsByType['Other'])): ?>
                            <?php foreach ($documentsByType['Other'] as $doc): ?>
                                <?php if (strpos($doc['original_name'], 'secondary_fee') !== false): ?>
                                    <p>
                                        <?= htmlspecialchars($doc['original_name']) ?>
                                        <span class="document-link">
                                            <a href="view_document.php?id=<?= $doc['id'] ?>" class="view-button">View</a>
                                        </span>
                                    </p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Are you/have you been on work study program?</label>
                            <p><?= htmlspecialchars($application['previous_work_study']) ?></p>
                        </div>
                        <div class="form-group">
                            <?php if (isset($documentsByType['Other'])): ?>
                                <?php foreach ($documentsByType['Other'] as $doc): ?>
                                    <?php if (strpos($doc['original_name'], 'work_study') !== false): ?>
                                        <p>
                                            <?= htmlspecialchars($doc['original_name']) ?>
                                            <span class="document-link">
                                                <a href="view_document.php?id=<?= $doc['id'] ?>" class="view-button">View</a>
                                            </span>
                                        </p>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Do you receive any financial support from external sponsors?</label>
                        <p><?= htmlspecialchars($application['external_sponsor']) ?>
                        <?php if ($application['external_sponsor'] === 'Yes'): ?>
                            : <?= htmlspecialchars($application['sponsor_details']) ?>
                        <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Have you completed paying tuition fee for this academic year?</label>
                            <p><?= htmlspecialchars($application['fee_payment_status']) ?>
                            <?php if ($application['fee_payment_status'] === 'No'): ?>
                                (Balance: <?= htmlspecialchars($application['fee_balance']) ?>)
                            <?php endif; ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <?php if (isset($documentsByType['Fee Statement'])): ?>
                                <label>Fee Statement</label>
                                <?php foreach ($documentsByType['Fee Statement'] as $doc): ?>
                                    <p>
                                        <?= htmlspecialchars($doc['original_name']) ?>
                                        <span class="document-link">
                                            <a href="view_document.php?id=<?= $doc['id'] ?>" class="view-button">View</a>
                                        </span>
                                    </p>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Have you ever deferred your University studies?</label>
                        <p><?= htmlspecialchars($application['deferred_studies']) ?>
                        <?php if ($application['deferred_studies'] === 'Yes'): ?>
                            : <?= htmlspecialchars($application['deferral_reason']) ?>
                        <?php endif; ?>
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Additional Information</label>
                        <p><?= htmlspecialchars($application['additional_info']) ?></p>
                        
                        <?php if (isset($documentsByType['Other'])): ?>
                            <label>Other Supporting Documents</label>
                            <?php foreach ($documentsByType['Other'] as $doc): ?>
                                <?php if (strpos($doc['original_name'], 'other_doc') !== false): ?>
                                    <p>
                                        <?= htmlspecialchars($doc['original_name']) ?>
                                        <span class="document-link">
                                            <a href="view_document.php?id=<?= $doc['id'] ?>" class="view-button">View</a>
                                        </span>
                                    </p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-footer">
                    <div class="declaration">
                        <h3>DECLARATION</h3>
                        <p>I declare that the information given in this form is correct to the best of my knowledge. I understand that giving false information may lead to disqualification from the Work Study Program.</p>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Applicant's Signature</label>
                                <p><?= htmlspecialchars($application['applicant_signature']) ?></p>
                            </div>
                            <div class="form-group">
                                <label>Date</label>
                                <p><?= htmlspecialchars($application['signature_date']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>