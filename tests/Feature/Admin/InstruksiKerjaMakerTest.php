<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;

class InstruksiKerjaMakerTest extends TestCase
{
    public function test_admin_can_open_instruksi_kerja_maker(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.instruksi-kerja-maker.index'))
            ->assertOk()
            ->assertSee('Instruksi Kerja Maker')
            ->assertSee('Prosedur Pengecekan')
            ->assertSee('Contoh Benar')
            ->assertSee('Contoh Salah');
    }

    public function test_guest_cannot_open_instruksi_kerja_maker(): void
    {
        $this->get(route('admin.instruksi-kerja-maker.index'))
            ->assertRedirect(route('login'));
    }
}