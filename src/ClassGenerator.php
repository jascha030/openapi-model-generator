<?php

namespace Jascha030\OpenApiModelGenerator;

use AppendIterator;
use EmptyIterator;
use Iterator;
use IteratorAggregate;
use IteratorIterator;
use Jascha030\OpenApiModelGenerator\Iterator\LazyIterator;
use SplFileInfo;
use Traversable;

use function array_key_last;
use function file_get_contents;
use function Jascha\OpenApiModelGenerator\last;
use function json_decode;

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

    public function build(): Iterator
    {
        if (!isset($this->parsedDoc['components']['schemas']) || !\is_array($this->parsedDoc['components']['schemas'])) {
            return new EmptyIterator();
        }

        $schemas = $this->parsedDoc['components']['schemas'];

        $iterator = new AppendIterator();

        foreach ($schemas as $className => $schema) {
            $iterator->append(new IteratorIterator(new LazyIterator(function () use ($className, $schema): \Generator {
                yield $this->sanitizeClassName($className) => $this->createModelClass($className, $schema);
            })));
        }

        return $iterator;
    }

    /**
     * @noinspection PhpSameParameterValueInspection
     */
    private function sanitizeClassName(string $fullyQualified, string $seperator = '.'): string
    {
        $parts = explode($seperator, $fullyQualified);

        $baseName = empty($parts) // @phpstan-ignore-line
            ? $fullyQualified
            : last($parts);

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
                $type = last(explode('/', $property['$ref']));

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
                    $itemType = last(explode('/', $property['items']['$ref']));

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
        return $this->build();
    }
}