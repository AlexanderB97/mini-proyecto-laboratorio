<?php

final class PdfReportDecorator extends ReportDecorator
{
    public function generate(): string
    {
        return $this->report->generate() . ' + PDF';
    }
}
