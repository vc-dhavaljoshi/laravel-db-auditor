<?php

namespace Vcian\LaravelDBPlayground\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vcian\LaravelDBPlayground\Constants\Constant;

class AuditService
{
    protected $results = Constant::ARRAY_DECLARATION;

    protected $headers = Constant::ARRAY_DECLARATION;

    protected $tableList;

    public function __construct(protected DBConnectionService $dBConnectionService)
    {
        $this->tableList = $this->dBConnectionService->getTableList();
    }

    /**
     * Get All the Constrains list with table name and column.
     * @param string $input
     * @return array
     */
    public function getList($input): array
    {
        try {
            if ($this->tableList) {
                foreach ($this->tableList as $tableName) {
                    $this->checkConstrain($tableName, $input);
                }
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }
        return $this->results;
    }

    /**
     * Check Constrain
     * @param string $tableName
     * @param string $input
     * @return array
     */
    public function checkConstrain(string $tableName, string $input): array
    {
        try {

            if ($input === Constant::CONSTRAIN_ALL_KEY) {
                $result = DB::select("SHOW KEYS FROM {$tableName}");
                $this->checkForeignKeyData($tableName);
            } else {
                $result = DB::select("SHOW KEYS FROM {$tableName} WHERE Key_name LIKE '%" . strtolower($input) . "%'");
            }

            if ($input == Constant::CONSTRAIN_FOREIGN_KEY) {
                $this->checkForeignKeyData($tableName);
            }

            if ($result) {
                foreach ($result as $value) {
                    array_push($this->results, [$tableName, $value->Column_name, $value->Key_name]);
                }
            } else {
                array_push($this->results, [$tableName, Constant::DASH, Constant::DASH]);
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $this->results;
    }

    /**
     * Set Display Headers
     * @param array
     * @return void
     */
    public function setHeaders(mixed $headers): void
    {
        if (is_array($headers)) {
            $this->headers = $headers;
        } else {
            array_push($this->headers, $headers);
        }
    }

    /**
     * Get Display Headers
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Add Foreign Key
     * @param string
     * @return void
     */
    public function checkForeignKeyData(string $tableName): void
    {
        try {
            $resultForeignKey = DB::select("SELECT i.TABLE_SCHEMA, i.TABLE_NAME, i.CONSTRAINT_TYPE,k.COLUMN_NAME, i.CONSTRAINT_NAME, 
            k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME FROM information_schema.TABLE_CONSTRAINTS i 
            LEFT JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME 
            WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY' AND i.TABLE_SCHEMA = '" . config("database.connections.mysql.database") . "' AND i.TABLE_NAME = '" . $tableName . "'");

            if ($resultForeignKey) {
                foreach ($resultForeignKey as $value) {
                    array_push($this->results, [$value->TABLE_NAME, $value->COLUMN_NAME, Constant::CONSTRAIN_FOREIGN_KEY, $value->REFERENCED_TABLE_NAME, $value->REFERENCED_COLUMN_NAME]);
                }
                $this->setHeaders(Constant::HEADER_TITLE_REFERENCED_TABLE_NAME);
                $this->setHeaders(Constant::HEADER_TITLE_REFERENCED_COLUMN_NAME);
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    /**
     * Check table Exist or not and value
     * @param string $tableName
     * @param string $input
     */
    public function getTableList(string $tableName, string $input)
    {

        try {

            $checkTableStatus = Constant::ARRAY_DECLARATION;
            if (in_array($checkTableStatus, $this->tableList)) {
                return true;
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        return true;
    }

    /**
     * Get Field List By User Input
     */
    public function getFieldByUserInput(string $tableName)
    {
        $fields = Constant::ARRAY_DECLARATION;
        try {
            $fields = $this->getFields($tableName, "int");
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $fields;
    }

    /**
     * Get Fields
     */
    public function getFields($tableName, $type)
    {
        $fieldList = Constant::ARRAY_DECLARATION;
        $fieldType = $this->dBConnectionService->getFieldWithType($tableName);
        if ($fieldType) {
            foreach ($fieldType as $field) {
                if (str_contains($field->Type, $type)) {
                    if (!$this->getConstrainFields($tableName, $field->Field)) {
                        array_push($fieldList, $field->Field);
                    }
                }
            }
        }
        return $fieldList;
    }

    /**
     * Add Constrain
     */
    public function addConstrain($table, $field, $constrain)
    {
        try {
            $query = "ALTER TABLE " . $table . " ADD " . $constrain . "";
            if ($constrain == Constant::CONSTRAIN_INDEX_KEY) {
                $query .= " " . $field . "_" . strtolower($constrain);
            }
            $query .= " (" . $field . ")";
            DB::select($query);
            return true;
        } catch (Exception $exception) {
            return $exception->getMessage();
            Log::error($exception->getMessage());
        }
    }

    /**
     * Field Exist
     */
    public function getConstrainFields($table, $fieldName)
    {
        $result = DB::select("SHOW KEYS FROM {$table} WHERE Key_name LIKE '%" . strtolower($fieldName) . "%'");
        if ($result) {
            return true;
        }
        return false;
    }
}
