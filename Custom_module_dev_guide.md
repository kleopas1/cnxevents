FreeScout Modules Development Guide




Sample Modules        2

Module Naming        2

Step-by-Step Instruction        2

Module Settings        3

Actions and Filters        4

JavaScript and Content Security Policy (CSP)        5

JavaScript Localization and PHP Variables        5

Routes        5

Public Assets        6

Extra service providers        6

Adding Methods to Models        7

Validation        7

Storing Custom Data for Mailboxes, Customers and Threads        8

Cache        8

Curl Requests        8

Console Commands        8

Including Packages        9

Module Composer Dependencies        9

Packages Configs        10

Packages Views        10

Updating        10

Troubleshooting        11



Modules allow to extend application functionality (just like WordPress plugins). Modules are developed using Laravel-Modules v2 package (documentation).


When developing modules make sure to use pure PHP, no NodeJS. Also no using VueJS.


Sample Modules
Sample FreeScout module is available here.


Also there are Community modules to check here and Official modules here.

Module Naming
Module folder name must be in a singular form (for example, TelegramNotification), it never changes and must be unique.
When developing custom modules it is strongly recommended to use some prefix (for example your company name, nickname or any other unique string to avoid collisions): php artisan module:make PrefixSampleModule. Official modules do not use prefixes.
Module alias in module.json is the identifier of the module, it must be equal to the module folder name, lowercase, it never changes and must be unique, no spaces allowed.
No slashes “/” allowed in module name.
Module name in module.json can be any unique text, it can be changed in future, no quotes allowed.
To search modules in FreeScout use only \Module::findByAlias('modulealias') function, as other functions like Module::find('Name') are searching modules by name which may change.
In php artisan module:... commands use module name, not alias (for example, "Sample Module").


To check in the main app if module is active use:

\Module::isActive('alias')

Step-by-Step Instruction
1. Generate module files in /Modules folder:

php artisan module:make SampleModule


where SampleModule will be the name of the module folder and module's alias.

2. Change parameters in module.json:

You can set any name as you wish. “active” parameter is not taken into account by the app, modules active flag is stored in DB (“modules” table).


To set custom icon:

"img": “https://example.org/custom-icon.jpg”


To set custom icon from external location:

        "img": “https://example.org/custom-icon.jpg”


To set custom icon from within the module directory (use all lowercase and and make sure to place the icon in your modules Public directory - /Modules/MyModuleName/Public/img/my-module-icon.png):

        "img": "/modules/samplemodule/img/my-module-icon.png"


To require another module:

"requiredModules": {"tags": "1.0.4"},


To allow updating the module from FreeScout’s web interface (the example below supposes that your module is hosted on GitHub):

"latestVersionUrl": "https://raw.githubusercontent.com/presswizards/FreeScoutGPT/refs/heads/main/module.json",

"latestVersionZipUrl": "https://github.com/presswizards/FreeScoutGPT/archive/refs/heads/main.zip"


3. In the generated server provider create a constant containing the alias of your module to use it in your module:

define('SAMPLE_MODULE', 'samplemodule');


You can give any name to your constant, just make sure that it is unique and that it is ending with _MODULE:


define('TELEGRAM_NOTIFICATION_MODULE', 'telegramnotification');

define('TN_MODULE', 'telegramnotification');


This constant can be used anywhere: in module controllers, views, etc.


4. Activate your module in Manage > Modules.

5. Develop the module.


Module Settings
Options must be prefixed with module alias plus dot. If in some modules you see “_” as a separator (for example, samplemodule_title_max_length) - this is done by mistake.


Get module option:

\Option::get('samplemodule.title_max_length')


Set module option:

\Option::set('samplemodule.title_max_length', 255);


Default module settings can be set in module's Config/config.php:

'options' => [

    'title_max_length' => ['default' => 90],

],


Notice, that options names are without prefixes in the Config/config.php.

Actions and Filters
Modules interact with the application via actions & filters (read more). Default priority is 20. The lower the number, the earlier the execution.


If you need to add some action/filter to the application, just create a pull request on GitHub.


If you need to adjust some existing module - see this.


Fire action:

Eventy::action('sample.action', 'awesome');


in blade:

@action('sample.action', 'awesome')


In the name of a hook it’s preferable to use Module’s alias when needed: samplemodule.action_name


Process action:

Eventy::addAction('sample.action', function($what) {

    echo 'You are '. $what;

}, 10, 1);


\Eventy::addAction('sample.action', [$this, 'processAction']);


Run filter:

$value = Eventy::filter('sample.filter', 'awesome');


in blade:

@filter('sample.filter', 'awesome')


Process filter:

Eventy::addFilter('sample.filter', function($what) {

    $what = 'not '. $what;

    return $what;

}, 10, 1);


Filters always return the first parameter they receive.


JavaScript and Content Security Policy (CSP)
See instructions here: https://github.com/freescout-helpdesk/freescout/wiki/Development-Guide#javascript-and-content-security-policy-csp 

JavaScript Localization and PHP Variables
1. Add strings or variables to /Resources/views/js/vars.blade.php

or add to the module’s Service Provider:


        \Eventy::addAction('js.lang.messages', function() {

            ?>

                "samplemodule_text": "<?php echo __("Test") ?>",

<?php

        });

2. Run php artisan freescout:module-build

Retrieving localized strings in JS:

Lang.get('messages.hello_world');

Lang.get('messages.hello_world', { name: 'Joe' });


Retrieving variables in JS:

alert(Vars.hello_world);


Routes
Routes configuration is located in /Modules/ModuleName/Http/routes.php


Routes must have the following prefix in order the app to able to work in a subdirectory (added automatically on module generation):

'prefix' => \Helper::getSubdirectory()


In order to have access to the route in JS, add laroute => true to the route:


Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\SampleModule\Http\Controllers'], function() {

     Route::get('/{id}', ['uses' => 'SampleModuleController@index', 'laroute' => true])->name('samplemodule_index');

});


Using in JS:

laroute.route('samplemodule_index', {id: 7'});


Run:

php artisan freescout:module-build


In module’s service provider:


        \Eventy::addFilter('javascripts', function($javascripts) {

            $javascripts[] = \Module::getPublicPath(SAMPLE_MODULE).'/js/laroute.js';

            return $javascripts;

        });


Public Assets
Module's public files can be added to the application in the module's service provider:


// Add module's css file to the application layout

\Eventy::addFilter('stylesheets', function($value) {

$styles[] = \Module::getPublicPath(SAMPLE_MODULE).'/css/module.css';

      return $styles;

});


// Add module's JS file to the application layout

\Eventy::addFilter('javascripts', function($value) {

$javascripts[] = \Module::getPublicPath(SAMPLE_MODULE).'/js/laroute.js';

$javascripts[] = \Module::getPublicPath(SAMPLE_MODULE).'/js/module.js';

           return $javascripts;

});


To add an asset in the template without merging it into build.js:


<script src="{{ asset(\Module::getPublicPath(SAMPLE_MODULE).'/js/highcharts.js') }}"></script>


Extra service providers
module.json:

   "providers": [

        "Modules\\SampleModule\\Providers\\ExtraProvider"

    ],


Service provider from module's vendor:

   "providers": [

        "\\Service\\Provider\\PathProvider"

    ],


and add to the main service provider:


// It has to be included here to require vendor service providers in module.json

require_once __DIR__.'/../vendor/autoload.php';


Adding Methods to Models

Add to the module’s service provider boot() method:


\MacroableModels::addMacro(\App\User::class, ‘saId', function() {

return $this->id;

});


Validation
Service provider:

        \Eventy::addFilter('settings.section_params', function($params, $section) {


            // Validation.

            $params['validator_rules'] = [

                'settings.woocommerce\.url' => 'required|url,

            ];


            return $params;

        }, 20, 2);


Settings template:

<div class="form-group{{ $errors->has('settings.woocommerce->url') ? ' has-error' : '' }}">

        <label class="col-sm-2 control-label">{{ __('Store URL') }}</label>


        <div class="col-sm-6">

                <input type="url" class="form-control input-sized-lg" name="settings[woocommerce.url]" value="{{ old('settings') ? old('settings')['woocommerce.url'] : $settings['woocommerce.url'] }}">


                @include('partials/field_error', ['field'=>'settings.woocommerce->url'])

        </div>

</div>

Storing Custom Data for Mailboxes, Customers and Threads
Custom data for Mailboxes, Customers and Threads can be stored in meta fields:


Set data:

$mailbox->setMetaParam('eup', $meta_settings);

or

$mailbox->setMetaParam('eup.test_value', $value);


Get data: $meta_settings = $mailbox->meta['eup'] ?? []`

Cache
Use underscore to store module data in cache.


\SampleModule::put(‘sample_module.data’, ‘data’, now()->addHours(1));

\SampleModule::get(‘sample_module.data’);

\SampleModule::forget(‘sample_module.data’);


Curl Requests
If you module if performing curl requests to external resources make sure to set timeout and proxy like this:


        curl_setopt($ch, CURLOPT_TIMEOUT, config('app.curl_timeout'));

        curl_setopt($ch, CURLOPT_PROXY, config('app.proxy'));


…or with Guzzle:


        $client = new \GuzzleHttp\Client();

        $client->request('POST', $url, [

                'form_params' => $params,

                'timeout' => config('app.curl_timeout'), // add this

                'connect_timeout' => onfig('app.curl_'connect_timeout' '), // this

                'proxy' => config('app.proxy'), // and this

        ]);


Console Commands
Add to service provider the following function:


   public function registerCommands()

    {

        $this->commands([

            \Modules\SampleModule\Console\CommandName::class

        ]);

    }


And to the boot() method:


$this->registerCommands();

Including Packages
Module Composer Dependencies

Module may include composer packages in it's composer.json:

   "require": {

        "rivsen/hello-world": "0.1.0"

    }


cd /Modules/SampleModule

composer update


Module's packages are stored in the it's vendor folder, committed and distributed with the module.


If some package requires a package which is already loaded in the main composer.json, just ignore it like this:

   "replace": {

        "laravel/framework": "*"

    }


Create .gitignore file in the root of your module with the following content (to ignore .git folders inside module's vendor directory):

/vendor/**/.git


Include autoload in module’s Service Provider:

require_once __DIR__.'/../vendor/autoload.php';


Now you can access added classes as usually:

\Rivsen\Demo\Hello


ATTENTION: Do not run Laravel-Modules's module:update command, it will add requirements from module's composer to the main FreeScout composer.json, which is not allowed.


To enable package's service provider add it to module.json:

"providers": [

    "\\Package\\PackageServiceProvider",

],

"aliases": {

    "Package": "\\Package\\Laravel\\Facades\\Package"

},


Packages Configs
To configure third party packages’ configs copy package config files into module's /Config folder.


Add to registerConfig():

$this->mergeConfigFrom(__DIR__ . '/../Config/package.php', 'package');


or

public function overrideConfigs()

{

        config([

            'package_config.param' => false

        ]);

}


Sometimes you need to merge some provider’s config too:

        $this->mergeConfigFrom(

            __DIR__.'/../Config/telegram.php', 'telegram'

        );


Packages Views
Perform “php artisan vendor:publish” and copy views into /Moules/Module/views/vendor/ and add to the registerViews():


        // ApiDocs vendor.

        $this->loadViewsFrom(array_merge(array_map(function ($path) {

            return $path . '/modules/apidoc';

        }, \Config::get('view.paths')), [__DIR__.'/../Resources/views/vendor/apidoc']), 'apidoc');




Updating
Updating to a newer version via web interface is possible only for official modules. Custom modules can be updated only manually.

Troubleshooting
If module's service provider is not found, the module is automatically deactivated and floating flash message is shown to the admin. Other exceptions are processed normally.

