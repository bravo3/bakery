<?php
namespace Bravo3\Bakery\Enum;

use Eloquent\Enumeration\AbstractEnumeration;

/**
 * Supported packagers
 *
 * @method static PackagerType YUM()
 * @method static PackagerType APT()
 */
final class PackagerType extends AbstractEnumeration
{
    const YUM = 'YUM';
    const APT = 'APT';
}
