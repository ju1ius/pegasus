<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler;

class ExtensionRegistry
{
    /**
     * @var Extension[]
     */
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
    public function addDirectory(string $dir)
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

    public function getExtension(string $name): ?Extension
    {
        return $this->extensions[$name] ?? null;
    }

    protected function discoverExtensions(string $extensionsDir): void
    {
        foreach (new \FilesystemIterator($extensionsDir) as $path => $finfo) {
            if ($finfo->isDir() && file_exists($path . '/bootstrap.php')) {
                $ext = include_once($path . '/bootstrap.php');
                $this->registerExtension($ext);
            }
        }
    }

}
