<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/29/18
 * Time: 8:19 AM
 */

namespace Model\Entity;


use Component\Collection;
use Model\Contract\HasId;

class MediaCollection extends Collection
{

    public function addEntity(HasId $entity, $key = null)
    {
        return parent::addEntity($entity, $key); // TODO: Change the autogenerated stub
    }


    public function buildEntity(): HasId
    {
        // TODO: Implement buildEntity() method.
    }

}