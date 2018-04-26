<?php

namespace LinkControl;

use ConnCrud\SqlCommand;
use EntityForm\Metadados;

class EntityUpdateEntityDatabase extends EntityDatabase
{
    private $entity;
    private $old;
    private $new;

    public function __construct(string $entity, array $dados)
    {
        parent::__construct($entity);
        $this->setEntity($entity);
        $this->old = $dados;
        $this->new = Metadados::getDicionario($entity);
        $this->start();
    }

    /**
     * @param string $entity
     */
    public function setEntity(string $entity)
    {
        $this->entity = $entity;
    }

    public function start()
    {
        $this->checkChanges();
        $this->removeColumnsToEntity();
        $this->addColumnsToEntity();
        $this->createKeys();
    }

    private function checkChanges()
    {
        $changes = $this->getChanges();

        if ($changes) {
            $sql = new SqlCommand();
            foreach ($changes as $id => $dados)
                $sql->exeCommand("ALTER TABLE " . PRE . $this->entity . " CHANGE {$dados['column']} " . parent::prepareSqlColumn($this->new[$id]));
        }
    }

    private function getChanges()
    {
        $data = null;
        foreach ($this->old as $i => $d) {
            if (isset($this->new[$i])) {
                if ($d['column'] !== $this->new[$i]['column'] || $d['unique'] !== $this->new[$i]['unique'] || $d['default'] !== $this->new[$i]['default'] || $d['size'] !== $this->new[$i]['size'])
                    $data[$i] = $d;
            }
        }

        return $data;
    }

    /**
     * Remove colunas que existiam
     */
    private function removeColumnsToEntity()
    {
        $del = $this->getDeletes();

        if ($del) {
            foreach ($del as $id => $dic) {
                $this->dropKeysFromColumnRemoved($id, $dic);

                $sql = new SqlCommand();
                $sql->exeCommand("ALTER TABLE " . PRE . $this->entity . " DROP COLUMN " . $dic['column']);
            }
        }
    }

    private function getDeletes()
    {
        $data = null;
        foreach ($this->old as $i => $d) {
            if (!isset($this->new[$i]))
                $data[$i] = $d;

            if(!empty($d['select']) && (empty($this->new[$i]['select']) || $d['select'] !== $this->new[$i]['select'])) {
                foreach ($d['select'] as $e => $oldSelect) {
                    if(empty($this->new[$i]['select']) || !in_array($oldSelect, $this->new[$i]['select']))
                        $data[10001 + $e] = parent::getSelecaoUnique($d, $oldSelect)[1];
                }
            }

        }

        return $data;
    }

    private function dropKeysFromColumnRemoved($id, $dados)
    {
        $sql = new SqlCommand();
        if ($dados['key'] === "list" || $dados['key'] === "extend" || $dados['key'] === "selecao" || $dados['key'] === "selecaoUnique" || $dados['key'] === "publisher") {
            $sql->exeCommand("ALTER TABLE " . PRE . $this->entity . " DROP FOREIGN KEY " . PRE . $dados['column'] . "_" . $this->entity . ", DROP INDEX fk_" . $dados['column']);

        } elseif ($dados['key'] === "list_mult" || $dados['key'] === "extend_mult" || $dados['key'] === "selecao_mult") {
            $sql->exeCommand("DROP TABLE " . PRE . $this->entity . "_" . $dados['relation']);

        } elseif ($id < 10000){

            //INDEX
            $sql->exeCommand("SHOW KEYS FROM " . PRE . $this->entity . " WHERE KEY_NAME ='index_{$id}'");
            if ($sql->getRowCount() > 0)
                $sql->exeCommand("ALTER TABLE " . PRE . $this->entity . " DROP INDEX index_" . $id);
        }

        //UNIQUE
        if ($id < 10000) {
            $sql->exeCommand("SHOW KEYS FROM " . PRE . $this->entity . " WHERE KEY_NAME ='unique_{$id}'");
            if ($sql->getRowCount() > 0)
                $sql->exeCommand("ALTER TABLE " . PRE . $this->entity . " DROP INDEX unique_" . $id);
        }
    }

    private function addColumnsToEntity()
    {
        $add = $this->getAdds();

        if ($add) {
            $sql = new SqlCommand();
            foreach ($add as $id => $dados) {
                if (in_array($dados['key'], ["list_mult", "extend_mult", "selecao_mult"])) {
                    parent::createRelationalTable($dados);

                } else {
                    $sql->exeCommand("ALTER TABLE " . PRE . $this->entity . " ADD " . parent::prepareSqlColumn($dados));

                    if (in_array($dados['key'], array('extend', 'list', "selecao", "selecaoUnique")))
                        parent::createIndexFk($this->entity, $dados['column'], $dados['relation'], "", $dados['key']);
                    elseif($dados['key'] === "publisher")
                        parent::createIndexFk($this->entity, $dados['column'], "usuarios", "", "publisher");
                }
            }
        }
    }

    private function getAdds()
    {
        $data = null;
        $i = 10000;
        foreach ($this->new as $e => $dic) {
            if (!isset($this->old[$e]))
                $data[$e] = $dic;


            if(!empty($dic['select']) && (empty($this->old[$e]['select']) || $dic['select'] !== $this->old[$e]['select'])) {
                foreach ($dic['select'] as $newSelect) {
                    if(empty($this->old[$e]['select']) || !in_array($newSelect, $this->old[$e]['select'])) {
                        $data[$i] = parent::getSelecaoUnique($dic, $newSelect)[1];
                        $i++;
                    }
                }
            }
        }

        return $data;
    }

    private function createKeys()
    {
        $sql = new SqlCommand();
        foreach ($this->new as $i => $dados) {

            $sql->exeCommand("SHOW KEYS FROM " . PRE . $this->entity . " WHERE KEY_NAME = 'unique_{$i}'");
            if ($sql->getRowCount() > 0 && !$dados['unique'])
                $sql->exeCommand("ALTER TABLE " . PRE . $this->entity . " DROP INDEX unique_" . $i);
            else if ($sql->getRowCount() === 0 && $dados['unique'])
                $sql->exeCommand("ALTER TABLE `" . PRE . $this->entity . "` ADD UNIQUE KEY `unique_{$i}` (`{$dados['column']}`)");

        }
    }
}
