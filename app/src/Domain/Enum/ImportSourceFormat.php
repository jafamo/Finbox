<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum ImportSourceFormat: string
{
    case Csv = 'csv';
    case PdfLayout = 'pdf_layout';
}
