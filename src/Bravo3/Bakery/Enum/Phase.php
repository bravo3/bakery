<?php
namespace Bravo3\Bakery\Enum;

use Eloquent\Enumeration\AbstractEnumeration;

/**
 * @method static Phase ERROR()
 * @method static Phase CONNECTION()
 * @method static Phase ENVIRONMENT()
 * @method static Phase OPERATION()
 * @method static Phase CODE_CHECKOUT()
 * @method static Phase SCRIPT()
 * @method static Phase UPDATE_PACKAGES()
 * @method static Phase INSTALL_PACKAGES()
 * @method static Phase RUN_SERVICES()
 * @method static Phase TESTING()
 */
final class Phase extends AbstractEnumeration
{
    const ERROR            = 'ERROR';
    const CONNECTION       = 'CONNECTION';
    const ENVIRONMENT      = 'ENVIRONMENT';
    const OPERATION        = 'OPERATION';
    const CODE_CHECKOUT    = 'CODE_CHECKOUT';
    const SCRIPT           = 'SCRIPT';
    const UPDATE_PACKAGES  = 'UPDATE_PACKAGES';
    const INSTALL_PACKAGES = 'INSTALL_PACKAGES';
    const RUN_SERVICES     = 'RUN_SERVICES';
    const TESTING          = 'TESTING';
}
