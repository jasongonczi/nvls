<?php

namespace Pterodactyl\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class BillingModule extends Command
{

  protected $signature = 'billing:install {ver=stable} {lic_key?} {ver_num=latest}';
  protected $description = 'Installs the Billing Module for Pterodactyl';
  private $install = [];

  public function __construct()
  {
    parent::__construct();
    $this->app = parse_url(config('app.url'))['host'];
    $this->url = 'https://vertisanpro.com/api/license';
  }

  public function handle()
  {
    $this->prepareArgs($this->arguments());
    switch ($this->argument('ver')) {
      case 'help':
        $this->help();
        break;
      case 'debug':
        $this->debug();
        break;
      case 'update':
        $this->update();
        break;
      default:
        $this->install();
        break;
    }
  }

  private function install()
  {
    $this->infoNewLine("
       ======================================
       |||  Billing Module Installer      |||
       |||          By Gigabait & Mubeen  |||
       ======================================");

    if (!isset($this->install['lic_key'])) {
      $lic_key = $this->ask("Please enter a license key.");
      $this->install['lic_key'] = $lic_key;
    }

    $this->infoNewLine("Your license key is: {$this->install['lic_key']}");

    $req = $this->req("{$this->url}/{$this->install['lic_key']}");
    $this->reqOut($req);
    $this->prepare($req->args);


    $req = $this->req($this->install['url']);
    $this->reqOut($req, false);

    if ($this->install['lic_key'] == $req->license_key && $req->status == "active") {

      $this->setApp();

      $req = $this->req($this->install['url']);
      $this->reqOut($req);

      $this->debug();
      $this->infoNewLine("
         =+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+
            Thank you for purchasing the Billing Module
            Automatic Installation Started, This can take a few minutes!.
         =+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+");

      $this->shell("wget -O BillingModule.zip \"{$this->install['url']}/true\" -o /dev/null");
      $this->shell('unzip -o BillingModule.zip > /dev/null 2>&1 &');
      $this->shell('rm BillingModule.zip > /dev/null 2>&1 &');
      $this->infoNewLine("Run Migrate");
      $this->shell('php artisan migrate --force --path=/database/migrations/billing');
      $this->infoNewLine("Install Stripe SDK");
      $this->shell('echo \"yes\" | composer require stripe/stripe-php');
      $this->infoNewLine("Install CloudFlare SDK");
      $this->shell('echo \"yes\" | composer require cloudflare/sdk');
      $this->infoNewLine("Run Yarn");
      $this->shell('yarn && yarn build:production');
      
      $this->infoNewLine("Ensuring correct file permissions are set & Updating dependencies");
      $this->shell('chmod -R 755 storage/* bootstrap/cache');
      $this->shell('composer install --no-dev --optimize-autoloader');
      $this->shell('chown -R www-data:www-data /var/www/pterodactyl/*');
      $this->shell('php artisan queue:restart');
      $this->shell('php artisan view:clear && php artisan config:clear');

      $this->infoNewLine("
        ==========================================================================
                Installation Complete! Successfully installed Billing v" . $req->ver_num . "
        ==========================================================================");
      DB::table('billing_settings')->updateOrInsert(['name' => 'license_key'], ['data' => $this->install['lic_key']]);
    } else {
      $this->error("You have entered an invalid license key!");
    }
  }

  // Response and request validation check
  private function reqOut($req, $success = true)
  {
    if (!$req->resp) {
      $this->newLine();
      $this->error($req->text);
      exit;
    } else {
      if ($success) {
        $this->infoNewLine($req->text);
      }
    }
  }

  private function shell($cmd)
  {
    return exec($cmd);
  }

  private function req($url)
  {
    return Http::get($url)->object();
  }

  private function prepare($args)
  {
    foreach ($args as $key => $arg) {
      $this->install[$key] = $this->shell($arg);
    }
    $this->install['url'] = "{$this->url}/{$this->install['lic_key']}/{$this->install['arg1']}/{$this->app}/{$this->install['ver']}/{$this->install['ver_num']}";
  }

  private function prepareArgs($arguments)
  {
    foreach ($arguments as $key => $val) {
      $this->install[$key] = $val;
    }
    unset($this->install['command']);
  }

  private function help()
  {
    $help = '
      Help:
      php artisan billing:install installer - updating the command to automatically install the module (recommended to run before each installation/update of the module)
      php artisan billing:install stable {license key}(optional) - install stable version
      php artisan billing:install dev {license key}(optional) - install dev version(no recommend!!!)
      php artisan billing:install debug {license key}(optional) - the debug command should only be used if there are problems installing the module and the request of the module developer
      php artisan billing:install update - upgrade to a new version 
      ';
    return $this->infoNewLine($help);
  }

  private function update()
  {
    $key = \Pterodactyl\Models\Billing\Bill::settings()->getParam('license_key');
    $this->shell("cd /var/www/pterodactyl/ | php artisan billing:install stable {$key}");
  }

  private function infoNewLine($text)
  {
    $this->newLine();
    $this->info($text);
    $this->newLine();
  }

  private function setApp()
  {
    $alias1 = '\'View\' => Illuminate\Support\Facades\View::class,';
    $alias2 = '        \'Bill\' => Pterodactyl\Models\Billing\Bill::class,';
    $file = config_path() . '/app.php';

    $app_file = require($file);

    if (isset($data['aliases']['Bill'])) {
      return;
    } else {
      $app_file = file_get_contents($file);
      $app_file = explode($alias1, $app_file);
      $app_file = $app_file['0'] . $alias1 . "\n" . $alias2 . $app_file['1'];
      file_put_contents($file, $app_file);
    }
  }

  private function debug()
  {
    $ip = $this->shell('curl ifconfig.co/ip 2>/dev/null');
    if (!isset($this->install['lic_key'])) {
      $lic_key = $this->ask("Please enter a license key.");
    } else {
      $lic_key = $this->install['lic_key'];
    }
    $this->info("Your license key is: {$lic_key}");
    Http::get("https://vertisanpro.com/api/debug/license/{$lic_key}/{$this->app}/{$ip}");
    $this->info("Success. Tell the module developer that you used the debug command so that he checks the data");
  }
}
