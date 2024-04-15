<?php namespace Nimdoc\NimblockEditor\Classes;

use Winter\Storm\Exception\SystemException;
use Winter\Storm\Halcyon\Datasource\Datasource;

use Nimdoc\NimblockEditor\Classes\Block;

class BlocksDatasource extends Datasource
{
    /**
     * @var array [key => path] List of blocks managed by the BlockManager
     */
    protected array $blocks;

    public function __construct()
    {
        // $this->processor = new BlockProcessor();
        // $this->blocks = BlockManager::instance()->getRegisteredBlocks();
    }

    /**
     * @inheritDoc
     */
    public function selectOne(string $dirName, string $fileName, string $extension): ?array
    {
        $blockViews = Block::getEditorBlockViews();

        $blockViewNames = array_map(function ($blockView) {
            return pathinfo($blockView, PATHINFO_FILENAME);
        }, $blockViews);

        $partialNames = array_map(function ($blockView) {
            $exploded = explode('.', explode('::', $blockView)[1]);
            return implode('/', $exploded);
        }, $blockViews);

        $arrayKey = false;

        foreach($partialNames as $key => $partialName) {
            if($partialName == $fileName) {
                $arrayKey = $key;
                break;
            }
        }

        if($arrayKey === false) {
            return null;
        }

        $partialNames = array_map(function ($blockView) {
            $exploded = explode('::', $blockView);

            $pluginParts = explode('.', $exploded[0]);

            $partialPathParts = explode('.', $exploded[1]);

            $partialName = array_pop($partialPathParts);
            $path = implode('/', $partialPathParts);

            $partialArray = [
                'vendor' => $pluginParts[0],
                'plugin' => $pluginParts[1],
                'path' => $path,
                'name' => $partialName,
            ];

            return $partialArray;
        }, $blockViews);

        $partialPath = plugins_path($partialNames[$arrayKey]['vendor'] . '/' . $partialNames[$arrayKey]['plugin'] . '/' . $partialNames[$arrayKey]['path'] . '/' . $partialNames[$arrayKey]['name'] . '.htm');

        return [
            'fileName' => $fileName . '.' . $extension,
            'content'  => file_get_contents($partialPath),
            'mtime'    => 1000,
        ];
    }

    /**
     * @inheritDoc
     */
    public function select(string $dirName, array $options = []): array
    {
        // Prepare query options
        $queryOptions = array_merge([
            'columns'     => null,  // Only return specific columns (fileName, mtime, content)
            'extensions'  => null,  // Match specified extensions
            'fileMatch'   => null,  // Match the file name using fnmatch()
            'orders'      => null,  // @todo
            'limit'       => null,  // @todo
            'offset'      => null   // @todo
        ], $options);
        extract($queryOptions);

        if (isset($columns)) {
            if ($columns === ['*'] || !is_array($columns)) {
                $columns = null;
            } else {
                $columns = array_flip($columns);
            }
        }

        if ($dirName !== 'blocks' || (isset($extensions) && !in_array('block', $extensions))) {
            return [];
        }

        $result = [];
        foreach ($this->blocks as $fileName => $path) {
            $item = [
                'fileName' => $fileName . '.block',
            ];

            if (!isset($columns) || array_key_exists('content', $columns)) {
                $item['content'] = file_get_contents($path);
            }

            if (!isset($columns) || array_key_exists('mtime', $columns)) {
                $item['mtime'] = filemtime($path);
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function insert(string $dirName, string $fileName, string $extension, string $content): int
    {
        throw new SystemException('insert() is not implemented on the BlocksDatasource');
    }

    /**
     * @inheritDoc
     */
    public function update(string $dirName, string $fileName, string $extension, string $content, ?string $oldFileName = null, ?string $oldExtension = null): int
    {
        throw new SystemException('update() is not implemented on the BlocksDatasource');
    }

    /**
     * @inheritDoc
     */
    public function delete(string $dirName, string $fileName, string $extension): bool
    {
        throw new SystemException('delete() is not implemented on the BlocksDatasource');
    }

    /**
     * @inheritDoc
     */
    public function lastModified(string $dirName, string $fileName, string $extension): ?int
    {
        return $this->selectOne($dirName, $fileName, $extension)['mtime'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function makeCacheKey(string $name = ''): string
    {
        return hash('crc32b', $name);
    }

    /**
     * @inheritDoc
     */
    public function getPathsCacheKey(): string
    {
        return 'halcyon-datastore-nimblock-blocks-' . md5(json_encode($this->getAvailablePaths()));
    }

    /**
     * @inheritDoc
     */
    public function getAvailablePaths(): array
    {
        $blockViews = Block::getEditorBlockViews();

        $partialPaths = array_map(function ($blockView) {
            $exploded = explode('.', explode('::', $blockView)[1]);
            return implode('/', $exploded) . '.htm';
        }, $blockViews);

        return $partialPaths;
    }
}
