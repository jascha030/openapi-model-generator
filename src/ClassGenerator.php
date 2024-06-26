<?php

declare(strict_types=1);

namespace Jascha030\OpenApiModelGenerator;

use EmptyIterator;
use Iterator;
use IteratorAggregate;
use Jascha030\OpenApiModelGenerator\Iterator\LazyIterator;
use SplFileInfo;
use Traversable;
use Jascha030\OpenApiModelGenerator\Helper\Arr;

use function file_get_contents;
use function json_decode;

/**
 * @implements IteratorAggregate<string,mixed>
 */
class ClassGenerator implements IteratorAggregate
{
    private const SCALAR = [
        'Mixed',
        'Int',
        'Number',
        'String',
        'Array',
    ];

    private const TYPE_MAP = [
        'integer' => 'int',
        'string'  => 'string',
        'array'   => 'array',
        'mixed'   => 'mixed',
    ];

    /**
     * @var array<string,mixed>
     */
    private array $parsedDoc;

    public function __construct(
        private readonly string $path,
        private readonly string $baseNamespace,
    ) {
        $file = new SplFileInfo($path);

        if (!$file->isFile()) {
            throw new \InvalidArgumentException('Could not read from filepath "' . $this->path . '".');
        }

        if ($file->getExtension() !== 'json') {
            throw new \InvalidArgumentException('Input file must be JSON.');
        }

        try {
            $decoded = json_decode(file_get_contents($file->getRealPath()), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Could not parse JSON file, Error: ' . $e->getMessage());
        }

        $this->parsedDoc = $decoded;
    }

    public function build(): Iterator|LazyIterator|EmptyIterator
    {
        //        if (!isset($this->parsedDoc['components']['schemas']) || !\is_array($this->parsedDoc['components']['schemas'])) {
        //            return new EmptyIterator();
        //        }

        $schemas = $this->parsedDoc['definitions'];

        return new LazyIterator(function () use ($schemas) {
            foreach ($schemas as $className => $schema) {
                yield $this->sanitizeClassName($className) => $this->createModelClass($className, $schema);
            }
        });
    }

    /**
     * @noinspection PhpSameParameterValueInspection
     */
    private function sanitizeClassName(string $fullyQualified, string $seperator = '.'): string
    {
        $parts = explode($seperator, $fullyQualified);

        $baseName = empty($parts) // @phpstan-ignore-line
            ? $fullyQualified
            : Arr::last($parts);

        return \in_array($baseName, self::SCALAR, true)
            ? strtolower($baseName)
            : $baseName;
    }

    private function createModelClass(string $className, mixed $schema): string
    {
        $class     = new \Nette\PhpGenerator\ClassType($className);
        $typeMap   = self::TYPE_MAP;

        foreach ($schema['properties'] as $name => $property) {
            $itemType = null;

            if (isset($property['type'])) {
                $type = $typeMap[$property['type']] ?? 'mixed';
            } elseif (isset($property['$ref'])) {
                $type = Arr::last(explode('/', $property['$ref']));

                if (isset($this->parsedDoc['components']['schemas'][$type]['enum'])) {
                    $type = 'int';
                }
            }

            if (!isset($type)) {
                continue;
            }

            if ('array' === $type) {
                if (isset($property['items']['type'])) {
                    $itemType = $typeMap[$property['items']['type']] ?? 'mixed';
                } elseif (isset($property['items']['$ref'])) {
                    $itemType = Arr::last(explode('/', $property['items']['$ref']));

                    if (isset($this->parsedDoc['components']['schemas'][$type]['enum'])) {
                        $itemType = 'int';
                    }
                }
            }

            $type = $this->sanitizeClassName($type);

            if (null !== $itemType) {
                $itemType = $this->sanitizeClassName($itemType);
            }

            $class
                ->addProperty($name)
                ->setType($type)
                ->setPrivate()
                ->setNullable($property['nullable'] ?? false)
                ->addComment($property['description'] ?? '')
                ->addComment(null !== $itemType ? "\n@var null|{$itemType}[]" : '');

            $class
                ->setExtends('Model');

            $class
                ->addMethod('get' . ucfirst($name))
                ->setBody(sprintf(
                    <<<'PHP'
            return $this->%s;
PHP,
                    $name,
                ))
                ->setPublic()
                ->setReturnType('?' . $type)
                ->addComment(null !== $itemType ? "@return null|{$itemType}[]" : '');

            $class
                ->addMethod('set' . ucfirst($name))
                ->setPublic()
                ->setBody(sprintf(
                    <<<'PHP'
        $this->%s = $%s;

        return $this;
PHP,
                    $name,
                    $name,
                ))
                ->addComment(null !== $itemType ? "@param null|{$itemType}[] \${$name}" : '')
                ->setReturnType('static')
                ->addParameter($name, null)
                ->setType('?' . $type);
        }

        return sprintf(
            <<<'PHP'
<?php

declare(strict_types=1);

namespace %s\Model;

%s

PHP,
            $this->baseNamespace,
            $class,
        );
    }

    public function getIterator(): Traversable
    {
        yield from $this->build();
    }
}
