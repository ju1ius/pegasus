<?php

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Extension;


class ExtensionManager
{

    protected $extensions = [];

    /**
     * @param array $extension_dirs
     */
    public function __construct($extension_dirs=[])
    {
        $extension_dirs = array_merge([__DIR__.'/Extension'], $extension_dirs);
        foreach ($extension_dirs as $dir) {
            $this->discoverExtensions($dir);
        }
    }

    public function addDirectory($dir)
    {
        $this->discoverExtensions($dir);
    }

    public function registerExtension(Extension $ext)
    {
        $name = $ext->getName();
        $this->extensions[$name] = $ext;
    }

    public function getExtension($name)
    {
        return isset($this->extensions[$name])
            ? $this->extensions[$name]
            : null
        ;
    }
    
    protected function discoverExtensions($dir)
    {
        foreach (new \FilesystemIterator($dir) as $path => $finfo) {
            if ($finfo->isDir() && file_exists($path . '/bootstrap.php')) {
                $ext = include_once($path . '/bootstrap.php');
                $this->registerExtension($ext);
            }
        }
    }
    
}
