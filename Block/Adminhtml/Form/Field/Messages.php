<?php

namespace AnyPlaceMedia\SendSMS\Block\Adminhtml\Form\Field;

use AnyPlaceMedia\SendSMS\Block\Adminhtml\Form\Field\StatusColumn;
use AnyPlaceMedia\SendSMS\Block\Adminhtml\Form\Field\GdprColumn;
use AnyPlaceMedia\SendSMS\Block\Adminhtml\Form\Field\ShortColumn;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Ranges
 */
class Messages extends AbstractFieldArray
{
    /**
     * @var StatusColumn
     */
    private $statusRenderer;

    /**
     * @var GdprColumn
     */
    private $gdprColumn;

    /**
     * @var ShortColumn
     */
    private $shortColumn;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('status', [
            'label' => __('Status'),
            'renderer' => $this->getStatusRenderer()
        ]);

        $this->addColumn('message', [
            'label' => __('Message'),
            'class' => 'sendsms-char-count required-entry',
        ]);

        $this->addColumn('gdpr', [
            'label' => __('Unsubcribe link?'),
            'renderer' => $this->getGdprRenderer(),
        ]);

        $this->addColumn('short', [
            'label' => __('Minimize all urls?'),
            'renderer' => $this->getShortRenderer(),
        ]);


        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $status = $row->getStatus();
        if ($status !== null) {
            $options['option_' . $this->getStatusRenderer()->calcOptionHash($status)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return StatusColumn
     * @throws LocalizedException
     */
    private function getStatusRenderer()
    {

        if (!$this->statusRenderer) {
            $this->statusRenderer = $this->getLayout()->createBlock(
                StatusColumn::class,
                'status',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->statusRenderer;
    }

    /**
     * This will create a dropdown with yes/no
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    private function getGdprRenderer()
    {
        if (!$this->gdprColumn) {
            $this->gdprColumn = $this->getLayout()->createBlock(
                GdprColumn::class,
                'gdpr',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->gdprColumn;
    }

    /**
     * This will create a dropdown with yes/no
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    private function getShortRenderer()
    {
        if (!$this->shortColumn) {
            $this->shortColumn = $this->getLayout()->createBlock(
                ShortColumn::class,
                'short',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->shortColumn;
    }
}
