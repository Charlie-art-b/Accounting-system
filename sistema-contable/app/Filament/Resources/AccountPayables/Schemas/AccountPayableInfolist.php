<?php

namespace App\Filament\Resources\AccountPayables\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountPayableInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Proveedor')
                    ->schema([
                        TextEntry::make('supplier.nombre_razon_social')
                            ->label('Nombre/Razón Social'),
                        TextEntry::make('supplier.tipo_identificacion')
                            ->label('Tipo de Identificación')
                            ->badge(),
                        TextEntry::make('supplier.numero_identificacion')
                            ->label('Número de Identificación'),
                        TextEntry::make('supplier.correo_electronico')
                            ->label('Correo Electrónico')
                            ->icon('heroicon-o-envelope')
                            ->placeholder('-'),
                        TextEntry::make('supplier.telefono')
                            ->label('Teléfono')
                            ->icon('heroicon-o-phone')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Información del Documento')
                    ->schema([
                        TextEntry::make('document_number')
                            ->label('Número de Documento')
                            ->copyable()
                            ->weight('bold'),
                        TextEntry::make('type')
                            ->label('Tipo de Documento')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'invoice' => 'success',
                                'receipt' => 'info',
                                'debit_note' => 'warning',
                                'other' => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'invoice' => 'Factura',
                                'receipt' => 'Recibo',
                                'debit_note' => 'Nota de débito',
                                'other' => 'Otro',
                                default => $state,
                            }),
                        TextEntry::make('issue_date')
                            ->label('Fecha de Emisión')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar'),
                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'partial' => 'info',
                                'paid' => 'success',
                                'voided' => 'danger',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'Pendiente',
                                'partial' => 'Parcial',
                                'paid' => 'Pagado',
                                'voided' => 'Anulado',
                                default => $state,
                            }),
                    ])
                    ->columns(2),

                Section::make('Términos de Pago')
                    ->schema([
                        TextEntry::make('payment_terms')
                            ->label('Términos de Pago')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'cash' => 'success',
                                'credit' => 'warning',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'cash' => 'Efectivo',
                                'credit' => 'Crédito',
                                default => $state,
                            }),
                        TextEntry::make('payment_period')
                            ->label('Período de Pago')
                            ->numeric()
                            ->suffix(' días')
                            ->placeholder('-')
                            ->visible(fn ($record): bool => $record->payment_terms === 'credit'),
                        TextEntry::make('due_date')
                            ->label('Fecha de Vencimiento')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-clock')
                            ->color(fn ($record): string => 
                                $record->due_date < now() && $record->status !== 'paid' 
                                    ? 'danger' 
                                    : 'gray'
                            ),
                    ])
                    ->columns(3),

                Section::make('Información Financiera')
                    ->schema([
                        TextEntry::make('total_amount')
                            ->label('Monto Total')
                            ->money('USD')
                            ->weight('bold')
                            ->size('lg'),
                        TextEntry::make('paid_amount')
                            ->label('Monto Pagado')
                            ->money('USD')
                            ->color('success'),
                        TextEntry::make('pending_amount')
                            ->label('Saldo Pendiente')
                            ->money('USD')
                            ->color(fn ($record): string => $record->pending_amount > 0 ? 'warning' : 'success')
                            ->weight('bold'),
                        TextEntry::make('payment_date')
                            ->label('Fecha de Pago')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-check-circle')
                            ->placeholder('Sin pago registrado')
                            ->visible(fn ($record): bool => $record->payment_date !== null),
                    ])
                    ->columns(2),

                Section::make('Información del Registro')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Fecha de Creación')
                            ->dateTime('d/m/Y H:i:s'),
                        TextEntry::make('updated_at')
                            ->label('Última Actualización')
                            ->dateTime('d/m/Y H:i:s')
                            ->since(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
