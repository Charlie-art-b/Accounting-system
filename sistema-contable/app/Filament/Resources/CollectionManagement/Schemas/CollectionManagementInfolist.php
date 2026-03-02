<?php

namespace App\Filament\Resources\CollectionManagement\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Schema;

class CollectionManagementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la Factura')
                    ->description('Datos de la cuenta por cobrar')
                    ->schema([
                        TextEntry::make('accountReceivable.invoice_number')
                            ->label('Número de factura'),

                        TextEntry::make('accountReceivable.issue_date')
                            ->label('Fecha de emisión')
                            ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '—'),

                        TextEntry::make('accountReceivable.due_date')
                            ->label('Fecha de vencimiento')
                            ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '—'),

                        TextEntry::make('days_late')
                            ->label('Días de atraso')
                            ->badge()
                            ->formatStateUsing(function ($state) {
                                if ($state > 30) return "{$state} días";
                                if ($state > 7) return "{$state} días";
                                return "{$state} días";
                            })
                            ->color(fn ($state) => $state > 30 ? 'danger' : ($state > 7 ? 'warning' : 'success'))
                            ->icon(fn ($state) => $state > 30 ? 'heroicon-o-exclamation-circle' : ($state > 7 ? 'heroicon-o-clock' : 'heroicon-o-check-circle')),

                        TextEntry::make('accountReceivable.description')
                            ->label('Descripción')
                            ->placeholder('Sin descripción')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Cliente')
                    ->schema([
                        TextEntry::make('customer.name')
                            ->label('Nombre'),

                        TextEntry::make('customer.identification')
                            ->label('Identificación'),

                        TextEntry::make('customer.email')
                            ->label('Correo electrónico')
                            ->icon('heroicon-o-envelope'),

                        TextEntry::make('customer.phone')
                            ->label('Teléfono')
                            ->icon('heroicon-o-phone'),
                    ])
                    ->columns(2),

                Section::make('Información Financiera')
                    ->schema([
                        TextEntry::make('accountReceivable.total_amount')
                            ->label('Monto total')
                            ->money('CRC'),

                        TextEntry::make('accountReceivable.paid_amount')
                            ->label('Monto pagado')
                            ->money('CRC'),

                        TextEntry::make('pending_amount')
                            ->label('Monto pendiente')
                            ->money('CRC')
                            ->weight(\Filament\Support\Enums\FontWeight::Bold),

                        TextEntry::make('status')
                            ->label('Estado del cobro')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'overdue' => 'Vencido',
                                'due_soon' => 'Próximo a vencer',
                                default => 'Pendiente',
                            })
                            ->color(fn ($state) => match ($state) {
                                'overdue' => 'danger',
                                'due_soon' => 'warning',
                                default => 'success',
                            })
                            ->icon(fn ($state) => match ($state) {
                                'overdue' => 'heroicon-o-exclamation-circle',
                                'due_soon' => 'heroicon-o-clock',
                                default => 'heroicon-o-check-circle',
                            }),

                        TextEntry::make('accountReceivable.status')
                            ->label('Estado de la cuenta')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'paid' => 'Pagada',
                                'partial' => 'Pago parcial',
                                'pending' => 'Pendiente',
                                default => ucfirst($state ?? 'pending'),
                            })
                            ->color(fn ($state) => match ($state) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                'pending' => 'gray',
                                default => 'gray',
                            }),
                    ])
                    ->columns(2),

                Section::make('Gestión de Cobros')
                    ->description('Información sobre recordatorios y acciones')
                    ->schema([
                        TextEntry::make('last_action')
                            ->label('Última acción')
                            ->placeholder('Sin acciones registradas')
                            ->columnSpanFull(),

                        TextEntry::make('next_reminder_at')
                            ->label('Próximo recordatorio')
                            ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i') : 'No programado')
                            ->icon('heroicon-o-bell'),

                        TextEntry::make('reminder_attempts')
                            ->label('Intentos de recordatorio')
                            ->suffix(' intentos')
                            ->icon('heroicon-o-arrow-path'),

                        TextEntry::make('notes')
                            ->label('Notas de gestión')
                            ->placeholder('Sin notas')
                            ->columnSpanFull()
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'Sin notas';
                                
                                // Formatear notas estructuradas (fecha|monto|nota)
                                $lines = explode("\n", trim($state));
                                $formatted = [];
                                
                                foreach ($lines as $line) {
                                    if (str_contains($line, '|')) {
                                        $parts = explode('|', $line, 3);
                                        if (count($parts) === 3) {
                                            $formatted[] = sprintf(
                                                "📅 %s - ₡%s - %s",
                                                $parts[0],
                                                number_format((float)$parts[1], 2),
                                                $parts[2]
                                            );
                                            continue;
                                        }
                                    }
                                    $formatted[] = $line;
                                }
                                
                                return implode("<br>", $formatted);
                            })
                            ->html(),
                    ])
                    ->columns(2),

                // SECCIÓN: HISTORIAL DE PAGOS
                Section::make('Historial de Pagos')
                    ->description('Pagos registrados para esta cuenta por cobrar')
                    ->schema([
                        RepeatableEntry::make('accountReceivable.payments')
                            ->label('')
                            ->schema([
                                TextEntry::make('paid_at')
                                    ->label('Fecha de pago')
                                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i') : '—')
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('amount')
                                    ->label('Monto')
                                    ->money('CRC')
                                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success'),

                                TextEntry::make('is_reversal')
                                    ->label('Tipo')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state ? 'Reverso' : 'Pago')
                                    ->color(fn ($state) => $state ? 'danger' : 'success'),

                                TextEntry::make('note')
                                    ->label('Nota')
                                    ->placeholder('Sin nota')
                                    ->limit(50),
                            ])
                            ->columns(4)
                            ->hidden(fn ($record) => !$record->accountReceivable || $record->accountReceivable->payments->isEmpty()),
                    ])
                    ->collapsed()
                    ->hidden(fn ($record) => !$record->accountReceivable || $record->accountReceivable->payments->isEmpty()),

                Section::make('Información del Registro')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creado')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Última actualización')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
