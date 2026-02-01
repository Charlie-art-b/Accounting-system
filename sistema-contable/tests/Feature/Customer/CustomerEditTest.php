<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\Test;

class CustomerEditTest extends TestCase
{
    use RefreshDatabase;

    protected function customer(): Customer
    {
        return Customer::create([
            'name' => 'Base',
            'first_last_name' => 'Test',
            'id_type' => 'identification',
            'identification' => 'E001',
            'email' => 'base@test.com',
            'customer_type' => 'individual',
        ]);
    }

    #[Test] public function edit_customer_name()
    {
        $c = $this->customer();
        $c->update(['name' => 'Nuevo']);
        $this->assertEquals('Nuevo', $c->fresh()->name);
    }

    #[Test] public function edit_first_last_name()
    {
        $c = $this->customer();
        $c->update(['first_last_name' => 'Actualizado']);
        $this->assertEquals('Actualizado', $c->fresh()->first_last_name);
    }

    #[Test] public function edit_second_last_name()
    {
        $c = $this->customer();
        $c->update(['second_last_name' => 'Segundo']);
        $this->assertEquals('Segundo', $c->fresh()->second_last_name);
    }

    #[Test] public function edit_email_to_lowercase()
    {
        $c = $this->customer();
        $c->update(['email' => 'NUEVO@MAIL.COM']);
        $this->assertEquals('nuevo@mail.com', $c->fresh()->email);
    }

    #[Test] public function edit_phone()
    {
        $c = $this->customer();
        $c->update(['phone' => '88888888']);
        $this->assertEquals('88888888', $c->fresh()->phone);
    }

    #[Test] public function edit_address()
    {
        $c = $this->customer();
        $c->update(['address' => 'San José']);
        $this->assertEquals('San José', $c->fresh()->address);
    }

    #[Test] public function edit_notes()
    {
        $c = $this->customer();
        $c->update(['notes' => 'Nota']);
        $this->assertEquals('Nota', $c->fresh()->notes);
    }

    #[Test] public function edit_status()
    {
        $c = $this->customer();
        $c->update(['status' => false]);
        $this->assertFalse($c->fresh()->status);
    }

    #[Test] public function edit_customer_type()
    {
        $c = $this->customer();
        $c->update(['customer_type' => 'legal_person']);
        $this->assertEquals('legal_person', $c->fresh()->customer_type);
    }

    #[Test] public function not_allow_invalid_customer_type()
    {
        $c = $this->customer();
        $this->expectException(QueryException::class);
        $c->update(['customer_type' => 'company']);
    }
}