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
/****   Test  04/11/2020 15:10pm********/
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

$params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = $runCode;
$params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = $runType;

$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);

$app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
$bootstrap->run($app);
//try {
//    require __DIR__ . '/app/bootstrap.php';
//} catch (\Exception $e) {
//    echo <<<HTML
//<div style="font:12px/1.35em arial, helvetica, sans-serif;">
//    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
//        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
//        Autoload error</h3>
//    </div>
//    <p>{$e->getMessage()}</p>
//</div>
//HTML;
//    exit(1);
//}
//
//$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
///** @var \Magento\Framework\App\Http $app */
//$app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
//$bootstrap->run($app);


