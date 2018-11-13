<?php

namespace Eleanorsoft\Magento2;

use Eleanorsoft\Phar\ArgumentList;
use Eleanorsoft\Phar\CommandAbstract;
use Eleanorsoft\Util;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Scaffold extends CommandAbstract
{

    public $moduleSourceTmpPath = 'var' . DIRECTORY_SEPARATOR . 'esutil' . DIRECTORY_SEPARATOR;

    /**
     * todo: What is its purpose?
     *
     * @var ArgumentList
     */
    protected $argumentList;

    public function run(ArgumentList $argumentList)
    {
        clearstatcache(true);
        Util::output("Search config in .esutil\n");
        if (realpath('.esutil')) {
            $config = parse_ini_file(realpath('.esutil'));
            Util::output("Got config from file: " . json_encode($config) . "\n");
            foreach ($config as $k => $v) {
                $argumentList->set($k, $v);
            }
        }

        $this->argumentList = $argumentList;
        $this->prepareFS();

        // full class name: Namespace\ModuleName\Type\Of\Class\ClassName
        $className = $this->getArgument('type');
        $classParts = explode('\\', $className);

        // download and extract
        Util::output("Download and extract source...\n");
        $repoName = $this->getGithubRepoNameByClassParts($classParts);
        $repoUrl = $this->getGithubRepoZipUrl($repoName);
        $repoFolder = $this->downloadAndExtract($repoUrl);
        $repoFolder = realpath($repoFolder) . DIRECTORY_SEPARATOR;

        $repoFolder.= $repoName . '-master' . DIRECTORY_SEPARATOR;

        // process module
        Util::output("Replace placeholders in $repoFolder...\n");
        $this->processModuleSource(
            $repoFolder,
            [
                'namespace' => $classParts[0],
                'module' => $classParts[1],
                'controller' => @$classParts[2] == 'Controller' ? @$classParts[count($classParts) - 2] : null,
                'action' => @$classParts[2] == 'Controller' ? end($classParts) : null,
                'model' => @$classParts[2] == 'Model' ? end($classParts) : null,
            ]
        );

        // copy to new location
        // all modules are in app/code. Namespace and module name are taken from the
        // full class path
        $magentoModulePath = sprintf('app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . '%s' . DIRECTORY_SEPARATOR . '%s' . DIRECTORY_SEPARATOR . '', $classParts[0], $classParts[1]);
        Util::output("Copy files from $repoFolder to $magentoModulePath...\n");
        $this->copyFolderContents($repoFolder, $magentoModulePath);

        // remove artifacts
        Util::output("Remove $repoFolder...\n");
        $this->rmrf($repoFolder);

        Util::output("Done\n");
    }

    protected function getArgument($code, $default = null, $formatters = [])
    {
        return $this->argumentList->get($code, $default, $formatters);
    }

    protected function processModuleSource($moduleFolder, $config)
    {
        $placeholders = [
            'magento2-module-namespace' => [
                'default' => 'Eleanorsoft',
                'placeholder' => '__Namespace__',
                'value' => @$config['namespace'],
            ],
            'magento2-module-name' => [
                'default' => 'MyModule',
                'placeholder' => '__Module__',
                'value' => @$config['module'],
            ],
            'magento2-controller-name' => [
                'default' => 'Index',
                'placeholder' => '__Controller__',
                'value' => @$config['controller'],
            ],
            'magento2-action-name' => [
                'default' => 'Index',
                'placeholder' => '__Action__',
                'value' => @$config['action'],
            ],
            'magento2-model-name' => [
                'default' => 'Item',
                'placeholder' => '__Model__',
                'value' => @$config['model'],
            ],
        ];

        $replaceArgs = ['placeholders' => [], 'values' => []];
        foreach ($placeholders as $code => $config) {
            if ($config['value'] === false) {
                $config['value'] = $this->getArgument($code, $config['default']);
            }

            $replaceArgs['placeholders'][] = $config['placeholder'];
            $replaceArgs['values'][] = $config['value'];

            $replaceArgs['placeholders'][] = strtolower($config['placeholder']);
            $replaceArgs['values'][] = strtolower($config['value']);
        }

        $filesToRename = [];

        /** @var RecursiveDirectoryIterator $it */
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($moduleFolder));

        $it->rewind();
        while($it->valid()) {

            Util::output(sprintf("File %s\n", $it->getSubPathName()));

            if (!$it->isDot() or $it->getFilename() == '.') {

                if (!$it->hasChildren()) {
                    $extension = pathinfo($it->key(), PATHINFO_EXTENSION);

                    if (in_array($extension, ['php', 'xml', 'json'])) {
                        $c = file_get_contents($it->key());

                        $c = str_replace(
                            $replaceArgs['placeholders'],
                            $replaceArgs['values'],
                            $c
                        );

                        while (preg_match('/__config\.(.+)__/Usim', $c, $match)) {
                            $val = $this->getArgument($match[1]);
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
                $replaceArgs['placeholders'],
                $replaceArgs['values'],
                $origname
            );
            rename($realPath, dirname($realPath) . DIRECTORY_SEPARATOR . $name);
        }
    }

    protected function copyFolderContents($source, $dest)
    {
        /** @var RecursiveDirectoryIterator $it */
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source));
        $it->rewind();
        while($it->valid()) {
            if (!$it->isDot()) {
                if (!$it->hasChildren()) {
                    Util::output(sprintf("File %s - ", $it->getSubPathName()));
                    $realPath = realpath($it->key());
                    $newPath = $dest . str_replace($source, '', $realPath);
                    if (realpath($newPath)) {
                        Util::output("EXISTS (skipping this file)\n");
                        unlink($realPath);
                    } else {
                        @mkdir(dirname($newPath), 0777, true);
                        rename($realPath, $newPath);
                        Util::output("OK\n");
                    }
                }
            }

            $it->next();
        }
    }

    protected function downloadAndExtract($repoUrl)
    {
        $repoLocalId = $this->getRepoLocalId($repoUrl);
        $downloadedFilePath = $this->downloadZip($repoUrl, $repoLocalId);
        $repoFolder = $this->getRepoTmpFolder($repoLocalId);
        $this->extractZip($downloadedFilePath, $repoFolder);
        return $repoFolder;
    }

    protected function getGithubRepoZipUrl($repoName)
    {
        $repoUrl = sprintf(
            'https://github.com/Eleanorsoft/%s/archive/master.zip',
            $repoName
        );
        return $repoUrl;
    }

    protected function getGithubRepoNameByClassParts($classParts)
    {
        if (count($classParts) == 2) {
            return 'm2-module';
        }
        if (@$classParts[2] == 'Setup') {
            return 'm2-setup';
        }
        if (@$classParts[2] == 'Controller') {
            return 'm2-' . implode('-', array_map('strtolower', array_slice($classParts, 2, -2)));
        }
        return 'm2-' . implode('-', array_map('strtolower', array_slice($classParts, 2, -1)));
    }

    protected function getRepoLocalId($repoId)
    {
        return md5($repoId . uniqid('', true));
    }

    protected function getRepoTmpFolder($repoLocalId)
    {
        return $this->moduleSourceTmpPath . $repoLocalId . '_f' . DIRECTORY_SEPARATOR;
    }

    protected function prepareFS()
    {
        @mkdir($this->moduleSourceTmpPath, 0777, true);
    }

    protected function downloadZip($sourceUrl, $fileUniqueName)
    {
        $archiveStr = file_get_contents($sourceUrl);
        if (!$archiveStr) {
            throw new \Exception("Can't download from URL $sourceUrl");
        }
        $filename = $this->moduleSourceTmpPath . $fileUniqueName;
        if (!file_put_contents($filename, $archiveStr)) {
            throw new \Exception("Can't write repo file $filename");
        }
        return $filename;
    }

    protected function extractZip($zipFile, $destFolder)
    {
        $zip = new \ZipArchive();
        $res = $zip->open($zipFile);
        if ($res === true) {
            @mkdir($destFolder, 0777, true);
            if (!$zip->extractTo($destFolder)) {
                throw new \Exception("Can't extract archive $zipFile to $destFolder");
            }
            $zip->close();
            @unlink($zipFile);
        } else {
            throw new \Exception("Can't open archive $zipFile");
        }
    }

    /**
     * Remove the directory and its content (all files and subdirectories).
     * @param string $dir the directory name
     */
    protected function rmrf($dir) {
        foreach (glob($dir) as $file) {
            if (is_dir($file)) {
                $this->rmrf("$file" . DIRECTORY_SEPARATOR . "*");
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }

}