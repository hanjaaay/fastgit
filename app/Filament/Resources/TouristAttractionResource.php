<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TouristAttractionResource\Pages;
use App\Filament\Resources\TouristAttractionResource\RelationManagers;
use App\Models\TouristAttraction;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components; // Pastikan ini sudah di-import
use Filament\Tables\Filters;
use Filament\Forms\Components\TextInput; // <-- PASTIIN INI JUGA ADA DI IMPORTS

class TouristAttractionResource extends Resource
{
    protected static ?string $model = TouristAttraction::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    protected static ?string $label = 'Wisata';

    protected static ?string $pluralLabel = 'Wisata';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Card::make()
                    ->schema([
                        Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Components\Textarea::make('description')
                            ->maxLength(65535),
                        Components\TextInput::make('location')
                            ->maxLength(255),
                        Components\TextInput::make('city')
                            ->maxLength(255),
                        Components\TextInput::make('province')
                            ->maxLength(255),
                        Components\TagsInput::make('facilities'),
                        Components\TimePicker::make('opening_hours'),
                        Components\TimePicker::make('closing_hours'),
                        // Tambahkan field 'price' di sini
                        TextInput::make('price') // Menggunakan TextInput dari Filament\Forms\Components\TextInput
                            ->numeric() // Memastikan input hanya angka
                            ->required() // Sangat penting: membuat field ini wajib diisi di form
                            ->default(0) // Opsional: Memberikan nilai default 0 jika tidak diisi,
                                        // meskipun 'required' akan memastikan selalu ada input
                            ->prefix('IDR') // Opsional: Untuk tampilan mata uang
                            ->label('Harga Tiket Dasar'), // Label di UI Filament
                        Components\TextInput::make('latitude'),
                        Components\TextInput::make('longitude'),
                        Components\FileUpload::make('featured_image')
                            ->image()
                            ->columnSpanFull(), // Tambahkan ini agar field mengambil lebar penuh jika perlu
                        Components\FileUpload::make('gallery')
                            ->image()
                            ->multiple()
                            ->columnSpanFull(), // Tambahkan ini agar field mengambil lebar penuh jika perlu
                        Components\Toggle::make('is_active')
                            ->label('Aktif'), // Label di UI Filament
                    ])
                    ->columns(2), // Ini akan mengatur field dalam 2 kolom
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama Wisata')->searchable(),
                Tables\Columns\TextColumn::make('location')->label('Lokasi')->searchable(),
                Tables\Columns\TextColumn::make('description')->label('Deskripsi')->limit(30),
                Tables\Columns\TextColumn::make('status')->badge()->label('Status'),
                Tables\Columns\TextColumn::make('price')->label('Harga')->money('IDR')->sortable(), // Tambahkan juga di table
                Tables\Columns\ImageColumn::make('featured_image')->label('Gambar Utama'), // Tambahkan juga di table
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTouristAttractions::route('/'),
            'create' => Pages\CreateTouristAttraction::route('/create'),
            'edit' => Pages\EditTouristAttraction::route('/{record}/edit'),
        ];
    }     
}