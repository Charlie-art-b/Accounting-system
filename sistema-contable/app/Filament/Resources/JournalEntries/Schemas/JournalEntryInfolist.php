<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Schema;

class JournalEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Encabezado')
                ->columns(2)
                ->schema([
                    TextEntry::make('id')
                        ->label('ID'),

                    TextEntry::make('customer.name')
                        ->label('Cliente'),

                    TextEntry::make('journal_type')
                        ->label('Tipo')
                        ->badge(),

                    TextEntry::make('reference')
                        ->label('Referencia')
                        ->placeholder('—'),

                    TextEntry::make('description')
                        ->label('Descripción')
                        ->columnSpanFull()
                        ->placeholder('—'),

                    TextEntry::make('posted_at')
                        ->label('Posteado')
                        ->dateTime()
                        ->placeholder('Draft'),

                    TextEntry::make('postedBy.name')
                        ->label('Posteado por')
                        ->placeholder('—'),

                    TextEntry::make('total_debit')
                        ->label('Total Débitos'),

                    TextEntry::make('total_credit')
                        ->label('Total Créditos'),

                    TextEntry::make('is_reversal')
                        ->label('Es reverso')
                        ->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No'),

                    TextEntry::make('reversed_entry_id')
                        ->label('Reversa a')
                        ->placeholder('—'),
                ]),

            Section::make('Líneas del asiento')
                ->schema([
                    RepeatableEntry::make('lines')
                        ->label('')
                        ->schema([
                            TextEntry::make('account.display')
                                ->label('Cuenta')
                                ->weight('bold'),

                            TextEntry::make('description')
                                ->label('Detalle')
                                ->placeholder('—'),

                            TextEntry::make('debit')
                                ->label('Débito'),

                            TextEntry::make('credit')
                                ->label('Crédito'),
                        ])
                        ->columns(4),
                ]),
        ]);
    }
}
