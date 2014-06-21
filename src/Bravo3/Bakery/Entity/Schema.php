<?php
namespace Bravo3\Bakery\Entity;

use Bravo3\Bakery\Enum\PackagerType;
use Bravo3\Bakery\Operation\OperationInterface;

class Schema implements \IteratorAggregate
{

    /**
     * @var PackagerType
     */
    protected $packager_type;

    /**
     * @var OperationInterface[]
     */
    protected $operations;

    /**
     * @param PackagerType         $packager_type
     * @param OperationInterface[] $operations
     */
    function __construct(PackagerType $packager_type, $operations = [])
    {
        $this->packager_type = $packager_type;
        $this->operations    = $operations;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->operations);
    }

    /**
     * Number of operations on the stack
     *
     * @return int
     */
    public function getOperationCount()
    {
        return count($this->operations);
    }

    /**
     * Add an operation to the schema
     *
     * @param OperationInterface $operation
     * @return $this
     */
    public function addOperation(OperationInterface $operation)
    {
        $this->operations[] = $operation;
        return $this;
    }

    /**
     * Set all schema operations
     *
     * @param OperationInterface[] $operations
     * @return $this
     */
    public function setOperations($operations)
    {
        $this->operations = $operations;
        return $this;
    }

    /**
     * Get all operations
     *
     * @return OperationInterface[]
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * Set the packager type
     *
     * @param PackagerType $packager_type
     * @return $this
     */
    public function setPackagerType(PackagerType $packager_type)
    {
        $this->packager_type = $packager_type;
        return $this;
    }

    /**
     * Get the packager type
     *
     * @return PackagerType
     */
    public function getPackagerType()
    {
        return $this->packager_type;
    }

}
