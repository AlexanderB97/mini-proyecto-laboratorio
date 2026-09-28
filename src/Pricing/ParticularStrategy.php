<?php

final class ParticularStrategy implements PricingStrategy
{
    public function calculate(float $amount): float
    {
        return $amount;
    }
}
