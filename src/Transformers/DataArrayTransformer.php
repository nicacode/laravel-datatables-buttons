<?php

namespace Yajra\DataTables\Transformers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Throwable;
use Yajra\DataTables\Html\Column;

class DataArrayTransformer
{
    /**
     * Transform row data by column's definition.
     *
     * @param  array  $row
     * @param  array|Collection<array-key, Column>  $columns
     * @param  string  $type
     * @return array
     */
    public function transform(array $row, array|Collection $columns, string $type = 'printable'): array
    {
        if ($columns instanceof Collection) {
            return $this->buildColumnByCollection($row, $columns, $type);
        }

        return Arr::only($row, $columns);
    }

    /**
     * Transform row column by collection.
     *
     * @param  array  $row
     * @param  Collection<array-key, Column>  $columns
     * @param  string  $type
     * @return array
     */
    protected function buildColumnByCollection(array $row, Collection $columns, string $type = 'printable'): array
    {
        $results = [];
        $columns->each(function (Column $column) use ($row, $type, &$results) {
            if ($column[$type]) {
                $title = $column->title;
                if (is_array($column->data)) {
                    $key = $column->data['filter'] ?? $column->name ?? '';
                } else {
                    $key = $column->data ?? $column->name;
                }

                /** @var string $data */
                $data = Arr::get($row, $key) ?? '';

                if ($type == 'exportable') {
                    $title = $this->decodeContent($title);
                    $dataType = gettype($data);
                    $data = $this->decodeContent($data);
                    settype($data, $dataType);
                }

                $results[$title] = $data;
            }
        });

        return $results;
    }

    /**
     * Decode content to a readable text value.
     *
     * @param  bool|string  $data
     * @return string
     */
    protected function decodeContent(bool|string $data): string
    {
        try {
            if (is_bool($data)) {
                return $data ? 'True' : 'False';
            }

            $decoded = html_entity_decode(trim(strip_tags($data)), ENT_QUOTES, 'UTF-8');

            return str_replace("\xc2\xa0", ' ', $decoded);
        } catch (Throwable $e) {
            return $data;
        }
    }
}
