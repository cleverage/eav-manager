<?php

namespace CleverAge\EAVManager\ProcessBundle\Process\Generic;

use CleverAge\EAVManager\ProcessBundle\Process\ProcessInterface;

/**
 * @TODO describe class usage
 */
class FilterProcess implements ProcessInterface
{

    /** @var array */
    protected $conditions;

    /** @var array */
    protected $dataToProceed;

    /** @var array */
    protected $proceededData;

    /**
     * FilterProcess constructor.
     *
     * @param array $conditions
     */
    public function __construct(array $conditions)
    {
        $this->conditions = $conditions;
    }


    /**
     * {@inheritdoc}
     */
    public function setInput($data)
    {
        $this->dataToProceed = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->proceededData = [];
        foreach ($this->dataToProceed as $key => $item) {
            if ($this->checkConditions($item)) {
                $this->proceededData[$key] = $item;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->proceededData;
    }

    /**
     * Check an item against an array of conditions.
     * Available conditions :
     *     - eq
     *     - neq
     *
     * @TODO for now filter out unset value
     *
     * @param array $item
     *
     * @return bool
     */
    protected function checkConditions(array $item): bool
    {
        foreach ($this->conditions as $key => $condition) {
            if (!isset($item[$key])) {
                return false;
            }

            foreach ($condition as $conditionType => $value) {
                if ($conditionType === 'eq') {
                    if ($item[$key] != $value) {
                        return false;
                    }
                } elseif ($conditionType === 'neq') {
                    if ($item[$key] == $value) {
                        return false;
                    }
                } else {
                    throw new \UnexpectedValueException("Unknown condition type {$conditionType}");
                }
            }
        }

        return true;
    }

}
