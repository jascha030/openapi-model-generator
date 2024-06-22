<?php

declare(strict_types=1);

namespace Jascha030\OpenApiModelGenerator;

use Jascha030\OpenApiModelGenerator\Swagger\SwaggerDocument;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

use function file_get_contents;

class Parser
{
    private ?SwaggerDocument $parsedDoc;

    public function __construct(private readonly Serializer $serializer) {}

    /**
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function parseFile(string $path): SwaggerDocument
    {
        $file = new SplFileInfo($path);

        if (!$file->isFile()) {
            throw new \InvalidArgumentException('Could not read from filepath "' . $path . '".');
        }

        if ($file->getExtension() !== 'json') {
            throw new \InvalidArgumentException('Input file must be JSON.');
        }

        $contents = file_get_contents($path);

        $this->parsedDoc = $this->serializer->deserialize($contents, SwaggerDocument::class, JsonEncoder::FORMAT);

        return $this->getParsedDoc();
    }

    /**
     * @throws RuntimeException
     */
    public function getParsedDoc(): SwaggerDocument
    {
        return $this->parsedDoc ?? throw new RuntimeException('No document has been parsed yet.');
    }
}
