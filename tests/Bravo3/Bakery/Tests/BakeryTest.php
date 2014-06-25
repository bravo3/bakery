<?php
namespace Bravo3\Bakery\Tests;

use Bravo3\Bakery\Bakery;
use Bravo3\Bakery\Entity\Host;
use Bravo3\Bakery\Entity\Schema;
use Bravo3\Bakery\Enum\PackagerType;
use Bravo3\Bakery\Enum\Phase;
use Bravo3\Bakery\Operation\EnvironmentOperation;
use Bravo3\Bakery\Operation\InstallPackagesOperation;
use Bravo3\Bakery\Operation\ScriptOperation;
use Bravo3\Bakery\Operation\UpdatePackagesOperation;
use Bravo3\Bakery\Tests\Resources\FileLogger;
use Bravo3\SSH\Credentials\KeyCredential;
use Bravo3\SSH\Credentials\PasswordCredential;

class BakeryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @large
     */
    public function testStuff()
    {
        $host            = new Host('127.0.0.1', 22, new KeyCredential('jordon', null, '/home/jordon/.ssh/jordon.pem'));
        $logger_bake     = new FileLogger("/tmp/bakery_bake.log", false, true);
        $logger_callback = new FileLogger("/tmp/bakery_callback.log", false, true);
        $logger_out      = new FileLogger("/tmp/bakery_out.log", false, true);

        $logger_bake->debug("\n\n\n---");
        $logger_callback->debug("\n\n\n---");
        $logger_out->debug("\n\n\n---");

        $callback = function (Phase $phase, $progress, $total, $msg) use ($logger_callback) {
            $logger_callback->info($phase->value().' '.$progress.' / '.$total.': '.$msg);
        };

        $bakery = new Bakery($host, $logger_out, $callback);
        $bakery->setLogger($logger_bake);

        $schema = new Schema(PackagerType::APT());
        $schema->addOperation(
            new EnvironmentOperation([
                'env' => 'bake',
                'action' => '1234',
            ])
        )->addOperation(
                new UpdatePackagesOperation()
        )->addOperation(
            new InstallPackagesOperation(['apache2', 'mysql-server'])
        );

        $this->assertTrue($bakery->bake($schema));
    }

}
 