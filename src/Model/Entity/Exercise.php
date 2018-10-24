<?php
namespace Model\Entity;

use Model\Contract\HasId;

class Exercise implements HasId
{
    private $id;
    private $name;
    private $rawName;
    private $tags;
    private $hardness;
    private $media;
    private $app;
    private $state;
    private $lang;
    private $names;
    private $ids = [];
    private $muscles;
    private $thumbnail;
    private $version;
    private $gifFormat;
    private $mp4Format;
    private $m4vFormat;
    private $formats = [];
    private $from;
    private $limit;


    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from): void
    {
        $this->from = $from;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return mixed
     */
    public function getRawName()
    {
        return $this->rawName;
    }

    /**
     * @param mixed $rawName
     */
    public function setRawName($rawName): void
    {
        $this->rawName = $rawName;
    }

    /**
     * @return array
     */
    public function getFormats(): array
    {
        return $this->formats;
    }

    /**
     * @param array $formats
     */
    public function setFormats(array $formats): void
    {
        $this->formats = $formats;
    }

    /**
     * @return mixed
     */
    public function getGifFormat()
    {
        return $this->gifFormat;
    }

    /**
     * @param mixed $gifFormat
     */
    public function setGifFormat($gifFormat): void
    {
        $this->gifFormat = $gifFormat;
    }

    /**
     * @return mixed
     */
    public function getMp4Format()
    {
        return $this->mp4Format;
    }

    /**
     * @param mixed $mp4Format
     */
    public function setMp4Format($mp4Format): void
    {
        $this->mp4Format = $mp4Format;
    }

    /**
     * @return mixed
     */
    public function getM4vFormat()
    {
        return $this->m4vFormat;
    }

    /**
     * @param mixed $m4vFormat
     */
    public function setM4vFormat($m4vFormat): void
    {
        $this->m4vFormat = $m4vFormat;
    }


    /**
     * @return mixed
     */
    public function getMuscles()
    {
        return $this->muscles;
    }

    /**
     * @param mixed $muscles
     */
    public function setMuscles($muscles): void
    {
        $this->muscles = $muscles;
    }

    /**
     * @return mixed
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @param mixed $thumbnail
     */
    public function setThumbnail($thumbnail): void
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version): void
    {
        $this->version = $version;
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @param array $ids
     */
    public function setIds(array $ids): void
    {
        $this->ids = $ids;
    }

    /**
     * @return mixed
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * @param mixed $names
     */
    public function setNames($names): void
    {
        $this->names = $names;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param mixed $app
     */
    public function setApp($app): void
    {
        $this->app = $app;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state): void
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param mixed $lang
     */
    public function setLang($lang): void
    {
        $this->lang = $lang;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags($tags): void
    {
        $this->tags = $tags;
    }

    /**
     * @return mixed
     */
    public function getHardness()
    {
        return $this->hardness;
    }

    /**
     * @param mixed $hardness
     */
    public function setHardness($hardness): void
    {
        $this->hardness = $hardness;
    }

    /**
     * @return mixed
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param mixed $media
     */
    public function setMedia(MediaCollection $media): void
    {
        $this->media = $media;
    }




}

