<?php

namespace ju1ius\Pegasus\Compiler;

class ExtensionRegistry
{

    protected $extensions = [];

    /**
     * @param string[] $extensionDirs
     */
    public function __construct(array $extensionDirs = [])
    {
        $extensionDirs = array_merge([__DIR__ . '/Extension'], $extensionDirs);
        foreach ($extensionDirs as $dir) {
            $this->discoverExtensions($dir);
        }
    }

    /**
     * @param string $dir
     *
     * @return $this
     */
    public function addDirectory($dir)
    {
        $this->discoverExtensions($dir);

        return $this;
    }

    /**
     * @param Extension $ext
     *
     * @return $this
     */
    public function registerExtension(Extension $ext)
    {
        $name = $ext->getName();
        $this->extensions[$name] = $ext;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return Extension|null
     */
    public function getExtension($name)
    {
        return isset($this->extensions[$name]) ? $this->extensions[$name] : null;
    }

    /**
     * @param string $extensionsDir
     */
    protected function discoverExtensions($extensionsDir)
    {
        foreach (new \FilesystemIterator($extensionsDir) as $path => $finfo) {
            if ($finfo->isDir() && file_exists($path . '/bootstrap.php')) {
                $ext = include_once($path . '/bootstrap.php');
                $this->registerExtension($ext);
            }
        }
    }

}
