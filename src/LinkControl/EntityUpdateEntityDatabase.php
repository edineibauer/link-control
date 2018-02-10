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
            foreach ($changes as $old => $novo) {
                $sql->exeCommand("ALTER TABLE " . PRE . $this->entity . " CHANGE {$old} " . parent::prepareSqlColumn($this->new[$novo]));
            }
        }
    }

    private function getChanges()
    {
        $data = null;
        foreach ($this->old as $i => $d) {
            if (isset($this->new[$i])) {
                if ($d['column'] !== $this->new[$i]['column'] || $d['unique'] !== $this->new[$i]['unique'] || $d['default'] !== $this->new[$i]['default'] || $d['size'] !== $this->new[$i]['size'])
                    $data[$d['column']] = $i;
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
            foreach ($del as $column => $id) {
                $this->dropKeysFromColumnRemoved($id);

                $sql = new SqlCommand();
                $sql->exeCommand("ALTER TABLE " . PRE . $this->entity . " DROP COLUMN " . $column);
            }
        }
    }

    private function getDeletes()
    {
        $data = null;
        foreach ($this->old as $i => $d) {
            if (!isset($this->new[$i])) {
                $data[$d['column']] = $i;
            }
        }

        return $data;
    }

    private function dropKeysFromColumnRemoved($id)
    {
        $dados = $this->old[$id];
        $sql = new SqlCommand();
        if ($dados['key'] === "list" || $dados['key'] === "extend") {
            $sql->exeCommand("ALTER TABLE " . PRE . $this->entity . " DROP FOREIGN KEY " . PRE . $dados['column'] . "_" . $this->entity . ", DROP INDEX fk_" . $dados['column']);

        } elseif ($dados['key'] === "list_mult" || $dados['key'] === "extend_mult") {
            $sql->exeCommand("DROP TABLE " . PRE . $this->entity . "_" . $dados['relation']);

        } else {

            //INDEX
            $sql->exeCommand("SHOW KEYS FROM " . PRE . $this->entity . " WHERE KEY_NAME ='index_{$id}'");
            if ($sql->getRowCount() > 0)
                $sql->exeCommand("ALTER TABLE " . PRE . $this->entity . " DROP INDEX index_" . $id);
        }

        //UNIQUE
        $sql->exeCommand("SHOW KEYS FROM " . PRE . $this->entity . " WHERE KEY_NAME ='unique_{$id}'");
        if ($sql->getRowCount() > 0)
            $sql->exeCommand("ALTER TABLE " . PRE . $this->entity . " DROP INDEX unique_" . $id);
    }

    private function addColumnsToEntity()
    {
        $add = $this->getAdds();

        if ($add) {
            $sql = new SqlCommand();
            foreach ($add as $id) {
                $dados = $this->new[$id];
                if (in_array($this->new[$id]['key'], ["list_mult", "extend_mult"])) {
                    parent::createRelationalTable($id, $dados);

                } else {
                    $sql->exeCommand("ALTER TABLE " . PRE . $this->entity . " ADD " . parent::prepareSqlColumn($this->new[$id]));

                    if (in_array($dados['key'], array('extend', 'list')))
                        parent::createIndexFk($id, $this->entity, $dados['column'], $dados['relation'], $dados['key']);
                }
            }
        }
    }

    private function getAdds()
    {
        $data = null;
        foreach ($this->new as $i => $d) {
            if (!isset($this->old[$i])) {
                $data[] = $i;
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
