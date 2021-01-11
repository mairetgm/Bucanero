<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-12-11T14:10:32+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/FileUpload.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\File\Uploader;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Psr\Log\LoggerInterface;

class FileUpload
{
    private $fileSystem;

    private $uploaderFactory;

    private $logger;

    private $request;

    public function __construct(
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory,
        LoggerInterface $logger,
        RequestInterface $request

    ) {
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->logger = $logger;
        $this->request = $request;
    }

    /**
     * @param DataObject $dataObject
     *
     * @return bool|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function processInputFieldValue($dataObject)
    {
        /** @var Fields $field */
        $field = $dataObject->getData('field_object');
        $attributeCode = $field->getAttributeCode();
        $value = $this->processUploads($attributeCode);

        if (!isset($value['name'])) {
            return false;
        }

        $path = 'customer';
        if ($field->getAttributeTypeId() === CustomAttributes::ADDRESS_ENTITY) {
            $path = 'customer_address';
        }

        $fileName = $value['name'];
        $dispersionPath = Uploader::getDispersionPath($fileName);
        $mediaDir = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);

        $customerPath = $mediaDir->getAbsolutePath($path);
        $existingFile = $customerPath
            . DIRECTORY_SEPARATOR
            . $dispersionPath
            . DIRECTORY_SEPARATOR
            . Uploader::getCorrectFileName($fileName);

        if (!empty($value['tmp_name']) && file_exists($existingFile)) {
            $existing = $dispersionPath . DIRECTORY_SEPARATOR . Uploader::getCorrectFileName($fileName);
            return $existing;
        }

        if (!empty($value['tmp_name'])) {
            try {
                $uploader = $this->uploaderFactory->create(['fileId' => $value]);
                $uploader->setFilesDispersion(true);
                $uploader->setFilenamesCaseSensitivity(false);
                $uploader->setAllowRenameFiles(true);
                $uploader->save($customerPath, $fileName);
                $result = $uploader->getUploadedFileName();
                return $result;
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }

        return false;
    }

    public function checkoutUploads($uploads)
    {
        foreach ($uploads as $upload) {
            $this->processUpload($upload);
        }
    }

    public function adminUploads($uploads, $fileUploadNameMapping)
    {
        foreach ($uploads as $upload) {
            $filename = $upload['name'];
            if (isset($fileUploadNameMapping[$filename])) {
                $upload['name'] = $fileUploadNameMapping[$filename];
            }
            $this->processUpload($upload);
        }
    }

    public function registrationUploads($uploads)
    {
        foreach ($uploads as $upload) {
            $this->processUpload($upload);
        }
    }

    /**
     * @param array $file
     * @param string $customerFolder
     *
     * @return bool|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function processUpload($file, $customerFolder = 'customer')
    {
        $fileName = $file['name'];
        $dispersionPath = Uploader::getDispersionPath($fileName);
        $mediaDir = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);

        $customerPath = $mediaDir->getAbsolutePath($customerFolder);

        $existingFile = $customerPath
            . DIRECTORY_SEPARATOR
            . $dispersionPath
            . DIRECTORY_SEPARATOR
            . Uploader::getCorrectFileName($fileName);
        if (!empty($file['tmp_name']) && file_exists($existingFile)) {
            return Uploader::getCorrectFileName($fileName);
        }
        if (!empty($file['tmp_name'])) {
            try {
                $uploader = $this->uploaderFactory->create(['fileId' => $file]);
                $uploader->setFilesDispersion(true);
                $uploader->setFilenamesCaseSensitivity(false);
                $uploader->setAllowRenameFiles(true);
                $uploader->save($customerPath, $fileName);
                $result = $uploader->getUploadedFileName();
                return $this->removeDispersionPath($dispersionPath, $result);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
        return false;
    }

    private function removeDispersionPath($dispersionPath, $result)
    {
        return str_replace($dispersionPath, '', $result);
    }

    /**
     * @param $attributeCode
     *
     * @return array
     * @deprecated
     */
    private function processUploads($attributeCode)
    {
        $value = [];

        $allFiles = $this->request->getFiles();
        $files = $allFiles['order'];

        if ($files !== null) {
            foreach ($files as $fileTypes) {
                foreach ($fileTypes as $code => $data) {
                    if ($data['tmp_name'] !== '') {
                        $value[$code] = $data;
                    }
                }
            }
        }

        $finalValue = [];

        if (isset($value[$attributeCode])) {
            $finalValue = $value[$attributeCode];
        }

        return $finalValue;
    }
}