<?php
/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the Closure to execute when that URI is requested.
  |
 */

Route::filter('initialize', function() {
	Session::forget('type');
	Session::forget('valu');
	Session::forget('che');
	Session::forget('msg');
	$success = Input::get("success");
	if($success) {
		Session::put('success', $success);
	}
	else {
		Session::forget('success');
	}
	$error = Input::get("error");
	if($success) {
		Session::put('error', $error);
	}
	else {
		Session::forget('error');
	}
});

Route::get('/dist', 'HelloController@index');
Route::get('/test', 'HelloController@test');
Route::get('/payms', 'HelloController@payms');
Route::post('/dog/addschedule', 'CustomerController@add_schedule');
Route::post('/dog/cancelschedule', 'CustomerController@cancel_schedule');
Route::get('/dog/getwalkers', 'CustomerController@get_walkers');
Route::post('/dog/assignwalker', 'CustomerController@assign_walker');
Route::get('/walk/walkinprogress', 'CustomerController@walkinprogress');
Route::get('/walk/nonreviewedwalks', 'CustomerController@nonreviewedwalks');
Route::get('/walks', 'CustomerController@get_walks');
Route::post('/walk/walksummary', 'ProviderController@walk_summary');
Route::post('/walk/photo', 'ProviderController@upload_photo');
Route::post('/walk/video', 'ProviderController@upload_video');
Route::get('/walker/walks', 'ProviderController@get_walks');
Route::get('/walker/details', 'ProviderController@get_details');
Route::post('/walker/cancelwalk', 'ProviderController@cancel_walk');
Route::post('/walker/getwalk', 'ProviderController@walk_details');
Route::post('/walker/getschedule', 'ProviderController@get_schedule');
// On Demand API's
// Owner APIs
Route::post('/user/login', 'OwnerController@login');
Route::post('/user/register', 'OwnerController@register');
Route::post('/user/location', 'CustomerController@set_location');
Route::any('/user/details', 'OwnerController@details');
Route::post('/user/addcardtoken', 'OwnerController@addcardtoken');
Route::get('/user/braintreekey', 'OwnerController@get_braintree_token');
Route::post('/user/deletecardtoken', 'OwnerController@deletecardtoken');
Route::post('/user/update', 'OwnerController@update_profile');
Route::post('/user/paydebt', 'OwnerController@pay_debt');
Route::post('/user/selectcard', 'OwnerController@select_card');
Route::post('/user/card_selection', 'OwnerController@card_selection');
Route::get('/user', 'OwnerController@getProfile');
Route::any('/user/thing', 'CustomerController@create');
Route::post('/user/updatething', 'CustomerController@update_thing');
Route::post('/user/createrequest', 'CustomerController@create_request');
Route::post('/user/payment_type', 'OwnerController@payment_type');
Route::post('/user/createrequestlater', 'CustomerController@create_request_later');
Route::post('/user/createfuturerequest', 'CustomerController@create_future_request');
Route::post('/user/getfuturerequest', 'CustomerController@get_future_request');
Route::post('/user/deletefuturerequest', 'CustomerController@delete_future_request');
/* Route::post('/user/getproviders', 'CustomerController@get_providers'); */
Route::post('/user/getproviders', 'CustomerController@get_providers_old');
Route::post('/user/getproviders_new', 'CustomerController@get_providers');
Route::post('/user/getprovidersall', 'CustomerController@get_providers_all');
Route::post('/user/getnearbyproviders', 'CustomerController@get_nearby_providers');
Route::post('/user/createrequestproviders', 'CustomerController@create_request_providers');
Route::post('/user/cancellation', 'CustomerController@cancellation');
Route::get('/user/getrequest', 'CustomerController@get_request');
Route::post('/user/getrunningrequest', 'CustomerController@get_running_request');
Route::post('/user/cancelrequest', 'CustomerController@cancel_request');
/* CRON JOB START */
Route::get('/server/schedulerequest', 'CustomerController@schedule_request');
Route::get('/server/schedulefuturerequest', 'CustomerController@schedule_future_request');
Route::get('/server/autotransfertoproviders', 'CustomerController@auto_transfer_to_providers');
/* CRON JOB END */
Route::get('/user/getrequestlocation', 'CustomerController@get_request_location');
Route::post('/user/rating', 'CustomerController@set_walker_rating');
Route::get('/user/requestinprogress', 'CustomerController@request_in_progress');
Route::get('/user/requestpath', 'CustomerController@get_walk_location');
Route::get('/provider/requestpath', 'ProviderController@get_walk_location');
Route::post('/user/referral', 'OwnerController@set_referral_code');
Route::get('/user/referral', 'OwnerController@get_referral_code');
Route::post('/user/apply-referral', 'OwnerController@apply_referral_code');
Route::post('/user/apply-promo', 'OwnerController@apply_promo_code');

Route::post('/user/promocode', 'OwnerController@promocode');
Route::post('/user/verifygeofence', 'OwnerController@verifygeofence');

Route::get('/user/cards', 'OwnerController@get_cards');
Route::get('/user/history', 'OwnerController@get_completed_requests');
Route::post('/user/paybypaypal', 'OwnerController@paybypaypal');
Route::get('/user/send_eta', 'OwnerController@send_eta');
Route::get('/user/current_eta', 'CustomerController@eta');
Route::get('/user/credits', 'OwnerController@get_credits');
Route::get('/user/payment_options', array('as' => '/user/payment_options', 'uses' => 'OwnerController@payment_options_allowed'));
Route::get('/user/check_promo_code', 'CustomerController@check_promo_code');
Route::post('/user/logout', 'OwnerController@logout');
Route::post('/user/payment_select', 'CustomerController@payment_select');
Route::post('/user/provider_list', 'CustomerController@get_provider_list');
Route::post('/user/setdestination', 'CustomerController@user_set_destination');
Route::get('/provider/check_banking', 'ProviderController@check_banking');
// Walker APIs
Route::get('/provider/getrequests', 'ProviderController@get_requests');
Route::get('/provider/getrequest', 'ProviderController@get_request');
Route::post('/provider/respondrequest', 'ProviderController@respond_request');
Route::post('/provider/location', 'ProviderController@walker_location');
Route::post('/provider/requestwalkerstarted', 'ProviderController@request_walker_started');
Route::post('/provider/requestwalkerarrived', 'ProviderController@request_walker_arrived');
Route::post('/provider/requestwalkstarted', 'ProviderController@request_walk_started');
Route::post('/request/location', 'ProviderController@walk_location');
Route::post('/provider/requestwalkcompleted', 'ProviderController@request_walk_completed');
Route::post('/provider/prepayment', 'ProviderController@pre_payment');
Route::post('/provider/paymentselection', 'ProviderController@payment_selection');
Route::post('/provider/rating', 'ProviderController@set_dog_rating');
Route::post('/provider/login', 'ProviderController@login');
Route::post('/provider/register', 'ProviderController@register');
Route::post('/provider/update', 'ProviderController@update_profile');
Route::post('/provider_services/update', 'ProviderController@provider_services_update');
Route::get('/provider/services_details', 'ProviderController@services_details');
Route::get('/provider/requestinprogress', 'ProviderController@request_in_progress');
Route::get('/provider/checkstate', 'ProviderController@check_state');
Route::post('/provider/togglestate', 'ProviderController@toggle_state');
Route::get('/provider/history', 'ProviderController@get_completed_requests');
Route::post('panic', array('as' => 'panic', 'uses' => 'ProviderController@panic'));
Route::post('/provider/logout', 'ProviderController@logout');
// Info Page API
Route::get('/application/pages', 'ApplicationController@pages');
Route::get('/application/types', 'ApplicationController@types');
Route::get('/application/page/{id}', 'ApplicationController@get_page');
Route::post('/application/forgot-password', 'ApplicationController@forgot_password');

//Register intial admin - added to create intial admin user - remember to remove for production deployment
Route::get('/admin/register', array('as' => '/admin/register', 'uses' => 'AdminController@admin_register'));
Route::post('/admin/add', array('as' => 'AdminAdd', 'uses' => 'AdminController@add'));

// Admin Panel
Route::get('/admin/requests_payment', array('as' => 'AdminRequest_payment', 'uses' => 'AdminController@walks_payment'));
Route::post('/admin/requests_pdf', array('as' => 'AdminRequest_paymentPdf', 'uses' => 'AdminController@admin_week_pdf'));
Route::get('/admin/report', array('as' => 'AdminReport', 'uses' => 'AdminController@report'));
Route::get('/admin/payprovider/{id}', array('as' => 'AdminPayProvider', 'uses' => 'AdminController@pay_provider'));
Route::get('/admin/chargeuser/{id}', array('as' => 'AdminChargeUser', 'uses' => 'AdminController@charge_user'));
Route::get('/admin/addreq/{id}', array('as' => 'AdminAddRequest', 'uses' => 'AdminController@add_request'));
Route::post('/admin/transfer_amount', array('as' => 'AdminProviderPay', 'uses' => 'AdminController@transfer_amount'));
Route::get('/admin/map_view', array('as' => 'AdminMapview', 'uses' => 'AdminController@map_view'));
Route::get('/admin/providers', array('as' => 'AdminProviders', 'uses' => 'AdminController@DriverList'));

Route::get('/admin/driver/profile/{id}',
	array(
		'uses' => 'AdminController@DriverProfile'
	)
);

Route::post('admin/driver/profile',
	array(
		'as' => 	'DriverProfileSave',
		'uses' => 	'AdminController@DriverProfileSave'
	)
);

Route::get('/admin/users', array('as' => 'AdminUsers', 'uses' => 'AdminController@owners'));

Route::get('/admin/requests', array('as' => 'AdminRequest', 'uses' => 'AdminController@walks'));
Route::get('/admin/healthcarerequests', array('as' => 'AdminHealthcareRequest', 'uses' => 'AdminController@HealthcareRequest'));
Route::get('/admin/dispatcherrequests', array('as' => 'AdminDispatcherRequest', 'uses' => 'AdminController@DispatcherRequest'));
Route::get('/admin/schedule', array('as' => 'AdminSchedule', 'uses' => 'AdminController@scheduled_walks'));
Route::get('/admin/reviews', array('as' => 'AdminReviews', 'uses' => 'AdminController@reviews'));
Route::get('/admin/reviews/delete/{id}', array('as' => 'AdminReviewsDelete', 'uses' => 'AdminController@delete_review'));
Route::get('/admin/reviews/delete_client/{id}', array('as' => 'AdminReviewsDeleteDog', 'uses' => 'AdminController@delete_review_owner'));
Route::get('/admin/search', array('as' => 'AdminSearch', 'uses' => 'AdminController@search'));
Route::get('/admin/login', array('as' => 'AdminLogin', 'uses' => 'AdminController@login'));
Route::post('/admin/verify', array('as' => 'AdminVerify', 'uses' => 'AdminController@verify'));
Route::get('/admin/logout', array('as' => 'AdminLogout', 'uses' => 'AdminController@logout'));
Route::get('/admin/admins', array('as' => 'AdminAdmins', 'uses' => 'AdminController@admins'));
Route::get('/admin/add_admin', array('as' => 'AdminAddAdmin', 'uses' => 'AdminController@add_admin'));
Route::get('/admin/user/referral/{id}', array('as' => 'AdminUserReferral', 'uses' => 'AdminController@referral_details'));
Route::post('/admin/admins/add', array('as' => 'AdminAdminsAdd', 'uses' => 'AdminController@add_admin_do'));
Route::get('/admin/admins/edit/{id}', array('as' => 'AdminAdminsEdit', 'uses' => 'AdminController@edit_admins'));
Route::post('/admin/admins/update', array('as' => 'AdminAdminsUpdate', 'uses' => 'AdminController@update_admin'));
Route::get('/admin/admins/delete/{id}', array('as' => 'AdminAdminsDelete', 'uses' => 'AdminController@delete_admin'));

Route::get('/admin/enterpriseclients',
	array(
		'as' => 		'EnterpriseClients',
		'uses' => 		'AdminController@EnterpriseClients',
		'before' =>		'initialize'
	)
);

Route::get('/admin/enterpriseclient/profile/{id}',
	array(
		'as' => 		'EnterpriseClientProfile',
		'uses' => 		'AdminController@EnterpriseClientProfile',
		'before' =>		'initialize'
	)
);

Route::post('/admin/enterpriseclient/profile',
	array(
		'as' => 		'EnterpriseClientProfileSave',
		'uses' => 		'AdminController@EnterpriseClientProfileSave',
		'before' =>		'initialize'
	)
);

Route::get('/admin/enterpriseclient/rateprofile/{index}/{enterpriseclient_id}/{service_type}',
	array(
		'as' => 		'RateProfile',
		'uses' => 		'AdminController@EnterpriseRateProfile',
		'before' =>		'initialize'
	)
);

Route::get('/admin/enterpriseclient/fundingprofile/{index}/{enterpriseclient_id}/{funding_rule_type}',
	array(
		'as' => 		'FundingProfile',
		'uses' => 		'AdminController@EnterpriseFundingProfile',
		'before' =>		'initialize'
	)
);


Route::get('/admin/healthprovider/decline/{id}', array('as' => 'AdminHealthcareProviderDecline', 'uses' => 'AdminController@decline_healthcareprovider'));
Route::get('/admin/healthprovider/approve/{id}', array('as' => 'AdminHealthcareProviderApprove', 'uses' => 'AdminController@approve_EnterpriseClient'));
Route::get('/admin/healthcareagents', array('as' => 'AdminHealthcareAgents', 'uses' => 'AdminController@HealthcareAgents'));
Route::get('/admin/healthcareagent/decline/{id}', array('as' => 'AdminHealthcareAgentsDecline', 'uses' => 'AdminController@decline_healthcareagent'));
Route::get('/admin/healthcareagent/approve/{id}', array('as' => 'AdminHealthcareAgentsApprove', 'uses' => 'AdminController@approve_healthcareagent'));
Route::get('/admin', array('as' => 'admin', 'uses' => 'AdminController@index'));
Route::get('/admin/add', array('as' => 'AdminAdd', 'uses' => 'AdminController@add'));
Route::get('/admin/savesetting', array('as' => 'AdminSettingDontShow', 'uses' => 'AdminController@skipSetting'));
Route::get('/admin/provider/edit/{id}', array('as' => 'AdminProviderEdit', 'uses' => 'AdminController@edit_walker'));
Route::get('/admin/provider/edit/availability/{id}', array('as' => 'provider_availabilty', 'uses' => 'AdminController@provider_availabilty'));
Route::get('/admin/provider/add', array('as' => 'AdminProviderAdd', 'uses' => 'AdminController@add_walker'));
Route::get('/admin/promo_code/add', array('as' => 'AdminPromoAdd', 'uses' => 'AdminController@add_promo_code'));
Route::get('/admin/promo_code/edit/{id}', array('as' => 'AdminPromoCodeEdit', 'uses' => 'AdminController@edit_promo_code'));
Route::get('/admin/promo_code/deactivate/{id}', array('as' => 'AdminPromoCodeDeactivate', 'uses' => 'AdminController@deactivate_promo_code'));
Route::get('/admin/promo_code/activate/{id}', array('as' => 'AdminPromoCodeActivate', 'uses' => 'AdminController@activate_promo_code'));
Route::post('/admin/provider/update', array('as' => 'AdminProviderUpdate', 'uses' => 'AdminController@update_walker'));
Route::post('/admin/promo_code/update', array('as' => 'AdminPromoUpdate', 'uses' => 'AdminController@update_promo_code'));
Route::get('/admin/provider/history/{id}', array('as' => 'AdminProviderHistory', 'uses' => 'AdminController@walker_history'));
Route::get('/admin/provider/documents/{id}', array('as' => 'AdminProviderDocuments', 'uses' => 'AdminController@walker_documents'));
Route::get('/admin/provider/requests/{id}', array('as' => 'AdminProviderRequest', 'uses' => 'AdminController@walker_upcoming_walks'));
Route::get('/admin/provider/decline/{id}', array('as' => 'AdminProviderDecline', 'uses' => 'AdminController@decline_walker'));
Route::get('/admin/provider/delete/{id}', array('as' => 'AdminProviderDelete', 'uses' => 'AdminController@delete_walker'));
Route::get('/admin/provider/approve/{id}', array('as' => 'AdminProviderApprove', 'uses' => 'AdminController@approve_walker'));
Route::get('/admin/providers_xml', array('as' => 'AdminProviderXml', 'uses' => 'AdminController@walkers_xml'));
Route::get('/admin/user/delete/{id}', array('as' => 'AdminDeleteUser', 'uses' => 'AdminController@delete_owner'));

Route::get('/admin/user/edit/{id}', array('as' => 'AdminUserEdit', 'uses' => 'AdminController@edit_rider'));

Route::post('/admin/user/update', array('as' => 'AdminUserUpdate', 'uses' => 'AdminController@update_owner'));
Route::get('/admin/user/history/{id}', array('as' => 'AdminUserHistory', 'uses' => 'AdminController@owner_history'));
Route::get('/admin/user/requests/{id}', array('as' => 'AdminUserRequest', 'uses' => 'AdminController@owner_upcoming_walks'));
Route::get('/admin/request/decline/{id}', array('as' => 'AdminUserDecline', 'uses' => 'AdminController@decline_walk'));
Route::get('/admin/request/approve/{id}', array('as' => 'AdminUserApprove', 'uses' => 'AdminController@approve_walk'));
Route::get('/admin/request/map/{id}', array('as' => 'AdminRequestMap', 'uses' => 'AdminController@view_map'));
Route::get('/admin/request/change_provider/{id}', array('as' => 'AdminRequestChangeProvider', 'uses' => 'AdminController@change_walker'));
Route::get('/admin/request/alternative_providers_xml/{id}', array('as' => 'AdminRequestAlternative', 'uses' => 'AdminController@alternative_walkers_xml'));
Route::post('/admin/request/change_provider', array('as' => 'AdminRequestChange', 'uses' => 'AdminController@save_changed_walker'));
Route::post('/admin/request/pay_provider', array('as' => 'AdminRequestPay', 'uses' => 'AdminController@pay_walker'));
Route::get('/admin/settings', array('as' => 'AdminSettings', 'uses' => 'AdminController@get_settings'));
Route::get('/admin/settings/installation', array('as' => 'AdminSettingInstallation', 'uses' => 'AdminController@installation_settings'));
Route::post('/admin/install', array('as' => 'AdminInstallFinish', 'uses' => 'AdminController@finish_install'));
Route::post('/admin/certi', array('as' => 'AdminAddCerti', 'uses' => 'AdminController@addcerti'));
Route::post('/admin/theme', array('as' => 'AdminTheme', 'uses' => 'AdminController@theme'));
Route::post('/admin/settings', array('as' => 'AdminSettingsSave', 'uses' => 'AdminController@save_settings'));
Route::get('/admin/informations', array('as' => 'AdminInformations', 'uses' => 'AdminController@get_info_pages'));
Route::get('/admin/information/edit/{id}', array('as' => 'AdminInformationEdit', 'uses' => 'AdminController@edit_info_page'));
Route::post('/admin/information/update', array('as' => 'AdminInformationUpdate', 'uses' => 'AdminController@update_info_page'));
Route::get('/admin/information/delete/{id}', array('as' => 'AdminInformationDelete', 'uses' => 'AdminController@delete_info_page'));
Route::get('/admin/provider-types', array('as' => 'AdminProviderTypes', 'uses' => 'AdminController@get_provider_types'));
Route::get('/admin/provider-type/edit/{id}', array('as' => 'AdminProviderTypeEdit', 'uses' => 'AdminController@edit_provider_type'));
Route::post('/admin/provider-type/update', array('as' => 'AdminProviderTypeUpdate', 'uses' => 'AdminController@update_provider_type'));
Route::get('/admin/provider-type/delete/{id}', array('as' => 'AdminProviderTypeDelete', 'uses' => 'AdminController@delete_provider_type'));
Route::get('/admin/document-types', array('as' => 'AdminDocumentTypes', 'uses' => 'AdminController@get_document_types'));
Route::get('/admin/promo_code', array('as' => 'AdminPromoCodes', 'uses' => 'AdminController@get_promo_codes'));
Route::get('/admin/edit_keywords', array('as' => 'AdminKeywords', 'uses' => 'AdminController@edit_keywords'));
Route::post('/admin/save_keywords', array('as' => 'AdminKeywordsSave', 'uses' => 'AdminController@save_keywords'));
Route::post('/admin/save_keywords_ui', array('as' => 'AdminUIKeywordsSave', 'uses' => 'AdminController@save_keywords_UI'));
Route::post('/admin/save_developer_details', array('as' => 'AdminDeveloperDetailsSave', 'uses' => 'AdminController@save_developer_details'));
Route::get('/admin/document-type/edit/{id}', array('as' => 'AdminDocumentTypesEdit', 'uses' => 'AdminController@edit_document_type'));
Route::post('/admin/document-type/update', array('as' => 'AdminDocumentTypesUpdate', 'uses' => 'AdminController@update_document_type'));
Route::get('/admin/document-type/delete/{id}', array('as' => 'AdminDocumentTypesDelete', 'uses' => 'AdminController@delete_document_type'));
Route::post('/admin/adminCurrency', array('as' => 'adminCurrency', 'uses' => 'AdminController@adminCurrency'));
Route::get('/admin/details_payment', array('as' => 'AdminPayment', 'uses' => 'AdminController@payment_details'));
Route::get('/admin/provider/banking/{id}', array('as' => 'AdminProviderBanking', 'uses' => 'AdminController@banking_provider'));
Route::post('/admin/provider/providerB_bankingSubmit', array('as' => 'AdminProviderBBanking', 'uses' => 'AdminController@providerB_bankingSubmit'));
Route::post('/admin/provider/providerS_bankingSubmit', array('as' => 'AdminProviderSBanking', 'uses' => 'AdminController@providerS_bankingSubmit'));
Route::post('admin/add-request', array('as' => 'adminmanualrequest', 'uses' => 'AdminController@create_manual_request'));
//Admin Panel Sorting
Route::get('/admin/sortur', array('as' => '/admin/sortur', 'uses' => 'AdminController@sortur'));
Route::get('/admin/sortpv', array('as' => '/admin/sortpv', 'uses' => 'AdminController@sortpv'));
Route::get('/admin/sortpvtype', array('as' => '/admin/sortpvtype', 'uses' => 'AdminController@sortpvtype'));
Route::get('/admin/sortreq', array('as' => '/admin/sortreq', 'uses' => 'AdminController@sortreq'));
Route::get('/admin/sortpromo', array('as' => '/admin/sortpromo', 'uses' => 'AdminController@sortpromo'));
Route::post('/admin/healthcarerequest/AdminCancelRide', array('as' => 'AdminCancelRide', 'uses' => 'AdminController@AdminCancelRide'));
Route::post('/admin/healthcarerequest/AdminConfirmRide', array('as' => 'AdminConfirmRide', 'uses' => 'AdminController@AdminConfirmRide'));
Route::post('/admin/healthcarerequest/AdminCompleteRide', array('as' => 'AdminCompleteRide', 'uses' => 'AdminController@AdminCompleteRide'));
//Provider Availability
Route::get('/admin/provider/allow_availability', array('as' => 'AdminProviderAllowAvailability', 'uses' => 'AdminController@allow_availability'));
Route::get('/admin/provider/disable_availability', array('as' => 'AdminProviderDisableAvailability', 'uses' => 'AdminController@disable_availability'));
Route::get('/admin/provider/availability/{id}', array('as' => 'AdminProviderAvailability', 'uses' => 'AdminController@availability_provider'));
Route::post('/admin/provider/availabilitySubmit/{id}', array('as' => 'AdminProviderAvailabilitySubmit', 'uses' => 'AdminController@provideravailabilitySubmit'));
Route::get('/admin/provider/view_documents/{id}', array('as' => 'AdminViewProviderDoc', 'uses' => 'AdminController@view_documents_provider'));
Route::post('/admin/dispatcherrequest/DispatcherConfirmRide', array('as' => 'DispatcherConfirmRide', 'uses' => 'AdminController@DispatcherConfirmRide'));
Route::post('/admin/dispatcherrequest/DispatcherCancelRide', array('as' => 'DispatcherCancelRide', 'uses' => 'AdminController@DispatcherCancelRide'));
Route::post('/admin/dispatcherrequest/DispatcherCompleteRide', array('as' => 'DispatcherAdminManualRideCompleted', 'uses' => 'AdminController@DispatcherManualRideCompleted'));
Route::post('/admin/healthcare/delete', array('as' => 'AdminDeleteRide', 'uses' => 'AdminController@AdminDeleteRide'));
Route::post('/admin/healthcare/AddBalance', array('as' => 'AddBalance', 'uses' => 'AdminController@AddBalance'));
Route::post('/admin/healthcare/sendrideinfo', array('as'=>'AdminSendRideInfo', 'uses'=>'AdminController@AdminSendRideInfo'));

Route::get('/admin/transportationproviders',
	array(
		'as' => 		'AdminTransportationProvider',
		'uses' => 		'AdminController@TransportationProvider',
		'before' =>		'initialize'
	)
);

Route::get('/admin/tp/profile/{id}',
	array(
		'as' => 		'AdminTransportationProviderProfile',
		'uses' => 		'AdminController@TransportationProviderProfile',
		'before' =>		'initialize'
	)
);

Route::get('/admin/transportationproviders/new', array('as'=>'AddNewTransportationProvider', 'uses'=>'AdminController@AddNewTransportationProvider'));
Route::post('/admin/transportationproviders/insert', array('as' => 'TransportationProviderInsertUpdate', 'uses' => 'AdminController@TransportationProviderInsertUpdate'));
Route::get('/admin/driver/certificate_status/{id}', array('as' => 'DriverCertificateStatus', 'uses' => 'AdminController@DriverCertificateStatus'));
Route::get('/admin/driver/addcertificatestatus', array('as'=>'AddCertificateJson', 'uses'=>'AdminController@AddCertificateJson'));
Route::post('/admin/driver/sendcertificate', array('as'=>'SendCertificate', 'uses'=>'AdminController@SendCertificate'));
Route::get('/admin/feecalculator',array('as'=>'FeeCalculator', 'uses'=>'AdminController@FeeCalculator'));
Route::post('/admin/generatefeereceipt',array('as'=>'GenerateFeeReceipt', 'uses'=>'AdminController@GenerateFeeReceipt'));
Route::get('/admin/trainingmodule', array('as'=>'TrainingModuleAdmin', 'uses'=>'AdminController@TrainingModuleAdmin'));
Route::get('/admin/trainingmodule/enrolldrivers/{id}', array('as'=>'ModulesEnrollDriversAdmin', 'uses'=>'AdminController@EnrollDriversAdmin'));
Route::post('/admin/trainingmodule/adddrivers', array('as'=>'AddDriversAdmin', 'uses'=>'AdminController@AddDriversAdmin'));
Route::get('/admin/trainingmodule/driverlisting/{id}', array('as'=>'TrainingDriverlistingAdmin', 'uses'=>'AdminController@DriverListingAdmin'));
Route::get('/admin/trainingmodule/deletedriver/{id}/{driverid}', array('as'=>'ModulesDeleteDriversAdmin', 'uses'=>'AdminController@DeleteDriverAdmin'));
Route::get('/admin/DownloadDriverReport', array('as' => 'DownloadDriverReport', 'uses' => 'AdminController@DownloadDriverReport'));
Route::post('/admin/trainingmodule/invoicesent', array('as'=>'InvoiceSent', 'uses'=>'AdminController@InvoiceSent'));
Route::post('/admin/trainingmodule/invoicepaid', array('as'=>'InvoicePaid', 'uses'=>'AdminController@InvoicePaid'));
Route::get('/admin/transportationproviders/rating', array('as'=>'RatingTransportationProvider', 'uses'=>'AdminController@RatingTransportationProvider'));


/// /Providers Who currently walking
Route::get('/admin/provider/current', array('as' => 'AdminProviderCurrent', 'uses' => 'AdminController@current'));
Route::get('/admin/provider/accessdlc/{id}', array('as' => 'AdminProviderAccessDLC', 'uses' => 'AdminController@AdminProviderAccessDLC'));
Route::get('/admin/provider/authorize/{id}', array('as' => 'AdminProviderAuthorize', 'uses' => 'AdminController@AdminProviderAuthorize'));
//dispatcher
Route::get('/dispatcher/signin', array('as' => '/dispatcher/signin', 'uses' => 'DispatcherController@userLogin'));
Route::get('/dispatcher/signup', array('as' => '/dispatcher/signup', 'uses' => 'DispatcherController@userRegister'));
Route::post('/dispatcher/forgot-password', array('as' => '/dispatcher/forgot-password', 'uses' => 'DispatcherController@dispatcherForgotPassword'));
Route::post('/dispatcher/verify', array('as' => '/dispatcher/verify', 'uses' => 'DispatcherController@userVerify'));
Route::get('/dispatcher/logout', array('as' => '/dispatcher/logout', 'uses' => 'DispatcherController@userLogout'));
Route::get('/dispatcher/trips', array('as' => '/dispatcher/trips', 'uses' => 'DispatcherController@userTrips'));
Route::get('/dispatcher/myservice', array('as' => 'myservice', 'uses' => 'DispatcherController@myservices'));
Route::get('/dispatcher/request-service', array('as' => 'requestservice', 'uses' => 'DispatcherController@servicerequest'));
Route::post('/dispatcher/getdrivers', array('as' => 'drivers', 'uses' => 'DispatcherController@getdrivers'));
Route::post('/dispatcher/getdrivername', array('as' => 'drivername', 'uses' => 'DispatcherController@getdrivername'));
Route::post('/dispatcher/getalldrivers', array('as' => 'alldrivers', 'uses' => 'DispatcherController@getalldrivers'));
Route::post('/dispatcher/getloggedindrivers', array('as' => 'loggedindrivers', 'uses' => 'DispatcherController@getloggedindrivers'));
Route::post('/dispatcher/save', array('as' => '/dispatcher/save', 'uses' => 'DispatcherController@userSave'));
Route::post('/dispatcher/savedispatcher', array('as' => 'savedispatcher', 'uses' => 'DispatcherController@savedispatcherrequest'));
Route::post('/dispatcher/forgot-password', array('as' => '/dispatcher/forgot-password', 'uses' => 'DispatcherController@dispatcherForgotPassword'));
Route::get('/dispatcher/request/map/{id}', array('as' => 'dispatcherRequestMap', 'uses' => 'DispatcherController@view_map'));
Route::get('/dispatcher/disp_request/map/{id}', array('as' => 'dispatcherMap', 'uses' => 'DispatcherController@view_dispatcher_map'));
Route::get('/dispatcher', 'DispatcherController@index');
Route::get('/dispatcher/login', 'DispatcherController@index');
Route::post('/dispatcher/request/cancelrideDispatcher', array('as' => 'cancelridedispatcher', 'uses' => 'DispatcherController@cancelrideDispatcher'));
Route::post('/dispatcher/request/cancelmanulrideDispatcher', array('as' => 'cancelmanulridedispatcher', 'uses' => 'DispatcherController@cancelmanulrideDispatcher'));
//payment for stripe
Route::post('/dispatcher/save-customerpayments', array('as' => 'save-customerpayments', 'uses' => 'DispatcherController@savecustomerpayments'));
Route::post('/dispatcher/save-customer-payments', array('as' => 'save-customer-payments', 'uses' => 'DispatcherController@SavePassengerCards'));
Route::post('/dispatcher/check-dispatcherassigned', array('as' => 'dispatcherassigned', 'uses' => 'DispatcherController@checkDispatcherassigned'));
Route::post('/dispatcher/dispatcher_chargeuser', array('as' => 'DispatcherChargeUser', 'uses' => 'DispatcherController@dispatchercharge_user'));
Route::post('/dispatcher/updatedefaultcard', array('as' => 'updatedefaultcard', 'uses' => 'DispatcherController@updatedefaultpaymentcard'));
Route::post('/dispatcher/updatedefaultPassengercard', array('as' => 'updatedefaultPassengercard', 'uses' => 'DispatcherController@updatedefaultPassengerpaymentcard'));

Route::post('/dispatcher/request/DispatcherRideCompleted', array('as' => 'DispatcherRideCompleted', 'uses' => 'DispatcherController@DispatcherRideCompleted'));
Route::post('/dispatcher/checkPaymentData', array('as' => 'checkpaymentdata', 'uses' => 'DispatcherController@checkPaymentData'));
Route::post('/dispatcher/CalculateAmount', array('as' => 'dispatchercalculatecost', 'uses' => 'DispatcherController@CalculateAmount'));
Route::get('/dispatcher/manualrides', array('as' => 'manualrides', 'uses' => 'DispatcherController@manualrides'));
Route::get('/dispatcher/submittedrides', array('as' => 'submittedrides', 'uses' => 'DispatcherController@SubmittedRides'));

Route::get('/dispatcher/confirmedrides', array('as' => 'confirmedrides', 'uses' => 'DispatcherController@ConfirmedRides'));

Route::get('/dispatcher/cancelledrides', array('as' => 'cancelledrides', 'uses' => 'DispatcherController@CancelledRides'));

Route::get('/dispatcher/completedrides', array('as' => 'completedrides', 'uses' => 'DispatcherController@CompletedRides'));

Route::post('/dispatcher/assigndriver', array('as' => 'assigndriver', 'uses' => 'DispatcherController@AssignDriver'));

Route::post('/dispatcher/assigntp', array('as' => 'assigntp', 'uses' => 'DispatcherController@AssignTP'));

Route::post('/dispatcher/getservicespecificdrivers', array('as' => 'getservicespecificdrivers', 'uses' => 'DispatcherController@GetServicespecificDrivers'));
Route::post('/dispatcher/getcardlink',array('as'=>'getcardlink', 'uses'=>'DispatcherController@getcardlink'));


Route::post('/dispatcher/dispatchermanualrideconfirmed', array('as' => 'DispatcherManualRideConfirmed',
    'uses' => 'DispatcherController@DispatcherManualRideConfirmed'));

Route::post('/dispatcher/request/dispatchermanualridecomplete', array('as' => 'DispatcherManualRideCompleted',
    'uses' => 'DispatcherController@DispatcherManualRideCompleted'));


Route::post('/dispatcher/checkotp', array('as' => 'CheckUserOTP', 'uses' => 'DispatcherController@CheckUserbyOTP'));
Route::post('/dispatcher/resendotp', array('as' => 'ResendOTP', 'uses' => 'DispatcherController@ResendOTP'));

Route::get('/dispatcher/myprofile', array('as' => 'DispatcherProfile', 'uses' => 'DispatcherController@MyProfile'));

Route::post('/dispatcher/update_profile', array('as' => 'UpdateDispatcherProfile', 'uses' => 'DispatcherController@UpdateDispatcherProfile'));

Route::post('/dispatcher/update_password', array('as' => 'UpdateDispatcherPassword', 'uses' => 'DispatcherController@UpdateDispatcherPassword'));

Route::post('/dispatcher/dispatchersendrideinfo', array('as'=>'DispatcherSendRideInfo', 'uses'=>'DispatcherController@DispatcherSendRideInfo'));

Route::get('/dispatcher/trainingmodule', array('as'=>'TrainingModules', 'uses'=>'DispatcherController@TrainingModules'));
Route::get('/dispatcher/trainingmodule/enrolldrivers/{id}', array('as'=>'TrainingModulesEnrollDrivers', 'uses'=>'DispatcherController@EnrollDrivers'));
Route::post('/dispatcher/trainingmodule/adddrivers', array('as'=>'AddDrivers', 'uses'=>'DispatcherController@AddDrivers'));
Route::get('/dispatcher/trainingmodule/driverlisting/{id}', array('as'=>'TrainingModulesDriverlisting', 'uses'=>'DispatcherController@DriverListing'));
Route::get('/dispatcher/trainingmodule/deletedriver/{id}/{driverid}', array('as'=>'ModulesDeleteDrivers', 'uses'=>'DispatcherController@DeleteDriver'));
//Route::post('/dispatcher/trainingmodule/uploadcsv', array('as'=>'UploadCSV', 'uses'=>'DispatcherController@UploadDriverCSV'));
Route::get('/dispatcher/registerdrivers',array('as'=>'RegisterDrivers', 'uses'=>'DispatcherController@RegisterDrivers'));
Route::post('/dispatcher/addnewdriver',array('as'=>'AddNewDriver', 'uses'=>'DispatcherController@AddNewDriver'));
Route::post('/dispatcher/addcsvdriver',array('as'=>'AddCSVDrivers', 'uses'=>'DispatcherController@AddCSVDrivers'));
Route::post('/dispatcher/ratetransportationprovider',array('as'=>'RateTransportationProvider', 'uses'=>'DispatcherController@RateTransportationProvider'));

Route::get('/dispatcher/addeditcarddetails/{id}',array('as'=>'AddEditCardDetails', 'uses'=>'DispatcherController@AddEditCardDetails'));

Route::get('/admin/dispatchers', array('as' => 'GetDispatchers', 'uses' => 'AdminController@GetDispatchers'));

Route::get('admin/dispatcher/profile/{id}',
	array(
		'as' => 	'DispatcherProfile',
		'uses' => 	'AdminController@DispatcherProfile'
	)
);

Route::post('admin/dispatcher/profile',
	array(
		'as' => 	'DispatcherProfileSave',
		'uses' => 	'AdminController@DispatcherProfileSave'
	)
);

Route::get('/admin/dispatcher/decline/{id}', array('as' => 'AdminDispatcherDecline', 'uses' => 'AdminController@decline_dispatcher'));
Route::get('/admin/dispatcher/approve/{id}', array('as' => 'AdminDispatcherApprove', 'uses' => 'AdminController@approve_dispatcher'));
Route::get('/admin/dispatcher/makeadmin/{id}', array('as' => 'MakeDispatcherAdmin', 'uses' => 'AdminController@MakeDispatcherAdmin'));
Route::get('/admin/dispatcher/removeadmin/{id}', array('as' => 'RemoveDispatcherAdmin', 'uses' => 'AdminController@RemoveDispatcherAdmin'));
// healthcare user
Route::get('/booking/request-ride', array('as' => 'request-service', 'uses' => 'EnterpriseClientController@servicerequest'));
Route::get('/booking/myrides', array('as' => 'myservices', 'uses' => 'EnterpriseClientController@myservices'));
Route::get('/booking/logout', array('as' => '/booking/logout', 'uses' => 'EnterpriseClientController@userLogout'));
Route::post('/booking/saverequest', array('as' => 'saverequest', 'uses' => 'EnterpriseClientController@saveuserrequest'));
Route::get('/booking/requests/map/{id}', array('as' => 'healthcareRequestMap', 'uses' => 'EnterpriseClientController@view_map'));
Route::get('/booking/signin', array('as' => '/booking/signin', 'uses' => 'EnterpriseClientController@userLogin'));
Route::get('/booking/signup', array('as' => '/booking/signup', 'uses' => 'EnterpriseClientController@userRegister'));
Route::post('/booking/verify', array('as' => '/booking/verify', 'uses' => 'EnterpriseClientController@userVerify'));
Route::post('/booking/forgot-password', array('as' => '/booking/forgot-password', 'uses' => 'EnterpriseClientController@healthcareForgotPassword'));
Route::post('/booking/save', array('as' => '/booking/save', 'uses' => 'EnterpriseClientController@userSave'));
Route::get('/booking/DownloadReport', array('as' => 'DownloadReport', 'uses' => 'EnterpriseClientController@DownloadReport'));
Route::post('/booking/CalculateAmount', array('as' => 'EnterpriseClientCalculateAmount', 'uses' => 'EnterpriseClientController@CalculateAmount'));
Route::post('/booking/request/cancelride', array('as' => 'cancelridehealthcare', 'uses' => 'EnterpriseClientController@cancelrideHealthcare'));
Route::post('/booking/request/healthcareridecomplete', array('as' => 'HealthcareRideCompleted', 'uses' => 'EnterpriseClientController@HealthcareRideCompleted'));
Route::post('/booking/check-healthcareassigned', array('as' => 'healthcareassigned', 'uses' => 'EnterpriseClientController@checkHealthcareassigned'));
Route::get('/booking', 'EnterpriseClientController@index');
Route::get('/booking/login', 'EnterpriseClientController@index');
Route::get('/booking/myprofile', array('as' => 'myprofile', 'uses' => 'EnterpriseClientController@MyProfile'));
Route::post('/booking/updateprofile', array('as' => 'updateprofile', 'uses' => 'EnterpriseClientController@UpdateProfile'));
Route::post('/booking/updatepassword', array('as' => 'updatepassword', 'uses' => 'EnterpriseClientController@UpdatePassword'));
Route::post('/booking/updatehospitalprovider', array('as' => 'updatehospitalprovider',
    'uses' => 'EnterpriseClientController@UpdateHospitalProvider'));
Route::post('/booking/addhospitalprovider', array('as' => 'addhospitalprovider',
    'uses' => 'EnterpriseClientController@AddHospitalProvider'));
Route::post('/booking/deletehospitalprovider', array('as' => 'deletehospitalprovider',
    'uses' => 'EnterpriseClientController@DeleteHospitalProvider'));
Route::get('/booking/viewreceipts', array('as' => 'healthcarereceipts',
    'uses' => 'EnterpriseClientController@HealthcareReceipts'));
Route::post('/booking/sendrideinfo', array('as'=>'SendRideInfo', 'uses'=>'EnterpriseClientController@SendRideInfo'));

Route::post('/booking/checkPaymentOwnerData', array('as' => 'checkpaymentownerdata', 'uses' => 'EnterpriseClientController@checkPaymentOwnerData'));
// Web User
Route::get('/', 'HomeViewController@index');
Route::get('/allstate', 'EnterpriseClientController@index');

Route::get('/healthcare/signin', 'EnterpriseClientController@index');

Route::get('/healthcare', 'EnterpriseClientController@index');

Route::post('/booking/checkotp', array('as' => 'CheckHealthcareOTP', 'uses' => 'EnterpriseClientController@CheckUserbyOTP'));
Route::post('/booking/resendotp', array('as' => 'ResendHealthcareOTP', 'uses' => 'EnterpriseClientController@ResendOTP'));

Route::get('/booking/web',
	array(
		'as' => 'BookFromWeb',
		'uses' => 'WebUserController@BookFromWeb'
	)
);

//Route::get('/booking/signin', array('as' => '/booking/signin', 'uses' => 'WebUserController@userLogin'));
Route::get('/user/signup', array('as' => '/user/signup', 'uses' => 'WebUserController@userRegister'));
Route::post('/user/save', array('as' => '/user/save', 'uses' => 'WebUserController@userSave'));
Route::post('/user/forgot-password', array('as' => '/user/forgot-password', 'uses' => 'WebUserController@userForgotPassword'));
Route::get('/user/logout', array('as' => '/user/logout', 'uses' => 'WebUserController@userLogout'));
Route::post('/user/verify', array('as' => '/user/verify', 'uses' => 'WebUserController@userVerify'));
Route::get('/user/trips', array('as' => '/user/trips', 'uses' => 'WebUserController@userTrips'));
Route::get('/user/scheduledtrips', array('as' => '/user/scheduledtrips', 'uses' => 'WebUserController@userScheduledTrips'));
Route::get('/user/deletescheduledtrips', array('as' => '/user/deletescheduledtrips', 'uses' => 'WebUserController@delete_future_request'));
Route::get('/user/trip/status/{id}', array('as' => '/user/trip/status', 'uses' => 'WebUserController@userTripStatus'));
Route::get('/user/trip/cancel/{id}', array('as' => '/user/trip/cancel', 'uses' => 'WebUserController@userTripCancel'));
Route::get('/find', array('as' => '/find', 'uses' => 'WebUserController@surroundingCars'));
Route::get('user/paybypaypal/{id}', array('as' => 'user/paybypaypal', 'uses' => 'WebUserController@webpaybypaypal'));
Route::get('user/paybypalweb/{id}', array('as' => 'user/paybypalweb', 'uses' => 'WebUserController@paybypalwebSubmit'));
Route::get('userpaypalstatus', array('as' => 'userpaypalstatus', 'uses' => 'WebUserController@paypalstatus'));
Route::get('userpaypalipn', array('as' => 'userpaypalipn', 'uses' => 'WebUserController@userpaypalipn'));
Route::get('/user/request-trip', array('as' => 'userrequestTrip', 'uses' => 'WebUserController@userRequestTrip'));
Route::get('/user/skipReview/{id}', array('as' => 'userSkipReview', 'uses' => 'WebUserController@userSkipReview'));
Route::post('/user/eta', array('as' => 'etaweb', 'uses' => 'WebUserController@send_eta_web'));
Route::get('/user/request-fare', array('as' => 'userrequestFare', 'uses' => 'WebUserController@request_fare'));
Route::get('/user/requesteta', array('as' => 'userrequestETA', 'uses' => 'WebUserController@request_eta'));
Route::post('/user/request-trip', array('as' => 'userrequesttrips', 'uses' => 'WebUserController@saveUserRequestTrip'));
Route::post('/user/post-review', array('as' => '/user/post-review', 'uses' => 'WebUserController@saveUserReview'));
Route::get('/user/profile', array('as' => '/user/profile', 'uses' => 'WebUserController@userProfile'));
Route::get('/user/payments', array('as' => 'userPayment', 'uses' => 'WebUserController@userPayments'));
Route::get('termsncondition', array('as' => 'termsncondition', 'uses' => 'WebController@termsncondition'));
Route::get('privacypolicy', array('as' => 'privacypolicy', 'uses' => 'WebController@privacypolicy'));
Route::get('banking_provider_mobile/{id}', array('as' => 'banking_provider_mobile', 'uses' => 'WebController@banking_provider_mobile'));
Route::post('provider/provider_braintree_banking', array('as' => 'ProviderBBanking', 'uses' => 'WebController@providerB_bankingSubmit'));
Route::post('provider/provider_stripe_banking', array('as' => 'ProviderSBanking', 'uses' => 'WebController@providerS_bankingSubmit'));
Route::get('page/{title}', array('as' => 'page', 'uses' => 'WebController@page'));
Route::get('track/{id}', array('as' => 'track', 'uses' => 'WebController@track_ride'));
Route::get('get_track_loc/{id}', array('as' => 'getTrackLoc', 'uses' => 'WebController@get_track_loc'));
Route::post('/user/payments', array('as' => 'userpayments', 'uses' => 'WebUserController@saveUserPayment'));
Route::get('/user/payment/delete/{id}', array('as' => '/user/payment/delete', 'uses' => 'WebUserController@deleteUserPayment'));
Route::post('/user/update_profile', array('as' => '/user/update_profile', 'uses' => 'WebUserController@updateUserProfile'));
Route::post('/user/update_password', array('as' => '/user/update_password', 'uses' => 'WebUserController@updateUserPassword'));
Route::post('/user/update_code', array('as' => '/user/update_code', 'uses' => 'WebUserController@updateUserCode'));
Route::get('/user/trip/{id}', array('as' => '/user/trip', 'uses' => 'WebUserController@userTripDetail'));
// Search Admin Panel
Route::get('/admin/searchpv', array('as' => '/admin/searchpv', 'uses' => 'AdminController@searchpv'));
Route::get('/admin/searchur', array('as' => '/admin/searchur', 'uses' => 'AdminController@searchur'));
Route::get('/admin/searchreq', array('as' => '/admin/searchreq', 'uses' => 'AdminController@searchreq'));
Route::get('/admin/searchrev', array('as' => '/admin/searchrev', 'uses' => 'AdminController@searchrev'));
Route::get('/admin/searchinfo', array('as' => '/admin/searchinfo', 'uses' => 'AdminController@searchinfo'));
Route::get('/admin/searchpvtype', array('as' => '/admin/searchpvtype', 'uses' => 'AdminController@searchpvtype'));
Route::get('/admin/searchdoc', array('as' => '/admin/searchdoc', 'uses' => 'AdminController@searchdoc'));
Route::get('/admin/searchpromo', array('as' => '/admin/searchpromo', 'uses' => 'AdminController@searchpromo'));
// Web Provider
Route::get('/provider/signin', array(
    'as' => 'ProviderSignin',
    'uses' => 'WebProviderController@providerLogin'
));
Route::get('/provider/activation/{act}', array(
    'as' => '/provider/activation',
    'uses' => 'WebProviderController@providerActivation'
));
Route::get('/provider/signup', array(
    'as' => 'ProviderSignup',
    'uses' => 'WebProviderController@providerRegister'
));

Route::post('/provider/save', array(
    'as' => 'ProviderSave',
    'uses' => 'WebProviderController@providerSave'
));

Route::get('/provider/availability', array(
    'as' => 'ProviderAvail',
    'uses' => 'WebProviderController@provideravailability'
));
Route::post('/provider/availabilitysubmit', array(
    'as' => 'provideravailabilitySubmit',
    'uses' => 'WebProviderController@provideravailabilitysubmit'
));

Route::post('/provider/forgot-password', array(
    'as' => 'providerForgotPassword',
    'uses' => 'WebProviderController@providerForgotPassword'));
Route::get('/provider/logout', array(
    'as' => 'ProviderLogout',
    'uses' => 'WebProviderController@providerLogout'
));
Route::post('/provider/verify', array(
    'as' => 'ProviderVerify',
    'uses' => 'WebProviderController@providerVerify'
));
Route::get('/provider/trips', array(
    'as' => 'ProviderTrips',
    'uses' => 'WebProviderController@providerTrips'
));
Route::get('/provider/requests_payment', array('as' => 'ProviderRequest_payment', 'uses' => 'WebProviderController@walks_payment'));
Route::get('/provider/providers_payout', array('as' => 'ProviderProviderpay', 'uses' => 'WebProviderController@walkers_payout'));
Route::get('/provider/trip/{id}', array(
    'as' => 'ProviderTripDetail',
    'uses' => 'WebProviderController@providerTripDetail'
));
Route::get('/provider/trip/changestate/{id}', array(
    'as' => 'providerTripChangeState',
    'uses' => 'WebProviderController@providerTripChangeState'
));
Route::get('/provider/tripinprogress', array(
    'as' => 'providerTripInProgress',
    'uses' => 'WebProviderController@providerTripInProgress'));
Route::get('/provider/skipReview', array(
    'as' => 'providerSkipReview',
    'uses' => 'WebProviderController@providerSkipReview'));
Route::get('/provider/profile', array(
    'as' => 'providerProfile',
    'uses' => 'WebProviderController@providerProfile'));
Route::post('/provider/update_profile', array(
    'as' => 'updateProviderProfile',
    'uses' => 'WebProviderController@updateProviderProfile'));
Route::post('/provider/update_password', array(
    'as' => 'updateProviderPassword',
    'uses' => 'WebProviderController@updateProviderPassword'));
Route::get('/provider/documents', array(
    'as' => 'providerDocuments',
    'uses' => 'WebProviderController@providerDocuments'));
Route::post('/provider/update_documents', array(
    'as' => 'providerUpdateDocuments',
    'uses' => 'WebProviderController@providerUpdateDocuments'));
Route::get('/provider/request', array(
    'as' => 'providerRequestPing',
    'uses' => 'WebProviderController@providerRequestPing'));
Route::post('user/request', array('as' => 'manualrequest', 'uses' => 'WebProviderController@create_manual_request'));
Route::get('/provider/request/decline/{id}', 'WebProviderController@decline_request');
Route::get('/provider/request/accept/{id}', 'WebProviderController@approve_request');
Route::post('provider/get-nearby', array('as' => 'nearby', 'uses' => 'WebProviderController@get_nearby'));
Route::any('/provider/availability/toggle', array(
    'as' => 'toggle_availability',
    'uses' => 'WebProviderController@toggle_availability'));
//Route::any('/provider/location/set', array(
// 		'as' => 'providerLocation',
//		'uses' =>'WebProviderController@set_location'));
Route::any('/provider/location/set', 'WebProviderController@set_location');

Route::get('/consumer/signup', array(
    'as' => 'ConsumerSignup',
    'uses' => 'ConsumerController@ConsumerRegister'
));

Route::post('/consumer/save', array(
    'as' => 'ConsumerSave',
    'uses' => 'ConsumerController@ConsumerSave'
));

Route::get('/consumer/signin', array(
    'as' => 'ConsumerSignin',
    'uses' => 'ConsumerController@ConsumerLogin'
));

Route::get('/consumer/logout', array(
    'as' => 'ConsumerLogout',
    'uses' => 'ConsumerController@ConsumerLogout'
));
Route::post('/consumer/verify', array(
    'as' => 'ConsumerVerify',
    'uses' => 'ConsumerController@ConsumerVerify'
));

Route::post('/consumer/forgot-password', array(
    'as' => 'ConsumerForgotPassword',
    'uses' => 'ConsumerController@ConsumerForgotPassword'));

Route::get('/consumer/myprofile', array(
    'as' => 'ConsumerProfile',
    'uses' => 'ConsumerController@MyProfile'));

Route::post('/consumer/update_profile', array(
    'as' => 'UpdateConsumerProfile',
    'uses' => 'ConsumerController@UpdateConsumerProfile'));

Route::post('/consumer/update_password', array(
    'as' => 'UpdateConsumerPassword',
    'uses' => 'ConsumerController@UpdateConsumerPassword'));


// Installer
//Route::any('/install', 'InstallerController@install');
Route::get('learningcenter/learning', array('as' => 'DriverLearningCenter', 'uses' => 'LearningCenterController@DriverLearningCenter'));
Route::get('learningcenter/section/{id}', array('as' => 'Sections', 'uses' => 'LearningCenterController@Sections'));
Route::get('learningcenter/content/{id}/{sectionid}', array('as' => 'Content', 'uses' => 'LearningCenterController@Contents'));
Route::get('learningcenter/content_details/{categoryiid}/{sectioniid}/{contentiid}', array('as' => 'ContentDetails', 'uses' => 'LearningCenterController@ContentDetails'));
Route::get('learningcenter/test/{id}/{content_id}/{category_id}', array('as' => 'ProviderTest', 'uses' => 'LearningCenterController@ProviderTest'));
Route::post('learningcenter/answer_result',array('as'=>'SendResults', 'uses'=>'LearningCenterController@AddParticipantsAnswers'));
Route::get('learningcenter/participant_result',array('as'=>'ParticipantResults', 'uses'=>'LearningCenterController@ProviderResults'));
Route::post('learningcenter/get_section_details',array('as'=>'GetSectionDetails','uses'=>'LearningCenterController@GetSectionDetails'));
Route::post('learningcenter/add_edit_section',array('as'=>'AddEditSection','uses'=>'LearningCenterController@AddEditSection'));
Route::post('learningcenter/delete_section',array('as'=>'SectionDelete','uses'=>'LearningCenterController@SectionDelete'));
Route::post('learningcenter/get_contents',array('as'=>'GetContents','uses'=>'LearningCenterController@GetContents'));
Route::post('learningcenter/add_edit_content',array('as'=>'AddEditContent','uses'=>'LearningCenterController@AddEditContent'));
Route::post('learningcenter/delete_content',array('as'=>'ContentDelete','uses'=>'LearningCenterController@ContentDelete'));
Route::post('learningcenter/add_edit_content_titles',array('as'=>'AddEditTextSection','uses'=>'LearningCenterController@AddEditTextSection'));
Route::post('learningcenter/get_title_details',array('as'=>'GetTitleDetails','uses'=>'LearningCenterController@GetTitleDetails'));
Route::post('learningcenter/delete_content_section',array('as'=>'ContentSectionDelete','uses'=>'LearningCenterController@ContentSectionDelete'));
Route::get('learningcenter/add_edit_quiz_selection/{id}',array('as'=>'AddEditQuizSelection','uses'=>'LearningCenterController@AddEditQuizSelection'));
Route::get('learningcenter/add_edit_quiz/{id}/{quizid}',array('as'=>'AddEditQuiz','uses'=>'LearningCenterController@AddEditQuiz'));
Route::post('learningcenter/add_new_quiz',array('as'=>'AddNewQuiz','uses'=>'LearningCenterController@AddNewQuiz'));
Route::post('learningcenter/add_edit_video_titles',array('as'=>'AddEditVideoSection','uses'=>'LearningCenterController@AddEditVideoSection'));
Route::post('learningcenter/add_edit_image_section',array('as'=>'AddEditImageSection','uses'=>'LearningCenterController@AddEditImageSection'));
Route::post('learningcenter/delete_content_video',array('as'=>'ContentVideoDelete','uses'=>'LearningCenterController@ContentVideoDelete'));
Route::post('learningcenter/delete_content_image',array('as'=>'ContentImageDelete','uses'=>'LearningCenterController@ContentImageDelete'));
Route::post('learningcenter/add_quiz_question_answer',array('as'=>'AddQuizQuestionAnswer','uses'=>'LearningCenterController@AddQuizQuestionAnswer'));
Route::post('learningcenter/edit_quiz/{id}/{quiz_id}',array('as'=>'DisplayQuiz','uses'=>'LearningCenterController@DisplayQuiz'));
Route::post('learningcenter/save_new_data/',array('as'=>'SaveUpdatedQuiz','uses'=>'LearningCenterController@SaveUpdatedQuiz'));
Route::post('learningcenter/delete_quiz_question_answer/',array('as'=>'DeleteQuestionAnswer','uses'=>'LearningCenterController@DeleteQuestionAnswer'));
Route::post('learningcenter/delete_quiz/',array('as'=>'DeleteQuiz','uses'=>'LearningCenterController@DeleteQuiz'));
Route::post('learningcenter/add_edit_doc_section',array('as'=>'AddEditDocumentSection','uses'=>'LearningCenterController@AddEditDocumentSection'));
Route::post('learningcenter/delete_content_doc',array('as'=>'ContentDocumentDelete','uses'=>'LearningCenterController@ContentDocumentDelete'));
Route::post('learningcenter/check_question',array('as'=>'CheckQuestion','uses'=>'LearningCenterController@CheckQuestion'));
Route::get('learningcenter/learningstep1', array('as' => 'LearningStep1', 'uses' => 'LearningCenterController@LearningStep1'));
Route::get('learningcenter/moduleerror',array('as'=>'ModuleError','uses'=>'LearningCenterController@ModuleError'));


Route::any('install', array('as' => 'install', 'uses' => 'InstallerController@install'))->before('new_installation');
Route::get('/install/complete', 'InstallerController@finish_install');
Route::post('user/fare', 'CustomerController@fare_calculator');
Route::get('token_braintree', array('as' => 'token_braintree', 'uses' => 'ApplicationController@token_braintree'));
Route::post('/user/addevent', 'CustomerController@user_create_event');
Route::post('/user/getevents', 'CustomerController@user_get_event');
Route::post('/user/deleteevents', 'CustomerController@user_delete_event');
Route::post('/user/invitemembers', 'CustomerController@invite_members');
Route::get('/dispatcher', 'DispatcherController@index');
Route::get('/provider', 'WebProviderController@index');

Route::get('/consumer', 'ConsumerController@index');

//Route::get('/openapi/ride/create_new_ride_request',array('as'=>'createriderequest','uses'=>'RideController@CreateNewRideRequest'));

Route::group(['prefix' => 'ride', 'before' =>'oauth'], function(){
    Route::any('create_request', array('uses'=>'RideController@CreateNewRideRequest'));
    Route::any('cancel_ride', array('uses'=>'RideController@CancelRideRequest'));
    Route::any('ride_details', array('uses'=>'RideController@GetRideInfo'));
});
Route::get('/api', 'ApiDocumentationController@index');
Route::get('/api/reference', 'ApiDocumentationController@index');

Route::post('authenticate/access_token', 'OAuthController@postAccessToken');

Route::post('/booking/save-ownerpayments', array('as' => 'save-ownerpayments', 'uses' => 'EnterpriseClientController@saveownerpayments'));

Route::post('/booking/checkownerdata', array('as' => 'checkPaymentOwnerData', 'uses' => 'EnterpriseClientController@checkPaymentOwnerData'));

Route::post('/booking/updateownercard', array('as' => 'updatedefaultOwnerpaymentcard', 'uses' => 'EnterpriseClientController@updatedefaultOwnerpaymentcard'));

Route::get('/driverlocation/map/{id}', array('as' => 'DriverLocationMap', 'uses' => 'DriverLocationController@ViewMap'));

Route::post('driverlocation/walker_location', array('as'=>'getWalkerLocation', 'uses'=>'DriverLocationController@GetWalkerLocation'));

Route::post('dispatcher/notification/',array('as'=>'Notification','uses'=>'DispatcherController@Notification'));

// Display all SQL executed in Eloquent
Event::listen('illuminate.query', function($query)
{
   //var_dump($query);
});

