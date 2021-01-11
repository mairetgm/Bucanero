<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Helper;

use Magento\Framework\App\Helper\Context;
/**
 * Class email
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $directoryRead;
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind, \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead, Context $context)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context);
        $this->directoryRead = $directoryRead->create($this->storageHelper->getAbsoluteRootDir());
    }
    /**
     * @param array $data
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Mail_Exception
     */
    public function sendEmail($data = array())
    {
        if (empty($data["mail_recipients"])) {
            throw new \Exception(__("At least one recipient is required."));
        }
        if (empty($data["mail_subject"])) {
            throw new \Exception(__("The email subject is required."));
        }
        if (empty($data["mail_sender"])) {
            throw new \Exception(__("The email sender is required."));
        }
        $mails = explode(',', $data['mail_recipients']);
        foreach ($mails as $mail) {
            $this->mailWithAttachment($data["mail_sender"], $mail, $data["mail_subject"], $data["mail_message"]);
        }
    }
    /**
     * @param $mailFrom
     * @param $mailto
     * @param $subject
     * @param $message
     * @param array $filenames
     * @param $path
     * @param null $type
     * @return \Zend_Mail
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Mail_Exception
     */
    public function mailWithAttachment($mailFrom, $mailto, $subject, $message, $filenames = array(), $path = null, $type = null)
    {
        $mail = new \Zend_Mail();
        $mail->setType(\Zend_Mime::MULTIPART_MIXED);
        $mail->setFrom($mailFrom, $mailFrom);
        $mail->setBodyHtml($message);
        $mail->addTo($mailto, $mailto);
        $mail->setSubject($subject);
        if (!is_array($filenames)) {
            $filenames = [$filenames];
        }
        foreach ($filenames as $filename) {
            $mail->createAttachment($this->directoryRead->readFile($path . $filename), $type == null ? \Zend_Mime::TYPE_OCTETSTREAM : "text/" . $type, \Zend_Mime::DISPOSITION_INLINE, \Zend_Mime::ENCODING_BASE64, basename($filename));
        }
        return $mail->send();
    }
}