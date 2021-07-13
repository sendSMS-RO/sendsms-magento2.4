<?php
namespace AnyPlaceMedia\SendSMS\API\Data;

interface HistoryInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const HISTORY_ID = 'history_id';
    const STATUS = 'status';
    const MESSAGE = 'message';
    const DETAILS = 'details';
    const CONTENT = 'content';
    const TYPE = 'type';
    const SENT_ON = 'sent_on';
    const PHONE = 'phone';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Get message
     *
     * @return string|null
     */
    public function getMessage();

    /**
     * Get details
     *
     * @return string|null
     */
    public function getDetails();

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent();

    /**
     * Get type
     *
     * @return string|null
     */
    public function getType();

    /**
     * Get sent on
     *
     * @return string|null
     */
    public function getSentOn();

    /**
     * Get phone
     *
     * @return string|null
     */
    public function getPhone();

    /**
     * Set ID
     *
     * @param int $id
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setId($id);

    /**
     * Set status
     *
     * @param string $status
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setStatus($status);

    /**
     * Set message
     *
     * @param string $message
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setMessage($message);

    /**
     * Set details
     *
     * @param string $details
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setDetails($details);

    /**
     * Set content
     *
     * @param string $content
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setContent($content);

    /**
     * Set type
     *
     * @param string $type
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setType($type);

    /**
     * Set sent on
     *
     * @param string $sent_on
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setSentOn($sent_on);

    /**
     * Set phone
     *
     * @param string $phone
     * @return \AnyPlaceMedia\SendSMS\Api\Data\HistoryInterface
     */
    public function setPhone($phone);
}
