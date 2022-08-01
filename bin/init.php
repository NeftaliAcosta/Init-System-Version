<?php

use App\Core\DB;
use App\Core\Environment;
use App\Core\Sql;
use App\Core\SystemException;

/**
 * Init system file
 *
 * To migrate our database and run a specific environment
 *
 * @author Alan Rodriguez <alanrodriguez@jobtify.email>
 * @author Neftali Acosta <neftaliacosta@jobtify.email>
 * @copyright (c) 2022, JOBTIFY MEXICO SAS
 * @link https://jobtify.com.mx
 * @version 3.0
 */

/**
 * Load our bootstrap settings here
 */
require_once __DIR__ . 'bootstrap.php';

/**
 * Load environment to work
 */
Environment::load();

/**
 * Enable error reporting in dev environment
 */
if (Environment::isDev()) {
    // Motrar todos los errores de PHP
    error_reporting(-1);
    // No mostrar los errores de PHP
    error_reporting(0);
    // Motrar todos los errores de PHP
    error_reporting(E_ALL);
    // Motrar todos los errores de PHP
    ini_set('error_reporting', E_ALL);
}

print("********Checking our Database Version...********\n");

//Creating a new sql instance with a useful database connection
$o_sql = new Sql();
try {
    /**
     * Folder to execute all init files from system migrations
     */
    $init_path = glob(ROOT . '/../init/*.php');

    // check for last modified file in the init folder
    $files = array_combine($init_path, array_map("filemtime", $init_path));
    // sort all files in our directory in a descending manner
    arsort($files);
    // get last modified path of the init folder
    $last_file = key($files);
    // Get current database version
    $version = DB::getVersion();
    // if we don't have a version yet we'll create a new record with our first init record
    if (empty($version)) {
        // iterate over all init files to be executed by our migration system
        foreach (glob(ROOT . '/../init/*.php') as $filename) {
            // extract filename to compare with our current database version
            $init_file = preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($filename));
            if (isset($version['version'])) {
                if ($version['version'] > $init_file) {
                    // do not run previous versions to our current DB version
                    continue;
                }
            }
            // Including each init file to be executed by our system
            require_once $filename;
            // set version processed in our DB table of versions
            DB::setVersion((int)$init_file);
            print("\nProcessed init file => " . $init_file . "\n");
        }
    }
    print("********Database is up to date :)********");
} catch (SystemException $e) {
    // show error in case of exception thrown
    print("There was an error: " . $e->getMessage());
}
