<?php

final class WatermarkDecorator extends ReportDecorator
{
    public function generate(): string
    {
        return $this->report->generate() . ' + marca de agua';
    }
}
