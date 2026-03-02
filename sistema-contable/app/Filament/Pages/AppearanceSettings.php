<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppearanceSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Personalizacion';

    protected string $view = 'filament.pages.appearance-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('users.update') ?? false;
    }

    public function mount(): void
    {
        $setting = AppSetting::current();

        $this->form->fill([
            'company_name' => $setting->company_name,
            'company_email' => $setting->company_email,
            'company_phone' => $setting->company_phone,
            'company_address' => $setting->company_address,
            'profile_photo_path' => $setting->profile_photo_path,
            'logo_path' => $setting->logo_path,
            'favicon_path' => $setting->favicon_path,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                Section::make('Imagenes')
                    ->description('Cambia la foto de perfil institucional, logo y favicon.')
                    ->schema([
                        Grid::make(3)->schema([
                            FileUpload::make('profile_photo_path')
                                ->label('Foto de perfil')
                                ->image()
                                ->disk('public')
                                ->directory('settings/profile')
                                ->visibility('public'),

                            FileUpload::make('logo_path')
                                ->label('Logo')
                                ->image()
                                ->disk('public')
                                ->directory('settings/logo')
                                ->visibility('public'),

                            FileUpload::make('favicon_path')
                                ->label('Favicon')
                                ->image()
                                ->disk('public')
                                ->directory('settings/favicon')
                                ->visibility('public'),
                        ]),
                    ]),

                Section::make('Datos de empresa')
                    ->description('Informacion visible de la empresa en el panel.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('company_name')
                                ->label('Nombre de empresa')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('company_email')
                                ->label('Correo empresa')
                                ->email()
                                ->maxLength(255),

                            TextInput::make('company_phone')
                                ->label('Telefono')
                                ->maxLength(50),

                            TextInput::make('company_address')
                                ->label('Direccion')
                                ->maxLength(255),
                        ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar cambios')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action(fn () => $this->save()),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        AppSetting::current()->update($data);

        Notification::make()
            ->success()
            ->title('Configuracion actualizada')
            ->body('Los cambios fueron guardados correctamente.')
            ->send();

        $this->redirect(Filament::getHomeUrl(), navigate: true);
    }
}
