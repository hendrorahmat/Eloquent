<?php

namespace app\Models;
use \PDO;

class Database
{
    private static $instance = null;
    private $_conn,$_table,$_columns = '*',$_query,$_statement,
            $_params = [],$_attr,$_test,$_prevData = [],$_bridge = [];

    private function __construct()
    {
        try {
            $this->_conn = new PDO('mysql:host='.Config::get('mysql/host').
                ';dbname='.Config::get('mysql/db'),Config::get('mysql/username'),
                Config::get('mysql/password'));
            $this->_conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    private function run()
    {
        // var_dump($this->_params);
        // die($this->_query.' '.$this->_attr);
        try {
            $this->_statement = $this->_conn->prepare($this->_query.' '.$this->_attr);
            $this->_statement->execute($this->_params);
            $this->flush();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function flush()
    {
        $this->_attr ='';
        $this->_query ='';
        $this->_params =[];
        $this->_prevData = [];
        $this->_bridge =[];

    }

    public function setTable($table)
    {
        $this->_table = $table;
        return $this;
    }

    public function select($column = '*')
    {
        $this->_query = "SELECT $column FROM $this->_table";
        $this->_columns = $column;

        return $this;
    }

    public function where($column,$operator,$value,$bridge = ' AND ')
    {
        $this->_query = "SELECT $this->_columns FROM $this->_table WHERE";
        $this->_prevData[] = [
            'column'   => $column,
            'operator' => $operator,
            'value'    => $value
        ];
        $this->getWhere($bridge);

        return $this;
    }

    protected function getWhere($bridge)
    {
        $this->_attr     = '';
        $this->_params   = [];
        $this->_bridge[] = $bridge;

        $i = 1;
        foreach ($this->_prevData as $prevData) {
            $this->_attr .= $prevData['column'].' '.$prevData['operator']." ?";
            $this->_params[] = $prevData['value'];

            if ($i < count($this->_prevData)) {
                $this->_attr .= $this->_bridge[$i];
            }

            $i++;
        }
        return $this;
    }

    public function orwhere($column,$operator,$value)
    {
        $this->where($column,$operator,$value,$bridge = ' OR ');
        return $this;
    }

    public function all()
    {
        $this->run();
        return $this->_statement->fetchAll(PDO::FETCH_OBJ);
    }

    public function first()
    {
        $this->run();
        return $this->_statement->fetch(PDO::FETCH_OBJ);
    }

    public function create($fields = [])
    {
        $column = implode(", ", array_keys($fields));
        $values = '';
        $x = 1;
        foreach ($fields as $field) {
            $this->_params[] = $field;
            $values .= '?';
            if ($x < count($fields)) {
                $values .= ' ,';
            }
            $x++;
        }
        $this->_query = "INSERT INTO $this->_table($column) VALUES ($values)";
        $this->run();
    }

    public function update($fields = [])
    {
        $column ='';
        $x = 1;
        $totalPrev = count($this->_params);

        foreach ($fields as $key => $value) {
            $this->_params[] = $value;
            $column .= $key.'= ?';
            if ($x < count($fields)) {
                $column .=', ';
            }
            $x++;
        }

        for ($i=0; $i < $totalPrev; $i++) {
            $this->_params[] = array_shift($this->_params);
        }
        $this->_query = "UPDATE $this->_table SET $column WHERE";
        $this->run();
    }

    public function delete()
    {
        $this->_query = "DELETE FROM $this->_table WHERE";
        $this->run();
    }

    public function orderBy($column ='id',$type)
    {
        $this->_attr .= "ORDER BY $column $type";
        return $this;
    }
    public function take($number)
    {
        $this->_attr .= "LIMIT $number";
        return $this;
    }
}