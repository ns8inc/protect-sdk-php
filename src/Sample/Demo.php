<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Sample;

/**
 * A demo class for illustrative purposes.
 *
 * Feel free to delete this once there's some actual code in this repo.
 */
class Demo
{
    /**
     * This is just a number.
     *
     * @var int
     */
    protected $number;

    /**
     * Instantiate the class.
     *
     * @param int $number A number
     */
    public function __construct(int $number)
    {
        $this->number = $number;
    }

    /**
     * Checks whether the object's number is greater than 2.
     *
     * @return bool True if the number is greater than 2, False otherwise
     */
    public function isGreaterThanTwo() : bool
    {
        return $this->number > 2;
    }
}
