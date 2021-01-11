<?php

namespace Wyomind\OrderUpdater\Block\Adminhtml\Profiles\Renderer;

class Datetime extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime
{

    public $module = "OrderUpdater";

    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getImportedAt() == "" || $row->getImportedAt() == "0000-00-00 00:00:00") {
            return "-";
        } else {
            $url = $this->getUrl(strtolower($this->module).'/profiles/report', ['id' => $row->getId()]);
            return parent::render($row) . "&nbsp;&nbsp;<a href='javascript:void(require([\"wyomind_OrderUpdater_profile\"], function (profile) {profile.report(\"" . $url . "\", " . $row->getId() . ", \"" . $row->getName() . "\", \"" . parent::render($row) . "\")}));'>" . __("Show report") . "</a>";
        }
    }

}
