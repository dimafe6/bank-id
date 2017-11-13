<?php

namespace Dimafe6\BankID\Service;

use Dimafe6\BankID\Model\CollectResponse;
use Dimafe6\BankID\Model\OrderResponse;
use OutOfBoundsException;
use ReflectionClass;
use SoapClient;

/**
 * Class BankIDService
 *
 * @category PHP
 * @package  Dimafe6\BankID\Service
 * @author   Dmytro Feshchenko <dimafe2000@gmail.com>
 */
class BankIDService
{
    /**
     * Bank ID Sign method name
     */
    const METHOD_SIGN = 'Sign';

    /**
     * Bank ID Authenticate method name
     */
    const METHOD_AUTH = 'Authenticate';

    /**
     * Bank ID Collect method name
     */
    const METHOD_COLLECT = 'Collect';

    /**
     * @var SoapClient
     */
    private $client;

    /**
     * @var string
     */
    private $wsdlUrl;

    /**
     * @var string
     */
    private $soapOptions;

    /**
     * BankIDService constructor.
     * @param string $wsdlUrl Bank ID API url
     * @param array $options SoapClient options
     * @param bool $enableSsl Enable SSL
     */
    public function __construct($wsdlUrl, $options = [], $enableSsl = false)
    {
        if (!$enableSsl) {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ]);

            $options['stream_context'] = $context;
        }

        $this->wsdlUrl = $wsdlUrl;
        $this->soapOptions = $options;
        $this->client = new SoapClient($this->wsdlUrl, $this->soapOptions);
    }

    /**
     * @return array
     * @author Dmytro Feshchenko <dimafe2000@gmail.com>
     */
    private function availableMethods()
    {
        $class = new ReflectionClass(__CLASS__);
        $constants = $class->getConstants();
        $results = array_filter($constants, function ($constant) {
            return false !== strpos($constant, 'METHOD_');
        }, ARRAY_FILTER_USE_KEY);

        return array_values($results);
    }

    /**
     * @param $method
     * @return bool
     * @author Dmytro Feshchenko <dimafe2000@gmail.com>
     */
    private function isMethodAvailable($method)
    {
        return in_array($method, $this->availableMethods());
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @author Dmytro Feshchenko <dimafe2000@gmail.com>
     */
    public function soapCall($method, $parameters)
    {
        return $this->client->__soapCall($method, $parameters);
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws \SoapFault
     * @throws \OutOfBoundsException
     * @author Dmytro Feshchenko <dimafe2000@gmail.com>
     */
    public function call($method, $parameters)
    {
        if (!$this->isMethodAvailable($method)) {
            throw new OutOfBoundsException("Invalid method '$method'");
        }

        if (!is_array($parameters)) {
            throw new \InvalidArgumentException('parameters must be is an array');
        }

        return $this->soapCall($method, $parameters);
    }

    /**
     * @param $personalNumber
     * @param $userVisibleData
     * @param null $userHiddenData
     * @return OrderResponse
     */
    public function getSignResponse($personalNumber, $userVisibleData, $userHiddenData = null)
    {
        $parameters = [
            'personalNumber'  => $personalNumber,
            'userVisibleData' => base64_encode($userVisibleData),
        ];

        if (!empty($userHiddenData)) {
            $parameters['userNonVisibleData'] = base64_encode($userHiddenData);
        }

        $options = ['parameters' => $parameters];

        $response = $this->call(self::METHOD_SIGN, $options);

        $orderResponse = new OrderResponse();
        $orderResponse->orderRef = $response->orderRef;
        $orderResponse->autoStartToken = $response->autoStartToken;

        return $orderResponse;
    }

    /**
     * @param $personalNumber
     * @return OrderResponse
     * @throws \SoapFault
     */
    public function getAuthResponse($personalNumber)
    {
        $parameters = [
            'personalNumber' => $personalNumber,
        ];

        $options = ['parameters' => $parameters];

        $response = $this->call(self::METHOD_AUTH, $options);

        $orderResponse = new OrderResponse();
        $orderResponse->orderRef = $response->orderRef;
        $orderResponse->autoStartToken = $response->autoStartToken;

        return $orderResponse;
    }

    /**
     * @param string $orderRef
     * @return CollectResponse
     * @throws \SoapFault
     */
    public function collectResponse($orderRef)
    {
        $response = $this->call(self::METHOD_COLLECT, ['orderRef' => $orderRef]);

        $collect = new CollectResponse();
        $collect->progressStatus = $response->progressStatus;

        if ($collect->progressStatus == CollectResponse::PROGRESS_STATUS_COMPLETE) {
            $collect->userInfo = $response->userInfo;
            $collect->signature = $response->signature;
            $collect->ocspResponse = $response->ocspResponse;
        }

        return $collect;
    }
}
