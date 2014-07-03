<?php
namespace Bravo3\Bakery\Enum;

use Eloquent\Enumeration\AbstractEnumeration;

/**
 * @method static RepositoryType SVN()
 * @method static RepositoryType GIT()
 */
final class RepositoryType extends AbstractEnumeration
{
    const SVN = 'SVN';
    const GIT = 'GIT';
}
