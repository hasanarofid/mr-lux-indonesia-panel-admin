<?php

namespace App\Filament\Resources;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Z3d0X\FilamentLogger\Resources\ActivityResource as BaseActivityResource;

class ActivityResource extends BaseActivityResource
{
    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with([
            'causer',
            'subject' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\Sale::class => ['items.product'],
                    \App\Models\StockEntry::class => ['items.product'],
                    \App\Models\Purchase::class => ['items.product'],
                    \App\Models\DeliveryNote::class => ['items.product'],
                    \App\Models\WarehousePickup::class => ['items.product'],
                    \App\Models\WarehouseReturn::class => ['items.product'],
                ]);
            }
        ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with([
            'causer',
            'subject' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\Sale::class => ['items.product'],
                    \App\Models\StockEntry::class => ['items.product'],
                    \App\Models\Purchase::class => ['items.product'],
                    \App\Models\DeliveryNote::class => ['items.product'],
                    \App\Models\WarehousePickup::class => ['items.product'],
                    \App\Models\WarehouseReturn::class => ['items.product'],
                ]);
            }
        ]);
    }

    public static function getSubjectLabel(?Model $record): string
    {
        if (!$record || !$record->subject_type) {
            return '-';
        }

        $subject = $record->subject;
        $typeName = Str::of($record->subject_type)->afterLast('\\')->headline();

        if ($subject) {
            $name = match (true) {
                isset($subject->invoice_number) => $subject->invoice_number,
                isset($subject->return_number) => $subject->return_number,
                isset($subject->number) => $subject->number,
                isset($subject->name) => $subject->name,
                isset($subject->supplier_name) => $subject->supplier_name,
                isset($subject->title) => $subject->title,
                isset($subject->label) => $subject->label,
                method_exists($subject, 'getName') => $subject->getName(),
                default => '#' . $record->subject_id,
            };

            if ($record->subject_type === \App\Models\StockEntry::class) {
                $typeName = 'Mutasi';
                $name = "{$subject->type} " . \Carbon\Carbon::parse($subject->date)->format('d/m/Y');
            }

            if ($record->subject_type === \App\Models\Purchase::class) {
                $typeName = 'Pembelian';
                $date = isset($subject->date) ? \Carbon\Carbon::parse($subject->date)->format('d/m/Y') : '-';
                $name = "{$subject->supplier_name} {$date}";
            }

            if ($record->subject_type === \App\Models\DeliveryNote::class) {
                $typeName = $subject->type === 'MANUAL' ? 'SJ Manual' : 'SJ Otomatis';
            }

            if ($record->subject_type === \App\Models\WarehousePickup::class) {
                $typeName = 'Pengambilan Gudang';
            }

            if ($record->subject_type === \App\Models\WarehouseReturn::class) {
                $typeName = 'Retur Gudang';
            }

            $summary = static::getItemSummary($record);

            return "{$typeName} {$name}{$summary}";
        }

        // Fallback for deleted records: check properties (old or new attributes)
        $props = $record->properties;
        $attributes = $props->get('old') ?? $props->get('attributes') ?? [];
        
        $name = match (true) {
            isset($attributes['invoice_number']) => $attributes['invoice_number'],
            isset($attributes['return_number']) => $attributes['return_number'],
            isset($attributes['number']) => $attributes['number'],
            isset($attributes['name']) => $attributes['name'],
            isset($attributes['supplier_name']) => $attributes['supplier_name'],
            isset($attributes['label']) => $attributes['label'],
            !empty($record->subject_id) => '#' . $record->subject_id,
            default => 'Unknown',
        };

        if ($record->subject_type === \App\Models\StockEntry::class) {
            $typeName = 'Mutasi';
            $type = $attributes['type'] ?? '';
            $date = isset($attributes['date']) ? \Carbon\Carbon::parse($attributes['date'])->format('d/m/Y') : '';
            $name = "{$type} {$date}";
        }

        if ($record->subject_type === \App\Models\Purchase::class) {
            $typeName = 'Pembelian';
            $supplier = $attributes['supplier_name'] ?? '';
            $date = isset($attributes['date']) ? \Carbon\Carbon::parse($attributes['date'])->format('d/m/Y') : '';
            $name = "{$supplier} {$date}";
        }

        if ($record->subject_type === \App\Models\DeliveryNote::class) {
            $type = $attributes['type'] ?? null;
            $typeName = $type === 'MANUAL' ? 'SJ Manual' : 'SJ Otomatis';
        }

        if ($record->subject_type === \App\Models\WarehousePickup::class) {
            $typeName = 'Pengambilan Gudang';
        }

        if ($record->subject_type === \App\Models\WarehouseReturn::class) {
            $typeName = 'Retur Gudang';
        }

        $summary = static::getItemSummary($record);

        return "{$typeName} {$name}{$summary}";
    }

    protected static function getItemSummary(Model $record): string
    {
        // Try to get from properties first (works for deleted records)
        $summary = $record->properties->get('attributes')['item_summary'] 
            ?? $record->properties->get('old')['item_summary'] 
            ?? null;

        if ($summary) {
            return " ({$summary})";
        }

        // Fallback to direct relationship (works for live records)
        $subject = $record->subject;
        if (!$subject || !method_exists($subject, 'items')) {
            return '';
        }

        try {
            $items = $subject->items;
            if (!$items || $items->isEmpty()) {
                return '';
            }

            $productNames = $items->map(function($item) {
                return $item->product ? $item->product->name : null;
            })->filter()->unique()->values();

            if ($productNames->isEmpty()) {
                $count = $items->count();
                return " ({$count} item)";
            }

            $displayNames = $productNames->take(2);
            $summary = $displayNames->implode(', ');

            if ($productNames->count() > 2) {
                $count = $productNames->count() - 2;
                $summary .= " +{$count}";
            }

            return " ({$summary})";
        } catch (\Exception $e) {
            return '';
        }
    }

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return parent::form($form)
            ->schema([
                \Filament\Forms\Components\Group::make([
                    \Filament\Forms\Components\Section::make([
                        \Filament\Forms\Components\TextInput::make('causer_id')
                            ->afterStateHydrated(function ($component, ?Model $record) {
                                return $component->state($record->causer?->name);
                            })
                            ->label(__('filament-logger::filament-logger.resource.label.user')),

                        \Filament\Forms\Components\TextInput::make('subject_type')
                            ->afterStateHydrated(function ($component, ?Model $record) {
                                return $component->state(static::getSubjectLabel($record));
                            })
                            ->label(__('filament-logger::filament-logger.resource.label.subject')),

                        \Filament\Forms\Components\Textarea::make('description')
                            ->label(__('filament-logger::filament-logger.resource.label.description'))
                            ->rows(2)
                            ->columnSpan('full'),
                    ])
                    ->columns(2),
                ])
                ->columnSpan(['sm' => 3]),

                \Filament\Forms\Components\Group::make([
                    \Filament\Forms\Components\Section::make([
                        \Filament\Forms\Components\Placeholder::make('log_name')
                            ->content(function (?Model $record): string {
                                return $record->log_name ? ucwords($record->log_name) : '-';
                            })
                            ->label(__('filament-logger::filament-logger.resource.label.type')),

                        \Filament\Forms\Components\Placeholder::make('event')
                            ->content(function (?Model $record): string {
                                return $record?->event ? ucwords($record?->event) : '-';
                            })
                            ->label(__('filament-logger::filament-logger.resource.label.event')),

                        \Filament\Forms\Components\Placeholder::make('created_at')
                            ->label(__('filament-logger::filament-logger.resource.label.logged_at'))
                            ->content(function (?Model $record): string {
                                return $record->created_at ? "{$record->created_at->format(config('filament-logger.datetime_format', 'd/m/Y H:i:s'))}" : '-';
                            }),
                    ])
                ]),
                \Filament\Forms\Components\Section::make()
                    ->columns()
                    ->visible(fn ($record) => $record->properties?->count() > 0)
                    ->schema(function (?Model $record) {
                        $properties = $record->properties->except(['attributes', 'old']);

                        $schema = [];

                        if ($properties->count()) {
                            $schema[] = \Filament\Forms\Components\KeyValue::make('properties')
                                ->label(__('filament-logger::filament-logger.resource.label.properties'))
                                ->columnSpan('full');
                        }

                        if ($old = $record->properties->get('old')) {
                            $schema[] = \Filament\Forms\Components\KeyValue::make('old')
                                ->afterStateHydrated(fn (\Filament\Forms\Components\KeyValue $component) => $component->state($old))
                                ->label(__('filament-logger::filament-logger.resource.label.old'));
                        }

                        if ($attributes = $record->properties->get('attributes')) {
                            $schema[] = \Filament\Forms\Components\KeyValue::make('attributes')
                                ->afterStateHydrated(fn (\Filament\Forms\Components\KeyValue $component) => $component->state($attributes))
                                ->label(__('filament-logger::filament-logger.resource.label.new'));
                        }

                        return $schema;
                    }),
            ])
            ->columns(['sm' => 4, 'lg' => null]);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->columns([
                TextColumn::make('log_name')
                    ->badge()
                    ->colors(static::getLogNameColors())
                    ->label(__('filament-logger::filament-logger.resource.label.type'))
                    ->formatStateUsing(fn ($state) => ucwords($state))
                    ->sortable(),

                TextColumn::make('event')
                    ->label(__('filament-logger::filament-logger.resource.label.event'))
                    ->sortable(),

                TextColumn::make('description')
                    ->label(__('filament-logger::filament-logger.resource.label.description'))
                    ->wrap(),

                TextColumn::make('subject_type')
                    ->label(__('filament-logger::filament-logger.resource.label.subject'))
                    ->formatStateUsing(function ($state, Model $record) {
                        return static::getSubjectLabel($record);
                    }),

                TextColumn::make('causer.name')
                    ->label(__('filament-logger::filament-logger.resource.label.user')),

                TextColumn::make('created_at')
                    ->label(__('filament-logger::filament-logger.resource.label.logged_at'))
                    ->dateTime(config('filament-logger.datetime_format', 'd/m/Y H:i:s'), config('app.timezone'))
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc');
    }
}
