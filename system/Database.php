<?php

namespace System;

use MongoDB\Client;

class Database
{
    protected $db;

    function __construct($database, $collection)
    {
        $mongoDBClient = new Client();
        $this->db = $mongoDBClient->$database->$collection;
    }

    function insertOne($insert_data)
    {
        $insertOneResult = $this->db->insertOne($insert_data);
        return $insertOneResult->getInsertedId();
    }

    function insertMany($insert_data)
    {
        $insertResult = $this->db->insertMany($insert_data);
        return $insertResult;
    }

    function updateOne($collection, $update_column, $update_value)
    {
        $this->db->drop();

        $updateResult = $collection->updateOne(
            $update_column,
            $update_value
        );

        return $updateResult->getModifiedCount();
    }

    function updateMany($conditions, $updated_data)
    {
        $updateResult = $this->db->updateMany(
            $conditions,
            ['$set' => $updated_data]
        );

        return $updateResult->getModifiedCount();
    }

    function fetchData($filter_data)
    {
        $cursor = $this->db->find($filter_data);

        return $cursor;
    }

    function deleteData($delete_data)
    {
        $this->db->drop();

        $deleteResult = $this->db->deleteOne($delete_data);

        return $deleteResult->getDeletedCount();
    }
}
