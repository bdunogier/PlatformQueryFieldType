<?php
namespace BD\EzPlatformQueryFieldType\Symfony\DependencyInjection\Compiler;

use BD\EzPlatformQueryFieldType\DataProvider\QueryTypeDataProvider;
use BD\EzPlatformQueryFieldType\FieldType\Mapper\QueryFormMapper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class QueryTypesListPass implements CompilerPassInterface
{
    /**
     * @var \Symfony\Component\Serializer\NameConverter\NameConverterInterface
     */
    private $nameConverter;

    public function __construct()
    {
        $this->nameConverter = new CamelCaseToSnakeCaseNameConverter();
    }

    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ezpublish.query_type.registry') || !$container->has(QueryTypeDataProvider::class)) {
            return;
        }

        $queryTypes = [];
        foreach ($container->getDefinition('ezpublish.query_type.registry')->getMethodCalls() as $methodCall) {
            if ($methodCall[0] === 'addQueryType') {
                $queryTypes[] = $methodCall[1][0];
            } else if ($methodCall[0] === 'addQueryTypes') {
                foreach (array_keys($methodCall[1][0]) as $queryTypeIdentifier) {
                    $queryTypes[$this->buildQueryTypeName($queryTypeIdentifier)] = $queryTypeIdentifier;
                }
            }
        }

        $formMapperDefinition = $container->getDefinition(QueryTypeDataProvider::class);
        $formMapperDefinition->setArgument(3, $queryTypes);
    }

    /**
     * Builds a human readable name out of a query type identifier
     *
     * @param $queryTypeIdentifier
     * @return string
     */
    private function buildQueryTypeName($queryTypeIdentifier)
    {
        return ucfirst(
            str_replace('_', ' ', $this->nameConverter->normalize($queryTypeIdentifier))
        );
    }
}