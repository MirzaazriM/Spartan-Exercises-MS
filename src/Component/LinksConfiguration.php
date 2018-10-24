<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 7/16/18
 * Time: 11:04 AM
 */

namespace Component;


class LinksConfiguration
{

    private $config = 'LOCAL';
    private $localTagsUrl = 'http://spartan-tags:8888';
    private $onlineTagsUrl = '12.456.43.54';

    public function __construct()
    {
    }

    public function getUrl():String {

        if($this->config == 'LOCAL'){
            return $this->localTagsUrl;
        }else {
            return $this->onlineTagsUrl;
        }
    }
}