<?php

use Dblencowe\Wealth\Wealth;
use Dblencowe\Wealth\Poloniex;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$currencyCode = getenv('BASE_CURRENCY');
$refresh = $argv[2] ?? true;
$lookupCurrencies = (array) json_decode(getenv('CURRENCIES'));

$poloniexExchange = new Poloniex(getenv('POLONIEX_KEY'),getenv('POLONIEX_SECRET'));
$poloniexCurrencies = $poloniexExchange->getBalances();

$locale = getenv('LOCALE');

// $wealth = new Wealth($currencyCode, $lookupCurrencies, $locale, $refresh);
$wealth = new Wealth($currencyCode, $poloniexCurrencies, $locale, $refresh);
$wealth->run();
