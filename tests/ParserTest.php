<?php

declare(strict_types=1);

namespace Jascha030\OpenApiModelGenerator;

use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    use ContainerAwareTestTrait;

    public function testGetParsedDocBeforeParse(): void
    {
        $parser = $this->getTestParser();

        $this->expectException(\RuntimeException::class);
        $parser->getParsedDoc();
    }

    private function getTestParser(): Parser
    {
        return $this->getContainer()->get(Parser::class);
    }
}
