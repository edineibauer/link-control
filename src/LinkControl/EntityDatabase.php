<?php

namespace LinkControl;

use ConnCrud\SqlCommand;
use EntityForm\Metadados;
use Helpers\Check;

abstract class EntityDatabase
{
    private $entity;
    private $indice = 10000;

    /**
     * @param string $entityName
     * @param array $dados
     */
    public function __construct(string $entityName)
    {
        $this->entity = $entityName;
    }


    protected function createRelationalTable($dados)
    {
        $table = $this->entity . "_" . $dados['relation'] . "_" . $dados['column'];

        $string = "CREATE TABLE IF NOT EXISTS `" . PRE . $table . "` ("
            . "`{$this->entity}_id` INT(11) NOT NULL,"
            . "`{$dados['relation']}_id` INT(11) NOT NULL"
            . ") ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $this->exeSql($string);

        $this->createIndexFk($table, $this->entity . "_id", $this->entity, $dados['column']);
        $this->createIndexFk($table, $dados['relation'] . "_id", $dados['relation'], $dados['column']);
    }

    protected function createIndexFk($table, $column, $tableTarget, $col = "", $key = null)
    {
        $delete = !$key || $key === "extend" ? "CASCADE" : ($key === "publisher" ? "SET NULL" : "RESTRICT");
        $col = !empty($col) ? "_" . $col : "";

        $this->exeSql("ALTER TABLE `" . PRE . $table . "` ADD KEY `fk_" . $column . $col . "` (`{$column}`)");
        $this->exeSql("ALTER TABLE `" . PRE . $table . "` ADD CONSTRAINT `" . PRE . $column . $col . "_" . $table . "` FOREIGN KEY (`{$column}`) REFERENCES `" . PRE . $tableTarget . "` (`id`) ON DELETE " . $delete . " ON UPDATE NO ACTION");
    }

    protected function prepareSqlColumn($dados)
    {
        $type = in_array($dados['type'], ["float", "real", "decimal", "double"]) ? "double" : $dados['type'];
        return "`{$dados['column']}` {$type} "
            . (!empty($dados['size']) ? "({$dados['size']}) " : ($dados['type'] === "varchar" ? "(254) " : " "))
            . ($dados['default'] === false ? "NOT NULL " : "")
            . ($dados['default'] !== false && !empty($dados['default']) ? $this->prepareDefault($dados['default']) : ($dados['default'] !== false ? "DEFAULT NULL" : ""));
    }

    protected function getSelecaoUnique(array $data, string $select)
    {
        $inputType = json_decode(file_get_contents(PATH_HOME . "vendor/conn/entity-form/entity/input_type.json"), true);
        $dic = Metadados::getDicionario($data['relation']);
        foreach ($dic as $item) {
            if($item['column'] === $select){
                $dicionario = array_merge($inputType['default'], $inputType['selecao']);
                $dicionario["nome"] = $select;
                $dicionario["column"] = Check::name($select) . '__' . Check::name($data['column']);
                $dicionario["relation"] = $item['relation'];
                $dicionario["key"] = "selecaoUnique";
                $this->indice++;

                return [$this->indice, $dicionario];
            }
        }

        return null;
    }

    protected function exeSql($sql)
    {
        $exe = new SqlCommand();
        $exe->exeCommand($sql);
        if($exe->getErro()) {
            var_dump($sql);
            var_dump($exe->getErro());
        }
    }

    private function prepareDefault($default)
    {
        if ($default === 'datetime' || $default === 'date' || $default === 'time')
            return "";

        if (is_numeric($default))
            return "DEFAULT {$default}";

        return "DEFAULT '{$default}'";
    }
}
