<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'bank')]
class Bank
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $internalCode;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes;

    public function __construct(
        Uuid $id,
        string $name,
        ?string $internalCode = null,
        ?string $notes = null,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->internalCode = $internalCode;
        $this->notes = $notes;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function internalCode(): ?string
    {
        return $this->internalCode;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function update(string $name, ?string $internalCode, ?string $notes): void
    {
        $this->name = $name;
        $this->internalCode = $internalCode;
        $this->notes = $notes;
    }
}
