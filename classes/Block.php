<?php namespace Nimdoc\NimblockEditor\Classes;

use Event;
use EditorJS\EditorJS;
use Cms\Classes\Controller;
use Cms\Classes\CmsCompoundObject;
use Cms\Classes\PartialStack;

class Block extends CmsCompoundObject
{
    protected $dirName = 'blocks';

    protected PartialStack $partialStack;

    public function __construct(array $attributes = [])
    {
        $this->partialStack = new PartialStack();
        parent::__construct($attributes);
    }

    /**
     * Returns name of a PHP class to us a parent for the PHP class created for the object's PHP section.
     */
    public function getCodeClassParent(): string
    {
        return BlockCode::class;
    }

    public static function renderJson(string $json, $controller=null): string
    {
        $controller = $controller ?? new Controller();

        $blocks = self::getBlocks($json);

        $html = array_map(
            function ($block) use ($controller) {
                $blockType = strtolower($block['type']);
                $blockPath = 'blocks/' . $blockType . '.htm';
                return $controller->renderPartial($blockPath); //, $partialData);
            },
            $blocks
        );

        return html_entity_decode(implode("\n", $html));
    }

    public static function getBlocks($jsonField)
    {
        $editorConfig = self::getEditorBlockConfig();
        $validationSettings = [];
        $validationSettings['tools'] = 
            array_map(function ($block) {
                return array_get($block, 'validation', []);
            }, array_filter($editorConfig, function ($block) {
                return array_key_exists('validation', $block);
            }));
    
        $blocksViews = array_map(function ($block) {
                return array_get($block, 'view');
            }, $editorConfig);

        try {
            $editor = new EditorJS($jsonField, json_encode($validationSettings));
            $blocks = $editor->getBlocks();
        } catch (EditorJSException $e) {
            return $e->getMessage();
        }

        return $blocks;
    }

    /**
     * Renders the provided block
     */
    public static function render(string|array $block, array $data = [], ?Controller $controller = null): string
    {
        if (!$controller) {
            $controller = new Controller();
        }

        if (empty($block)) {
            throw new SystemException("The block name was not provided");
        }

        return $controller->renderPartial($block, $data);
    }

    public static function getEditorBlockViews()
    {
        $blocksWithView = array_filter(self::getEditorBlockConfig(), function ($block) {
            return isset($block['view']);
        });
        $blockViews = array_map(function ($block) {
            return $block['view'];
        }, $blocksWithView);

        return $blockViews;
    }

    public static function getEditorBlockConfig()
    {
        $config = [];
        Event::fire('nimdoc.nimblockeditor.editor.config', [&$config]);
        return $config;
    }

    /**
     * Get a new query builder for the object
     * @return \Winter\Storm\Halcyon\Builder
     */
    public function newQuery()
    {
        $datasource = $this->getDatasource();

        $query = new BlockBuilder($datasource, new BlockProcessor());

        return $query->setModel($this);
    }

    /**
     * Execute the lifecycle of the partial manually. Usually this would only happen for cms partials (i.e. component
     * partials), but this method enables this functionality for blocks
     */
    public function executeLifecycle(Controller $controller): static
    {
        $this->partialStack->stackPartial();

        $manager = ComponentManager::instance();

        foreach ($this->components as $component => $properties) {
            // Do not inject the viewBag component to the environment.
            // Not sure if they're needed there by the requirements,
            // but there were problems with array-typed properties used by Static Pages
            // snippets and setComponentPropertiesFromParams(). --ab
            if ($component == 'viewBag') {
                continue;
            }

            list($name, $alias) = strpos($component, ' ')
                ? explode(' ', $component)
                : [$component, $component];

            if (!$componentObj = $manager->makeComponent($name, $this, $properties)) {
                throw new SystemException(Lang::get('cms::lang.component.not_found', ['name'=>$name]));
            }

            $componentObj->alias = $alias;
            $parameters[$alias] = $this->components[$alias] = $componentObj;

            $this->partialStack->addComponent($alias, $componentObj);

            $this->setComponentPropertiesFromParams($componentObj, $parameters);
            $componentObj->init();
        }

        CmsException::mask($this->page, 300);
        $parser = new CodeParser($this);
        $partialObj = $parser->source($controller->getPage(), $controller->getLayout(), $controller);
        CmsException::unmask();

        CmsException::mask($this, 300);
        $partialObj->onStart();
        $this->runComponents();
        $partialObj->onEnd();
        CmsException::unmask();

        return $this;
    }
}