<?php

namespace Dimafe6\BankID\Model;

use Psr\Http\Message\ResponseInterface;

/**
 * Class AbstractResponseModel
 *
 * Constructor convert ResponseInterface to object properties
 *
 * @category PHP
 * @package  Dimafe6\BankID\Model
 * @author   Dmytro Feshchenko <dimafe2000@gmail.com>
 */
class AbstractResponseModel
{
    /**
     * AbstractResponseModel constructor.
     * @param ResponseInterface|null $response
     */
    public function __construct(ResponseInterface $response = null)
    {
        if (null !== $response) {
            $responseText  = $response->getBody()->getContents();
            $responseArray = (array)json_decode($responseText);

            foreach ($responseArray as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}
