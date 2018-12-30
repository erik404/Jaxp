<?php
/**
 * Copyright (c) 2018 Erik-Jan van de Wal (ejvandewal@gmail.com)
 */

require(__DIR__ . '/Entities/Menu.php');
require(__DIR__ . '/Entities/Food.php');
require(__DIR__ . '/../src/Jaxp.php');

class Example
{

    const FILE_EXAMPLE = '/example.xml';

    private $xmlFile;

    public function __construct()
    {
        $this->xmlFile = __DIR__ . $this::FILE_EXAMPLE;

        if (!file_exists($this->xmlFile)) {
            throw new \Exception(sprintf('Could not find file %s', $this::FILE_EXAMPLE));
        }
    }

    public function run()
    {
        $menu = new \erik404\Jaxp\example\Entities\Menu();
        $jaxp = new \erik404\Jaxp($this->xmlFile, $menu);

        $jaxp->hydrateParent();

        print_r($jaxp->returnHydratedObjects());

    }

}

$example = new Example();
$example->run();