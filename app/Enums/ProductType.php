<?php

namespace App\Enums;

enum ProductType: string
{
    case Course = 'course';
    case Webinar = 'webinar';
    case Workshop = 'workshop';
    case Bootcamp = 'bootcamp';
    case Mentoring = 'mentoring';
    case DigitalProduct = 'digital_product';

    public function label(): string
    {
        return match ($this) {
            self::Course => 'Course',
            self::Webinar => 'Webinar',
            self::Workshop => 'Workshop',
            self::Bootcamp => 'Bootcamp',
            self::Mentoring => 'Mentoring',
            self::DigitalProduct => 'Digital Product',
        };
    }

    /** Badge variant for this type — the badge component only offers these three. */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Course, self::Webinar => 'neutral',
            self::Workshop, self::Bootcamp => 'accent',
            self::Mentoring, self::DigitalProduct => 'lavender',
        };
    }

    /**
     * Label for the (disabled, non-functional) detail-page CTA — commerce
     * itself is out of scope until Phase 5.
     */
    public function ctaLabel(): string
    {
        return match ($this) {
            self::Webinar, self::Workshop => 'Register',
            self::Mentoring => 'Book a Session',
            self::DigitalProduct => 'Get Access',
            self::Course, self::Bootcamp => 'Enroll Now',
        };
    }

    /** @return array<string, string> value => label, for Blade <select> options. */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }
}
