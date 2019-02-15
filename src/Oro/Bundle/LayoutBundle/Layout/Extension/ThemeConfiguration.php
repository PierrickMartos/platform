<?php

namespace Oro\Bundle\LayoutBundle\Layout\Extension;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Provides schema for configuration that is loaded from the following files:
 * * Resources/views/layouts/{folder}/theme.yml
 * * Resources/views/layouts/{folder}/config/assets.yml
 * * Resources/views/layouts/{folder}/config/images.yml
 * * Resources/views/layouts/{folder}/config/page_templates.yml
 */
class ThemeConfiguration implements ConfigurationInterface
{
    public const ROOT_NODE = 'themes';

    public const AUTO = 'auto';

    /** @var ThemeConfigurationExtensionInterface[] */
    private $extensions = [];

    /**
     * @param ThemeConfigurationExtensionInterface $extension
     */
    public function addExtension(ThemeConfigurationExtensionInterface $extension)
    {
        $this->extensions[] = $extension;
    }

    /**
     * @return string[]
     */
    public function getAdditionalConfigFileNames(): array
    {
        $fileNames = [[
            'assets.yml',
            'images.yml',
            'page_templates.yml'
        ]];
        foreach ($this->extensions as $extension) {
            $extensionFileNames = $extension->getConfigFileNames();
            if ($extensionFileNames) {
                $fileNames[] = $extensionFileNames;
            }
        }
        return array_merge(...$fileNames);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::ROOT_NODE);

        $configTreeBuilder = new TreeBuilder();
        $configNode = $configTreeBuilder->root('config');
        $configNode->info('Layout theme additional config')->end();
        // Allow extra configuration keys to be present in this configuration node.
        // This is needed to give other bundles ability to declare and add custom configuration.
        $configNode->ignoreExtraKeys(false);

        $rootNode
            ->useAttributeAsKey('name')
            ->normalizeKeys(false)
            ->prototype('array')
                ->children()
                    ->scalarNode('label')
                        ->info('The label is displayed in the theme management UI. Can be empty for "hidden" themes')
                        ->isRequired()
                    ->end()
                    ->scalarNode('description')
                        ->info('The description is displayed in the theme selection UI. Can be empty')
                    ->end()
                    ->scalarNode('icon')
                        ->info('The icon is displayed in the UI')
                    ->end()
                    ->scalarNode('logo')
                        ->info('The logo image is displayed in the UI')
                    ->end()
                    ->scalarNode('screenshot')
                        ->info('The screenshot image is used in theme management UI for the theme preview')
                    ->end()
                    ->scalarNode('directory')
                        ->info('The directory name where to look up for layout updates. By default theme identifier')
                    ->end()
                    ->scalarNode('parent')
                        ->info('The identifier of the parent theme')
                    ->end()
                    ->arrayNode('groups')
                        ->info('Layout groups for which the theme is applicable')
                        ->example('[main, embedded_forms, frontend]')
                        ->prototype('scalar')->end()
                        ->cannotBeEmpty()
                    ->end()
                    ->append($configNode)
                ->end()
            ->end();

        $this->appendConfigNodes($configNode->children());

        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $configNode
     */
    private function appendConfigNodes(NodeBuilder $configNode)
    {
        $this->appendAssets($configNode);
        $this->appendImages($configNode);
        $this->appendPageTemplates($configNode);
        foreach ($this->extensions as $extension) {
            $extension->appendConfig($configNode);
        }
    }

    /**
     * @param NodeBuilder $configNode
     */
    private function appendAssets(NodeBuilder $configNode)
    {
        $configNode->arrayNode('assets')
            ->useAttributeAsKey('name')
            ->normalizeKeys(false)
            ->prototype('array')
                ->children()
                    ->arrayNode('inputs')
                        ->info('Input assets list')
                        ->prototype('variable')->end()
                    ->end()
                    ->arrayNode('filters')
                        ->info('Filters to manipulate input assets')
                        ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('output')
                        ->info('Output asset')
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param NodeBuilder $configNode
     */
    private function appendImages(NodeBuilder $configNode)
    {
        $widthHeightValidator = function ($value) {
            return null !== $value && !is_int($value) && self::AUTO !== $value;
        };

        $configNode->arrayNode('images')
            ->children()
                ->arrayNode('types')
                ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('label')->cannotBeEmpty()->end()
                            ->scalarNode('max_number')->defaultNull()->end()
                            ->arrayNode('dimensions')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('dimensions')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->validate()
                            ->ifTrue(function (array $dimension) {
                                return self::AUTO === $dimension['width'] && self::AUTO === $dimension['height'];
                            })
                            ->thenInvalid('Either width or height can be set to \'auto\', not both.')
                        ->end()
                        ->children()
                            ->scalarNode('width')
                                ->validate()
                                    ->ifTrue($widthHeightValidator)
                                    ->thenInvalid('Width value can be null, \'auto\' or integer only')
                                ->end()
                                ->isRequired()
                            ->end()
                            ->scalarNode('height')
                                ->validate()
                                    ->ifTrue($widthHeightValidator)
                                    ->thenInvalid('Height value can be null, \'auto\' or integer only')
                                ->end()
                                ->isRequired()
                            ->end()
                            ->arrayNode('options')
                                ->prototype('variable')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param NodeBuilder $configNode
     */
    private function appendPageTemplates(NodeBuilder $configNode)
    {
        $configNode->arrayNode('page_templates')
            ->children()
                ->arrayNode('templates')
                    ->info('List of page templates')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('route_name')->cannotBeEmpty()->end()
                            ->scalarNode('key')->cannotBeEmpty()->end()
                            ->scalarNode('label')->cannotBeEmpty()->end()
                            ->scalarNode('description')->defaultNull()->end()
                            ->scalarNode('screenshot')->defaultNull()->end()
                            ->booleanNode('enabled')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('titles')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
            ->end();
    }
}
