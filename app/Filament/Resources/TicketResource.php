<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use App\Models\TouristAttraction;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make() // Opsional: bungkus dalam card agar lebih rapi
                    ->schema([
                        Select::make('tourist_attraction_id')
                            ->label('Destinasi Wisata')
                            ->options(TouristAttraction::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Tiket')
                            ->required(),
                        TextInput::make('type')
                            ->label('Tipe')
                            ->required(),
                        TextInput::make('price')
                            ->label('Harga')
                            ->numeric()
                            ->required()
                            ->prefix('IDR'), // Menambahkan prefix IDR untuk tampilan
                        TextInput::make('quota')
                            ->label('Kuota')
                            ->numeric()
                            ->required(),
                        // --- TAMBAHKAN FIELD INI ---
                        TextInput::make('available_quantity')
                            ->label('Jumlah Tersedia Awal') // Label yang jelas di UI
                            ->numeric() // Pastikan inputnya angka
                            ->required(), // **Ini yang paling penting:** membuat field ini wajib diisi
                        // --- AKHIR TAMBAH FIELD ---
                        DateTimePicker::make('valid_from')
                            ->label('Berlaku Dari')
                            ->required(),
                        DateTimePicker::make('valid_until')
                            ->label('Berlaku Sampai')
                            ->required(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->nullable(), // Mengizinkan deskripsi kosong di form jika di DB juga nullable
                                         // Jika di DB NOT NULL, tambahkan ->required()
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(2), // Atur layout menjadi 2 kolom jika diinginkan
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('touristAttraction.name')
                    ->label('Destinasi Wisata')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Tiket')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quota')
                    ->label('Kuota')
                    ->numeric()
                    ->sortable(),
                // --- TAMBAHKAN KOLOM INI DI TABEL ---
                Tables\Columns\TextColumn::make('available_quantity')
                    ->label('Tersedia') // Label di tabel
                    ->numeric()
                    ->sortable(),
                // --- AKHIR TAMBAH KOLOM ---
                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Berlaku Dari')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Berlaku Sampai')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}