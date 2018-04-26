<?php

namespace LinkControl;

use Entity\Entity;
use EntityForm\Metadados;
use Helpers\Check;

class EntityCreateEntityDatabase extends EntityDatabase
{
    /**
     * @param string $entity
     * @param array $dados
     */
    public function __construct(string $entity, array $dados)
    {
        parent::__construct($entity);
        $data = Metadados::getDicionario($entity);

        if ($data) {
            if (isset($dados['dicionario']) && !empty($dados['dicionario'])) {
                new EntityUpdateEntityDatabase($entity, $dados['dicionario']);
            } else {
                $data = $this->checkCreateMultSelectField($entity, $data);
                $this->prepareCommandToCreateTable($entity, $data);
                $this->createKeys($entity, $data);
            }
        }
    }

    /**
     * @param string $entity
     * @param array $dicionario
     * @return array
     */
    private function checkCreateMultSelectField(string $entity, array $dicionario) :array
    {
        foreach ($dicionario as $dic) {
            if(in_array($dic['key'], ["list_mult", "extend_mult", "selecao_mult", "list", "extend", "selecao"]) && !empty($dic['select'])) {
                $relDic = Metadados::getDicionario($dic['relation']);
                foreach ($dic['select'] as $select) {
                    foreach ($relDic as $item) {
                        if($item['column'] === $select) {
                            $ret = parent::getSelecaoUnique($dic, $select);
                            $dicionario[$ret[0]] = $ret[1];
                        }
                    }
                }
            }
        }

        return $dicionario;
    }

    /**
     * @param string $entity
     * @param array $data
     */
    private function prepareCommandToCreateTable(string $entity, array $data)
    {
        $string = "CREATE TABLE IF NOT EXISTS `" . PRE . $entity . "` (`id` INT(11) NOT NULL";
        foreach ($data as $dados) {
            if (!in_array($dados['key'], ["list_mult", "extend_mult", "selecao_mult"])) {
                $string .= ", " . parent::prepareSqlColumn($dados);
            }
        }

        $string .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8";

        parent::exeSql($string);
    }

    private function createKeys(string $entity, array $data)
    {
        parent::exeSql("ALTER TABLE `" . PRE . $entity . "` ADD PRIMARY KEY (`id`), MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");

        foreach ($data as $i => $dados) {
            if ($dados['unique'])
                parent::exeSql("ALTER TABLE `" . PRE . $entity . "` ADD UNIQUE KEY `unique_{$i}` (`{$dados['column']}`)");

            if (in_array($dados['key'], ["title", "link", "status", "email", "cpf", "cnpj", "telefone", "cep"]))
                parent::exeSql("ALTER TABLE `" . PRE . $entity . "` ADD KEY `index_{$i}` (`{$dados['column']}`)");

            if (in_array($dados['key'], array("extend", "extend_mult", "list", "list_mult", "selecao", "selecao_mult", "selecaoUnique"))) {
                if (in_array($dados['key'], ["extend", "list", "selecao", "selecaoUnique"]))
                    parent::createIndexFk($entity, $dados['column'], $dados['relation'], "", $dados['key']);
                else
                    parent::createRelationalTable($dados);
            } elseif($dados['key'] === "publisher") {
                parent::createIndexFk($entity, $dados['column'], "usuarios", "", "publisher");
            }
        }
    }
}
