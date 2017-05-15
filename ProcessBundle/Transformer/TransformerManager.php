<?php

namespace CleverAge\EAVManager\ProcessBundle\Transformer;

/**
 * @TODO describe class usage
 */
class TransformerManager
{
    /**
     * @param array $mapping
     * @param array $data
     *
     * @return array
     */
    public function transform(array $mapping, array $data): array
    {
        $result = [];
        foreach ($data as $item) {
            $result[] = $this->mapValues($mapping, $item);
        }

        return $result;
    }

    /**
     * @param array $mapping
     * @param array $item
     *
     * @return array
     */
    protected function mapValues(array $mapping, array $item): array
    {
        $result = [];
        foreach ($mapping as $attribute => $config) {
            $value = null;
            if (isset($config['constant'])) {
                $value = $config['constant'];
            } else {
                $origCode = $attribute;
                if (isset($config['code'])) {
                    $origCode = $config['code'];
                }

                if (isset($item[$origCode])) {
                    $value = $item[$origCode];
                }
            }

            if (isset($config['transformer'])) {
                $value = $this->transformValue($value, $config['transformer']);
            }

            $result[$attribute] = $value;
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @param array $transformerConfig
     *
     * @return mixed
     */
    protected function transformValue($value, array $transformerConfig)
    {
        //TODO implement how transformer are handled

        return $value;
    }
}
