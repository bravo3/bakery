<?php
namespace Bravo3\Bakery\Tests;

use Bravo3\Bakery\Bakery;
use Bravo3\Bakery\Entity\Host;
use Bravo3\Bakery\Entity\Schema;
use Bravo3\Bakery\Enum\PackagerType;
use Bravo3\Bakery\Enum\Phase;
use Bravo3\Bakery\Operation\ScriptOperation;
use Bravo3\Bakery\Tests\Resources\FileLogger;
use Bravo3\SSH\Credentials\PasswordCredential;

class BakeryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @large
     */
    public function testStuff()
    {
        // TODO: experimenting here, the ssh2 extension has a strange bug in the tunnel authentication
        //       needs to be resolved in the bravo3/ssh library

        //$host   = new Host('localhost', 22, new KeyCredential('jordon', null, '/home/jordon/.ssh/jordon.pem'));
        //$tunnel   = new Host('localhost', 22, new KeyCredential('jordon', null, '/home/jordon/.ssh/jordon.pem'));
        $host   = new Host('127.0.0.1', 22, new PasswordCredential('jordon', 'xxx'));
        $tunnel = new Host('127.0.0.1', 22, new PasswordCredential('jordon', 'xxx'));

        $logger_bake     = new FileLogger("/tmp/bakery_bake.log");
        $logger_callback = new FileLogger("/tmp/bakery_callback.log", false);
        $logger_out      = new FileLogger("/tmp/bakery_out.log", false);

        $logger_bake->debug("\n\n\n---");
        $logger_callback->debug("\n\n\n---");
        $logger_out->debug("\n\n\n---");

        $callback = function (Phase $phase, $progress, $total, $msg) use ($logger_callback) {
            $logger_callback->info($phase->value().' '.$progress.' / '.$total.': '.$msg);
        };

        $bakery = new Bakery($host, $logger_out, $callback);
        $bakery->setLogger($logger_bake);
        $bakery->addTunnel($tunnel);

        $schema = new Schema(PackagerType::APT());
        $schema->addOperation(new ScriptOperation("echo `whoami` > /tmp/bakery.out\necho 'hello world'"));

        $this->assertTrue($bakery->bake($schema));
    }

}
 