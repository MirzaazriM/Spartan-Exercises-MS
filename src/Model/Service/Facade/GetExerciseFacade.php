<?php

namespace Model\Service\Facade;

use Model\Entity\Exercise;
use Model\Entity\ExerciseCollection;
use Model\Mapper\ExerciseMapper;

class GetExerciseFacade
{

    private $lang;
    private $app;
    private $like;
    private $state;
    private $exerciseMapper;
    private $configuration;

    public function __construct(string $lang, string $app = null, string $like = null, string $state, ExerciseMapper $exerciseMapper) {
        $this->lang = $lang;
        $this->app = $app;
        $this->like = $like;
        $this->state = $state;
        $this->exerciseMapper = $exerciseMapper;
        $this->configuration = $exerciseMapper->getConfiguration();
    }


    /**
     * Handle exercise data
     *
     * @return mixed|ExerciseCollection|null
     */
    public function handleExercises() {
        $data = null;

        // Calling By App
        if(!empty($this->app)){
            $data = $this->getExercisesByApp();
        }
        // Calling by Search
        else if(!empty($this->like)){
            $data = $this->searchExercises();
        }
        // Calling by State
        else{
            $data = $this->getExercises();
        }

        // return data
        return $data;
    }


    /**
     * Get exercises
     *
     * @return ExerciseCollection
     */
    public function getExercises() {
        // create entity and set its values
        $entity = new Exercise();
        $entity->setLang($this->lang);
        $entity->setState($this->state);

        // call mapper for data
        $collection = $this->exerciseMapper->getExercises($entity);

        // return data
        return $collection;
    }


    /**
     * Get exercises for a given app
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getExercisesByApp() {
        // call apps MS for data
        $client = new \GuzzleHttp\Client();
        $result = $client->request('GET', $this->configuration['apps_url'] . '/apps/data?app=' . $this->app . '&lang=' . $this->lang . '&state=' . $this->state . '&type=exercises', []);
        $data = json_decode($result->getBody()->getContents(), true);

        // return data
        return $data;
    }


    /**
     * Get exercises by search term
     *
     * @return ExerciseCollection
     */
    public function searchExercises():ExerciseCollection {

        // create entity and set its values
        $entity = new Exercise();
        $entity->setLang($this->lang);
        $entity->setState($this->state);
        $entity->setName($this->like);

        // call mapper for data
        $data = $this->exerciseMapper->searchExercises($entity);

        // return data
        return $data;
    }

}