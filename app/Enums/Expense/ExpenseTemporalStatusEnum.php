<?php

namespace App\Enums\Expense;

enum ExpenseTemporalStatusEnum: string
{
    case ON_TIME = 'On Time';
    case DUE_SOON = 'Due Soon';
    case OVERDUE = 'Overdue';

    public static function getTranslateds(): array
    {
        return [
            self::ON_TIME->value => 'A tiempo',
            self::DUE_SOON->value => 'Pronto vencimiento',
            self::OVERDUE->value => 'Vencido',
        ];
    }
}
