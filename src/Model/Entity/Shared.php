<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/29/18
 * Time: 8:27 AM
 */

namespace Model\Entity;


class Shared
{

    private $response = [];

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @param array $response
     */
    public function setResponse(array $response): void
    {
        $this->response = $response;
    }



}