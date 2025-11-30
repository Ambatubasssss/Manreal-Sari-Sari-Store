<?php

namespace App\Libraries;

use MongoDB\Client;
use MongoDB\Database;
use Exception;

class MongoDB
{
    private Client $client;
    private Database $database;

    public function __construct()
    {
        try {
            $host = getenv('MONGODB_HOST') ?: 'localhost';
            $port = getenv('MONGODB_PORT') ?: '27017';
            $database = getenv('MONGODB_DATABASE') ?: 'manreal';
            $username = getenv('MONGODB_USERNAME') ?: '';
            $password = getenv('MONGODB_PASSWORD') ?: '';

            $uri = "mongodb://";
            if (!empty($username) && !empty($password)) {
                $uri .= $username . ':' . $password . '@';
            }
            $uri .= $host . ':' . $port . '/' . $database;

            $this->client = new Client($uri);
            $this->database = $this->client->selectDatabase($database);

            // Test connection
            $this->database->listCollections();

        } catch (Exception $e) {
            throw new Exception('MongoDB Connection Error: ' . $e->getMessage());
        }
    }

    /**
     * Get MongoDB Client instance
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Get MongoDB Database instance
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * Get a specific collection
     */
    public function getCollection(string $collectionName)
    {
        return $this->database->{$collectionName};
    }

    /**
     * Insert document into collection
     */
    public function insert(string $collection, array $data)
    {
        try {
            $result = $this->getCollection($collection)->insertOne($data);
            return $result->getInsertedId();
        } catch (Exception $e) {
            throw new Exception('MongoDB Insert Error: ' . $e->getMessage());
        }
    }

    /**
     * Insert multiple documents
     */
    public function insertMany(string $collection, array $data)
    {
        try {
            $result = $this->getCollection($collection)->insertMany($data);
            return $result->getInsertedIds();
        } catch (Exception $e) {
            throw new Exception('MongoDB InsertMany Error: ' . $e->getMessage());
        }
    }

    /**
     * Find one document
     */
    public function findOne(string $collection, array $filter = [], array $options = [])
    {
        try {
            return $this->getCollection($collection)->findOne($filter, $options);
        } catch (Exception $e) {
            throw new Exception('MongoDB FindOne Error: ' . $e->getMessage());
        }
    }

    /**
     * Find multiple documents
     */
    public function find(string $collection, array $filter = [], array $options = [])
    {
        try {
            return $this->getCollection($collection)->find($filter, $options);
        } catch (Exception $e) {
            throw new Exception('MongoDB Find Error: ' . $e->getMessage());
        }
    }

    /**
     * Update one document
     */
    public function updateOne(string $collection, array $filter, array $update, array $options = [])
    {
        try {
            return $this->getCollection($collection)->updateOne($filter, $update, $options);
        } catch (Exception $e) {
            throw new Exception('MongoDB UpdateOne Error: ' . $e->getMessage());
        }
    }

    /**
     * Update multiple documents
     */
    public function updateMany(string $collection, array $filter, array $update, array $options = [])
    {
        try {
            return $this->getCollection($collection)->updateMany($filter, $update, $options);
        } catch (Exception $e) {
            throw new Exception('MongoDB UpdateMany Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete one document
     */
    public function deleteOne(string $collection, array $filter)
    {
        try {
            return $this->getCollection($collection)->deleteOne($filter);
        } catch (Exception $e) {
            throw new Exception('MongoDB DeleteOne Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple documents
     */
    public function deleteMany(string $collection, array $filter)
    {
        try {
            return $this->getCollection($collection)->deleteMany($filter);
        } catch (Exception $e) {
            throw new Exception('MongoDB DeleteMany Error: ' . $e->getMessage());
        }
    }

    /**
     * Count documents
     */
    public function count(string $collection, array $filter = []): int
    {
        try {
            return $this->getCollection($collection)->countDocuments($filter);
        } catch (Exception $e) {
            throw new Exception('MongoDB Count Error: ' . $e->getMessage());
        }
    }

    /**
     * Aggregate documents
     */
    public function aggregate(string $collection, array $pipeline, array $options = [])
    {
        try {
            return $this->getCollection($collection)->aggregate($pipeline, $options);
        } catch (Exception $e) {
            throw new Exception('MongoDB Aggregate Error: ' . $e->getMessage());
        }
    }

    /**
     * Create indexes for a collection
     */
    public function createIndexes(string $collection, array $indexes): array
    {
        try {
            return $this->getCollection($collection)->createIndexes($indexes);
        } catch (Exception $e) {
            throw new Exception('MongoDB CreateIndexes Error: ' . $e->getMessage());
        }
    }
}
