<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 8/13/18
 * Time: 4:30 PM
 */

namespace Model\Core\Helper\CacheDeleter;


class DeleteCache
{

    private $configuration;

    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Delete cached files
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteCacheAtParentMicroservices() {
        // delete all cached exercises responses
        $dir = glob("../src/Model/Service/cached_files/*");
        // $files = glob('cached_responses/*');
        foreach($dir as $file){
            if(is_file($file))
                unlink($file);
        }

        // delete cache at workouts MS
        $client = new \GuzzleHttp\Client();
        $client->request('DELETE', $this->configuration['workouts_url'] . '/workouts/cache', []);
    }
}