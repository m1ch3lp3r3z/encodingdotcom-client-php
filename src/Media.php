<?php

namespace MobileRider\Encoding;

class Media extends \MobileRider\Encoding\Generics\DataItem implements \Serializable
{
    const STATUS_NEW         = 'New';
    const STATUS_DOWNLOADING = 'Downloading';
    const STATUS_DOWNLOADED  = 'Downloaded';
    const STATUS_READY       = 'Ready to process';
    const STATUS_WAITING     = 'Waiting for encoder';
    const STATUS_PROCESSING  = 'Processing';
    const STATUS_SAVING      = 'Saving';
    const STATUS_FINISHED    = 'Finished';
    const STATUS_ERROR       = 'Error';
    const STATUS_STOPPED     = 'Stopped Perform';

    private static $availableStatuses = array(
        self::STATUS_NEW,
        self::STATUS_DOWNLOADING,
        self::STATUS_DOWNLOADED,
        self::STATUS_READY,
        self::STATUS_WAITING,
        self::STATUS_PROCESSING,
        self::STATUS_SAVING,
        self::STATUS_FINISHED,
        self::STATUS_ERROR,
        self::STATUS_STOPPED
    );

    private $sources = [];
    private $formats = [];
    private $options = [];
    private $error = '';

    private $isExtended = false;
    private $isOnHold = false;

    public function __construct(array $sources = null, array $formats = null, array $options = null)
    {
        if ($sources) {
            foreach ($sources as $source) {
                $this->addSource($source);
            }
        }

        if ($formats) {
            foreach ($formats as $format) {
                $this->addFormat($format);
            }
        }
    }

    public function addSource($source)
    {
        if (is_string($source)) {
            $source = new \MobileRider\Encoding\Media\Source($source);
        }

        $this->sources[] = $source;
    }

    public function getSources()
    {
        return $this->sources;
    }

    public function clearSources()
    {
        $this->sources = [];
    }

    public function hasSources()
    {
        return (bool) $this->sources;
    }

    public function addFormat(\MobileRider\Encoding\Media\Format $format)
    {
        $this->formats[] = $format;
    }

    public function clearFormats()
    {
        $this->formats = [];
    }

    public function hasFormats()
    {
        return (bool) $this->formats;
    }

    public function getFormats()
    {
        return $this->formats;
    }

    public function isOnHold()
    {
        return $this->getStatus() == self::STATUS_READY && $this->isOnHold;
    }

    public function isEncoding()
    {
        return !$this->isNew() && !$this->isOnHold() && in_array($this->getStatus(), array(
            self::STATUS_NEW, // We assume NEW is the starting status of the encoding process
            self::STATUS_DOWNLOADED,
            self::STATUS_DOWNLOADING,
            self::STATUS_READY,
            self::STATUS_WAITING,
            self::STATUS_PROCESSING,
            self::STATUS_SAVING
        ));
    }

    public function isExtended()
    {
        return $this->isExtended;
    }

    public function isSourceExtended()
    {
        if (!$this->hasSources()) {
            return false;
        }

        // Assume if one is extended all the others are
        // all the sources should be updated in the same request
        return $this->getSources()[0]->isExtended();
    }

    public function hasMultipleSources()
    {
        return count($this->sources) > 1;
    }

    public function setOptions($options)
    {
        if (!$options) {
            return false;
        }

        $this->options = array_merge($this->options, $options);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function clearOptions()
    {
        $this->options = [];
    }

    public function setStatus($status)
    {
        $this->set('status', $status);
    }

    public function update(array $data, array $options = null, $extended = false)
    {
        $this->setData($data);
        $this->setOptions($options);
        $this->isExtended = $extended;
    }

    public function serialize()
    {
        return serialize([
            'id' => $this->getId(),
            'isOnHold' => $this->isOnHold,
            'data' => [
                'status' => $this->getStatus()
            ]
        ]);
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->isOnHold = $data['isOnHold'];
        $this->initialize($data['id'], $data['data']);
    }

    public function isReady()
    {
        return $this->getStatus() == self::STATUS_READY;
    }

    public function isDone()
    {
        return $this->getStatus() == self::STATUS_FINISHED;
    }

    public function setError($msg)
    {
        $this->error = $msg;
        $this->setStatus(self::STATUS_ERROR);
    }

    public function getError()
    {
        return $this->error;
    }

    public function isError()
    {
        return $this->getStatus() == self::STATUS_ERROR;
    }

    public function hasError()
    {
        if ($this->isError()) {
            return true;
        }

        foreach($this->getFormats() as $format) {
            if ($format->isError()) {
                return true;
            }
        }

        return false;
    }

    public function getVideoTrack()
    {

    }

    public function hasVideoTrack()
    {

    }

    public function getAudioTrack()
    {

    }

    public function hasAudioTrack()
    {

    }

    public function hasTextTrack()
    {

    }

    public function getTextTrack()
    {

    }
}
