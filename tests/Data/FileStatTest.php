<?php
declare(strict_types = 1);

class FileStatTest extends PHPUnit_Framework_TestCase
{
    public function testEquals_SameObject()
    {
        $stat1 = new \Echron\IO\Data\FileStat('test.txt');

        $this->assertTrue($stat1->equals($stat1));
    }

    public function testEquals_Same()
    {
        $stat1 = new \Echron\IO\Data\FileStat('test.txt');
        $stat1->setChangeDate(time());
        $stat1->setBytes(10);

        $stat2 = new \Echron\IO\Data\FileStat('test2.txt');
        $stat2->setChangeDate($stat1->getChangeDate());
        $stat2->setBytes(10);

        $this->assertTrue($stat1->equals($stat2));
        $this->assertTrue($stat2->equals($stat1));
    }

    public function testEquals_Different_Size()
    {
        $stat1 = new \Echron\IO\Data\FileStat('test.txt');
        $stat1->setChangeDate(time());
        $stat1->setBytes(10);

        $stat2 = new \Echron\IO\Data\FileStat('test2.txt');
        $stat2->setChangeDate($stat1->getChangeDate());
        $stat2->setBytes(20);

        $this->assertFalse($stat1->equals($stat2));
        $this->assertFalse($stat2->equals($stat1));

    }

    public function testEquals_Different_ChangeDate()
    {
        $stat1 = new \Echron\IO\Data\FileStat('test.txt');
        $stat1->setChangeDate(time());
        $stat1->setBytes(10);

        $stat2 = new \Echron\IO\Data\FileStat('test2.txt');
        $stat2->setChangeDate($stat1->getChangeDate() - 10);
        $stat2->setBytes($stat1->getBytes());

        $this->assertFalse($stat1->equals($stat2));
        $this->assertFalse($stat2->equals($stat1));
    }

    public function testEquals_Different_Type()
    {
        $stat1 = new \Echron\IO\Data\FileStat('test.txt');
        $stat1->setType(\Echron\IO\Data\FileType::File());
        $stat1->setChangeDate(time());
        $stat1->setBytes(10);

        $stat2 = new \Echron\IO\Data\FileStat('test2.txt');
        $stat2->setType(\Echron\IO\Data\FileType::Dir());
        $stat2->setChangeDate($stat1->getChangeDate());
        $stat2->setBytes($stat1->getBytes());

        $this->assertFalse($stat1->equals($stat2));
        $this->assertFalse($stat2->equals($stat1));
    }

    public function testEquals_Different_ChangeDateAndSizeAndType()
    {
        $stat1 = new \Echron\IO\Data\FileStat('test.txt');
        $stat1->setType(\Echron\IO\Data\FileType::File());
        $stat1->setChangeDate(time());
        $stat1->setBytes(10);

        $stat2 = new \Echron\IO\Data\FileStat('test2.txt');
        $stat2->setType(\Echron\IO\Data\FileType::Dir());
        $stat2->setChangeDate($stat1->getChangeDate() - 10);
        $stat2->setBytes($stat1->getBytes() - 10);

        $this->assertFalse($stat1->equals($stat2));
        $this->assertFalse($stat2->equals($stat1));
    }

}
