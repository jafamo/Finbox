<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Enum\ImportSourceFormat;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'import_profile')]
class ImportProfile
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Bank::class)]
    #[ORM\JoinColumn(name: 'bank_id', referencedColumnName: 'id', nullable: false)]
    private Bank $bank;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 20, enumType: ImportSourceFormat::class)]
    private ImportSourceFormat $sourceFormat;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $dateFormat;

    #[ORM\Column(type: 'string', length: 1, nullable: true)]
    private ?string $decimalSeparator;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $parserConfig;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $encoding;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive;

    public function __construct(
        Uuid $id,
        Bank $bank,
        string $name,
        ImportSourceFormat $sourceFormat,
        ?string $dateFormat = null,
        ?string $decimalSeparator = null,
        ?array $parserConfig = null,
        ?string $encoding = null,
        bool $isActive = true,
    ) {
        $this->id = $id;
        $this->bank = $bank;
        $this->name = $name;
        $this->sourceFormat = $sourceFormat;
        $this->dateFormat = $dateFormat;
        $this->decimalSeparator = $decimalSeparator;
        $this->parserConfig = $parserConfig;
        $this->encoding = $encoding;
        $this->isActive = $isActive;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function bank(): Bank
    {
        return $this->bank;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function sourceFormat(): ImportSourceFormat
    {
        return $this->sourceFormat;
    }

    public function dateFormat(): ?string
    {
        return $this->dateFormat;
    }

    public function decimalSeparator(): ?string
    {
        return $this->decimalSeparator;
    }

    public function parserConfig(): ?array
    {
        return $this->parserConfig;
    }

    public function encoding(): ?string
    {
        return $this->encoding;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function update(
        string $name,
        ImportSourceFormat $sourceFormat,
        ?string $dateFormat,
        ?string $decimalSeparator,
        ?array $parserConfig,
        ?string $encoding,
        bool $isActive,
    ): void {
        $this->name = $name;
        $this->sourceFormat = $sourceFormat;
        $this->dateFormat = $dateFormat;
        $this->decimalSeparator = $decimalSeparator;
        $this->parserConfig = $parserConfig;
        $this->encoding = $encoding;
        $this->isActive = $isActive;
    }
}
