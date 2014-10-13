<?php

namespace tests;

use OfficeML\Processor\Lexer;
use OfficeML\Processor\TagMapper;

class TagMapperTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_parse_path_correctly()
    {
        $method = new \ReflectionMethod(
            'PHPStamp\Processor\TagMapper', 'parsePath'
        );
        $method->setAccessible(true);

        $lexer = new Lexer(array('[[', ']]'));

        // case 0
        $pathString = '';
        $lexer->setInput($pathString);
        $this->assertEquals(
            array(), $method->invoke(new TagMapper, $lexer)
        );

        // case 1
        $pathString = 'root';
        $lexer->setInput($pathString);
        $this->assertEquals(
            array('root'), $method->invoke(new TagMapper, $lexer)
        );

        // case 2
        $pathString = 'root.child';
        $lexer->setInput($pathString);
        $this->assertEquals(
            array('root', 'child'), $method->invoke(new TagMapper, $lexer)
        );

        // case 3
        $pathString = 'root-child';
        $lexer->setInput($pathString);
        $this->setExpectedException('PHPStamp\Exception\ProcessorException');
        $result = $method->invoke(new TagMapper, $lexer);
    }

    /** @test */
    public function it_works()
    {
        // prepare
        $lexer = new Lexer(array('[[', ']]'));
        $pathString = '[[root.child:function(arg1, arg2):function2(arg3, arg4, arg5)]]';
        $lexer->setInput($pathString);

        $mapper = new TagMapper();
        while ($tag = $mapper->parse($lexer)) {
            var_dump($tag);
        }
    }
}
 