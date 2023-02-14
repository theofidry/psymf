<?php

/*
 * This file is part of the PsyshBundle package.
 *
 * (c) Jérôme Gangneux <jerome@gangneux.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\PsyshBundle;

use PHPUnit\Framework\TestCase;

class InteractiveTest extends TestCase
{
    /**
     * @return array<array{array<string>, ?string, ?array<string>, bool}>
     */
    public static function findDataset(): array
    {
        return [
            [
                [
                    '\\App\\NameSpace\\BlogPost',
                    '\\App\\OtherName\\UserComment',
                ], 'User', [
                    '\\App\\OtherName\\UserComment',
                ], true,
            ], [
                [
                    '\\App\\NameSpace\\ColorRed',
                    '\\App\\OtherName\\ColorBlue',
                ], 'Color', [
                    '\\App\\NameSpace\\ColorRed',
                    '\\App\\OtherName\\ColorBlue',
                ], true,
            ], [
                [
                    '\\App\\NameSpace\\ColorRed',
                    '\\App\\OtherName\\ColorBlue',
                ], 'color', [
                    '\\App\\NameSpace\\ColorRed',
                    '\\App\\OtherName\\ColorBlue',
                ], true,
            ], [
                [
                    '\\App\\NameSpace\\Color',
                    '\\App\\OtherName\\Color',
                ], 'BlogPost', null, true,
            ], [
                [
                    'Exception',
                ], 'Exception', [
                    'Exception',
                ], true,
            ], [
                [
                    '\\Example\\ClassA',
                    '\\Example\\ClassB',
                    '\\Example\\DataObject',
                ], 'Class', [
                    '\\Example\\ClassA',
                    '\\Example\\ClassB',
                ], true,
            ], [
                [
                    '\\Example\\ClassA',
                    '\\Example\\ClassB',
                    '\\Example\\DataObject',
                    '\\Namespace\\BlogPost',
                    '\\Namespace\\Example\\Comment',
                    '\\Namespace\\Query\\ExampleQuery',
                ], 'example', [
                    '\\Example\\ClassA',
                    '\\Example\\ClassB',
                    '\\Example\\DataObject',
                    '\\Namespace\\Example\\Comment',
                    '\\Namespace\\Query\\ExampleQuery',
                ], true,
            ], [
                [
                    '\\Example\\ClassA',
                    '\\Example\\ClassB',
                    '\\Example\\DataObject',
                    '\\Namespace\\BlogPost',
                    '\\Namespace\\Example\\Comment',
                    '\\Namespace\\Query\\Example',
                ], 'Example', [ // Better match kicks in!
                    '\\Namespace\\Query\\Example',
                ], true,
            ], [
                [
                    'example.classA',
                    'example.classB',
                    'example.dataObject',
                    'namespace.blogPost',
                    'namespace.example.comment',
                    'namespace.query.exampleQuery',
                ], 'blog', [
                    'namespace.blogPost',
                ], true,
            ], [
                [
                    'example.classA',
                    'example.classB',
                    'example.dataObject',
                    'namespace.blogPost',
                    'namespace.example.comment',
                    'namespace.query.example',
                ], 'example', [ // Better match does not kick in
                    'example.classA',
                    'example.classB',
                    'example.dataObject',
                    'namespace.example.comment',
                    'namespace.query.example',
                ], true,
            ],
        ];
    }

    /**
     * @dataProvider findDataset
     *
     * @param array<string>  $candidates
     * @param ?array<string> $expected
     */
    public function testFind(array $candidates, ?string $identifier, ?array $expected, bool $matchFunction): void
    {
        $find = Interactive::find(
            $candidates,
            $identifier,
            function (string $identifier) use ($matchFunction) { return $matchFunction; }
        );
        if (null === $find) {
            $this->assertSame($expected, $find, 'Null was not expected');
        } else {
            /** @var array<string> $expected */
            $diff = array_diff($expected, $find);
            $error = 'Expected: ' . json_encode($expected) . \PHP_EOL . 'Got: ' . json_encode(array_values($find));
            $this->assertCount(0, $diff, $error);
        }
    }
}
