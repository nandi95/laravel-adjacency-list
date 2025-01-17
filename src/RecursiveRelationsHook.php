<?php

namespace Staudenmeir\LaravelAdjacencyList;

use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Barryvdh\LaravelIdeHelper\Contracts\ModelHookInterface;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Collection;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

/**
 * @template RT { name: string, manyRelation: boolean, comment: string, relationClass: class-string|null }
 */
class RecursiveRelationsHook implements ModelHookInterface
{
    /**
     * @var array<array<RT>>
     */
    private static array $relationMap = [
        [
            'name' => 'ancestors',
            'manyRelation' => true,
            'comment' => 'The model\'s recursive parents.'
        ],
        [
            'name' => 'ancestorsAndSelf',
            'manyRelation' => true,
            'comment' => 'The model\'s recursive parents and itself.'
        ],
        [
            'name' => 'bloodline',
            'manyRelation' => true,
            'comment' => 'The model\'s ancestors, descendants and itself.'
        ],
        [
            'name' => 'children',
            'manyRelation' => true,
            'comment' => 'The model\'s direct children.'
        ],
        [
            'name' => 'childrenAndSelf',
            'manyRelation' => true,
            'comment' => 'The model\'s direct children and itself.'
        ],
        [
            'name' => 'descendants',
            'manyRelation' => true,
            'comment' => 'The model\'s recursive children.'
        ],
        [
            'name' => 'descendantsAndSelf',
            'manyRelation' => true,
            'comment' => 'The model\'s recursive children and itself.'
        ],
        [
            'name' => 'parent',
            'manyRelation' => false,
            'comment' => 'The model\'s direct parent.'
        ],
        [
            'name' => 'parentAndSelf',
            'manyRelation' => true,
            'comment' => 'The model\'s direct parent and itself.'
        ],
        [
            'name' => 'rootAncestor',
            'manyRelation' => false,
            'comment' => 'The model\'s topmost parent.'
        ],
        [
            'name' => 'siblings',
            'manyRelation' => true,
            'comment' => 'The parent\'s other children.'
        ],
        [
            'name' => 'siblingsAndSelf',
            'manyRelation' => true,
            'comment' => 'All the parent\'s children.'
        ]
    ];

    public function run(ModelsCommand $command, Model $model): void
    {
        $traits = collect(class_uses_recursive($model));

        if ($traits->doesntContain(HasRecursiveRelationships::class)) {
            return;
        }

        foreach (self::$relationMap as $relationDefinition) {
            $type = $relationDefinition['manyRelation']
                ? '\\' . Collection::class . '|' . class_basename($model) . '[]'
                : class_basename($model);

            $command->setProperty(
                $relationDefinition['name'],
                $type,
                true,
                false,
                $relationDefinition['comment'],
                !$relationDefinition['manyRelation']
            );

            if ($relationDefinition['manyRelation']) {
                $command->setProperty(
                    $relationDefinition['name'] . '_count',
                    'int',
                    true,
                    false,
                    null,
                    true
                );
            }
        }
    }
}
