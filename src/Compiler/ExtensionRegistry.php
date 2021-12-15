<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExtensionRegistry
{
    /**
     * @var Extension[]
     */
    protected array $extensions = [];

    /**
     * @param EventDispatcherInterface $dispatcher
     * @param string[] $extensionDirs
     */
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        array $extensionDirs = []
    ) {
        $extensionDirs = array_merge([__DIR__ . '/Extension'], $extensionDirs);
        $this->addDirectory(...$extensionDirs);
    }

    /**
     * @return $this
     */
    public function addDirectory(string ...$dirs): static
    {
        foreach ($dirs as $dir) {
            $this->discoverExtensions($dir);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function registerExtension(Extension $ext): static
    {
        $ext->setEventDispatcher($this->dispatcher);
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
