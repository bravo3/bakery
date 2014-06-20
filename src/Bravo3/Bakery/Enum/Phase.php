<?php
namespace Bravo3\Bakery\Enum;

use Eloquent\Enumeration\AbstractEnumeration;

/**
 * @method static Phase CONNECTION()
 * @method static Phase ENVIRONMENT()
 * @method static Phase OPERATION()
 * @method static Phase SUB_OPERATION()
 * @method static Phase ERROR()
 */
final class Phase extends AbstractEnumeration
{
    const CONNECTION    = 'CONNECTION';
    const ENVIRONMENT    = 'ENVIRONMENT';
    const OPERATION     = 'OPERATION';
    const SUB_OPERATION = 'SUB_OPERATION';
    const ERROR         = 'ERROR';
} 