<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/27/18
 * Time: 6:52 PM
 */

namespace Application\Controller;

use Model\Entity\Media;
use Model\Entity\MediaCollection;
use Model\Entity\Names;
use Model\Entity\NamesCollection;
use Model\Entity\ResponseBootstrap;
use Model\Service\ExerciseService;
use Symfony\Component\HttpFoundation\Request;

class ExerciseController
{

    private $exerciseService;

    public function __construct(ExerciseService $exerciseService)
    {
        $this->exerciseService = $exerciseService;
    }


    /**
     * Get exercise
     *
     * @param Request $request
     * @return ResponseBootstrap
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(Request $request):ResponseBootstrap {
        // get data
        $id = $request->get('id');
        $lang = $request->get('lang');
        $state = $request->get('state');

        // create response object
        $response = new ResponseBootstrap();

        // check if parameters are present
        if(isset($id) && isset($lang) && isset($state)){
            return $this->exerciseService->getExercise($id, $lang, $state);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return data
        return $response;
    }


    /**
     * Get list of all exercises
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function getList(Request $request):ResponseBootstrap {
        // get data
        $from = $request->get('from');
        $limit = $request->get('limit');
        $state = $request->get('state');

        // create response object
        $response = new ResponseBootstrap();

        // check if parameters are present
        if(isset($from) && isset($limit)){ //  && isset($state)
            return $this->exerciseService->getListOfExercises($from, $limit, $state);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return data
        return $response;
    }


    /**
     * Get exercises by parameters
     *
     * @param Request $request
     * @return ResponseBootstrap
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getExercises(Request $request):ResponseBootstrap {
        // get data
        $lang = $request->get('lang');
        $app = $request->get('app');
        $like = $request->get('like');
        $state = $request->get('state');

        // create response object
        $response = new ResponseBootstrap();

        // check if data is present
        if(!empty($lang) && !empty($state)){
            return $this->exerciseService->getExercises($lang, $state, $app, $like);
        }else{
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return data
        return $response;
    }


    /**
     * Get exercises by ids
     *
     * @param Request $request
     * @return ResponseBootstrap
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getIds(Request $request):ResponseBootstrap {
        // get data
        $ids = $request->get('ids');
        $lang = $request->get('lang');
        $state = $request->get('state');

        // convert ids string to array
        $ids = explode(',', $ids);

        // create response object
        $response = new ResponseBootstrap();

        // check if data is present
        if(!empty($ids) && !empty($lang) && !empty($state)){
            return $this->exerciseService->getExercisesById($ids, $lang, $state);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return data
        return $response;
    }


    /**
     * Delete exercise
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function delete(Request $request):ResponseBootstrap {
        // get id
        $id = $request->get('id');

        // create response object
        $response = new ResponseBootstrap();

        // check if id is present
        if(isset($id)){
            return $this->exerciseService->deleteExercise($id);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return response
        return $response;
    }


    /**
     * Delete exercises cache
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function deleteCache(Request $request):ResponseBootstrap {
        // call service function
        return $this->exerciseService->deleteCache();
    }



    /**
     * Release exercise
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function postRelease(Request $request):ResponseBootstrap {
        // get data
        $data = json_decode($request->getContent(), true);
        $id = $data['id'];

        // create response object in case of failure
        $response = new ResponseBootstrap();

        // check if data is set
        if(isset($id)){
            return $this->exerciseService->releaseExercise($id);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return response
        return $response;
    }


    /**
     * Add exercise
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function post(Request $request):ResponseBootstrap {
        // get data
        $data = json_decode($request->getContent(), true);
        $hardness = $data['hardness'];
        $muscles = $data['muscles_involved'];
        $thumbnail = $data['thumbnail'];
        $rawName = $data['raw_name'];
        $names = $data['names'];
        $tags = $data['tags'];
        $media = $data['media'];

        // create names collection
        $namesCollection = new NamesCollection();
        // set names into names collection
        foreach($names as $name){
            $temp = new Names();
            $temp->setName($name['name']);
            $temp->setLang($name['lang']);

            $namesCollection->addEntity($temp);
        }

        // create media collection
        $mediaCollection = new MediaCollection();
        // set media into media collection
        foreach($media as $med){
            $temp = new Media();
            $temp->setType($med['type']);
            $temp->setSource($med['source']);

            $mediaCollection->addEntity($temp);
        }

        // create response object
        $response = new ResponseBootstrap();

        // check if data is set
        if(isset($hardness) && isset($thumbnail) && isset($muscles) && isset($rawName) && isset($namesCollection) && isset($mediaCollection) && isset($tags)){
            return $this->exerciseService->createExercise($hardness, $muscles, $thumbnail, $rawName, $namesCollection, $mediaCollection, $tags);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return response
        return $response;
    }


    /**
     * Edit exercise
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function put(Request $request):ResponseBootstrap {
        // get data
        $data = json_decode($request->getContent(), true);
        $id = $data['id'];
        $hardness = $data['hardness'];
        $muscles = $data['muscles_involved'];
        $thumbnail = $data['thumbnail'];
        $rawName = $data['raw_name'];
        $names = $data['names'];
        $tags = $data['tags'];
        $media = $data['media'];

        // create names collection
        $namesCollection = new NamesCollection();
        // set names into names collection
        foreach($names as $name){
            $temp = new Names();
            $temp->setName($name['name']);
            $temp->setLang($name['lang']);

            $namesCollection->addEntity($temp);
        }

        // create media collection
        $mediaCollection = new MediaCollection();
        // set media into media collection
        foreach($media as $med){
            $temp = new Media();
            $temp->setType($med['type']);
            $temp->setSource($med['source']);

            $mediaCollection->addEntity($temp);
        }

        // create response object
        $response = new ResponseBootstrap();

        // check if data is set
        if(isset($id) && isset($hardness) && isset($thumbnail) && isset($muscles) && isset($rawName) && isset($namesCollection) && isset($mediaCollection) && isset($tags)){
            return $this->exerciseService->editExercise($id, $hardness, $muscles, $thumbnail, $rawName, $namesCollection, $mediaCollection, $tags);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return response
        return $response;
    }


    /**
     * Get total number of exercises
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function getTotal(Request $request):ResponseBootstrap {
        // call service for response
        return $this->exerciseService->getTotal();
    }

}