<?php
use SilverStripe\Core\Environment;

// find the database name from the environment file
if (empty(Environment::getEnv('SS_DATABASE_NAME'))) {
    Environment::setEnv('SS_DATABASE_NAME', 'SS_cwp');
}

date_default_timezone_set('Pacific/Auckland');
