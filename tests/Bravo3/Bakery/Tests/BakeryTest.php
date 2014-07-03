<?php
namespace Bravo3\Bakery\Tests;

use Bravo3\Bakery\Bakery;
use Bravo3\Bakery\Entity\Host;
use Bravo3\Bakery\Entity\Repository;
use Bravo3\Bakery\Entity\Schema;
use Bravo3\Bakery\Enum\PackagerType;
use Bravo3\Bakery\Enum\Phase;
use Bravo3\Bakery\Enum\RepositoryType;
use Bravo3\Bakery\Operation\CodeCheckoutOperation;
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
        //$host            = new Host('127.0.0.1', 22, new KeyCredential('jordon', null, '/home/jordon/.ssh/jordon.pem'));
        $host            = new Host('54.206.100.211', 22, new KeyCredential('ec2-user', null, '/home/jordon/.ssh/test-sydney.pem'));
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

        $repo = new Repository();
        $repo->setRepositoryType(RepositoryType::GIT());
        $repo->setCheckoutPath('/tmp/test-repo');
        $repo->setUri('git@github.com:jordonsc/hyperion_dbal.git');
        $repo->setPrivateKey(file_get_contents('/home/jordon/.ssh/jordon.pem'));
        $repo->setTag('c38a0835235003a08d3f5f851f57ba5914cff98e');
        $repo->setHostFingerprint('16:27:ac:a5:76:28:2d:36:63:1b:56:4d:eb:df:a6:48');

        $schema = new Schema(PackagerType::YUM());
        $schema->addOperation(
            new EnvironmentOperation([
                'env' => 'bake',
                'action' => '1234',
            ])
        )->addOperation(
            new CodeCheckoutOperation($repo)
        );

//            ->addOperation(
//                new UpdatePackagesOperation()
//        )->addOperation(
//            new InstallPackagesOperation(['apache2', 'mysql-server'])
//        );

        $this->assertTrue($bakery->bake($schema));
    }

}
 