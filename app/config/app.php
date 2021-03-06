<?php
error_reporting(E_ALL ^ E_DEPRECATED);
return array(

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => TRUE,

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => 'localhost',

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => 'enR8R5l0xpms9RCeCFJotGUwJM0a8hyZ',

    'cipher' => MCRYPT_RIJNDAEL_128,

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => array(

        'Illuminate\Foundation\Providers\ArtisanServiceProvider',
        'Illuminate\Auth\AuthServiceProvider',
        'Illuminate\Cache\CacheServiceProvider',
        'Illuminate\Session\CommandsServiceProvider',
        'Illuminate\Foundation\Providers\ConsoleSupportServiceProvider',
        'Illuminate\Routing\ControllerServiceProvider',
        'Illuminate\Cookie\CookieServiceProvider',
        'Illuminate\Database\DatabaseServiceProvider',
        'Illuminate\Encryption\EncryptionServiceProvider',
        'Illuminate\Filesystem\FilesystemServiceProvider',
        'Illuminate\Hashing\HashServiceProvider',
        'Illuminate\Html\HtmlServiceProvider',
        'Illuminate\Log\LogServiceProvider',
        'Illuminate\Mail\MailServiceProvider',
        'Illuminate\Database\MigrationServiceProvider',
        'Illuminate\Pagination\PaginationServiceProvider',
        'Illuminate\Queue\QueueServiceProvider',
        'Illuminate\Redis\RedisServiceProvider',
        'Illuminate\Remote\RemoteServiceProvider',
        'Illuminate\Auth\Reminders\ReminderServiceProvider',
        'Illuminate\Database\SeedServiceProvider',
        'Illuminate\Session\SessionServiceProvider',
        'Illuminate\Translation\TranslationServiceProvider',
        'Illuminate\Validation\ValidationServiceProvider',
        'Illuminate\View\ViewServiceProvider',
        'Illuminate\Workbench\WorkbenchServiceProvider',
        'Aws\Laravel\AwsServiceProvider',
        'Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider',
        'Way\Generators\GeneratorsServiceProvider',
        'Raahul\LarryFour\LarryFourServiceProvider',
        'Davibennun\LaravelPushNotification\LaravelPushNotificationServiceProvider',
        'Intervention\Image\ImageServiceProvider',
        'Barryvdh\DomPDF\ServiceProvider',
        'LucaDegasperi\OAuth2Server\Storage\FluentStorageServiceProvider',
        'LucaDegasperi\OAuth2Server\OAuth2ServerServiceProvider'
    ),

    /*
    |--------------------------------------------------------------------------
    | Service Provider Manifest
    |--------------------------------------------------------------------------
    |
    | The service provider manifest is used by Laravel to lazy load service
    | providers which are not needed for each request, as well to keep a
    | list of all of the services. Here, you may set its storage spot.
    |
    */

    'manifest' => storage_path().'/meta',

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are lazy loaded so they don't hinder performance.
    |
    */

    'aliases' => array(

        'App'               => 'Illuminate\Support\Facades\App',
        'Artisan'           => 'Illuminate\Support\Facades\Artisan',
        'Auth'              => 'Illuminate\Support\Facades\Auth',
        'Blade'             => 'Illuminate\Support\Facades\Blade',
        'Cache'             => 'Illuminate\Support\Facades\Cache',
        'ClassLoader'       => 'Illuminate\Support\ClassLoader',
        'Config'            => 'Illuminate\Support\Facades\Config',
        'Controller'        => 'Illuminate\Routing\Controller',
        'Cookie'            => 'Illuminate\Support\Facades\Cookie',
        'Crypt'             => 'Illuminate\Support\Facades\Crypt',
        'DB'                => 'Illuminate\Support\Facades\DB',
        'Eloquent'          => 'Illuminate\Database\Eloquent\Model',
        'Event'             => 'Illuminate\Support\Facades\Event',
        'File'              => 'Illuminate\Support\Facades\File',
        'Form'              => 'Illuminate\Support\Facades\Form',
        'Hash'              => 'Illuminate\Support\Facades\Hash',
        'HTML'              => 'Illuminate\Support\Facades\HTML',
        'Input'             => 'Illuminate\Support\Facades\Input',
        'Lang'              => 'Illuminate\Support\Facades\Lang',
        'Log'               => 'Illuminate\Support\Facades\Log',
        'Mail'              => 'Illuminate\Support\Facades\Mail',
        'Paginator'         => 'Illuminate\Support\Facades\Paginator',
        'Password'          => 'Illuminate\Support\Facades\Password',
        'Queue'             => 'Illuminate\Support\Facades\Queue',
        'Redirect'          => 'Illuminate\Support\Facades\Redirect',
        'Redis'             => 'Illuminate\Support\Facades\Redis',
        'Request'           => 'Illuminate\Support\Facades\Request',
        'Response'          => 'Illuminate\Support\Facades\Response',
        'Route'             => 'Illuminate\Support\Facades\Route',
        'Schema'            => 'Illuminate\Support\Facades\Schema',
        'Seeder'            => 'Illuminate\Database\Seeder',
        'Session'           => 'Illuminate\Support\Facades\Session',
        'SoftDeletingTrait' => 'Illuminate\Database\Eloquent\SoftDeletingTrait',
        'SSH'               => 'Illuminate\Support\Facades\SSH',
        'Str'               => 'Illuminate\Support\Str',
        'URL'               => 'Illuminate\Support\Facades\URL',
        'Validator'         => 'Illuminate\Support\Facades\Validator',
        'View'              => 'Illuminate\Support\Facades\View',
        'AWS' => 'Aws\Laravel\AwsFacade',
        'PushNotification' => 'Davibennun\LaravelPushNotification\Facades\PushNotification',
        'Image' => 'Intervention\Image\Facades\Image',
        'PDF' => 'Barryvdh\DomPDF\Facade',
		'PaymentServices'   => 'helpers\PaymentServices',
        'RideServices'      => 'helpers\RideServices',
        'ValidationServices' => 'helpers\ValidationServices',
        'Authorizer' => 'LucaDegasperi\OAuth2Server\Facades\AuthorizerFacade'
    ),
    'menu_titles' => array(
        'admin_control' => 'Admin Control',
        'income_history' => 'Income History',
        'log_out' => 'Log Out',
        'dashboard' => 'Dashboard',
        'map_view' => 'Map View',
        'providers' => 'Providers',
        'requests' => 'Requests',
        'customers' => 'Customers',
        'reviews' => 'Reviews',
        'information' => 'Information',
        'types' => 'Types',
        'documents' => 'Documents',
        'settings' => 'Settings',
        'balance' => 'Balance',
        'create_request' => 'Create Request',
        'promotional_codes' => 'Promotional Codes',
    ),
    'generic_keywords'=> array(
        'Provider' => 'Service Provider',
        'User' => 'User',
        'Services' => 'Transport',
        'Trip' => 'Trip',
        'Currency' => '$',
        'total_trip' => '1',
        'cancelled_trip' => '3',
        'total_payment' => '5',
        'completed_trip' => '4',
        'card_payment' => '6',
        'credit_payment' => '2',
        'cash_payment' => '5',
        'promotional_payment' => '35',
        'schedules_icon' => '36',
    ),
    /* DEVICE PUSH NOTIFICATION DETAILS */
    'customer_certy_url' => 'https://192.168.0.89/uber_schedule/api/public/apps/ios_push/iph_cert/Client_certy.pem',
    'customer_certy_pass' => '123456',
    'customer_certy_type' => '1',
    'provider_certy_url' => 'https://192.168.0.89/uber_schedule/api/public/apps/ios_push/walker/iph_cert/Walker_certy.pem',
    'provider_certy_pass' => '123456',
    'provider_certy_type' => '1',
    'gcm_browser_key' => 'AIzaSyAKe3XmUV93WvHJvII4Qzpf0R052mxb0KI',
    /* DEVICE PUSH NOTIFICATION DETAILS END */
    'currency_symb' => '$', 
    
    /* Developer Company Details */
    'developer_company_name' => 'ButterFLi Inc',
    'developer_company_web_link' => 'https://www.gobutterfli.com', 
    'developer_company_email' => 'developer@gobutterfli.com',
    'developer_company_fb_link' => 'https://www.facebook.com/gobutterfli', 
    'developer_company_twitter_link' => 'https://twitter.com/gobutterfli',
    /* Developer Company Details END */
    
    /* APP LINK DATA */
    'android_client_app_url'=>'https://www.google.com',
    'android_provider_app_url'=>'https://www.apple.com',
    'ios_client_app_url'=>'https://www.apple.com',
    'ios_provider_app_url'=>'https://www.google.com',
    /* APP LINK DATA END */
    
    'google_maps_server_key' => 'AIzaSyBRpT5YM5zkopiPlvtK2E-uDCthDIzB_Jk',
    'google_maps_client_key' => 'AIzaSyDaKg4PG8MB0N-XfDnVU8mOXDKSmrSo-iI',

    'no_data_available' => 'History not availalbe.', 
    'data_not_available' => 'Data not availalbe.', 
    'blank_fiend_val' => 'N/A',

    'website_title' => 'ButterFLi Panel',
    'welcome_title' => 'Welcome to ButterFli',
    'referral_prefix' => 'TNN',
    'datenow'=>'Y-m-d H:i:s',
    'appdate'=>'d-m-Y 23:59:59',
    'referral_zero_len' => 10,
    'website_meta_description' => '',
    'website_meta_keywords' => '',

    's3_bucket' => 'butterfli-bucket',

    'twillo_account_sid' => 'ACd45e9169dc14ea5eb0b8e7491e066368',
    'twillo_auth_token' => '72d6122e7cfe062938d6d4ddbe8c5406',
    'twillo_number' => '18556132354',

    'production' => false,

    'default_payment' => 'stripe',

	//'stripe_secret_key' => 'sk_test_wEP1Or9xZdFRXT9x2o9T6EDn',
	//'stripe_publishable_key'=>'pk_test_ZWy3dscGrlCewiIWMOmOgZiX',
    'stripe_secret_key' => 'sk_live_Jp7xyrCbwQzhy07UMEayL6Gs',
    'stripe_publishable_key' => 'pk_live_yZAFi4s35NGcu9e4E9hHrh4S',
    'braintree_environment' => '',
    'braintree_merchant_id' => '',
    'braintree_public_key' => '',
    'braintree_private_key' => '',
    'braintree_cse' => '',
        
    'coinbaseAPIKey' => 'g0xKQqKRwNj84IW9',
    'coinbaseAPISecret' => 'iEHiMMeUXWEGHV02M3lcxt8evBPaOzlC',

    'paypal_sdk_mode' => 'sandbox',
    'paypal_sdk_UserName' => 'npavankumar34-buyer_api1.gmail.com',
    'paypal_sdk_Password' => 'WUUPVM3ETSJ6CARS',
    'paypal_sdk_Signature' => 'AnIGq3pWk8Gb1yRu1ZjCY0N3ccikAdq-3A6AHjDQPytHJVE2N4d6jeWH',
    'paypal_sdk_AppId' => 'APP-80W284485P519543T',

);
