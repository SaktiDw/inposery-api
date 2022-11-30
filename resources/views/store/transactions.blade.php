<x-store-layout>
    <div class="flex flex-col gap-4">
        <div class="flex gap-4">
            <x-select />
            <x-search />
        </div>
        @php
        $columns = [
        (object) [
        'key'=>'id',
        'title'=>'id',
        'dataType' => 'numbering'
        ],
        (object) [
        'key'=>'product',
        'title'=>'Price',
        'render'=> 'name'
        ],
        (object) [
        'key'=>'price',
        'title'=>'Price',
        ],
        (object) [
        'key'=>'qty',
        'title'=>'Quantity',
        ],
        (object) [
        'key'=>'type',
        'title'=>'Type',
        ],
        (object) [
        'key'=>'create_at',
        'title'=>'Date',
        ],
        ];
        @endphp
        <x-table :data="$data" :columns="$columns" />
    </div>
</x-store-layout>
