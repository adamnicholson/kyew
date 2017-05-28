<?php

namespace Kyew\PubSub;

use Kyew\PubSub;

class DatabasePubSub implements PubSub
{
    /**
     * @var \PDO
     */
    private $connection;
    /**
     * @var string
     */
    private $table;
    /**
     * @var array
     */
    private $listeners = [];

    /**
     * DatabasePubSub constructor.
     * @param \PDO $connection
     * @param string $table
     */
    public function __construct(\PDO $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * @inheritdoc
     */
    public function publish($event, $data)
    {
        $statement = $this->connection->prepare("INSERT INTO {$this->table} (`key`, `value`) VALUES (?, ?)");
        $statement->execute([$event, $data]);
    }

    /**
     * @inheritdoc
     */
    public function on($event, callable $callback)
    {
        $this->listeners[$event][] = $callback;
    }

    /**
     * @inheritdoc
     */
    public function recheck($event)
    {
        $statement = $this->connection->prepare("SELECT `value` FROM {$this->table} WHERE `key` = ?");
        $statement->execute([$event]);
        $row = $statement->fetchObject();

        if (!$row) {
            return;
        }

        foreach ($this->listeners[$event] ?? [] as $listener) {
            $listener($row->value);
        }

        $statement = $this->connection->prepare("DELETE FROM {$this->table} WHERE `key` = ?");
        $statement->execute([$event]);
    }
}
