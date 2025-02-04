<?php

namespace App\Models\Enums;

use Illuminate\Support\Collection;

enum SchoolCharacteristic: int
{
    case ELEMENTARY_EDUCATION = 1;
    case EARLY_CHILDHOOD_EDUCATION = 2;
    case ACCREDITED_SCHOOL = 3;
    case OTHER = 4;

    public function name(): string
    {
        return match ($this) {
            self::ELEMENTARY_EDUCATION => 'Ensino Fundamental',
            self::EARLY_CHILDHOOD_EDUCATION => 'Educação Infantil',
            self::ACCREDITED_SCHOOL => 'Escola Credenciada',
            self::OTHER => 'Outra',
        };
    }

    /**
     * @return Collection<int, string>
     */
    public static function getDescriptiveValues(): Collection
    {
        return collect(self::cases())->mapWithKeys(fn (self $type) => [$type->value => $type->name()]);
    }
}
