<?php

namespace App\Filament\Resources\CommunityResource\RelationManagers;

use App\Filament\Resources\CommunityResource;
use App\Models\Community;
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Jesuit;

class AttachedHousesRelationManager extends RelationManager
{
    protected static string $relationship = 'attachedHouses';
    protected static ?string $title = 'Attached Houses';
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->unique(Community::class, 'code', ignoreRecord: true),
                Forms\Components\Select::make('region_id')
                    ->relationship('region', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('address')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('diocese')->maxLength(255),
                Forms\Components\TextInput::make('taluk')->maxLength(255),
                Forms\Components\TextInput::make('district')->maxLength(255),
                Forms\Components\TextInput::make('state')->maxLength(255),
                Forms\Components\TextInput::make('country')->maxLength(255)->default('India'),
                Forms\Components\TextInput::make('phone')->tel()->maxLength(255),
                Forms\Components\TextInput::make('email')->email()->maxLength(255),
                Forms\Components\Toggle::make('is_active')->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region.name')
                    ->sortable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('leader')
                    ->label('Coordinator')
                    ->formatStateUsing(fn ($state): ?string => $state?->user?->name)
                    ->placeholder('Not Assigned'),
                Tables\Columns\TextColumn::make('jesuits_count')
                    ->label('Members')
                    ->counts('jesuits')
                    ->placeholder('0'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('region')
                    ->relationship('region', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $owner = $this->getOwnerRecord();
                        $data['is_attached_house'] = true;
                        $data['parent_community_id'] = $owner->id;
                        $data['province_id'] = $owner->province_id;
                        $data['assistancy_id'] = $owner->assistancy_id;
                        $data['superior_type'] = 'Coordinator';
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Community $record): string => CommunityResource::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make()
                    ->url(fn (Community $record): string => CommunityResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['region'])->withCount('jesuits'));
    }

    protected function getTableQuery(): Builder
    {
        $ownerRecord = $this->getOwnerRecord();

        if (!$ownerRecord) {
            Log::error('AttachedHousesRelationManager Error: Owner record is null.');
            throw new \Exception('Owner record not found in AttachedHousesRelationManager::getTableQuery.');
        }

        // Get the relationship query directly from the owner record
        // This ensures the base constraints (like parent_community_id = ownerRecord->id) are applied
        $query = $ownerRecord->{static::$relationship}(); // e.g., $ownerRecord->attachedHouses()

        // Verify it returned a Relation object which has a getQuery method
        if (!$query instanceof Relation) {
             Log::error('AttachedHousesRelationManager Error: Relationship method did not return a Relation object.', [
                'owner_record_id' => $ownerRecord->id,
                'relationship_name' => static::$relationship,
                'return_type' => is_object($query) ? get_class($query) : gettype($query)
            ]);
            throw new \Exception('Relationship method "' . static::$relationship . '" did not return a valid Eloquent Relation object.');
        }

        // Get the Eloquent Builder instance from the Relation object
        $builder = $query->getQuery();

        // Now, apply the specific filter for this manager
        // This ensures we only show attached houses *related to this parent*
        return $builder->where('is_attached_house', true);
    }
} 