<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrderUpdater\Cron;

/**
 * Class Run
 * @package Wyomind\OrderUpdater\Cron
 */
class Run
{
    /**
     * @var string
     */
    public $module = "OrderUpdater";
    /**
     * @var \Wyomind\OrderUpdater\Model\ResourceModel\Profiles\CollectionFactory
     */
    protected $collectionFactory;
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Wyomind\OrderUpdater\Model\ResourceModel\Profiles\CollectionFactory $collectionFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->logger = $this->objectManager->create("Wyomind\\OrderUpdater\\Logger\\LoggerCron");
        $this->collectionFactory = $collectionFactory;
    }
    /**
     * @param \Magento\Cron\Model\Schedule $schedule
     * @throws \Exception
     */
    public function run(\Magento\Cron\Model\Schedule $schedule)
    {
        try {
            $log = [];
            $this->logger->notice("-------------------- CRON PROCESS --------------------");
            $log[] = "-------------------- CRON PROCESS --------------------";
            $coll = $this->collectionFactory->create();
            $cnt = 0;
            foreach ($coll as $profile) {
                $done = false;
                try {
                    $log[] = "--> Running profile : " . $profile->getName() . " [#" . $profile->getId() . "] <--";
                    $cron = [];
                    $cron['current']['localDate'] = $this->coreDate->date('l Y-m-d H:i:s');
                    $cron['current']['gmtDate'] = $this->coreDate->gmtDate('l Y-m-d H:i:s');
                    $cron['current']['localTime'] = $this->coreDate->timestamp();
                    $cron['current']['gmtTime'] = $this->coreDate->gmtTimestamp();
                    $cron['file']['localDate'] = $this->coreDate->date('l Y-m-d H:i:s', $profile->getImportedAt());
                    $cron['file']['gmtDate'] = $profile->getImportedAt();
                    $cron['file']['localTime'] = $this->coreDate->timestamp($profile->getImportedAt());
                    $cron['file']['gmtTime'] = strtotime($profile->getImportedAt());
                    $cron['offset'] = $this->coreDate->getGmtOffset("hours");
                    $log[] = '   * Last update : ' . $cron['file']['gmtDate'] . " GMT / " . $cron['file']['localDate'] . ' GMT' . $cron['offset'] . "";
                    $log[] = '   * Current date : ' . $cron['current']['gmtDate'] . " GMT / " . $cron['current']['localDate'] . ' GMT' . $cron['offset'] . "";
                    $cronExpr = json_decode($profile->getCronSettings());
                    $i = 0;
                    if ($cronExpr != null && isset($cronExpr->days)) {
                        foreach ($cronExpr->days as $d) {
                            foreach ($cronExpr->hours as $h) {
                                $time = explode(':', $h);
                                if (date('l', $cron['current']['gmtTime']) == $d) {
                                    $cron['tasks'][$i]['localTime'] = strtotime($this->coreDate->date('Y-m-d')) + $time[0] * 60 * 60 + $time[1] * 60;
                                    $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                                } else {
                                    $cron['tasks'][$i]['localTime'] = strtotime("last " . $d, $cron['current']['localTime']) + $time[0] * 60 * 60 + $time[1] * 60;
                                    $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                                }
                                if (true || $cron['tasks'][$i]['localTime'] >= $cron['file']['localTime'] && $cron['tasks'][$i]['localTime'] <= $cron['current']['localTime'] && $done != true) {
                                    $log[] = '   * Scheduled : ' . ($cron['tasks'][$i]['localDate'] . " GMT" . $cron['offset']) . "";
                                    $result = $profile->multipleImport(false);
                                    if ($result) {
                                        $done = true;
                                        $log[] = "   * EXECUTED!";
                                        $log[] = __('The profile %1 [ID:%2] has been processed.', $profile->getName(), $profile->getId());
                                        $log[] = __('%1', $result["success"]);
                                        $log[] = __('%1', $result["warning"]);
                                    }
                                    $cnt++;
                                    break 2;
                                }
                                $i++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $cnt++;
                    $log[] = "   * ERROR! " . $e->getMessage() . "";
                }
                if (!$done) {
                    $log[] = "   * SKIPPED!";
                }
            }
            if ($this->framework->getStoreConfig($this->module . '/settings/log')) {
                $this->logger->notice(implode("
", $log));
            }
            if ($this->framework->getStoreConfig($this->module . "/settings/enable_reporting")) {
                $emails = explode(',', $this->framework->getStoreConfig($this->module . "/settings/emails"));
                if (count($emails) > 0) {
                    try {
                        if ($cnt) {
                            $template = "wyomind_OrderUpdater_cron_report";
                            $transport = $this->transportBuilder->setTemplateIdentifier($template)->setTemplateOptions(['area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])->setTemplateVars(['report' => implode("<br/>", $log), 'subject' => $this->framework->getStoreConfig($this->module . '/settings/report_title')])->setFrom(['email' => $this->framework->getStoreConfig($this->module . '/settings/sender_email'), 'name' => $this->framework->getStoreConfig($this->module . '/settings/sender_name')])->addTo($emails[0]);
                            $count = count($emails);
                            for ($i = 1; $i < $count; $i++) {
                                $transport->addCc($emails[$i]);
                            }
                            $transport->getTransport()->sendMessage();
                        }
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->logger->notice('   * EMAIL ERROR! ' . $e->getMessage());
                        throw new \Magento\Framework\Exception\LocalizedException(__("Error: %s", $e->getMessage()));
                    }
                }
            }
        } catch (\Exception $e) {
            $schedule->setStatus('failed');
            $schedule->setMessage($e->getMessage());
            $schedule->save();
            $this->logger->notice("CRITICAL ERROR ! ");
            $this->logger->notice($e->getMessage());
        }
    }
}