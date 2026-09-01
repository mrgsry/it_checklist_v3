<?php

namespace Tests\Unit;

use App\Models\SubmissionAnswer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SubmissionAnswerTest extends TestCase
{
    #[DataProvider('checkboxValueProvider')]
    public function test_it_parses_selected_checkbox_options(?string $value, array $expected): void
    {
        $answer = new SubmissionAnswer(['answer_value' => $value]);

        $this->assertSame($expected, $answer->selectedCheckboxOptions());
    }

    public static function checkboxValueProvider(): array
    {
        return [
            'multiple options' => ['Pengecekan Lensa Camera, Pengecekan Kabel Camera', ['Pengecekan Lensa Camera', 'Pengecekan Kabel Camera']],
            'single option' => ['Pengecekan Lensa Camera', ['Pengecekan Lensa Camera']],
            'empty value' => [null, []],
            'empty entries' => [' Pengecekan Lensa Camera, , ', ['Pengecekan Lensa Camera']],
        ];
    }

    public function test_it_reads_checkbox_statuses_and_identifies_abnormal_statuses(): void
    {
        $answer = new SubmissionAnswer([
            'answer_value' => json_encode([
                'Pengecekan Lensa Camera' => 'normal',
                'Pengecekan Body Camera' => 'tidak_normal',
            ], JSON_THROW_ON_ERROR),
        ]);

        $this->assertSame([
            'Pengecekan Lensa Camera' => 'normal',
            'Pengecekan Body Camera' => 'tidak_normal',
        ], $answer->checkboxStatuses());
        $this->assertTrue($answer->hasAbnormalCheckboxStatus());
    }

    public function test_legacy_checkbox_values_are_treated_as_normal(): void
    {
        $answer = new SubmissionAnswer(['answer_value' => 'Pengecekan Lensa Camera']);

        $this->assertSame(['Pengecekan Lensa Camera' => 'normal'], $answer->checkboxStatuses());
        $this->assertFalse($answer->hasAbnormalCheckboxStatus());
    }
}