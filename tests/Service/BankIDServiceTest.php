<?php

namespace Dimafe6\BankID\Service;

use Dimafe6\BankID\Model\CollectResponse;
use Dimafe6\BankID\Model\OrderResponse;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class BankIDServiceTest extends TestCase
{
    const TEST_PERSONAL_NUMBER = '199202271434';

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|BankIDService $bankIDService
     */
    private $bankIDService;

    /** @var BankIDService $originalBankIDService */
    private $originalBankIDService;

    private $expectedOrderResponse = null;

    public function setUp()
    {
        $this->originalBankIDService = new BankIDService(
            'https://appapi2.test.bankid.com/rp/v4?wsdl',
            ['local_cert' => __DIR__ . '/../bankId.pem'],
            false
        );

        $this->bankIDService = $this->getMockBuilder(BankIDService::class)
            ->setMethods(['soapCall'])
            ->setConstructorArgs([
                'https://appapi2.test.bankid.com/rp/v4?wsdl',
                [],
                false
            ])
            ->setMockClassName('TestBankIDService')
            ->getMock();

        $this->expectedOrderResponse = new \StdClass();
        $this->expectedOrderResponse->orderRef = '11111111-1111-1111-1111-111111111111';
        $this->expectedOrderResponse->autoStartToken = '22222222-2222-2222-2222-222222222222';
    }

    public function testMock()
    {
        $this->assertInstanceOf('TestBankIDService', $this->bankIDService);
    }

    public function callProvider()
    {
        return [
            "valid1"   => [
                BankIDService::METHOD_AUTH,
                [
                    'parameters' => [
                        'personalNumber' => self::TEST_PERSONAL_NUMBER
                    ],
                ]
            ],
            "valid2"   => [
                BankIDService::METHOD_AUTH,
                []
            ],
            "invalid1" => [
                BankIDService::METHOD_AUTH,
                'error'
            ],
            "invalid2" => [
                'NotExistsMethodName',
                []
            ]
        ];
    }

    /**
     * @dataProvider callProvider
     * @param string $method
     * @param array $parameters
     */
    public function testCallMethod($method, $parameters)
    {
        if ($parameters === 'error') {
            $this->expectException(\InvalidArgumentException::class);
        }

        if ($method === 'NotExistsMethodName') {
            $this->expectException(OutOfBoundsException::class);
        }

        $result = false;

        try {
            $result = $this->originalBankIDService->call($method, $parameters);
        } catch (\SoapFault $e) {
            if ("ALREADY_IN_PROGRESS" !== $e->getMessage()) {
                $this->fail($e->getMessage());
            }
        }

        if (false === $result) {
            $this->assertTrue(true);
        } else {
            $this->assertInstanceOf('\StdClass', $result);
        }
    }

    public function testAuthResponse()
    {
        $authParameters = [
            'parameters' => [
                'personalNumber' => self::TEST_PERSONAL_NUMBER
            ],
        ];

        $this->bankIDService
            ->expects($this->any())
            ->method('soapCall')
            ->with(
                $this->logicalOr(
                    $this->equalTo(BankIDService::METHOD_AUTH),
                    $this->equalTo($authParameters)
                )
            )
            ->will($this->returnValue($this->expectedOrderResponse));

        $authResponse = $this->bankIDService->getAuthResponse(self::TEST_PERSONAL_NUMBER);

        $this->assertInstanceOf(OrderResponse::class, $authResponse);
        $this->assertEquals($this->expectedOrderResponse->orderRef, $authResponse->orderRef);
        $this->assertEquals($this->expectedOrderResponse->autoStartToken, $authResponse->autoStartToken);

        return $authResponse;
    }

    /**
     * @depends testMock
     * @return OrderResponse
     */
    public function testSignResponse()
    {
        $options = [
            'parameters' => [
                'personalNumber'     => self::TEST_PERSONAL_NUMBER,
                'userVisibleData'    => base64_encode('userVisibleData'),
                'userNonVisibleData' => base64_encode('userNonVisibleData')
            ]
        ];

        $this->bankIDService
            ->expects($this->any())
            ->method('soapCall')
            ->with(
                $this->logicalOr(
                    $this->equalTo(BankIDService::METHOD_SIGN),
                    $this->equalTo($options)
                )
            )
            ->will($this->returnValue($this->expectedOrderResponse));

        $signResponse = $this->bankIDService->getSignResponse(
            self::TEST_PERSONAL_NUMBER,
            'userVisibleData',
            'userNonVisibleData'
        );

        $this->assertInstanceOf(OrderResponse::class, $signResponse);
        $this->assertEquals($this->expectedOrderResponse->orderRef, $signResponse->orderRef);
        $this->assertEquals($this->expectedOrderResponse->autoStartToken, $signResponse->autoStartToken);

        return $signResponse;
    }

    /**
     * @depends testAuthResponse
     * @param $authResponse
     * @return CollectResponse
     */
    public function testCollectResponse($authResponse)
    {
        $this->assertTrue($authResponse instanceof OrderResponse);

        $userInfo = new \StdClass();

        $userInfo->givenName = 'Name';
        $userInfo->surname = 'Surname';
        $userInfo->name = 'Name Surname';
        $userInfo->personalNumber = self::TEST_PERSONAL_NUMBER;
        $userInfo->notBefore = '2017-06-16T00:00:00.000+02:00';
        $userInfo->notAfter = '2019-06-16T23:59:59.000+02:00';
        $userInfo->ipAddress = '127.0.0.1';

        $expectedResponse = new \StdClass();
        $expectedResponse->progressStatus = CollectResponse::PROGRESS_STATUS_COMPLETE;
        $expectedResponse->signature = 'PD94bWwgdm...';
        $expectedResponse->userInfo = $userInfo;
        $expectedResponse->ocspResponse = 'MIIHegoBA...';

        $this->bankIDService
            ->expects($this->any())
            ->method('soapCall')
            ->with(
                $this->logicalOr(
                    $this->equalTo(BankIDService::METHOD_COLLECT),
                    $this->equalTo(['orderRef' => $authResponse->orderRef])
                )
            )
            ->will($this->returnValue($expectedResponse));

        $collectResponse = $this->bankIDService->collectResponse($authResponse->orderRef);

        $this->assertInstanceOf(CollectResponse::class, $collectResponse);

        if (!$collectResponse instanceof CollectResponse) {
            $this->fail('Error collect response');
        }

        $this->assertEquals(CollectResponse::PROGRESS_STATUS_COMPLETE, $collectResponse->progressStatus);
        $this->assertEquals('PD94bWwgdm...', $collectResponse->signature);
        $this->assertNotEquals(null, $collectResponse->userInfo);
        $this->assertEquals('Name', $collectResponse->userInfo->givenName);
        $this->assertEquals('Surname', $collectResponse->userInfo->surname);
        $this->assertEquals('Name Surname', $collectResponse->userInfo->name);
        $this->assertEquals(self::TEST_PERSONAL_NUMBER, $collectResponse->userInfo->personalNumber);
        $this->assertEquals('2017-06-16T00:00:00.000+02:00', $collectResponse->userInfo->notBefore);
        $this->assertEquals('2019-06-16T23:59:59.000+02:00', $collectResponse->userInfo->notAfter);
        $this->assertEquals('127.0.0.1', $collectResponse->userInfo->ipAddress);
        $this->assertEquals('MIIHegoBA...', $collectResponse->ocspResponse);

        return $collectResponse;
    }
}
