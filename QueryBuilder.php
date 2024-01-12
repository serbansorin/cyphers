<?php


class QueryBuilder {
    protected $connection;
    protected $tableName;
    protected $conditions = [];

    protected $query;
    protected $bindings = [];

    protected $select = '*';
    protected $limit;
    protected $offset;
    protected $orderBy;
    protected $groupBy;
    private $conditionNumber = -1;




    public function __construct($connection= null, $tableName = null) {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->query = [
            'select' => '*',
            'where' => '',
            'limit' => '',
            'offset' => '',
            'orderBy' => '',
            'groupBy' => '',
            'from' => '',
        ];
    }


    protected function isSqlConditionOrValue($condition) {
        $isCond = in_array(strtoupper($condition), ['=', '>', '<', '>=', '<=', '<>', '!=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'IS NULL', 'IS NOT NULL']);
        return $isCond;
    }

    protected function addCondition($column, $condition, $value) {
        $this->conditions[] = "$column $condition '$value'";
        $this->conditionNumber++;
    }

    public function table($tableName) {
        $this->tableName = $tableName;
        $this->query['from'] = "FROM $tableName";
        return $this;
    }


    public function select($columns = ['*']) 
    {

        $this->select = implode(', ', $columns);
        $this->query['select'] = "SELECT $this->select";
        return $this;

    }

    public function where($column, $condition = null, $value = null, $operator = 'AND') {
        $this->isSqlConditionOrValue($condition) ? $this->addCondition($column, $condition, $value) : $this->addCondition($column, '=', $condition);
        $this->query['where'] .= $this->conditionNumber > 0 ? " $operator " . $this->conditions[$this->conditionNumber] : $this->conditions[$this->conditionNumber];
    }

    public function orWhere($column, $condition = null, $value = null) {
        $this->where($column, $condition, $value, 'OR');
        $this->query['where'] .= $this->conditionNumber > 0 ? " OR " . $this->conditions[$this->conditionNumber] : $this->conditions[$this->conditionNumber];
    }

    public function whereIn($column, $values, $operator = 'AND') {
        $this->conditions[] = "$column IN (" . implode(', ', $values) . ")";
        $this->conditionNumber++;
        $this->query['where'] .= $this->conditionNumber > 0 ? " $operator " . $this->conditions[$this->conditionNumber] : $this->conditions[$this->conditionNumber];
    }

    public function limit($limit) {
        $this->limit = $limit;
        $this->query['limit'] = "LIMIT $limit";
        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset;
        $this->query['offset'] = "OFFSET $offset";
        return $this;
    }

    public function orderBy($orderBy) {
        $this->orderBy = $orderBy;
        $this->query['orderBy'] = "ORDER BY $orderBy";
        return $this;
    }

    public function groupBy($groupBy) {
        $this->groupBy = $groupBy;
        $this->query['groupBy'] = "GROUP BY $groupBy";
        return $this;
    }

    public function insert($data) {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_values($data));

        $query = "INSERT INTO $this->tableName ($columns) VALUES ($values)";

        $this->connection->query($query);
    }

    public function update($data) {
        $set = '';
        foreach ($data as $column => $value) {
            $set .= "$column = '$value', ";
        }
        $set = rtrim($set, ', ');

        $query = "UPDATE $this->tableName SET $set";

        if (!empty($this->conditions)) {
            $query .= ' WHERE ' . $this->query['where'];
        }

        $this->connection->query($query);
    }

    public function delete() {
        $query = "DELETE FROM $this->tableName WHERE " . $this->query['where'];

        $this->connection->query($query);
    }

        
    public function get($selections = ['*'])
    {
        $this->select($selections);
        $query = $this->buildQuery();
        $result = $this->connection->query($query);
        $this->resetQuery();
        return new Collection($result->fetch_all(MYSQLI_ASSOC));
    }

    public function first($selections = ['*'])
    {
        $this->select($selections);
        $query = $this->buildQuery();
        $result = $this->connection->query($query);
        $this->resetQuery();
        return Collection::make($result->fetch_assoc())->first();
    }

    public static function find($id, $selections = ['*'])
    {
        $db = new self();
        $db->select($selections);
        $query = $db->buildQuery();
        $result = $db->connection->query($query);
        $db->resetQuery();
        return Collection::make($result->fetch_assoc())->first();
    }

    private function buildQuery() {
        $query = '';

        foreach ($this->query as $key => $value) {
            if ($value == '') {
                continue;
            }

            switch ($key) {
                case 'select':
                    $query .= 'SELECT ' . $value . ' ';
                    break;
                case 'from':
                    $query .= $value . ' ';
                    break;
                case 'where':
                    $query .= 'WHERE ' . $value . ' ';
                    break;
                case 'limit':
                    $query .= $value . ' ';
                    break;
                case 'offset':
                    $query .= $value . ' ';
                    break;
                case 'orderBy':
                    $query .= $value . ' ';
                    break;
                case 'groupBy':
                    $query .= $value . ' ';
                    break;

            }
        }
        return $query;
    }

    public function __call($name, $arguments) {
        return $this->connection->$name(...$arguments);
    }

    public function __get($name) {
        return $this->connection->$name;
    }

    public function __set($name, $value) {
        $this->connection->$name = $value;
    }

    public function __isset($name) {
        return isset($this->connection->$name);
    }

    public function __unset($name) {
        unset($this->connection->$name);
    }

    public function __sleep() {
        return ['connection', 'tableName', 'conditions'];
    }

    public function __wakeup() {
        $this->connection = new \mysqli('localhost', 'username', 'password', 'database');
    }

    public function __toString() {
        return $this->connection->host_info;
    }
}
