<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * SQL query builder
 */
class CRequest {
  use RequestTrait;

  public $select = array();
  public $table  = array();
  public $ljoin  = array();
  public $rjoin  = array();
  public $where  = array();
  public $group  = array();
  public $having = array();
  public $order  = array();
  public $forceindex = array();
  public $limit  = "";

  /** @var bool */
  private $strict_mode = true;

  /**
   * CRequest constructor.
   *
   * @param bool $strict
   */
  public function __construct(bool $strict = true) {
    $this->strict_mode = $strict;
  }

  /**
   * SELECT [...]
   * 
   * @param mixed $select An array or a string of the SELECT clause
   * 
   * @return CRequest Current request
   */
  function addSelect($select) {
    if (is_array($select)) {
      $this->select = array_merge($this->select, $select);
    }
    elseif (is_string($select)) {
      $this->select[] = $select;
    }
    
    return $this;
  }
  
  /**
   * SELECT [...] AS [...]
   * 
   * @param string $column An column name in the SELECT clause
   * @param string $as     The columns's alias
   * 
   * @return CRequest Current request
   */
  function addColumn($column, $as = null) {
    if ($as) {
      $this->select[$as] = $column; 
    }
    else {
      $this->select[] = $column; 
    }

    return $this;
  }
  
  /**
   * FROM [...]
   * 
   * @param mixed $table An array or a string of the FROM clause
   * 
   * @return CRequest Current request
   */
  function addTable($table) {
    if (is_array($table)) {
      $this->table = array_merge($this->table, $table);
    }
    elseif (is_string($table)) {
      $this->table[] = $table;
    }
    
    return $this;
  }
  
  /**
   * LEFT JOIN [...] ON [...]
   * 
   * @param mixed $ljoin An array or a string of the LEFT JOIN clause
   * 
   * @return CRequest Current request
   */
  function addLJoin($ljoin) {
    if (is_array($ljoin)) {
      $this->ljoin = array_merge($this->ljoin, $ljoin);
    }
    elseif (is_string($ljoin)) {
      $this->ljoin[] = $ljoin;
    }
    
    return $this;
  }
  
  /**
   * LEFT JOIN [...] ON [...]
   * 
   * @param string $table The table name of the LEFT JOIN clause
   * @param string $on    The conditional expression of the LEFT JOIN clause
   * 
   * @return CRequest Current request
   */
  function addLJoinClause($table, $on) {
    $this->ljoin[$table] = $on;
    
    return $this;
  }
  
  /**
   * RIGHT JOIN [...] ON [...]
   * 
   * @param mixed $ljoin An array or a string of the RIGHT JOIN statement
   * 
   * @return CRequest Current request
   */
  function addRJoin($ljoin) {
    if (is_array($ljoin)) {
      $this->rjoin = array_merge($this->rjoin, $ljoin);
    }
    
    return $this;
  }
  
  /**
   * RIGHT JOIN [...] ON [...]
   *
   * @param string $table The table name of the LEFT JOIN clause
   * @param string $on    The conditional expression of the LEFT JOIN clause
   * 
   * @return CRequest Current request
   */
  function addRJoinClause($table, $on) {
    $this->rjoin[$table] = $on;

    return $this;
  }
  
  /**
   * WHERE [...]
   * 
   * @param mixed $where An array or a string of the SELECT clause
   * 
   * @return CRequest Current request
   */
  function addWhere($where) {
    if (is_array($where)) {
      $this->where = array_merge($this->where, $where);
    }
    elseif (is_string($where)) {
      $this->where[] = $where;
    }
    
    return $this;
  }
  
  /**
   * WHERE [...]
   * 
   * @param string $key   The field to perform the test on
   * @param string $value The test to be performed
   * 
   * @return CRequest Current request
   */
  function addWhereClause($key, $value) {
    if ($key) {
      $this->where[$key] = $value;
    }
    else {
      $this->where[] = $value;
    }
    
    return $this;
  }

  /**
   * GROUP BY [...]
   * 
   * @param mixed $group An array or a string of the GROUP BY clause
   * 
   * @return CRequest Current request
   */
  function addGroup($group) {
    if (is_array($group)) {
      $this->group = array_merge($this->group, $group);
    }
    elseif (is_string($group)) {
      $this->group[] = $group;
    }
    
    return $this;
  }
  
  /**
   * HAVING [...]
   * 
   * @param mixed $having An array or a string of the HAVING clause
   * 
   * @return CRequest Current request
   */
  function addHaving($having) {
    if (is_array($having)) {
      $this->having = array_merge($this->having, $having);
    }
    elseif (is_string($having)) {
      $this->having[] = $having;
    }
    
    return $this;
  }

  /**
   * ORDER BY [...]
   * 
   * @param mixed $order An array or a string of the ORDER BY clause
   * 
   * @return CRequest Current request
   */
  function addOrder($order) {
    if (is_array($order)) {
      $this->order = array_merge($this->order, $order);
    }
    elseif (is_string($order)) {
      $this->order[] = $order;
    }

    return $this;
  }
  
  /**
   * FORCE INDEX [...]
   * 
   * @param mixed $forceindex An array or a string of the FORCE INDEX clause
   * 
   * @return CRequest Current request
   */
  function addForceIndex($forceindex) {
    if (is_array($forceindex)) {
      $this->forceindex = array_merge($this->forceindex, $forceindex);
    }
    elseif (is_string($forceindex)) {
      $this->forceindex[] = $forceindex;
    }
    
    return $this;
  }
  
  /**
   * LIMIT [...]
   * 
   * @param mixed $limit An array or a string of the LIMIT statement
   * 
   * @return CRequest Current request
   */
  function setLimit($limit) {
    $this->limit = $limit;
    
    return $this;
  }
  
  /**
   * Create an artificial limit from an array of results
   * 
   * @param array  $list  The list 
   * @param string $limit The limit, MySQL styled
   * 
   * @return array The slice of the list 
   */
  static function artificialLimit($list, $limit) {
    preg_match("/(?:(\d+),\s*)?(\d+)/", $limit, $matches);
    $offset = intval($matches[1]);
    $length = intval($matches[2]);
    
    return array_slice($list, $offset, $length, true);
  }
  
  /**
   * Returns the SQL query fragment containing everything after the action clause (INSERT, SELECT, UPDATE, DELETE)
   * 
   * @param string $from The table names
   * 
   * @return string
   */
  function getRequestFrom($from) {
    $sql = "\nFROM $from";
    
    // Force index by fields
    if ($this->forceindex) {
      $sql .= "\nFORCE INDEX (";
      $sql .= is_array($this->forceindex) ? implode(', ', $this->forceindex) : $this->forceindex;
      $sql .= ")";
    }
    
    // Left join clauses
    if ($this->ljoin) {
      assert(is_array($this->ljoin));
      foreach ($this->ljoin as $table => $condition) {
        if (is_string($table)) {
          $sql .= "\nLEFT JOIN `$table` ON $condition";
        }
        else {
          $sql .= "\nLEFT JOIN $condition";
        }
      }
    }
    
    // Right join clauses
    if ($this->rjoin) {
      assert(is_array($this->rjoin));
      foreach ($this->rjoin as $table => $condition) {
        $sql .= "\nRIGHT JOIN `$table` ON $condition";
      }
    }
    
    // Where clauses
    $where = array();
    if (is_array($this->where)) {
      $where = $this->where;
      foreach ($where as $field => $eq) {
        if (is_string($field)) {
          $rep = str_replace('.', '`.`', $field);
          $where[$field] = "`$rep` $eq";
        }
        
        $where[$field] = "($where[$field])";
      }
    }
    
    if ($this->where) {
      $sql .= "\nWHERE ";
      $sql .= is_array($this->where) ? implode("\nAND ", $where) : $this->where;
    }
    
    // Group by fields
    if ($this->group) {
      $sql .= "\nGROUP BY ";

      $group_by = is_array($this->group) ? implode(', ', $this->group) : $this->group;

      if ($this->strict_mode) {
        $this->checkGroupBy($group_by);
      }

      $sql .= $group_by;
    }
    
    // Having
    $having = array();
    if (is_array($this->having)) {
      $having = $this->having;
      foreach ($having as $field => $eq) {
        if (is_string($field)) {
          $rep = str_replace('.', '`.`', $field);
          $having[$field] = "`$rep` $eq";
        }
        
        $having[$field] = "($having[$field])";
      }
    }
    
    if ($this->having) {
      $sql .= "\nHAVING ";
      $sql .= is_array($this->having) ? implode("\nAND ", $having) : $this->having;
    }
    
    // Order by fields
    if ($this->order) {
      $sql .= "\nORDER BY ";

      $order_by = is_array($this->order) ? implode(', ', $this->order) : $this->order;

      if ($this->strict_mode) {
        $this->checkOrderBy($order_by);
      }

      $sql .= $order_by;
    }
    
    // Limits
    if ($this->limit) {
      if ($this->strict_mode) {
        $this->checkLimit($this->limit);
      }

      $sql .= "\nLIMIT $this->limit";
    }
    
    return $sql;
  }
  
  /**
   * Make the SQL general SELECT query string
   * 
   * @param CStoredObject $object     Object on which table we look up rows, already added tables if null
   * @param bool          $found_rows Found rows count alternative
   * 
   * @return string
   */
  function makeSelect(CStoredObject $object = null, $found_rows = false) {
    // Stored object binding
    if ($object) {
      // Get the columns
      if (!count($this->select)) {
        // Restrain loading to a column collection
        if (is_array($object->_spec->columns)) {
          $this->select[] = "`{$object->_spec->table}`.`{$object->_spec->key}`";
          foreach ($object->_spec->columns as $_column) {
            $this->select[] = "`{$object->_spec->table}`.`$_column`";
          }
        }
        else {
          $this->select[] = "`{$object->_spec->table}`.*";
        }
      }

      // Get the table
      $this->table = array($object->_spec->table);
    }

    // Select clauses
    $select = array();
    foreach ($this->select as $as => $column) {
      $select[$as] = is_string($as) ? "$column AS `$as`" : $column;
    }
    
    $select = implode(",\n", $select);
    
    $sql = $found_rows ? "SELECT SQL_CALC_FOUND_ROWS $select" : "SELECT $select";
    
    // Table clauses
    $tables = array();
    foreach ($this->table as $_table) {
      $tables[] = (strpos($_table, ' ') === false && strpos($_table, '`') === false) ? "`$_table`" : $_table;
    }

    $table = implode(', ', $tables);

    return $sql . $this->getRequestFrom($table);
  }

  /**
   * Returns the SQL query string that count the number of rows
   * 
   * @param CStoredObject $object  Object on which table we prefix selects, one prefix if null
   * @param array         $columns The columns to include in the SELECT clause
   * 
   * @return string A COUNT request
   */
  function makeSelectCount(CStoredObject $object = null, $columns = array()) {
    $this->select = array();
    $this->addSelect("COUNT(*) AS `total`");
    $this->addSelect($columns);

    return $this->makeSelect($object);
  }

  /**
   * Returns the SQL string that get ids for concerned object
   *
   * @param CStoredObject $object Object concerned
   *
   * @return string
   */
  function makeSelectIds(CStoredObject $object) {
    $spec = $object->_spec;
    $this->addSelect("`$spec->table`.`$spec->key`");
    return $this->makeSelect($object);
  }


  /**
   * Make the SQL general DELETE query string
   *
   * @param CStoredObject $object Object on which table we look up rows, already added tables if null
   *
   * @return string
   */
  function makeDelete(CStoredObject $object = null) {
    // Stored object binding
    if ($object) {
      // Get the table
      $this->table = array($object->_spec->table);
    }

    // Table clauses
    $table = implode(', ', $this->table);

    // Force Index incompatible with DELETEs
    $this->forceindex = array();

    return "DELETE " . $this->getRequestFrom($table);
  }
}
