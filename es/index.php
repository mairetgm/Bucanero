<?php
/**
 * Application entry point
 *
 * Example - run a particular store or website:
 * --------------------------------------------
 * require __DIR__ . '/app/bootstrap.php';
 * $params = $_SERVER;
 * $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'website2';
 * $params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'website';
 * $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
 * \/** @var \Magento\Framework\App\Http $app *\/
 * $app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
 * $bootstrap->run($app);
 * --------------------------------------------
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/app/bootstrap.php';
$params = $_SERVER;
$domainName = $_SERVER['SERVER_NAME'];
switch ($domainName) {
    case 'hol.bucanero.local':
        $runType = 'website';
        $runCode = 'WS_HOL';
        break;
    case 'car.bucanero.local':
        $runType = 'website';
        $runCode = 'WS_CAR';
        break;
    case 'cav.bucanero.local':
        $runType = 'website';
        $runCode = 'WS_CA';
        break;
    default:
        $runType = 'website';
        $runCode = 'WS_HAV';
}


$params = $_SERVER;
$params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'es';

//store code as same in admin panel

$params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'store';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);

/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication('Magento\Framework\App\Http');
$bootstrap->run($app);



