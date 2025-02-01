<?php

namespace App\Tests\Entity;

use App\Entity\Type;
use App\Entity\Event;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class TypeTest extends TestCase
{
    public function testTypeProperties()
    {
        $type = new Type();
        $type->setName('hello');
        $this->assertEquals('hello', $type->getName());
        $this->assertEquals('hello', (string) $type);
    }

    public function testAddEvent()
    {
        $type = new Type();
        $event = new Event();
        $type->addEvent($event);

        $this->assertCount(1, $type->getEvents());
        $this->assertTrue($type->getEvents()->contains($event));
        $this->assertSame($type, $event->getType());
    }

    public function testRemoveEvent()
    {
        $type = new Type();
        $event = new Event();

        $type->addEvent($event);
        $type->removeEvent($event);

        $this->assertCount(0, $type->getEvents());
        $this->assertFalse($type->getEvents()->contains($event));
        $this->assertNull($event->getType());
    }
}
