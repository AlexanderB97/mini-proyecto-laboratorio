<?php

final class PrepagaStrategy implements PricingStrategy
{
    public function __construct(private float $discount = 0.20) {}

    public function calculate(float $amount): float
    {
        return $amount * (1 - $this->discount);
    }
}
