<?php

final class DigitalSignatureDecorator extends ReportDecorator
{
    public function generate(): string
    {
        return $this->report->generate() . ' + firma digital';
    }
}
