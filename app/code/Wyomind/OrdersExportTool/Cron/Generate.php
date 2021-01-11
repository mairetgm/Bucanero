<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Cron;

/**
 * Module cron task
 */
class Generate
{
    /**
     * @var \Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\CollectionFactory|null
     */
    protected $_collectionFactory = null;
    protected $_logger = null;
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind, \Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\CollectionFactory $collectionFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_collectionFactory = $collectionFactory;
        $this->_logger = $this->objectManager->create("\\Wyomind\\OrdersExportTool\\Logger\\LoggerCron");
    }
    /**
     * Check whether a profile must be executed or not based on cron schedule
     * @param \Magento\Cron\Model\Schedule $schedule
     * @throws \Exception
     */
    public function checkToGenerate(\Magento\Cron\Model\Schedule $schedule)
    {
        try {
            $log = [];
            $this->_logger->notice('-------------------- CRON PROCESS --------------------');
            $log[] = '-------------------- CRON PROCESS --------------------';
            $coll = $this->_collectionFactory->create();
            $cnt = 0;
            foreach ($coll as $profileTmp) {
                $done = false;
                try {
                    $profile = clone $profileTmp;
                    $this->_logger->notice("--> Running profile : " . $profile->getName() . " [#" . $profile->getId() . "] <--");
                    $log[] = "--> Running profile : " . $profile->getName() . " [#" . $profile->getId() . "] <--";
                    $profile->isCron = true;
                    if (count($log) == 2) {
                        $profile->loadCustomFunctions();
                    }
                    $cron = array();
                    $cron['offset'] = $this->_coreDate->getGmtOffset('hours');
                    $cron['current']['gmtDate'] = $this->_coreDate->gmtDate('l Y-m-d H:i:s');
                    $cron['current']['gmtTime'] = $this->_coreDate->gmtTimestamp();
                    $cron['current']['localDate'] = $this->_coreDate->date('l Y-m-d H:i:s');
                    $cron['current']['localTime'] = $this->_coreDate->timestamp();
                    $cron['file']['gmtDate'] = $profile->getUpdatedAt();
                    $cron['file']['gmtTime'] = strtotime($profile->getUpdatedAt());
                    $cron['file']['localDate'] = $this->_coreDate->date('l Y-m-d H:i:s', $profile->getUpdatedAt());
                    $cron['file']['localTime'] = $this->_coreDate->timestamp($profile->getUpdatedAt());
                    $log[] = '   * Last update : ' . $cron['file']['gmtDate'] . " GMT / " . $cron['file']['localDate'] . ' GMT' . $cron['offset'];
                    $log[] = '   * Current date : ' . $cron['current']['gmtDate'] . " GMT / " . $cron['current']['localDate'] . ' GMT' . $cron['offset'];
                    $this->_logger->notice('   * Last update : ' . $cron['file']['gmtDate'] . " GMT / " . $cron['file']['localDate'] . ' GMT' . $cron['offset']);
                    $this->_logger->notice('   * Current date : ' . $cron['current']['gmtDate'] . " GMT / " . $cron['current']['localDate'] . ' GMT' . $cron['offset']);
                    $cronExpr = json_decode($profile->getScheduledTask());
                    $i = 0;
                    if ($cronExpr != null && isset($cronExpr->days)) {
                        foreach ($cronExpr->days as $d) {
                            foreach ($cronExpr->hours as $h) {
                                $time = explode(':', $h);
                                if (date('l', $cron['current']['localTime']) == $d) {
                                    $cron['tasks'][$i]['localTime'] = strtotime($this->_coreDate->date('Y-m-d')) + $cron['offset'] * 60 * 60 + $time[0] * 60 * 60 + $time[1] * 60;
                                    $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                                } else {
                                    $cron['tasks'][$i]['localTime'] = strtotime("last " . $d, $cron['current']['localTime']) + $cron['offset'] * 60 * 60 + $time[0] * 60 * 60 + $time[1] * 60;
                                    $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                                }
                                if ($cron['tasks'][$i]['localTime'] >= $cron['file']['localTime'] && $cron['tasks'][$i]['localTime'] <= $cron['current']['localTime'] && $done != true) {
                                    $this->_logger->notice('   * Scheduled : ' . ($cron['tasks'][$i]['localDate'] . " GMT" . $cron['offset']));
                                    $log[] = '   * Scheduled : ' . ($cron['tasks'][$i]['localDate'] . " GMT" . $cron['offset']);
                                    $this->_logger->notice("   * Starting generation");
                                    $result = $profile->generate();
                                    if ($result === $profile) {
                                        $done = true;
                                        $this->_logger->notice("   * EXECUTED!");
                                        $log[] = "   * EXECUTED!";
                                    } else {
                                        $this->_logger->notice("   * ERROR! " . $result);
                                        $log[] = "   * ERROR! " . $result;
                                    }
                                    $cnt++;
                                    break 2;
                                }
                                $i++;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $cnt++;
                    $this->_logger->notice("   * ERROR! " . $e->getMessage());
                    $log[] = "   * ERROR! " . $e->getMessage();
                }
                if (!$done) {
                    $this->_logger->notice("   * SKIPPED!");
                    $log[] = "   * SKIPPED!";
                }
            }
            if ($this->_framework->getStoreConfig("ordersexporttool/settings/enable_reporting")) {
                $emails = explode(',', $this->_framework->getStoreConfig("ordersexporttool/settings/emails"));
                if (count($emails) > 0) {
                    try {
                        if ($cnt) {
                            $template = "wyomind_ordersexporttool_cron_report";
                            $transport = $this->_transportBuilder->setTemplateIdentifier($template)->setTemplateOptions(['area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])->setTemplateVars(['report' => implode("<br/>", $log), 'subject' => $this->_framework->getStoreConfig('ordersexporttool/settings/report_title')])->setFrom(['email' => $this->_framework->getStoreConfig('ordersexporttool/settings/sender_email'), 'name' => $this->_framework->getStoreConfig('ordersexporttool/settings/sender_name')])->addTo($emails[0]);
                            $count = count($emails);
                            for ($i = 1; $i < $count; $i++) {
                                $transport->addCc($emails[$i]);
                            }
                            $transport->getTransport()->sendMessage();
                        }
                    } catch (\Throwable $e) {
                        $this->_logger->notice('   * EMAIL ERROR! ' . $e->getMessage());
                        $log[] = '   * EMAIL ERROR! ' . $e->getMessage();
                    }
                }
            }
        } catch (\Throwable $e) {
            $schedule->setStatus('failed');
            $schedule->setMessages($e->getMessage());
            $schedule->save();
            $this->_logger->notice("MASSIVE ERROR ! ");
            $this->_logger->notice($e->getMessage());
        }
    }
}