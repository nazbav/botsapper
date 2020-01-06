<?php
/**
 * @author Vasiliy Makogon, makogon-vs@yandex.ru
 * @link https://github.com/Vasiliy-Makogon/Database/
 *
 * Обёртка над объектом mysqli_result.
 */

namespace Krugozor\Database\Mysql;

/**
 * Class Statement
 *
 * @package Krugozor\Database\Mysql
 */
class Statement {
    /**
     * Рузультат SQL-операции в виде объекта mysqli_result.
     *
     * @var mysqli_result
     */
    private $mysqli_result = null;

    /**
     * @param mysqli_result
     */
    public function __construct(\mysqli_result $mysqli_result) {
        $this->mysqli_result = $mysqli_result;
    }

    /**
     * Извлекает результирующий ряд в виде ассоциативного массива.
     *
     * @see mysqli_fetch_assoc
     *
     * @param void
     *
     * @return array
     */

    public function fetch_assoc() {
        return $this->mysqli_result->fetch_assoc();
    }

    /**
     * Извлекает результирующий ряд в виде массива.
     *
     * @see mysqli_fetch_row
     *
     * @param void
     *
     * @return array
     */
    public function fetch_row() {
        return $this->mysqli_result->fetch_row();
    }

    /**
     * Извлекает результирующий ряд в виде объекта.
     *
     * @see mysqli_fetch_object
     *
     * @param void
     *
     * @return stdClass
     */
    public function fetch_object() {
        return $this->mysqli_result->fetch_object();
    }

    /**
     * Возвращает результат в виде массива ассоциативных массивов.
     *
     * @param void
     *
     * @return array
     */
    public function fetch_assoc_array() {
        $array = array();

        while ($row = $this->mysqli_result->fetch_assoc()) {
            $array[] = $row;
        }
        return $array;
    }

    /**
     * Возвращает результат в виде массива массивов.
     *
     * @param void
     *
     * @return array
     */
    public function fetch_row_array() {
        $array = array();

        while ($row = $this->mysqli_result->fetch_row()) {
            $array[] = $row;
        }

        return $array;
    }

    /**
     * Возвращает результат в виде массива объектов.
     *
     * @param void
     *
     * @return array
     */
    public function fetch_object_array() {
        $array = array();

        while ($row = $this->mysqli_result->fetch_object()) {
            $array[] = $row;
        }

        return $array;
    }

    /**
     * Возвращает значение первого поля результирующей таблицы.
     *
     * @param void
     *
     * @return string
     */
    public function getOne() {
        $row = $this->mysqli_result->fetch_row();

        return $row[0];
    }

    /**
     * Возвращает количество рядов в результате.
     * Эта команда верна только для операторов SELECT.
     *
     * @see mysqli_num_rows
     *
     * @param void
     *
     * @return int
     */
    public function getNumRows() {
        return $this->mysqli_result->num_rows;
    }

    /**
     * Возвращает объект результата mysqli_result.
     *
     * @param void
     *
     * @return mysqli_result
     */
    public function getResult() {
        return $this->mysqli_result;
    }

    /**
     * __destruct
     */
    public function __destruct() {
        $this->free();
    }

    /**
     * Освобождает память занятую результатами запроса.
     *
     * @param void
     *
     * @return void
     */
    public function free() {
        $this->mysqli_result->free();
    }
}
