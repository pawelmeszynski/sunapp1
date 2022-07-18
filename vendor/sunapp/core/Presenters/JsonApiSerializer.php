<?php

namespace SunAppModules\Core\Presenters;

use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\JsonApiSerializer as BaseJsonApiSerializer;

class JsonApiSerializer extends BaseJsonApiSerializer
{
    /**
     * Serialize a collection.
     *
     * @param  string  $resourceKey
     * @param  array  $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        $resources = [];

        foreach ($data as $resource) {
            $resources[] = $this->item($resourceKey, $resource)['data'];
        }

        return ['data' => $resources];
    }

    /**
     * Serialize an item.
     *
     * @param  string  $resourceKey
     * @param  array  $data
     *
     * @return array
     */
    public function item($resourceKey, array $data)
    {
        $id = $this->getIdFromData($data);
        $resource = [
            'data' => [
                'type' => $resourceKey,
                'id' => $id,
                'attributes' => $data,
            ],
        ];

        unset($resource['data']['attributes']['id']);

        if (isset($resource['data']['attributes']['links'])) {
            $custom_links = $data['links'];
            unset($resource['data']['attributes']['links']);
        }

        if (isset($resource['data']['attributes']['meta'])) {
            $resource['data']['meta'] = $data['meta'];
            unset($resource['data']['attributes']['meta']);
        }

        if (empty($resource['data']['attributes'])) {
            $resource['data']['attributes'] = (object)[];
        }

        if (isset($custom_links)) {
            $resource['data']['links'] = $custom_links;
        }

        return $resource;
    }

    /**
     * Serialize the included data.
     *
     * @param  ResourceInterface  $resource
     * @param  array  $data
     *
     * @return array
     */
    public function includedData(ResourceInterface $resource, array $data)
    {

        list($serializedData, $linkedIds) = $this->pullOutNestedIncludedData($data);
        foreach ($data as $value) {
            foreach ($value as $key => $includeObject) {
                $wanted_includes = explode(',', request()->input('include', ''));
                if (
                    $this->isNull($includeObject)
                    || $this->isEmpty($includeObject)
                    || !in_array($key, $wanted_includes)
                ) {
                    continue;
                }

                $includeObjects = $this->createIncludeObjects($includeObject);
                list($serializedData, $linkedIds) = $this->serializeIncludedObjectsWithCacheKey(
                    $includeObjects,
                    $linkedIds,
                    $serializedData
                );
            }
        }

        return empty($serializedData) ? [] : ['included' => $serializedData];
    }

    /**
     * Check if the objects are part of a collection or not
     *
     * @param $includeObject
     *
     * @return array
     */
    private function createIncludeObjects($includeObject)
    {
        if ($this->isCollection($includeObject)) {
            $includeObjects = $includeObject['data'];

            return $includeObjects;
        }
        $includeObjects = [$includeObject['data']];

        return $includeObjects;
    }

    /**
     * @param $includeObjects
     * @param $linkedIds
     * @param $serializedData
     *
     * @return array
     */
    private function serializeIncludedObjectsWithCacheKey($includeObjects, $linkedIds, $serializedData)
    {
        foreach ($includeObjects as $object) {
            $includeType = $object['type'];
            $includeId = $object['id'];
            $cacheKey = "$includeType:$includeId";
            if (!array_key_exists($cacheKey, $linkedIds)) {
                $serializedData[] = $object;
                $linkedIds[$cacheKey] = $object;
            }
        }
        return [$serializedData, $linkedIds];
    }

    /**
     * @param  array  $includedData
     *
     * @return array
     */
    protected function parseRelationships($includedData)
    {
        $relationships = [];

        foreach ($includedData as $key => $inclusion) {
            foreach ($inclusion as $includeKey => $includeObject) {
                $relationships = $this->buildRelationships($includeKey, $relationships, $includeObject, $key);
                if (isset($includedData[0][$includeKey]['meta'])) {
                    $relationships[$includeKey][0]['meta'] = $includedData[0][$includeKey]['meta'];
                }
            }
        }

        return $relationships;
    }

    /**
     * @param $includeKey
     * @param $relationships
     * @param $includeObject
     * @param $key
     *
     * @return array
     */
    private function buildRelationships($includeKey, $relationships, $includeObject, $key)
    {
        $relationships = $this->addIncludekeyToRelationsIfNotSet($includeKey, $relationships);

        if ($this->isNull($includeObject)) {
            $relationship = $this->null();
        } elseif ($this->isEmpty($includeObject)) {
            $relationship = [
                'data' => [],
            ];
        } elseif ($this->isCollection($includeObject)) {
            $relationship = ['data' => []];

            $relationship = $this->addIncludedDataToRelationship($includeObject, $relationship);
        } else {
            $relationship = [
                'data' => [
                    'type' => $includeObject['data']['type'],
                    'id' => $includeObject['data']['id'],
                ],
                'links' => $includeObject['data']['links']
            ];
        }

        $relationships[$includeKey][$key] = $relationship;

        return $relationships;
    }

    /**
     * @param $includeKey
     * @param $relationships
     *
     * @return array
     */
    private function addIncludekeyToRelationsIfNotSet($includeKey, $relationships)
    {
        if (!array_key_exists($includeKey, $relationships)) {
            $relationships[$includeKey] = [];
            return $relationships;
        }

        return $relationships;
    }

    /**
     * @param $includeObject
     * @param $relationship
     *
     * @return array
     */
    private function addIncludedDataToRelationship($includeObject, $relationship)
    {
        foreach ($includeObject['data'] as $object) {
            $relationship['data'][] = [
                'data' => [
                    'type' => $object['type'],
                    'id' => $object['id'],
                ],
                'links' => $object['links'] ?? []
            ];
        }

        return $relationship;
    }

    /**
     * @param  array  $data
     * @param  array  $relationships
     *
     * @return array
     */
    protected function fillRelationships($data, $relationships)
    {
        if ($this->isCollection($data)) {
            foreach ($relationships as $key => $relationship) {
                $data = $this->fillRelationshipAsCollection($data, $relationship, $key);
            }
        } else { // Single resource
            foreach ($relationships as $key => $relationship) {
                $data = $this->fillRelationshipAsSingleResource($data, $relationship, $key);
            }
        }

        return $data;
    }

    /**
     * Loops over the relationships of the provided data and formats it
     *
     * @param $data
     * @param $relationship
     * @param $key
     *
     * @return array
     */
    private function fillRelationshipAsCollection($data, $relationship, $key)
    {
        foreach ($relationship as $index => $relationshipData) {
            $data['data'][$index]['relationships'][$key] = $relationshipData;
        }
        return $data;
    }

    /**
     * @param $data
     * @param $relationship
     * @param $key
     *
     * @return array
     */
    private function fillRelationshipAsSingleResource($data, $relationship, $key)
    {
        $data['data']['relationships'][$key] = $relationship[0];
        return $data;
    }
}
