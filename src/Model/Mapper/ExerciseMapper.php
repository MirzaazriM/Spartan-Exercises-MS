<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/27/18
 * Time: 6:52 PM
 */

namespace Model\Mapper;

use Model\Core\Helper\CacheDeleter\DeleteCache;
use Model\Entity\Exercise;
use Model\Entity\ExerciseCollection;
use Model\Entity\Shared;
use PDO;
use PDOException;
use Component\DataMapper;
use Symfony\Component\Cache\Simple\FilesystemCache;

class ExerciseMapper extends DataMapper
{

    /**
     * Get configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }


    /**
     * Fetch single exercise
     *
     * @param Exercise $exercise
     * @return Exercise
     */
    public function getExercise(Exercise $exercise):Exercise {

        // create response object
        $response = new Exercise();

        try {
            // set database instructions
            $sql = "SELECT
                      e.id AS id,
                      e.hardness AS hardness,
                      e.muscles_invovled AS muscles,
                      e.thumbnail AS thumbnail,
                      e.raw_name AS raw_name,
                      e.state AS state,
                      e.version AS version,
                      en.name AS name,
                      en.language AS language,
                      GROUP_CONCAT(DISTINCT em.id) AS media_ids,
                      GROUP_CONCAT(DISTINCT et.tag_id) AS tags
                    FROM exercises AS e
                    LEFT JOIN exercies_name AS en ON e.id = en.exercise_parent 
                    LEFT JOIN exercise_tag AS et ON e.id = et.exercise_parent
                    LEFT JOIN exercise_media AS em ON e.id = em.exercise_parent
                    WHERE e.id = ?
                    AND en.language = ?
                    AND e.state = ?";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $exercise->getId(),
                $exercise->getLang(),
                $exercise->getState()
            ]);

            // fetch data
            $data = $statement->fetch();

            // extract media ids
            $mediaIds = $data['media_ids'];

            if(isset($mediaIds)){
                // get media data
                $sql = 'SELECT * FROM exercise_media WHERE id IN (' .$mediaIds . ')';
                $statementMedia = $this->connection->prepare($sql);
                $statementMedia->execute();

                // fetch media data
                $media = $statementMedia->fetchAll(PDO::FETCH_ASSOC);

                $formats = [];
                $format = null;
                // loop through formats and set full links
                foreach($media as $med){
                    $format['id'] = $med['id'];
                    $format['type'] = $med['type'];
                    $format['source'] = $this->configuration['asset_link'] . $med['source'];

                    // add to formats array
                    array_push($formats, $format);
                }
                $response->setFormats($formats);
            }

            // set exercise values
            $response->setId($data['id']);
            $response->setName($data['name']);
            $response->setLang($data['language']);
            $response->setRawName($data['raw_name']);
            $response->setState($data['state']);
            $response->setHardness($data['hardness']);
            $response->setThumbnail($this->configuration['asset_link'] . $data['thumbnail']);
            $response->setMuscles($this->configuration['asset_link'] . $data['muscles']);
            $response->setVersion($data['version']);
            $response->setTags($data['tags']);

        }catch(PDOException $e){
            // send monolog record in case of failure
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Get exercise mapper: " . $e->getMessage());
        }

        // return data
        return $response;
    }


    /**
     * Fetch core exercises data
     *
     * @param Exercise $exercise
     * @return array
     */
    public function getList(Exercise $exercise){

        try {

            // get state
            $state = $exercise->getState();

            // check if state is set and set query
            if($state === null or $state === ''){
                // set database instructions
                $sql = "SELECT
                           e.id,
                           e.hardness,
                           e.muscles_invovled,
                           e.thumbnail,
                           e.raw_name,
                           e.state,
                           e.version,
                           en.name,
                           en.language
                        FROM exercises AS e 
                        LEFT JOIN exercies_name AS en ON e.id = en.exercise_parent
                        /* WHERE en.language = 'en' */
                        LIMIT :from,:limit";
                // set statement
                $statement = $this->connection->prepare($sql);
                // set from and limit as core variables
                $from = $exercise->getFrom();
                $limit = $exercise->getLimit();
                // bind parametars
                $statement->bindParam(':from', $from, PDO::PARAM_INT);
                $statement->bindParam(':limit', $limit, PDO::PARAM_INT);
                // execute query
                $statement->execute();

            }else {
                // set database instructions
                $sql = "SELECT
                           e.id,
                           e.hardness,
                           e.muscles_invovled,
                           e.thumbnail,
                           e.raw_name,
                           e.state,
                           e.version,
                           en.name,
                           en.language
                        FROM exercises AS e 
                        LEFT JOIN exercies_name AS en ON e.id = en.exercise_parent
                        WHERE en.language = 'en' AND e.state = :state
                        LIMIT :from,:limit";
                // set statement
                $statement = $this->connection->prepare($sql);
                // set from and limit as core variables
                $from = $exercise->getFrom();
                $limit = $exercise->getLimit();
                $state = $exercise->getState();
                // bind parametars
                $statement->bindParam(':from', $from, PDO::PARAM_INT);
                $statement->bindParam(':limit', $limit, PDO::PARAM_INT);
                $statement->bindParam(':state', $state);
                // execute query
                $statement->execute();

            }


            // set data
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);

            // create formatted data variable
            $formattedData = [];

            // loop through data and add link prefixes
            foreach($data as $item){
                $item['muscles_invovled'] = $this->configuration['asset_link'] . $item['muscles_invovled'];
                $item['thumbnail'] = $this->configuration['asset_link'] . $item['thumbnail'];

                // add formatted item in new array
                array_push($formattedData, $item);
            }

        }catch (PDOException $e){
            $formattedData = [];
            // send monolog record in case of failure
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Get exercises list mapper: " . $e->getMessage());
        }

        // return data
        return $formattedData;
    }


    /**
     * Fetch exercises
     *
     * @param Exercise $exercise
     * @return ExerciseCollection
     */
    public function getExercises(Exercise $exercise):ExerciseCollection {

        // create response object
        $exerciseCollection = new ExerciseCollection();

        try {
            // set database instructions
            $sql = "SELECT
                      e.id AS id,
                      e.hardness AS hardness,
                      e.muscles_invovled AS muscles,
                      e.thumbnail AS thumbnail,
                      e.raw_name AS raw_name,
                      e.state AS state,
                      e.version AS version,
                      en.name AS name,
                      en.language AS language,
                      GROUP_CONCAT(DISTINCT em.id) AS media_ids,
                      GROUP_CONCAT(DISTINCT et.tag_id) AS tags
                    FROM exercises AS e
                    LEFT JOIN exercies_name AS en ON e.id = en.exercise_parent 
                    LEFT JOIN exercise_tag AS et ON e.id = et.exercise_parent
                    LEFT JOIN exercise_media AS em ON e.id = em.exercise_parent
                    WHERE en.language = ?
                    AND e.state = ?
                    GROUP BY e.id";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $exercise->getLang(),
                $exercise->getState()
            ]);

            // Fetch Data
            while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                // create exercise entity
                $exercise = new Exercise();

                // extract media ids
                $mediaIds = $row['media_ids'];

                if(isset($mediaIds)){
                    // get media data
                    $sql = 'SELECT * FROM exercise_media WHERE id IN (' .$mediaIds . ')';
                    $statementMedia = $this->connection->prepare($sql);
                    $statementMedia->execute();

                    // fetch data
                    $media = $statementMedia->fetchAll(PDO::FETCH_ASSOC);

                    $formats = [];
                    $format = null;
                    // loop through formats and set full links
                    foreach($media as $med){
                        $format['id'] = $med['id'];
                        $format['type'] = $med['type'];
                        $format['source'] = $this->configuration['asset_link'] . $med['source'];

                        // add to formats array
                        array_push($formats, $format);
                    }
                    $exercise->setFormats($formats);
                }

                // set exercise values
                $exercise->setId($row['id']);
                $exercise->setName($row['name']);
                $exercise->setLang($row['language']);
                $exercise->setState($row['state']);
                $exercise->setHardness($row['hardness']);
                $exercise->setThumbnail($this->configuration['asset_link'] . $row['thumbnail']);
                $exercise->setMuscles($this->configuration['asset_link'] . $row['muscles']);
                $exercise->setVersion($row['version']);
                $exercise->setTags($row['tags']);

                // add exercise to exercise collection
                $exerciseCollection->addEntity($exercise);
            }

            // set response status
            if($statement->rowCount() == 0){
                $exerciseCollection->setStatusCode(204);
            }else {
                $exerciseCollection->setStatusCode(200);
            }

        }catch(PDOException $e){
            $exerciseCollection->setStatusCode(204);

            // send monolog record in case of failure
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Get exercises mapper: " . $e->getMessage());
        }

        // return data
        return $exerciseCollection;
    }


    /**
     * Fetch exercises by search term
     *
     * @param Exercise $exercise
     * @return ExerciseCollection
     */
    public function searchExercises(Exercise $exercise):ExerciseCollection {

        // create response object
        $exerciseCollection = new ExerciseCollection();

        try {
            // set database instructions
            $sql = "SELECT
                      e.id AS id,
                      e.hardness AS hardness,
                      e.muscles_invovled AS muscles,
                      e.thumbnail AS thumbnail,
                      e.raw_name AS raw_name,
                      e.state AS state,
                      e.version AS version,
                      en.name AS name,
                      en.language AS language,
                      GROUP_CONCAT(DISTINCT em.id) AS media_ids,
                      GROUP_CONCAT(DISTINCT et.tag_id) AS tags
                    FROM exercises AS e
                    LEFT JOIN exercies_name AS en ON e.id = en.exercise_parent 
                    LEFT JOIN exercise_tag AS et ON e.id = et.exercise_parent
                    LEFT JOIN exercise_media AS em ON e.id = em.exercise_parent
                    WHERE en.language = ?
                    AND e.state = ?
                    AND en.name LIKE ?
                    GROUP BY e.id";
            $term = '%' . $exercise->getName() . '%';
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $exercise->getLang(),
                $exercise->getState(),
                $term
            ]);

            // Fetch Data
            while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                // create exercise entity
                $exercise = new Exercise();

                // extract media ids
                $mediaIds = $row['media_ids'];

                if(isset($mediaIds)){
                    // get media data
                    $sql = 'SELECT * FROM exercise_media WHERE id IN (' .$mediaIds . ')';
                    $statementMedia = $this->connection->prepare($sql);
                    $statementMedia->execute();

                    // fetch data
                    $media = $statementMedia->fetchAll(PDO::FETCH_ASSOC);

                    $formats = [];
                    $format = null;
                    // loop through formats and set full links
                    foreach($media as $med){
                        $format['id'] = $med['id'];
                        $format['type'] = $med['type'];
                        $format['source'] = $this->configuration['asset_link'] . $med['source'];

                        // add to formats array
                        array_push($formats, $format);
                    }
                    $exercise->setFormats($formats);
                }

                // set exercise values
                $exercise->setId($row['id']);
                $exercise->setName($row['name']);
                $exercise->setLang($row['language']);
                $exercise->setState($row['state']);
                $exercise->setHardness($row['hardness']);
                $exercise->setThumbnail($this->configuration['asset_link'] . $row['thumbnail']);
                $exercise->setMuscles($this->configuration['asset_link'] . $row['muscles']);
                $exercise->setVersion($row['version']);
                $exercise->setTags($row['tags']);

                // add exercise to exercise collection
                $exerciseCollection->addEntity($exercise);
            }

            // set response status
            if($statement->rowCount() == 0){
                $exerciseCollection->setStatusCode(204);
            }else {
                $exerciseCollection->setStatusCode(200);
            }

        }catch(PDOException $e){
            $exerciseCollection->setStatusCode(204);

            // send monolog record in case of failure
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Search exercises mapper: " . $e->getMessage());
        }

        // return data
        return $exerciseCollection;
    }


    /**
     * Fetch exercises by ids
     *
     * @param Exercise $exercise
     * @return ExerciseCollection
     */
    public function getExercisesById(Exercise $exercise):ExerciseCollection {

        // create response object
        $exerciseCollection = new ExerciseCollection();

        // helper function for converting array to comma separated string
        $whereIn = $this->sqlHelper->whereIn($exercise->getIds());

        try {
            // set database instructions
            $sql = "SELECT
                      e.id AS id,
                      e.raw_name,
                      e.hardness AS hardness,
                      e.muscles_invovled AS muscles,
                      e.thumbnail AS thumbnail,
                      e.raw_name AS raw_name,
                      e.state AS state,
                      e.version AS version,
                      en.name AS name,
                      en.language AS language,
                      GROUP_CONCAT(DISTINCT em.id) AS media_ids,
                      GROUP_CONCAT(DISTINCT et.tag_id) AS tags
                    FROM exercises AS e
                    LEFT JOIN exercies_name AS en ON e.id = en.exercise_parent 
                    LEFT JOIN exercise_tag AS et ON e.id = et.exercise_parent
                    LEFT JOIN exercise_media AS em ON e.id = em.exercise_parent
                    WHERE e.id IN (" . $whereIn . ")
                    AND e.state = ?
                    AND en.language = ?
                    GROUP BY e.id";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $exercise->getState(),
                $exercise->getLang()
            ]);

            // Fetch Data
            while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                // create exercise entity
                $exercise = new Exercise();

                // extract media ids
                $mediaIds = $row['media_ids'];

                if(isset($mediaIds)){
                    // get media data
                    $sql = 'SELECT * FROM exercise_media WHERE id IN (' .$mediaIds . ')';
                    $statementMedia = $this->connection->prepare($sql);
                    $statementMedia->execute();
                    // fetch data
                    $media = $statementMedia->fetchAll(PDO::FETCH_ASSOC);

                    $formats = [];
                    $format = null;
                    // loop through formats and set full links
                    foreach($media as $med){
                        $format['id'] = $med['id'];
                        $format['type'] = $med['type'];
                        $format['source'] = $this->configuration['asset_link'] . $med['source'];

                        // add to formats array
                        array_push($formats, $format);
                    }
                    $exercise->setFormats($formats);
                }

                // set exercise values
                $exercise->setId($row['id']);
                $exercise->setName($row['name']);
                $exercise->setRawName($row['raw_name']);
                $exercise->setLang($row['language']);
                $exercise->setState($row['state']);
                $exercise->setHardness($row['hardness']);
                $exercise->setThumbnail($this->configuration['asset_link'] . $row['thumbnail']);
                $exercise->setMuscles($this->configuration['asset_link'] . $row['muscles']);
                $exercise->setVersion($row['version']);
                $exercise->setTags($row['tags']);

                // add exercise to exercise collection
                $exerciseCollection->addEntity($exercise);
            }

            // set esponse status
            if($statement->rowCount() == 0){
                $exerciseCollection->setStatusCode(204);
            }else {
                $exerciseCollection->setStatusCode(200);
            }

        }catch(PDOException $e){
            $exerciseCollection->setStatusCode(204);

            // send monolog record in case of failure
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Get exercises by ids mapper: " . $e->getMessage());
        }

        // return data
        return $exerciseCollection;
    }


    /**
     * Delete exercise by id
     *
     * @param Exercise $exercise
     * @return Shared
     */
    public function deleteExercise(Exercise $exercise):Shared {

        // create response object
        $shared = new Shared();

        try {
            // begin transaction
            $this->connection->beginTransaction();

            // set database instructions
            $sql = "DELETE 
                       e.*,
                       ea.*,
                       en.*,
                       ena.*,
                       em.*,
                       ema.*,
                       et.*
                    FROM exercises AS e 
                    LEFT JOIN exercise_audit AS ea ON e.id = ea.exercise_parent
                    LEFT JOIN exercies_name AS en ON e.id = en.exercise_parent
                    LEFT JOIN exercise_name_audit AS ena ON en.id = ena.exercise_parent
                    LEFT JOIN exercise_media AS em ON e.id = em.exercise_parent
                    LEFT JOIN exercise_media_audit AS ema ON em.id = ema.exercise_media_parent
                    LEFT JOIN exercise_tag AS et ON e.id = et.exercise_parent
                    WHERE e.id = ?
                    AND e.state != 'R'";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $exercise->getId()
            ]);

            // set response status
            if($statement->rowCount() > 0){
                $shared->setResponse([200]);
            }else {
                $shared->setResponse([304]);
            }

            // commit transaction
            $this->connection->commit();

        }catch(PDOException $e){
            // rollback everything in case of failure
            $this->connection->rollBack();
            $shared->setResponse([304]);

            // send monolog record in case of failure
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Delete exercise mapper: " . $e->getMessage());
        }

        // return response
        return $shared;
    }


    /**
     * Release exercise
     *
     * @param Exercise $exercise
     * @return Shared
     */
    public function releaseExercise(Exercise $exercise):Shared {

        // create response object
        $shared = new Shared();

        try {
            // begin transaction
            $this->connection->beginTransaction();

            // set database instructions
            $sql = "UPDATE 
                      exercises  
                    SET state = 'R'
                    WHERE id = ?";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $exercise->getId()
            ]);

            // set response values
            if($statement->rowCount() > 0){
                // set response status
                $shared->setResponse([200]);

                // get latest version value
                $version = $this->lastVersion();

                // set new version of the workout
                $sql = "UPDATE exercises SET version = ? WHERE id = ?";
                $statement = $this->connection->prepare($sql);
                $statement->execute(
                    [
                        $version,
                        $exercise->getId()
                    ]
                );
            }else {
                $shared->setResponse([304]);
            }

            // commit transaction
            $this->connection->commit();

        }catch(PDOException $e){
            // rollback everything in case of failure
            $this->connection->rollBack();
            $shared->setResponse([304]);

            // send monolog record in case of failure
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Release exercise mapper: " . $e->getMessage());
        }

        // return response
        return $shared;
    }


    /**
     * Create exercise
     *
     * @param Exercise $exercise
     * @return Shared
     */
    public function createExercise(Exercise $exercise):Shared {

        // create response object
        $shared = new Shared();

        try {
            // begin transaction
            $this->connection->beginTransaction();

            // get newest id for the verision column
            $version = $this->lastVersion();

            // set database instructions for workout table
            $sql = "INSERT INTO exercises
                      (hardness, muscles_invovled, thumbnail, raw_name, state, version)
                     VALUES (?,?,?,?,?,?)";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $exercise->getHardness(),
                $exercise->getMuscles(),
                $exercise->getThumbnail(),
                $exercise->getName(),
                'P',
                $version
            ]);

            // if first query was successfull cntinue with other ones
            if($statement->rowCount() > 0){
                // get exercise parent id
                $exerciseParent = $this->connection->lastInsertId();

                // INSERT EXERCISE NAMES AND LANGUAGES
                $sqlName = "INSERT INTO exercies_name
                              (name, language, exercise_parent)
                            VALUES (?,?,?)";
                $statementName = $this->connection->prepare($sqlName);

                // loop through names collection
                $names = $exercise->getNames();
                foreach($names as $name){
                    // execute query
                    $statementName->execute([
                        $name->getName(),
                        $name->getLang(),
                        $exerciseParent
                    ]);
                }

                // INSERT MEDIA
                $sqlMedia = "INSERT INTO exercise_media
                              (type, source, exercise_parent)
                            VALUES (?,?,?)";
                $statementMedia = $this->connection->prepare($sqlMedia);

                // loop through media collection
                $media = $exercise->getMedia();
                foreach($media as $med){
                    // execute query
                    $statementMedia->execute([
                        $med->getType(),
                        $med->getSource(),
                        $exerciseParent
                    ]);
                }


                // INSERT TAGS
                $sqlTags = "INSERT INTO exercise_tag
                                (exercise_parent, tag_id)
                              VALUES (?,?)";
                $statementTags = $this->connection->prepare($sqlTags);

                // loop through tagscollection
                $tags = $exercise->getTags();
                foreach($tags as $tag){
                    // execute query
                    $statementTags->execute([
                        $exerciseParent,
                        $tag
                    ]);
                }

                $shared->setResponse([200]);
            }else {
                $shared->setResponse([304]);
            }

            // commit transaction
            $this->connection->commit();

        }catch(PDOException $e){
            // rollback everything in case of failure
            $this->connection->rollBack();
            $shared->setResponse([304]);

            // send monolog record in case of failure
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Create exercise mapper: " . $e->getMessage());
        }

        // return response
        return $shared;
    }


    /**
     * Update exercise
     *
     * @param Exercise $exercise
     * @return Shared
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function editExercise(Exercise $exercise):Shared {

        // create response object
        $shared = new Shared();

        try {
            // begin transaction
            $this->connection->beginTransaction();

            // update main workout table
            $sql = "UPDATE exercises SET
                      hardness = ?,
                      muscles_invovled = ?,
                      thumbnail = ?,
                      raw_name = ?   
                    WHERE id = ?";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $exercise->getHardness(),
                $exercise->getMuscles(),
                $exercise->getThumbnail(),
                $exercise->getName(),
                $exercise->getId()
            ]);

            // if row is changed, update version
            if($statement->rowCount() > 0){
                // get last version
                $lastVersion = $this->lastVersion();

                // set database instructions
                $sql = "UPDATE exercises SET version = ? WHERE id = ?";
                $statement = $this->connection->prepare($sql);
                $statement->execute([
                    $lastVersion,
                    $exercise->getId()
                ]);

                // send requests for deleting all cached files at exercise, workouts and mobile MS
                $deleter = new DeleteCache($this->configuration);
                $deleter->deleteCacheAtParentMicroservices();
            }

            // update names
            $sqlNames = "INSERT INTO
                            exercies_name (name, language, exercise_parent)
                            VALUES (?,?,?)
                         ON DUPLICATE KEY
                         UPDATE
                            name = VALUES(name),
                            language = VALUES(language),
                            exercise_parent = VALUES(exercise_parent)";
            $statementNames = $this->connection->prepare($sqlNames);

            // loop through data and make updates if neccesary
            $names = $exercise->getNames();
            foreach($names as $name){
                // execute name query
                $statementNames->execute([
                    $name->getName(),
                    $name->getLang(),
                    $exercise->getId()
                ]);
            }

            // update formats
            $sqlMedia = "INSERT INTO
                            exercise_media (type, source, exercise_parent)
                            VALUES (?,?,?)
                         ON DUPLICATE KEY
                         UPDATE
                            type = VALUES(type),
                            source = VALUES(source),
                            exercise_parent = VALUES(exercise_parent)";
            $statementMedia = $this->connection->prepare($sqlMedia);

            // loop through data and make updates if neccesary
            $media = $exercise->getMedia();
            foreach($media as $med){
                // execute name query
                $statementMedia->execute([
                    $med->getType(),
                    $med->getSource(),
                    $exercise->getId()
                ]);
            }


            // delete tags before updating
            $sqlDelete = "DELETE FROM exercise_tag WHERE exercise_parent = ?";
            $statementDelete = $this->connection->prepare($sqlDelete);
            $statementDelete->execute([
                $exercise->getId()
            ]);

            // update tags
            $sqlTags = "INSERT INTO
                            exercise_tag (exercise_parent, tag_id)
                            VALUES (?,?)
                        ON DUPLICATE KEY
                        UPDATE
                            exercise_parent = VALUES(exercise_parent),
                            tag_id = VALUES(tag_id)";
            $statementTags = $this->connection->prepare($sqlTags);

            // loop through data and make updates if neccesary
            $tags = $exercise->getTags();
            foreach($tags as $tag){
                // execute query
                $statementTags->execute([
                    $exercise->getId(),
                    $tag
                ]);
            }

            $shared->setResponse([200]);

            // commit transaction
            $this->connection->commit();

        }catch(PDOException $e){
            // rollback everything in case of failure
            $this->connection->rollBack();
            $shared->setResponse([304]);

            // send monolog record in case of failure
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Edit exercise mapper: " . $e->getMessage());
        }

        // return response
        return $shared;
    }


    /**
     * Get total number of exercises
     *
     * @return null
     */
    public function getTotal() {

        try {
            // set database instructions
            $sql = "SELECT COUNT(*) as total FROM exercises";
            $statement = $this->connection->prepare($sql);
            $statement->execute();

            // set total number
            $total = $statement->fetch(PDO::FETCH_ASSOC)['total'];

        }catch(PDOException $e){
            $total = null;
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Get total exercises mapper: " . $e->getMessage());
        }

        // return data
        return $total;
    }


    /**
     * Get last version number
     *
     * @return string
     */
    public function lastVersion(){
        // set database instructions
        $sql = "INSERT INTO version VALUES(null)";
        $statement = $this->connection->prepare($sql);
        $statement->execute([]);

        // fetch id
        $lastId = $this->connection->lastInsertId();

        // return last id
        return $lastId;
    }
}