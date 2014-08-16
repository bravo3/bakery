<?php
namespace Bravo3\Bakery\Enum;

use Eloquent\Enumeration\AbstractEnumeration;

/**
 * @method static ServiceType SYSVINIT()
 * @method static ServiceType SYSTEMD()
 * @method static ServiceType UPSTART()
 */
final class ServiceType extends AbstractEnumeration
{
    const SYSVINIT = 'SYSVINIT';
    const UPSTART  = 'UPSTART';
    const SYSTEMD  = 'SYSTEMD';
}
