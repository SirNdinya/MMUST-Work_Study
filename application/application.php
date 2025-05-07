<?php
session_start();
require_once __DIR__ . '/../config/init.php';

// Display errors if they exist
if (!empty($_SESSION['error'])) {
    echo '<div class="alert alert-error">';
    echo htmlspecialchars($_SESSION['error']);
    echo '</div>';
    unset($_SESSION['error']);
}

// Repopulate form fields if they exist
$formData = $_SESSION['form_data'] ?? [];

// Check if user is logged in as student
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work-Study Application</title>
    <link rel="stylesheet" href="../assets/css/application.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
    <nav class="simple-dashboard-nav">
    <div class="nav-container">
        <a href="http://localhost/work_study/pages/student_dashboard.php" class="back-to-dashboard">
            ← Back to Dashboard
        </a>
    </div>
</nav>


        <div class="header">
            <img src="../assets/images/logo.png" alt="University Logo">
            <h1>MASINDE MULIRO UNIVERSITY OF SCIENCE AND TECHNOLOGY</h1>
            <h2>STUDENT AFFAIRS DEPARTMENT - WORKSTUDY PROGRAM</h2>
            <h3>2024/2025 ACADEMIC YEAR</h3>
        </div>

        <form id="applicationForm" action="submit.php" method="post" enctype="multipart/form-data">
            <!-- SECTION 1: PERSONAL DETAILS -->
            <div class="form-section">
                <h2>1. PERSONAL DETAILS OF THE APPLICANT</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">i. Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($_SESSION['full_name'] ?? '') ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="reg_number">Registration Number</label>
                        <input type="text" id="reg_number" name="reg_number" value="<?= htmlspecialchars($_SESSION['reg_number'] ?? '') ?>" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="school">ii. School</label>
                        <select id="school" name="school" required>
                            <option value="">Select School</option>
                            <option value="SNS">School of Natural Sciences</option>
                            <option value="SES">School of Engineering Sciences</option>
                            <option value="SHS">School of Health Sciences</option>
                            <option value="SBSS">School of Biological and Physical Sciences</option>
                            <option value="SASS">School of Arts and Social Sciences</option>
                            <option value="SED">School of Education</option>
                            <option value="SNAHS">School of Nursing and Allied Health Sciences</option>
                            <option value="SMS">School of Medicine</option>
                            <option value="SASNR">School of Agriculture and Natural Resources</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>iii. Gender</label>
                        <div class="radio-group">
                            <input type="radio" id="male" name="gender" value="M" required>
                            <label for="male">Male</label>
                            <input type="radio" id="female" name="gender" value="F">
                            <label for="female">Female</label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="home_address">iv. Home Address</label>
                        <input type="text" id="home_address" name="home_address" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Telephone Number</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="county">v. Home County</label>
                        <input type="text" id="county" name="county" required>
                    </div>
                    <div class="form-group">
                        <label for="subcounty">Sub-county</label>
                        <input type="text" id="subcounty" name="subcounty" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="next_of_kin">vi. Name of Next of Kin</label>
                    <input type="text" id="next_of_kin" name="next_of_kin" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="kin_address">vii. Address of Next of Kin</label>
                        <input type="text" id="kin_address" name="kin_address" required>
                    </div>
                    <div class="form-group">
                        <label for="kin_phone">Telephone Number</label>
                        <input type="tel" id="kin_phone" name="kin_phone" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="chief_name">viii. Name of Chief</label>
                    <input type="text" id="chief_name" name="chief_name" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="chief_address">ix. Chief's Address</label>
                        <input type="text" id="chief_address" name="chief_address" required>
                    </div>
                    <div class="form-group">
                        <label for="chief_phone">Telephone Number</label>
                        <input type="tel" id="chief_phone" name="chief_phone" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="disability">x. Do you have any disability?</label>
                    <select id="disability" name="disability" required>
                        <option value="">Select</option>
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                    <input type="text" id="disability_details" name="disability_details" 
                           placeholder="If Yes, specify" style="margin-top:8px; display:none;">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>xi. Student's Status</label>
                        <div class="radio-group">
                            <input type="radio" id="government" name="student_status" value="Government Sponsored" required>
                            <label for="government">Government Sponsored (KUCCPS)</label>
                            <input type="radio" id="self" name="student_status" value="Self Sponsored">
                            <label for="self">Self Sponsored (PSSP)</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>xii. Accommodation Status</label>
                        <div class="radio-group">
                            <input type="radio" id="resident" name="accommodation" value="Resident" required>
                            <label for="resident">Resident</label>
                            <input type="radio" id="non_resident" name="accommodation" value="Non Resident">
                            <label for="non_resident">Non Resident</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: FAMILY BACKGROUND -->
            <div class="form-section">
                <h2>2. FAMILY BACKGROUND</h2>
                
                <div class="form-group">
                    <label>A. Parental Status</label>
                    <div class="radio-group vertical">
                        <input type="radio" id="both_parents" name="parental_status" value="Both parents" required>
                        <label for="both_parents">Have both parents</label>
                        
                        <input type="radio" id="one_parent" name="parental_status" value="One parent">
                        <label for="one_parent">Have one parent</label>
                        
                        <input type="radio" id="orphan" name="parental_status" value="Orphan">
                        <label for="orphan">Total orphan</label>
                    </div>
                </div>

                <div id="parent_details">
                    <div class="form-row">
                        <div class="form-group">
                            <h3>B(i). Father's Information</h3>
                            <label>1. Age</label>
                            <input type="number" name="father_age" min="30" max="100">
                            
                            <label>2. Occupation</label>
                            <input type="text" name="father_occupation">
                            
                            <label>3. Current Employer</label>
                            <input type="text" name="father_employer">
                            
            
                        </div>
                        
                        <div class="form-group">
                            <h3>B(ii). Mother's Information</h3>
                            <label>1. Age</label>
                            <input type="number" name="mother_age" min="25" max="100">
                            
                            <label>2. Occupation</label>
                            <input type="text" name="mother_occupation">
                            
                            <label>3. Current Employer</label>
                            <input type="text" name="mother_employer">
                            
                         
                        </div>
                    </div>
                </div>

                <div id="death_cert_section" style="display:none;">
                    <div class="form-group">
                        <label>Death Certificate(s) (Attach)</label>
                        <div id="death_certificates_wrapper">
                            <!-- initial input -->
                            <div class="death-cert-input">
                                <input type="file" name="death_certificates[]" class="form-control mb-2" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addDeathCertificate()">+ Add Another</button>
                        <div id="death_certificates_preview" class="file-previews mt-2"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label>C. Siblings Information</label>
                    <label>Total number of siblings (excluding yourself)</label>
                    <input type="number" name="siblings_count" min="0">
                    
                    <label>No. of brothers/sisters in:</label>
                    <div class="form-row">
                        <div class="form-group">
                            <label>i. University/College/Tertiary Institution</label>
                            <input type="number" name="siblings_tertiary" min="0">
                        </div>
                        <div class="form-group">
                            <label>ii. Secondary</label>
                            <input type="number" name="siblings_secondary" min="0">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>iii. How many are out of School?</label>
                            <input type="number" name="siblings_out" min="0">
                        </div>
                        <div class="form-group">
                            <label>Why?</label>
                            <input type="text" name="siblings_out_reason">
                        </div>
                    </div>
                    
                    <label>iv. Any who are working and their occupations</label>
                    <textarea name="siblings_working" rows="3"></textarea>
                </div>
            </div>

            <!-- SECTION 3: OTHER INFORMATION -->
            <div class="form-section">
                <h2>3. OTHER INFORMATION</h2>
                
                <div class="form-group">
                    <label>i. Who paid your secondary school fee?</label>
                    <input type="text" name="secondary_payer">
                    <div class="file-upload-container">
                        <label>Attach evidence</label>
                        <input type="file" name="secondary_fee_proof" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div id="secondary_fee_preview" class="file-previews"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>ii. Are you/have you been on work study program?</label>
                        <select name="previous_work_study" required>
                            <option value="">Select</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Attach evidence</label>
                        <input type="file" name="work_study_proof" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>iii. Do you receive any financial support from external sponsors such as HELB, NGOs, CDF?</label>
                    <select name="external_sponsor" required>
                        <option value="">Select</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                    <div id="sponsor_details" style="display:none;">
                        <label>If Yes, specify the source and amount</label>
                        <textarea name="sponsor_details" rows="2"></textarea>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>iv. Have you completed paying tuition fee for this academic year?</label>
                        <select name="fee_payment_status" required>
                            <option value="">Select</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="form-group" id="fee_balance_container" style="display:none;">
                        <label>If No, state the balance</label>
                        <input type="number" name="fee_balance" min="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Attach Current fee Statement</label>
                    <input type="file" name="fee_statement" accept=".pdf,.jpg,.jpeg,.png" required>
                    <div id="fee_statement_preview" class="file-previews"></div>
                </div>
                
                <div class="form-group">
                    <label>v. Have you ever deferred your University studies?</label>
                    <select name="deferred_studies" required>
                        <option value="">Select</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                    <div id="deferral_reasons" style="display:none;">
                        <label>If yes, give reasons:</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="medical" name="deferral_reason[]" value="Medical">
                            <label for="medical">Medical</label>
                            
                            <input type="checkbox" id="social" name="deferral_reason[]" value="Social">
                            <label for="social">Social</label>
                            
                            <input type="checkbox" id="financial" name="deferral_reason[]" value="Financial">
                            <label for="financial">Financial</label>
                            
                            <input type="checkbox" id="academic" name="deferral_reason[]" value="Academic">
                            <label for="academic">Academic</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>vi. Additional Information</label>
                    <textarea name="additional_info" rows="4"></textarea>
                    <div class="file-upload-container">
                        <label>Attach any other supporting documents</label>
                        <input type="file" name="other_documents[]" multiple accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div id="other_documents_preview" class="file-previews"></div>
                </div>
            </div>

            <div class="form-footer">
                <div class="declaration">
                    <h3>DECLARATION</h3>
                    <p>By writing your name, you confirm that the provided information are true. I understand that giving false information may lead to disqualification from the Work Study Program.</p>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Applicant's Name</label>
                            <input type="text" name="applicant_signature" required>
                        </div>
                        <div class="form-group">
                            <label>Enter the current Date</label>
                            <input type="date" name="signature_date" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">Reset Form</button>
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </div>
            </div>
        </form>
    </div>

    <script src="http://localhost/work_study/assets/js/application.js"></script>
</body>
</html>