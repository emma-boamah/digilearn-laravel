<?php

namespace App\Enums;

enum LessonLevel: string
{
    case PRIMARY_1 = 'primary-1';
    case PRIMARY_2 = 'primary-2';
    case PRIMARY_3 = 'primary-3';
    case PRIMARY_4 = 'primary-4';
    case PRIMARY_5 = 'primary-5';
    case PRIMARY_6 = 'primary-6';
    case JHS_1 = 'jhs-1';
    case JHS_2 = 'jhs-2';
    case JHS_3 = 'jhs-3';
    case SHS_1 = 'shs-1';
    case SHS_2 = 'shs-2';
    case SHS_3 = 'shs-3';
    case UNIVERSITY = 'university';

    public function score(): int
    {
        return match($this) {
            self::PRIMARY_1 => 1,
            self::PRIMARY_2 => 2,
            self::PRIMARY_3 => 3,
            self::PRIMARY_4 => 4,
            self::PRIMARY_5 => 5,
            self::PRIMARY_6 => 6,
            self::JHS_1     => 7,
            self::JHS_2     => 8,
            self::JHS_3     => 9,
            self::SHS_1     => 10,
            self::SHS_2     => 11,
            self::SHS_3     => 12,
            self::UNIVERSITY=> 13,
        };
    }

    public function stage(): string
    {
        return match($this) {
            self::PRIMARY_1, self::PRIMARY_2, self::PRIMARY_3, 
            self::PRIMARY_4, self::PRIMARY_5, self::PRIMARY_6 => 'foundation',
            
            self::JHS_1, self::JHS_2, self::JHS_3 => 'intermediate',
            
            self::SHS_1, self::SHS_2, self::SHS_3 => 'advanced',
            
            self::UNIVERSITY => 'higher_education',
        };
    }
}
