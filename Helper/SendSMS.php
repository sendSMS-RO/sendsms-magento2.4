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
    protected $curl;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \AnyPlaceMedia\SendSMS\Model\HistoryFactory $history,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resourceConfig,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeDate = $date;
        $this->history = $history;
        $this->resourceConfig = $resourceConfig;
        $this->collection = $collectionFactory->create();
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->curl = $curl;
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
            $url = 'https://api.sendsms.ro/json?action=message_send' . ($gdpr ? "_gdpr" : "")
                . '&username=' . urlencode($username)
                . '&password=' . urlencode(trim($password))
                . '&from=' . urlencode($from)
                . '&to=' . urlencode($phone)
                . '&text=' . urlencode($message)
                . '&short=' . ($short ? 'true' : 'false');

            $this->curl->setOption(CURLOPT_HEADER, 0);
            $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
            $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'GET');
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, '1');
            //set curl header
            $this->curl->addHeader("Content-Type", "application/json");
            //get request with url
            $this->curl->get($url);
            //read response
            $status = json_decode($this->curl->getBody(), true);

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
                ->addFieldToFilter(
                    'path',
                    [
                        'in' => [
                            'sendsms_settings/sendsms/sendsms_settings_price',
                            'sendsms_settings/sendsms/sendsms_settings_price_date'
                        ]
                    ]
                )
                ->getData();
            foreach ($configs as $config) {
                if ($config['path'] === 'sendsms_settings/sendsms/sendsms_settings_price') {
                    $price = $config['value'];
                }
                if ($config['path'] === 'sendsms_settings/sendsms/sendsms_settings_price_date') {
                    $priceTime = $config['value'];
                }
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

            $header = ['message', 'to', 'from'];
            $stream->writeCsv($header);

            foreach ($phones as $phone) {
                $simulation = $this->scopeConfig->getValue(
                    'sendsms_settings/sendsms/sendsms_settings_simulation',
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                );
                if ($simulation) {
                    $phone = $this->scopeConfig->getValue(
                        'sendsms_settings/sendsms/sendsms_settings_simulation_number',
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                    );
                }
                $data = [];
                $data[] = $message;
                $data[] = $this->validatePhone($phone);
                $data[] = $from;
                $stream->writeCsv($data);
                if($simulation) {
                    break;
                }
            }

            $name = 'Magento - ' . $this->storeManager->getStore()->getName() . ' - ' . uniqid();

            $url = "https://api.sendsms.ro/json";

            // $start_time = "2970-01-01 02:00:00";
            $url = $url . "?action=batch_create";
            $url .= "&username=" . urlencode($username);
            $url .= "&password=" . urlencode($password);
            $url .= "&name=" . urlencode($name);
            $url .= "&start_time=";

            $readableFile = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->openFile('sendsms/batch.csv');
            $data = 'data=' . urlencode($readableFile->readAll());
            $readableFile->close();
            unset($stream);
            $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
            $this->curl->addHeader("Connection", "keep-alive");

            $this->curl->post($url, $data);

            $response = $this->curl->getBody();
            $result = json_decode($response, true);

            $this->directory->delete($filepath);

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
            $url = 'http://api.sendsms.ro/json?action=route_check_price&username=' . urlencode($username)
                . '&password=' . urlencode($password)
                . '&to=' . urlencode($to);

            $this->curl->setOption(CURLOPT_HEADER, 0);
            $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
            $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'GET');
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, '1');
            //set curl header
            $this->curl->addHeader("Content-Type", "application/json");
            //get request with url
            $this->curl->get($url);
            //read response
            $status = json_decode($this->curl->getBody(), true);

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
            $url = 'http://api.sendsms.ro/json?action=user_get_balance&username=' . urlencode($username)
                . '&password=' . urlencode($password);

            $this->curl->setOption(CURLOPT_HEADER, 0);
            $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
            $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'GET');
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, '1');
            //set curl header
            $this->curl->addHeader("Content-Type", "application/json");
            //get request with url
            $this->curl->get($url);
            //read response
            $status = json_decode($this->curl->getBody(), true);
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
        if (empty($phone_number)) {
            return '';
        }
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

    public function clearPhoneNumber($phone_number)
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
        $bad = [
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
        ];
        $cleanLetters = ["A", "a", "A", "a", "I", "i", "S", "s", "T", "t", "S", "s", "T", "t", "a", " ", "-"];
        return str_replace($bad, $cleanLetters, $string);
    }
}
