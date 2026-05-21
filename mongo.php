<?php
require_once __DIR__ . '/config.php';

if (!extension_loaded('mongodb')) {
    die("<h2>MongoDB extension not installed</h2>
         <p>Add <code>extension=mongodb</code> to your php.ini and restart Apache.
         Download <code>php_mongodb.dll</code> from
         <a href='https://pecl.php.net/package/mongodb'>pecl.php.net/package/mongodb</a>.</p>");
}

class Mongo
{
    private MongoDB\Driver\Manager $manager;
    private string $db;

    public function __construct(string $uri, string $db)
    {
        $this->manager = new MongoDB\Driver\Manager($uri);
        $this->db = $db;
    }

    private function ns(string $collection): string
    {
        return $this->db . '.' . $collection;
    }

    public function find(string $collection, array $filter = [], array $options = []): array
    {
        $cursor = $this->manager->executeQuery(
            $this->ns($collection),
            new MongoDB\Driver\Query($filter, $options)
        );
        return $cursor->toArray();
    }

    public function findOne(string $collection, array $filter = [], array $options = [])
    {
        $options['limit'] = 1;
        $rows = $this->find($collection, $filter, $options);
        return $rows[0] ?? null;
    }

    public function findById(string $collection, $id)
    {
        if (is_string($id)) {
            try { $id = new MongoDB\BSON\ObjectId($id); } catch (Throwable $e) { return null; }
        }
        return $this->findOne($collection, ['_id' => $id]);
    }

    public function insert(string $collection, array $doc)
    {
        $bulk = new MongoDB\Driver\BulkWrite();
        $id = $bulk->insert($doc);
        $this->manager->executeBulkWrite($this->ns($collection), $bulk);
        return $id;
    }

    public function update(string $collection, array $filter, array $set, bool $multi = false): int
    {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->update($filter, ['$set' => $set], ['multi' => $multi, 'upsert' => false]);
        $result = $this->manager->executeBulkWrite($this->ns($collection), $bulk);
        return $result->getModifiedCount();
    }

    public function delete(string $collection, array $filter, bool $multi = false): int
    {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->delete($filter, ['limit' => $multi ? 0 : 1]);
        $result = $this->manager->executeBulkWrite($this->ns($collection), $bulk);
        return $result->getDeletedCount();
    }

    /**
     * Count documents matching a filter. Uses the aggregation pipeline
     * (`$match` + `$count`) instead of the legacy `count` command, which is
     * deprecated since MongoDB 4.0.
     */
    public function count(string $collection, array $filter = []): int
    {
        $pipeline = [];
        if (!empty($filter)) $pipeline[] = ['$match' => $filter];
        $pipeline[] = ['$count' => 'n'];

        $cmd = new MongoDB\Driver\Command([
            'aggregate' => $collection,
            'pipeline'  => $pipeline,
            'cursor'    => (object) [],
        ]);
        $cursor = $this->manager->executeCommand($this->db, $cmd);
        $row = $cursor->toArray()[0] ?? null;
        return (int) ($row->n ?? 0);
    }

    /** Helper: convert string id to ObjectId or return null on bad input */
    public static function oid(?string $id): ?MongoDB\BSON\ObjectId
    {
        if (!$id) return null;
        try { return new MongoDB\BSON\ObjectId($id); }
        catch (Throwable $e) { return null; }
    }
}

try {
    $db = new Mongo(MONGO_URI, MONGO_DB);
} catch (Throwable $e) {
    die("<h2>Cannot connect to MongoDB</h2><p>" . htmlspecialchars($e->getMessage()) . "</p>
         <p>Make sure <code>mongod</code> is running and reachable at <code>" . htmlspecialchars(MONGO_URI) . "</code>.</p>");
}
