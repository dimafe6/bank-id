<?php

namespace Dimafe6\BankID\Service;

use Dimafe6\BankID\Model\CollectResponse;
use Dimafe6\BankID\Model\OrderResponse;
use PHPUnit\Framework\TestCase;

class BankIDServiceTest extends TestCase
{
    const TEST_PERSONAL_NUMBER = '199202271434';

    /**
     * @var BankIDService $bankIDService
     */
    private $bankIDService;

    public function setUp()
    {
        $this->bankIDService = new BankIDService(
            'https://appapi2.test.bankid.com/rp/v5/',
            isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '127.0.0.1',
            [
                'verify' => false,
                'cert'   => realpath(__DIR__ . '/../bankId.pem'),
            ]
        );
    }

    /**
     * @return OrderResponse
     */
    public function testSignResponse()
    {
        $signResponse = $this->bankIDService->getSignResponse(
            self::TEST_PERSONAL_NUMBER,
            'userVisibleData',
            'userNonVisibleData'
        );

        $this->assertInstanceOf(OrderResponse::class, $signResponse);

        return $signResponse;
    }

    /**
     * @depends testSignResponse
     * @param OrderResponse $signResponse
     * @return \Dimafe6\BankID\Model\CollectResponse
     */
    public function testCollectSignResponse($signResponse)
    {
        $this->assertInstanceOf(OrderResponse::class, $signResponse);

        $attempts = 0;
        do {
            fwrite(STDOUT, "\nWaiting confirmation from BankID application...\n");
            sleep(10);

            $collectResponse = $this->bankIDService->collectResponse($signResponse->orderRef);
            $attempts++;
        } while ($collectResponse->status !== CollectResponse::STATUS_COMPLETED && $attempts <= 6);

        $this->assertInstanceOf(CollectResponse::class, $collectResponse);
        $this->assertEquals(CollectResponse::STATUS_COMPLETED, $collectResponse->status);

        return $collectResponse;
    }

    /**
     * @return OrderResponse
     */
    public function testAuthResponse()
    {
        $authResponse = $this->bankIDService->getAuthResponse(self::TEST_PERSONAL_NUMBER);

        $this->assertInstanceOf(OrderResponse::class, $authResponse);

        return $authResponse;
    }

    /**
     * @depends testAuthResponse
     * @param OrderResponse $authResponse
     * @return \Dimafe6\BankID\Model\CollectResponse
     */
    public function testCollectAuthResponse($authResponse)
    {
        $this->assertInstanceOf(OrderResponse::class, $authResponse);

        $attempts = 0;
        do {
            fwrite(STDOUT, "\nWaiting confirmation from BankID application...\n");
            sleep(10);

            $collectResponse = $this->bankIDService->collectResponse($authResponse->orderRef);
            $attempts++;
        } while ($collectResponse->status !== CollectResponse::STATUS_COMPLETED && $attempts <= 6);

        $this->assertInstanceOf(CollectResponse::class, $collectResponse);
        $this->assertEquals(CollectResponse::STATUS_COMPLETED, $collectResponse->status);

        return $collectResponse;
    }

    public function testCancelResponse()
    {
        $authResponse = $this->bankIDService->getAuthResponse(self::TEST_PERSONAL_NUMBER);

        $this->assertInstanceOf(OrderResponse::class, $authResponse);

        sleep(3);

        $cancelResponse = $this->bankIDService->cancelOrder($authResponse->orderRef);

        $this->assertTrue($cancelResponse);
    }

}
