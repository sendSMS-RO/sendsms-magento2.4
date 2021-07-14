<?php namespace AnyPlaceMedia\SendSMS\Model;

use AnyPlaceMedia\SendSMS\API\Data\HistoryInterface;
use Magento\Framework\DataObject\IdentityInterface;

class History extends \Magento\Framework\Model\AbstractModel implements HistoryInterface, IdentityInterface
{

    /**#@+
     * Post's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'sendsms_history';

    /**
     * @var string
     */
    protected $_cacheTag = 'sendsms_history';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sendsms_history';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('AnyPlaceMedia\SendSMS\Model\ResourceModel\History');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::HISTORY_ID);
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get message
     *
     * @return string|null
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * Get details
     *
     * @return string|null
     */
    public function getDetails()
    {
        return $this->getData(self::DETAILS);
    }

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * Get type
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Get sent on
     *
     * @return string|null
     */
    public function getSentOn()
    {
        return $this->getData(self::SENT_ON);
    }

    /**
     * Get phone
     *
     * @return string|null
     */
    public function getPhone()
    {
        return $this->getData(self::PHONE);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setId($id)
    {
        return $this->setData(self::HISTORY_ID, $id);
    }

    /**
     * Set status
     *
     * @param string $status
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set message
     *
     * @param string $message
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * Set details
     *
     * @param string $details
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setDetails($content)
    {
        return $this->setData(self::DETAILS, $content);
    }

    /**
     * Set content
     *
     * @param string $content
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Set type
     *
     * @param string $type
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Set sent on
     *
     * @param string $sent_on
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setSentOn($sent_on)
    {
        return $this->setData(self::SENT_ON, $sent_on);
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setPhone($phone)
    {
        return $this->setData(self::PHONE, $phone);
    }
}
