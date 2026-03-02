<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')

            ->columns([

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable(),

                // 🔥 CORRECTO PARA SPATIE
                TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->separator(', ')
                    ->colors([
                        'danger' => fn ($state) => $state === 'administrador',
                        'warning' => fn ($state) => $state === 'gerente',
                        'info' => fn ($state) => $state === 'sub-gerente',
                        'success' => fn ($state) => $state === 'asistente',
                    ])
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Rol')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->label('Fecha de creacion')
                    ->form([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
            ])

            ->recordActions([

                ViewAction::make(),

                EditAction::make()
                    ->visible(fn () => auth()->user()?->can('users.update')),

                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('users.delete'))
                    ->requiresConfirmation()
                    ->before(function (User $record, DeleteAction $action) {

                        $currentUser = auth()->user();

                        if ($record->id === $currentUser->id) {
                            Notification::make()
                                ->danger()
                                ->title('No permitido')
                                ->body('No puedes eliminar tu propio usuario.')
                                ->send();

                            $action->halt();
                        }

                
                        if ($record->hasRole('administrador')) {

                            $adminsCount = User::role('administrador')->count();

                            if ($adminsCount <= 1) {
                                Notification::make()
                                    ->danger()
                                    ->title('No permitido')
                                    ->body('No puedes eliminar el último administrador del sistema.')
                                    ->send();

                                $action->halt();
                            }
                        }

                        foreach ($record->getAllPermissions() as $permission) {
                            if (!$currentUser->can($permission->name)) {

                                Notification::make()
                                    ->danger()
                                    ->title('No autorizado')
                                    ->body('No puedes eliminar un usuario con permisos superiores a los tuyos.')
                                    ->send();

                                $action->halt();
                            }
                        }
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Usuario eliminado')
                            ->body('El usuario ha sido eliminado correctamente.')
                    ),
            ])

            ->toolbarActions([

                BulkActionGroup::make([

                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('users.delete'))
                        ->requiresConfirmation()
                        ->before(function ($records, DeleteBulkAction $action) {

                            $currentUser = auth()->user();
                            $blocked = [];

                            foreach ($records as $user) {

                                if ($user->id === $currentUser->id) {
                                    $blocked[] = "{$user->name}: No puedes eliminar tu propio usuario.";
                                    continue;
                                }

                                if ($user->hasRole('administrador')) {

                                    $adminsCount = User::role('administrador')->count();

                                    if ($adminsCount <= 1) {
                                        $blocked[] = "{$user->name}: Es el último administrador.";
                                        continue;
                                    }
                                }

                                foreach ($user->getAllPermissions() as $permission) {
                                    if (!$currentUser->can($permission->name)) {
                                        $blocked[] = "{$user->name}: Tiene permisos superiores.";
                                        break;
                                    }
                                }
                            }

                            if (!empty($blocked)) {

                                $reasonsList = implode("\n• ", $blocked);

                                Notification::make()
                                    ->danger()
                                    ->title('Eliminación bloqueada')
                                    ->body("No se pueden eliminar algunos usuarios:\n\n• {$reasonsList}")
                                    ->send();

                                $action->halt();
                            }
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Usuarios eliminados')
                                ->body('Los usuarios seleccionados han sido eliminados correctamente.')
                        ),
                ]),
            ]);
    }
}
