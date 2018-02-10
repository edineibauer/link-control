<?php

namespace LinkControl;

use Helpers\Helper;

class EntityImport
{

    public function __construct(string $lib)
    {
        $this->checkEntityExist($lib);
    }

    private function checkEntityExist(string $lib)
    {
        if (file_exists(PATH_HOME . "vendor/conn/{$lib}/entity/cache/info")) {
            foreach (Helper::listFolder(PATH_HOME . "vendor/conn/{$lib}/entity/cache") as $file) {
                if ($file !== "info" && preg_match('/\w+\.json$/i', $file)) {
                    if (file_exists(PATH_HOME . "entity/cache/{$file}"))
                        return false;

                    $this->importEntity($file, $lib);
                }
            }
        }
    }

    /**
     * @param string $file
     * @param string $lib
    */
    private function importEntity(string $file, string $lib)
    {
        try {
            $entity = str_replace('.json', '', $file);
            Helper::createFolderIfNoExist(PATH_HOME . "entity");
            Helper::createFolderIfNoExist(PATH_HOME . "entity/cache");
            Helper::createFolderIfNoExist(PATH_HOME . "entity/cache/info");

            copy(PATH_HOME . "vendor/conn/{$lib}/entity/cache/{$file}", PATH_HOME . "entity/cache/{$file}");
            copy(PATH_HOME . "vendor/conn/{$lib}/entity/cache/info/{$file}", PATH_HOME . "entity/cache/info/{$file}");

            new EntityCreateEntityDatabase($entity, []);

        } catch (\Exception $e) {
            echo $e->getMessage() . " #linha {$e->getLine()}";
            die;
        }
    }
}