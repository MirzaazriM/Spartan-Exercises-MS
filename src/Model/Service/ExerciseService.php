<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/27/18
 * Time: 6:52 PM
 */

namespace Model\Service;

use Component\LinksConfiguration;
use Model\Core\Helper\Monolog\MonologSender;
use Model\Entity\Exercise;
use Model\Entity\Media;
use Model\Entity\MediaCollection;
use Model\Entity\NamesCollection;
use Model\Entity\ResponseBootstrap;
use Model\Mapper\ExerciseMapper;
use Model\Service\Facade\GetExerciseFacade;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\Cache\Simple\FilesystemCache;

class ExerciseService extends LinksConfiguration
{

    private $exerciseMapper;
    private $configuration;
    private $monologHelper;

    public function __construct(ExerciseMapper $exerciseMapper)
    {
        $this->exerciseMapper = $exerciseMapper;
        $this->configuration = $exerciseMapper->getConfiguration();
        $this->monologHelper = new MonologSender();
    }


    /**
     * Get single exercise
     *
     * @param int $id
     * @param string $lang
     * @param string $state
     * @return ResponseBootstrap
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getExercise(int $id, string $lang, string $state):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new Exercise();
            $entity->setId($id);
            $entity->setLang($lang);
            $entity->setState($state);

            // get data from database
            $res = $this->exerciseMapper->getExercise($entity);
            $id = $res->getId();

            // get exercise tag ids
            $tagIds = $res->getTags();

            // call tags MS for tags data
            $client = new \GuzzleHttp\Client();
            $result = $client->request('GET', $this->configuration['tags_url'] . '/tags/ids?lang=' .$lang. '&state=R' . '&ids=' .$tagIds, []);
           // die($this->configuration['tags_url'] . '/tags/ids?lang=' .$lang. '&state=R' . '&ids=' .$tagIds);
            $tags = $result->getBody()->getContents();

            // check data and set response
            if(isset($id)){
                $response->setStatus(200);
                $response->setMessage('Success');
                $response->setData([
                    'id' => $res->getId(),
                    'name' => $res->getName(),
                    'hardness' => $res->getHardness(),
                    'raw_name' => $res->getRawName(),
                    'thumbnail' => $res->getThumbnail(),
                    'formats' => $res->getFormats(),
                    'muscles_involved' => $res->getMuscles(),
                    'version' => $res->getVersion(),
                    'tags' => json_decode($tags)
                ]);
            }else {
                $response->setStatus(204);
                $response->setMessage('No content');
            }

            // return data
            return $response;

        }catch(\Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Get exercise service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Get list of exercises
     *
     * @param int $from
     * @param int $limit
     * @return ResponseBootstrap
     */
    public function getListOfExercises(int $from, int $limit, string $state = null):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new Exercise();
            $entity->setFrom($from);
            $entity->setLimit($limit);
            $entity->setState($state);

            // call mapper for data
            $data = $this->exerciseMapper->getList($entity);

            // set response according to data content
            if(!empty($data)){
                $response->setStatus(200);
                $response->setMessage('Success');
                $response->setData(
                    $data
                );
            }else {
                $response->setStatus(204);
                $response->setMessage('No content');
            }

            // return data
            return $response;

        }catch (\Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Get exercises list service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Get exercises by paramaetars
     *
     * @param string $lang
     * @param string|null $app
     * @param string|null $like
     * @param string $state
     * @return ResponseBootstrap
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getExercises(string $lang, string $state,  string $app = null, string $like = null):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create facade and call its functions for data
            $facade = new GetExerciseFacade($lang, $app, $like, $state, $this->exerciseMapper);
            $res = $facade->handleExercises();

            $data = [];

            if(gettype($res) === 'object'){
                // convert collection to array
                // $data = [];
                for($i = 0; $i < count($res); $i++){
                    $data[$i]['id'] = $res[$i]->getId();
                    $data[$i]['name'] = $res[$i]->getName();
                    $data[$i]['hardness'] = $res[$i]->getHardness();
                    $data[$i]['thumbnail'] = $res[$i]->getThumbnail();
                    $data[$i]['formats'] = $res[$i]->getFormats();
                    $data[$i]['muscles_involved'] = $res[$i]->getMuscles();
                    $data[$i]['version'] = $res[$i]->getVersion();

                    // get tag ids
                    $tagIds = $res[$i]->getTags();

                    // call tags MS for tags data
                    $client = new \GuzzleHttp\Client();
                    $result = $client->request('GET', $this->configuration['tags_url'] . '/tags/ids?lang=' .$lang. '&state=R' . '&ids=' .$tagIds, []);
                    $tags = $result->getBody()->getContents();

                    $data[$i]['tags'] = json_decode($tags);
                }
            }else if(gettype($res) === 'array'){
                $data = $res;
            }


            // Check Data and Set Response
            if(!empty($data)){
                $response->setStatus(200);
                $response->setMessage('Success');
                $response->setData(
                    $data
                );
            }else {
                $response->setStatus(204);
                $response->setMessage('No content');
            }

            // return response
            return $response;

        }catch(\Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Get exercises service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Get exercises by ids
     *
     * @param array $ids
     * @param string $lang
     * @param string $state
     * @return ResponseBootstrap
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getExercisesById(array $ids, string $lang, string $state):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // set ids as a string
            $identifier = implode(',', $ids);

            // create cashing adapter
            $cache = new PhpArrayAdapter(
            // single file where values are cached
                __DIR__ . '/cached_files/' . $identifier . '.cache',
                // a backup adapter, if you set values after warmup
                new FilesystemAdapter()
            );

            // get identifier
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

            // if cached data is empty, get data from the database
            if(empty($data)) {
                // set entity
                $entity = new Exercise();
                $entity->setIds($ids);
                $entity->setLang($lang);
                $entity->setState($state);

                // get data from database
                $res = $this->exerciseMapper->getExercisesById($entity);

                // convert collection to array
                $data = [];
                for ($i = 0; $i < count($res); $i++) {
                    $data[$i]['id'] = $res[$i]->getId();
                    $data[$i]['name'] = $res[$i]->getName();
                    $data[$i]['raw_name'] = $res[$i]->getRawName();
                    $data[$i]['hardness'] = $res[$i]->getHardness();
                    $data[$i]['thumbnail'] = $res[$i]->getThumbnail();
                    $data[$i]['formats'] = $res[$i]->getFormats();
                    $data[$i]['muscles_involved'] = $res[$i]->getMuscles();
                    $data[$i]['version'] = $res[$i]->getVersion();

                    // get tags
                    $tagIds = $res[$i]->getTags();

                    // create guzzle client and call MS for data
                    $client = new \GuzzleHttp\Client();
                    $result = $client->request('GET', $this->configuration['tags_url'] . '/tags/ids?lang=' . $lang . '&state=R' . '&ids=' . $tagIds, []);
                    $tags = $result->getBody()->getContents();

                    $data[$i]['tags'] = json_decode($tags);
                }

                // cache data
                $values = array(
                    'id' => $identifier,
                    'raw.exercises' => $data,
                );
                $cache->warmUp($values);
             }

            // check data and set response
            if(!empty($data)){
                $response->setStatus(200);
                $response->setMessage('Success');
                $response->setData(
                    $data
                );
            }else {
                $response->setStatus(204);
                $response->setMessage('No content');
            }

            // return data
            return $response;

        }catch(\Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Get exercises by ids service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Delete exercise by id
     *
     * @param int $id
     * @return ResponseBootstrap
     */
    public function deleteExercise(int $id):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new Exercise();
            $entity->setId($id);

            // get response from database
            $res = $this->exerciseMapper->deleteExercise($entity)->getResponse();

            // check data and set response
            if($res[0] == 200){
                $response->setStatus(200);
                $response->setMessage('Success');
            }else {
                $response->setStatus(304);
                $response->setMessage('Not modified');
            }

            // return response
            return $response;

        }catch(\Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Delete exercise service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Delete exercise cache
     *
     * @return ResponseBootstrap
     */
    public function deleteCache():ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // delete all cached responses
            $dir = glob("../src/Model/Service/cached_files/*");
            // $files = glob('cached_responses/*');
            foreach($dir as $file){
                if(is_file($file))
                    unlink($file);
            }

            // set response
            $response->setStatus(200);
            $response->setMessage('Success');

            // return response
            return $response;

        }catch (\Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Delete cache service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Release exercise
     *
     * @param int $id
     * @return ResponseBootstrap
     */
    public function releaseExercise(int $id):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new Exercise();
            $entity->setId($id);

            // get response from database
            $res = $this->exerciseMapper->releaseExercise($entity)->getResponse();

            // check data and set response
            if($res[0] == 200){
                $response->setStatus(200);
                $response->setMessage('Success');
            }else {
                $response->setStatus(304);
                $response->setMessage('Not modified');
            }

            // return response
            return $response;

        }catch(\Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Release exercise service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Add exercise
     *
     * @param string $hardness
     * @param string $muscles
     * @param string $thumbnail
     * @param string $rawName
     * @param NamesCollection $names
     * @param MediaCollection $mediaCollection
     * @param array $tags
     * @return ResponseBootstrap
     */
    public function createExercise(string $hardness, string $muscles, string $thumbnail, string $rawName, NamesCollection $names, MediaCollection $mediaCollection, array $tags):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new Exercise();
            $entity->setHardness($hardness);
            $entity->setMuscles($muscles);
            $entity->setThumbnail($thumbnail);
            $entity->setName($rawName);
            $entity->setTags($tags);
            $entity->setNames($names);
            $entity->setMedia($mediaCollection);

            // get response from database
            $res = $this->exerciseMapper->createExercise($entity)->getResponse();

            // check data and set response
            if($res[0] == 200){
                $response->setStatus(200);
                $response->setMessage('Success');
            }else {
                $response->setStatus(304);
                $response->setMessage('Not modified');
            }

            // return response
            return $response;

        }catch(\Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Create exercise service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Edit exercise
     *
     * @param int $id
     * @param string $hardness
     * @param string $muscles
     * @param string $thumbnail
     * @param string $rawName
     * @param NamesCollection $names
     * @param MediaCollection $mediaCollection
     * @param array $tags
     * @return ResponseBootstrap
     */
    public function editExercise(int $id, string $hardness, string $muscles, string $thumbnail, string $rawName, NamesCollection $names, MediaCollection $mediaCollection, array $tags):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new Exercise();
            $entity->setId($id);
            $entity->setHardness($hardness);
            $entity->setMuscles($muscles);
            $entity->setThumbnail($thumbnail);
            $entity->setName($rawName);
            $entity->setTags($tags);
            $entity->setNames($names);
            $entity->setMedia($mediaCollection);

            // get response from database
            $res = $this->exerciseMapper->editExercise($entity)->getResponse();

            // check data and set response
            if($res[0] == 200){
                $response->setStatus(200);
                $response->setMessage('Success');
            }else {
                $response->setStatus(304);
                $response->setMessage('Not modified');
            }

            // return response
            return $response;

        }catch(\Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Edit exercise service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Get total number of exercises
     *
     * @return ResponseBootstrap
     */
    public function getTotal():ResponseBootstrap {
        try {
            // create response object
            $response = new ResponseBootstrap();

            // call mapper for data
            $data = $this->exerciseMapper->getTotal();

            // check data and set response
            if(!empty($data)){
                $response->setStatus(200);
                $response->setMessage('Success');
                $response->setData([
                    $data
                ]);
            }else {
                $response->setStatus(204);
                $response->setMessage('No content');
            }

            // return data
            return $response;

        }catch(\Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Get total exercises service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }
}