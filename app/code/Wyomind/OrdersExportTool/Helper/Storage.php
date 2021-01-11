<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Helper;

/**
 * Class Storage
 * @package Wyomind\OrdersExportTool\Helper
 */
class Storage extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|null
     */
    protected $_ioWrite = null;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|null
     */
    protected $_directoryList = null;
    /**
     * @var array
     */
    protected $_ext = [1 => 'xml', 2 => 'txt', 3 => 'csv', 4 => 'tsv', 5 => 'din'];
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind, \Magento\Framework\App\Helper\Context $context)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_ioWrite = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        parent::__construct($context);
    }
    /**
     * Get file type
     * @param string $type
     * @return string
     */
    public function getFileType($type)
    {
        return $this->_ext[$type];
    }
    /**
     * Get the file name
     * @param $dateFormat
     * @param $name
     * @param string $type
     * @param $currentTime
     * @param string $temp
     * @param null|string $increment
     * @return string
     */
    public function getFileName($dateFormat, $name, $type, $currentTime, $temp = '.temp', $increment = null)
    {
        $nameTmp = $this->_dateTime->date($dateFormat, $currentTime);
        $fileNameOutput = str_replace('{f}', $name, $nameTmp);
        return $fileNameOutput . $increment . "." . $this->getFileType($type) . $temp;
    }
    /**
     * Return the file name
     * @param object $model
     * @return string|string[]|null
     */
    public function getFile($model)
    {
        $types = ['none', 'xml', 'txt', 'csv', 'tsv', 'din'];
        $ext = $types[$model->getType()];
        $date = $this->_dateTime->date($model->getDateFormat(), strtotime($model->getUpdatedAt()));
        $fileName = preg_replace('/^\\//', '', $model->getPath() . str_replace('{f}', $model->getName(), $date) . '.' . $ext);
        return $fileName;
    }
    /**
     * Return the file url
     * @param string $file
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFileUrl($file)
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . $file;
    }
    /**
     * Open a file with write permission
     * @param string $path
     * @param string $file
     * @return file interface
     * @throws \Exception
     */
    public function openDestinationFile($path, $file)
    {
        $io = null;
        $this->_ioWrite->create($path);
        if (!$this->_ioWrite->isWritable($path)) {
            throw new \Exception(__('File "%1" cannot be saved.<br/>Please, make sure the directory "%2" is writable by web server.', $file, $path));
        } else {
            $io = $this->_ioWrite->openFile($path . $file, 'w');
        }
        return $io;
    }
    /**
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getAbsoluteRootDir()
    {
        $rootDirectory = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        return $rootDirectory;
    }
}