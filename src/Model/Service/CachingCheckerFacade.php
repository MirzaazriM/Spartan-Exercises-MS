<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 8/11/18
 * Time: 3:32 PM
 */

namespace Model\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;

class CachingCheckerFacade
{

    /**
     * Check if there is already cached response
     *
     * @param $identifier
     * @return mixed
     */
    public function checkCachedResponses($identifier, $cache){
        // get cached identifier if exists
        $ids_identifier = $cache->getItem($identifier);

        // loop through cached responses and check if there is an identifier match
        $dir = "../src/Model/Service/cached_files/*";
        foreach(glob($dir) as $file)
        {
            $filenamePartOne = substr($file, 34);
            $position = strpos($filenamePartOne, '.');
            $filename = substr($filenamePartOne, 0, $position);

            // check if filename is equal to the given ids
            if($ids_identifier->getKey() == $filename){
                // if yes get cached data
                $cacheItem = $cache->getItem('raw.exercises');
                $data = $cacheItem->get();
            }
        }

        // return cached data if exists
        return $data;
    }


    /**
     * Cache data
     *
     * @param $identifier
     * @param $cache
     * @param $data
     */
    public function setCache($identifier, $cache, $data){
        $values = array(
            'id' => $identifier,
            'raw.exercises' => $data,
        );
        $cache->warmUp($values);
    }

}