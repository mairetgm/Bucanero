<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Helper;

/**
 * Class Ftp
 * @package Wyomind\OrdersExportTool\Helper
 */
class Ftp extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind, \Magento\Framework\App\Helper\Context $context)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context);
    }
    /**
     * @param $data
     * @return \Magento\Framework\Filesystem\Io\Ftp|\Magento\Framework\Filesystem\Io\Sftp|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConnection($data)
    {
        $port = $data['ftp_port'];
        $login = $data['ftp_login'];
        $password = $data['ftp_password'];
        $sftp = $data['use_sftp'];
        $active = $data['ftp_active'];
        $host = str_replace(["ftp://", "ftps://"], "", $data["ftp_host"]);
        if ($data['ftp_port'] != "" && $data["use_sftp"]) {
            $host .= ":" . $data['ftp_port'];
        }
        if (isset($data['file_path'])) {
            $fullFilePath = rtrim($data['ftp_dir'], "/") . "/" . ltrim($data['file_path'], "/");
            $fullPath = dirname($fullFilePath);
        } else {
            $fullPath = rtrim($data['ftp_dir'], "/");
        }
        if ($sftp) {
            $ftp = $this->_ioSftp;
        } else {
            $ftp = $this->_ioFtp;
        }
        $ftp->open(array(
            'host' => $host,
            'port' => $port,
            'user' => $login,
            //ftp
            'username' => $login,
            //sftp
            'password' => $password,
            'timeout' => '10',
            'path' => $fullPath,
            'passive' => !$active,
        ));
        // sftp doesn't chdir automatically when opening connection
        if ($sftp) {
            $ftp->cd($fullPath);
        }
        return $ftp;
    }
    /**
     * @param $useSftp
     * @param $ftpPassive
     * @param $ftpHost
     * @param $ftpPort
     * @param $ftpLogin
     * @param $ftpPassword
     * @param $ftpDir
     * @param $path
     * @param $file
     * @return bool
     */
    public function ftpUpload($useSftp, $ftpPassive, $ftpHost, $ftpPort, $ftpLogin, $ftpPassword, $ftpDir, $path, $file)
    {
        if ($useSftp) {
            $ftp = $this->_ioSftp;
        } else {
            $ftp = $this->_ioFtp;
        }
        $rtn = false;
        try {
            $host = str_replace(["ftp://", "ftps://"], "", $ftpHost);
            $ftp->open([
                'host' => $host,
                'port' => $ftpPort,
                // only ftp
                'user' => $ftpLogin,
                'username' => $ftpLogin,
                // only sftp
                'password' => $ftpPassword,
                'timeout' => '120',
                'path' => $ftpDir,
                'passive' => $ftpPassive,
            ]);
            if ($useSftp) {
                $ftp->cd($ftpDir);
            }
            if (!$useSftp && $ftp->write($file, $this->storageHelper->getAbsoluteRootDir() . $path . $file)) {
                if ($this->framework->isAdmin()) {
                    $this->messageManager->addSuccess(sprintf(__("File '%s' successfully uploaded on %s"), $file, $ftpHost) . ".");
                }
                $rtn = true;
            } elseif ($useSftp && $ftp->write($file, $this->storageHelper->getAbsoluteRootDir() . $path . $file)) {
                if ($this->framework->isAdmin()) {
                    $this->messageManager->addSuccess(sprintf(__("File '%s' successfully uploaded on %s"), $file, $ftpHost) . ".");
                }
                $rtn = true;
            } else {
                if ($this->framework->isAdmin()) {
                    $this->messageManager->addError(sprintf(__("Unable to upload '%s'on %s"), $file, $ftpHost) . ".");
                }
                $rtn = false;
            }
        } catch (\Exception $e) {
            if ($this->framework->isAdmin()) {
                $this->messageManager->addError(__("Ftp upload error : ") . $e->getMessage());
            }
        }
        $ftp->close();
        return $rtn;
    }
}