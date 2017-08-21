<?php

namespace Dimafe6\BankID\Service;

use Dimafe6\BankID\Model\CollectResponse;
use Dimafe6\BankID\Model\OrderResponse;
use PHPUnit\Framework\TestCase;

class BankIDServiceTest extends TestCase
{
    /**
     * @var BankIDService
     */
    private $bankIDService;

    /**
     * @var string
     */
    private $personalNumber;

    public function setUp()
    {
        $this->bankIDService = new BankIDService(
            'https://appapi2.test.bankid.com/rp/v4?wsdl',
            ['local_cert' => __DIR__ . '/../bankId.pem'],
            false
        );
        $this->personalNumber = getenv('personalNumber');
        if (empty($this->personalNumber)) {
            $this->fail("Need set personalNumber variable in phpunit.xml");
        }
    }

    public function testConstructor()
    {
        $this->assertTrue($this->bankIDService instanceof BankIDService);
        $this->assertTrue(!empty($this->personalNumber));
    }

    /**
     * @depends testConstructor
     * @return OrderResponse
     */
    public function testSignResponse()
    {
        $signResponse = $this->bankIDService->getSignResponse($this->personalNumber, 'Test user data');
        $this->assertTrue($signResponse instanceof OrderResponse);

        return $signResponse;
    }

    /**
     * @depends testSignResponse
     * @param $signResponse
     * @return CollectResponse
     */
    public function testCollectSignResponse($signResponse)
    {
        $this->assertTrue($signResponse instanceof OrderResponse);

        fwrite(STDOUT, "\n");

        $attemps = 0;

        do {
            fwrite(STDOUT, "Waiting 5sec for confirmation from BankID mobile application...\n");
            sleep(5);
            $collectResponse = $this->bankIDService->collectResponse($signResponse->orderRef);
            $this->assertTrue($collectResponse instanceof CollectResponse);
            if (!$collectResponse instanceof CollectResponse) {
                $this->fail('Error collect response');
            }
            $attemps++;
        } while ($collectResponse->progressStatus !== CollectResponse::PROGRESS_STATUS_COMPLETE && $attemps <= 12);

        $this->assertEquals(CollectResponse::PROGRESS_STATUS_COMPLETE, $collectResponse->progressStatus);

        return $collectResponse;
    }

    public function testAuthResponse()
    {
        $authResponse = $this->bankIDService->getAuthResponse($this->personalNumber);
        $this->assertTrue($authResponse instanceof OrderResponse);

        return $authResponse;
    }

    /**
     * @depends testAuthResponse
     * @param $authResponse
     * @return CollectResponse
     */
    public function testAuthSignResponse($authResponse)
    {
        $this->assertTrue($authResponse instanceof OrderResponse);

        fwrite(STDOUT, "\n");

        $attemps = 0;

        do {
            fwrite(STDOUT, "Waiting 5sec for confirmation from BankID mobile application...\n");
            sleep(5);
            $collectResponse = $this->bankIDService->collectResponse($authResponse->orderRef);
            $this->assertTrue($collectResponse instanceof CollectResponse);
            if (!$collectResponse instanceof CollectResponse) {
                $this->fail('Error collect response');
            }
            $attemps++;
        } while ($collectResponse->progressStatus !== CollectResponse::PROGRESS_STATUS_COMPLETE && $attemps <= 12);

        $this->assertEquals(CollectResponse::PROGRESS_STATUS_COMPLETE, $collectResponse->progressStatus);

        return $collectResponse;
    }
}
