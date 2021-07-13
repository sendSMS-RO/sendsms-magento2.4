<?php

namespace AnyPlaceMedia\SendSMS\Helper;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\Store;

class SendSMS extends AbstractHelper
{
    protected $scopeConfig;
    protected $storeDate;
    protected $history;
    protected $resourceConfig;
    protected $collection;
    protected $storeManager;
    protected $filesystem;
    protected $directory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \AnyPlaceMedia\SendSMS\Model\HistoryFactory $history,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resourceConfig,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeDate = $date;
        $this->history = $history;
        $this->resourceConfig = $resourceConfig;
        $this->collection = $collectionFactory->create();
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * @param $phone
     * @param $message
     * @param $type
     */
    public function sendSMS($phone, $message, $type = 'order', $gdpr = false, $short = false)
    {
        $username = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_username',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $password = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_password',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $from = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_from',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $simulation = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_simulation',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        if ($simulation && $type !== 'test') {
            $phone = $this->scopeConfig->getValue(
                'sendsms_settings/sendsms/sendsms_settings_simulation_number',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
        }
        $phone = $this->validatePhone($phone);

        if (!empty($phone) && !empty($username) && !empty($password)) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_URL, 'https://api.sendsms.ro/json?action=message_send' . ($gdpr ? "_gdpr" : "") . '&username=' . urlencode($username) . '&password=' . urlencode(trim($password)) . '&from=' . urlencode($from) . '&to=' . urlencode($phone) . '&text=' . urlencode($message) . '&short=' . ($short ? 'true' : 'false'));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Connection: keep-alive"));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $status = curl_exec($curl);
            $status = json_decode($status, true);
            curl_close($curl);

            # add to history
            $history = $this->history->create();
            $history->setStatus(isset($status['status']) ? $status['status'] : '');
            $history->setMessage(isset($status['message']) ? $status['message'] : '');
            $history->setDetails(isset($status['details']) ? $status['details'] : '');
            $history->setContent($message);
            $history->setType($type);
            $history->setSentOn($this->storeDate->date());
            $history->setPhone($phone);
            $history->save();

            $price = 0;
            $priceTime = 0;
            $configs = $this->collection
                ->addFieldToFilter('scope', ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
                ->addFieldToFilter('scope_id', Store::DEFAULT_STORE_ID)
                ->addFieldToFilter('path', ['in' => ['sendsms_settings/sendsms/sendsms_settings_price', 'sendsms_settings/sendsms/sendsms_settings_price_date']])
                ->getData();
            foreach ($configs as $config) {
                if ($config['path'] === 'sendsms_settings/sendsms/sendsms_settings_price')
                    $price = $config['value'];
                if ($config['path'] === 'sendsms_settings/sendsms/sendsms_settings_price_date')
                    $priceTime = $config['value'];
            }
            if (empty($priceTime) || empty($price) || $priceTime < date('Y-m-d H:i:s')) {
                $this->routeCheckPrice($phone);
            }
        }
    }

    public function batchCreate($phones, $message)
    {
        $from = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_from',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $username = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_username',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $password = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_password',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        try {
            $filepath = 'sendsms/batch.csv';
            $this->directory->create('sendsms');
            $stream = $this->directory->openFile($filepath, 'w+');
            // $stream->lock();

            $header = ['message', 'to', 'from'];
            $stream->writeCsv($header);

            foreach ($phones as $phone) {
                $data = [];
                $data[] = $message;
                $data[] = $this->validatePhone($phone['telephone']);
                $data[] = $from;
                $stream->writeCsv($data);
            }

            $name = 'Magento - ' . $this->storeManager->getStore()->getName() . ' - ' . uniqid();

            $file_type = "csv";
            $url = "https://api.sendsms.ro/json";

            $start_time = "2970-01-01 02:00:00";
            $curl = curl_init();

            $url = $url . "?action=batch_create";
            $url .= "&username=" . urlencode($username);
            $url .= "&password=" . urlencode($password);
            $url .= "&name=" . urlencode($name);
            $url .= "&file_type=" . urlencode($file_type);

            if (!is_null($start_time)) {
                $url .= "&start_time=" . urlencode($start_time);
            }

            $data = 'data=' . urlencode($this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->openFile('sendsms/batch.csv')->readAll());
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Connection: keep-alive"));
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

            $result = curl_exec($curl);

            if ($result === false) {
                error_log(curl_error($curl) . ' - ' . curl_errno($curl));
            }

            $size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

            $result = json_decode(substr($result, $size), true);
            $this->directory->delete($filepath);
            curl_close($curl);

            # add to history
            $history = $this->history->create();
            $history->setStatus(isset($result['status']) ? $result['status'] : '');
            $history->setMessage(isset($result['message']) ? $result['message'] : '');
            $history->setDetails(isset($result['details']) ? $result['details'] : '');
            $history->setContent("We created your campaign. Go and check the batch called: " . $name);
            $history->setType("Batch Campaign");
            $history->setSentOn($this->storeDate->date());
            $history->setPhone("Go to hub.sendsms.ro");
            $history->save();
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function routeCheckPrice($to)
    {
        $username = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_username',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $password = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_password',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );

        if (!empty($username) && !empty($password)) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_URL, 'http://api.sendsms.ro/json?action=route_check_price&username=' . urlencode($username) . '&password=' . urlencode($password) . '&to=' . urlencode($to));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Connection: keep-alive"));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $status = curl_exec($curl);
            $status = json_decode($status, true);
            curl_close($curl);
            if ($status['details']['status'] === 64) {
                $this->resourceConfig->saveConfig(
                    'sendsms_settings/sendsms/sendsms_settings_price',
                    $status['details']['cost'],
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    Store::DEFAULT_STORE_ID
                );
                $this->resourceConfig->saveConfig(
                    'sendsms_settings/sendsms/sendsms_settings_price_date',
                    date('Y-m-d H:i:s', strtotime('+1 day')),
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    Store::DEFAULT_STORE_ID
                );
            }
        }
    }

    public function getBalance()
    {
        $username = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_username',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $password = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_password',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );

        if (!empty($username) && !empty($password)) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_URL, 'http://api.sendsms.ro/json?action=user_get_balance&username=' . urlencode($username) . '&password=' . urlencode($password));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Connection: keep-alive"));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $status = curl_exec($curl);
            $status = json_decode($status, true);
            curl_close($curl);
            return $status;
        }
        return false;
    }

    /**
     * @param $phone
     * @return string
     */
    public function validatePhone($phone_number)
    {
        if (empty($phone_number)) return '';
        $phone_number = $this->clearPhoneNumber($phone_number);
        //Strip out leading zeros:
        //this will check the country code and apply it if needed
        $cc = $this->scopeConfig->getValue(
            'sendsms_settings/sendsms/sendsms_settings_prefix',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        if ($cc === "INT") {
            return $phone_number;
        }
        $phone_number = ltrim($phone_number, '0');

        if (!preg_match('/^' . $cc . '/', $phone_number)) {
            $phone_number = $cc . $phone_number;
        }

        return $phone_number;
    }

    function clearPhoneNumber($phone_number)
    {
        $phone_number = str_replace(['+', '-'], '', filter_var($phone_number, FILTER_SANITIZE_NUMBER_INT));
        //Strip spaces and non-numeric characters:
        $phone_number = preg_replace("/[^0-9]/", "", $phone_number);
        return $phone_number;
    }

    /**
     * @param $string
     * @return string
     */
    public function cleanDiacritice($string)
    {
        $bad = array(
            "\xC4\x82",
            "\xC4\x83",
            "\xC3\x82",
            "\xC3\xA2",
            "\xC3\x8E",
            "\xC3\xAE",
            "\xC8\x98",
            "\xC8\x99",
            "\xC8\x9A",
            "\xC8\x9B",
            "\xC5\x9E",
            "\xC5\x9F",
            "\xC5\xA2",
            "\xC5\xA3",
            "\xC3\xA3",
            "\xC2\xAD",
            "\xe2\x80\x93"
        );
        $cleanLetters = array("A", "a", "A", "a", "I", "i", "S", "s", "T", "t", "S", "s", "T", "t", "a", " ", "-");
        return str_replace($bad, $cleanLetters, $string);
    }
}
