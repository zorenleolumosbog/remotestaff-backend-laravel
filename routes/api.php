<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Api\Users\RegisterController;
use App\Http\Controllers\Api\Users\LoginController;
use App\Http\Controllers\Api\Users\ChangePasswordController;
use App\Http\Controllers\Api\Users\PasswordResetController;
use App\Http\Controllers\Api\Users\ProfileController;
use App\Http\Controllers\Api\Users\fileAttachmentController;
use App\Http\Controllers\Api\Users\CharRefController;
use App\Http\Controllers\Api\Users\ContactInfoController;
use App\Http\Controllers\Api\Users\EducationalAttainmentController;
use App\Http\Controllers\Api\Users\EmploymentHistoryController;
use App\Http\Controllers\Api\Users\jobApplicationController;
use App\Http\Controllers\Api\Users\WfhRefController;
use App\Http\Controllers\Api\Users\WorkRefController;
use App\Http\Controllers\Api\Users\SkillsController;
use App\Http\Controllers\Api\Users\ImporterController;
use App\Http\Controllers\Api\Users\OnboardSpeedtestController;
use App\Http\Controllers\Api\Users\uploadFileController;

use App\Http\Controllers\Api\Admin\BarangayController;
use App\Http\Controllers\Api\Admin\CivilStatusController;
use App\Http\Controllers\Api\Admin\CountryController;
use App\Http\Controllers\Api\Admin\CurrencyController;
use App\Http\Controllers\Api\Admin\JobSourceController;
use App\Http\Controllers\Api\Admin\OrganizationalUnitController;
use App\Http\Controllers\Api\Admin\RegionController;
use App\Http\Controllers\Api\Admin\RegistrantTypeController;
use App\Http\Controllers\Api\Admin\ReligionController;
use App\Http\Controllers\Api\Admin\SocialMediaController;
use App\Http\Controllers\Api\Admin\StateController;
use App\Http\Controllers\Api\Admin\TaxRateController;
use App\Http\Controllers\Api\Admin\TaxTypeController;
use App\Http\Controllers\Api\Admin\TowncityController;
use App\Http\Controllers\Api\Admin\UserAuthenticationController;
use App\Http\Controllers\Api\Admin\UserManagementController;
use App\Http\Controllers\Api\Admin\UserRoleController;
use App\Http\Controllers\Api\Admin\OnboardRegistrationExpiryController;
use App\Http\Controllers\Api\Admin\RegistrantController;
use App\Http\Controllers\Api\Admin\RegistrantReportController;
use App\Http\Controllers\Api\Admin\IndustryController;
use App\Http\Controllers\Api\Admin\SkillController;
use App\Http\Controllers\Api\Admin\SkillLevelController;
use App\Http\Controllers\Api\Admin\ManageFileController;
use App\Http\Controllers\Api\Admin\AccountTypeController;
use App\Http\Controllers\Api\Admin\AccountClassificationController;
use App\Http\Controllers\Api\Admin\DegreeLevelController;
use App\Http\Controllers\Api\Admin\InternetTypeController;
use App\Http\Controllers\Api\Admin\IspController;
use App\Http\Controllers\Api\Admin\HardwareTypeController;
use App\Http\Controllers\Api\Admin\FieldOfStudyController;
use App\Http\Controllers\Api\Admin\InvoiceItemTypeController;
use App\Http\Controllers\Api\Admin\LegacySubcontractorController;
use App\Http\Controllers\Api\Admin\DepartmentController;
use App\Http\Controllers\Api\Admin\DepartmentSectionController;
use App\Http\Controllers\Api\Admin\DepartmentSectionPersonnelController;
use App\Http\Controllers\Api\Admin\ContractStatusController;
use App\Http\Controllers\Api\Admin\ContractServiceTypeController;
use App\Http\Controllers\Api\Admin\ContractTypeController;
use App\Http\Controllers\Api\Admin\CancellationReasonTypeController;

use App\Http\Controllers\Api\Accounting\ClientController;
use App\Http\Controllers\Api\Accounting\ClientSubcontractorsController;
use App\Http\Controllers\Api\Accounting\ClientBasicRateController;
use App\Http\Controllers\Api\Accounting\ClientSubConRateController;
use App\Http\Controllers\Api\Accounting\ForexRateController;
use App\Http\Controllers\Api\Accounting\InvoiceController;
use App\Http\Controllers\Api\Accounting\ManualInvoiceController;
use App\Http\Controllers\Api\Accounting\ClientInvoiceItemController;
use App\Http\Controllers\Api\Accounting\PrepopulatedClientInvoiceItemController;
use App\Http\Controllers\Api\Accounting\VoidInvoiceController;
use App\Http\Controllers\Api\Accounting\ForexRateTypeController;
use App\Http\Controllers\Api\Timesheet\TimesheetController;

use App\Http\Controllers\Api\Client\ScreenCaptureController;
use App\Http\Controllers\Api\Client\ActivityNotesController;
use App\Http\Controllers\Api\Client\AttendanceController;
use App\Http\Controllers\Api\Client\SubcontractorController as ClientSubcontractorController;
use App\Http\Controllers\Api\Client\TimesheetController as ClientTimesheetController;

use App\Http\Controllers\Api\Client\CrfController;

use App\Http\Controllers\Api\SecurityAccessMatrix\PillarController;
use App\Http\Controllers\Api\SecurityAccessMatrix\SubPillarController;
use App\Http\Controllers\Api\SecurityAccessMatrix\MenuHeadingController;

/* Route for Email Verification */

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/home');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/profile', function () {
    // Only verified users may access this route...
})->middleware(['auth', 'verified']);


//* Job Seeker Onboarding */

Route::post('/register/onboarding', [RegisterController::class, 'onBoarding'])->name('onboarding.register');
Route::get('/validate-preregister/onboarding/{preregid}', [RegisterController::class, 'validatePreRegistration'])->name('onboarding.validate_preregister');
Route::post('/preregister/onboarding', [RegisterController::class, 'preRegistration'])->name('onboarding.preregister');
Route::post('/validated-reg/onboarding', [ProfileController::class, 'onBoardingValidateProfile'])->name('profile.validate');
Route::get('/account/verify/{token}/{registrant_type}', [RegisterController::class, 'verifyAccount'])->name('user.verify');
Route::post('/login/', [LoginController::class, 'onBoardingLogin'])->name('onboarding.login');
Route::post('/reset-password/', [PasswordResetController::class, 'onBoardingReset'])->name('onboarding.reset');
Route::post('/change-password/', [ChangePasswordController::class, 'passwordResetOnboarding'])->name('onboarding.change');


Route::group(['middleware' => 'auth:jobseeker'], function () {
    /* Profile */
    Route::post('/update-profile/onboarding', [ProfileController::class, 'onBoardingStoreProfile'])->name('profile.update');
    Route::post('/get-profile/onboarding', [ProfileController::class, 'onBoardingGetProfile'])->name('profile.get');
    Route::post('/delete-profile/onboarding', [ProfileController::class, 'onBoardingDeleteProfile'])->name('profile.delete');
    Route::post('/update-password/onboarding', [ProfileController::class, 'updatePass'])->name('password.check');

    /* File Atttachment */
    Route::post('/upload-attachment/onboarding', [fileAttachmentController::class, 'store'])->name('attachment.upload');
    Route::get('/get-attachments/onboarding', [fileAttachmentController::class, 'get'])->name('attachments.get');
    Route::post('/delete-attachments/onboarding', [fileAttachmentController::class, 'delete'])->name('attachments.delete');
    Route::get('/getbyfiletype/onboarding', [fileAttachmentController::class, 'getByFileType'])->name('byfiletype.get');
    Route::get('/getbyjbfiletype/onboarding', [fileAttachmentController::class, 'getByJbFileType'])->name('byjbfiletype.get');
    Route::get('/getfile/onboarding', [fileAttachmentController::class, 'getByFile'])->name('file.get');
    Route::get('/getvideo/onboarding', [fileAttachmentController::class, 'getVideo'])->name('video.get');

    /* Employment History */
    Route::post('/update-history/onboarding', [EmploymentHistoryController::class, 'store'])->name('emphist.update');
    Route::post('/get-history/onboarding', [EmploymentHistoryController::class, 'get'])->name('emphist.get');
    Route::post('/delete-history/onboarding', [EmploymentHistoryController::class, 'delete'])->name('emphist.delete');

    /* Character Reference */
    Route::post('/update-character/onboarding', [CharRefController::class, 'store'])->name('char_ref.update');
    Route::get('/get-character/onboarding', [CharRefController::class, 'get'])->name('char_ref.get');
    Route::post('/delete-character/onboarding', [CharRefController::class, 'delete'])->name('character_ref.delete');

    /* Work From Home Reference */
    Route::post('/update-wfhres/onboarding', [WfhRefController::class, 'store'])->name('wfhref.update');
    Route::post('/get-wfhres/onboarding', [WfhRefController::class, 'get'])->name('wfhref.get');
    Route::get('/get-nettype/onboarding', [WfhRefController::class, 'getNetType'])->name('nettype.get');
    Route::get('/get-isp/onboarding', [WfhRefController::class, 'getISP'])->name('isp.get');
    Route::get('/get-hardware/onboarding', [WfhRefController::class, 'getHardware'])->name('hardware.get');
    Route::post('/delete-wfhres/onboarding', [WfhRefController::class, 'delete'])->name('wfh_ref.delete');

    /* Work Reference */
    Route::post('/update-workref/onboarding', [WorkRefController::class, 'store'])->name('wrkref.update');
    Route::post('/get-workref/onboarding', [WorkRefController::class, 'get'])->name('wrkref.get');
    Route::post('/delete-workref/onboarding', [WorkRefController::class, 'delete'])->name('wrkref.delete');
    Route::get('/getworkrefnotice/onboarding', [WorkRefController::class, 'getNotice'])->name('notice.get');
    Route::get('/getworkreftimezone/onboarding', [WorkRefController::class, 'getTimezone'])->name('timezone.get');

    /* Educational Attainment */
    Route::post('/update-educational/onboarding', [EducationalAttainmentController::class, 'store'])->name('educational_attainment.update');
    Route::post('/get-educational/onboarding', [EducationalAttainmentController::class, 'get'])->name('educational_attainment.get');
    Route::get('/get-degreelevel/onboarding', [EducationalAttainmentController::class, 'getLevel'])->name('degreelevel.get');
    Route::get('/get-fieldstudy/onboarding', [EducationalAttainmentController::class, 'getFieldStudy'])->name('fieldstudy.get');
    Route::post('/delete-educational/onboarding', [EducationalAttainmentController::class, 'delete'])->name('educational_attainment.delete');

    /* Job Application */
    Route::post('/update-job-application-status/onboarding', [jobApplicationController::class, 'store'])->name('job_application_status.update');
    Route::post('/get-job-application-status/onboarding', [jobApplicationController::class, 'get'])->name('job_application_status.get');
    Route::post('/delete-job-application-status/onboarding', [jobApplicationController::class, 'delete'])->name('job_application_status.delete');

    /* Contact Info */
    Route::post('/update-contact/onboarding', [ContactInfoController::class, 'store'])->name('add_altemail.add');
    Route::post('/get-contact/onboarding', [ContactInfoController::class, 'get'])->name('get_contacts.get');
    Route::post('/delete-contact/onboarding', [ContactInfoController::class, 'delete'])->name('delete_contacts.delete');
    Route::post('/primary-contact/onboarding', [ContactInfoController::class, 'set'])->name('primary.set');

    /* Skills */
    Route::post('/update-skills/onboarding', [SkillsController::class, 'store'])->name('skills.add');
    Route::get('/get-skills/onboarding', [SkillsController::class, 'get'])->name('skills.get');
    Route::post('/delete-skills/onboarding', [SkillsController::class, 'delete'])->name('skills.delete');


    Route::get('/get-skillstype/onboarding', [SkillsController::class, 'getSkills'])->name('getskills.get');
    Route::get('/get-skilllevel/onboarding', [SkillsController::class, 'getSkillLevel'])->name('getskilllevel.get');


     /* Check Email */
    Route::post('/check-email/onboarding', [ProfileController::class, 'onBoardingEmailCheck'])->name('check_email.get');

    /* Get Completeness */
    Route::post('/completeness/onboarding', [ProfileController::class, 'totalCompleteness'])->name('completeness.get');

    /* Speedtest */
    Route::get('/speedtest-history/onboarding/{reg_id}', [OnboardSpeedtestController::class, 'index'])->name('speedtest.index');
    Route::get('/speedtest/onboarding/{reg_id}', [OnboardSpeedtestController::class, 'show'])->name('speedtest.show');
    Route::post('/store-speedtest/onboarding', [OnboardSpeedtestController::class, 'store'])->name('speedtest.store');
    Route::post('/upload-file-speedtest/onboarding', [OnboardSpeedtestController::class, 'upload'])->name('speedtest.upload');

     //  Upload file
    Route::post('/upload-file/onboarding', [uploadFileController::class, 'store'])->name('uploadfile.upload');
    Route::post('/get-upload-file/onboarding', [uploadFileController::class, 'get'])->name('uploadfile.get');
    Route::post('/delete-upload-file/onboarding', [uploadFileController::class, 'delete'])->name('uploadfile.delete');



});

//Logout
Route::post('/logout/', [LoginController::class, 'logout'])->name('logout');

//importer
Route::get('/importer/', [ImporterController::class, 'get'])->name('importer');


//Unauthorized

Route::get('/unauthorized', function () {
    return response()->json(['error' => 'Unauthenticated.'], 401);
})->name('unauthorized');

/* End Route for Email Verification */


//  Download file
Route::get('/download-file/{id}', [fileAttachmentController::class, 'download'])->name('download.get');

/* Cron Unvirified */
Route::get('cron-verify', [RegisterController::class, 'cronUnverified'])->name('cron.verify');

Route::get('/country', [CountryController::class, 'index'])->name('_country.index');
Route::get('/country/{id}', [CountryController::class, 'show'])->name('_country.show');
Route::get('/state', [StateController::class, 'index'])->name('_state.index');
Route::get('/state/{id}', [StateController::class, 'show'])->name('_state.show');
Route::get('/region', [RegionController::class, 'index'])->name('_region.index');
Route::get('/region/{id}', [RegionController::class, 'show'])->name('_region.show');
Route::get('/towncity', [TowncityController::class, 'index'])->name('_towncity.index');
Route::get('/towncity/{id}', [TowncityController::class, 'show'])->name('_towncity.show');
Route::get('/barangay', [BarangayController::class, 'index'])->name('_barangay.index');
Route::get('/barangay/{id}', [BarangayController::class, 'show'])->name('_barangay.show');
Route::get('/organizational-unit', [OrganizationalUnitController::class, 'index'])->name('_organizational_unit.index');
Route::get('/organizational-unit/{id}', [OrganizationalUnitController::class, 'show'])->name('_organizational_unit.show');
Route::get('/currency', [CurrencyController::class, 'index'])->name('_currency.index');
Route::get('/currency/{id}', [CurrencyController::class, 'show'])->name('_currency.show');
Route::get('/tax-type', [TaxTypeController::class, 'index'])->name('_tax_type.index');
Route::get('/tax-type/{id}', [TaxTypeController::class, 'show'])->name('_tax_type.show');
Route::get('/tax-rate', [TaxRateController::class, 'index'])->name('_tax_rate.index');
Route::get('/tax-rate/{id}', [TaxRateController::class, 'show'])->name('_tax_rate.show');
Route::get('/registrant-type', [RegistrantTypeController::class, 'index'])->name('_registrant_type.index');
Route::get('/registrant-type/{id}', [RegistrantTypeController::class, 'show'])->name('_registrant_type.show');
Route::get('/job-source', [JobSourceController::class, 'index'])->name('_job_source.index');
Route::get('/job-source/{id}', [JobSourceController::class, 'show'])->name('_job_source.show');
Route::get('/religion', [ReligionController::class, 'index'])->name('_religion.index');
Route::get('/religion/{id}', [ReligionController::class, 'show'])->name('_religion.show');
Route::get('/civil-status', [CivilStatusController::class, 'index'])->name('_civil_status.index');
Route::get('/civil-status/{id}', [CivilStatusController::class, 'show'])->name('_civil_status.show');
Route::get('/social-media', [SocialMediaController::class, 'index'])->name('_social_media.index');
Route::get('/social-media/{id}', [SocialMediaController::class, 'show'])->name('_social_media.show');
Route::get('/industry', [IndustryController::class, 'index'])->name('_industry.index');
Route::get('/industry/{id}', [IndustryController::class, 'show'])->name('_industry.show');
Route::get('/skill', [SkillController::class, 'index'])->name('_skill.index');
Route::get('/skill/{id}', [SkillController::class, 'show'])->name('_skill.show');
Route::get('/skill-level', [SkillLevelController::class, 'index'])->name('_skill_level.index');
Route::get('/skill-level/{id}', [SkillLevelController::class, 'show'])->name('_skill_level.show');
Route::get('/manage-file', [ManageFileController::class, 'index'])->name('_hris_attachment.index');
Route::get('/manage-file/{id}', [ManageFileController::class, 'show'])->name('_hris_attachment.show');
Route::get('/account-type', [AccountTypeController::class, 'index'])->name('_account_type.index');
Route::get('/account-type/{id}', [AccountTypeController::class, 'show'])->name('_account_type.show');
Route::get('/account-classification', [AccountClassificationController::class, 'index'])->name('_account_classification.index');
Route::get('/account-classification/{id}', [AccountClassificationController::class, 'show'])->name('_account_classification.show');
Route::get('/internet-type', [InternetTypeController::class, 'index'])->name('_internet_type.index');
Route::get('/internet-type/{id}', [InternetTypeController::class, 'show'])->name('_internet_type.show');
Route::get('/isp', [IspController::class, 'index'])->name('_Isp.index');
Route::get('/isp/{id}', [IspController::class, 'show'])->name('_Isp.show');
Route::get('/degree-level', [DegreeLevelController::class, 'index'])->name('_degree_level.index');
Route::get('/degree-level/{id}', [DegreeLevelController::class, 'show'])->name('_degree_level.show');
Route::get('/field-of-study', [FieldOfStudyController::class, 'index'])->name('_field_of_study.index');
Route::get('/field-of-study/{id}', [FieldOfStudyController::class, 'show'])->name('_field_of_study.show');
Route::get('/hardware-type', [HardwareTypeController::class, 'index'])->name('_hardware_type.index');
Route::get('/hardware-type/{id}', [HardwareTypeController::class, 'show'])->name('_hardware_type.show');
Route::get('/invoice-item-type', [InvoiceItemTypeController::class, 'index'])->name('_invoice_item_type.index');
Route::get('/invoice-item-type/{id}', [InvoiceItemTypeController::class, 'show'])->name('_invoice_item_type.show');

Route::get('/check-registrant-type-by-email/{email}', [RegistrantController::class, 'checkRegistrantType'])->name('_check_registrant_type.show');

// //* In-House Admin */
// Route::post('admin/register', [UserAuthenticationController::class, 'register'])->name('admin_user.register');
// Route::post('admin/login', [UserAuthenticationController::class, 'login'])->name('admin_user.login');
// Route::get('admin/account/verify/{token}', [UserAuthenticationController::class, 'verify'])->name('admin_user.verify');
// Route::post('admin/reset-password', [UserAuthenticationController::class, 'resetPass'])->name('admin_user.reset');
// Route::post('admin/change-password', [UserAuthenticationController::class, 'changePass'])->name('admin_user.change');

Route::group(['prefix' => 'admin', 'middleware' => ['auth:admin', 'corporate_role']], function () {
    // //User Type endpoints
    // Route::get('/user-role', [UserRoleController::class, 'index'])->name('user_role.index');
    // Route::get('/user-role/{id}', [UserRoleController::class, 'show'])->name('user_role.show');
    // Route::post('/store-user-role', [UserRoleController::class, 'store'])->name('user_role.store');
    // Route::put('/update-user-role/{id}', [UserRoleController::class, 'update'])->name('user_role.update');
    // Route::delete('/delete-user-role/{id}', [UserRoleController::class, 'delete'])->name('user_role.delete');

    // //User Management endpoints
    // Route::get('/user-management', [UserManagementController::class, 'index'])->name('user_management.index');
    // Route::get('/user-management/{id}', [UserManagementController::class, 'show'])->name('user_management.show');
    // Route::post('/store-user-management', [UserManagementController::class, 'store'])->name('user_management.store');
    // Route::put('/update-user-management/{id}', [UserManagementController::class, 'update'])->name('user_management.update');
    // Route::delete('/delete-user-management/{id}', [UserManagementController::class, 'delete'])->name('user_management.delete');

    //Subcontractor endpoints
    Route::get('/legacy-subcontractor', [LegacySubcontractorController::class, 'index'])->name('legacy_subcontractor.index');

    //Logout Auth User
    Route::post('/logout/', [UserAuthenticationController::class, 'logout'])->name('admin_user.logout');

    //Department endpoints
    Route::get('/department', [DepartmentController::class, 'index'])->name('department.index');
    Route::get('/department/{id}', [DepartmentController::class, 'show'])->name('department.show');
    Route::post('/store-department', [DepartmentController::class, 'store'])->name('department.store');
    Route::put('/update-department/{id}', [DepartmentController::class, 'update'])->name('department.update');
    Route::delete('/delete-department/{id}', [DepartmentController::class, 'delete'])->name('department.delete');

    //Department Section endpoints
    Route::get('/department-section', [DepartmentSectionController::class, 'index'])->name('department_section.index');
    Route::get('/department-section/{id}', [DepartmentSectionController::class, 'show'])->name('department_section.show');
    Route::post('/store-department-section', [DepartmentSectionController::class, 'store'])->name('department_section.store');
    Route::put('/update-department-section/{id}', [DepartmentSectionController::class, 'update'])->name('department_section.update');
    Route::delete('/delete-department-section/{id}', [DepartmentSectionController::class, 'delete'])->name('department_section.delete');

    //Department Section Personnel endpoints
    Route::get('/department-section-personnel', [DepartmentSectionPersonnelController::class, 'index'])->name('department_section_personnel.index');
    Route::get('/department-section-personnel/{id}', [DepartmentSectionPersonnelController::class, 'show'])->name('department_section_personnel.show');
    Route::post('/store-department-section-personnel', [DepartmentSectionPersonnelController::class, 'store'])->name('department_section_personnel.store');
    Route::put('/update-department-section-personnel/{id}', [DepartmentSectionPersonnelController::class, 'update'])->name('department_section_personnel.update');
    Route::delete('/delete-department-section-personnel/{id}', [DepartmentSectionPersonnelController::class, 'delete'])->name('department_section_personnel.delete');

    //Country endpoints
    Route::get('/geography', [CountryController::class, 'geography'])->name('country.geography');
    Route::get('/country', [CountryController::class, 'index'])->name('country.index');
    Route::get('/country/{id}', [CountryController::class, 'show'])->name('country.show');
    Route::post('/store-country', [CountryController::class, 'store'])->name('country.store');
    Route::put('/update-country/{id}', [CountryController::class, 'update'])->name('country.update');
    Route::delete('/delete-country/{id}', [CountryController::class, 'delete'])->name('country.delete');

    //State endpoints
    Route::get('/state', [StateController::class, 'index'])->name('state.index');
    Route::get('/state/{id}', [StateController::class, 'show'])->name('state.show');
    Route::post('/store-state', [StateController::class, 'store'])->name('state.store');
    Route::put('/update-state/{id}', [StateController::class, 'update'])->name('state.update');
    Route::delete('/delete-state/{id}', [StateController::class, 'delete'])->name('state.delete');

    //Region endpoints
    Route::get('/region', [RegionController::class, 'index'])->name('region.index');
    Route::get('/region/{id}', [RegionController::class, 'show'])->name('region.show');
    Route::post('/store-region', [RegionController::class, 'store'])->name('region.store');
    Route::put('/update-region/{id}', [RegionController::class, 'update'])->name('region.update');
    Route::delete('/delete-region/{id}', [RegionController::class, 'delete'])->name('region.delete');

    //Towncity endpoints
    Route::get('/towncity', [TowncityController::class, 'index'])->name('towncity.index');
    Route::get('/towncity/{id}', [TowncityController::class, 'show'])->name('towncity.show');
    Route::post('/store-towncity', [TowncityController::class, 'store'])->name('towncity.store');
    Route::put('/update-towncity/{id}', [TowncityController::class, 'update'])->name('towncity.update');
    Route::delete('/delete-towncity/{id}', [TowncityController::class, 'delete'])->name('towncity.delete');

    //Barangay endpoints
    Route::get('/barangay', [BarangayController::class, 'index'])->name('barangay.index');
    Route::get('/barangay/{id}', [BarangayController::class, 'show'])->name('barangay.show');
    Route::post('/store-barangay', [BarangayController::class, 'store'])->name('barangay.store');
    Route::put('/update-barangay/{id}', [BarangayController::class, 'update'])->name('barangay.update');
    Route::delete('/delete-barangay/{id}', [BarangayController::class, 'delete'])->name('barangay.delete');

    //Organizational Unit endpoints
    Route::get('/organizational-unit', [OrganizationalUnitController::class, 'index'])->name('organizational_unit.index');
    Route::get('/organizational-unit/{id}', [OrganizationalUnitController::class, 'show'])->name('organizational_unit.show');
    Route::post('/store-organizational-unit', [OrganizationalUnitController::class, 'store'])->name('organizational_unit.store');
    Route::put('/update-organizational-unit/{id}', [OrganizationalUnitController::class, 'update'])->name('organizational_unit.update');
    Route::delete('/delete-organizational-unit/{id}', [OrganizationalUnitController::class, 'delete'])->name('organizational_unit.delete');

    //Currency endpoints
    Route::get('/currency', [CurrencyController::class, 'index'])->name('currency.index');
    Route::get('/currency/{id}', [CurrencyController::class, 'show'])->name('currency.show');
    Route::post('/store-currency', [CurrencyController::class, 'store'])->name('currency.store');
    Route::put('/update-currency/{id}', [CurrencyController::class, 'update'])->name('currency.update');
    Route::delete('/delete-currency/{id}', [CurrencyController::class, 'delete'])->name('currency.delete');

    //Tax Type endpoints
    Route::get('/tax-type', [TaxTypeController::class, 'index'])->name('tax_type.index');
    Route::get('/tax-type/{id}', [TaxTypeController::class, 'show'])->name('tax_type.show');
    Route::post('/store-tax-type', [TaxTypeController::class, 'store'])->name('tax_type.store');
    Route::put('/update-tax-type/{id}', [TaxTypeController::class, 'update'])->name('tax_type.update');
    Route::delete('/delete-tax-type/{id}', [TaxTypeController::class, 'delete'])->name('tax_type.delete');

    //Tax Rate endpoints
    Route::get('/tax-rate', [TaxRateController::class, 'index'])->name('tax_rate.index');
    Route::get('/tax-rate/{id}', [TaxRateController::class, 'show'])->name('tax_rate.show');
    Route::post('/store-tax-rate', [TaxRateController::class, 'store'])->name('tax_rate.store');
    Route::put('/update-tax-rate/{id}', [TaxRateController::class, 'update'])->name('tax_rate.update');
    Route::delete('/delete-tax-rate/{id}', [TaxRateController::class, 'delete'])->name('tax_rate.delete');

    //Registrant Type endpoints
    Route::get('/registrant-type', [RegistrantTypeController::class, 'index'])->name('registrant_type.index');
    Route::get('/registrant-type/{id}', [RegistrantTypeController::class, 'show'])->name('registrant_type.show');
    Route::post('/store-registrant-type', [RegistrantTypeController::class, 'store'])->name('registrant_type.store');
    Route::put('/update-registrant-type/{id}', [RegistrantTypeController::class, 'update'])->name('registrant_type.update');
    Route::delete('/delete-registrant-type/{id}', [RegistrantTypeController::class, 'delete'])->name('registrant_type.delete');

    //Job Source endpoints
    Route::get('/job-source', [JobSourceController::class, 'index'])->name('job_source.index');
    Route::get('/job-source/{id}', [JobSourceController::class, 'show'])->name('job_source.show');
    Route::post('/store-job-source', [JobSourceController::class, 'store'])->name('job_source.store');
    Route::put('/update-job-source/{id}', [JobSourceController::class, 'update'])->name('job_source.update');
    Route::delete('/delete-job-source/{id}', [JobSourceController::class, 'delete'])->name('job_source.delete');

    //Religion endpoints
    Route::get('/religion', [ReligionController::class, 'index'])->name('religion.index');
    Route::get('/religion/{id}', [ReligionController::class, 'show'])->name('religion.show');
    Route::post('/store-religion', [ReligionController::class, 'store'])->name('religion.store');
    Route::put('/update-religion/{id}', [ReligionController::class, 'update'])->name('religion.update');
    Route::delete('/delete-religion/{id}', [ReligionController::class, 'delete'])->name('religion.delete');

    //Civil Status endpoints
    Route::get('/civil-status', [CivilStatusController::class, 'index'])->name('civil_status.index');
    Route::get('/civil-status/{id}', [CivilStatusController::class, 'show'])->name('civil_status.show');
    Route::post('/store-civil-status', [CivilStatusController::class, 'store'])->name('civil_status.store');
    Route::put('/update-civil-status/{id}', [CivilStatusController::class, 'update'])->name('civil_status.update');
    Route::delete('/delete-civil-status/{id}', [CivilStatusController::class, 'delete'])->name('civil_status.delete');

    //Social Media endpoints
    Route::get('/social-media', [SocialMediaController::class, 'index'])->name('social_media.index');
    Route::get('/social-media/{id}', [SocialMediaController::class, 'show'])->name('social_media.show');
    Route::post('/store-social-media', [SocialMediaController::class, 'store'])->name('social_media.store');
    Route::put('/update-social-media/{id}', [SocialMediaController::class, 'update'])->name('social_media.update');
    Route::delete('/delete-social-media/{id}', [SocialMediaController::class, 'delete'])->name('social_media.delete');

    //Onboard Registration Expiry endpoints
    Route::get('/onboard-registration-expiry', [OnboardRegistrationExpiryController::class, 'index'])->name('onboard_registration_expiry.index');
    Route::get('/onboard-registration-expiry/{id}', [OnboardRegistrationExpiryController::class, 'show'])->name('onboard_registration_expiry.show');
    Route::post('/store-onboard-registration-expiry', [OnboardRegistrationExpiryController::class, 'store'])->name('onboard_registration_expiry.store');
    Route::put('/update-onboard-registration-expiry/{id}', [OnboardRegistrationExpiryController::class, 'update'])->name('onboard_registration_expiry.update');
    Route::delete('/delete-onboard-registration-expiry/{id}', [OnboardRegistrationExpiryController::class, 'delete'])->name('onboard_registration_expiry.delete');

    //Onboard Registration Reports endpoints
    Route::get('/registrant-report/verified/{registrant_type_id}', [RegistrantReportController::class, 'verified'])->name('registrant_report.verified');
    Route::get('/registrant-report/unverified/{registrant_type_id}', [RegistrantReportController::class, 'unverified'])->name('registrant_report.unverified');
    Route::get('/registrant-report/expired/{registrant_type_id}', [RegistrantReportController::class, 'expired'])->name('registrant_report.expired');

    //Industry Status endpoints
    Route::get('/industry', [IndustryController::class, 'index'])->name('industry.index');
    Route::get('/industry/{id}', [IndustryController::class, 'show'])->name('industry.show');
    Route::post('/store-industry', [IndustryController::class, 'store'])->name('industry.store');
    Route::put('/update-industry/{id}', [IndustryController::class, 'update'])->name('industry.update');
    Route::delete('/delete-industry/{id}', [IndustryController::class, 'delete'])->name('industry.delete');

    //Skill endpoints
    Route::get('/skill', [SkillController::class, 'index'])->name('skill.index');
    Route::get('/skill/{id}', [SkillController::class, 'show'])->name('skill.show');
    Route::post('/store-skill', [SkillController::class, 'store'])->name('skill.store');
    Route::put('/update-skill/{id}', [SkillController::class, 'update'])->name('skill.update');
    Route::delete('/delete-skill/{id}', [SkillController::class, 'delete'])->name('skill.delete');

    //Skill Level endpoints
    Route::get('/skill-level', [SkillLevelController::class, 'index'])->name('skill_level.index');
    Route::get('/skill-level/{id}', [SkillLevelController::class, 'show'])->name('skill_level.show');
    Route::post('/store-skill-level', [SkillLevelController::class, 'store'])->name('skill_level.store');
    Route::put('/update-skill-level/{id}', [SkillLevelController::class, 'update'])->name('skill_level.update');
    Route::delete('/delete-skill-level/{id}', [SkillLevelController::class, 'delete'])->name('skill_level.delete');

    // hris file attachment
    Route::get('/manage-file', [ManageFileController::class, 'index'])->name('hris_attachment.index');
    Route::get('/manage-file/{id}', [ManageFileController::class, 'show'])->name('hris_attachment.show');
    Route::post('/store-manage-file', [ManageFileController::class, 'store'])->name('hris_attachment.store');
    Route::put('/update-manage-file/{id}', [ManageFileController::class, 'update'])->name('hris_attachment.update');
    Route::delete('/delete-manage-file/{id}', [ManageFileController::class, 'delete'])->name('hris_attachment.delete');

    //AccountType Status endpoints
    Route::get('/account-type', [AccountTypeController::class, 'index'])->name('account_type.index');
    Route::get('/account-type/{id}', [AccountTypeController::class, 'show'])->name('account_type.show');
    Route::post('/store-account-type', [AccountTypeController::class, 'store'])->name('account_type.store');
    Route::put('/update-account-type/{id}', [AccountTypeController::class, 'update'])->name('account_type.update');
    Route::delete('/delete-account-type/{id}', [AccountTypeController::class, 'delete'])->name('account_type.delete');

    //AccountClassification Status endpoints
    Route::get('/account-classification', [AccountClassificationController::class, 'index'])->name('account_classification.index');
    Route::get('/account-classification/{id}', [AccountClassificationController::class, 'show'])->name('account_classification.show');
    Route::post('/store-account-classification', [AccountClassificationController::class, 'store'])->name('account_classification.store');
    Route::put('/update-account-classification/{id}', [AccountClassificationController::class, 'update'])->name('account_classification.update');
    Route::delete('/delete-account-classification/{id}', [AccountClassificationController::class, 'delete'])->name('account_classification.delete');

    //Internet Type endpoints
    Route::get('/internet-type', [InternetTypeController::class, 'index'])->name('internet_type.index');
    Route::get('/internet-type/{id}', [InternetTypeController::class, 'show'])->name('internet_type.show');
    Route::post('/store-internet-type', [InternetTypeController::class, 'store'])->name('internet_type.store');
    Route::put('/update-internet-type/{id}', [InternetTypeController::class, 'update'])->name('internet_type.update');
    Route::delete('/delete-internet-type/{id}', [InternetTypeController::class, 'delete'])->name('internet_type.delete');

    //ISP endpoints
    Route::get('/isp', [IspController::class, 'index'])->name('Isp.index');
    Route::get('/isp/{id}', [IspController::class, 'show'])->name('Isp.show');
    Route::post('/store-isp', [IspController::class, 'store'])->name('Isp.store');
    Route::put('/update-isp/{id}', [IspController::class, 'update'])->name('Isp.update');
    Route::delete('/delete-isp/{id}', [IspController::class, 'delete'])->name('Isp.delete');

    //Degree Level endpoints
    Route::get('/degree-level', [DegreeLevelController::class, 'index'])->name('degree_level.index');
    Route::get('/degree-level/{id}', [DegreeLevelController::class, 'show'])->name('degree_level.show');
    Route::post('/store-degree-level', [DegreeLevelController::class, 'store'])->name('degree_level.store');
    Route::put('/update-degree-level/{id}', [DegreeLevelController::class, 'update'])->name('degree_level.update');
    Route::delete('/delete-degree-level/{id}', [DegreeLevelController::class, 'delete'])->name('degree_level.delete');

    //Field of Study endpoints
    Route::get('/field-of-study', [FieldOfStudyController::class, 'index'])->name('field_of_study.index');
    Route::get('/field-of-study/{id}', [FieldOfStudyController::class, 'show'])->name('field_of_study.show');
    Route::post('/store-field-of-study', [FieldOfStudyController::class, 'store'])->name('field_of_study.store');
    Route::put('/update-field-of-study/{id}', [FieldOfStudyController::class, 'update'])->name('field_of_study.update');
    Route::delete('/delete-field-of-study/{id}', [FieldOfStudyController::class, 'delete'])->name('field_of_study.delete');

    //Hardware Type endpoints
    Route::get('/hardware-type', [HardwareTypeController::class, 'index'])->name('hardware_type.index');
    Route::get('/hardware-type/{id}', [HardwareTypeController::class, 'show'])->name('hardware_type.show');
    Route::post('/store-hardware-type', [HardwareTypeController::class, 'store'])->name('hardware_type.store');
    Route::put('/update-hardware-type/{id}', [HardwareTypeController::class, 'update'])->name('hardware_type.update');
    Route::delete('/delete-hardware-type/{id}', [HardwareTypeController::class, 'delete'])->name('hardware_type.delete');

    //Invoice Item Type endpoints
    Route::get('/invoice-item-type', [InvoiceItemTypeController::class, 'index'])->name('invoice_item_type.index');
    Route::get('/invoice-item-type/{id}', [InvoiceItemTypeController::class, 'show'])->name('invoice_item_type.show');
    Route::post('/store-invoice-item-type', [InvoiceItemTypeController::class, 'store'])->name('invoice_item_type.store');
    Route::put('/update-invoice-item-type/{id}', [InvoiceItemTypeController::class, 'update'])->name('invoice_item_type.update');
    Route::delete('/delete-invoice-item-type/{id}', [InvoiceItemTypeController::class, 'delete'])->name('invoice_item_type.delete');

    //Contract Status endpoints
    Route::get('/contract-status', [ContractStatusController::class, 'index'])->name('contract-status.index');
    Route::get('/contract-status/{id}', [ContractStatusController::class, 'show'])->name('contract-status.show');
    Route::post('/store-contract-status', [ContractStatusController::class, 'store'])->name('contract-status.store');
    Route::put('/update-contract-status/{id}', [ContractStatusController::class, 'update'])->name('contract-status.update');
    Route::delete('/delete-contract-status/{id}', [ContractStatusController::class, 'delete'])->name('contract-status.delete');

    //Contract Service Type endpoints
    Route::get('/contract-service-type', [ContractServiceTypeController::class, 'index'])->name('contract-service-type.index');
    Route::get('/contract-service-type/{id}', [ContractServiceTypeController::class, 'show'])->name('contract-service-type.show');
    Route::post('/store-contract-service-type', [ContractServiceTypeController::class, 'store'])->name('contract-service-type.store');
    Route::put('/update-contract-service-type/{id}', [ContractServiceTypeController::class, 'update'])->name('contract-service-type.update');
    Route::delete('/delete-contract-service-type/{id}', [ContractServiceTypeController::class, 'delete'])->name('contract-service-type.delete');

    //Contract Type endpoints
    Route::get('/contract-type', [ContractTypeController::class, 'index'])->name('contract-type.index');
    Route::get('/contract-type/{id}', [ContractTypeController::class, 'show'])->name('contract-type.show');
    Route::post('/store-contract-type', [ContractTypeController::class, 'store'])->name('contract-type.store');
    Route::put('/update-contract-type/{id}', [ContractTypeController::class, 'update'])->name('contract-type.update');
    Route::delete('/delete-contract-type/{id}', [ContractTypeController::class, 'delete'])->name('contract-type.delete');

    //Cancellation Reason Type endpoints
    Route::get('/cancellation-reason-type', [CancellationReasonTypeController::class, 'index'])->name('cancellation-reason-type.index');
    Route::get('/cancellation-reason-type/{id}', [CancellationReasonTypeController::class, 'show'])->name('cancellation-reason-type.show');
    Route::post('/store-cancellation-reason-type', [CancellationReasonTypeController::class, 'store'])->name('cancellation-reason-type.store');
    Route::put('/update-cancellation-reason-type/{id}', [CancellationReasonTypeController::class, 'update'])->name('cancellation-reason-type.update');
    Route::delete('/delete-cancellation-reason-type/{id}', [CancellationReasonTypeController::class, 'delete'])->name('cancellation-reason-type.delete');

    //Jobseeker endpoints
    Route::get('/jobseeker-registrant', [RegistrantController::class, 'getJobseeker'])->name('jobseeker_registrant.index');

    //Remote Contractor endpoints
    Route::get('/remote-contractor-registrant', [RegistrantController::class, 'getRemoteContractor'])->name('remote_contractor_registrant.index');

    //Corporate Apps endpoints
    Route::get('/corporate-apps-registrant', [RegistrantController::class, 'getCorporateApps'])->name('corporate_apps_registrant.index');

    //Client Apps endpoints
    Route::get('/client-registrant/{remote_contractor_id?}', [RegistrantController::class, 'getClient'])->name('client_registrant.index');

    //Jobseeker to Remote Contractor endpoints
    Route::post('/convert-jobseeker-to-remote-contractor-registrant', [RegistrantController::class, 'convertJobseekerToRemoteContractor'])->name('jobseeker_to_remote_contractor_registrant.index');

    //Assign More Client To Remote Contractor endpoints
    Route::post('/assign-more-client-to-remote-contractor', [RegistrantController::class, 'assignMoreClientToRemoteContractor'])->name('assign_more_client_to_remote_contractor.index');

    //Expired Onboard Registration endpoints
    Route::get('/expired-onboard-registrant', [RegistrantController::class, 'expired'])->name('expired_onboard_registrant.index');

    // Client sub contract
    Route::get('/subcontractor/{id}', [ContractController::class, 'show'])->name('client_sub_contractor.show');
    Route::get('/subcontractor', [ContractController::class, 'index'])->name('client_sub_contractor.index');
});

Route::group(['prefix' => 'accounting', 'middleware' => ['auth:admin', 'corporate_role']], function () {

     //Registrant Type endpoints
     Route::get('/forex-rate-type', [ForexRateTypeController::class, 'index'])->name('forex_rate_type.index');
     Route::get('/forex-rate-type/{id}', [ForexRateTypeController::class, 'show'])->name('forex_rate_type.show');
     Route::post('/store-forex-rate-type', [ForexRateTypeController::class, 'store'])->name('forex_rate_type.store');
     Route::put('/update-forex-rate-type/{id}', [ForexRateTypeController::class, 'update'])->name('forex_rate_type.update');
     Route::delete('/delete-forex-rate-type/{id}', [ForexRateTypeController::class, 'delete'])->name('forex_rate_type.delete');

    // Client endpoints
    Route::get('/client', [ClientController::class, 'index'])->name('client.index');
    Route::post('/store-client', [ClientController::class, 'store'])->name('client.store');
    Route::post('/update-client', [ClientController::class, 'update'])->name('client.update');
    Route::post('/delete-client', [ClientController::class, 'delete'])->name('client.delete');
    Route::get('/client-pgn', [ClientController::class, 'clientPgn'])->name('client.clientPgn');

    //ClientSubcontractors endpoints
    Route::get('/client-list', [ClientSubcontractorsController::class, 'clientList'])->name('client_subcontractors.clientList');
    Route::get('/subcon-list', [ClientSubcontractorsController::class, 'subconList'])->name('client_subcontractors.subconList');
    Route::get('/clientsubcon', [ClientSubcontractorsController::class, 'index'])->name('client_subcontractors.index');
    Route::post('/store-clientsubcon', [ClientSubcontractorsController::class, 'store'])->name('client_subcontractors.store');
    Route::post('/update-clientsubcon', [ClientSubcontractorsController::class, 'update'])->name('client_subcontractors.update');
    Route::post('/delete-clientsubcon', [ClientSubcontractorsController::class, 'delete'])->name('client_subcontractors.delete');

    // Client Basic Rate
    Route::get('/client-basic-rate', [ClientBasicRateController::class, 'index'])->name('client_basic_rate.index');
    Route::post('/store-client-basic-rate', [ClientBasicRateController::class, 'store'])->name('client_basic_rate.store');
    Route::post('/update-client-basic-rate', [ClientBasicRateController::class, 'update'])->name('client_basic_rate.update');
    Route::post('/delete-client-basic-rate', [ClientBasicRateController::class, 'delete'])->name('client_basic_rate.delete');
    Route::get('/subcontractor-list/{id}', [ClientBasicRateController::class, 'getSubcon'])->name('client_basic_rate.getSubcon');

    // Client Subcon Rate
    Route::get('/client-subcon-rate', [ClientSubConRateController::class, 'index'])->name('client_subcon_rate.index');
    Route::post('/store-client-subcon-rate', [ClientSubConRateController::class, 'store'])->name('client_subcon_rate.store');
    Route::post('/update-client-subcon-rate', [ClientSubConRateController::class, 'update'])->name('client_subcon_rate.update');
    Route::post('/delete-client-subcon-rate', [ClientSubConRateController::class, 'delete'])->name('client_subcon_rate.delete');

    // Client Forex Rate
    Route::get('/forex-rate', [ForexRateController::class, 'index'])->name('forex_rate.index');
    Route::get('/forex-rate-history', [ForexRateController::class, 'getForexRateHistory'])->name('forex_rate.getForexRateHistory');
    Route::post('/store-forex-rate', [ForexRateController::class, 'store'])->name('forex_rate.store');
    Route::get('/get-forex-rate/{id}', [ForexRateController::class, 'getForexRate'])->name('get-forex_rate.index');
    Route::put('/update-forex-rate/{id}', [ForexRateController::class, 'update'])->name('forex_rate.update');
    Route::delete('/delete-forex-rate/{id}', [ForexRateController::class, 'delete'])->name('forex_rate.delete');

    // Invoice endpoints
    Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice.index');
    Route::get('/get-unpaid-invoices', [InvoiceController::class, 'getUnpaidInvoices'])->name('invoice.getUnpaidInvoices');
    Route::get('/approve-invoice', [InvoiceController::class, 'approveInvoice'])->name('invoice.approveInvoice');
    Route::get('/timesheet-details', [InvoiceController::class, 'timesheetPerClient'])->name('invoice.timesheetPerClient');
    Route::get('/get-temp-invoice-list', [InvoiceController::class, 'temporaryInvoiceList'])->name('invoice.temporaryInvoiceList');
    Route::get('/print-pdf-invoice-per-client', [InvoiceController::class, 'printInvoicePerClient'])->name('invoice.printInvoicePerClient');
    Route::get('/send-invoice', [InvoiceController::class, 'sendInvoice'])->name('invoice.sendInvoice');
    Route::get('/export-pdf-invoice-list', [InvoiceController::class, 'exportInvoicesToPDF'])->name('invoice.exportInvoicesToPDF');
    Route::get('/export-csv-invoice-list', [InvoiceController::class, 'exportInvoicesToCSV'])->name('invoice.exportInvoicesToCSV');
    Route::get('/export-html-invoice-list', [InvoiceController::class, 'exportInvoicesToHTML'])->name('invoice.exportInvoicesToHTML');
    Route::get('/generate-timesheet', [InvoiceController::class, 'generateTimesheet'])->name('invoice.generateTimesheet');

    //Manual Invoice Item Endpoints
    Route::get('/save-invoice-header', [ManualInvoiceController::class, 'saveInvoiceHeader'])->name('save-invoice-header.index');
    Route::post('/save-invoice-detail', [ManualInvoiceController::class, 'saveInvoiceDetail'])->name('save-invoice-detail.index');
    // Route::get('/update-invoice-due-date', [PrepopulatedClientInvoiceItemController::class, 'updateInvoiceDueDate'])->name('update-invoice-due-date.index');
    // Route::get('/prepopulated-client-invoice-header-detail/{id}', [PrepopulatedClientInvoiceItemController::class, 'clientInvoiceHeaderDetail'])->name('prepopulated_client_invoice_header_detail.index');
    // Route::get('/prepopulated-client-invoice-header-item-detail/{id}', [PrepopulatedClientInvoiceItemController::class, 'clientInvoiceHeaderItemDetail'])->name('prepopulated_client_invoice_header_item_detail.index');
    // Route::put('/update-prepopulated-client-invoice-header-item-detail/{id}', [PrepopulatedClientInvoiceItemController::class, 'updateClientInvoiceHeaderItemDetail'])->name('update_prepopulated_client_invoice_header_item_detail.index');

    //Prepopulated Invoice Item Endpoints
    Route::get('/prepopulated-client-invoice-header', [PrepopulatedClientInvoiceItemController::class, 'clientInvoiceHeader'])->name('prepopulated_client_invoice_header.index');
    Route::get('/update-invoice-due-date', [PrepopulatedClientInvoiceItemController::class, 'updateInvoiceDueDate'])->name('update-invoice-due-date.index');
    Route::get('/prepopulated-client-invoice-header-detail/{id}', [PrepopulatedClientInvoiceItemController::class, 'clientInvoiceHeaderDetail'])->name('prepopulated_client_invoice_header_detail.index');
    Route::get('/prepopulated-client-invoice-header-item-detail/{id}', [PrepopulatedClientInvoiceItemController::class, 'clientInvoiceHeaderItemDetail'])->name('prepopulated_client_invoice_header_item_detail.index');
    Route::put('/update-prepopulated-client-invoice-header-item-detail/{id}', [PrepopulatedClientInvoiceItemController::class, 'updateClientInvoiceHeaderItemDetail'])->name('update_prepopulated_client_invoice_header_item_detail.index');

    //Invoice Item Endpoints
    Route::get('/client-invoice-header', [ClientInvoiceItemController::class, 'clientInvoiceHeader'])->name('client_invoice_header.index');
    Route::get('/client-invoice-header-detail/{id}', [ClientInvoiceItemController::class, 'clientInvoiceHeaderDetail'])->name('client_invoice_header_detail.index');
    Route::get('/client-invoice-header-item-detail/{id}', [ClientInvoiceItemController::class, 'clientInvoiceHeaderItemDetail'])->name('client_invoice_header_item_detail.index');
    Route::post('/store-client-invoice-header-item-detail', [ClientInvoiceItemController::class, 'storeClientInvoiceHeaderItemDetail'])->name('store_client_invoice_header_item_detail.index');
    Route::put('/update-client-invoice-header-item-detail/{id}', [ClientInvoiceItemController::class, 'updateClientInvoiceHeaderItemDetail'])->name('update_client_invoice_header_item_detail.index');
    Route::delete('/delete-client-invoice-header-item-detail/{id}', [ClientInvoiceItemController::class, 'deleteClientInvoiceHeaderItemDetail'])->name('delete_client_invoice_header_item_detail.index');
    Route::get('/voidable-client-invoice-header-item-detail/{id}', [ClientInvoiceItemController::class, 'voidableClientInvoiceHeaderItemDetail'])->name('voidable_client_invoice_header_item_detail.index');
    Route::put('/void-client-invoice-header-item-detail/{id}', [ClientInvoiceItemController::class, 'voidClientInvoiceHeaderItemDetail'])->name('void_client_invoice_header_item_detail.index');

    // Void invoice
    Route::get('/utility/show-invoice/{id}', [VoidInvoiceController::class, 'show'])->name('void_invoice.show');
    Route::post('/utility/update-invoice', [VoidInvoiceController::class, 'update'])->name('void_invoice.update');
});


Route::group(['prefix' => 'timesheet', 'middleware' => 'auth:admin'], function () {

    Route::get('/client-list', [TimesheetController::class, 'getClients'])->name('timesheet.clientList');
    Route::get('/staff-list', [TimesheetController::class, 'getStaff'])->name('timesheet.stafflist');
    Route::get('/get-timesheet', [TimesheetController::class, 'getTimeSheet'])->name('timesheet.get');
    Route::get('/get-history', [TimesheetController::class, 'getHistory'])->name('history.get');
    Route::post('/save-timesheet', [TimesheetController::class, 'store'])->name('timesheet.store');

    Route::get('/get-timesheet-report', [TimesheetController::class, 'getTimeSheetReport'])->name('timesheet-report.get');
});

Route::group(['prefix' => 'client', 'middleware' => 'auth:client', 'client_role'], function () {
    Route::get('/get-screenshots', [ScreenCaptureController::class, 'getScreenshots'])->name('client.screenshots');
    Route::get('/get-activity-notes', [ActivityNotesController::class, 'getActivityNotes'])->name('client.activitynotes');
    Route::get('/get-client-id', [ClientSubcontractorController::class, 'getClient'])->name('client.getclientid');
    Route::get('/get-subcontractors', [ClientSubcontractorController::class, 'getStaff'])->name('client.getstaffprod');

    Route::get('/remote-contractor-attendance', [AttendanceController::class, 'getRemoteContractorAttendance'])->name('remote_contractor_attendance.show');
    Route::get('/remote-contractor-timesheet', [ClientTimesheetController::class, 'getRemoteContractorTimesheet'])->name('remote_contractor_timesheet.show');
});

Route::group(['prefix' => 'crf', 'middleware' => 'auth:client', 'client_role'], function () {
    Route::get('/getJobOrder/{jobId}', [CrfController::class, 'getJobOrder'])->name('getJobOrder');
    Route::get('/getIndustries', [CrfController::class, 'getIndustries'])->name('getIndustries');
    Route::post('/updateJobOrder', [CrfController::class, 'updateJobOrder'])->name('updateJobOrder');
    Route::get('/getAllJobOrder/{start}/{count}/{clientId}', [CrfController::class, 'getAllJobOrder'])->name('getAllJobOrder');
});


Route::group(['prefix' => 'sam', 'middleware' => 'auth:admin'], function () {
    Route::apiResource('pillar', PillarController::class);
    Route::apiResource('subpillar', SubPillarController::class);
    Route::apiResource('menuheading', MenuHeadingController::class);
});
