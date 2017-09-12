<?php

namespace Eleanorsoft\Magento2;
use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PrepareModule extends CommandAbstract
{

    public function run(ArgumentList $argumentList)
    {
        $directory = $argumentList->get('magento2-module-path', './');
        $namespace = $argumentList->get('magento2-module-namespace', 'Eleanorsoft');
        $module = $argumentList->get('magento2-module-name', 'MyModule');

        $filesToRename = [];

        /** @var RecursiveDirectoryIterator $it */
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        $it->rewind();
        while($it->valid()) {

            Util::output(sprintf("File %s\n", $it->getSubPathName()));

            if (!$it->isDot() or $it->getFilename() == '.') {

                if (!$it->hasChildren()) {
                    $extension = pathinfo($it->key(), PATHINFO_EXTENSION);

                    if (in_array($extension, ['php', 'xml', 'json'])) {
                        $c = file_get_contents($it->key());

                        $c = str_replace(
                            ['__Namespace__', '__Module__', '__namespace__', '__module__'],
                            [$namespace, $module, strtolower($namespace), strtolower($module)],
                            $c
                        );

                        while (preg_match('/__config\.(.+)__/Usim', $c, $match)) {
                            $val = $argumentList->get($match[1]);
                            $c = str_replace('__config.' . $match[1] . '__', $val, $c);
                        }

                        file_put_contents($it->key(), $c);
                    }
                }

                $realPath = realpath($it->key());
                array_unshift($filesToRename, $realPath);
            }

            $it->next();
        }

        foreach ($filesToRename as $realPath) {
            $origname = pathinfo($realPath, PATHINFO_BASENAME);
            $name = str_replace(
                ['__Namespace__', '__Module__', '__namespace__', '__module__'],
                [$namespace, $module, strtolower($namespace), strtolower($module)],
                $origname
            );
            if ($name != $origname) {
                rename($realPath, dirname($realPath) . DIRECTORY_SEPARATOR . $name);
            }
        }
    }

}