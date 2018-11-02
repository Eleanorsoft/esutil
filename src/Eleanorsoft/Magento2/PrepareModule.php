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
        $githubPath = $argumentList->get('github-module-path', 'Eleanorsoft/magento2-entitymodule-template');
        $tmp = explode('/', $githubPath);
        $githubModuleName = end($tmp);

        $directory = $argumentList->get('magento2-module-path', './');
        if ($githubPath) {
            Util::output(sprintf("Downloading %s\n", $githubPath));

            $githubUrl = sprintf('https://github.com/%s/archive/master.zip', $githubPath);
            $archive = file_get_contents($githubUrl);
            if (!$archive) {
                throw new \Exception("Can't download template from github");
            }

            $filename = uniqid();
            file_put_contents($filename, $archive);
            $zip = new \ZipArchive();
            $res = $zip->open($filename);
            if ($res === true) {
                @mkdir($directory, 0777, true);
                $zip->extractTo($directory);
                $zip->close();
                @unlink($filename);

                $directory.= $githubModuleName . '-master/';

            } else {
                throw new \Exception("Can't extract archive");
            }

        }

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