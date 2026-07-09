<?php
/**
 ▄▄▄
  █ J █ u z d y
   ▀▀▀
 */
namespace Juzdy\Config\Loader;

use DirectoryIterator;
use Juzdy\Config\ConfigInterface;
use Juzdy\Config\Exception\InvalidConfigPathException;

final class ConfigLoader implements ConfigLoaderInterface
{
    public function __construct(
        private readonly PhpFileReader $phpReader = new PhpFileReader(),
        private readonly bool $recursive = false,
        private readonly array $extensions = ['php'],
    ) {
    }

    public function load(ConfigInterface $config, string $path, string $strategy = 'merge'): void
    {
        if (!file_exists($path)) {
            throw new InvalidConfigPathException(
                sprintf('Config path "%s" does not exist.', $path)
            );
        }

        if (is_file($path)) {
            $this->loadFile($config, $path, $strategy);
            return;
        }

        if (!is_dir($path)) {
            throw new InvalidConfigPathException(
                sprintf('Config path "%s" is neither file nor directory.', $path)
            );
        }

        $this->loadDirectory($config, $path, $strategy);
    }

    private function loadDirectory(ConfigInterface $config, string $directory, string $strategy): void
    {
        $files = [];

        foreach (new DirectoryIterator($directory) as $item) {
            if ($item->isDot()) {
                continue;
            }

            if ($item->isDir()) {
                if ($this->recursive) {
                    $this->loadDirectory($config, $item->getPathname(), $strategy);
                }
                continue;
            }

            if (!$item->isFile()) {
                continue;
            }

            $extension = strtolower($item->getExtension());

            if (!in_array($extension, $this->extensions, true)) {
                continue;
            }

            $files[] = $item->getPathname();
        }

        sort($files, SORT_STRING);

        foreach ($files as $file) {
            $this->loadFile($config, $file, $strategy);
        }
    }

    private function loadFile(ConfigInterface $config, string $file, string $strategy): void
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if ($extension !== 'php') {
            return;
        }

        $data = $this->phpReader->read($file, $config);
        $config->apply($data, $strategy);
    }
}